<?php

use App\Http\Controllers\Api\CompanyController;
use Illuminate\Support\Facades\Route;


Route::post('/company', [CompanyController::class, 'store']);

Route::get('/company/{edrpou}/versions', [CompanyController::class, 'indexVersions']);

