<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Enum\DayEnum;
use App\Models\Sesi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Enum;

class SesiController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $admin =
            Admin::where('username', $user->username)->first();

        if ($admin) {
            $sesiList = Sesi::select('sesis.*', 'pertemuans.name as pertemuan')
                ->join('pertemuans', 'pertemuans.id', '=', 'sesis.pertemuan_id')
                ->orderBy('id', 'asc')->paginate(10);
            return response()->json(
                [
                    'status' => 200,
                    'data' => $sesiList,
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
            $sesi = Sesi::where('id', $id)->first();
            return response()->json(
                [
                    'status' => 200,
                    'data' => $sesi,
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
                'hari' => [new Enum(DayEnum::class)],
                'waktu' => 'required|string|max:255',
                'pertemuan_id' => 'required|string|max:255',
            ]);
            if ($validator->fails()) {
                return response()->json(['status' => 400, 'message' => $validator->errors()->first(),], 400);
            }
            Sesi::create([
                'name' => $request->name,
                'pertemuan_id' => $request->pertemuan_id,
                'hari' => $request->hari,
                'waktu' => $request->waktu,
            ]);
            return response()->json(
                [
                    'status' => 200,
                    'message' => 'Berhasil menambah Sesi!',
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
                'id' => 'required|int',
                'name' => 'required|string|max:255',
                'hari' => [new Enum(DayEnum::class)],
                'waktu' => 'required|string|max:255',
                'pertemuan_id' => 'required|string|max:255',
            ]);
            if ($validator->fails()) {
                return response()->json(['status' => 400, 'message' => $validator->errors()->first(),], 400);
            }
            $sesi = Sesi::where('id', $request->id)->firstOrFail();
            $sesi->name = $request->name;
            $sesi->hari = $request->hari;
            $sesi->waktu = $request->waktu;
            $sesi->pertemuan_id = $request->pertemuan_id;
            $sesi->save();
            return response()->json(
                [
                    'status' => 200,
                    'message' => 'Success!',
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
            Sesi::where('id', $id)->delete();
            return response()->json(
                [
                    'status' => 200,
                    'message' => 'Berhasil menghapus Sesi',
                ],
            );
        } else {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
    }
}
