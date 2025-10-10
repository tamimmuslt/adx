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
public function deposit(Request $request)
{
    $validator = Validator::make($request->all(), [
        'currency'  => 'required|string',
        'amount'    => 'required|numeric|min:0.01',
        'method'    => 'nullable|string',
        'details'   => 'array',
    ], [
        'currency.required' => 'يجب تحديد العملة.',
        'amount.required'   => 'يجب تحديد المبلغ.',
        'amount.numeric'    => 'المبلغ يجب أن يكون رقمًا.',
        'amount.min'        => 'المبلغ لا يمكن أن يقل عن 0.01.',
        'details.array'     => 'تفاصيل المعاملة يجب أن تكون مصفوفة.'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'message' => 'حدثت أخطاء في التحقق من البيانات',
            'errors'  => $validator->errors()
        ], 422);
    }

    $data = $validator->validated();
    $userId = Auth::id();

    $wallet = \App\Models\Wallet::firstOrCreate(
        ['user_id' => $userId, 'asset_symbol' => strtoupper($data['currency'])],
        ['asset_type' => 'Cryptocurrency']
    );
    $wallet->quantity += $data['amount'];
    $wallet->save();

    $transaction = \App\Models\Transaction::create([
        'user_id'       => $userId,
        'type'          => 'deposit',
        'currency'      => strtoupper($data['currency']),
        'amount'        => $data['amount'],
        'balance_after' => $wallet->quantity,
        'method'        => $data['method'] ?? null,
        'status'        => 'completed',
        'details'       => json_encode($data['details'] ?? []),
    ]);

    return response()->json(['message'=>'Deposit successful', 'transaction'=>$transaction]);
}

// -------------------- WITHDRAW --------------------
public function withdraw(Request $request)
{
    $validator = Validator::make($request->all(), [
        'currency'  => 'required|string',
        'amount'    => 'required|numeric|min:0.01',
        'method'    => 'nullable|string',
        'details'   => 'array',
    ], [
        'currency.required' => 'يجب تحديد العملة.',
        'amount.required'   => 'يجب تحديد المبلغ.',
        'amount.numeric'    => 'المبلغ يجب أن يكون رقمًا.',
        'amount.min'        => 'المبلغ لا يمكن أن يقل عن 0.01.',
        'details.array'     => 'تفاصيل المعاملة يجب أن تكون مصفوفة.'
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
        'details'       => json_encode($data['details'] ?? []),
    ]);

    return response()->json(['message'=>'Withdraw request submitted', 'transaction'=>$transaction]);
}

}