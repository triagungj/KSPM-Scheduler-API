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

// public function generate()
 // {
 // $user = auth()->user();
 // $admin =
 // Admin::where('username', $user->username)->first();

 // if ($admin) {
 // // $partisipanNull = DB::table('partisipans')
 // // ->where('jabatan_id', '=', null)
 // // ->count();
 // // if ($partisipanNull > 0) {
 // // return response()->json(
 // // [
 // // 'status' => 403,
 // // 'message' => 'Terdapat Partisipan yang belum mengisi data Jabatan.',
 // // ],
 // // 403
 // // );
 // // }

 // // $totalAccepted = DB::table('schedule_requests')->where('status', '=', 'accepted')->count();
 // // $totalPartisipan = DB::table('partisipans')->count();
 // // if ($totalAccepted < $totalPartisipan) { // // return response()->json(
     // // [
     // // 'status' => 403,
     // // 'message' => 'Terdapat ajuan Jadwal yang belum diterima petugas.',
     // // ],
     // // 403
     // // );
     // // }

     // $population = 10;
     // $pertemuans = Pertemuan::all();
     // $sesis = Sesi::all();
     // $individu = new Collection();

     // $rowListPartisipanTotal = DB::table('partisipans')
     // ->select('jabatan_categories.id', 'jabatan_categories.name', DB::raw('count(*) as total'))
     // ->join('jabatans', 'jabatans.id', '=', 'partisipans.jabatan_id')
     // ->join('jabatan_categories', 'jabatan_categories.id', '=', 'jabatans.jabatan_category_id')
     // ->groupBy('jabatan_categories.name')
     // ->get();

     // $listPartisipanTotal = new Collection();
     // foreach ($rowListPartisipanTotal as $rowPartisipanTotal) {
     // $listPartisipanTotal->push((object)[
     // 'id' => $rowPartisipanTotal->id,
     // 'name' => $rowPartisipanTotal->name,
     // 'max' => ceil($rowPartisipanTotal->total / ($sesis->count() / $pertemuans->count())),
     // ]);
     // }
     // // return response()->json(
     // // [
     // // 'status' => 200,
     // // 'message' => 'anu',
     // // 'data' => $listPartisipanTotal,
     // // ],
     // // );

     // $scheduleCandidates = DB::table('schedule_candidates')
     // ->select([
     // 'schedule_candidates.id as schedule_candidate_id',
     // 'session_id as sesi_id',
     // 'sesis.pertemuan_id as pertemuan_id',
     // 'partisipans.id as partisipan_id',
     // 'partisipans.name as partisipan_name',
     // 'jabatan_categories.name as jabatan_category',
     // 'jabatans.jabatan_category_id'
     // ])
     // ->join('sesis', 'schedule_candidates.session_id', '=', 'sesis.id')
     // ->join('schedule_requests', 'schedule_requests.id', '=', 'schedule_candidates.schedule_request_id')
     // ->join('partisipans', 'partisipans.id', '=', 'schedule_requests.partisipan_id')
     // ->join('jabatans', 'jabatans.id', '=', 'partisipans.jabatan_id')
     // ->join('jabatan_categories', 'jabatan_categories.id', '=', 'jabatans.jabatan_category_id')
     // ->where('status', '=', StatusEnum::Accepted)
     // ->inRandomOrder()
     // ->get();


     // for ($i = 0; $i < $population; $i++) { // $sessionSchedules=new Collection(); // $fitnessTotal=0; // foreach ($pertemuans as $pertemuan) { // $sessionSchedulesTemp=new Collection(); // foreach ($scheduleCandidates as $scheduleCandidate) { // if ($scheduleCandidate->pertemuan_id == $pertemuan->id) {
         // // if (!$sessionSchedulesTemp->contains('partisipan_id', $scheduleCandidate->partisipan_id)) {
         // $sessionSchedulesTemp->push((object)$scheduleCandidate);
         // $sessionSchedules->push((object)$scheduleCandidate);
         // // }
         // }
         // }
         // }

         // foreach ($pertemuans as $pertemuan) {
         // foreach ($sesis as $sesi) {
         // if ($sesi->pertemuan_id == $pertemuan->id) {
         // $listTempSesi = new Collection();
         // foreach ($listPartisipanTotal as $partisipanTotal) {
         // $listTempSesi->push((object)[
         // 'id' => $partisipanTotal->id,
         // 'name' => $partisipanTotal->name,
         // 'total' => 0,
         // ]);
         // }
         // foreach ($sessionSchedules as $sessionSchedule) {
         // if ($sessionSchedule->sesi_id == $sesi->id) {
         // foreach ($listPartisipanTotal as $partisipanTotal) {
         // if ($partisipanTotal->id == $sessionSchedule->jabatan_category_id) {
         // foreach ($listTempSesi as $tempSesi) {
         // if ($tempSesi->id == $sessionSchedule->jabatan_category_id) {
         // $tempSesi->total++;
         // }
         // }
         // }
         // }
         // }
         // }

         // $fitCount = 0;
         // foreach ($listTempSesi as $tempSesi) {
         // foreach ($listPartisipanTotal as $partisipanTotal) {
         // if ($partisipanTotal->id == $tempSesi->id) {
         // if ($tempSesi->total <= $partisipanTotal->max) {
             // $fitCount++;
             // }
             // }
             // }
             // }
             // $isFitness = $fitCount == $listTempSesi->count();
             // if ($isFitness) {
             // $fitnessTotal++;
             // }
             // }
             // }
             // }
             // $fitnessValue = $fitnessTotal / $sesis->count();
             // $individu->push((object)[
             // 'total_candidates' => count($sessionSchedules),
             // 'fitness' => $fitnessValue,
             // 'fitness_total' => $fitnessTotal,
             // 'schedule' => $sessionSchedules,
             // ]);
             // }

             // $temp = $individu[0];

             // foreach ($individu as $cromosom) {
             // if ($cromosom->fitness >= $temp->fitness) {
             // $temp = $cromosom;
             // }
             // }

             // $collection = DB::table('sesis')
             // ->selectRaw('count(id) as total, hari')
             // ->groupBy('hari')
             // ->get();

             // $result = [];
             // foreach ($collection as $data) {
             // $listSesi = Sesi::where('hari', $data->hari)->get();
             // $listDetailSesi = [];
             // foreach ($listSesi as $sesi) {
             // $pengurus = [];
             // $anggota = [];
             // foreach ($temp->schedule as $schedule) {
             // if ($schedule->sesi_id == $sesi->id) {
             // if ($schedule->jabatan_category == 'Anggota') {
             // array_push($anggota, $schedule);
             // } else {
             // array_push($pengurus, $schedule);
             // }
             // }
             // }
             // $detailSesi = [
             // 'name' => $sesi->name,
             // 'waktu' => $sesi->waktu,
             // 'pengurus' => $pengurus,
             // 'anggota' => $anggota
             // ];
             // array_push($listDetailSesi, $detailSesi);
             // }
             // $detailResult = [
             // 'hari' => $data->hari,
             // 'list_sesi' => $listDetailSesi,
             // ];
             // array_push($result, $detailResult);
             // }

             // return
             // response()->json(
             // [
             // 'status' => 200,
             // 'message' => 'Generate jadwal berhasil',
             // 'individu' => count($individu),
             // 'candidates' => $temp->total_candidates,
             // 'fitness' => $temp->fitness,
             // 'data' => $result,
             // ],
             // );
             // } else {
             // return response()->json(['message' => 'Tidak memiliki akses'], 401);
             // }
             // }