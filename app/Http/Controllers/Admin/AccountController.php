<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Partisipan;
use App\Models\Petugas;
use App\Models\ScheduleRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AccountController extends Controller
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
            'id' => Str::uuid(),
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'is_petugas' => $request->is_petugas,
        ]);

        if ($request->is_superuser != null) {
            Petugas::create([
                'id' => Str::uuid(),
                'username' => $request->username,
                'name' => $request->name,
                'phone_number' => $request->phone_number,
                'is_superuser' => $request->is_superuser,
            ]);
        } else {
            Partisipan::create([
                'id' => Str::uuid(),
                'username' => $request->username,
                'name' => $request->name,
                'phone_number' => $request->phone_number,
                'jabatan_id' => $request->jabatan_id,
                'member_id' => $request->member_id,
            ]);

            $partisipan = Partisipan::where('username', $user->username)->firstOrFail();

            ScheduleRequest::create([
                'id' => Str::uuid(),
                'partisipan_id' => $partisipan->id,
            ]);
        }

        return response()->json(['status' => 200, 'message' => 'Success Registered']);
    }

    public function getListPartisipan(Request $request)
    {
        $user = auth()->user();
        $data =
            Admin::where('username', $user->username)->first();
        if ($data) {
            $query = $request->query('query');
            $partisipans = Partisipan::select(['partisipans.*', 'jabatans.name as jabatan'])
                ->join('jabatans', 'jabatans.id', '=', 'jabatan_id')
                ->where('partisipans.name', 'like', '%' . $query . '%')
                ->orderBy('created_at', 'desc')->paginate(10);

            return response()->json(
                [
                    'status' => 200,
                    'data' => $partisipans,
                ],
            );
        } else {
            return response()->json(['status' => 401, 'message' => 'Unauthorized'], 401);
        }
    }
    public function getPartisipan($id)
    {
        $user = auth()->user();
        $data =
            Admin::where('username', $user->username)->first();
        if ($data) {
            $partisipan = Partisipan::where('id', $id)->first();

            return response()->json(
                [
                    'status' => 200,
                    'data' => $partisipan,
                ],
            );
        } else {
            return response()->json(['status' => 401, 'message' => 'Unauthorized'], 401);
        }
    }
    public function createPartisipan(Request $request)
    {
        $auth = auth()->user();
        $admin =
            Admin::where('username', $auth->username)->first();
        if ($admin) {
            $validator = Validator::make($request->all(), [
                'username' => 'required|string|max:255|alpha_dash|unique:users',
                'password' => 'required|string|min:8',
                'name' => 'required|string',
                'phone_number' => 'required|string|min:8',
                'member_id' => 'required|string|min:8',
                'jabatan_id' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json(['message' => $validator->errors()->first()], 400);
            }

            $userCreated = User::create([
                'id' => Str::uuid(),
                'username' => $request->username,
                'password' => Hash::make($request->password),
                'is_petugas' => false,
            ]);

            Partisipan::create([
                'id' => Str::uuid(),
                'username' => $request->username,
                'name' => $request->name,
                'phone_number' => $request->phone_number,
                'jabatan_id' => $request->jabatan_id,
                'member_id' => $request->member_id,
                'jabatan_id' => $request->jabatan_id,
            ]);

            $partisipan = Partisipan::where('username', $userCreated->username)->firstOrFail();

            ScheduleRequest::create([
                'id' => Str::uuid(),
                'partisipan_id' => $partisipan->id,
            ]);
            return response()->json(
                [
                    'status' => 200,
                    'message' => 'Akun Partisipan berhasil dibuat',
                ],
            );
        } else {
            return response()->json(['status' => 401, 'message' => 'Unauthorized'], 401);
        }
    }
    public function updatePartisipan(Request $request, $id)
    {
        $auth = auth()->user();
        $admin =
            Admin::where('username', $auth->username)->first();
        if ($admin) {
            $validator = Validator::make($request->all(), [
                'username' => 'required|string|max:255|min:8',
                'name' => 'required|string|max:255',
                'password' => $request->password != ''
                    ? 'required|string|min:8'
                    : '',
                'phone_number' => 'required|string|min:8',
                'member_id' => 'required|string|min:8',
                'jabatan_id' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json(['message' => $validator->errors()->first()], 400);
            }

            $partisipan = Partisipan::where('id', $id)->firstOrFail();
            $partisipanUser = $partisipan->user;

            if ($partisipanUser->username != $request->username) {
                $partisipanUser->username = $request->username;
            }
            if ($request->password != '') {
                $partisipanUser->password = Hash::make($request->password);
            }
            $partisipanUser->save();

            $partisipan->name = $request->name;
            $partisipan->phone_number = $request->phone_number;
            $partisipan->member_id = $request->member_id;
            $partisipan->jabatan_id = $request->jabatan_id;
            if ($partisipan->save()) {
                return response()->json(
                    [
                        'status' => 200,
                        'message' => 'Akun Partisipan berhasil diedit',
                    ],
                );
            }
        } else {
            return response()->json(['status' => 401, 'message' => 'Unauthorized'], 401);
        }
    }
    public function deletePartisipan($id)
    {
        $user = auth()->user();
        $data =
            Admin::where('username', $user->username)->first();
        if ($data) {
            $partisipan = Partisipan::where('id', $id)->first();
            $partisipan->user->delete();
            return response()->json(
                [
                    'status' => 200,
                    'message' => 'Delete Success!',
                ],
            );
        } else {
            return response()->json(['status' => 401, 'message' => 'Unauthorized'], 401);
        }
    }
    public function deleteAllPartisipan()
    {
        $user = auth()->user();
        $data =
            Admin::where('username', $user->username)->first();
        if ($data) {
            User::where('is_petugas', '=', false)->delete();

            return response()->json(
                [
                    'status' => 200,
                    'message' => 'Delete Success!',
                ],
            );
        } else {
            return response()->json(['status' => 401, 'message' => 'Unauthorized'], 401);
        }
    }
}
