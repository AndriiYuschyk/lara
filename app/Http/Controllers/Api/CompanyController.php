<?php

namespace App\Http\Controllers\Api;

use App\Enums\CategoryVersionStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\CompanyRequest;
use App\Models\Company;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
            ->limit($limit)
            ->offset($offset)
            ->get();

        $data = $companies->map(function ($company) {
            return [
                'company_id' => $company->id,
                'name' => $company->name,
                'edrpou' => $company->edrpou,
                'address' => $company->address,
                'versions_count' => $company->versions_count,
                'created_at' => \Carbon\Carbon::parse($company->created_at)->format('d.m.Y H:i:s'),
            ];
        });

        $pageNumber = $offset > 0 ? (int) floor($offset / $limit) + 1 : 1;
        $totalPages = $limit > 0 ? (int) ceil($totalCount / $limit) : 0;

        return response()->json([
            'data' => $data,
            'meta' => [
                'page_number' => $pageNumber,
                'page_size' => $limit,
                'total_count' => $totalCount,
                'total_pages' => $totalPages,
            ],
        ], 200);
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
        $payload = $request->validated();

        $company = Company::where('edrpou', trim($payload['edrpou']))->first();

        if (!$company) {
            $company = Company::create($payload);

            return response()->json([
                'status' => CategoryVersionStatus::CREATED->value,
                'company_id' => $company->id,
                'version' => 1,
            ], 201);
        }

        $company->update($payload);

        $latestVersion = $company->latestVersion();
        $wasUpdated = $company->wasChanged(['name', 'address']);


        return response()->json([
            'status' => $wasUpdated ? CategoryVersionStatus::UPDATED->value : CategoryVersionStatus::DUPLICATE->value,
            'company_id' => $company->id,
            'version' => $latestVersion ? $latestVersion->version : 1,
        ], 200);
    }
}
