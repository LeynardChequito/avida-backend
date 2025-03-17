<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\RoomPlannerController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\InquiryController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\JobApplicationController;
use App\Http\Controllers\AboutUsController;
use App\Http\Controllers\AdminDashboardController;
use App\Models\Traffic;

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
Route::delete('/contacts/{id}', [ContactController::class, 'destroy']);

Route::get('/admin/inquiries', [InquiryController::class, 'index']); // Get all inquiries
Route::get('/admin/inquiries/{id}', [InquiryController::class, 'show']); // Get inquiry details
Route::patch('/admin/inquiries/{id}/status', [InquiryController::class, 'updateStatus']); // Update status
Route::delete('/admin/inquiries/{id}', [InquiryController::class, 'destroy']); // Delete inquiry
Route::get('/inquiries/{id}/with-replies', [InquiryController::class, 'showWithReplies']); // Fetch inquiry + replies
Route::post('/inquiries', [InquiryController::class, 'store']); // Submit inquiry (User)
Route::post('/inquiries/{id}/reply', [InquiryController::class, 'reply']); // Admin replies to user

Route::post('/appointments', [AppointmentController::class, 'store']);
Route::get('/appointments', [AppointmentController::class, 'index']);


Route::prefix('admin')->group(function () {
    Route::apiResource('/services', ServiceController::class);
    Route::get('/services', [ServiceController::class, 'index']);
    Route::post('/services', [ServiceController::class, 'store']);
    Route::put('/services/{id}', [ServiceController::class, 'update']);
    Route::delete('/services/{id}', [ServiceController::class, 'destroy']);
    Route::patch('/services/{id}/status', [ServiceController::class, 'updateStatus']);
    Route::get('/services/{id}', [ServiceController::class, 'show']);

});

Route::prefix('jobs')->group(function () {
    Route::get('/', [JobController::class, 'index']); // ✅ All jobs (Admin)
    Route::get('/published', [JobController::class, 'getPublishedJobs']); // ✅ Only published jobs (User)
    Route::post('/', [JobController::class, 'store']); // ✅ Create a new job
    Route::get('/{id}', [JobController::class, 'show']); // ✅ Get a single job
    Route::match(['PUT', 'POST'], '/{id}', [JobController::class, 'update']); // ✅ Allow both PUT & POST for updates
    Route::delete('/{id}', [JobController::class, 'destroy']); // ✅ Delete job
});

Route::post('/job-applications', [JobApplicationController::class, 'store']);
// Route::get('/job-applications', [JobApplicationController::class, 'index']);
Route::get('/admin/job-applications', [JobApplicationController::class, 'index']); // Get all applications
Route::get('/admin/job-applications/{id}', [JobApplicationController::class, 'show']); // Get single application
Route::patch('/admin/job-applications/{id}/status', [JobApplicationController::class, 'updateStatus']); // Update status
Route::delete('/admin/job-applications/{id}', [JobApplicationController::class, 'destroy']); // Delete application
Route::post('/admin/job-applications/{id}/reply', [JobApplicationController::class, 'sendReply']);


Route::get('/about-us', [AboutUsController::class, 'show']);
Route::get('/admin/about-us', [AboutUsController::class, 'getAdminData']);




Route::get('/admin/dashboard/stats', [AdminDashboardController::class, 'getDashboardStats']);
Route::get('/admin/dashboard/property-trends', [AdminDashboardController::class, 'getPropertyTrends']);
Route::get('/admin/dashboard/inquiry-trends', [AdminDashboardController::class, 'getInquiryTrends']);
Route::get('/admin/dashboard/job-application-trends', [AdminDashboardController::class, 'getJobApplicationTrends']);
Route::get('/admin/dashboard/traffic', [AdminDashboardController::class, 'getWebsiteTraffic']);



// Authenticated Routes (Require JWT)
Route::middleware(['jwt.auth'])->group(function () {
    Route::post('/news', [NewsController::class, 'store']);
    Route::put('/news/{id}', [NewsController::class, 'update']); // ✅ Ensure PUT is explicitly declared
    Route::delete('/news/{id}', [NewsController::class, 'destroy']);

    // User routes
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/user/update-profile', [AuthController::class, 'updateProfile']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::patch('/appointments/{id}/status', [AppointmentController::class, 'updateStatus']);
    Route::post('/appointments/{id}/message', [AppointmentController::class, 'sendMessage']);

    Route::match(['PUT', 'POST'], '/admin/about-us/update', [AboutUsController::class, 'update']);
    Route::put('/admin/about-us/update-status', [AboutUsController::class, 'updateStatus']);
    Route::post('/admin/about-us/revert/{version}', [AboutUsController::class, 'revertVersion']);
});

