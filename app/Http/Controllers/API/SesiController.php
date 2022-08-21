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
        $detailDataResult = [];
        $collection =
            DB::table('sesis')
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
        // foreach (Sesi::all() as $sesi) {

        //     array_push($result, $detailResult);
        //     $temp = $sesi->hari;
        //     $detailDataResult = [];

        // }


        return response()->json(
            [
                'status' => 200,
                'data' => $result,
            ],
            200
        );
    }
}
