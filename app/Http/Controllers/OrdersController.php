<?php
namespace App\Http\Controllers;
use App\Models\Deal;
use App\Models\Order;
use App\Models\Asset;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\Wallet;

class OrdersController extends Controller
{
    // إنشاء أمر جديد (Buy/Sell)use App\Models\Deal;
   public function store(Request $request)
{
    $validator = Validator::make($request->all(), [
        'asset_id'    => 'required|exists:assets,id',
        'order_type'  => 'required|in:buy,sell',
        'lots'        => 'nullable|numeric|min:0.1', //  أقل قيمة 0.1
        'leverage'    => 'required|integer|min:1',
        'take_profit' => 'nullable|numeric',
        'stop_loss'   => 'nullable|numeric',
        'pending_order' => 'boolean',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'message' => 'حدثت أخطاء في التحقق من البيانات',
            'errors'  => $validator->errors()
        ], 422);
    }

    $validated = $validator->validated();
    $lots = $validated['lots'] ?? 0.1;

    //  جلب الأصل وسعره الحالي
    $asset = Asset::findOrFail($validated['asset_id']);
    $entryPrice = $asset->price;

    if (!$entryPrice || $entryPrice <= 0) {
        return response()->json([
            'message' => 'سعر الأصل غير متوفر حالياً، يرجى تحديث الأسعار أولاً!',
        ], 422);
    }

    // \ حساب المبلغ المطلوب كهامش (نسخة معدّلة لتكون منطقية)
    $amountUSD = ($entryPrice * $lots) / ($validated['leverage'] * 100);

    $user = Auth::user();

    //  التحقق من الرصيد
    if ($user->balance < $amountUSD) {
        return response()->json([
            'message' => "رصيدك غير كافٍ لإتمام الصفقة. المبلغ المطلوب كهامش هو: $" . number_format($amountUSD, 2)
        ], 422);
    }

    //  خصم الهامش
    $user->balance -= $amountUSD;
    $user->save();

    //  إنشاء الطلب
    $order = Order::create([
        'user_id'       => $user->id,
        'asset_id'      => $validated['asset_id'],
        'order_type'    => $validated['order_type'],
        'lots'          => $lots,
        'leverage'      => $validated['leverage'],
        'entry_price'   => $entryPrice,
        'take_profit'   => $validated['take_profit'] ?? null,
        'stop_loss'     => $validated['stop_loss'] ?? null,
        'pending_order' => $validated['pending_order'] ?? false,
        'status'        => 'pending',
    ]);

    //  إنشاء صفقة جديدة
    Deal::create([
        'order_id'    => $order->id,
        'user_id'     => $user->id,
        'asset_id'    => $validated['asset_id'],
        'side'        => $validated['order_type'],
        'lots'        => $lots,
        'entry_price' => $entryPrice,
        'close_price' => 0,
        'pnl'         => 0,
        'executed_at' => now(),
    ]);

    //  تحديث المحفظة
    $wallet = Wallet::firstOrCreate(
        [
            'user_id'      => $user->id,
            'asset_symbol' => $asset->symbol,
            'asset_type'   => $asset->category,
        ],
        ['quantity' => 0]
    );

    if ($validated['order_type'] === 'buy') {
        $wallet->quantity += $lots;
    } elseif ($validated['order_type'] === 'sell') {
        if ($wallet->quantity < $lots) {
            return response()->json([
                'message' => 'رصيدك غير كافٍ لبيع هذه الكمية.',
            ], 422);
        }
        $wallet->quantity -= $lots;
    }

    $wallet->save();

    return response()->json([
        'message' => "تم إنشاء الطلب بنجاح  تم حجز مبلغ $" . number_format($amountUSD, 2) . " من رصيدك كهامش للصفقة.",
        'order'   => $order,
        'wallet'  => $wallet,
        'balance' => $user->balance,
    ], 201);
}

    // جلب أمر واحد بالتفصيل لمستخدمه فقط
    public function show($id)
    {
        $order = Order::where('user_id', Auth::id())->with('asset')->findOrFail($id);
        $order->pnl = $this->calculatePnL($order);
        return response()->json($order);
    }

    // تحديث الحالة (إلغاء أو تنفيذ)
