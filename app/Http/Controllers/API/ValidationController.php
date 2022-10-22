<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Enum\PartisipanEnum;
use App\Models\Enum\StatusEnum;
use App\Models\Enum\ValidationEnum;
use App\Models\Jabatan;
use App\Models\ScheduleRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;

class ValidationController extends Controller
{
    public function getListCount()
    {
        $user = auth()->user();
        $admin =
            Admin::where('username', $user->username)->first();
        if ($user->is_petugas || $admin) {

            $partisipanRequest = ScheduleRequest::join('partisipans', 'partisipans.id', '=', 'partisipan_id')
                ->join('jabatans', 'jabatans.id', '=', 'partisipans.jabatan_id')
                ->get(['schedule_requests.*', 'jabatans.*']);

            $pengurusSchedule = $partisipanRequest->where('name', '!=', 'Anggota Magang');
            $anggotaRequest = $partisipanRequest->where('name', '=', 'Anggota Magang');

            $pengurusScheduleRequest = $pengurusSchedule;
            $pengurusRequested = $pengurusScheduleRequest->where('status', '=', StatusEnum::Requested)->count();
            $pengurusRejected = $pengurusScheduleRequest->where('status', '=', StatusEnum::Rejected)->count();
            $pengurusAccepted = $pengurusScheduleRequest->where('status', '=', StatusEnum::Accepted)->count();
            $pengurusEmpty = $pengurusScheduleRequest->where('status', '=', null)->count();
            $pengurusValidated = $pengurusScheduleRequest->where('status', '!=', StatusEnum::Requested)
                ->where('status', '!=', null)
                ->count();
            $pengurusAll = $pengurusScheduleRequest->count();

            $anggotaScheduleRequest = $anggotaRequest;
            $anggotaRequested = $anggotaScheduleRequest->where('status', '=', StatusEnum::Requested)->count();
            $anggotaRejected = $anggotaScheduleRequest->where('status', '=', StatusEnum::Rejected)->count();
            $anggotaAccepted = $anggotaScheduleRequest->where('status', '=', StatusEnum::Accepted)->count();
            $anggotaEmpty = $anggotaScheduleRequest->where('status', '=', null)->count();
            $anggotaValidated = $anggotaScheduleRequest->where('status', '!=', StatusEnum::Requested)
                ->where('status', '!=', null)
                ->count();
            $anggotaAll = $anggotaScheduleRequest->count();

            $countPengurus = [
                'requested' => $pengurusRequested,
                'rejected' => $pengurusRejected,
                'accepted' => $pengurusAccepted,
                'empty' => $pengurusEmpty,
                'validated' => $pengurusValidated,
                'total' => $pengurusAll,
            ];
            $countAnggota = [
                'requested' => $anggotaRequested,
                'rejected' => $anggotaRejected,
                'accepted' => $anggotaAccepted,
                'empty' => $anggotaEmpty,
                'validated' => $anggotaValidated,
                'total' => $anggotaAll,
            ];

            $listCount = [
                'pengurus' => $countPengurus,
                'anggota' => $countAnggota,
            ];

            return
                response()->json(
                    [
                        'status' => 200,
                        'data' => $listCount,
                    ],
                );
        } else {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
    }

    public function getListValidation(Request $request)
    {
        $user = auth()->user();
        if ($user->is_petugas) {
            $request->validate([
                'partisipan_type' => [new Enum(PartisipanEnum::class)],
                'validation_type' => [new Enum(ValidationEnum::class)],
            ]);
            $jabatanAnggota = Jabatan::where('name', 'Anggota Magang')->firstOrFail();

            $partisipanRequest = ScheduleRequest::join('partisipans', 'partisipans.id', '=', 'partisipan_id')
                ->join('jabatans', 'jabatans.id', '=', 'partisipans.jabatan_id');

            $partisipanType = $request->partisipan_type;
            $pengurus = 'pengurus';
            $anggota = 'anggota';

            if ($partisipanType == $pengurus) {
                $partisipanRequest = $partisipanRequest->where('jabatans.id', '!=', $jabatanAnggota->id);
            } else if ($partisipanType == $anggota) {
                $partisipanRequest = $partisipanRequest->where('jabatans.id', $jabatanAnggota->id);
            }

            switch ($request->validation_type) {
                case 'requested':
                    $partisipanRequest = $partisipanRequest->where('status', '=', StatusEnum::Requested)
                        ->get(['schedule_requests.id', 'partisipans.name as name']);
                    break;
                case 'rejected':
                    $partisipanRequest = $partisipanRequest->where('status', '=', StatusEnum::Rejected)
                        ->get(['schedule_requests.id', 'partisipans.name as name']);
                    break;
                case 'accepted':
                    $partisipanRequest = $partisipanRequest->where('status', '=', StatusEnum::Accepted)
                        ->get(['schedule_requests.id', 'partisipans.name as name']);
                    break;
                case 'empty':
                    $partisipanRequest = $partisipanRequest->where('status', '=', null)
                        ->get(['schedule_requests.id', 'partisipans.name as name']);
                    break;
                case 'validated':
                    $partisipanRequest = $partisipanRequest->where('status', '!=', StatusEnum::Requested)
                        ->where('status', '!=', null)
                        ->get(['schedule_requests.id', 'partisipans.name as name']);
                    break;
                case 'all':
                    $partisipanRequest = $partisipanRequest
                        ->get(['schedule_requests.id', 'partisipans.name as name']);
                    break;
                default:
                    $partisipanRequest = $partisipanRequest
                        ->get(['schedule_requests.id', 'partisipans.name as name']);
                    break;
            }

            $result = [
                'validation_type' => $request->validation_type,
                'total' => $partisipanRequest->count(),
                'list_request' => $partisipanRequest
            ];

            return
                response()->json(
                    [
                        'status' => 200,
                        'data' => $result,
                    ],
                );
        } else {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
    }

    public function getDetailValidation($id)
    {
        $user = auth()->user();
        if ($user->is_petugas) {
            $dataScheduleRequest = ScheduleRequest::where('id', $id)->firstOrFail();

            if ($dataScheduleRequest->petugas_id != null) {
                $petugasName = $dataScheduleRequest->petugas->name;
            }
            $bukti = $dataScheduleRequest->bukti != null
                ? url('/file') . '/' . $dataScheduleRequest->bukti
                : null;

            $listScheduleCandidate = $dataScheduleRequest->scheduleCandidate;

            $listSessionId = [];
            foreach ($listScheduleCandidate as $scheduleCandidate) {
                array_push($listSessionId, (int) $scheduleCandidate->session_id);
            }

            $partisipan = $dataScheduleRequest->partisipan;

            $avatarUrl = $partisipan->avatar_url != null
                ? url('/image') . '/' . $partisipan->avatar_url
                : null;

            $partisipan = [
                'name' => $partisipan->name,
                'jabatan' => $partisipan->jabatan->name,
                'member_id' => $partisipan->member_id,
                'phone' => $partisipan->phone_number,
                'avatar_url' => $avatarUrl,
            ];

            $resultData = [
                'id' => $id,
                'partisipan' => $partisipan,
                'petugas_name' => $petugasName ?? null,
                'status' => $dataScheduleRequest->status,
                'validate_at' => $dataScheduleRequest->tanggal_validasi,
                'partisipan_notes' => $dataScheduleRequest->catatan_partisipan,
                'petugas_notes' => $dataScheduleRequest->catatan_petugas,
                'bukti' => $bukti,
                'list_session_id' => $listSessionId,

            ];

            return
                response()->json(
                    [
                        'status' => 200,
                        'data' => $resultData,
                    ],
                );
        } else {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
    }

    public function rejectScheduleRequest(Request $request)
    {
        $user = auth()->user();
        if ($user->is_petugas) {
            $dataScheduleRequest = ScheduleRequest::where('id', $request->id)->firstOrFail();
            $dataScheduleRequest->status = StatusEnum::Rejected;
            $dataScheduleRequest->catatan_petugas = $request->petugas_notes;
            $dataScheduleRequest->tanggal_validasi = date('Y-m-d H:i:s');
            $dataScheduleRequest->petugas_id = $user->petugas->id;
            $dataScheduleRequest->save();
            return
                response()->json(
                    [
                        'status' => 200,
                        'message' => 'Jadwal sedia partisipan ditolak.',
                    ],
                );
        } else {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
    }

    public function acceptScheduleRequest($id)
    {
        $user = auth()->user();
        if ($user->is_petugas) {
            $dataScheduleRequest = ScheduleRequest::where('id', $id)->firstOrFail();
            $dataScheduleRequest->status = StatusEnum::Accepted;
            $dataScheduleRequest->catatan_petugas = null;
            $dataScheduleRequest->tanggal_validasi = date('Y-m-d H:i:s');
            $dataScheduleRequest->petugas_id = $user->petugas->id;
            $dataScheduleRequest->save();

            return
                response()->json(
                    [
                        'status' => 200,
                        'message' => 'Jadwal sedia partisipan diterima.',
                    ],
                );
        } else {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
    }
}
