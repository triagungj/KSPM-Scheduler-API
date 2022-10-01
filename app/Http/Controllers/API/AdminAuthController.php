<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AdminAuthController extends Controller
{
    public function loginAdmin(Request $request){
        if (!Auth::attempt($request->only('username', 'password'))) {
            return response()->json(['message' => 'Username/Password salah'], 401);
        }

        $user = Admin::where('username', $request['username'])->firstOrFail();

        if ($user) {
            $token = $user->createToken('auth_token')->plainTextToken;
            return response()->json(['status' => 200, 'message' => 'Login Success', 'token' => $token]);
        } else {
            return response()->json(['status' => 401, 'message' => 'Username/Password salah']);
        }
    }

    public function logoutAdmin(Request $request)
    {
        $user = auth()->user();
        if ($user) {
          
            $user = auth()->user();
            $request->user()->currentAccessToken()->delete();
          
            return [
                'status' => 200,
                'message' => 'Kamu telah berhasil logout!',
            ];
            
        } else {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
    }
}
