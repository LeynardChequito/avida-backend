<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\RoomPlannerController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\InquiryController;

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
Route::delete('/admin/property/{id}', [PropertyController::class, 'deleteProperty']);

Route::get('/contacts', [ContactController::class, 'index']);
Route::post('/contacts', [ContactController::class, 'store']);
Route::put('/contacts/{id}', [ContactController::class, 'update']); // Update contacts (Admin)

Route::get('/admin/inquiries', [InquiryController::class, 'index']); // Get all inquiries
Route::get('/admin/inquiries/{id}', [InquiryController::class, 'show']); // Get inquiry details
Route::patch('/admin/inquiries/{id}/status', [InquiryController::class, 'updateStatus']); // Update status
Route::delete('/admin/inquiries/{id}', [InquiryController::class, 'destroy']); // Delete inquiry
Route::get('/inquiries/{id}/with-replies', [InquiryController::class, 'showWithReplies']); // Fetch inquiry + replies
Route::post('/inquiries', [InquiryController::class, 'store']); // Submit inquiry (User)
Route::post('/inquiries/{id}/reply', [InquiryController::class, 'reply']); // Admin replies to user


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

