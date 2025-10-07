<?php
namespace App\Http\Controllers;

use App\Models\Wallet;
use App\Models\Asset;
use Illuminate\Support\Facades\Auth;

class WalletController extends Controller
{
    // قائمة الأصول والكميات والقيم للمستخدم
    public function index()
    {
        $wallets = Wallet::where('user_id', Auth::id())->get();
        $walletData = $wallets->map(function ($wallet) {
            $asset = Asset::where('symbol', $wallet->asset_symbol)->first();
            $currentPrice = $asset ? $asset->price : 0;
            return [
                'name'          => $wallet->asset_symbol,
                'type'          => $wallet->asset_type,
                'quantity'      => $wallet->quantity,
                'current_price' => $currentPrice,
                'value'         => $currentPrice * $wallet->quantity,
            ];
        });
        return response()->json($walletData);
    }
}
