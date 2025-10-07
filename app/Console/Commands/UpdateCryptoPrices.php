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
use App\Models\Asset;
use App\Models\AssetPrice;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
    use App\Models\AssetQuote;

class UpdateCryptoPrices extends Command
{
    protected $signature = 'update:crypto-prices';
    protected $description = 'Fetch crypto OHLC (1m) from Binance and store in DB';

    public function handle()
    {
        $this->info("🚀 Fetching crypto klines from Binance...");

        $assets = Asset::where('category', 'crypto')->get();

        if ($assets->isEmpty()) {
            $this->error("❌ No crypto assets found in DB.");
            return 0;
        }

        foreach ($assets as $asset) {
            try {
                $symbol = str_replace('/USD', 'USDT', $asset->symbol);

                // جلب آخر شمعة (OHLC)
                $url = "https://api.binance.com/api/v3/klines?symbol={$symbol}&interval=1m&limit=1";
                $response = Http::get($url);

                if (!$response->successful() || empty($response->json()[0])) {
                    $this->error("❌ Binance klines failed for {$symbol}, using fallback tick.");
                    $this->saveOrUpdateQuote($asset, $symbol);
                    continue;
                }

                $k = $response->json()[0];
                $openTime = (int) $k[0]; // ms
                $open  = (float) $k[1];
                $high  = (float) $k[2];
                $low   = (float) $k[3];
                $close = (float) $k[4];

                // حفظ الشمعة في asset_prices
                AssetPrice::firstOrCreate(
                    ['asset_id' => $asset->id, 'open_time' => $openTime],
                    [
                        'open' => $open,
                        'high' => $high,
                        'low'  => $low,
                        'close'=> $close,
                        'timestamp' => Carbon::createFromTimestampMs($openTime),
                    ]
                );

                // تحديث عمود السعر الأخير في assets
                $asset->update(['price' => $close]);

                // حفظ أو تحديث سعر الشراء والبيع في asset_quotes
                $this->saveOrUpdateQuote($asset, $symbol);

                $this->info("✅ {$asset->symbol} processed: OHLC and quotes updated.");

                // تأخير قليل لتخفيف الضغط على API
                usleep(200000); // 200ms

            } catch (\Exception $e) {
                $this->error("❌ Exception for {$asset->symbol}: ".$e->getMessage());
            }
        }

        $this->info("🎯 All crypto prices processed.");
        return 0;
    }

    /**
     * حفظ أو تحديث سعر الشراء والبيع في asset_quotes
     */
    protected function saveOrUpdateQuote($asset, $symbol)
    {
        try {
            $url = "https://api.binance.com/api/v3/ticker/bookTicker?symbol={$symbol}";
            $r = Http::get($url);
            if ($r->successful()) {
                $d = $r->json();
                $buy = isset($d['askPrice']) ? (float)$d['askPrice'] : null;
                $sell = isset($d['bidPrice']) ? (float)$d['bidPrice'] : null;

                if ($buy !== null && $sell !== null) {
                    // تحديث السجل إذا موجود أو إنشاء سجل جديد إذا غير موجود
                    DB::table('asset_quotes')->updateOrInsert(
                        ['asset_id' => $asset->id],  // الشرط: نفس الأصل
                        [
                            'buy_price' => $buy,
                            'sell_price'=> $sell,
                            'timestamp' => now(),
                        ]
                    );  

                    // تحديث عمود السعر الأخير في assets
$asset->price = $sell;
$asset->save();

                    $this->info("ℹ️ {$asset->symbol} quote updated: buy={$buy}, sell={$sell}");
                }
            }
        } catch (\Exception $e) {
            $this->error("❌ Quote exception for {$asset->symbol}: ".$e->getMessage());
        }
    }
}
