<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class NewsController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user) {
            $newsList = News::orderBy('created_at', 'desc')->paginate(5);
            return response()->json(
                [
                    'status' => 200,
                    'data' => $newsList,
                ],
            );
        } else {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
    }
    public function detail($id)
    {
        $user = auth()->user();

        if ($user) {
            $detailNews = News::where('id', $id)->firstOrFail();
            return response()->json(
                [
                    'status' => 200,
                    'data' => $detailNews,
                ],
            );
        } else {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
    }
    public function create(Request $request)
    {
        $user = auth()->user();
        $admin = Admin::where('username', $user->username)->first();

        if ($admin) {
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'description' => 'required|string',
            ]);
            if ($validator->fails()) {
                return response()->json(['status' => 400, 'message' => $validator->errors()->first(),], 400);
            }
            News::create([
                'id' => Str::uuid(),
                'title' => $request->title,
                'description' => $request->description,
            ]);
            return response()->json(
                [
                    'status' => 200,
                    'message' => 'Success!',
                ],
            );
        } else {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
    }

    public function update(Request $request)
    {
        $user = auth()->user();
        $admin = Admin::where('username', $user->username)->first();

        if ($admin) {
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'description' => 'required|string',
            ]);
            if ($validator->fails()) {
                return response()->json(['status' => 400, 'message' => $validator->errors()->first(),], 400);
            }
            $news = News::where('id', $request->id)->firstOrFail();
            $news->title = $request->title;
            $news->description = $request->description;
            $news->save();
            return response()->json(
                [
                    'status' => 200,
                    'message' => 'Success!',
                ],
            );
        } else {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
    }
    public function delete($id)
    {
        $user = auth()->user();
        $admin = Admin::where('username', $user->username)->first();

        if ($admin) {
            News::where('id', $id)->delete();
            return response()->json(
                [
                    'status' => 200,
                    'message' => 'Delete Success!',
                ],
            );
        } else {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
    }
}
