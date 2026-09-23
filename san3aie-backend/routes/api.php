<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminMonetizationController;
use App\Http\Controllers\Api\AreaController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FeaturedWorkerController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ServiceRequestController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\WorkerController;
use App\Http\Controllers\WorkerVerificationController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/areas', [AreaController::class, 'index']);

Route::get('/workers', [WorkerController::class, 'index']);
Route::get('/workers/{id}', [WorkerController::class, 'show']);

Route::get('/subscription/plans', [SubscriptionController::class, 'plans']);
Route::get('/featured-workers', [FeaturedWorkerController::class, 'index']);
Route::get('/payment/mock/{transactionId}', [SubscriptionController::class, 'mockPayment']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::put('/worker/profile', [WorkerController::class, 'updateProfile']);
    Route::post('/worker/availability', [WorkerController::class, 'availability']);

    Route::post('/service-requests', [ServiceRequestController::class, 'store']);
    Route::get('/customer/requests', [ServiceRequestController::class, 'customerRequests']);
    Route::get('/worker/requests', [ServiceRequestController::class, 'workerRequests']);
    Route::get('/service-requests/{id}', [ServiceRequestController::class, 'show']);
    Route::post('/service-requests/{id}/accept', [ServiceRequestController::class, 'accept']);
    Route::post('/service-requests/{id}/reject', [ServiceRequestController::class, 'reject']);
    Route::post('/service-requests/{id}/status', [ServiceRequestController::class, 'updateStatus']);
    Route::post('/service-requests/{id}/cancel', [ServiceRequestController::class, 'cancel']);

    Route::post('/service-requests/{id}/review', [ReviewController::class, 'store']);
    Route::get('/worker/reviews', [ReviewController::class, 'workerReviews']);

    Route::get('/dashboard', [DashboardController::class, 'index']);

    Route::put('/profile', [ProfileController::class, 'update']);
    Route::put('/profile/password', [ProfileController::class, 'changePassword']);

    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/unread', [NotificationController::class, 'unread']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);

    Route::post('/worker/verification', [WorkerVerificationController::class, 'store']);
    Route::get('/worker/verification', [WorkerVerificationController::class, 'status']);

    Route::get('/subscription', [SubscriptionController::class, 'current']);
    Route::post('/subscription/subscribe', [SubscriptionController::class, 'subscribe']);
    Route::post('/subscription/cancel', [SubscriptionController::class, 'cancel']);
    Route::get('/payments', [SubscriptionController::class, 'paymentHistory']);

    Route::get('/worker/featured', [FeaturedWorkerController::class, 'myFeature']);
});

Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard']);
    Route::get('/users', [AdminController::class, 'users']);
    Route::get('/workers', [AdminController::class, 'workers']);
    Route::get('/customers', [AdminController::class, 'customers']);
    Route::get('/categories', [AdminController::class, 'categories']);
    Route::get('/areas', [AdminController::class, 'areas']);
    Route::get('/requests', [AdminController::class, 'requests']);
    Route::get('/reviews', [AdminController::class, 'reviews']);
    Route::get('/statistics', [AdminController::class, 'statistics']);
    Route::get('/reports', [AdminController::class, 'reports']);

    Route::post('/users/{id}/block', [AdminController::class, 'blockUser']);
    Route::post('/users/{id}/unblock', [AdminController::class, 'unblockUser']);
    Route::delete('/users/{id}', [AdminController::class, 'deleteUser']);

    Route::get('/verifications', [WorkerVerificationController::class, 'index']);
    Route::post('/verifications/{id}/approve', [WorkerVerificationController::class, 'approve']);
    Route::post('/verifications/{id}/reject', [WorkerVerificationController::class, 'reject']);

    Route::get('/plans', [AdminMonetizationController::class, 'plans']);
    Route::post('/plans', [AdminMonetizationController::class, 'storePlan']);
    Route::put('/plans/{id}', [AdminMonetizationController::class, 'updatePlan']);
    Route::delete('/plans/{id}', [AdminMonetizationController::class, 'deletePlan']);

    Route::get('/subscriptions', [AdminMonetizationController::class, 'subscriptions']);
    Route::get('/payments', [AdminMonetizationController::class, 'payments']);

    Route::get('/featured-workers', [AdminMonetizationController::class, 'featuredWorkers']);
    Route::post('/featured-workers/{workerId}', [AdminMonetizationController::class, 'featureWorker']);
    Route::delete('/featured-workers/{workerId}', [AdminMonetizationController::class, 'removeFeature']);

    Route::get('/monetization/statistics', [AdminMonetizationController::class, 'statistics']);
    Route::post('/payments/{id}/refund', [AdminMonetizationController::class, 'refundPayment']);
});
