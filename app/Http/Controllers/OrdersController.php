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
        'lots'        => 'required|integer|min:1',
        'leverage'    => 'required|integer|min:1',
        'take_profit' => 'required|numeric',
        'stop_loss'   => 'required|numeric',
    ], [
        'asset_id.required'   => 'يجب تحديد الأصل (Asset).',
        'asset_id.exists'     => 'الأصل المحدد غير موجود.',
        'order_type.required' => 'يجب تحديد نوع العملية (شراء أو بيع).',
        'order_type.in'       => 'نوع العملية يجب أن يكون buy أو sell فقط.',
        'lots.required'       => 'يجب تحديد عدد اللوتات.',
        'lots.integer'        => 'عدد اللوتات يجب أن يكون رقمًا صحيحًا.',
        'lots.min'            => 'عدد اللوتات يجب أن تكون على الأقل 1.',
        'leverage.required'   => 'يجب تحديد الرافعة المالية.',
        'leverage.integer'    => 'الرافعة يجب أن تكون رقمًا صحيحًا.',
        'leverage.min'        => 'الرافعة يجب أن تكون على الأقل 1.',
        'take_profit.numeric' => 'قيمة جني الأرباح يجب أن تكون رقمًا.',
        'stop_loss.numeric'   => 'قيمة وقف الخسارة يجب أن تكون رقمًا.',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'message' => 'حدثت أخطاء في التحقق من البيانات',
            'errors'  => $validator->errors()
        ], 422);
    }

    $validated = $validator->validated();

    // جلب الأصل والسعر الحالي
    $asset = Asset::findOrFail($validated['asset_id']);
    $entryPrice = $asset->price;

    if (!$entryPrice || $entryPrice <= 0) {
        return response()->json([
            'message' => 'سعر الأصل غير متوفر حالياً، يرجى تحديث الأسعار أولاً!',
        ], 422);
    }

    // إنشاء الطلب
    $order = Order::create([
        'user_id'     => Auth::id(),
        'asset_id'    => $validated['asset_id'],
        'order_type'  => $validated['order_type'],
        'lots'        => $validated['lots'],
        'leverage'    => $validated['leverage'],
        'entry_price' => $entryPrice,
        'take_profit' => $validated['take_profit'] ?? null,
        'stop_loss'   => $validated['stop_loss'] ?? null,
        'status'      => 'pending',
    ]);

    // إنشاء الصفقة
    Deal::create([
        'order_id'    => $order->id,
        'user_id'     => Auth::id(),
        'asset_id'    => $validated['asset_id'],
        'side'        => $validated['order_type'],
        'lots'        => $validated['lots'],
        'entry_price' => $entryPrice,
        'close_price' => null,
        'pnl'         => 0,
        'executed_at' => now(),
    ]);

    // تحديث أو إنشاء المحفظة
    $wallet = Wallet::firstOrCreate([
        'user_id'      => Auth::id(),
        'asset_symbol' => $asset->symbol,
        'asset_type'   => $asset->category,
    ], [
        'quantity' => 0,
    ]);

    // تعديل كمية الأصل في المحفظة حسب نوع العملية
    if ($validated['order_type'] === 'buy') {
        $wallet->quantity += $validated['lots'];
    } elseif ($validated['order_type'] === 'sell') {
        if ($wallet->quantity < $validated['lots']) {
            return response()->json([
                'message' => 'رصيدك غير كافٍ لبيع هذه الكمية.',
            ], 422);
        }
        $wallet->quantity -= $validated['lots'];
    }

    $wallet->save();

    return response()->json([
        'message' => ' تم إنشاء الطلب وتحديث المحفظة بنجاح',
        'order'   => $order,
        'wallet'  => $wallet,
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
        $order = Order::where('user_id', Auth::id())->with('asset')->findOrFail($id);
        $pnl = $this->calculatePnL($order);

        // التأكد من وجود المستخدم في order
        $user = $order->user ?? User::find($order->user_id);

        if ($order->status !== 'executed') {
            $user->balance += $pnl;
            $user->save();
            $order->status = 'executed';
            $order->save();
        }

        return response()->json([
            'order' => $order,
            'balance' => $user->balance,
        ]);
    }
}
