<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Enum\PartisipanEnum;
use App\Models\Enum\StatusEnum;
use App\Models\Partisipan;
use App\Models\Pertemuan;
use App\Models\Schedule;
use App\Models\ScheduleCandidate;
use App\Models\Sesi;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
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

    function getScheduleCandidates()
    {
        $candidates =  DB::table('schedule_candidates')
            ->select([
                'schedule_candidates.id as schedule_candidate_id',
                'session_id as sesi_id',
                'sesis.pertemuan_id as pertemuan_id',
                'partisipans.id as partisipan_id',
                'partisipans.name as partisipan_name',
                'jabatan_categories.name as jabatan_category',
                'jabatans.jabatan_category_id'
            ])
            ->join('sesis', 'schedule_candidates.session_id', '=', 'sesis.id')
            ->join('schedule_requests', 'schedule_requests.id', '=', 'schedule_candidates.schedule_request_id')
            ->join('partisipans', 'partisipans.id', '=', 'schedule_requests.partisipan_id')
            ->join('jabatans', 'jabatans.id', '=', 'partisipans.jabatan_id')
            ->join('jabatan_categories', 'jabatan_categories.id', '=', 'jabatans.jabatan_category_id')
            ->where('status', '=', StatusEnum::Accepted)
            ->orderBy('sesis.id')
            ->get();
        return $candidates;
    }

    function getMaxPartisipanSesi()
    {
        $allPertemuan = Pertemuan::all();
        $allSesi = Sesi::all();

        $rowListPartisipanTotal = DB::table('partisipans')
            ->select('jabatan_categories.id', 'jabatan_categories.name', DB::raw('count(*) as total'))
            ->join('jabatans', 'jabatans.id', '=', 'partisipans.jabatan_id')
            ->join('jabatan_categories', 'jabatan_categories.id', '=', 'jabatans.jabatan_category_id')
            ->groupBy('jabatan_categories.name')
            ->get();

        $listPartisipanTotal = new Collection();
        foreach ($rowListPartisipanTotal as $rowPartisipanTotal) {
            $listPartisipanTotal->push((object)[
                'id' => $rowPartisipanTotal->id,
                'name' => $rowPartisipanTotal->name,
                'total' => $rowPartisipanTotal->total,
                'min' => ceil($rowPartisipanTotal->total / ($allSesi->count() / $allPertemuan->count()) / 2),
                'max' => ceil($rowPartisipanTotal->total / ($allSesi->count() / $allPertemuan->count())),
            ]);
        }

        return $listPartisipanTotal;
    }

    function createIndividu(Collection $newCandidates, $allPertemuan, $allSesi, $allPartisipan, $minMaxPartisipan, $maxFitness)
    {
        $fitnessTotal = 0;

        foreach ($allPertemuan as $pertemuan) {
            // CHECK DUPLIKASI PARTISIPAN 
            foreach ($allPartisipan as $partisipan) {
                $partisipanCount = $newCandidates->whereIn('push', true)->whereIn('schedule.partisipan_id', $partisipan->id)
                    ->whereIn('schedule.pertemuan_id', $pertemuan->id)->count();
                if ($partisipanCount == 1) {
                    $fitnessTotal++;
                }
            }

            // HITUNG TOTAL PARTISIPAN DENGAN MASING-MASING JABATAN DALAM SETIAP SESI
            $sesiPertemuan = $allSesi->whereIn('pertemuan_id', $pertemuan->id)->all();
            foreach ($sesiPertemuan as $sesi) {
                $listTotalPartisipan = new Collection();
                foreach ($minMaxPartisipan as $partisipanTotal) {
                    $listTotalPartisipan->push((object)[
                        'id' => $partisipanTotal->id,
                        'name' => $partisipanTotal->name,
                        'total' => 0,
                    ]);
                }

                $sesiSchedules = $newCandidates->whereIn('push', true)->whereIn('schedule.sesi_id', $sesi->id)->all();
                foreach ($sesiSchedules as $sesiSchedule) {
                    $tempSesi = $listTotalPartisipan->firstWhere('id', $sesiSchedule->schedule->jabatan_category_id);
                    $tempSesi->total++;
                }
                foreach ($minMaxPartisipan as $partisipanTotal) {
                    $totalPartisipanSesi = $listTotalPartisipan->whereIn('id', $partisipanTotal->id)->first();
                    if (
                        $totalPartisipanSesi->total <= $partisipanTotal->max &&
                        $totalPartisipanSesi->total >= $partisipanTotal->min
                    ) {
                        $fitnessTotal++;
                    }
                }
            }
        }
        $fitnessValue = $fitnessTotal / $maxFitness;
        return (object)[
            'fitness' => $fitnessValue,
            'fitness_total' => $fitnessTotal,
            'schedule' => $newCandidates,
        ];
    }

    public function generateSchedule()
    {
        $user = auth()->user();
        $admin = Admin::where('username', $user->username)->first();
        if ($admin) {
            // INISIASI RULES
            $totalPopulation = 20;
            $mutationRate = 0.5;

            $allPertemuan = clone Pertemuan::all();
            $allSesi = clone Sesi::all();
            $allPartisipan = clone Partisipan::all();

            $minMaxPartisipan = clone $this->getMaxPartisipanSesi();
            // return $minMaxPartisipan;

            $maxFitness = ($allSesi->count() * $minMaxPartisipan->count()) + ($allPartisipan->count() * $allPertemuan->count());
            // $maxFitness = $allSesi->count() * $minMaxPartisipan->count();


            // MENGAMBIL JADWAL KANDIDAT DARI DATABASE
            $listScheduleCandidates = clone $this->getScheduleCandidates();
            $cutPoint = $listScheduleCandidates->whereIn('pertemuan_id', $listScheduleCandidates->first()->pertemuan_id)->count();

            // INISIASI POPULASI AWAL
            $population = new Collection();

            for ($i = 0; $i < $totalPopulation; $i++) {
                $newCandidates = new Collection();

                // Memasukan Jadwal ke dalam Setiap Individu pada Populasi
                foreach ($listScheduleCandidates as $scheduleCandidate) {
                    $randomValue = rand(true, false);

                    $newCandidates->push((object)[
                        'push' => $randomValue, // true = dimasukkan
                        'schedule' => $scheduleCandidate,
                    ]);
                }
                $population->push($this->createIndividu(
                    $newCandidates,
                    $allPertemuan,
                    $allSesi,
                    $allPartisipan,
                    $minMaxPartisipan,
                    $maxFitness
                ));
            }

            // $temp = $population->sortBy('fitness', SORT_NUMERIC, true)->pluck('fitness')->toArray();
            // return $population[0];


            $isLooping = true;
            set_time_limit(0);
            $count = 0;
            $population = $population->sortBy('fitness', SORT_NUMERIC, true);
            while ($isLooping) {
                $scheduleChild1 = new Collection();
                $scheduleChild2 = new Collection();

                $newPopulation = $population->values();

                $parent1 = clone $newPopulation[0]->schedule;
                $randomIndex = rand(1, ceil($population->count() / 2) - 1);
                $parent2 = clone $newPopulation[$randomIndex]->schedule;


                // $offspring1Parent1 = clone $parent1;
                // $offspring2Parent1 = $offspring1Parent1->splice($cutPoint);

                // $offspring1Parent2 = clone $parent2;
                // $offspring2Parent2 = $offspring1Parent2->splice($cutPoint);

                // $scheduleChild1 = new Collection();
                // $scheduleChild2 = new Collection();

                // foreach ($offspring1Parent1 as $offspring) {
                //     $scheduleChild1->push($offspring);
                // }
                // foreach ($offspring2Parent2 as $offspring) {
                //     $scheduleChild1->push($offspring);
                // }

                // foreach ($offspring1Parent2 as $offspring) {
                //     $scheduleChild2->push($offspring);
                // }

                // foreach ($offspring2Parent1 as $offspring) {
                //     $scheduleChild2->push($offspring);
                // }

                // for ($i = 1; $i <= $listScheduleCandidates->count(); $i++) {
                //     if ($i % 2 != 0) {
                //         $scheduleChild1->push($parent1[$i - 1]);
                //         $scheduleChild2->push($parent2[$i - 1]);
                //     } else {
                //         $scheduleChild1->push($parent2[$i - 1]);
                //         $scheduleChild2->push($parent1[$i - 1]);
                //     }
                // }

                $counter = 0;
                foreach ($allSesi as $sesi) {
                    $counter++;
                    $scheduleSesiChild1 = $parent1->whereIn('schedule.sesi_id', $sesi->id);
                    $scheduleSesiChild2 = $parent2->whereIn('schedule.sesi_id', $sesi->id);

                    if ($counter % 2 != 0) {
                        foreach ($scheduleSesiChild1 as $sesiSchedule) {
                            $scheduleChild1->push($sesiSchedule);
                        }
                        foreach ($scheduleSesiChild2 as $sesiSchedule) {
                            $scheduleChild2->push($sesiSchedule);
                        }
                    } else {
                        foreach ($scheduleSesiChild1 as $sesiSchedule) {
                            $scheduleChild2->push($sesiSchedule);
                        }
                        foreach ($scheduleSesiChild2 as $sesiSchedule) {
                            $scheduleChild1->push($sesiSchedule);
                        }
                    }
                }

                // return response()->json([
                //     'parent_1' => [
                //         'total_parent_1' => $parent1->count(),
                //         'parent_1' => $parent1,
                //     ],
                //     // 'offspring_parent_1' => [
                //     //     'total_off_spring_1' => $offspring1Parent1->count(),
                //     //     'off_spring_1' => $offspring1Parent1,
                //     //     'total_off_spring_2' => $offspring2Parent1->count(),
                //     //     'off_spring_2' => $offspring2Parent1,
                //     // ],
                //     'parent_2' => [
                //         'total_parent_2' => $parent2->count(),
                //         'parent_2' => $parent2
                //     ],
                //     // 'offspring_parent_2' => [
                //     //     'total_off_spring_1' => $offspring1Parent2->count(),
                //     //     'off_spring_1' => $offspring1Parent2,
                //     //     'total_off_spring_2' => $offspring2Parent2->count(),
                //     //     'off_spring_2' => $offspring2Parent2,
                //     // ],
                //     'child_1' => $scheduleChild1,
                //     'child_2' => $scheduleChild2,
                // ]);

                foreach ($scheduleChild1 as $scheduleChild) {
                    $random = rand(0, 100) / 100;
                    if ($random <= $mutationRate) {
                        $scheduleChild->push = !$scheduleChild->push;
                    }
                    // if ($random < $mutationRate) {
                    //     $countPartisipan = $scheduleChild1->whereIn('push', true)->whereIn('schedule.partisipan_id', $scheduleChild->schedule->partisipan_id)
                    //         ->whereIn('schedule.pertemuan_id', $scheduleChild->schedule->pertemuan_id)->count();
                    //     if ($countPartisipan > 1) {
                    //         $scheduleChild->push = false;
                    //     } else {
                    //         $scheduleChild->push = true;
                    //     }
                    // }
                }
                foreach ($scheduleChild2 as $scheduleChild) {
                    $random = rand(0, 100) / 100;

                    if ($random <= $mutationRate) {
                        $scheduleChild->push = !$scheduleChild->push;
                    }

                    // if ($random < $mutationRate) {
                    //     $countPartisipan = $scheduleChild2->whereIn('push', true)->whereIn('schedule.partisipan_id', $scheduleChild->schedule->partisipan_id)
                    //         ->whereIn('schedule.pertemuan_id', $scheduleChild->schedule->pertemuan_id)->count();
                    //     if ($countPartisipan > 1) {
                    //         $scheduleChild->push = false;
                    //     } else {
                    //         $scheduleChild->push = true;
                    //     }
                    // }
                }
                $population->push($this->createIndividu(
                    $scheduleChild1,
                    $allPertemuan,
                    $allSesi,
                    $allPartisipan,
                    $minMaxPartisipan,
                    $maxFitness
                ));
                $population->push($this->createIndividu(
                    $scheduleChild2,
                    $allPertemuan,
                    $allSesi,
                    $allPartisipan,
                    $minMaxPartisipan,
                    $maxFitness
                ));

                $population = $population->sortBy('fitness', SORT_NUMERIC, true);
                $population = $population->splice(0, -2);

                // $fitCount = $population->whereBetween('fitness', [0.9, 1])->count();
                $fitCount = $population->whereIn('fitness_total', $maxFitness)->count();
                if ($fitCount > 0) {
                    $isLooping = false;
                }

                $count++;
                if ($count == 100) {
                    $isLooping = false;
                }
            }

            // $population = $population->sortBy('fitness', SORT_REGULAR, true)->values();
            // $temp = $population->sortBy('fitness', SORT_NUMERIC, true)->pluck('fitness')->toArray();
            // return response()->json([
            //     'total' => $listScheduleCandidates->count(),
            //     'data' => $cutPoint,
            //     'list_fitness' => $temp,
            //     // 'population' => $newPopulation,
            //     // 'parent1' => $parent1,
            //     // 'parent2' => $parent2,
            //     'max_fitness' => $maxFitness,
            //     'best_individu' => $population->first(),
            // ]);


            $collection = DB::table('sesis')
                ->selectRaw('count(id) as total, hari')
                ->groupBy('hari')
                ->get();


            $result = [];
            $bestIndividu = $population->sortBy('fitness', SORT_REGULAR, true)->first();

            foreach ($collection as $data) {
                $listSesi = Sesi::where('hari', $data->hari)->get();
                $listDetailSesi = [];
                foreach ($listSesi as $sesi) {
                    $pengurus = [];
                    $anggota = [];
                    $schedules = $bestIndividu->schedule->whereIn('push', true)->whereIn('schedule.sesi_id', $sesi->id)->values();

                    foreach ($schedules as $schedule) {
                        if ($schedule->schedule->sesi_id == $sesi->id) {
                            if ($schedule->schedule->jabatan_category == 'Anggota') {
                                array_push($anggota, $schedule->schedule);
                            } else {
                                array_push($pengurus, $schedule->schedule);
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

            // $temp = $individu->sortBy('fitness', SORT_NUMERIC, true)->all(); 
            $temp = $population->sortBy('fitness', SORT_NUMERIC, true)->pluck('fitness')->toArray();
            return
                response()->json(
                    [
                        'status' => 200,
                        'message' => 'Generate jadwal berhasil',
                        // 'individu' => $temp[0],
                        // 'individu2' => $temp[1],
                        'max_fitness' => $maxFitness,
                        'fitness_total' => $bestIndividu->fitness_total,
                        'fitness' => $bestIndividu->fitness,
                        'list_fitness' => $temp,

                        'data' => $result,
                        'list' => $bestIndividu->schedule,
                        // 'count' => $bestIndividu->schedule->count(),
                    ],
                );
        } else {
            return response()->json(['message' => 'Tidak memiliki akses'], 401);
        }
    }

    // public function generate()
    // {
    //     $user = auth()->user();
    //     $admin =
    //         Admin::where('username', $user->username)->first();

    //     if ($admin) {
    //         $population = 4;
    //         $mutationRate = 0.5;
    //         $allPertemuan = Pertemuan::all();
    //         $allSesi = Sesi::all();
    //         $allPartisipan = Partisipan::all();
    //         $individu = new Collection();

    //         $rowListPartisipanTotal = DB::table('partisipans')
    //             ->select('jabatan_categories.id', 'jabatan_categories.name', DB::raw('count(*) as total'))
    //             ->join('jabatans', 'jabatans.id', '=', 'partisipans.jabatan_id')
    //             ->join('jabatan_categories', 'jabatan_categories.id', '=', 'jabatans.jabatan_category_id')
    //             ->groupBy('jabatan_categories.name')
    //             ->get();

    //         $listPartisipanTotal = new Collection();
    //         foreach ($rowListPartisipanTotal as $rowPartisipanTotal) {
    //             $listPartisipanTotal->push((object)[
    //                 'id' => $rowPartisipanTotal->id,
    //                 'name' => $rowPartisipanTotal->name,
    //                 'min' => ceil($rowPartisipanTotal->total / ($allSesi->count() / $allPertemuan->count()) / 2),
    //                 'max' => ceil($rowPartisipanTotal->total / ($allSesi->count() / $allPertemuan->count())),
    //             ]);
    //         }

    //         $maxFitness = ($allSesi->count() * $listPartisipanTotal->count()) + ($allPartisipan->count() * $allPertemuan->count());
    //         // $maxFitness = ($allSesi->count() * $listPartisipanTotal->count());

    //         $scheduleCandidates = DB::table('schedule_candidates')
    //             ->select([
    //                 'schedule_candidates.id as schedule_candidate_id',
    //                 'session_id as sesi_id',
    //                 'sesis.pertemuan_id as pertemuan_id',
    //                 'partisipans.id as partisipan_id',
    //                 'partisipans.name as partisipan_name',
    //                 'jabatan_categories.name as jabatan_category',
    //                 'jabatans.jabatan_category_id'
    //             ])
    //             ->join('sesis', 'schedule_candidates.session_id', '=', 'sesis.id')
    //             ->join('schedule_requests', 'schedule_requests.id', '=', 'schedule_candidates.schedule_request_id')
    //             ->join('partisipans', 'partisipans.id', '=', 'schedule_requests.partisipan_id')
    //             ->join('jabatans', 'jabatans.id', '=', 'partisipans.jabatan_id')
    //             ->join('jabatan_categories', 'jabatan_categories.id', '=', 'jabatans.jabatan_category_id')
    //             ->where('status', '=', StatusEnum::Accepted)
    //             ->orderBy('sesis.pertemuan_id')
    //             ->get();

    //         $cutPoint = $scheduleCandidates->whereIn('pertemuan_id', $scheduleCandidates->first()->pertemuan_id)->count();

    //         // INISIASI POPULASI AWAL
    //         for ($i = 0; $i < $population; $i++) {
    //             $candidates = new Collection();
    //             foreach ($scheduleCandidates as $scheduleCandidate) {
    //                 $randomValue = rand(0, 1) == 1;
    //                 // $counting = $candidates->whereIn('schedule.partisipan_id', $scheduleCandidate->partisipan_id)
    //                 //     ->whereIn('schedule.pertemuan_id', $scheduleCandidate->pertemuan_id)->count();

    //                 // if ($counting == 0)
    //                 $candidates->push((object)[
    //                     'push' => $randomValue,
    //                     'schedule' => $scheduleCandidate,
    //                 ]);
    //             }
    //             $individu->push($this->createIndividu(
    //                 $candidates,
    //                 $allPertemuan,
    //                 $allSesi,
    //                 $allPartisipan,
    //                 $listPartisipanTotal,
    //                 $maxFitness
    //             ));
    //         }

    //         $isLooping = true;
    //         $count = 0;
    //         set_time_limit(0);
    //         while ($isLooping) {
    //             $individu->sortBy('fitness', SORT_NUMERIC, true);
    //             $parent1 = clone $individu[0];
    //             $parent2 = clone $individu[1];

    //             $offspring1Parent1 = $parent1->schedule;
    //             $offspring2Parent2 = $parent2->schedule;

    //             $offspring2Parent1 = $offspring1Parent1->splice($cutPoint);
    //             $offspring1Parent2 = $offspring2Parent2->splice($cutPoint);

    //             $scheduleChild1 = new Collection();
    //             $scheduleChild2 = new Collection();

    //             foreach ($offspring1Parent1 as $offspring) {
    //                 $scheduleChild1->push($offspring);
    //             }
    //             foreach ($offspring1Parent2 as $offspring) {
    //                 $scheduleChild1->push($offspring);
    //             }

    //             foreach ($offspring2Parent1 as $offspring) {
    //                 $scheduleChild2->push($offspring);
    //             }
    //             foreach ($offspring2Parent2 as $offspring) {
    //                 $scheduleChild2->push($offspring);
    //             }

    //             $slice = $individu->splice(0, -2);

    //             foreach ($scheduleChild1 as $schedule) {
    //                 $random = rand(0, 100) / 100;
    //                 // if ($random < $mutationRate) {
    //                 //     $schedule->push = !$schedule->push;
    //                 // }
    //                 if ($random < $mutationRate) {
    //                     $countPartisipan = $scheduleChild1->whereIn('push', true)->whereIn('schedule.partisipan_id', $schedule->schedule->partisipan_id)
    //                         ->whereIn('schedule.pertemuan_id', $schedule->schedule->pertemuan_id)->count();
    //                     if ($countPartisipan > 1) {
    //                         $schedule->push = false;
    //                     } else {
    //                         $schedule->push = true;
    //                     }
    //                 }
    //             }
    //             foreach ($scheduleChild2 as $schedule) {
    //                 $random = rand(0, 100) / 100;
    //                 // if ($random < $mutationRate) {
    //                 //     $schedule->push = !$schedule->push;
    //                 // }
    //                 if ($random < $mutationRate) {
    //                     $countPartisipan = $scheduleChild2->whereIn('push', true)->whereIn('schedule.partisipan_id', $schedule->schedule->partisipan_id)
    //                         ->whereIn('schedule.pertemuan_id', $schedule->schedule->pertemuan_id)->count();
    //                     if ($countPartisipan > 1) {
    //                         $schedule->push = false;
    //                     } else {
    //                         $schedule->push = true;
    //                     }
    //                 }
    //             }

    //             $individu->push($this->createIndividu(
    //                 $scheduleChild1,
    //                 $allPertemuan,
    //                 $allSesi,
    //                 $allPartisipan,
    //                 $listPartisipanTotal,
    //                 $maxFitness
    //             ));
    //             $individu->push($this->createIndividu(
    //                 $scheduleChild2,
    //                 $allPertemuan,
    //                 $allSesi,
    //                 $allPartisipan,
    //                 $listPartisipanTotal,
    //                 $maxFitness
    //             ));

    //             // $fitCount = $individu->whereBetween('fitness', [0.2, 1])->count();
    //             $fitCount = $individu->whereIn('fitness', 1)->count();
    //             if ($fitCount > 0) {
    //                 $isLooping = false;
    //             }

    //             // $count++;
    //             // if ($count > 3000) {
    //             //     $isLooping = false;
    //             // }
    //         }


    //         // $temp = $individu->sortBy('fitness', SORT_NUMERIC, true)->pluck('fitness')->toArray();
    //         // return
    //         //     response()->json(
    //         //         [
    //         //             'status' => 200,
    //         //             'data' => $temp,
    //         //         ],
    //         //     );
    //         // $temp = $individu->sortBy('fitness', SORT_NUMERIC, true)->pluck('fitness')->toArray();
    //         // return response()->json(['fitness' => $temp]);

    //         $collection = DB::table('sesis')
    //             ->selectRaw('count(id) as total, hari')
    //             ->groupBy('hari')
    //             ->get();


    //         $result = [];
    //         $individu = $individu->sortBy('fitness', SORT_NUMERIC, true);

    //         foreach ($collection as $data) {
    //             $listSesi = Sesi::where('hari', $data->hari)->get();
    //             $listDetailSesi = [];
    //             foreach ($listSesi as $sesi) {
    //                 $pengurus = [];
    //                 $anggota = [];
    //                 $schedules = $individu->first()->schedule->whereIn('schedule.sesi_id', $sesi->id)->all();

    //                 foreach ($schedules as $schedule) {
    //                     if ($schedule->schedule->sesi_id == $sesi->id) {
    //                         if ($schedule->schedule->jabatan_category == 'Anggota') {
    //                             array_push($anggota, $schedule->schedule);
    //                         } else {
    //                             array_push($pengurus, $schedule->schedule);
    //                         }
    //                     }
    //                 }
    //                 $detailSesi = [
    //                     'name' => $sesi->name,
    //                     'waktu' => $sesi->waktu,
    //                     'pengurus' => $pengurus,
    //                     'anggota' => $anggota
    //                 ];
    //                 array_push($listDetailSesi, $detailSesi);
    //             }
    //             $detailResult = [
    //                 'hari' => $data->hari,
    //                 'list_sesi' => $listDetailSesi,
    //             ];
    //             array_push($result, $detailResult);
    //         }

    //         // $temp = $individu->sortBy('fitness', SORT_NUMERIC, true)->all(); 
    //         $temp = $individu->sortBy('fitness', SORT_NUMERIC, true)->pluck('fitness')->toArray();
    //         return
    //             response()->json(
    //                 [
    //                     'status' => 200,
    //                     'message' => 'Generate jadwal berhasil',
    //                     // 'individu' => $temp[0],
    //                     // 'individu2' => $temp[1],
    //                     'max_fitness' => $maxFitness,
    //                     'fitness_total' => $individu->first()->fitness_total,
    //                     'fitness' => $individu->first()->fitness,
    //                     'list_fitness' => $temp,

    //                     // 'data' => $result,
    //                     'data' => $individu->first()->schedule,
    //                     'count' => $individu->first()->schedule->count(),
    //                 ],
    //             );
    //     } else {
    //         return response()->json(['message' => 'Tidak memiliki akses'], 401);
    //     }
    // }

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
                        ->select([
                            'schedule_candidates.id as schedule_candadate_id',
                            'partisipans.name as partisipan_name',
                            'jabatan_categories.name as jabatan_category'
                        ])
                        ->join('schedule_candidates', 'schedule_candidates.id', '=', 'schedules.schedule_candidate_id')
                        ->join('schedule_requests', 'schedule_requests.id', '=', 'schedule_candidates.schedule_request_id')
                        ->join('sesis', 'sesis.id', '=', 'schedule_candidates.session_id')
                        ->join('partisipans', 'partisipans.id', '=', 'schedule_requests.partisipan_id')
                        ->join('jabatans', 'jabatans.id', '=', 'partisipans.jabatan_id')
                        ->join('jabatan_categories', 'jabatans.jabatan_category_id', '=', 'jabatan_categories.id')
                        ->where('sesis.id', '=', $sesi->id)
                        ->where('jabatans.name', '!=', 'Anggota Magang')
                        ->get();
                    $anggota =
                        DB::table('schedules')
                        ->select([
                            'schedule_candidates.id as schedule_candadate_id',
                            'partisipans.name as partisipan_name',
                            'jabatan_categories.name as jabatan_category'
                        ])
                        ->join('schedule_candidates', 'schedule_candidates.id', '=', 'schedules.schedule_candidate_id')
                        ->join('schedule_requests', 'schedule_requests.id', '=', 'schedule_candidates.schedule_request_id')
                        ->join('sesis', 'sesis.id', '=', 'schedule_candidates.session_id')
                        ->join('partisipans', 'partisipans.id', '=', 'schedule_requests.partisipan_id')
                        ->join('jabatans', 'jabatans.id', '=', 'partisipans.jabatan_id')
                        ->join('jabatan_categories', 'jabatans.jabatan_category_id', '=', 'jabatan_categories.id')
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
