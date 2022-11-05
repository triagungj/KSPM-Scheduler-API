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
            $pertemuans = Pertemuan::orderBy('name', 'asc')->get();

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

            $imagePath = url('/image') . '/';

            foreach ($listPartisipans as $partisipan) {
                if ($partisipan->avatar_url != null) {
                    $partisipan->avatar_url = $imagePath . $partisipan->avatar_url;
                }
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

        if ($admin) {
            $partisipanNull = DB::table('partisipans')
                ->where('jabatan_id', '=', null)
                ->count();
            if ($partisipanNull > 0) {
                return response()->json(
                    [
                        'status' => 403,
                        'message' => 'Terdapat Partisipan yang belum mengisi data Jabatan.',
                    ],
                    403
                );
            }

            $totalAccepted = DB::table('schedule_requests')->where('status', '=', 'accepted')->count();
            $totalPartisipan = DB::table('partisipans')->count();
            if ($totalAccepted < $totalPartisipan) {
                return response()->json(
                    [
                        'status' => 403,
                        'message' => 'Terdapat ajuan Jadwal yang belum diterima petugas.',
                    ],
                    403
                );
            }

            $population = 2;
            $pertemuans = Pertemuan::all();
            $sesisCount = Sesi::all()->count();
            $individu = [];

            $totalStaff = DB::table('partisipans')
                ->join('jabatans', 'jabatans.id', '=', 'partisipans.jabatan_id')
                ->join('jabatan_categories', 'jabatan_categories.id', '=', 'jabatans.jabatan_category_id')
                ->where('jabatan_categories.name', '=', 'Staff')
                ->count();
            $totalAnggota = DB::table('partisipans')
                ->join('jabatans', 'jabatans.id', '=', 'partisipans.jabatan_id')
                ->join('jabatan_categories', 'jabatan_categories.id', '=', 'jabatans.jabatan_category_id')
                ->where('jabatan_categories.name', '=', 'Anggota')
                ->count();

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
                                'partisipans.name as partisipan_name',
                                'schedule_candidates.id as schedule_candidate_id',
                                'jabatan_categories.name as jabatan_category',
                                'session_id'
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
                                    if ($staff < $totalStaff / ($sesisCount / 2)) {
                                        $staff++;
                                        array_push($sessionSchedule, $scheduleCandidate);
                                    }
                                } else {
                                    if ($anggota < $totalAnggota / ($sesisCount / 2)) {
                                        $anggota++;
                                        array_push($sessionSchedule, $scheduleCandidate);
                                    }
                                }
                            }
                        }
                        $isPengurusIntiFit = $pengurusInti >= 1 && $pengurusInti <= 2;
                        $isStaffFit = $staff <= $totalStaff / ($sesisCount / 2);
                        $isAnggotaFit = $anggota <= $totalAnggota / ($sesisCount / 2);


                        if ($isPengurusIntiFit && $isStaffFit && $isAnggotaFit) {
                            $fitnessTotal++;
                        }
                    }
                }

                $fitness = $fitnessTotal != null ? (float) $fitnessTotal / $sesisCount : null;

                array_push($individu, [
                    'fitness' => $fitness,
                    'fitness_total' => $fitnessTotal,
                    'schedule' => $sessionSchedule,
                ]);
            }
            $temp = $individu[0];

            foreach ($individu as $cromosom) {
                if ($cromosom['fitness'] >= $temp['fitness']) {
                    $temp = $cromosom;
                }
            }

            $collection = DB::table('sesis')
                ->selectRaw('count(id) as total, hari')
                ->groupBy('hari')
                ->get();
            $result = [];
            foreach ($collection as $data) {
                $listSesi = Sesi::where('hari', $data->hari)->get();
                $listDetailSesi = [];
                foreach ($listSesi as $sesi) {
                    $pengurus = [];
                    $anggota = [];
                    foreach ($temp['schedule'] as $schedule) {
                        if ($schedule->session_id == $sesi->id) {
                            if ($schedule->jabatan_category == 'Anggota') {
                                array_push($anggota, $schedule);
                            } else {
                                array_push($pengurus, $schedule);
                            }
                        }
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

            return
                response()->json(
                    [
                        'status' => 200,
                        'message' => 'Generate jadwal berhasil',
                        'total' => count($individu),
                        'data' => $result,
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

        if ($admin) {
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
                    $pengurus =
                        DB::table('schedules')
                        ->select(['schedule_candidates.id as schedule_candadate_id', 'partisipans.name as partisipan_name'])
                        ->join('schedule_candidates', 'schedule_candidates.id', '=', 'schedules.schedule_candidate_id')
                        ->join('schedule_requests', 'schedule_requests.id', '=', 'schedule_candidates.schedule_request_id')
                        ->join('sesis', 'sesis.id', '=', 'schedule_candidates.session_id')
                        ->join('partisipans', 'partisipans.id', '=', 'schedule_requests.partisipan_id')
                        ->join('jabatans', 'jabatans.id', '=', 'partisipans.jabatan_id')
                        ->where('sesis.id', '=', $sesi->id)
                        ->where('jabatans.name', '!=', 'Anggota Magang')
                        ->get();
                    $anggota =
                        DB::table('schedules')
                        ->select(['schedule_candidates.id as schedule_candadate_id', 'partisipans.name as partisipan_name'])
                        ->join('schedule_candidates', 'schedule_candidates.id', '=', 'schedules.schedule_candidate_id')
                        ->join('schedule_requests', 'schedule_requests.id', '=', 'schedule_candidates.schedule_request_id')
                        ->join('sesis', 'sesis.id', '=', 'schedule_candidates.session_id')
                        ->join('partisipans', 'partisipans.id', '=', 'schedule_requests.partisipan_id')
                        ->join('jabatans', 'jabatans.id', '=', 'partisipans.jabatan_id')
                        ->where('sesis.id', '=', $sesi->id)
                        ->where('jabatans.name', '=', 'Anggota Magang')
                        ->get();
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

    public function submitSchedule(Request $request)
    {
        $user = auth()->user();
        $admin =
            Admin::where('username', $user->username)->first();

        if ($admin) {
            $partisipanNull = DB::table('partisipans')
                ->where('jabatan_id', '=', null)
                ->count();
            if ($partisipanNull > 0) {
                return response()->json(
                    [
                        'status' => 403,
                        'message' => 'Terdapat Partisipan yang belum mengisi data Jabatan.',
                    ],
                    403
                );
            }

            $totalAccepted = DB::table('schedule_requests')->where('status', '=', StatusEnum::Accepted)->count();
            $totalStaff = DB::table('partisipans')
                ->join('jabatans', 'jabatans.id', '=', 'partisipans.jabatan_id')
                ->join('jabatan_categories', 'jabatan_categories.id', '=', 'jabatans.jabatan_category_id')
                ->where('jabatan_categories.name', '=', 'Staff')
                ->count();
            $totalAnggota = DB::table('partisipans')
                ->join('jabatans', 'jabatans.id', '=', 'partisipans.jabatan_id')
                ->join('jabatan_categories', 'jabatan_categories.id', '=', 'jabatans.jabatan_category_id')
                ->where('jabatan_categories.name', '=', 'Anggota')
                ->count();
            if ($totalAccepted < $totalStaff + $totalAnggota) {
                return response()->json(
                    [
                        'status' => 403,
                        'message' => 'Terdapat ajuan Jadwal yang belum diterima petugas.',
                    ],
                    403
                );
            }
            DB::table('schedules')->delete();
            $listSession = $request->input('schedule_candidates');
            $listSessionId = [];
            foreach ($listSession as $sessionRequest) {
                $session = [
                    'id' => Str::uuid(),
                    'schedule_candidate_id' => $sessionRequest,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ];
                array_push($listSessionId, $session);
            }
            Schedule::insert($listSessionId);
            return response()->json(
                [
                    'status' => 200,
                    'message' => 'Jadwal Berhasil Diterbitkan'
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
            DB::table('schedules')->delete();
            DB::table('schedule_candidates')->delete();
            DB::table('schedule_requests')
                ->update(['status' => null, 'bukti' => null, 'petugas_id' => null]);


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
