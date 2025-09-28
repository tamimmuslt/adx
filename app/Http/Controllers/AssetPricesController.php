<?php


namespace App\Http\Controllers;

use App\Models\AssetPrice;
use App\Models\Asset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class AssetPricesController extends Controller
{
    // ========================
    // عرض آخر الأسعار لكل الأصول
    // ========================
    public function index()
    {
        $prices = AssetPrice::with('asset')->latest('timestamp')->get();
        return response()->json($prices);
    }

    // ========================
    // إضافة سعر جديد (Admin فقط)
    // ========================
    public function store(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();

        if ($user->role !== 'admin') {
            return response()->json(['error'=>'Only admins can add prices'],403);
        }

        $validator = Validator::make($request->all(), [
            'asset_id' => 'required|exists:assets,id',
            'buy_price' => 'required|numeric|min:0',
            'sell_price' => 'required|numeric|min:0',
            'timestamp' => 'nullable|date'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors'=>$validator->errors()],422);
        }

        $price = AssetPrice::create($validator->validated());

        return response()->json(['message'=>'Price added successfully','price'=>$price]);
    }

    // ========================
    // تعديل سعر موجود (Admin فقط)
    // ========================
    public function update(Request $request, $id)
    {
        $user = JWTAuth::parseToken()->authenticate();

        if ($user->role !== 'admin') {
            return response()->json(['error'=>'Only admins can update prices'],403);
        }

        $price = AssetPrice::find($id);
        if (!$price) {
            return response()->json(['error'=>'Price record not found'],404);
        }

        $validator = Validator::make($request->all(), [
            'buy_price' => 'sometimes|required|numeric|min:0',
            'sell_price' => 'sometimes|required|numeric|min:0',
            'timestamp' => 'sometimes|date'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors'=>$validator->errors()],422);
        }

        $price->update($validator->validated());

        return response()->json(['message'=>'Price updated successfully','price'=>$price]);
    }

    // ========================
    // حذف سعر (Admin فقط)
    // ========================
    public function destroy($id)
    {
        $user = JWTAuth::parseToken()->authenticate();

        if ($user->role !== 'admin') {
            return response()->json(['error'=>'Only admins can delete prices'],403);
        }

        $price = AssetPrice::find($id);
        if (!$price) {
            return response()->json(['error'=>'Price record not found'],404);
        }

        $price->delete();

        return response()->json(['message'=>'Price deleted successfully']);
    }
}
