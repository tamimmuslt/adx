<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'user_id',
        'type',           // deposit, withdraw, trade, fee
        'currency',       // BTC, USD, USDT, XAU...
        'amount',
        'balance_after',
        'method',         // Bank transfer, Binance Wallet, Crypto, ...
        'status',         // completed, pending, failed
    ];

    // العلاقة مع المستخدم
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
