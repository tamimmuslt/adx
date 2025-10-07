<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetQuote extends Model
{
    protected $fillable = [
        'asset_id',
        'buy_price',
        'sell_price',
        'timestamp',
    ];
protected $table = 'asset_quotes';

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }
}
