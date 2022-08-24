<?php

namespace App\Http\Controllers;

use App\Models\Scene;
use App\Models\Scenes_Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SceneController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $scenes = Scene::with(['characters, props'])->get();

        return $scenes;
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

        $first_page = $request->first_page !== 'null' ? $request->first_page : null; // nullで送ると文字列になる
        $final_page = $request->final_page !== 'null' ? $request->final_page : null;
        $usage = $request->usage !== 'null' ? 1 : null;

        try {
            $scene = Scene::create(['character_id' => $request->character_id, 'prop_id' => $request->prop_id, 'first_page' => $first_page, 'final_page' => $final_page, 'usage' => $usage]);
            if($request->memo !== 'null'){
                $scene_comment = Scenes_Comment::create(['scene_id' => $scene->id, 'memo' => $request->memo]);
            }            

            DB::commit();
        }catch (\Exception $exception) {
            DB::rollBack();
            
            throw $exception;
        }

        // リソースの新規作成なので
        // レスポンスコードは201(CREATED)を返却する
        return response($scene, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $scene = Scene::where('id', $id)
            ->with(['scene_comment'])->first();

        return $scene ?? abort(404);
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
