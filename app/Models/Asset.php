<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'symbol', 'category','price'];

    public function prices()
    {
        return $this->hasMany(AssetPrice::class);
    }

    public function latestPrice()
    {
        return $this->hasOne(AssetPrice::class)->latestOfMany('open_time');
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function quotes()
    {
        return $this->hasMany(AssetQuote::class);
    }

      public function deals()
    {
        return $this->hasMany(Deal::class);
    }
}
