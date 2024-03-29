<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Partisipan;
use App\Models\Petugas;
use App\Models\ScheduleRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AccountController extends Controller
{
    public function getListPartisipan(Request $request)
    {
        $user = auth()->user();
        $data =
            Admin::where('username', $user->username)->first();
        if ($data) {
            $query = $request->query('query');
            $partisipans = Partisipan::select(['partisipans.*', 'jabatans.name as jabatan'])
                ->leftJoin('jabatans', 'jabatans.id', '=', 'jabatan_id')
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
                'username' => 'required|string|max:255|alpha_dash|unique:partisipans,username,' . $id,
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
                $partisipan->username = $request->username;
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
            $partisipan->delete();
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
    public function generatePartisipan(Request $request)
    {
        $auth = auth()->user();
        $admin =
            Admin::where('username', $auth->username)->first();
        if ($admin) {
            $validator = Validator::make($request->all(), [
                'file' => 'required|mimes:csv,txt|max:2048',
            ]);

            if ($validator->fails()) {
                return response()->json(['message' => $validator->errors()->first()], 400);
            }

            if ($file = $request->file('file')) {
                $file->store('/public/files');
                $fileName = $request->file('file')->hashName();
                $filePath = storage_path('app/public/files/' . $fileName);
                $row = 1;
                $partisipans = [];
                $listScheduleRequest = [];
                $users = [];
                if (($handle = fopen($filePath, "r")) !== FALSE) {
                    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                        if ($row == 1) {
                            $row++;
                            continue;
                        }
                        $partisipanId = Str::uuid();
                        $partisipan = [
                            'id' => $partisipanId,
                            'username' => $data[0],
                            'name' => $data[1],
                            'member_id' => $data[4],
                            'phone_number' => $data[3]
                        ];

                        $scheduleRequest = [
                            'id' => Str::uuid(),
                            'partisipan_id' => $partisipanId,
                            'status' => null,
                        ];
                        $user = [
                            'id' => Str::uuid(),
                            'username' => $data[0],
                            'password' => Hash::make($data[2]),
                        ];

                        array_push($users, $user);
                        array_push($partisipans, $partisipan);
                        array_push($listScheduleRequest, $scheduleRequest);
                        $row++;
                    }
                    fclose($handle);
                }
            }

            $insertUsers = DB::table('users')->insert($users);
            $insertPartisipan = DB::table('partisipans')->insert($partisipans);
            $insertScheduleRequest = DB::table('schedule_requests')->insert($listScheduleRequest);

            if ($insertPartisipan && $insertUsers && $insertScheduleRequest) {
                return response()->json(
                    [
                        'status' => 200,
                        'message' => "Berhasil menambah Partisipan",
                    ],
                );
            } else {
                return response()->json(
                    [
                        'status' => 400,
                        'message' => "Gagal menambah Partisipan",
                    ],
                );
            }
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
            Partisipan::where('id', 'like', '%%')->delete();
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
    public function getListPetugas()
    {
        $user = auth()->user();
        $admin =
            Admin::where('username', $user->username)->first();
        if ($admin) {
            $petugas = Petugas::orderBy('created_at', 'desc')->get();

            return response()->json(
                [
                    'status' => 200,
                    'data' => $petugas,
                ],
            );
        } else {
            return response()->json(['status' => 401, 'message' => 'Unauthorized'], 401);
        }
    }
    public function getPetugas($id)
    {
        $user = auth()->user();
        $data =
            Admin::where('username', $user->username)->first();
        if ($data) {
            $petugas = Petugas::where('id', $id)->first();

            return response()->json(
                [
                    'status' => 200,
                    'data' => $petugas,
                ],
            );
        } else {
            return response()->json(['status' => 401, 'message' => 'Unauthorized'], 401);
        }
    }
    public function updatePetugas(Request $request, $id)
    {
        $auth = auth()->user();
        $admin =
            Admin::where('username', $auth->username)->first();
        if ($admin) {
            $validator = Validator::make($request->all(), [
                'username' => 'required|string|max:255|min:8|alpha_dash|unique:petugas,username,' . $id,
                'name' => 'required|string|max:255',
                'password' => $request->password != ''
                    ? 'required|string|min:8'
                    : '',
                'phone_number' => 'required|string|min:8',
            ]);

            if ($validator->fails()) {
                return response()->json(['message' => $validator->errors()->first()], 400);
            }

            $petugas = Petugas::where('id', $id)->firstOrFail();
            $petugasUser = $petugas->user;

            if ($petugasUser->username != $request->username) {
                $petugasUser->username = $request->username;
                $petugas->username = $request->username;
            }
            if ($request->password != '') {
                $petugasUser->password = Hash::make($request->password);
            }
            $petugasUser->save();

            $petugas->name = $request->name;
            $petugas->phone_number = $request->phone_number;
            if ($petugas->save()) {
                return response()->json(
                    [
                        'status' => 200,
                        'message' => 'Akun Petugas berhasil diedit',
                    ],
                );
            }
        } else {
            return response()->json(['status' => 401, 'message' => 'Unauthorized'], 401);
        }
    }
    public function createPetugas(Request $request)
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
            ]);

            if ($validator->fails()) {
                return response()->json(['message' => $validator->errors()->first()], 400);
            }

            User::create([
                'id' => Str::uuid(),
                'username' => $request->username,
                'password' => Hash::make($request->password),
                'is_petugas' => true,
            ]);

            Petugas::create([
                'id' => Str::uuid(),
                'username' => $request->username,
                'name' => $request->name,
                'phone_number' => $request->phone_number,
            ]);

            return response()->json(
                [
                    'status' => 200,
                    'message' => 'Akun Petugas berhasil dibuat',
                ],
            );
        } else {
            return response()->json(['status' => 401, 'message' => 'Unauthorized'], 401);
        }
    }
    public function deletePetugas($id)
    {
        $user = auth()->user();
        $admin =
            Admin::where('username', $user->username)->first();
        if ($admin) {
            $petugas = Petugas::where('id', $id)->first();
            $petugas->user->delete();
            $petugas->delete();
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
    public function deleteAllPetugas()
    {
        $user = auth()->user();
        $data =
            Admin::where('username', $user->username)->first();
        if ($data) {
            Petugas::where('id', 'like', '%%')->delete();
            User::where('is_petugas', '=', true)->delete();

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
