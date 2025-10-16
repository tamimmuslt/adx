<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use MgCodeur\CurrencyConverter\Facades\CurrencyConverter;
class CurrencyController extends Controller
// {public function getEURUSD()
// {
//     try {
//         $response = Http::get('https://api.frankfurter.app/latest?from=EUR&to=USD');

//         if ($response->successful() && isset($response['rates']['USD'])) {
//             return response()->json([
//                 'symbol' => 'EUR/USD',
//                 'price' => $response['rates']['USD'],
//             ]);
//         }

//         return response()->json([
//             'error' => 'Failed to fetch EUR/USD price',
//             'data' => $response->json()
//         ], 500);

//     } catch (\Exception $e) {
//         return response()->json([
//             'error' => 'Exception occurred',
//             'message' => $e->getMessage()
//         ], 500);
//     }
// }
{


public function getRates()


{
    try {
        // سعر الدولار هو دائماً 1
        $usd_price = 1;

        // سعر اليورو مقابل الدولار الأمريكي مباشرةً
        $responseEur = Http::get('https://api.frankfurter.app/latest?from=EUR&to=USD');
        $eur_price = $responseEur->successful() && isset($responseEur['rates']['USD'])
            ? $responseEur['rates']['USD']
            : null;

        return response()->json([
            'USD_USD' => $usd_price,
            'EUR_USD' => $eur_price
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Exception occurred',
            'message' => $e->getMessage()
        ], 500);
    }

}

}