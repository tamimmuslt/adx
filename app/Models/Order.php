<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'asset_id',
        'order_type',
        'lots',
        'leverage',
        'take_profit',
        'stop_loss',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function deals()
    {
        return $this->hasMany(Deal::class);
    }
}
