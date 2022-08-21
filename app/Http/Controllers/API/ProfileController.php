<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Partisipant;
use App\Models\Petugas;
use Illuminate\Http\Request;

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
                200
            );
        } else {
            $data =
                Partisipant::where('username', $user->username)->firstOrFail();
            $jabatan = $data->jabatan;
            return
                response()->json(
                    [
                        'status' => 200,
                        'data' => [
                            'name' => $data->name,
                            'phone_number' => $data->phone_number,
                            'jabatan' => $jabatan->name,
                            'member_id' => $data->member_id,
                        ]
                    ],
                    200
                );
        }
    }
    public function edit(Request $request)
    {
        $user = auth()->user();
        if ($user->is_petugas) {
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
            $data =
                Partisipant::where('username', $user->username)->firstOrFail();
            $data->name = $request->name;
            $data->member_id = $request->member_id;
            $data->phone_number = $request->phone_number;
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