public function update(Request $request, $id)
{
    $validator = Validator::make($request->all(), [
        'status' => 'required|in:executed,cancelled',
    ]);
    if ($validator->fails()) {
        return response()->json([
            'message' => 'حدثت أخطاء في التحقق من البيانات',
            'errors'  => $validator->errors()
        ], 422);
    }

    $userId = auth::id(); // أو استخدم JWTAuth::parseToken()->authenticate()->id;
    $order = Order::where('user_id', $userId)->findOrFail($id);
    $order->status = $request->status;
    $order->save();

    return response()->json([
        'message' => 'تم تحديث حالة الأمر بنجاح',
        'order'   => $order,
    ]);
}

    // حذف أمر (اختياري)
    public function destroy($id)
    {
        $order = Order::where('user_id', Auth::id())->findOrFail($id);
        $order->delete();

        return response()->json(['message' => 'تم حذف الأمر بنجاح']);
    }

    // حساب الربح والخسارة
    public function calculatePnL($order)
    {
        $currentPrice = isset($order->asset->price) ? $order->asset->price : 0;
        $entryPrice = $order->entry_price ?? 0;
        $lots = $order->lots;
        $leverage = $order->leverage;

        if ($entryPrice == 0 || $currentPrice == 0) {
            return 0;
        }

        if ($order->order_type === 'buy') {
            $profitLoss = ($currentPrice - $entryPrice) * $lots * $leverage;
        } else {
            $profitLoss = ($entryPrice - $currentPrice) * $lots * $leverage;
        }
        return $profitLoss;
    }

    // إغلاق الصفقة واحتساب الربح/الخسارة وتحديث الرصيد
   public function closeOrder($id)
{
    $user = Auth::user();
    $order = Order::where('user_id', $user->id)
                  ->with('asset')
                  ->findOrFail($id);

    if ($order->status === 'executed') {
        return response()->json([
            'message' => ' تم إغلاق هذه الصفقة مسبقًا.',
        ], 400);
    }

    $currentPrice = $order->asset->price;
    $entryPrice = $order->entry_price;
    $lots = $order->lots;
    $leverage = $order->leverage;

    //  حساب الربح أو الخسارة
    if ($order->order_type === 'buy') {
        $pnl = ($currentPrice - $entryPrice) * $lots * $leverage;
    } else {
        $pnl = ($entryPrice - $currentPrice) * $lots * $leverage;
    }

    //  حساب المارجن المحجوز (الهامش)
    $margin = ($entryPrice * $lots) / $leverage;

    //  تحديث الرصيد (إرجاع المارجن + الربح/الخسارة)
    $user->balance += $margin + $pnl;
    $user->save();

    //  تحديث حالة الطلب
    $order->status = 'executed';
    $order->close_price = $currentPrice;
    $order->save();

    //  تحديث الصفقة المرتبطة
    $deal = $order->deal;
    if ($deal) {
        $deal->close_price = $currentPrice;
        $deal->pnl = $pnl;
        $deal->save();
    }

    //  تعديل الكمية في الـ Wallet
    $wallet = Wallet::where('user_id', $user->id)
                    ->where('asset_symbol', $order->asset->symbol)
                    ->first();

    if ($wallet) {
        if ($order->order_type === 'buy') {
            // تقليل الكمية عند الإغلاق
            $wallet->quantity -= $lots;
        } else {
            // إعادة الكمية عند بيعها
            $wallet->quantity += $lots;
        }
        if ($wallet->quantity < 0) $wallet->quantity = 0;
        $wallet->save();
    }

    return response()->json([
        'message' => ' تم إغلاق الصفقة بنجاح!',
        'pnl' => round($pnl, 2),
        'margin_released' => round($margin, 2),
        'balance' => round($user->balance, 2),
        'order' => $order,
    ]);
}

public function index()
{
    $user = Auth::user();

    // جلب جميع الطلبات الخاصة بالمستخدم الحالي مع معلومات الأصل
    $orders = Order::with('asset')
        ->where('user_id', $user->id)
        ->orderBy('created_at', 'desc')
        ->get();

    return response()->json([
        'orders' => $orders,
    ]);
}

}
