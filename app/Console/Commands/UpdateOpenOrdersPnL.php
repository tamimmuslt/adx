<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use Carbon\Carbon;

class UpdateOpenOrdersPnL extends Command
{
    /**
     * اسم الأمر
     */
    protected $signature = 'update:open-orders-pnl';

    /**
     * وصف الأمر
     */
    protected $description = '📈 تحديث الربح والخسارة (PnL) لجميع الصفقات المفتوحة بشكل تلقائي';

    /**
     * تنفيذ الأمر
     */
    public function handle()
    {
        $openOrders = Order::with('asset', 'deals')
            ->where('status', 'pending')
            ->get();

        if ($openOrders->isEmpty()) {
            $this->info(' لا توجد صفقات مفتوحة حالياً.');
            return;
        }
foreach ($openOrders as $order) {
    $currentPrice = $order->asset->price ?? 0;
    $entryPrice = $order->entry_price ?? 0;
    $lots = $order->lots ?? 0;
    $leverage = $order->leverage ?? 1;

    if ($entryPrice == 0 || $currentPrice == 0 || $lots == 0) {
        continue;
    }

    if ($order->order_type === 'buy') {
        $pnl = ($currentPrice - $entryPrice) * $lots * $leverage;
    } else {
        $pnl = ($entryPrice - $currentPrice) * $lots * $leverage;
    }

    // تحديث كل الصفقات المرتبطة
    foreach ($order->deals as $deal) {
        $deal->pnl = round($pnl, 2);
        $deal->updated_at = Carbon::now();
        $deal->save();
    }

    $this->info(" تم تحديث الصفقة رقم {$order->id} — PnL: " . round($pnl, 2) . " USD");
}

}
}