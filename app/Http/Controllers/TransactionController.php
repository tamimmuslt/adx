<?php
namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
class TransactionController extends Controller
{
    // إرجاع سجل كل المعاملات المالية للمستخدم
    public function index()
    {
        $transactions = Transaction::where('user_id', Auth::id())->orderBy('created_at', 'desc')->get();

        $result = $transactions->map(function ($tx) {
            return [
                'type'      => ucfirst($tx->type),
                'currency'  => $tx->currency,
                'amount'    => $tx->amount,
                'method'    => $tx->method,
                'status'    => ucfirst($tx->status),
                'created_at'=> $tx->created_at->format('Y-m-d H:i:s'),
            ];
        });

        return response()->json($result);
    }

    // مثال: دالة لإضافة إيداع للمحفظة

// -------------------- DEPOSIT --------------------
// public function deposit(Request $request)
// {
//     $validator = Validator::make($request->all(), [
//         'currency'  => 'required|string',
//         'amount'    => 'required|numeric|min:0.01',
//         'method'    => 'required|string',
//         'details'   => 'array',
//     ],
//     [
//             'method.required' => 'يجب اختيار طريقة الدفع.',
//         ]);

//     if ($validator->fails()) {
//         return response()->json([
//             'message' => 'حدثت أخطاء في التحقق من البيانات',
//             'errors'  => $validator->errors()
//         ], 422);
//     }

//     $data = $validator->validated();
//     $user = Auth::user();

//     //  تحديث المحفظة
//     $wallet = \App\Models\Wallet::firstOrCreate(
//         ['user_id' => $user->id, 'asset_symbol' => strtoupper($data['currency'])],
//         ['asset_type' => 'Cryptocurrency']
//     );
//     $wallet->quantity += $data['amount'];
//     $wallet->save();

//     //  تحديث رصيد المستخدم العام
//     $user->balance += $data['amount'];
//     $user->save();

//     //  تسجيل العملية
//     $transaction = \App\Models\Transaction::create([
//         'user_id'       => $user->id,
//         'type'          => 'deposit',
//         'currency'      => strtoupper($data['currency']),
//         'amount'        => $data['amount'],
//         'balance_after' => $user->balance, // بدلاً من wallet->quantity
//         'method'        => $data['method'],
//         'status'        => 'completed',
//         'details'       => json_encode($data['details'] ?? []),
//     ]);

//     return response()->json([
//         'message' => ' تم الإيداع بنجاح',
//         'transaction' => $transaction,
//         'new_balance' => $user->balance
//     ]);
// }

// // -------------------- WITHDRAW --------------------
// public function withdraw(Request $request)
// {
//     $validator = Validator::make($request->all(), [
//         'currency'  => 'required|string',
//         'amount'    => 'required|numeric|min:0.01',
//         'method'    => 'required|string',//تعديل بعد الاختبار 
//         'details'   => 'array',
//     ], [
//         'currency.required' => 'يجب تحديد العملة.',
//         'amount.required'   => 'يجب تحديد المبلغ.',
//         'amount.numeric'    => 'المبلغ يجب أن يكون رقمًا.',
//         'amount.min'        => 'المبلغ لا يمكن أن يقل عن 0.01.',
//         'method.required'   => 'يجب اختيار طريقة الدفع.',
//         'details.array'     => 'تفاصيل المعاملة يجب أن تكون مصفوفة.'
//     ]);

//     if ($validator->fails()) {
//         return response()->json([
//             'message' => 'حدثت أخطاء في التحقق من البيانات',
//             'errors'  => $validator->errors()
//         ], 422);
//     }

//     $data = $validator->validated();
//     $userId = Auth::id();

//     $wallet = \App\Models\Wallet::where('user_id', $userId)
//         ->where('asset_symbol', strtoupper($data['currency']))
//         ->first();

//     if (!$wallet || $wallet->quantity < $data['amount']) {
//         return response()->json(['message'=>'رصيدك غير كافٍ'], 422);
//     }

//     $wallet->quantity -= $data['amount'];
//     $wallet->save();

//     $transaction = \App\Models\Transaction::create([
//         'user_id'       => $userId,
//         'type'          => 'withdraw',
//         'currency'      => strtoupper($data['currency']),
//         'amount'        => $data['amount'],
//         'balance_after' => $wallet->quantity,
//         'method'        => $data['method'] ?? null,
//         'status'        => 'pending',
//         'details'       => json_encode($data['details'] ?? []),
//     ]);

//     return response()->json(['message'=>' تم ارسال طلب السحب بانتظار موافقة الادارة ', 'transaction'=>$transaction]);
// }
public function deposit(Request $request)
{
    $baseRules = [
        'currency'  => 'required|string',
        'amount'    => 'required|numeric|min:0.01',
        'method'    => 'required|string',
        'details'   => 'array',
    ];

    // تحقق خاص حسب method
    $detailsRules = [];
    switch ($request->input('method')) {
        case 'Bank Transfer':
            $detailsRules = [
                'details.bank_name'         => 'required|string',
                'details.account_holder'    => 'required|string',
                'details.account_number'    => 'required|string',
                'details.iban'              => 'required|string',
                'details.swift'             => 'required|string',
            ];
            break;
        case 'Digital Currencies':
            $detailsRules = [
                'details.wallet'        => 'required|string',
                'details.wallet_address'=> 'required|string',
            ];
            break;
        case 'Credit Card':
            $detailsRules = [
                'details.card_number'     => 'required|string',
                'details.expiration_date' => 'required|string',
                'details.cardholder_name' => 'required|string',
                'details.cvv'             => 'required|string',
            ];
            break;
        case 'Other':
            $detailsRules = [
                'details.notes'   => 'nullable|string',
            ];
            break;
    }

    $validator = Validator::make($request->all(), array_merge($baseRules, $detailsRules), [
        'method.required' => 'يجب اختيار طريقة الدفع.',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'message' => 'حدثت أخطاء في التحقق من البيانات',
            'errors'  => $validator->errors()
        ], 422);
    }

