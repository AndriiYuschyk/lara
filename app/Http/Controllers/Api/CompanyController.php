<?php

namespace App\Http\Controllers\Api;

use App\Enums\CategoryVersionStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\CompanyRequest;
use App\Http\Resources\CompanyCollection;
use App\Http\Resources\CompanyVersionResource;
use App\Http\Resources\CompanyWithKvedsResource;
use App\Models\Company;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;
use Illuminate\Support\Facades\Log;

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
     *     path="/api/companies",
     *     summary="Отримати список компаній збережених в системі",
     *     description="Повертає список всіх компаній збережених в системі з інформацієї про кількість збережених версій з підтримкою пагінації (limit, offset)",
     *     operationId="listCompanies",
     *     tags={"Companies"},
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Кількість записів на сторінці (limit)",
     *         required=false,
     *         @OA\Schema(type="integer", example=20, default=20)
     *     ),
     *     @OA\Parameter(
     *         name="offset",
     *         in="query",
     *         description="Зміщення (offset)",
     *         required=false,
     *         @OA\Schema(type="integer", example=0, default=0)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Список усіх компаній",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="company_id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="ТОВ Українська енергетична біржа"),
     *                     @OA\Property(property="edrpou", type="string", example="37027819"),
     *                     @OA\Property(property="address", type="string", example="01001, Україна, м. Київ, вул. Хрещатик, 44"),
     *                     @OA\Property(property="versions_count", type="integer", example=5),
     *                     @OA\Property(property="created_at", type="string", example="14.11.2025 18:30:45")
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="meta",
     *                 type="object",
     *                 @OA\Property(property="page_number", type="integer", example=2),
     *                 @OA\Property(property="page_size", type="integer", example=20),
     *                 @OA\Property(property="total_count", type="integer", example=150),
     *                 @OA\Property(property="total_pages", type="integer", example=8)
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $limit = (int) $request->get('limit', 20);
        $offset = (int) $request->get('offset', 0);

        $totalCount = Company::count();

        $companies = Company::withCount('versions')
            ->orderBy('created_at', 'desc')
            ->offset($offset)
            ->limit($limit)
            ->get();

        $collection = new \Illuminate\Pagination\LengthAwarePaginator(
            $companies,
            $totalCount,
            $limit,
            ($offset / $limit) + 1,
            ['path' => $request->url()]
        );

        return response()->json(new CompanyCollection($collection), 200);
    }

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

        $versions = CompanyVersionResource::collection($company->versions);

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
        $payload = $request->validated();

        $company = Company::where('edrpou', trim($payload['edrpou']))->first();

        if (!$company) {
            $company = Company::create($payload);

            log::info('Created new Company: ', array_merge([
                'company_id' => $company->id,
                'version' => 1,
                'status' => CategoryVersionStatus::CREATED->value,
            ], $payload));

            return response()->json([
                'status' => CategoryVersionStatus::CREATED->value,
                'company_id' => $company->id,
                'version' => 1,
            ], 201);
        }

        $company->update($payload);

        $latestVersion = $company->latestVersion();
        $wasUpdated = $company->wasChanged(['name', 'address']);
        $status = $wasUpdated ? CategoryVersionStatus::UPDATED->value : CategoryVersionStatus::DUPLICATE->value;

        log::info("Company data was {$status}: ", array_merge([
            'company_id' => $company->id,
            'version' => $latestVersion ? $latestVersion->version : 1,
            'status' => $status,
        ], $payload));

        return response()->json([
            'status' => $status,
            'company_id' => $company->id,
            'version' => $latestVersion ? $latestVersion->version : 1,
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/companies-kveds",
     *     summary="Отримати список компаній збережених в системі з їх КВЕДами",
     *     description="Повертає список всіх компаній збережених в системі з інформацієї про кількість збережених версій і масивом їх КВЕДів кодів з підтримкою пагінації (limit, offset)",
     *     operationId="listCompaniesWithKveds",
     *     tags={"Companies"},
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Кількість записів",
     *         required=false,
     *         @OA\Schema(type="integer", example=20, default=20)
     *     ),
     *     @OA\Parameter(
     *         name="offset",
     *         in="query",
     *         description="Зміщення",
     *         required=false,
     *         @OA\Schema(type="integer", example=0, default=0)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Список компаній з КВЕДами",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="company_id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="ТОВ Українська компанія"),
     *                     @OA\Property(property="edrpou", type="string", example="37027819"),
     *                     @OA\Property(property="address", type="string", example="43024б Київ, вул. Хрещатик, 44"),
     *                     @OA\Property(
     *                         property="kveds",
     *                         type="array",
     *                         @OA\Items(
     *                             @OA\Property(property="code", type="string", example="62.01"),
     *                             @OA\Property(property="name", type="string", example="Комп'ютерне програмування"),
     *                             @OA\Property(property="is_primary", type="boolean", example=true)
     *                         )
     *                     ),
     *                     @OA\Property(property="created_at", type="string", example="14.11.2025 18:30:45")
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="meta",
     *                 type="object",
     *                 @OA\Property(property="page_number", type="integer"),
     *                 @OA\Property(property="page_size", type="integer"),
     *                 @OA\Property(property="total_count", type="integer"),
     *                 @OA\Property(property="total_pages", type="integer")
     *             )
     *         )
     *     )
     * )
     */
    public function indexCompanyKveds(Request $request): JsonResponse
    {
        $limit = (int) $request->get('limit', 20);
        $offset = (int) $request->get('offset', 0);

        $totalCount = Company::count();

        $companies = Company::with('kveds')
            ->withCount('versions')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->offset($offset)
            ->get();

        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $companies, $totalCount, $limit, ($offset / $limit) + 1, ['path' => $request->url()]
        );

        return response()->json([
            'data' => CompanyWithKvedsResource::collection($paginator->getCollection()),
            'meta' => [
                'page_number' => $paginator->currentPage(),
                'page_size' => $paginator->perPage(),
                'total_count' => $paginator->total(),
                'total_pages' => $paginator->lastPage(),
            ],
        ], 200);
    }
}
