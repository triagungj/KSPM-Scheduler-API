<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Response;

class FileController extends Controller
{
    public function image($imagename)
    {
        $path = storage_path('app/public/images/' . $imagename);
        return Response::download($path);
    }
}
