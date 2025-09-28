<?php

// namespace App\Console\Commands;

// use Illuminate\Console\Command;
// use App\Models\Asset;
// use App\Models\AssetPrice;
// use GuzzleHttp\Client;
// use Carbon\Carbon;

// class FetchInvestingPrices extends Command
// {
//     protected $signature = 'fetch:asset-prices';
//     protected $description = 'Fetch latest prices for all assets safely avoiding rate limits';

//     private $client;
//     private $ALPHA_VANTAGE_KEY = '0IWAH37NLL8FESZT';

//     public function __construct()
//     {
//         parent::__construct();
//         $this->client = new Client();
//     }

//     public function handle()
//     {
//         $assets = Asset::all();

//         // العملات المشفرة
//         $cryptoAssets = $assets->where('category', 'crypto')->chunk(2); // دفعتين
//         foreach ($cryptoAssets as $chunk) {
//             foreach ($chunk as $asset) {
//                 try {
//                     $this->fetchCryptoPrice($asset);
//                 } catch (\Exception $e) {
//                     $this->error("Failed to fetch {$asset->symbol}: " . $e->getMessage());
//                 }
//                 sleep(5); // تأخير بين كل عملة
//             }
//             sleep(10); // تأخير بين كل chunk
//         }

//         // الأسهم، السلع، المؤشرات
//         foreach ($assets->where('category', '!=', 'crypto') as $asset) {
//             try {
//                 $this->fetchAlphaPrice($asset);
//             } catch (\Exception $e) {
//                 $this->error("Failed to fetch {$asset->symbol}: " . $e->getMessage());
//             }
//             sleep(5);
//         }

//         $this->info('Asset prices updated successfully.');
//     }

//     private function fetchCryptoPrice($asset)
//     {
//         $idMap = [
//             'BTC/USD'=>'bitcoin',
//             'ETH/USD'=>'ethereum',
//             'XRP/USD'=>'ripple',
//             'LTC/USD'=>'litecoin',
//             'ADA/USD'=>'cardano',
//             'DOGE/USD'=>'dogecoin',
//             'BNB/USD'=>'binancecoin',
//             'SOL/USD'=>'solana',
//             'DOT/USD'=>'polkadot',
//             'SHIB/USD'=>'shiba-inu',
//         ];

//         if (!isset($idMap[$asset->symbol])) {
//             $this->warn("No ID mapping for {$asset->symbol}");
//             return;
//         }

//         // تحقق من آخر سعر لتجنب التحديث المتكرر
//         $lastPrice = AssetPrice::where('asset_id', $asset->id)
//                         ->orderBy('timestamp', 'desc')
//                         ->first();
//         if ($lastPrice && Carbon::parse($lastPrice->timestamp)->diffInSeconds(now()) < 300) {
//             return; // السعر حديث
//         }

//         try {
//             $response = $this->client->get('https://api.coingecko.com/api/v3/simple/price', [
//                 'query' => [
//                     'ids' => $idMap[$asset->symbol],
//                     'vs_currencies' => 'usd'
//                 ]
//             ]);

//             $data = json_decode($response->getBody(), true);
//             $price = $data[$idMap[$asset->symbol]]['usd'] ?? null;

//             if ($price) {
//                 AssetPrice::create([
//                     'asset_id' => $asset->id,
//                     'buy_price' => $price,
//                     'sell_price' => $price,
//                     'timestamp' => now(),
//                 ]);
//             }
//         } catch (\GuzzleHttp\Exception\ClientException $e) {
//             $this->warn("CoinGecko rate limit hit for {$asset->symbol}, skipping.");
//         }
//     }

//     private function fetchAlphaPrice($asset)
//     {
//         $symbolMap = [
//             // الأسهم
//             'AAPL/USD'=>'AAPL',
//             'MSFT/USD'=>'MSFT',
//             'AMZN/USD'=>'AMZN',
//             'TSLA/USD'=>'TSLA',
//             'GOOGL/USD'=>'GOOGL',
//             'META/USD'=>'META',
//             'NVDA/USD'=>'NVDA',
//             'INTC/USD'=>'INTC',
//             'KO/USD'=>'KO',
//             'PEP/USD'=>'PEP',

