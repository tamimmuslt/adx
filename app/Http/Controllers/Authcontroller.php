<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\EmailVerificationCode;
use App\Models\PasswordResetToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Mail\EmailVerificationCode as EmailVerificationCodeMail;
use Illuminate\Support\Str;

class Authcontroller extends Controller
{

    // ========================
    // تسجيل مستخدم جديد (User عادي)
    // ========================
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email', 'unique:users', 'regex:/@gmail\.com$/i'],
            'phone' => ['required', 'unique:users'],
            'password' => ['required', 'min:6'],
            'full_name' => ['required', 'string'],
        ], ['email.regex'=>'Email must be a Gmail address.']);

        if ($validator->fails()) {
            return response()->json(['errors'=>$validator->errors()],422);
        }

        $data = $validator->validated();
        $data['password'] = Hash::make($data['password']);
        $data['role'] = 'user'; // مستخدم عادي
        $user = User::create($data);

        // توليد كود 6 أرقام للتحقق من البريد
        $code = rand(100000,999999);

        EmailVerificationCode::create([
            'user_id' => $user->id,
            'code_hash' => Hash::make($code),
            'expires_at' => now()->addMinutes(10),
        ]);

        // إرسال الكود على البريد
        Mail::to($user->email)->send(new EmailVerificationCodeMail($code));

        $token = JWTAuth::fromUser($user);

        return response()->json([
            'message'=>'User registered successfully. Please verify your email.',
            'user_id'=>$user->id,
            'access_token'=>$token,
            'token_type'=>'bearer',
            'expires_in'=>JWTAuth::factory()->getTTL()*60
        ]);
    }

    // ========================
    // تسجيل Admin (Dashboard)
    // ========================
    public function registerAdmin(Request $request)
{
    // التحقق من أن المستخدم الحالي هو أدمن
    $currentUser =JWTAuth::user();

    if (!$currentUser || $currentUser->role !== 'admin') {
        return response()->json([
            'error' => 'Only admins can create new admins.'
        ], 403);
    }

    // تحقق من بيانات الأدمن الجديد
    $validator = Validator::make($request->all(), [
        'email' => ['required','email','unique:users'],
        'password' => ['required','min:6'],
        'full_name' => ['required','string'],
        'phone' => ['required', 'unique:users'],
    ]);

    if ($validator->fails()) {
        return response()->json(['errors'=>$validator->errors()],422);
    }

    $data = $validator->validated();
    $data['password'] = Hash::make($data['password']);
    $data['role'] = 'admin';       // الدور
    $data['is_verified'] = true;   // الأدمن ما يحتاج تفعيل

    $user = User::create($data);

    return response()->json([
        'message'=>'Admin created successfully by super admin.',
        'user_id'=>$user->id,
    ]);
}



    // ========================
    // تسجيل الدخول
    // ========================
    public function login(Request $request)
{
    $validator = Validator::make($request->all(), [
        'email' => ['required', 'email'],
        'password' => ['required', 'min:6'],
        'role' => ['nullable', 'in:user,admin'] // اختيار الدور إذا أردت
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    $credentials = $validator->validated();

    // البحث عن المستخدم
    $userQuery = User::where('email', $credentials['email']);

    if (isset($credentials['role'])) {
        $userQuery->where('role', $credentials['role']);
    }

    $user = $userQuery->first();

    // تحقق من وجود المستخدم وكلمة المرور
    if (!$user || !Hash::check($credentials['password'], $user->password)) {
        return response()->json(['error' => 'Invalid credentials'], 401);
    }

    // تحقق من تفعيل البريد
    if (!$user->is_verified) {
        return response()->json(['error' => 'Email not verified'], 403);
    }

    // توليد JWT
    $token = JWTAuth::fromUser($user);

    return response()->json([
        'access_token' => $token,
        'token_type' => 'bearer',
        'expires_in' => JWTAuth::factory()->getTTL() * 60
    ]);
}

    // ========================
    // التحقق من البريد
    // ========================
public function verifyEmail(Request $request)
{
    $validator = Validator::make($request->all(), [
        'email' => 'required|email|exists:users,email',
        'code'  => 'required|string|size:6'
    ]);

    if($validator->fails()){
        return response()->json(['errors'=>$validator->errors()],422);
    }

    $user = User::where('email', $request->email)->first();

    $record = EmailVerificationCode::where('user_id', $user->id)
                ->where('used', false)
                ->where('expires_at', '>', now())
                ->first();

    if(!$record){
        return response()->json(['error'=>'Invalid or expired code'],400);
    }

    if(!Hash::check($request->code, $record->code_hash)){
        return response()->json(['error'=>'Invalid code'],400);
    }

    $record->update(['used' => true]);
    $user->update(['is_verified' => true]);

    return response()->json(['message'=>'Email verified successfully']);
}

    // ========================
    // إعادة إرسال كود التحقق
    // ========================
    public function resendVerificationCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'=>'required|email|exists:users,email'
        ]);

        if($validator->fails()){
            return response()->json(['errors'=>$validator->errors()],422);
        }

        $user = User::where('email',$request->email)->first();

        if($user->is_verified){
            return response()->json(['message'=>'Email is already verified.'],400);
        }

        // حذف أي كود قديم
        EmailVerificationCode::where('user_id',$user->id)->where('used',false)->delete();

        $code = rand(100000,999999);

        EmailVerificationCode::create([
            'user_id'=>$user->id,
            'code_hash'=>Hash::make($code),
            'expires_at'=>now()->addMinutes(10)
        ]);

        Mail::to($user->email)->send(new EmailVerificationCodeMail($code));

        return response()->json(['message'=>'Verification code resent successfully.']);
    }

    // ========================
    // طلب إعادة تعيين كلمة المرور
    // ========================
    public function requestPasswordReset(Request $request)
    {    $validator = Validator::make($request->all(), [
        'email' => 'required|email|exists:users,email',
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    $user = User::where('email', $request->email)->first();

    // حذف أي توكن غير مستخدم من قبل
    PasswordResetToken::where('user_id', $user->id)
        ->where('used', false)
        ->delete();

    // توليد كود عشوائي
    $code = rand(100000, 999999);

    PasswordResetToken::create([
        'user_id' => $user->id,
        'token' => Hash::make($code),
        'expires_at' => now()->addMinutes(15),
        'used' => false,
    ]);

    // إرسال الكود على البريد
    Mail::to($user->email)->send(new EmailVerificationCodeMail($code));

    return response()->json([
        'message' => 'Password reset code sent successfully.',
    ]);

    }

    // ========================
    // تسجيل الخروج
    // ========================
    public function logout()
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
            return response()->json(['message'=>'Successfully logged out']);
        } catch (\Exception $e) {
            return response()->json(['error'=>'Something went wrong during logout','message'=>$e->getMessage()],500);
        }
    }

    // ========================
    // تحديث التوكن
    // ========================
    public function refresh()
    {
        try {
            $token = JWTAuth::refresh(JWTAuth::getToken());
            return response()->json([
                'access_token'=>$token,
                'token_type'=>'bearer',
                'expires_in'=>JWTAuth::factory()->getTTL()*60
            ]);
        } catch (\Exception $e) {
            return response()->json(['error'=>'Token refresh failed','message'=>$e->getMessage()],500);
        }
    }


public function resetPassword(Request $request)
{
    $validator = Validator::make($request->all(), [
        'email' => 'required|email|exists:users,email',
        'code' => 'required|string|size:6',
        'new_password' => 'required|string|min:6|confirmed',
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    $user = User::where('email', $request->email)->first();

    $tokenRecord = PasswordResetToken::where('user_id', $user->id)
        ->where('used', false)
        ->where('expires_at', '>', now())
        ->first();

    if (!$tokenRecord || !Hash::check($request->code, $tokenRecord->token)) {
        return response()->json(['error' => 'Invalid or expired code'], 400);
    }

    // تحديث كلمة المرور
    $user->password = Hash::make($request->new_password);
    $user->save();

    // تمييز التوكن كمستخدم
    $tokenRecord->update(['used' => true]);

    return response()->json(['message' => 'Password has been reset successfully']);
}

public function me(Request $request)
{
    $user = JWTAuth::user();
    return response()->json([
        'id'         => $user->id,
        'full_name'  => $user->full_name,
        'email'      => $user->email,
        'phone'      => $user->phone,
        'balance'    => $user->balance,
        'role'       => $user->role,
        'created_at' => $user->created_at,
    ]);
}

}
