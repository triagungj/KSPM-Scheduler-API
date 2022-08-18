<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Partisipant;
use App\Models\Petugas;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:255|unique:users',
            'password' => 'required|string|min:8',
            'name' => 'required|string',
            'phone_number' => 'required|string|min:8',
            'is_petugas' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        $user = User::create([
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'is_petugas' => $request->is_petugas,
        ]);

        if ($request->is_superuser != null) {
            Petugas::create([
                'username' => $request->username,
                'name' => $request->name,
                'phone_number' => $request->phone_number,
                'is_superuser' => $request->is_superuser,
            ]);
        } else {
            Partisipant::create([
                'username' => $request->username,
                'name' => $request->name,
                'phone_number' => $request->phone_number,
            ]);
        }

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

        if ($user->is_petugas == true) {
            $data =
                Petugas::where('username', $request['username'])->firstOrFail();
            return response()->json(['status' => 200, 'is_petugas' => $user->is_petugas, 'is_superuser' => $data->is_superuser, 'message' => 'Login Berhasil!', 'token' => $token]);
        } else {
            $data = Partisipant::where('username', $request['username'])->firstOrFail();
            return response()->json(['status' => 200, 'is_petugas' => $user->is_petugas, 'message' => 'Login Berhasil!', 'token' => $token]);
        }
    }

    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();
        } catch (\Exception $e) {
            throw new HttpException(500, $e->getMessage());
        }
        return [
            'status' => 200,
            'message' => 'Kamu telah berhasil logout!'
        ];
    }
}
