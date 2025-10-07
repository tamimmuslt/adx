<?php
namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
    public function deposit(Request $request)
    {
        $request->validate([
            'currency' => 'required|string',
            'amount'   => 'required|numeric|min:0.01',
            'method'   => 'nullable|string',
        ]);

        $userId = Auth::id();

        // أضف أو حدث كمية العملة بالمحفظة
        $wallet = \App\Models\Wallet::firstOrCreate(
            ['user_id' => $userId, 'asset_symbol' => strtoupper($request->currency)],
            ['asset_type' => 'Cryptocurrency']
        );
        $wallet->quantity += $request->amount;
        $wallet->save();

        // سجل حركة المعاملة
        $transaction = Transaction::create([
            'user_id'      => $userId,
            'type'         => 'deposit',
            'currency'     => strtoupper($request->currency),
            'amount'       => $request->amount,
            'balance_after'=> $wallet->quantity,
            'method'       => $request->method,
            'status'       => 'completed',
        ]);

        return response()->json(['message'=>'Deposit successful', 'transaction'=>$transaction]);
    }

    // مثال: دالة للسحب من المحفظة
    public function withdraw(Request $request)
    {
        $request->validate([
            'currency' => 'required|string',
            'amount'   => 'required|numeric|min:0.01',
            'method'   => 'nullable|string',
        ]);

        $userId = Auth::id();
        $wallet = \App\Models\Wallet::where('user_id', $userId)->where('asset_symbol', strtoupper($request->currency))->first();

        if (!$wallet || $wallet->quantity < $request->amount) {
            return response()->json(['message'=>'Insufficient balance'], 422);
        }

        $wallet->quantity -= $request->amount;
        $wallet->save();

        $transaction = Transaction::create([
            'user_id'      => $userId,
            'type'         => 'withdraw',
            'currency'     => strtoupper($request->currency),
            'amount'       => $request->amount,
            'balance_after'=> $wallet->quantity,
            'method'       => $request->method,
            'status'       => 'pending',
        ]);

        return response()->json(['message'=>'Withdraw request submitted', 'transaction'=>$transaction]);
    }
}
