<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Pertemuan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PertemuanController extends Controller
{

    public function index()
    {
        $user = auth()->user();
        $data =
            Admin::where('username', $user->username)->first();

        if ($data) {
            $pertemuan = Pertemuan::orderBy('name', 'asc')->get();
            return response()->json(
                [
                    'status' => 200,
                    'data' => $pertemuan,
                ],
            );
        } else {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
    }
    public function get($id)
    {
        $user = auth()->user();
        $admin =
            Admin::where('username', $user->username)->first();

        if ($admin) {
            $pertemuan = Pertemuan::where('id', $id)->first();
            return response()->json(
                [
                    'status' => 200,
                    'data' => $pertemuan,
                ],
            );
        } else {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
    }

    public function create(Request $request)
    {
        $user = auth()->user();
        $admin = Admin::where('username', $user->username)->first();

        if ($admin) {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
            ]);
            if ($validator->fails()) {
                return response()->json(['status' => 400, 'message' => $validator->errors()->first(),], 400);
            }
            Pertemuan::create([
                'id' => Str::uuid(),
                'name' => $request->name,
            ]);
            return response()->json(
                [
                    'status' => 200,
                    'message' => 'Berhasil menambah Pertemuan!',
                ],
            );
        } else {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
    }

    public function update(Request $request)
    {
        $user = auth()->user();
        $admin = Admin::where('username', $user->username)->first();

        if ($admin) {
            $validator = Validator::make($request->all(), [
                'id' => 'required|string|max:255',
                'name' => 'required|string|max:255',
            ]);
            if ($validator->fails()) {
                return response()->json(['status' => 400, 'message' => $validator->errors()->first(),], 400);
            }
            $pertemuan = Pertemuan::where('id', $request->id)->firstOrFail();
            $pertemuan->name = $request->name;
            $pertemuan->save();
            return response()->json(
                [
                    'status' => 200,
                    'message' => 'Berhasil mengubah pertemuan!',
                ],
            );
        } else {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
    }
    public function delete($id)
    {
        $user = auth()->user();
        $admin =
            Admin::where('username', $user->username)->first();

        if ($admin) {
            Pertemuan::where('id', $id)->delete();
            return response()->json(
                [
                    'status' => 200,
                    'message' => 'Berhasil menghapus Pertemuan',
                ],
            );
        } else {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
    }
}
