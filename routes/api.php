<?php

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

// 🔐 Public Auth Routes
Route::post('/login', [AuthController::class, 'login']);
Route::get('/verify-email', [AuthController::class, 'verifyEmail']); // custom token
// Route::get('/verify-email/{id}/{hash}', [VerificationController::class, '__invoke'])->name('verification.verify'); // default Laravel


// 🔐 Admin-Only Registration (after login as admin)
Route::middleware(['jwt.auth', 'isAdmin'])->post('/register', [AuthController::class, 'register']);

// 🌐 Public Routes
Route::get('/news', [NewsController::class, 'index']);
Route::get('/news/{id}', [NewsController::class, 'show']);

Route::get('/layouts', [RoomPlannerController::class, 'getLayouts']);
Route::post('/layouts/save', [RoomPlannerController::class, 'saveLayout']);

Route::post('/submit-property', [PropertyController::class, 'submitProperty']);
Route::get('/properties/{id}', [PropertyController::class, 'getProperty']);
Route::get('/properties', [PropertyController::class, 'getPublishedProperties']);



Route::post('/inquiries', [InquiryController::class, 'store']);
Route::get('/inquiries/{id}/with-replies', [InquiryController::class, 'showWithReplies']);

Route::post('/appointments', [AppointmentController::class, 'store']);
Route::get('/appointments', [AppointmentController::class, 'index']);

Route::get('/about-us', [AboutUsController::class, 'show']);

Route::get('/jobs/published', [JobController::class, 'getPublishedJobs']);
Route::post('/job-applications', [JobApplicationController::class, 'store']);

Route::get('/services', [ServiceController::class, 'index']); // 🌐 Public: Get only approved services
Route::get('/services/{id}', [ServiceController::class, 'show']); // 🌐 Public: Show approved service only

Route::get('/contacts', [ContactController::class, 'publicIndex']);


    // 🔐 Authenticated Routes (Any verified user with token)
Route::middleware(['jwt.auth', 'isAdmin'])->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/user/update-profile', [AuthController::class, 'updateProfile']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::post('/news', [NewsController::class, 'store']);
    Route::put('/news/{id}', [NewsController::class, 'update']);
    Route::delete('/news/{id}', [NewsController::class, 'destroy']);


});

// 🔐 Admin-Only Routes (Protected with adminOnly middleware)
Route::middleware(['jwt.auth', 'isAdmin'])->prefix('admin')->group(function () {
    // 🏠 Property Management
    Route::get('/properties', [PropertyController::class, 'getAllProperties']);
    Route::patch('/property/{id}', [PropertyController::class, 'updateApprovalStatus']);
    Route::delete('/property/{id}', [PropertyController::class, 'deleteProperty']);

    // 💼 Services CRUD
    Route::get('/services', [ServiceController::class, 'getAll']); // 🔐 Admin: Get all services (0 & 1)
    Route::post('/services', [ServiceController::class, 'store']); // 🔐 Admin: Add new service
    Route::put('/services/{id}', [ServiceController::class, 'update']); // 🔐 Admin: Update service
    Route::delete('/services/{id}', [ServiceController::class, 'destroy']); // 🔐 Admin: Delete service
    
Route::get('/contacts', [ContactController::class, 'adminIndex']); // inside Route::prefix('admin')
Route::post('/contacts', [ContactController::class, 'store']);
Route::put('/contacts/{id}', [ContactController::class, 'update']);
Route::delete('/contacts/{id}', [ContactController::class, 'destroy']);

    Route::patch('/appointments/{id}/status', [AppointmentController::class, 'updateStatus']);
    Route::post('/appointments/{id}/message', [AppointmentController::class, 'sendMessage']);
    
    
    // 📬 Inquiries
    Route::get('/inquiries', [InquiryController::class, 'index']);
    Route::get('/inquiries/{id}', [InquiryController::class, 'show']);
    Route::patch('/inquiries/{id}/status', [InquiryController::class, 'updateStatus']);
    Route::delete('/inquiries/{id}', [InquiryController::class, 'destroy']);
    Route::post('/inquiries/{id}/reply', [InquiryController::class, 'reply']);

    // 👔 Job Applications
    Route::get('/job-applications', [JobApplicationController::class, 'index']);
    Route::get('/job-applications/{id}', [JobApplicationController::class, 'show']);
    Route::patch('/job-applications/{id}/status', [JobApplicationController::class, 'updateStatus']);
    Route::delete('/job-applications/{id}', [JobApplicationController::class, 'destroy']);
    Route::post('/job-applications/{id}/reply', [JobApplicationController::class, 'sendReply']);


    // 📊 Dashboard
    Route::get('/dashboard/stats', [AdminDashboardController::class, 'getDashboardStats']);
    Route::get('/dashboard/property-trends', [AdminDashboardController::class, 'getPropertyTrends']);
    Route::get('/dashboard/inquiry-trends', [AdminDashboardController::class, 'getInquiryTrends']);
    Route::get('/dashboard/job-application-trends', [AdminDashboardController::class, 'getJobApplicationTrends']);
    Route::get('/dashboard/traffic', [AdminDashboardController::class, 'getWebsiteTraffic']);

    // 📄 About Us Content Management
    Route::get('/about-us', [AboutUsController::class, 'getAdminData']);
    Route::match(['PUT', 'POST'], '/about-us/update', [AboutUsController::class, 'update']);
    Route::put('/about-us/update-status', [AboutUsController::class, 'updateStatus']);
    Route::post('/about-us/revert/{version}', [AboutUsController::class, 'revertVersion']);
});


// 🔐 Admin Job CRUD (Moved outside and protected separately)
Route::middleware(['jwt.auth', 'isAdmin'])->prefix('jobs')->group(function () {
    Route::get('/', [JobController::class, 'index']);
    Route::post('/', [JobController::class, 'store']);
    Route::get('/{id}', [JobController::class, 'show']);
    Route::match(['PUT', 'POST'], '/{id}', [JobController::class, 'update']);
    Route::delete('/{id}', [JobController::class, 'destroy']);
});
