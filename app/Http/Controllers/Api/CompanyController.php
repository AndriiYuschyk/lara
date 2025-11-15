<?php

namespace App\Http\Controllers\Api;

use App\Enums\CategoryVersionStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\CompanyRequest;
use App\Models\Company;
use Illuminate\Http\JsonResponse;
use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *     title="Company API",
 *     version="1.0.0",
 *     description="API для управління компаніями з підтримкою версійності",
 * )
 *
 * @OA\Server(
 *     url="http://localhost:8080",
 *     description="Локальний сервер розробки"
 * )
 *
 * @OA\Tag(
 *     name="Companies",
 *     description="Операції з компаніями"
 * )
 */
class CompanyController extends Controller
{

    /**
     * @OA\Get(
     *     path="/api/company/{edrpou}/versions",
     *     summary="Отримати всі версії компанії за її ЄДРПОУ",
     *     description="Повертає історію всіх змін компанії за її ЄДРПОУ",
     *     operationId="getCompanyVersions",
     *     tags={"Companies"},
     *     @OA\Parameter(
     *         name="edrpou",
     *         in="path",
     *         required=true,
     *         description="ЄДРПОУ компанії",
     *         @OA\Schema(type="string", example="37027819")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Список версій компанії",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="versions",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="company_id", type="integer", example=1),
     *                     @OA\Property(property="version", type="integer", example=2),
     *                     @OA\Property(property="name", type="string", example="ТОВ Українська енергетична біржа"),
     *                     @OA\Property(property="edrpou", type="string", example="37027819"),
     *                     @OA\Property(property="address", type="string", example="01001, Україна, м. Київ, вул. Хрещатик, 44"),
     *                     @OA\Property(property="created_at", type="string", example="14.11.2025 18:30:45")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Компанію не знайдено",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Компанію не знайдено")
     *         )
     *     )
     * )
     */
    public function indexVersions(string $edrpou)
    {
        $company = Company::where('edrpou', $edrpou)->first();

        if (!$company) {
            return response()->json([
                'message' => 'Компанію не знайдено',
            ], 404);
        }

        $versions = $company->versions()
            ->orderBy('version', 'desc')
            ->get()
            ->map(function ($version) use ($company) {
                return [
                    'company_id' => $version->company_id,
                    'version' => $version->version,
                    'name' => $version->name,
                    'edrpou' => $company->edrpou,
                    'address' => $version->address,
                    'created_at' => \Carbon\Carbon::parse($version->created_at)->format('d.m.Y H:i:s'),
                ];
            });

        return response()->json([
            'versions' => $versions,
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/company",
     *     summary="Створення/Оновлення компанії",
     *     description="Створює нову компанію або оновлює існуючу за ЄДРПОУ",
     *     operationId="storeCompany",
     *     tags={"Companies"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Дані компанії",
     *         @OA\JsonContent(
     *             required={"name","edrpou","address"},
     *             @OA\Property(
     *                 property="name",
     *                 type="string",
     *                 maxLength=256,
     *                 description="Назва компанії",
     *                 example="ТОВ Українська енергетична біржа"
     *             ),
     *             @OA\Property(
     *                 property="edrpou",
     *                 type="string",
     *                 maxLength=10,
     *                 description="ЄДРПОУ компанії",
     *                 example="37027819"
     *             ),
     *             @OA\Property(
     *                 property="address",
     *                 type="string",
     *                 description="Адреса компанії",
     *                 example="01001, Україна, м. Київ, вул. Хрещатик, 44"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Компанію успішно створено - 'created'",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="created"),
     *             @OA\Property(property="company_id", type="integer", example=1),
     *             @OA\Property(property="version", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Компанію оновлено - 'updated' або виявлено дублікат - 'duplicate'",
     *         @OA\JsonContent(
     *             oneOf={
     *                 @OA\Schema(
     *                     @OA\Property(property="status", type="string", example="updated"),
     *                     @OA\Property(property="company_id", type="integer", example=1),
     *                     @OA\Property(property="version", type="integer", example=2)
     *                 ),
     *                 @OA\Schema(
     *                     @OA\Property(property="status", type="string", example="duplicate"),
     *                     @OA\Property(property="company_id", type="integer", example=1),
     *                     @OA\Property(property="version", type="integer", example=1)
     *                 )
     *             }
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Помилка валідації",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Назва компанії є обов'язковою (and 1 more error)"),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="name",
     *                     type="array",
     *                     @OA\Items(type="string", example="Назва компанії є обов'язковою")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function store(CompanyRequest $request): JsonResponse
    {
        $validatedData = $request->validated();

        $company = Company::where('edrpou', trim($validatedData['edrpou']))->first();

        if (!$company) {
            $company = Company::create($validatedData);

            $companyVersion = $company->versions()->create([
                'company_id' => $company->id,
                'version' => $company->getNextVersionNumber(),
                'name' => $validatedData['name'],
                'address' => $validatedData['address'],
            ]);

            return response()->json([
                'status' => CategoryVersionStatus::CREATED->value,
                'company_id' => $company->id,
                'version' => $companyVersion->version,
            ], 201);
        }

        $hasChanges = $company->name !== $validatedData['name']
            || $company->address !== $validatedData['address'];

        if ($hasChanges) {
            $company->update($validatedData);

            $companyVersion = $company->versions()->create([
                'company_id' => $company->id,
                'version' => $company->getNextVersionNumber(),
                'name' => $validatedData['name'],
                'address' => $validatedData['address'],
            ]);

            return response()->json([
                'status' => CategoryVersionStatus::UPDATED->value,
                'company_id' => $company->id,
                'version' => $companyVersion->version,
            ], 200);
        }

        return response()->json([
            'status' => CategoryVersionStatus::DUPLICATE->value,
            'company_id' => $company->id,
            'version' => $company->latestVersion()->version,
        ], 200);
    }
}
