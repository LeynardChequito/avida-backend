<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\RoomPlannerController;
use App\Http\Controllers\PropertyController;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// Public Routes
Route::get('/news', [NewsController::class, 'index']);
Route::get('/news/{id}', [NewsController::class, 'show']); // ✅ Fetch single news

Route::get('/layouts', [RoomPlannerController::class, 'getLayouts']);
Route::post('/layouts/save', [RoomPlannerController::class, 'saveLayout']);


Route::post('/submit-property', [PropertyController::class, 'submitProperty']);
Route::get('/admin/properties', [PropertyController::class, 'getAllProperties']);
Route::patch('/admin/property/{id}', [PropertyController::class, 'updateApprovalStatus']);
Route::get('/properties/{id}', [PropertyController::class, 'getProperty']);
Route::get('/properties', [PropertyController::class, 'getPublishedProperties']);

// Authenticated Routes (Require JWT)
Route::middleware(['jwt.auth'])->group(function () {
    Route::post('/news', [NewsController::class, 'store']);
    Route::put('/news/{id}', [NewsController::class, 'update']); // ✅ Ensure PUT is explicitly declared
    Route::delete('/news/{id}', [NewsController::class, 'destroy']);

    // User routes
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/user/update-profile', [AuthController::class, 'updateProfile']);
    Route::post('/logout', [AuthController::class, 'logout']);

});

