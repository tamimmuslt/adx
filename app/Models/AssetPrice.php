<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class AssetPrice extends Model
{
    protected $fillable = [
        'asset_id','open','high','low','close','open_time','timestamp'
    ];

    protected $casts = [
        'open' => 'float',
        'high' => 'float',
        'low' => 'float',
        'close' => 'float',
        'open_time' => 'integer',
        'timestamp' => 'datetime',
    ];

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }
}
