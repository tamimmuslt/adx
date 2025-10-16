<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    protected $fillable = [
        'user_id',
        'asset_symbol',   // BTC, XAU, TSLA...
        'asset_type',     // Cryptocurrency, Commodity, Stock ...
        'quantity',
        

    ];

    // العلاقة مع المستخدم
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
