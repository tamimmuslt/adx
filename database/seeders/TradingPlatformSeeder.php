<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class TradingPlatformSeeder extends Seeder
{
    public function run(): void
    {
        // Seed Users
        DB::table('users')->insert([
            [
                'email' => 'user1@gmail.com',
                'phone' => '701111111',
                'password' => Hash::make('password123'),
                'full_name' => 'Test User1',
                'is_verified' => true,
                'two_fa_enabled' => false,
                'role' => 'user',
                'balance' => 10000,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'email' => 'admin@gmail.com',
                'phone' => '709999999',
                'password' => Hash::make('admin12345'),
                'full_name' => 'Admin User',
                'is_verified' => true,
                'two_fa_enabled' => false,
                'role' => 'admin',
                'balance' => 0,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);

        // Seed Wallets
        DB::table('wallets')->insert([
            [
                'user_id' => 1,
                'asset_symbol' => 'BTC/USD',
                'asset_type' => 'Cryptocurrency',
                'quantity' => 0.82,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'user_id' => 1,
                'asset_symbol' => 'XAU/USD',
                'asset_type' => 'Commodity',
                'quantity' => 1.5,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'user_id' => 1,
                'asset_symbol' => 'TSLA/USD',
                'asset_type' => 'Stock',
                'quantity' => 10,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);

        // Seed Transactions
        DB::table('transactions')->insert([
            [
                'user_id' => 1,
                'type' => 'deposit',
                'currency' => 'USDT',
                'amount' => 200.0,
                'balance_after' => 12000.00,
                'method' => 'Binance Wallet',
                'status' => 'completed',
                'details' => json_encode(['note'=>'Initial deposit']),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'user_id' => 1,
                'type' => 'withdraw',
                'currency' => 'USD',
                'amount' => 250.0,
                'balance_after' => 11750.00,
                'method' => 'Bank Transfer',
                'status' => 'pending',
                'details' => json_encode([
                    'bank_name'=>'Bank of Beirut',
                    'iban'=>'LB123456789',
                    'note'=>'Withdrawal test'
                ]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'user_id' => 1,
                'type' => 'deposit',
                'currency' => 'BTC',
                'amount' => 0.005,
                'balance_after' => 0.825,
                'method' => 'Crypto',
                'status' => 'failed',
                'details' => json_encode(['tx_id'=>'0xdeadbeef', 'note'=>'Failed test']),
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }
}
