<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Partisipant;
use App\Models\Petugas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        if ($user->is_petugas) {
            $data =
                Petugas::where('username', $user->username)->firstOrFail();
            return response()->json(
                [
                    'status' => 200,
                    'data' => [
                        'name' => $data->name,
                        'phone_number' => $data->phone_number,
                    ]
                ],
            );
        } else {
            $data =
                Partisipant::where('username', $user->username)->firstOrFail();
            $jabatan = $data->jabatan;
            $avatarUrl = $data->avatar_url != null
                ? url('/image') . '/' . $data->avatar_url
                : null;
            return
                response()->json(
                    [
                        'status' => 200,
                        'data' => [
                            'name' => $data->name,
                            'phone_number' => $data->phone_number,
                            'jabatan' => $jabatan->name,
                            'member_id' => $data->member_id,
                            'avatar_url' => $avatarUrl,
                        ]
                    ],
                );
        }
    }
    public function edit(Request $request)
    {
        $user = auth()->user();
        if ($user->is_petugas) {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'phone_number' => 'required|string|min:8',
            ]);
            if ($validator->fails()) {
                return response()->json([$validator->errors()], 401);
            }
            $data =
                Petugas::where('username', $user->username)->firstOrFail();
            $data->name = $request->name;
            $data->phone_number = $request->phone_number;
            $data->save();
            return response()->json(
                [
                    'status' => 200,
                    'message' => 'Update Successed!'
                ],
            );
        } else {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'member_id' => 'required|string|max:255',
                'phone_number' => 'required|string|min:8',
                'image' => 'mimes:png,jpg,jpeg|max:2048',
            ]);
            if ($validator->fails()) {
                return response()->json($validator->errors());
            }
            $data =
                Partisipant::where('username', $user->username)->firstOrFail();
            $data->name = $request->name;
            $data->member_id = $request->member_id;
            $data->phone_number = $request->phone_number;
            if ($image = $request->file('image')) {
                $image->store('/public/images');
                $data->avatar_url = $image->hashName();
            }
            $data->save();
            return
                response()->json(
                    [
                        'status' => 200,
                        'message' => 'Update Successed!'
                    ],
                );
        }
    }
}
