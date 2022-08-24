<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Enum\StatusEnum;
use App\Models\Partisipant;
use App\Models\ScheduleCandidate;
use App\Models\ScheduleRequest;
use Illuminate\Http\Request;

class ScheduleRequestController extends Controller
{
    public function saveRequest(Request $request)
    {
        $user = auth()->user();
        if (!$user->is_petugas) {
            $dataPartisipant =
                Partisipant::where('username', $user->username)->firstOrFail();
            $dataRequestSchedule = ScheduleRequest::where('partisipant_id', $dataPartisipant->id)->firstOrFail();

            $listSession = $request->input('session_id');

            foreach ($listSession as $session) {
                ScheduleCandidate::create([
                    'schedule_request_id' => $dataRequestSchedule->id,
                    'session_id' => $session,
                ]);
            }

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

    public function requestSchedule(Request $request)
    {
        $user = auth()->user();
        if (!$user->is_petugas) {
            $dataPartisipant =
                Partisipant::where('username', $user->username)->firstOrFail();
            $dataRequestSchedule = ScheduleRequest::where('partisipant_id', $dataPartisipant->id)->firstOrFail();

            $listSession = $request->input('session_id');

            foreach ($listSession as $session) {
                ScheduleCandidate::create([
                    'schedule_request_id' => $dataRequestSchedule->id,
                    'session_id' => $session,
                ]);
            }

            $dataRequestSchedule->status = StatusEnum::Requested;
            $dataRequestSchedule->catatan_partisipant = $request->catatan_partisipant;
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
