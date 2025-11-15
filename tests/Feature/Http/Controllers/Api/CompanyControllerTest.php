<?php

namespace tests\Feature\Http\Controllers\Api;

use App\Enums\CategoryVersionStatus;
use App\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;

class CompanyControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /*
     * Тест валідації обов'язкових полів
     */
    public function test_validation_required_fields(): void
    {
        $response = $this->postJson('/api/company', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'edrpou', 'address']);
    }

    /*
     * Тест валідації максимальних довжин полів
     */
    public function test_validation_max_length(): void
    {
        $data = [
            'name' => str_repeat('A', 257), // 257 символів (макс 256)
            'edrpou' => $this->faker->unique()->numerify('###########'), // 11 цифр (макс 10)
            'address' => $this->faker->address(),
        ];

        $response = $this->postJson('/api/company', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'edrpou']);
    }

    /*
     * Тест створення нової компанії
     */
    public function test_create_new_company(): void
    {
        $data = [
            'name' => $this->faker->company(),
            'edrpou' => $this->faker->unique()->numerify('########'),
            'address' => $this->faker->address(),
        ];
        $response = $this->postJson('/api/company', $data);

        // Перевіряємо відповідь API
        $response->assertStatus(201)
            ->assertJson([
                'status' => CategoryVersionStatus::CREATED->value,
                'version' => 1,
            ])
            ->assertJsonStructure([
                'status',
                'company_id',
                'version',
            ]);

        // Перевіряємо що компанія створилась в БД
        $this->assertDatabaseHas('companies', [
            'edrpou' => $data['edrpou'],
            'name' => $data['name'],
        ]);

        // Перевіряємо що версія прив'язана до компанії
        $this->assertDatabaseHas('company_versions', [
            'version' => 1,
            'name' => $data['name'],
        ]);

        $company = Company::where('edrpou', $data['edrpou'])->first();
        $this->assertEquals(1, $company->versions()->count());
    }

    /*
     * Тест оновлення існуючої компанії
     */
    public function test_update_existing_company(): void
    {
        $data = [
            'name' => $this->faker->company(),
            'edrpou' => $this->faker->unique()->numerify('########'),
            'address' => $this->faker->address(),
        ];

        // Створюємо компанію версія 1
        $company = Company::create($data);

        // Перевіряємо що версія 1 створилась автоматично
        $this->assertEquals(1, $company->versions()->count());

        // Оновлюємо дані компанії і відправляємо запит
        $data['name'] = $this->faker->company();
        $data['address'] = $this->faker->address();

        $response = $this->postJson('/api/company', $data);

        $response->assertStatus(200)
            ->assertJson([
                'status' => CategoryVersionStatus::UPDATED->value,
                'company_id' => $company->id,
                'version' => 2,
            ]);

        // Перевіряємо що дані оновились
        $this->assertDatabaseHas('companies', [
            'edrpou' => $data['edrpou'],
            'name' => $data['name'],
        ]);

        // Перевіряємо що створилась версія 2
        $this->assertDatabaseHas('company_versions', [
            'company_id' => $company->id,
            'version' => 2,
            'name' => $data['name'],
        ]);

        // Перевіряємо що тепер 2 версії
        $company->refresh();
        $this->assertEquals(2, $company->versions()->count());
    }

    /*
     * Тест виявлення дублікату компанії за ЄДРПОУ
     */
    public function test_detect_duplicate_data(): void
    {
        $data = [
            'name' => $this->faker->company(),
            'edrpou' => $this->faker->unique()->numerify('########'),
            'address' => $this->faker->address(),
        ];

        // Створюємо компанію
        $this->postJson('/api/company', $data);

        // Повторний запит не змінює дані
        $response = $this->postJson('/api/company', $data);

        $response->assertStatus(200)
            ->assertJson([
                'status' => CategoryVersionStatus::DUPLICATE->value,
            ]);

        // Перевіряємо що версія залишилась 1
        $company = Company::where('edrpou', $data['edrpou'])->first();
        $this->assertEquals(1, $company->versions()->count());
    }

    public function test_get_company_versions(): void
    {
        $data = [
            'name' => $this->faker->company(),
            'edrpou' => $this->faker->unique()->numerify('########'),
            'address' => $this->faker->address(),
        ];

        // Створюємо компанію
        $company = Company::create($data);

        // Оновлюємо 2 рази для створення версій 2 і 3
        $company->update(['name' => $data['name'] . ' v2']);
        $company->update(['name' => $data['name'] . ' v3']);

        $response = $this->getJson("/api/company/{$data['edrpou']}/versions");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'versions' => [
                    '*' => [
                        'company_id',
                        'version',
                        'name',
                        'address',
                        'created_at',
                    ],
                ],
            ]);

        // Перевіряємо що отримали 3 версії
        $this->assertCount(3, $response->json('versions'));
    }

    /*
     * Тест отримання версій для неіснуючої компанії
     */
    public function test_versions_not_found(): void
    {
        $response = $this->getJson('/api/company/00000000/versions');

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Компанію не знайдено',
            ]);
    }

    public function test_get_companies_list(): void
    {
        // Створюємо 25 компаній
        Company::factory()->count(25)->create();

        $response = $this->getJson('/api/companies?limit=10&offset=0');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'company_id',
                        'name',
                        'edrpou',
                        'address',
                        'versions_count',
                        'created_at',
                    ],
                ],
                'meta' => [
                    'page_number',
                    'page_size',
                    'total_count',
                    'total_pages',
                ],
            ])
            ->assertJsonPath('meta.page_size', 10)
            ->assertJsonPath('meta.total_count', 25);

        $this->assertCount(10, $response->json('data'));
    }
}
