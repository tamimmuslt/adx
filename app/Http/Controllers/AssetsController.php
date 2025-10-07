<?php



// namespace App\Http\Controllers;

// use App\Models\Asset;
// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Validator;
// use Tymon\JWTAuth\Facades\JWTAuth;

// class AssetsController extends Controller
// {
//     // ========================
//     // عرض كل الأصول (للمستخدمين)
//     // ========================
//     public function index()
//     {
// $assets = Asset::with('latestPrice')->get();
//         return response()->json($assets);
//     }

//     public function show($id)
// {
//     $asset = Asset::with('latestPrice')->find($id);

//     if (!$asset) {
//         return response()->json(['error' => 'Asset not found'], 404);
//     }

//     return response()->json($asset);
// }
// public function history($id)
// {
//     $asset = Asset::find($id);

//     if (!$asset) {
//         return response()->json(['error' => 'Asset not found'], 404);
//     }

//     $prices = $asset->prices()->orderBy('timestamp', 'desc')->limit(50)->get();

//     return response()->json([
//         'asset' => $asset->symbol,
//         'history' => $prices
//     ]);
// }

//     // ========================
//     // إنشاء أصل جديد (Admin فقط)
//     // ========================
//     public function store(Request $request)
//     {
//         $user = JWTAuth::parseToken()->authenticate();

//         if ($user->role !== 'admin') {
//             return response()->json(['error'=>'Only admins can create assets'],403);
//         }

//         $validator = Validator::make($request->all(), [
//             'name' => 'required|string|unique:assets,name',
//             'symbol' => 'required|string|unique:assets,symbol',
//             'category' => 'required|in:commodities,indices,stocks,crypto'
//         ]);

//         if ($validator->fails()) {
//             return response()->json(['errors'=>$validator->errors()],422);
//         }

//         $asset = Asset::create($validator->validated());

//         return response()->json(['message'=>'Asset created successfully','asset'=>$asset]);
//     }

//     // ========================
//     // تعديل أصل (Admin فقط)
//     // ========================
//     public function update(Request $request, $id)
//     {
//         $user = JWTAuth::parseToken()->authenticate();

//         if ($user->role !== 'admin') {
//             return response()->json(['error'=>'Only admins can update assets'],403);
//         }

//         $asset = Asset::find($id);
//         if (!$asset) {
//             return response()->json(['error'=>'Asset not found'],404);
//         }

//         $validator = Validator::make($request->all(), [
//             'name' => 'sometimes|required|string|unique:assets,name,'.$asset->id,
//             'symbol' => 'sometimes|required|string|unique:assets,symbol,'.$asset->id,
//             'category' => 'sometimes|required|in:commodities,indices,stocks,crypto'
//         ]);

//         if ($validator->fails()) {
//             return response()->json(['errors'=>$validator->errors()],422);
//         }

//         $asset->update($validator->validated());

//         return response()->json(['message'=>'Asset updated successfully','asset'=>$asset]);
//     }

//     // ========================
//     // حذف أصل (Admin فقط)
//     // ========================
//     public function destroy($id)
//     {
//         $user = JWTAuth::parseToken()->authenticate();

//         if ($user->role !== 'admin') {
//             return response()->json(['error'=>'Only admins can delete assets'],403);
//         }

//         $asset = Asset::find($id);
//         if (!$asset) {
//             return response()->json(['error'=>'Asset not found'],404);
//         }

//         $asset->delete();

//         return response()->json(['message'=>'Asset deleted successfully']);
//     }

    
//     public function latestPrice($id)
// {
//     $asset = Asset::findOrFail($id);
//     $price = $asset->prices()->orderBy('open_time','desc')->first();

//     return response()->json([
//         'symbol' => $asset->symbol,
//         'price'  => $price->close ?? $asset->price ?? null,
//         'time'   => $price->timestamp ?? null,
//     ]);
// }

// public function chart(Request $request, $id)
// {
//     $limit = (int) $request->get('limit', 200);
//     $asset = Asset::findOrFail($id);

//     $rows = $asset->prices()->orderBy('open_time','desc')->take($limit)->get(['open','high','low','close','open_time']);
//     return response()->json($rows->reverse()->values());
// }

// }


namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetPrice;
use Illuminate\Http\Request;
use App\Models\AssetQuote;

class AssetsController extends Controller
{
    // جلب كل الأصول مع آخر سعر
    public function index()
    {
        $assets = Asset::with(['latestPrice'])->get();
        return response()->json($assets);
    }

    // جلب أصل واحد مع آخر سعر
    public function show($id)
    {
        $asset = Asset::with(['latestPrice'])->findOrFail($id);
        return response()->json($asset);
    }

    // جلب الأسعار التاريخية (شموع)
    public function prices(Request $request, $id)
    {
        $interval = $request->get('interval', '1m'); // الافتراضي دقيقة
        $limit = $request->get('limit', 100); // الافتراضي 100 شمعة

        $prices = AssetPrice::where('asset_id', $id)
            ->orderBy('open_time', 'desc')
            ->limit($limit)
            ->get();

        return response()->json($prices);
    }

    // جلب آخر سعر فقط
    public function latest($id)
    {
        $price = AssetPrice::where('asset_id', $id)
            ->orderBy('open_time', 'desc')
            ->first();

        return response()->json($price);
    }

public function getAssetData($id)
{
    $asset = Asset::findOrFail($id);

    // آخر 50 شمعة
    $prices = $asset->prices()
        ->orderBy('timestamp', 'desc')
        ->take(50)
        ->get(['open', 'high', 'low', 'close', 'timestamp'])
        ->reverse()
        ->values();

    // آخر سعر للـ buy/sell
    $latestQuote = $asset->quotes()
        ->latest('timestamp')
        ->first(['buy_price','sell_price','timestamp']);

    return response()->json([
        'asset' => $asset->only(['id','name','symbol']),
        'candlesticks' => $prices,
        'latest' => $latestQuote
    ]);
}


}