    $data = $validator->validated();
    $user = Auth::user();

    $wallet = \App\Models\Wallet::firstOrCreate(
        ['user_id' => $user->id, 'asset_symbol' => strtoupper($data['currency'])],
        ['asset_type' => 'Cryptocurrency']
    );
    $wallet->quantity += $data['amount'];
    $wallet->save();

    $user->balance += $data['amount'];
    $user->save();

    $transaction = \App\Models\Transaction::create([
        'user_id'       => $user->id,
        'type'          => 'deposit',
        'currency'      => strtoupper($data['currency']),
        'amount'        => $data['amount'],
        'balance_after' => $user->balance,
        'method'        => $data['method'],
        'status'        => 'completed',
        'details'       => json_encode($data['details']),
    ]);

    return response()->json([
        'message' => 'تم الإيداع بنجاح',
        'transaction' => $transaction,
        'new_balance' => $user->balance
    ]);
}

// -------------------- WITHDRAW --------------------
public function withdraw(Request $request)
{
    $baseRules = [
        'currency'  => 'required|string',
        'amount'    => 'required|numeric|min:0.01',
        'method'    => 'required|string',
        'details'   => 'array',
    ];
    $detailsRules = [];
    switch ($request->input('method')) {
        case 'Bank Transfer':
            $detailsRules = [
                'details.bank_name'         => 'required|string',
                'details.account_holder'    => 'required|string',
                'details.transaction_id'    => 'required|string',
            ];
            break;
        case 'Digital Currencies':
            $detailsRules = [
                'details.currency_type'     => 'required|string',
                'details.wallet_address'    => 'required|string',
                'details.transaction_id'    => 'required|string',
            ];
            break;
        case 'Credit Card':
            $detailsRules = [
                'details.card_number'     => 'required|string',
                'details.expiration_date' => 'required|string',
                'details.cardholder_name' => 'required|string',
                'details.cvv'             => 'required|string',
            ];
            break;
        case 'Other':
            $detailsRules = [
                'details.notes'   => 'nullable|string',
            ];
            break;
    }
    $validator = Validator::make($request->all(), array_merge($baseRules, $detailsRules), [
        'method.required' => 'يجب اختيار طريقة الدفع.',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'message' => 'حدثت أخطاء في التحقق من البيانات',
            'errors'  => $validator->errors()
        ], 422);
    }

    $data = $validator->validated();
    $userId = Auth::id();

    $wallet = \App\Models\Wallet::where('user_id', $userId)
        ->where('asset_symbol', strtoupper($data['currency']))
        ->first();

    if (!$wallet || $wallet->quantity < $data['amount']) {
        return response()->json(['message'=>'رصيدك غير كافٍ'], 422);
    }

    $wallet->quantity -= $data['amount'];
    $wallet->save();

    $transaction = \App\Models\Transaction::create([
        'user_id'       => $userId,
        'type'          => 'withdraw',
        'currency'      => strtoupper($data['currency']),
        'amount'        => $data['amount'],
        'balance_after' => $wallet->quantity,
        'method'        => $data['method'] ?? null,
        'status'        => 'pending',
        'details'       => json_encode($data['details']),
    ]);

    return response()->json(['message'=>'تم ارسال طلب السحب بانتظار موافقة الادارة ', 'transaction'=>$transaction]);
}

}