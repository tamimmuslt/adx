<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Asset;

class AssetsTableSeeder extends Seeder
{
    public function run(): void
    {
        $assets = [
            // Cryptocurrencies
            ['name'=>'Bitcoin','symbol'=>'BTC/USD','category'=>'crypto'],
            ['name'=>'Ethereum','symbol'=>'ETH/USD','category'=>'crypto'],
            ['name'=>'Ripple','symbol'=>'XRP/USD','category'=>'crypto'],
            ['name'=>'Litecoin','symbol'=>'LTC/USD','category'=>'crypto'],
            ['name'=>'Cardano','symbol'=>'ADA/USD','category'=>'crypto'],
            ['name'=>'Dogecoin','symbol'=>'DOGE/USD','category'=>'crypto'],
            ['name'=>'Binance Coin','symbol'=>'BNB/USD','category'=>'crypto'],
            ['name'=>'Solana','symbol'=>'SOL/USD','category'=>'crypto'],
            ['name'=>'Polkadot','symbol'=>'DOT/USD','category'=>'crypto'],
            ['name'=>'Shiba Inu','symbol'=>'SHIB/USD','category'=>'crypto'],

            // Commodities
            ['name'=>'Gold','symbol'=>'XAU/USD','category'=>'commodities'],
            ['name'=>'Silver','symbol'=>'XAG/USD','category'=>'commodities'],
            ['name'=>'Platinum','symbol'=>'XPT/USD','category'=>'commodities'],
            ['name'=>'Palladium','symbol'=>'XPD/USD','category'=>'commodities'],
            ['name'=>'Oil (WTI)','symbol'=>'CL/USD','category'=>'commodities'],
            ['name'=>'Oil (Brent)','symbol'=>'BRN/USD','category'=>'commodities'],
            ['name'=>'Natural Gas','symbol'=>'NG/USD','category'=>'commodities'],
            ['name'=>'Copper','symbol'=>'HG/USD','category'=>'commodities'],
            ['name'=>'Corn','symbol'=>'ZC/USD','category'=>'commodities'],
            ['name'=>'Wheat','symbol'=>'ZW/USD','category'=>'commodities'],

            // Indices
            ['name'=>'S&P 500','symbol'=>'SPX/USD','category'=>'indices'],
            ['name'=>'Dow Jones','symbol'=>'DJI/USD','category'=>'indices'],
            ['name'=>'Nasdaq 100','symbol'=>'NDX/USD','category'=>'indices'],
            ['name'=>'FTSE 100','symbol'=>'UKX/USD','category'=>'indices'],
            ['name'=>'DAX 30','symbol'=>'DAX/USD','category'=>'indices'],
            ['name'=>'Nikkei 225','symbol'=>'N225/USD','category'=>'indices'],
            ['name'=>'Hang Seng','symbol'=>'HSI/USD','category'=>'indices'],
            ['name'=>'CAC 40','symbol'=>'CAC/USD','category'=>'indices'],

            // Stocks
            ['name'=>'Apple','symbol'=>'AAPL/USD','category'=>'stocks'],
            ['name'=>'Microsoft','symbol'=>'MSFT/USD','category'=>'stocks'],
            ['name'=>'Amazon','symbol'=>'AMZN/USD','category'=>'stocks'],
            ['name'=>'Tesla','symbol'=>'TSLA/USD','category'=>'stocks'],
            ['name'=>'Alphabet (Google)','symbol'=>'GOOGL/USD','category'=>'stocks'],
            ['name'=>'Meta (Facebook)','symbol'=>'META/USD','category'=>'stocks'],
            ['name'=>'Nvidia','symbol'=>'NVDA/USD','category'=>'stocks'],
            ['name'=>'Intel','symbol'=>'INTC/USD','category'=>'stocks'],
            ['name'=>'Coca-Cola','symbol'=>'KO/USD','category'=>'stocks'],
            ['name'=>'PepsiCo','symbol'=>'PEP/USD','category'=>'stocks'],
        ];

        foreach ($assets as $asset) {
            Asset::updateOrCreate(
                ['symbol' => $asset['symbol']],
                $asset
            );
        }

        $this->command->info('Assets seeded successfully!');
    }
}
