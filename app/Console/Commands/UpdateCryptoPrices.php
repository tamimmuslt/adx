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


// namespace App\Console\Commands;

// use Illuminate\Console\Command;
// use App\Models\Asset;
// use App\Models\AssetPrice;
// use Illuminate\Support\Facades\Http;
// use Carbon\Carbon;
// use Illuminate\Support\Facades\DB;

// class UpdateCryptoPrices extends Command
// {
//     protected $signature = 'update:crypto-prices';
//     protected $description = 'Fetch crypto prices from CoinGecko and store in DB';

//     public function handle()
//     {
//         $this->info("🚀 Fetching crypto prices from CoinGecko...");

//         $assets = Asset::where('category', 'crypto')->get();

//         if ($assets->isEmpty()) {
//             $this->error("❌ No crypto assets found in DB.");
//             return 0;
//         }

//         foreach ($assets as $asset) {
//             try {
//                 // استخراج اسم العملة من الرمز
//                 $coinId = strtolower(explode('/', $asset->symbol)[0]); // BTC/USD -> btc

//                 // طلب من CoinGecko
//                 $url = "https://api.coingecko.com/api/v3/simple/price?ids={$coinId}&vs_currencies=usd";
//                 $response = Http::timeout(10)->get($url);

//                 if ($response->failed() || !isset($response->json()[$coinId]['usd'])) {
//                     $this->warn("⚠️ Failed to get current price for {$asset->symbol}");
//                     continue;
//                 }

//                 $price = (float) $response->json()[$coinId]['usd'];

//                 // حفظ السعر في جدول asset_prices
//                 AssetPrice::create([
//                     'asset_id'  => $asset->id,
//                     'open'      => $price,
//                     'high'      => $price,
//                     'low'       => $price,
//                     'close'     => $price,
//                     'timestamp' => now(),
//                 ]);

//                 // تحديث سعر الأصل الرئيسي
//                 $asset->update(['price' => $price]);

//                 // تحديث أو إنشاء سجل في quotes
//                 DB::table('asset_quotes')->updateOrInsert(
//                     ['asset_id' => $asset->id],
//                     [
//                         'buy_price' => $price,
//                         'sell_price' => $price,
//                         'timestamp' => now(),
//                     ]
//                 );

//                 $this->info("✅ {$asset->symbol}: {$price} USD");

//                 // تأخير خفيف لتجنب حظر CoinGecko
//                 sleep(1);

//             } catch (\Exception $e) {
//                 $this->error("❌ Exception for {$asset->symbol}: " . $e->getMessage());
//             }
//         }

//         $this->info("🎯 All crypto prices updated successfully!");
//         return 0;
//     }
// }



// namespace App\Console\Commands;

// use Illuminate\Console\Command;
// use Illuminate\Support\Facades\Http;
// use App\Models\Asset;

// class UpdateCryptoPrices extends Command
// {
//     protected $signature = 'update:crypto-price';
//     protected $description = 'Fetch crypto prices from CoinGecko and update database';

//     public function handle()
//     {
//         $this->info("🚀 Fetching crypto prices from CoinGecko...");

//         // خريطة تحويل الرموز إلى أسماء CoinGecko الرسمية
//         $map = [
//             'BTC' => 'bitcoin',
//             'ETH' => 'ethereum',
//             'XRP' => 'ripple',
//             'LTC' => 'litecoin',
//             'ADA' => 'cardano',
//             'DOGE' => 'dogecoin',
//             'BNB' => 'binancecoin',
//             'SOL' => 'solana',
//             'DOT' => 'polkadot',
//             'SHIB' => 'shiba-inu'
//         ];

//         $assets = Asset::where('category', 'crypto')->get();

//         foreach ($assets as $asset) {
//             try {
//                 // استخرج الرمز من مثل "BTC/USD"
//                 $symbol = strtoupper(explode('/', $asset->symbol)[0]);

//                 // تحقق من أن العملة موجودة بالخريطة
//                 if (!isset($map[$symbol])) {
//                     $this->error("⚠️ Unknown coin symbol: {$symbol}");
//                     continue;
//                 }

//                 $coingecko_id = $map[$symbol];

//                 // طلب CoinGecko
//                 $response = Http::get('https://api.coingecko.com/api/v3/simple/price', [
//                     'ids' => $coingecko_id,
//                     'vs_currencies' => 'usd',
//                     'include_24hr_high' => 'true',
//                     'include_24hr_low' => 'true',
//                     'include_24hr_change' => 'true'
//                 ]);

//                 if ($response->successful()) {
//                     $data = $response->json();

//                     if (isset($data[$coingecko_id])) {
//                         $info = $data[$coingecko_id];
//                         $price = $info['usd'] ?? 0;
//                         $high = $info['usd_24h_high'] ?? 0;
//                         $low = $info['usd_24h_low'] ?? 0;

//                         $asset->update([
//                             'price' => $price,
//                             'high' => $high,
//                             'low' => $low,
//                             'close' => $price,
//                         ]);

