<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'symbol', 'category'];

    // علاقة الأصول بالأسعار
    public function prices()
    {
        return $this->hasMany(AssetPrice::class);
    }

    // علاقة الأصول بالصفقات
    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
