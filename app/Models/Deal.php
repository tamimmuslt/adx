<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Deal extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id', 
        'user_id', 
        'asset_id', 
        'side', 
        'lots',
        'entry_price',
         'close_price', 
         'pnl', 
         'executed_price',
         'executed_lots',
         'executed_at'
    ];

    public function user()   { return $this->belongsTo(User::class); }
    public function asset()  { return $this->belongsTo(Asset::class); }
    public function order()  { return $this->belongsTo(Order::class); }
}
