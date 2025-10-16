<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Asset;
use App\Models\AssetPrice;
use Illuminate\Support\Facades\Http;

class UpdateMetalPrices extends Command
{
    protected $signature = 'update:metal-price';
    protected $description = 'Fetch metal prices (XAU, XAG) from GoldAPI and store in DB';

    public function handle()
    {
        $this->info("🚀 Starting metal price updater (every 10 seconds)...");

        $coinMap = [
            'XAU/USD' => 'XAU',
            'XAG/USD' => 'XAG',
        ];

        $assets = Asset::whereIn('symbol', array_keys($coinMap))->get();

        if ($assets->isEmpty()) {
            $this->error("❌ No metal assets found in DB.");
            return 0;
        }

        while (true) {
            foreach ($assets as $asset) {
                try {
                    $symbol = $coinMap[$asset->symbol];
                    $url = "https://www.goldapi.io/api/{$symbol}/USD";

                    $response = Http::withHeaders([
                        'x-access-token' => env('GOLD_API_KEY'),
                        'Content-Type' => 'application/json'
                    ])->get($url);

                    if ($response->failed()) {
                        $this->warn("⚠️ Failed to fetch price for {$asset->symbol}");
                        continue;
                    }

                    $data = $response->json();
                    $price = isset($data['price']) ? (float) $data['price'] : null;

                    if (!$price) {
                        $this->warn("⚠️ No price data for {$asset->symbol}");
                        continue;
                    }

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

                    $this->info("✅ {$asset->symbol}: {$price} USD");

                } catch (\Exception $e) {
                    $this->error("❌ Exception for {$asset->symbol}: " . $e->getMessage());
                }
            }

            $this->info("🎯 All metal prices updated! Waiting 10 seconds...");
            sleep(10);
        }

        return 0;
    }
}
