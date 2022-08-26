<?php

namespace App\Http\Controllers\API;

use App\Models\Sesi;
use App\Http\Controllers\Controller;
use App\Models\Enum\DayEnum;
use Illuminate\Support\Facades\DB;

class SesiController extends Controller
{

    public function index()
    {
        $result = [];
        $detailResult = [];
        $collection = DB::table('sesis')
            ->selectRaw('count(id) as total, hari')
            ->groupBy('hari')
            ->get();
        foreach ($collection as $data) {
            $sesi = Sesi::where('hari', $data->hari)->get();
            $detailResult = [
                'hari' => $data->hari,
                'result' => $sesi
            ];
            array_push($result, $detailResult);
        }

        return response()->json(
            [
                'status' => 200,
                'data' => $result,
            ],
            200
        );
    }
}
