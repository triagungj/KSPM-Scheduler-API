<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Enum\PartisipanEnum;
use App\Models\Enum\StatusEnum;
use App\Models\Pertemuan;
use App\Models\Schedule;
use App\Models\ScheduleCandidate;
use App\Models\Sesi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Enum;

class ScheduleController extends Controller
{
    public function getListMySchedule()
    {
        $user = auth()->user();
        if (!$user->is_petugas) {
            $listMySchedule = DB::table('schedules')
                ->select(['sesis.*', 'pertemuans.name as pertemuan'])
                ->join('schedule_candidates', 'schedule_candidates.id', '=', 'schedules.schedule_candidate_id')
                ->join('schedule_requests', 'schedule_requests.id', '=', 'schedule_candidates.schedule_request_id')
                ->join('partisipans', 'partisipans.id', '=', 'schedule_requests.partisipan_id')
                ->join('sesis', 'sesis.id', '=', 'schedule_candidates.session_id')
                ->join('pertemuans', 'pertemuans.id', '=', 'sesis.pertemuan_id')
                ->where('partisipans.id', '=', $user->partisipan->id)
                ->get();

            $data = [
                'name' => $user->partisipan->name,
                'schedule_status' => $user->partisipan->scheduleRequest->status,
                'schedules' => $listMySchedule,
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
    public function getListSchedule()
    {
        if (auth()) {
            $pertemuans = Pertemuan::all();

            $schedule = Schedule::first();

            $lastUpdate = DB::table('schedules')->latest('updated_at')->first();

            if ($schedule == null) {
                $data = null;
            } else {
                $data = [
                    'last_update' => $lastUpdate->updated_at,
                    'published' => true,
                    'list_schedule' => $pertemuans,
                ];
            }

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

    public function getListDetailSchedule($id)
    {
        if (auth()) {
            $result = [];
            $detailResult = [];
            $collection = DB::table('sesis')
                ->selectRaw('count(id) as total, hari')
                ->where('sesis.pertemuan_id', '=', $id)
                ->groupBy('hari')
                ->get();
            foreach ($collection as $data) {
                $sesi = Sesi::where('hari', $data->hari)->where('pertemuan_id', $id)->get();
                $detailResult = [
                    'hari' => $data->hari,
                    'list_session' => $sesi
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

    public function getDetailSchedule(Request $request)
    {
        if (auth()) {
            $request->validate([
                'partisipan_type' => [new Enum(PartisipanEnum::class)],
            ]);

            $sesiId = $request->session_id;
            $sesi = Sesi::join('pertemuans', 'pertemuans.id', '=', 'sesis.pertemuan_id')
                ->where('sesis.id', $sesiId)->get(['sesis.*', 'pertemuans.name as pertemuan'])->firstOrFail();

            if ($request->partisipan_type == 'anggota') {
                $listPartisipans = DB::table('schedules')
                    ->select(['partisipans.*', 'jabatans.name as jabatan'])
                    ->join('schedule_candidates', 'schedule_candidates.id', '=', 'schedules.schedule_candidate_id')
                    ->join('schedule_requests', 'schedule_requests.id', '=', 'schedule_candidates.schedule_request_id')
                    ->join('partisipans', 'partisipans.id', '=', 'schedule_requests.partisipan_id')
                    ->join('jabatans', 'jabatans.id', '=', 'partisipans.jabatan_id')
                    ->join('jabatan_categories', 'jabatan_categories.id', '=', 'jabatans.jabatan_category_id')
                    ->join('sesis', 'sesis.id', '=', 'schedule_candidates.session_id')
                    ->where('sesis.id', '=', $sesiId)
                    ->where('jabatan_categories.name', '=', 'Anggota')
                    ->get();
            } else {
                $listPartisipans = DB::table('schedules')
                    ->select(['partisipans.*', 'jabatans.name as jabatan'])
                    ->join('schedule_candidates', 'schedule_candidates.id', '=', 'schedules.schedule_candidate_id')
                    ->join('schedule_requests', 'schedule_requests.id', '=', 'schedule_candidates.schedule_request_id')
                    ->join('partisipans', 'partisipans.id', '=', 'schedule_requests.partisipan_id')
                    ->join('jabatans', 'jabatans.id', '=', 'partisipans.jabatan_id')
                    ->join('jabatan_categories', 'jabatan_categories.id', '=', 'jabatans.jabatan_category_id')
                    ->join('sesis', 'sesis.id', '=', 'schedule_candidates.session_id')
                    ->where('sesis.id', '=', $sesiId)
                    ->where('jabatan_categories.name', '!=', 'Anggota')
                    ->get();
            }

            $sesiCursor = Sesi::find($sesiId);

            $data = [
                'details' => $sesi,
                'list_partisipan' => $listPartisipans,
                'prev' => $sesiCursor->previous() != null,
                'next' => $sesiCursor->next() != null,
            ];
            return response()->json(
                [
                    'status' => 200,
                    'data' => $data,
                ],
            );
        } else {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
    }

    public function generateSchedule()
    {
        $user = auth()->user();
        $admin =
            Admin::where('username', $user->username)->first();

        if ($admin || $user->petugas->is_superuser) {
            $population = 2;

            Schedule::truncate();

            $pertemuans = Pertemuan::all();
            $sesis = Sesi::all();

            $totalStaff = DB::table('partisipans')
                ->join('jabatans', 'jabatans.id', '=', 'partisipans.jabatan_id')
                ->join('jabatan_categories', 'jabatan_categories.id', '=', 'jabatans.jabatan_category_id')
                ->where('jabatan_categories.name', '=', 'Staff')
                ->get();
            $totalAnggota = DB::table('partisipans')
                ->join('jabatans', 'jabatans.id', '=', 'partisipans.jabatan_id')
                ->join('jabatan_categories', 'jabatan_categories.id', '=', 'jabatans.jabatan_category_id')
                ->where('jabatan_categories.name', '=', 'Anggota')
                ->get();

            $individu = [];

            for ($i = 0; $i < $population; $i++) {
                $sessionSchedule = [];
                $fitnessTotal = 0;

                foreach ($pertemuans as $pertemuan) {
                    $sessions = Sesi::where('pertemuan_id', $pertemuan->id)->get();

                    foreach ($sessions as $session) {
                        $pengurusInti = 0;
                        $staff = 0;
                        $anggota = 0;

                        $scheduleCandidates = DB::table('schedule_candidates')
                            ->select([
                                'schedule_requests.*',
                                'schedule_candidates.id as schedule_candidate_id',
                                'jabatan_categories.name as jabatan_category',
                            ])
                            ->join('schedule_requests', 'schedule_requests.id', '=', 'schedule_candidates.schedule_request_id')
                            ->join('partisipans', 'partisipans.id', '=', 'schedule_requests.partisipan_id')
                            ->join('jabatans', 'jabatans.id', '=', 'partisipans.jabatan_id')
                            ->join('jabatan_categories', 'jabatan_categories.id', '=', 'jabatans.jabatan_category_id')
                            ->where('session_id', '=', $session->id)
                            ->where('status', '=', StatusEnum::Accepted)
                            ->inRandomOrder()
                            ->get();

                        foreach ($scheduleCandidates as $scheduleCandidate) {
                            $random = rand(true, false);
                            if ($random) {
                                if ($scheduleCandidate->jabatan_category == 'Pengurus Inti') {
                                    if ($pengurusInti < 2) {
                                        $pengurusInti++;
                                        array_push($sessionSchedule, $scheduleCandidate);
                                    }
                                } else if ($scheduleCandidate->jabatan_category == 'Staff') {
                                    if ($staff < count($totalStaff) / (count($sesis) / 2)) {
                                        $staff++;
                                        array_push($sessionSchedule, $scheduleCandidate);
                                    }
                                } else {
                                    if ($anggota < count($totalAnggota) / (count($sesis) / 2)) {
                                        $anggota++;
                                        array_push($sessionSchedule, $scheduleCandidate);
                                    }
                                }
                            }
                        }
                        $isPengurusIntiFit = $pengurusInti >= 1 && $pengurusInti <= 2;
                        $isStaffFit = $staff <= count($totalStaff) / (count($sesis) / 2);
                        $isAnggotaFit = $anggota <= count($totalAnggota) / (count($sesis) / 2);


                        if ($isPengurusIntiFit && $isStaffFit && $isAnggotaFit) {
                            $fitnessTotal++;
                        }
                    }
                }

                $fitness = (float) $fitnessTotal / count($sesis);

                array_push($individu, [
                    'fitness' => $fitness,
                    'fitness_total' => $fitnessTotal,
                    'schedule' => $sessionSchedule,
                ]);
            }

            // $allSchedule = Schedule::join('schedule_candidates', 'schedule_candidates.id', '=', 'schedule_candidate_id')
            //     ->get(['schedule_candidates.*']);
            $temp = $individu[0];

            foreach ($individu as $cromosom) {
                if ($cromosom['fitness'] >= $temp['fitness']) {
                    $temp = $cromosom;
                }
            }

            foreach ($temp['schedule'] as $schedule) {
                Schedule::create([
                    'id' => Str::uuid(),
                    'schedule_candidate_id' => $schedule->schedule_candidate_id,
                ]);
            }

            return
                response()->json(
                    [
                        'status' => 200,
                        'message' => 'Berhasil Mengatur Ulang Jadwal',
                        'total' => count($individu),
                        // 'data' => $temp,
                    ],
                );
        } else {
            return response()->json(['message' => 'Tidak memiliki akses'], 401);
        }
    }

    public function getAllSchedule()
    {
        $user = auth()->user();
        $admin =
            Admin::where('username', $user->username)->first();

        if ($admin || $user->petugas->is_superuser) {
            $result = [];
            $detailResult = [];

            $lastUpdate = DB::table('schedules')->latest('updated_at')->first();
            $collection = DB::table('sesis')
                ->selectRaw('count(id) as total, hari')
                ->groupBy('hari')
                ->get();
            foreach ($collection as $data) {
                $listSesi = Sesi::where('hari', $data->hari)->get();
                $listDetailSesi = [];
                foreach ($listSesi as $sesi) {
                    $pengurus = [];
                    $anggota = [];
                    $listPengurus =
                        DB::table('schedules')
                        ->select(['partisipans.name'])
                        ->join('schedule_candidates', 'schedule_candidates.id', '=', 'schedules.schedule_candidate_id')
                        ->join('schedule_requests', 'schedule_requests.id', '=', 'schedule_candidates.schedule_request_id')
                        ->join('sesis', 'sesis.id', '=', 'schedule_candidates.session_id')
                        ->join('partisipans', 'partisipans.id', '=', 'schedule_requests.partisipan_id')
                        ->join('jabatans', 'jabatans.id', '=', 'partisipans.jabatan_id')
                        ->where('sesis.id', '=', $sesi->id)
                        ->where('jabatans.name', '!=', 'Anggota Magang')
                        ->get();
                    $listAnggota =
                        DB::table('schedules')
                        ->select(['partisipans.name'])
                        ->join('schedule_candidates', 'schedule_candidates.id', '=', 'schedules.schedule_candidate_id')
                        ->join('schedule_requests', 'schedule_requests.id', '=', 'schedule_candidates.schedule_request_id')
                        ->join('sesis', 'sesis.id', '=', 'schedule_candidates.session_id')
                        ->join('partisipans', 'partisipans.id', '=', 'schedule_requests.partisipan_id')
                        ->join('jabatans', 'jabatans.id', '=', 'partisipans.jabatan_id')
                        ->where('sesis.id', '=', $sesi->id)
                        ->where('jabatans.name', '=', 'Anggota Magang')
                        ->get();

                    foreach ($listPengurus as $dataPartisipan) {
                        array_push($pengurus, $dataPartisipan->name);
                    }
                    foreach ($listAnggota as $dataAnggota) {
                        array_push($anggota, $dataAnggota->name);
                    }
                    $detailSesi = [
                        'name' => $sesi->name,
                        'waktu' => $sesi->waktu,
                        'pengurus' => $pengurus,
                        'anggota' => $anggota
                    ];
                    array_push($listDetailSesi, $detailSesi);
                }
                $detailResult = [
                    'hari' => $data->hari,
                    'list_sesi' => $listDetailSesi,
                ];
                array_push($result, $detailResult);
            }

            return response()->json(
                [
                    'status' => 200,
                    'last_update' => $lastUpdate != null ? $lastUpdate->updated_at . 'Z' : null,
                    'data' => $lastUpdate != null ?  $result : [],
                ],
            );
        } else {
            return response()->json(['message' => 'Tidak memiliki akses'], 401);
        }
    }

    public function resetSchedule()
    {
        $user = auth()->user();
        $admin =
            Admin::where('username', $user->username)->first();

        if ($admin) {
            ScheduleCandidate::where('id', 'like', '%%')->delete();
            DB::table('schedule_requests')
                ->update(['status' => null]);


            return response()->json(
                [
                    'status' => 200,
                    'message' => "Berhasil mengatur ulang periode!"
                ],
            );
        } else {
            return response()->json(['message' => 'Tidak memiliki akses'], 401);
        }
    }
}
