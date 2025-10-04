<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AssetsController;
use App\Http\Controllers\AssetPricesController;

// -----------------------
// Public Routes
// -----------------------
Route::post('/register', [AuthController::class, 'register']); // تسجيل مستخدم عادي
Route::post('/register-admin', [AuthController::class, 'registerAdmin']); // تسجيل Admin (Dashboard)
Route::post('/login', [AuthController::class, 'login']); // تسجيل الدخول
Route::post('/verify-email', [AuthController::class, 'verifyEmail']); // تحقق البريد
Route::post('/resend-verification-code', [AuthController::class, 'resendVerificationCode']); // إعادة إرسال كود
Route::post('/request-password-reset', [AuthController::class, 'requestPasswordReset']); // طلب رمز إعادة تعيين كلمة المرور
Route::post('/reset-password', [AuthController::class, 'resetPassword']); // إعادة تعيين كلمة المرور

// -----------------------
// Protected Routes (JWT)
// -----------------------
Route::group(['middleware' => ['auth:api']], function() {
    
    Route::post('/logout', [AuthController::class, 'logout']); // تسجيل الخروج
    Route::post('/refresh', [AuthController::class, 'refresh']); // تحديث التوكن

    // أمثلة لمسارات محمية حسب الدور
    Route::group(['middleware' => ['role:user']], function() {
        // مسارات خاصة بالمستخدم العادي
        Route::get('/user/dashboard', function() {
            return response()->json(['message' => 'Welcome to user dashboard']);
        });
    });

    Route::group(['middleware' => ['role:admin']], function() {
        // مسارات خاصة بالـ Admin
        Route::get('/admin/dashboard', function() {
            return response()->json(['message' => 'Welcome to admin dashboard']);
        });
    });
});

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:api')->group(function () {

    // ===== أصول التداول =====
    // عرض كل الأصول – متاح لأي مستخدم مسجّل دخول
    Route::get('/assets', [AssetsController::class, 'index']);

    // العمليات الخاصة بالمشرف (Admin)
    Route::middleware('admin')->group(function () {
        Route::post('/assets', [AssetsController::class, 'store']);       // إنشاء أصل جديد
        Route::put('/assets/{id}', [AssetsController::class, 'update']);   // تعديل أصل
        Route::delete('/assets/{id}', [AssetsController::class, 'destroy']); // حذف أصل
    });

});



/*
|--------------------------------------------------------------------------
| API Routes – Asset Prices
|--------------------------------------------------------------------------
|
| توفّر هذه المجموعة نقاط الوصول لإدارة أسعار الأصول.
| - المشاهدة متاحة لأي مستخدم مسجّل.
| - الإضافة/التعديل/الحذف للأدمن فقط.
|
*/

// Route::middleware('auth:api')->group(function () {

//     // ===== عرض آخر الأسعار لكل الأصول =====
//     Route::get('/asset-prices', [AssetPricesController::class, 'index']);

//     // ===== العمليات الخاصة بالأدمن =====
//     Route::middleware('admin')->group(function () {
//         Route::post('/asset-prices', [AssetPricesController::class, 'store']);      // إضافة سعر جديد
//         Route::put('/asset-prices/{id}', [AssetPricesController::class, 'update']);  // تعديل سعر
//         Route::delete('/asset-prices/{id}', [AssetPricesController::class, 'destroy']); // حذف سعر
//     });


// Route::get('/assets', [AssetsController::class, 'index']);          // كل الأصول + آخر سعر
// Route::get('/assets/{id}', [AssetsController::class, 'show']);      // أصل واحد + آخر سعر
// Route::get('/assets/{id}/history', [AssetsController::class, 'history']); // سجل الأسعار
Route::get('/assets', [AssetsController::class, 'index']);
Route::get('/assets/{id}/price', [AssetsController::class, 'latestPrice']);
Route::get('/assets/{id}/chart', [AssetsController::class, 'chart']);


