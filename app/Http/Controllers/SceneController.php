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
        $scenes = Scene::with(['character', 'prop', 'setting', 'scene_comments'])->orderBy('first_page')->orderBy('created_at')->get();     

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
        $decision = !empty($request->decision) ? 1 : 0;
        $usage = !empty($request->usage) ? 1 : 0;
        $usage_guraduation  = !empty($request->usage_guraduation) ? 1 : 0;
        $usage_left = !empty($request->usage_left) ? 1 : 0;
        $usage_right = !empty($request->usage_right) ? 1 : 0;

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
            if(!is_null($exist_update_first_page)){
                // 登録済みが最初のページがnullだったので、更新
                if($usage || $usage_guraduation || $usage_left || $usage_right){
                    // 使用するなら更新
                    $confirm = $exist_update_first_page -> toArray();
                    if(!$confirm['usage'] && !$confirm['usage_guraduation']){
                        // 全て0だったので、全部更新
                        $scene = Scene::where('id', $exist_update_first_page->id)
                                     ->update(['first_page' => $first_page, 'final_page' => $final_page, 'decision' => $decision, 'usage' => $usage, 'usage_guraduation' => $usage_guraduation, 'usage_left' => $usage_left, 'usage_right' => $usage_right, 'setting_id' => $request->setting_id]);
                        if($request->memo){
                            $scene_comment = Scenes_Comment::create(['scene_id' => $exist_update_first_page->id, 'memo' => $request->memo]);
                        }
                    }else if($confirm['usage'] && $confirm['usage_guraduation']){
                        // 中間公演が1→1、卒業公演が1→1
                        if(!$confirm['usage_left'] && $usage_left){
                            // 上手が0→1
                            $scene = Scene::where('id', $exist_update_first_page->id)
                                     ->update(['first_page' => $first_page, 'final_page' => $final_page, 'decision' => $decision, 'usage_left' => 1, 'setting_id' => $request->setting_id]);
                            if($request->memo){
                                $scene_comment = Scenes_Comment::create(['scene_id' => $exist_update_first_page->id, 'memo' => $request->memo]);
                            }
                        }else if(!$confirm['usage_right'] && $usage_right){
                            // 下手が0→1
                            $scene = Scene::where('id', $exist_update_first_page->id)
                                     ->update(['first_page' => $first_page, 'final_page' => $final_page, 'decision' => $decision, 'usage_right' => 1, 'setting_id' => $request->setting_id]);
                            if($request->memo){
                                $scene_comment = Scenes_Comment::create(['scene_id' => $exist_update_first_page->id, 'memo' => $request->memo]);
                            }
                        }else{
                            // 更新する必要がなかった
                            $scene = Scene::where('id', $exist_update_first_page->id)
                                ->update(['first_page' => $first_page, 'final_page' => $final_page, 'decision' => $decision]);
                            if($request->memo){
                                $scene_comment = Scenes_Comment::create(['scene_id' => $exist_update_first_page->id, 'memo' => $request->memo]);
                            }
                        }
                    }else if(!$confirm['usage'] && $usage){
                        // 中間公演が0→1、卒業公演が1→1
                        if(!$confirm['usage_left'] && $usage_left){
                            // 中間公演が0→1、上手が0→1
                            $scene = Scene::where('id', $exist_update_first_page->id)
                                     ->update(['first_page' => $first_page, 'final_page' => $final_page, 'decision' => $decision, 'usage' => 1, 'usage_left' => 1, 'setting_id' => $request->setting_id]);
                            if($request->memo){
                                $scene_comment = Scenes_Comment::create(['scene_id' => $exist_update_first_page->id, 'memo' => $request->memo]);
                            }
                        }else if(!$confirm['usage_right'] && $usage_right){
                            // 中間公演が0→1、下手が0→1
                            $scene = Scene::where('id', $exist_update_first_page->id)
                                     ->update(['first_page' => $first_page, 'final_page' => $final_page, 'decision' => $decision, 'usage' => 1, 'usage_right' => 1, 'setting_id' => $request->setting_id]);
                            if($request->memo){
                                $scene_comment = Scenes_Comment::create(['scene_id' => $exist_update_first_page->id, 'memo' => $request->memo]);
                            }
                        }else{
                            // 中間公演が0→1
                            $scene = Scene::where('id', $exist_update_first_page->id)
                                     ->update(['first_page' => $first_page, 'final_page' => $final_page, 'decision' => $decision, 'usage' => 1, 'setting_id' => $request->setting_id]);
                            if($request->memo){
                                $scene_comment = Scenes_Comment::create(['scene_id' => $exist_update_first_page->id, 'memo' => $request->memo]);
                            }
                        }
                    }else if(!$confirm['usage_guraduation'] && $usage_guraduation){
                        // 中間公演が1→1、卒業公演が0→1
                        if(!$confirm['usage_left'] && $usage_left){
                            // 卒業公演が0→1、上手が0→1
                            $scene = Scene::where('id', $exist_update_first_page->id)
                                     ->update(['first_page' => $first_page, 'final_page' => $final_page, 'decision' => $decision, 'usage_guraduation' => 1, 'usage_left' => 1, 'setting_id' => $request->setting_id]);
                            if($request->memo){
                                $scene_comment = Scenes_Comment::create(['scene_id' => $exist_update_first_page->id, 'memo' => $request->memo]);
                            }
                        }else if(!$confirm['usage_right'] && $usage_right){
                            // 卒業公演が0→1、下手が0→1
                            $scene = Scene::where('id', $exist_update_first_page->id)
                                     ->update(['first_page' => $first_page, 'final_page' => $final_page, 'decision' => $decision, 'usage_guraduation' => 1, 'usage_right' => 1, 'setting_id' => $request->setting_id]);
                            if($request->memo){
                                $scene_comment = Scenes_Comment::create(['scene_id' => $exist_update_first_page->id, 'memo' => $request->memo]);
                            }
                        }else{
                            // 卒業公演が0→1
                            $scene = Scene::where('id', $exist_update_first_page->id)
                                     ->update(['first_page' => $first_page, 'final_page' => $final_page, 'decision' => $decision, 'usage_guraduation' => 1, 'setting_id' => $request->setting_id]);
                            if($request->memo){
                                $scene_comment = Scenes_Comment::create(['scene_id' => $exist_update_first_page->id, 'memo' => $request->memo]);
                            }
                        }
                    }else{
                        // 更新する必要がなかった
                        $scene = Scene::where('id', $exist_update_first_page->id)
                            ->update(['first_page' => $first_page, 'final_page' => $final_page, 'decision' => $decision, 'setting_id' => $request->setting_id]);
                        if($request->memo){
                            $scene_comment = Scenes_Comment::create(['scene_id' => $exist_update_first_page->id, 'memo' => $request->memo]);
                        }
                    }
                }else{
                    // 使用しないなら更新しない
                    $scene = Scene::where('id', $exist_update_first_page->id)
                        ->update(['first_page' => $first_page, 'final_page' => $final_page, 'decision' => $decision, 'setting_id' => $request->setting_id]);
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
                    $confirm = $exist_update_fianl_page_null -> toArray();
                }else{
                    $id = $exist_update_fianl_page_notnull->id;
                    $confirm = $exist_update_fianl_page_notnull -> toArray();
                }
                if($usage || $usage_guraduation || $usage_left || $usage_right){
                    if(!$confirm['usage'] && !$confirm['usage_guraduation']){
                        // 全て0だったので、全部更新
                        $scene = Scene::where('id', $id)
                                     ->update(['final_page' => $final_page, 'decision' => $decision, 'usage' => $usage, 'usage_guraduation' => $usage_guraduation, 'usage_left' => $usage_left, 'usage_right' => $usage_right, 'setting_id' => $request->setting_id]);
                        if($request->memo){
                            $scene_comment = Scenes_Comment::create(['scene_id' => $id, 'memo' => $request->memo]);
                        }
                    }else if($confirm['usage'] && $confirm['usage_guraduation']){
                        // 中間公演が1→1、卒業公演が1→1
                        if(!$confirm['usage_left'] && $usage_left){
                            // 上手が0→1
                            $scene = Scene::where('id', $id)
                                     ->update(['final_page' => $final_page, 'decision' => $decision, 'usage_left' => 1, 'setting_id' => $request->setting_id]);
                            if($request->memo){
                                $scene_comment = Scenes_Comment::create(['scene_id' => $id, 'memo' => $request->memo]);
                            }
                        }else if(!$confirm['usage_right'] && $usage_right){
                            // 下手が0→1
                            $scene = Scene::where('id', $eid)
                                     ->update(['final_page' => $final_page, 'decision' => $decision, 'usage_right' => 1, 'setting_id' => $request->setting_id]);
                            if($request->memo){
                                $scene_comment = Scenes_Comment::create(['scene_id' => $id, 'memo' => $request->memo]);
                            }
                        }
                    }else if(!$confirm['usage'] && $usage){
                        // 中間公演が0→1、卒業公演が1→1
                        if(!$confirm['usage_left'] && $usage_left){
                            // 中間公演が0→1、上手が0→1
                            $scene = Scene::where('id', $id)
                                     ->update(['final_page' => $final_page, 'decision' => $decision, 'usage' => 1, 'usage_left' => 1, 'setting_id' => $request->setting_id]);
                            if($request->memo){
                                $scene_comment = Scenes_Comment::create(['scene_id' => $id, 'memo' => $request->memo]);
                            }
                        }else if(!$confirm['usage_right'] && $usage_right){
                            // 中間公演が0→1、下手が0→1
                            $scene = Scene::where('id', $exist_update_first_page->id)
                                     ->update(['first_page' => $first_page, 'final_page' => $final_page, 'decision' => $decision, 'usage' => 1, 'usage_right' => 1, 'setting_id' => $request->setting_id]);
                            if($request->memo){
                                $scene_comment = Scenes_Comment::create(['scene_id' => $id, 'memo' => $request->memo]);
                            }
                        }
                    }else if(!$confirm['usage_guraduation'] && $usage_guraduation){
                        // 中間公演が1→1、卒業公演が0→1
                        if(!$confirm['usage_left'] && $usage_left){
                            // 卒業公演が0→1、上手が0→1
                            $scene = Scene::where('id', $id)
                                     ->update(['final_page' => $final_page, 'decision' => $decision, 'usage_guraduation' => 1, 'usage_left' => 1, 'setting_id' => $request->setting_id]);
                            if($request->memo){
                                $scene_comment = Scenes_Comment::create(['scene_id' => $id, 'memo' => $request->memo]);
                            }
                        }else if(!$confirm['usage_right'] && $usage_right){
                            // 卒業公演が0→1、下手が0→1
                            $scene = Scene::where('id', $id)
                                     ->update(['final_page' => $final_page, 'decision' => $decision, 'usage_guraduation' => 1, 'usage_right' => 1, 'setting_id' => $request->setting_id]);
                            if($request->memo){
                                $scene_comment = Scenes_Comment::create(['scene_id' => $eid, 'memo' => $request->memo]);
                            }
                        }
                    }
                }else{
                    // 使用しないなら更新しない
                    $scene = Scene::where('id', $id)
                        ->update(['final_page' => $final_page, 'decision' => $decision, 'setting_id' => $request->setting_id]);
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
                $scene = Scene::create(['character_id' => $request->character_id, 'prop_id' => $request->prop_id, 'first_page' => $first_page, 'final_page' => $final_page, 'decision' > $decision, 'usage' => $usage, 'usage_guraduation' => $usage_guraduation, 'usage_left' => $usage_left, 'usage_right' => $usage_right, 'setting_id' => $request->setting_id]);
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
            ->with(['character', 'character.section', 'prop', 'prop.owner', 'prop.prop_comments', 'setting', 'scene_comments'])->first();

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
        $decision = !empty($request->decision) ? 1 : 0;
        $usage = !empty($request->usage) ? 1 : 0;
        $usage_guraduation = !empty($request->usage_guraduation) ? 1 : 0;
        $usage_left = !empty($request->usage_left) ? 1 : 0;
        $usage_right = !empty($request->usage_right) ? 1 : 0;

        try {
            $affected = Scene::where('id', $id)
                   ->update(['character_id' => $request->character_id, 'prop_id' => $request->prop_id, 'first_page' => $first_page, 'final_page' => $final_page, 'decision' => $decision, 'usage' => $usage, 'usage_guraduation' => $usage_guraduation, 'usage_left' => $usage_left, 'usage_right' => $usage_right, 'setting_id' => $request->setting_id]);
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
