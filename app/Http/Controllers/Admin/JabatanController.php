<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Jabatan;
use Illuminate\Http\Request;

class JabatanController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $data =
            Admin::where('username', $user->username)->first();

        if ($data) {
            $jabatans = Jabatan::all();
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
}
