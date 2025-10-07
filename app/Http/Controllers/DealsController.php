<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Deal;
use Tymon\JWTAuth\Facades\JWTAuth;

class DealsController extends Controller
{

public function index()
{
    $user = JWTAuth::user();
    $deals = Deal::where('user_id', $user->id)
        ->orderBy('executed_at', 'desc')
        ->get();
    return response()->json($deals);
}


}
