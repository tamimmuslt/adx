<?php
namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Asset;
use App\Models\AssetPrice;
use GuzzleHttp\Client;
use Carbon\Carbon;

class UpdateAssetPrice implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $asset; // هنا نخزن العملة

    /**
     * Create a new job instance.
     */
    public function __construct(Asset $asset)
    {
        $this->asset = $asset;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $client = new Client();
        $idMap = [
            'BTC/USD'=>'bitcoin',
            'ETH/USD'=>'ethereum',
            'XRP/USD'=>'ripple',
            'LTC/USD'=>'litecoin',
            'ADA/USD'=>'cardano',
            'DOGE/USD'=>'dogecoin',
            'BNB/USD'=>'binancecoin',
            'SOL/USD'=>'solana',
            'DOT/USD'=>'polkadot',
            'SHIB/USD'=>'shiba-inu',
        ];

        $asset = $this->asset;

        if(!isset($idMap[$asset->symbol])){
            return;
        }

        // تحقق من آخر سعر
        $lastPrice = AssetPrice::where('asset_id', $asset->id)
                        ->orderBy('timestamp', 'desc')
                        ->first();

        if ($lastPrice && Carbon::parse($lastPrice->timestamp)->diffInSeconds(now()) < 5) {
            return; // تجاوز آخر تحديث أقل من 5 ثواني
        }

        try {
            $response = $client->get('https://api.coingecko.com/api/v3/simple/price', [
                'query' => [
                    'ids' => $idMap[$asset->symbol],
                    'vs_currencies' => 'usd'
                ]
            ]);

            $data = json_decode($response->getBody(), true);
            $price = $data[$idMap[$asset->symbol]]['usd'] ?? null;

            if ($price) {
                AssetPrice::create([
                    'asset_id' => $asset->id,
                    'buy_price' => $price,
                    'sell_price' => $price,
                    'timestamp' => now(),
                ]);
            }
        } catch (\Exception $e) {
            logger("Failed to fetch {$asset->symbol}: ".$e->getMessage());
        }
    }
}
