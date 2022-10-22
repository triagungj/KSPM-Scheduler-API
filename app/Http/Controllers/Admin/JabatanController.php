<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Jabatan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class JabatanController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $data =
            Admin::where('username', $user->username)->first();

        if ($data) {
            $jabatans = Jabatan::select('jabatans.*', 'jabatan_categories.name as jabatan_category')
                ->join('jabatan_categories', 'jabatan_categories.id', '=', 'jabatans.jabatan_category_id')->get();
            return response()->json(
                [
                    'status' => 200,
                    'data' => $jabatans,
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
            $jabatan = Jabatan::where('id', $id)->first();
            return response()->json(
                [
                    'status' => 200,
                    'data' => $jabatan,
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
                'name' => 'required|string|max:255|unique:jabatans',
                'jabatan_category_id' => 'required|string|max:255',
            ]);
            if ($validator->fails()) {
                return response()->json(['status' => 400, 'message' => $validator->errors()->first(),], 400);
            }
            Jabatan::create([
                'id' => Str::uuid(),
                'name' => $request->name,
                'jabatan_category_id' => $request->jabatan_category_id
            ]);
            return response()->json(
                [
                    'status' => 200,
                    'message' => 'Berhasil menambah Jabatan!',
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
                'name' => 'required|string|max:255|unique:jabatans,name,' . $request->id,
                'jabatan_category_id' => 'required|string|max:255',
            ]);
            if ($validator->fails()) {
                return response()->json(['status' => 400, 'message' => $validator->errors()->first(),], 400);
            }
            $jabatan = Jabatan::where('id', $request->id)->firstOrFail();
            $jabatan->name = $request->name;
            $jabatan->jabatan_category_id = $request->jabatan_category_id;
            $jabatan->save();
            return response()->json(
                [
                    'status' => 200,
                    'message' => 'Berhasil mengubah Jabatan!',
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
            Jabatan::where('id', $id)->delete();
            return response()->json(
                [
                    'status' => 200,
                    'message' => 'Berhasil menghapus Jabatan',
                ],
            );
        } else {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
    }
}
