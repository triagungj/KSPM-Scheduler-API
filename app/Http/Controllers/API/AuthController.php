<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Partisipant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:255|unique:users',
            'password' => 'required|string|min:8',
            'nama' => 'required|string',
            'phone_number' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        $user = User::create([
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'is_petugas' => $request->is_petugas,
        ]);

        Partisipant::create([
            'username' => $request->username,
            'nama' => $request->nama,
            'phone_number' => $request->phone_number,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json(['status' => 200, 'message' => 'Success Registered']);
    }

    public function login(Request $request)
    {
        if (!Auth::attempt($request->only('username', 'password'))) {
            return response()->json(['message' => 'Username/Passowrd salah'], 401);
        }

        $user = User::where('username', $request['username'])->firstOrFail();

        $token = $user->createToken('auth_token')->plainTextToken;
        return response()->json(['status' => 200, 'is_petugas' => $user->is_petugas, 'message' => 'Login Berhasil!', 'token' => $token]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return [
            'status' => 200,
            'message' => 'Kamu telah berhasil logout!'
        ];
    }
}
