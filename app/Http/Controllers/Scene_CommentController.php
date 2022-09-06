<?php

namespace App\Http\Controllers;

use App\Models\Scenes_Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Scene_CommentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $scene_comment = Scenes_Comment::create(['scene_id' => $request->scene_id, 'memo' => $request->memo]);
            DB::commit();
        }catch (\Exception $exception) {
            DB::rollBack();
            
            throw $exception;
        }

        // リソースの新規作成なので
        // レスポンスコードは201(CREATED)を返却する
        return response($scene_comment, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            $affected = Scenes_Comment::where('id', $id)
                ->update(['memo' => $request->memo]);
                
            DB::commit();
        }catch (\Exception $exception) {
            DB::rollBack();
            
            throw $exception;
        }

        // レスポンスコードは204(No Content)を返却する
        return response($affected, 204) ?? abort(404);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $scene_comment = Scenes_Comment::where('id', $id)
                        ->delete();      

            DB::commit();

        }catch (\Exception $exception) {
            DB::rollBack();
            
            throw $exception;
        }

        return response($scene_comment, 204) ?? abort(404);
    }
}
