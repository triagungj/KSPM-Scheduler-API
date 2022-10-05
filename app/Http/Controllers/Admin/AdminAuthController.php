<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AdminAuthController extends Controller
{    
    public function getLogin(){
        return response()->json(['message' => 'Username/Password kontol'], 401);

    }
    public function loginAdmin(Request $request){
        
        if(auth()->guard('admin')->attempt(['username' => $request->input('username'),  'password' => $request->input('password')])){
            $user = auth()->guard('admin')->user();
            $admin = Admin::where('username', $user->username)->firstOrFail();
            $token = $admin->createToken('auth_token')->plainTextToken;
            return response()->json(['status' => 200, 'message' => 'Login Success', 'token' => $token]);
       
        }else {
            return response()->json(['status' => 401, 'message' => 'Username/Password salah'], 401);
        }
    }

    public function getProfileAdmin(){
        $user = auth()->user();
        $admin = Admin::where('username', $user->username)->firstOrFail();
        if($admin){
            return response()->json(['status' => 200, 'data' => $admin]);
        }else{
            return response()->json(['status' => 401, 'message' => 'Unauthorized'], 401);
        }
    }

    public function updateAdmin(Request $request)
    {
        $user = auth()->user();
        $admin = Admin::where('id', $user->id)->firstOrFail();
        if ($admin) {
             $validator = Validator::make($request->all(), [
                'username' => 'required|string|max:255|min:8',
                'phone_number' => 'required|string|min:10',
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => 400, 'message' => $validator->errors()->first(),], 400);
            }
          
            $admin->username = $request->username;
            $admin->phone_number = $request->phone_number;

            $admin->save();
                                
            return [
                'status' => 200,
                'message' => 'Success!',
            ];
            
        } else {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
    }

    public function changePasswordAdmin(Request $request)
    {
        $user = auth()->user();
        $validator = Validator::make($request->all(), [
            'new_password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            $error = $validator->errors();
            return response()->json([
                'message' => $error->first('new_password')
            ], 401,);
        }
        $data =
            Admin::where('username', $user->username)->firstOrFail();

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

    public function logoutAdmin(Request $request)
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
