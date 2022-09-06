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
        $scenes = Scene::with(['character', 'prop', 'scene_comments'])->orderBy('first_page')->get();

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

        $first_page = !empty($request->first_page) ? $request->first_page : null; // nullで送ると文字列になる
        $final_page = !empty($request->final_page) ? $request->final_page : null;
        $usage = !empty($request->usage) ? 1 : null;

        $exist = Scene::where([['character_id', '=', $request->character_id], 
                              ['prop_id', '=', $request->prop_id], 
                              ['first_page', '=', $first_page],
                              ['final_page','=', $final_page]])
            ->exists();
        
        $exist_update_first_page = Scene::where([['character_id', '=', $request->character_id], 
                                                 ['prop_id', '=', $request->prop_id], 
                                                 ['first_page', '=', null]])
            ->first();

        $exist_update_fianl_page_null = Scene::where([['character_id', '=', $request->character_id], 
                                                      ['prop_id', '=', $request->prop_id], 
                                                      ['first_page', '=', $first_page],
                                                      ['final_page', '=', null]])
            ->first();

        if($final_page){
            $exist_update_fianl_page_notnull = Scene::where([['character_id', '=', $request->character_id], 
                                                             ['prop_id', '=', $request->prop_id], 
                                                             ['first_page', '=', $first_page],
                                                             ['final_page', '<', $final_page]])
                ->first();
        }else{
            $exist_update_fianl_page_notnull = null;
        }     

        try {
            if(!is_null($exist_update_first_page) && $first_page){
                // 登録済みが最初のページがnullだったので、更新
                if($usage){
                    // 使用するなら更新
                    $scene = Scene::where('id', $exist_update_first_page->id)
                        ->update(['first_page' => $first_page, 'final_page' => $final_page, 'usage' => $usage]);
                    if($request->memo){
                        $scene_comment = Scenes_Comment::create(['scene_id' => $exist_update_first_page->id, 'memo' => $request->memo]);
                    }
                }else{
                    // 使用しないなら更新しない
                    $scene = Scene::where('id', $exist_update_first_page->id)
                        ->update(['first_page' => $first_page, 'final_page' => $final_page]);
                    if($request->memo){
                        $scene_comment = Scenes_Comment::create(['scene_id' => $exist_update_first_page->id, 'memo' => $request->memo]);
                    }
                }

                DB::commit();
                // リソースの新規作成なので
                // レスポンスコードは201(CREATED)を返却する
                return response($scene, 201);
            }else if((!is_null($exist_update_fianl_page_null) || (!is_null($exist_update_fianl_page_notnull)) )){
                // 登録済みが最初のページは一致するが、最後のページは一致しないかつより多くのページを含む場合
                if(!is_null($exist_update_fianl_page_null)){
                    $id = $exist_update_fianl_page_null->id;
                }else{
                    $id = $exist_update_fianl_page_notnull->id;
                }
                if($usage){
                    // 使用するなら更新
                    $scene = Scene::where('id', $id)
                        ->update(['final_page' => $final_page, 'usage' => $usage]);
                    if($request->memo){
                        $scene_comment = Scenes_Comment::create(['scene_id' => $id, 'memo' => $request->memo]);
                    }
                }else{
                    // 使用しないなら更新しない
                    $scene = Scene::where('id', $id)
                        ->update(['final_page' => $final_page]);
                    if($request->memo){
                        $scene_comment = Scenes_Comment::create(['scene_id' => $id, 'memo' => $request->memo]);
                    }
                }

                DB::commit();
                // リソースの新規作成なので
                // レスポンスコードは201(CREATED)を返却する
                return response($scene, 201);

            }else if(!($exist)){
                // 新規投稿
                $scene = Scene::create(['character_id' => $request->character_id, 'prop_id' => $request->prop_id, 'first_page' => $first_page, 'final_page' => $final_page, 'usage' => $usage]);
                if($request->memo){
                    $scene_comment = Scenes_Comment::create(['scene_id' => $scene->id, 'memo' => $request->memo]);
                }

                DB::commit();
                // リソースの新規作成なので
                // レスポンスコードは201(CREATED)を返却する
                return response($scene, 201);
            }
        }catch (\Exception $exception) {
            DB::rollBack();
            
            throw $exception;
        }
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
            ->with(['character', 'character.section', 'prop', 'prop.owner', 'prop.prop_comments', 'scene_comments'])->first();

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
        DB::beginTransaction();

        $first_page = !empty($request->first_page) ? $request->first_page : null; // nullで送ると文字列になる
        $final_page = !empty($request->final_page) ? $request->final_page : null;
        $usage = !empty($request->usage) ? 1 : null;

        try {
            $affected = Scene::where('id', $id)
                   ->update(['character_id' => $request->character_id, 'prop_id' => $request->prop_id, 'first_page' => $first_page, 'final_page' => $final_page, 'usage' => $usage]);
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
            $scene = Scene::where('id', $id)
                        ->delete();      
            DB::commit();
        }catch (\Exception $exception) {
            DB::rollBack();
            
            throw $exception;
        }

        // レスポンスコードは204(No Content)を返却する
        return response($scene, 204) ?? abort(404);
    }
}