//             // المؤشرات
//             'SPX/USD'=>'SPX',
//             'DJI/USD'=>'DJI',
//             'NDX/USD'=>'NDX',
//             'UKX/USD'=>'FTSE',
//             'DAX/USD'=>'DAX',
//             'N225/USD'=>'NIKKEI',
//             'HSI/USD'=>'HANGSENG',
//             'CAC/USD'=>'CAC40',

//             // السلع
//             'XAU/USD'=>'XAU',
//             'XAG/USD'=>'XAG',
//             'XPT/USD'=>'XPT',
//             'XPD/USD'=>'XPD',
//             'CL/USD' => 'CL',      // Oil WTI
//             'BRN/USD' => 'BZ',     // Oil Brent
//             'NG/USD' => 'NG',      // Natural Gas
//             'HG/USD' => 'HG',      // Copper
//             'ZC/USD' => 'ZC',      // Corn
//             'ZW/USD' => 'ZW',      // Wheat
//         ];

//         if (!isset($symbolMap[$asset->symbol])) {
//             $this->warn("No symbol mapping for {$asset->symbol}");
//             return;
//         }

//         // تحقق من آخر سعر
//         $lastPrice = AssetPrice::where('asset_id', $asset->id)
//                         ->orderBy('timestamp', 'desc')
//                         ->first();
//         if ($lastPrice && Carbon::parse($lastPrice->timestamp)->diffInSeconds(now()) < 300) {
//             return; // السعر حديث
//         }

//         try {
//             $response = $this->client->get('https://www.alphavantage.co/query', [
//                 'query' => [
//                     'function' => 'GLOBAL_QUOTE',
//                     'symbol' => $symbolMap[$asset->symbol],
//                     'apikey' => $this->ALPHA_VANTAGE_KEY,
//                 ]
//             ]);

//             $data = json_decode($response->getBody(), true);
//             $price = $data['Global Quote']['05. price'] ?? null;

//             if ($price) {
//                 AssetPrice::create([
//                     'asset_id' => $asset->id,
//                     'buy_price' => $price,
//                     'sell_price' => $price,
//                     'timestamp' => now(),
//                 ]);
//             }
//         } catch (\GuzzleHttp\Exception\ClientException $e) {
//             $this->warn("Alpha Vantage request failed for {$asset->symbol}, skipping.");
//         }
//     }
// }


namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AssetPrice;
use App\Models\Asset;
use WebSocket\Client;
use Carbon\Carbon;

class UpdateCryptoPrices extends Command
{
    protected $signature = 'update:crypto-prices';
    protected $description = 'Update crypto prices every second using WebSocket';

    public function handle()
    {
        // WebSocket لـ ticker
        $client = new Client("wss://stream.binance.com:9443/ws/btcusdt@ticker");

        $this->info("Started streaming BTC/USD prices...");

        while (true) {
            try {
                $message = $client->receive();
                $data = json_decode($message, true);

                // سعر شراء وبيع
                $buyPrice  = $data['b'] ?? null; // best bid
                $sellPrice = $data['a'] ?? null; // best ask

                if ($buyPrice && $sellPrice) {
                    $asset = Asset::where('symbol', 'BTC/USD')->first();

                    if ($asset) {
                        // تحديث بدل إضافة سجل جديد
                        AssetPrice::updateOrCreate(
                            ['asset_id' => $asset->id],
                            [
                                'buy_price'  => $buyPrice,
                                'sell_price' => $sellPrice,
                                'timestamp'  => Carbon::now(),
                            ]
                        );

                        $this->info("BTC/USD updated → Buy: $buyPrice | Sell: $sellPrice");
                    } else {
                        $this->warn("Asset BTC/USD not found in DB!");
                    }
                }

            } catch (\Exception $e) {
                $this->error("Error: " . $e->getMessage());
            }

            sleep(1);
        }
    }
}