//                         $this->info("✅ Updated {$asset->name}: \${$price}");
//                     } else {
//                         $this->error("⚠️ No data for {$asset->name}");
//                     }
//                 } else {
//                     $this->error("❌ Failed request for {$asset->name}");
//                 }
//             } catch (\Exception $e) {
//                 $this->error("⚠️ Error for {$asset->name}: " . $e->getMessage());
//             }
//         }

//         $this->info("🏁 Prices updated successfully!");
//     }
// }


namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Asset;
use App\Models\AssetPrice;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class UpdateCryptoPrices extends Command
{
    protected $signature = 'update:crypto-price';
    protected $description = 'Fetch crypto and metal prices from CoinGecko and store in DB';

    public function handle()
    {
        $this->info("🚀 Starting price updater (every 10 seconds)...");

        // خريطة العملات الرقمية (CoinGecko IDs)
        $cryptoMap = [
            'BTC/USD'  => 'bitcoin',
            'ETH/USD'  => 'ethereum',
            'XRP/USD'  => 'ripple',
            'LTC/USD'  => 'litecoin',
            'ADA/USD'  => 'cardano',
            'DOGE/USD' => 'dogecoin',
            'BNB/USD'  => 'binancecoin',
            'SOL/USD'  => 'solana',
            'DOT/USD'  => 'polkadot',
            'SHIB/USD' => 'shiba-inu',
        ];

        // الذهب والفضة (metals)
        $metalMap = [
            'XAU/USD' => 'gold',
            'XAG/USD' => 'silver',
        ];

        while (true) {
            try {
                // ====== العملات الرقمية ======
                $cryptoIds = implode(',', $cryptoMap);
                $cryptoResponse = Http::timeout(15)->get(
                    "https://api.coingecko.com/api/v3/simple/price?ids={$cryptoIds}&vs_currencies=usd&include_24hr_high=true&include_24hr_low=true"
                );

                if ($cryptoResponse->successful()) {
                    $cryptoData = $cryptoResponse->json();

                    $cryptoAssets = Asset::whereIn('symbol', array_keys($cryptoMap))->get();
                    foreach ($cryptoAssets as $asset) {
                        $coinId = $cryptoMap[$asset->symbol];

                        if (!isset($cryptoData[$coinId]['usd'])) {
                            $this->warn("⚠️ Failed to get price for {$asset->symbol}");
                            continue;
                        }

                        $price = (float)$cryptoData[$coinId]['usd'];
                        $high  = isset($cryptoData[$coinId]['usd_24h_high']) ? (float)$cryptoData[$coinId]['usd_24h_high'] : $price;
                        $low   = isset($cryptoData[$coinId]['usd_24h_low']) ? (float)$cryptoData[$coinId]['usd_24h_low'] : $price;

                        // حفظ السعر
                        AssetPrice::create([
                            'asset_id'  => $asset->id,
                            'open'      => $price,
                            'high'      => $high,
                            'low'       => $low,
                            'close'     => $price,
                            'timestamp' => now(),
                            'open_time' => now()->timestamp * 1000, // UNIX ms
                        ]);

                        // تحديث سعر الأصل
                        $asset->update(['price' => $price]);

                        // تحديث أو إنشاء سجل في quotes
                        DB::table('asset_quotes')->updateOrInsert(
                            ['asset_id' => $asset->id],
                            ['buy_price' => $price, 'sell_price' => $price, 'timestamp' => now()]
                        );

                        $this->info("✅ {$asset->symbol}: {$price} USD (high: {$high}, low: {$low})");
                    }
                } else {
                    $this->warn("⚠️ Failed to fetch crypto prices from CoinGecko.");
                }

                // ====== المعادن ======
                foreach ($metalMap as $symbol => $metalId) {
                    try {
                        $metalResponse = Http::timeout(10)->get(
                            "https://www.metals-api.com/api/latest?access_key=YOUR_METALS_API_KEY&base=USD&symbols={$metalId}"
                        );
                        // مثال: response structure: ['rates' => ['XAU' => 1925.5]]
                        if ($metalResponse->successful() && isset($metalResponse['rates'][$metalId])) {
                            $price = (float)$metalResponse['rates'][$metalId];

                            $asset = Asset::where('symbol', $symbol)->first();
                            if (!$asset) continue;

                            AssetPrice::create([
                                'asset_id'  => $asset->id,
                                'open'      => $price,
                                'high'      => $price,
                                'low'       => $price,
                                'close'     => $price,
                                'timestamp' => now(),
                                'open_time' => now()->timestamp * 1000,
                            ]);

                            $asset->update(['price' => $price]);
                            DB::table('asset_quotes')->updateOrInsert(
                                ['asset_id' => $asset->id],
                                ['buy_price' => $price, 'sell_price' => $price, 'timestamp' => now()]
                            );

                            $this->info("✅ {$symbol}: {$price} USD");
                        } else {
                            $this->warn("⚠️ Failed to fetch price for {$symbol}");
                        }
                    } catch (\Exception $e) {
                        $this->warn("⚠️ Exception for {$symbol}: {$e->getMessage()}");
                    }
                }

            } catch (\Exception $e) {
                $this->error("❌ Exception: " . $e->getMessage());
            }

            $this->info("🎯 All crypto and metal prices updated! Waiting 10 seconds...\n");
            sleep(10);
        }
    }
}
