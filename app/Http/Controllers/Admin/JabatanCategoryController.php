<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\JabatanCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class JabatanCategoryController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $data =
            Admin::where('username', $user->username)->first();

        if ($data) {
            $jabatanCategories = JabatanCategory::all();
            return response()->json(
                [
                    'status' => 200,
                    'data' => $jabatanCategories,
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
            $jabatanCategory = JabatanCategory::where('id', $id)->first();
            return response()->json(
                [
                    'status' => 200,
                    'data' => $jabatanCategory,
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
            ]);
            if ($validator->fails()) {
                return response()->json(['status' => 400, 'message' => $validator->errors()->first(),], 400);
            }
            JabatanCategory::create([
                'id' => Str::uuid(),
                'name' => $request->name,
            ]);
            return response()->json(
                [
                    'status' => 200,
                    'message' => 'Berhasil menambah Kategori Jabatan!',
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
                'name' => 'required|string|max:255|unique:jabatan_categories,name,' . $request->id,
            ]);
            if ($validator->fails()) {
                return response()->json(['status' => 400, 'message' => $validator->errors()->first(),], 400);
            }
            $jabatanCategory = JabatanCategory::where('id', $request->id)->firstOrFail();
            $jabatanCategory->name = $request->name;
            $jabatanCategory->save();
            return response()->json(
                [
                    'status' => 200,
                    'message' => 'Berhasil mengubah Kategori Jabatan!',
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
            JabatanCategory::where('id', $id)->delete();
            return response()->json(
                [
                    'status' => 200,
                    'message' => 'Berhasil menghapus Kategori Jabatan',
                ],
            );
        } else {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
    }
}
