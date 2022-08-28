<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Enum\StatusEnum;
use App\Models\Partisipant;
use App\Models\ScheduleCandidate;
use App\Models\ScheduleRequest;
use App\Models\Sesi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ScheduleRequestController extends Controller
{
    public function getListSession()
    {
        $user = auth()->user();
        if ($user) {
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
            );
        } else {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
    }

    public function getListMySession()
    {
        $user = auth()->user();
        if (!$user->is_petugas) {
            $dataPartisipant =
                Partisipant::where('username', $user->username)->firstOrFail();
            $dataRequestSchedule = $dataPartisipant->scheduleRequest;
            $listScheduleCandidate = $dataRequestSchedule->scheduleCandidate;

            $listSessionId = [];
            foreach ($listScheduleCandidate as $scheduleCandidate) {

                array_push($listSessionId, $scheduleCandidate->session_id);
            }

            $fileUrl = $dataRequestSchedule->bukti != null
                ? url('/file') . '/' . $dataRequestSchedule->bukti
                : null;

            $data = [
                'id' => $dataRequestSchedule->id,
                'status' => $dataRequestSchedule->status,
                'nomor_petugas' => $dataRequestSchedule->petugas_id != null
                    ? $dataRequestSchedule->petugas->phone_number
                    : null,
                'bukti' => $fileUrl,
                'partisipant_notes' => $dataRequestSchedule->catatan_partisipant,
                'petugas_notes' => $dataRequestSchedule->catatan_petugas,
                'session_list_id' => $listSessionId,
            ];

            return
                response()->json(
                    [
                        'status' => 200,
                        'data' => $data,
                    ],
                );
        } else {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
    }

    public function saveRequest(Request $request)
    {
        $user = auth()->user();
        if (!$user->is_petugas) {
            $validator = Validator::make($request->all(), [
                'file' => 'mimes:pdf,jpg,jpeg,png|max:2048',
            ]);
            if ($validator->fails()) {
                return response()->json($validator->errors());
            }

            $dataPartisipant =
                Partisipant::where('username', $user->username)->firstOrFail();
            $dataRequestSchedule = $dataPartisipant->scheduleRequest;
            ScheduleCandidate::where('schedule_request_id', $dataRequestSchedule->id)->delete();

            $listSession = $request->input('list_session_id');

            foreach ($listSession as $session) {
                ScheduleCandidate::create([
                    'id' => Str::uuid(),
                    'schedule_request_id' => $dataRequestSchedule->id,
                    'session_id' => $session,
                ]);
            }

            $dataRequestSchedule->catatan_partisipant = $request->partisipant_notes;
            if ($file = $request->file('file')) {
                $file->store('/public/files');
                $dataRequestSchedule->bukti = $file->hashName();
            }
            $dataRequestSchedule->save();

            return
                response()->json(
                    [
                        'status' => 200,
                        'message' => 'Berhasil disimpan!',
                    ],
                );
        } else {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
    }

    public function requestSchedule(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'mimes:pdf,jpg,jpeg,png|max:2048',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 401, 'message' => $validator->errors()->first(),], 401);
        }
        $user = auth()->user();
        if (!$user->is_petugas) {

            $dataPartisipant =
                Partisipant::where('username', $user->username)->firstOrFail();
            $dataRequestSchedule = $dataPartisipant->scheduleRequest;
            if ($dataRequestSchedule->bukti == null) {
                $validator = Validator::make($request->all(), [
                    'file' => 'required|mimes:pdf,jpg,jpeg,png|max:2048',
                ]);
                if ($validator->fails()) {
                    return response()->json(['status' => 401, 'message' => $validator->errors()->first(),], 401);
                }
            }
            ScheduleCandidate::where('schedule_request_id', $dataRequestSchedule->id)->delete();

            $listSession = $request->input('list_session_id');

            foreach ($listSession as $session) {
                ScheduleCandidate::create([
                    'id' => Str::uuid(),
                    'schedule_request_id' => $dataRequestSchedule->id,
                    'session_id' => $session,
                ]);
            }

            $dataRequestSchedule->catatan_partisipant = $request->partisipant_notes;
            if ($file = $request->file('file')) {
                $file->store('/public/files');
                $dataRequestSchedule->bukti = $file->hashName();
            }

            $dataRequestSchedule->status = StatusEnum::Requested;
            $dataRequestSchedule->save();


            return
                response()->json(
                    [
                        'status' => 200,
                        'message' => 'Update Successed!'
                    ],
                );
        } else {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
    }
}
