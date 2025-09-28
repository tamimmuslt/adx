<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AssetsController;

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


Route::middleware(['auth:api'])->group(function () {
    Route::get('/assets', [AssetsController::class,'index']); // عرض الأصول

    Route::post('/assets', [AssetsController::class,'store']); // إنشاء أصل
    Route::put('/assets/{id}', [AssetsController::class,'update']); // تعديل أصل
    Route::delete('/assets/{id}', [AssetsController::class,'destroy']); // حذف أصل
});
