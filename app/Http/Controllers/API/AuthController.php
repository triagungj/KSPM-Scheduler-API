<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Partisipan;
use App\Models\Petugas;
use App\Models\ScheduleRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        if (auth()->guard('user')->attempt(['username' => $request->input('username'),  'password' => $request->input('password')])) {
            $user = auth()->guard('user')->user();
            $user = User::where('username', $user->username)->firstOrFail();
            $token = $user->createToken('auth_token')->plainTextToken;
            if ($user->is_petugas == true) {
                return response()->json(['status' => 200, 'is_petugas' => $user->is_petugas, 'message' => 'Login Berhasil!', 'token' => $token]);
            } else {
                $data = Partisipan::where('username', $request['username'])->firstOrFail();
                $updated = $data->jabatan_id != null;
                return response()->json(['status' => 200, 'is_petugas' => $user->is_petugas, 'message' => 'Login Berhasil!', 'token' => $token, 'updated' => $updated,]);
            }
        } else {
            return response()->json(['status' => 401, 'message' => 'Username/Password salah'], 401);
        }
    }

    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'new_password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            $error = $validator->errors();
            return response()->json([
                'message' => $error->first('new_password')
            ], 401,);
        }

        $user = auth()->user();
        $data =
            User::where('username', $user->username)->firstOrFail();

        if (Hash::check($request->old_password, $data->password)) {
            $data->password = Hash::make($request->new_password);
            $data->save();
            return response()->json(
                [
                    'status' => 200,
                    'message' => 'Password berhasil diganti!'
                ],
            );
        } else {
            return response()->json(
                [
                    'status' => 401,
                    'message' => 'Password lama tidak valid!'
                ],
                401
            );
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

    public function getAdminContact()
    {
        $data = Admin::first();

        if ($data) {
            $phoneNumber = $data->phone_number;

            return response()->json(
                [
                    'status' => 200,
                    'message' => $phoneNumber,
                ],
            );
        } else {
            return response()->json(['status' => 401, 'message' => 'Unauthorized'], 401);
        }
    }
}
