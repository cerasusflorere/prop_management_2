<?php

namespace App\Http\Controllers;

use App\Models\Scene;
use App\Models\Scenes_Comment;
use App\Models\Prop;
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
        $scenes = Scene::with(['character', 'prop', 'prop.prop_comments','setting', 'scene_comments'])->orderBy('first_page')->orderBy('created_at')->get();     

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
        $quantity = !empty($request->quantity) ? $request->quantity : 1;
        $decision = !empty($request->decision) ? 1 : 0;
        $usage = !empty($request->usage) ? 1 : 0;
        $usage_guraduation  = !empty($request->usage_guraduation) ? 1 : 0;
        $usage_left = !empty($request->usage_left) ? 1 : 0;
        $usage_right = !empty($request->usage_right) ? 1 : 0;
        $setting_id = !empty($request->setting_id)? $request->setting_id : null; // nullで送ると文字列になる

        // ページ更新について
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
                                     ->update(['first_page' => $first_page, 'final_page' => $final_page, 'quantity' => $quantity, 'decision' => $decision, 'usage' => $usage, 'usage_guraduation' => $usage_guraduation, 'usage_left' => $usage_left, 'usage_right' => $usage_right, 'setting_id' => $setting_id]);
                        if($request->memo){
                            $scene_comment = Scenes_Comment::create(['scene_id' => $exist_update_first_page->id, 'memo' => $request->memo]);
                        }
                    }else if($confirm['usage'] && $confirm['usage_guraduation']){
                        // 中間公演が1→1、卒業公演が1→1
                        if(!$confirm['usage_left'] && $usage_left){
                            // 上手が0→1
                            $scene = Scene::where('id', $exist_update_first_page->id)
                                     ->update(['first_page' => $first_page, 'final_page' => $final_page, 'quantity' => $quantity, 'decision' => $decision, 'usage_left' => 1, 'usage_right' => 0, 'setting_id' => $setting_id]);
                            if($request->memo){
                                $scene_comment = Scenes_Comment::create(['scene_id' => $exist_update_first_page->id, 'memo' => $request->memo]);
                            }
                        }else if(!$confirm['usage_right'] && $usage_right){
                            // 下手が0→1
                            $scene = Scene::where('id', $exist_update_first_page->id)
                                     ->update(['first_page' => $first_page, 'final_page' => $final_page, 'quantity' => $quantity, 'decision' => $decision, 'usage_left' => 0,'usage_right' => 1,'setting_id' => $setting_id]);
                            if($request->memo){
                                $scene_comment = Scenes_Comment::create(['scene_id' => $exist_update_first_page->id, 'memo' => $request->memo]);
                            }
                        }else{
                            // 更新する必要がなかった
                            $scene = Scene::where('id', $exist_update_first_page->id)
                                ->update(['first_page' => $first_page, 'final_page' => $final_page, 'quantity' => $quantity, 'decision' => $decision, 'setting_id' => $setting_id]);
                            if($request->memo){
                                $scene_comment = Scenes_Comment::create(['scene_id' => $exist_update_first_page->id, 'memo' => $request->memo]);
                            }
                        }
                    }else if(!$confirm['usage'] && $usage){
                        // 中間公演が0→1、卒業公演が1→1
                        if(!$confirm['usage_left'] && $usage_left){
                            // 中間公演が0→1、上手が0→1
                            $scene = Scene::where('id', $exist_update_first_page->id)
                                     ->update(['first_page' => $first_page, 'final_page' => $final_page, 'quantity' => $quantity, 'decision' => $decision, 'usage' => 1, 'usage_left' => 1, 'usage_right' => 0, 'setting_id' => $setting_id]);
                            if($request->memo){
                                $scene_comment = Scenes_Comment::create(['scene_id' => $exist_update_first_page->id, 'memo' => $request->memo]);
                            }
                        }else if(!$confirm['usage_right'] && $usage_right){
                            // 中間公演が0→1、下手が0→1
                            $scene = Scene::where('id', $exist_update_first_page->id)
                                     ->update(['first_page' => $first_page, 'final_page' => $final_page, 'quantity' => $quantity, 'decision' => $decision, 'usage' => 1, 'usage_left' => 0, 'usage_right' => 1, 'setting_id' => $setting_id]);
                            if($request->memo){
                                $scene_comment = Scenes_Comment::create(['scene_id' => $exist_update_first_page->id, 'memo' => $request->memo]);
                            }
                        }else{
                            // 中間公演が0→1
                            $scene = Scene::where('id', $exist_update_first_page->id)
                                     ->update(['first_page' => $first_page, 'final_page' => $final_page, 'quantity' => $quantity, 'decision' => $decision, 'usage' => 1, 'setting_id' => $setting_id]);
                            if($request->memo){
                                $scene_comment = Scenes_Comment::create(['scene_id' => $exist_update_first_page->id, 'memo' => $request->memo]);
                            }
                        }
                    }else if(!$confirm['usage_guraduation'] && $usage_guraduation){
                        // 中間公演が1→1、卒業公演が0→1
                        if(!$confirm['usage_left'] && $usage_left){
                            // 卒業公演が0→1、上手が0→1
                            $scene = Scene::where('id', $exist_update_first_page->id)
                                     ->update(['first_page' => $first_page, 'final_page' => $final_page, 'quantity' => $quantity, 'decision' => $decision, 'usage_guraduation' => 1, 'usage_left' => 1, 'usage_right' => 0,'setting_id' => $setting_id]);
                            if($request->memo){
                                $scene_comment = Scenes_Comment::create(['scene_id' => $exist_update_first_page->id, 'memo' => $request->memo]);
                            }
                        }else if(!$confirm['usage_right'] && $usage_right){
                            // 卒業公演が0→1、下手が0→1
                            $scene = Scene::where('id', $exist_update_first_page->id)
                                     ->update(['first_page' => $first_page, 'final_page' => $final_page, 'quantity' => $quantity, 'decision' => $decision, 'usage_guraduation' => 1, 'usage_left' => 0,'usage_right' => 1, 'setting_id' => $setting_id]);
                            if($request->memo){
                                $scene_comment = Scenes_Comment::create(['scene_id' => $exist_update_first_page->id, 'memo' => $request->memo]);
                            }
                        }else{
                            // 卒業公演が0→1
                            $scene = Scene::where('id', $exist_update_first_page->id)
                                     ->update(['first_page' => $first_page, 'final_page' => $final_page, 'quantity' => $quantity, 'decision' => $decision, 'usage_guraduation' => 1, 'setting_id' => $setting_id]);
                            if($request->memo){
                                $scene_comment = Scenes_Comment::create(['scene_id' => $exist_update_first_page->id, 'memo' => $request->memo]);
                            }
                        }
                    }else if(!$confirm['usage'] && !$usage && $confirm['usage_guraduation'] && $usage_guraduation){
                        // 中間公演が0→0、卒業公演が1→1
                        if((!$confirm['usage_left'] && $usage_left) || (!$confirm['usage_right'] && $usage_right)){
                            // 上手が0→1または下手が0→1
                            $scene = Scene::where('id', $exist_update_first_page->id)
                                     ->update(['first_page' => $first_page, 'final_page' => $final_page, 'quantity' => $quantity, 'decision' => $decision, 'usage_left' => $usage_left, 'usage_right' => $usage_right, 'setting_id' => $setting_id]);
                            if($request->memo){
                                $scene_comment = Scenes_Comment::create(['scene_id' => $id, 'memo' => $request->memo]);
                            }
                        }else{
                            // 更新する必要がなかった
                            $scene = Scene::where('id', $exist_update_first_page->id)
                                ->update(['first_page' => $first_page, 'final_page' => $final_page, 'quantity' => $quantity, 'decision' => $decision, 'setting_id' => $setting_id]);
                            if($request->memo){
                                $scene_comment = Scenes_Comment::create(['scene_id' => $exist_update_first_page->id, 'memo' => $request->memo]);
                            }
                        }
                    }else{
                        // 更新する必要がなかった
                        $scene = Scene::where('id', $exist_update_first_page->id)
                            ->update(['first_page' => $first_page, 'final_page' => $final_page, 'quantity' => $quantity, 'decision' => $decision, 'setting_id' => $setting_id]);
                        if($request->memo){
                            $scene_comment = Scenes_Comment::create(['scene_id' => $exist_update_first_page->id, 'memo' => $request->memo]);
                        }
                    }
                }else{
                    // 使用しないなら更新しない
                    $scene = Scene::where('id', $exist_update_first_page->id)
                        ->update(['first_page' => $first_page, 'final_page' => $final_page, 'quantity' => $quantity, 'decision' => $decision, 'setting_id' => $setting_id]);
                    if($request->memo){
                        $scene_comment = Scenes_Comment::create(['scene_id' => $exist_update_first_page->id, 'memo' => $request->memo]);
                    }
                }
                
                // 決定か
                if(!$decision && $scene){
                    // decision: 0
                    $decision = Scene::where('id', '<>', $exist_update_first_page->id)
                        ->where('prop_id', $request->prop_id)->where('decision', 1)->first();
                    if(empty($decision)){
                        $affected_prop = Prop::where('id', $request->prop_id)
                            ->update(['decision' => 0]);
                    }
                }else{
                    $affected_prop = Prop::where('id', $request->prop_id)
                        ->update(['decision' => 1]);
                }

                // プリセットについて
                $first_preset = 0; // 4は変えない
                $preset_scene = 0; // 登録ページがないのに変えたかどうか 
                if($first_page){
                    // 新規投稿はページ登録有り
                    $presets = Scene::where('id', '<>', $exist_update_first_page->id)
                                ->where('prop_id', $request->prop_id)
                                ->where('first_page', '>=', 1)
                                ->select('first_page', 'usage_left', 'usage_right')
                                ->get()->toArray();

                    if(count($presets)){
                        // 使用ページ有り
                        // ソートしたい要素の値を配列に入れる
                        foreach ($presets as $preset => $colum) {
                            $ArrPage[] = $colum['first_page'];
                        }            
                        // ソートする
                        array_multisort($ArrPage, SORT_ASC, SORT_NUMERIC, $presets);

                        if($presets[0]['first_page'] >= $first_page){
                            // 既存の登録ページよりも前、つまり変更する可能性あり
                            if($presets[0]['usage_left']){
                                $first_preset = 1;
                            }else if($presets[0]['usage_right']){
                                $first_preset = 2;
                            }else{
                                $first_preset = 0;
                            }
                        }else{
                            $first_preset = 4;
                        }    
                    }else{
                        // 使用シーンなしまたはページ数の登録なし
                        $presets = Scene::where('id', '<>', $exist_update_first_page->id)
                                ->where('prop_id', $request->prop_id)
                                ->where('first_page', '=', null)
                                ->select('first_page', 'usage_left', 'usage_right')
                                ->get()->toArray();
                        if(count($presets)){
                            // ページ数の登録なし
                            $prop_preset = Prop::where('id', $request->prop_id)
                                            ->where('preset', '>=', 0)
                                            ->select('preset')
                                            ->first();
                            if($prop_preset){
                                // 小道具にプリセットの登録があった、念のため変えておく
                                $preset_scene = 1;
                            }  
                        }
                    }
                }else{
                    // 新規投稿にページ数なし
                    $presets = Scene::where('id', '<>', $exist_update_first_page->id)
                                ->where('prop_id', $request->prop_id)
                                ->where('first_page', '>=', 1)
                                ->select('first_page', 'usage_left', 'usage_right')
                                ->get()->toArray();
                    if(!count($presets)){
                        // 使用シーンなしまたはページ数の登録なし
                        $presets = Scene::where('id', '<>', $exist_update_first_page->id)
                                ->where('prop_id', $request->prop_id)
                                ->select('first_page', 'usage_left', 'usage_right')
                                ->get()->toArray();
                        if(count($presets)){
                            // ページ数の登録なし
                            $prop_preset = Prop::where('id', $request->prop_id)
                                            ->where('preset', '>', 0)
                                            ->select('preset')
                                            ->first();
                            if($prop_preset){
                                // 小道具にプリセットの登録があった、念のため変えておく
                                $preset_scene = 1;
                            }else{
                                // 小道具にプリセットの登録がない、登録しない
                                $first_preset = 4;
                            }
                        }
                    }else{
                        // 使用シーンがある、登録しない
                        $first_preset = 4;
                    }
                }

                if($first_preset === 0){
                    if($usage_left){
                        $preset = 1;
                    }else if($usage_right){
                        $preset = 2;
                    }else{
                        $preset = 0;
                    }
                }else if($first_preset !==4){
                    if($usage_left){
                        $preset = 1;
                    }else if($usage_right){
                        $preset = 2;
                    }else{
                        $preset = 0;
                    }
                }

                if($first_preset !== 4){
                    $preset_affect = Prop::where('id', $request->prop_id)
                                        ->update(['preset'=> $preset]);
                }

                DB::commit();

                if($preset_scene){
                    // プリセットの位置が指示通り
                    // ただし使用シーンなしまたはページ数なし
                    // レスポンスコードは205(Reset Content)を返却する
                    return response($scene, 205) ?? abort(404);
                }else{
                    // プリセットの位置が指示通り
                    // リソースの新規作成なので
                    // レスポンスコードは201(CREATED)を返却する
                    return response($scene, 201) ?? abort(404);
                }
                
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
                                     ->update(['final_page' => $final_page, 'quantity' => $quantity, 'decision' => $decision, 'usage' => $usage, 'usage_guraduation' => $usage_guraduation, 'usage_left' => $usage_left, 'usage_right' => $usage_right, 'setting_id' => $setting_id]);
                        if($request->memo){
                            $scene_comment = Scenes_Comment::create(['scene_id' => $id, 'memo' => $request->memo]);
                        }
                    }else if($confirm['usage'] && $confirm['usage_guraduation']){
                        // 中間公演が1→1、卒業公演が1→1
                        if(!$confirm['usage_left'] && $usage_left){
                            // 上手が0→1
                            $scene = Scene::where('id', $id)
                                     ->update(['final_page' => $final_page, 'quantity' => $quantity, 'decision' => $decision, 'usage_left' => 1, 'usage_right' => 0, 'setting_id' => $setting_id]);
                            if($request->memo){
                                $scene_comment = Scenes_Comment::create(['scene_id' => $id, 'memo' => $request->memo]);
                            }
                        }else if(!$confirm['usage_right'] && $usage_right){
                            // 下手が0→1
                            $scene = Scene::where('id', $id)
                                     ->update(['final_page' => $final_page, 'quantity' => $quantity, 'decision' => $decision, 'usage_left' => 0, 'usage_right' => 1, 'setting_id' => $setting_id]);
                            if($request->memo){
                                $scene_comment = Scenes_Comment::create(['scene_id' => $id, 'memo' => $request->memo]);
                            }
                        }else{
                            // 変更なし
                            $scene = Scene::where('id', $eid)
                                     ->update(['final_page' => $final_page, 'quantity' => $quantity, 'decision' => $decision, 'setting_id' => $setting_id]);
                            if($request->memo){
                                $scene_comment = Scenes_Comment::create(['scene_id' => $id, 'memo' => $request->memo]);
                            }
                        }
                    }else if(!$confirm['usage'] && $usage){
                        // 中間公演が0→1、卒業公演が1→1
                        if(!$confirm['usage_left'] && $usage_left){
                            // 中間公演が0→1、上手が0→1
                            $scene = Scene::where('id', $id)
                                     ->update(['final_page' => $final_page, 'quantity' => $quantity, 'decision' => $decision, 'usage' => 1, 'usage_left' => 1, 'usage_right' => 0, 'setting_id' => $setting_id]);
                            if($request->memo){
                                $scene_comment = Scenes_Comment::create(['scene_id' => $id, 'memo' => $request->memo]);
                            }
                        }else if(!$confirm['usage_right'] && $usage_right){
                            // 中間公演が0→1、下手が0→1
                            $scene = Scene::where('id', $id)
                                     ->update(['first_page' => $first_page, 'final_page' => $final_page, 'quantity' => $quantity, 'decision' => $decision, 'usage' => 1, 'usage_left' => 0,'usage_right' => 1, 'setting_id' => $setting_id]);
                            if($request->memo){
                                $scene_comment = Scenes_Comment::create(['scene_id' => $id, 'memo' => $request->memo]);
                            }
                        }else{
                            // 中間公演が0→1
                            $scene = Scene::where('id', $id)
                                     ->update(['first_page' => $first_page, 'final_page' => $final_page, 'quantity' => $quantity, 'decision' => $decision, 'usage' => 1, 'setting_id' => $setting_id]);
                            if($request->memo){
                                $scene_comment = Scenes_Comment::create(['scene_id' => $id, 'memo' => $request->memo]);
                            }
                        }
                    }else if(!$confirm['usage_guraduation'] && $usage_guraduation){
                        // 中間公演が1→1、卒業公演が0→1
                        if(!$confirm['usage_left'] && $usage_left){
                            // 卒業公演が0→1、上手が0→1
                            $scene = Scene::where('id', $id)
                                     ->update(['final_page' => $final_page, 'quantity' => $quantity, 'decision' => $decision, 'usage_guraduation' => 1, 'usage_left' => 1, 'usage_right' => 0, 'setting_id' => $setting_id]);
                            if($request->memo){
                                $scene_comment = Scenes_Comment::create(['scene_id' => $id, 'memo' => $request->memo]);
                            }
                        }else if(!$confirm['usage_right'] && $usage_right){
                            // 卒業公演が0→1、下手が0→1
                            $scene = Scene::where('id', $id)
                                     ->update(['final_page' => $final_page, 'quantity' => $quantity, 'decision' => $decision, 'usage_guraduation' => 1, 'usage_right' => 1, 'usage_right' => 0, 'setting_id' => $setting_id]);
                            if($request->memo){
                                $scene_comment = Scenes_Comment::create(['scene_id' => $id, 'memo' => $request->memo]);
                            }
                        }else{
                            // 卒業公演が0→1
                            $scene = Scene::where('id', $id)
                                     ->update(['final_page' => $final_page, 'quantity' => $quantity, 'decision' => $decision, 'usage_guraduation' => 1, 'setting_id' => $setting_id]);
                            if($request->memo){
                                $scene_comment = Scenes_Comment::create(['scene_id' => $id, 'memo' => $request->memo]);
                            }
                        }
                    }else if(!$confirm['usage'] && !$usage && $confirm['usage_guraduation'] && $usage_guraduation){
                        // 中間公演が0→0、卒業公演が1→1
                        if((!$confirm['usage_left'] && $usage_left) || (!$confirm['usage_right'] && $usage_right)){
                            // 上手が0→1または下手が0→1
                            $scene = Scene::where('id', $id)
                                     ->update(['final_page' => $final_page, 'quantity' => $quantity, 'decision' => $decision, 'usage_left' => $usage_left, 'usage_right' => $usage_right, 'setting_id' => $setting_id]);
                            if($request->memo){
                                $scene_comment = Scenes_Comment::create(['scene_id' => $id, 'memo' => $request->memo]);
                            }
                        }else{
                            // 変更なし
                            $scene = Scene::where('id', $id)
                                     ->update(['final_page' => $final_page, 'quantity' => $quantity, 'decision' => $decision, 'setting_id' => $setting_id]);
                            if($request->memo){
                                $scene_comment = Scenes_Comment::create(['scene_id' => $id, 'memo' => $request->memo]);
                            }
                        }
                    }
                }else{
                    // 使用しないなら更新しない
                    $scene = Scene::where('id', $id)
                        ->update(['final_page' => $final_page, 'quantity' => $quantity, 'decision' => $decision, 'setting_id' => $setting_id]);
                    if($request->memo){
                        $scene_comment = Scenes_Comment::create(['scene_id' => $id, 'memo' => $request->memo]);
                    }
                }

                // 決定か
                if(!$decision){
                    // decision: 0
                    $decision = Scene::where('id', '<>', $id)
                            ->where('prop_id', $request->prop_id)->where('decision', 1)->first();
                    if(empty($decision)){
                        $affected_prop = Prop::where('id', $request->prop_id)
                             ->update(['decision' => 0]);
                    }
                }else{
                    $affected_prop = Prop::where('id', $request->prop_id)
                             ->update(['decision' => 1]);
                }

                // プリセットについて
                $first_preset = 0; // 4は変えない
                $preset_scene = 0; // 登録ページがないのに変えたかどうか 
                if($first_page){
                    // 新規投稿はページ登録有り
                    $presets = Scene::where('id', '<>', $id)
                                ->where('prop_id', $request->prop_id)
                                ->where('first_page', '>=', 1)
                                ->select('first_page', 'usage_left', 'usage_right')
                                ->get()->toArray();

                    if(count($presets)){
                        // 使用ページ有り
                        // ソートしたい要素の値を配列に入れる
                        foreach ($presets as $preset => $colum) {
                            $ArrPage[] = $colum['first_page'];
                        }            
                        // ソートする
                        array_multisort($ArrPage, SORT_ASC, SORT_NUMERIC, $presets);

                        if($presets[0]['first_page'] >= $first_page){
                            // 既存の登録ページよりも前、つまり変更する可能性あり
                            if($presets[0]['usage_left']){
                                $first_preset = 1;
                            }else if($presets[0]['usage_right']){
                                $first_preset = 2;
                            }else{
                                $first_preset = 0;
                            }
                        }else{
                            $first_preset = 4;
                        }    
                    }else{
                        // 使用シーンなしまたはページ数の登録なし
                        $presets = Scene::where('id', '<>', $id)
                                ->where('prop_id', $request->prop_id)
                                ->where('first_page', '=', null)
                                ->select('first_page', 'usage_left', 'usage_right')
                                ->get()->toArray();
                        if(count($presets)){
                            // ページ数の登録なし
                            $prop_preset = Prop::where('id', $request->prop_id)
                                            ->where('preset', '>=', 0)
                                            ->select('preset')
                                            ->first();
                            if($prop_preset){
                                // 小道具にプリセットの登録があった、念のため変えておく
                                $preset_scene = 1;
                            }  
                        }
                    }
                }else{
                    // 新規投稿にページ数なし
                    $presets = Scene::where('id', '<>', $id)
                                ->where('prop_id', $request->prop_id)
                                ->where('first_page', '>=', 1)
                                ->select('first_page', 'usage_left', 'usage_right')
                                ->get()->toArray();
                    if(!count($presets)){
                        // 使用シーンなしまたはページ数の登録なし
                        $presets = Scene::where('id', '<>', $id)
                                ->where('prop_id', $request->prop_id)
                                ->select('first_page', 'usage_left', 'usage_right')
                                ->get()->toArray();
                        if(count($presets)){
                            // ページ数の登録なし
                            $prop_preset = Prop::where('id', $request->prop_id)
                                            ->where('preset', '>', 0)
                                            ->select('preset')
                                            ->first();
                            if($prop_preset){
                                // 小道具にプリセットの登録があった、念のため変えておく
                                $preset_scene = 1;
                            }else{
                                // 小道具にプリセットの登録がない、登録しない
                                $first_preset = 4;
                            }
                        }
                    }else{
                        // 使用シーンがある、登録しない
                        $first_preset = 4;
                    }
                }

                if($first_preset === 0){
                    if($usage_left){
                        $preset = 1;
                    }else if($usage_right){
                        $preset = 2;
                    }else{
                        $preset = 0;
                    }
                }else if($first_preset !==4){
                    if($usage_left){
                        $preset = 1;
                    }else if($usage_right){
                        $preset = 2;
                    }else{
                        $preset = 0;
                    }
                }

                if($first_preset !== 4){
                    $preset_affect = Prop::where('id', $request->prop_id)
                                        ->update(['preset'=> $preset]);
                }

                DB::commit();

                if($preset_scene){
                    // プリセットの位置が指示通り
                    // ただし使用シーンなしまたはページ数なし
                    // レスポンスコードは205(Reset Content)を返却する
                    return response($scene, 205) ?? abort(404);
                }else{
                    // プリセットの位置が指示通り
                    // リソースの新規作成なので
                    // レスポンスコードは201(CREATED)を返却する
                    return response($scene, 201) ?? abort(404);
                }
                

            }else if(!($exist)){
                // 新規投稿
                $scene = Scene::create(['character_id' => $request->character_id, 'prop_id' => $request->prop_id, 'first_page' => $first_page, 'final_page' => $final_page, 'quantity' => $quantity, 'decision' => $decision, 'usage' => $usage, 'usage_guraduation' => $usage_guraduation, 'usage_left' => $usage_left, 'usage_right' => $usage_right, 'setting_id' => $setting_id]);
                if($request->memo){
                    $scene_comment = Scenes_Comment::create(['scene_id' => $scene->id, 'memo' => $request->memo]);
                }

                // 決定か
                if(!$decision){
                    // decision: 0
                    $decision = Scene::where('id', '<>', $scene->id)
                        ->where('prop_id', $request->prop_id)->where('decision', 1)->first();
                    if(empty($decision)){
                        $affected_prop = Prop::where('id', $request->prop_id)
                            ->update(['decision' => 0]);
                    }
                }else{
                    $affected_prop = Prop::where('id', $request->prop_id)
                            ->update(['decision' => 1]);
                }

                // プリセットについて
                $first_preset = 0; // 4は変えない
                $preset_scene = 0; // 登録ページがないのに変えたかどうか 
                if($first_page){
                    // 新規投稿はページ登録有り
                    $presets = Scene::where('id', '<>', $scene->id)
                                ->where('prop_id', $request->prop_id)
                                ->where('first_page', '>=', 1)
                                ->select('first_page', 'usage_left', 'usage_right')
                                ->get()->toArray();

                    if(count($presets)){
                        // 使用ページ有り
                        // ソートしたい要素の値を配列に入れる
                        foreach ($presets as $preset => $colum) {
                            $ArrPage[] = $colum['first_page'];
                        }            
                        // ソートする
                        array_multisort($ArrPage, SORT_ASC, SORT_NUMERIC, $presets);

                        if($presets[0]['first_page'] >= $first_page){
                            // 既存の登録ページよりも前、つまり変更する可能性あり
                            if($presets[0]['usage_left']){
                                $first_preset = 1;
                            }else if($presets[0]['usage_right']){
                                $first_preset = 2;
                            }else{
                                $first_preset = 0;
                            }
                        }else{
                            $first_preset = 4;
                        }    
                    }else{
                        // 使用シーンなしまたはページ数の登録なし
                        $presets = Scene::where('id', '<>', $scene->id)
                                ->where('prop_id', $request->prop_id)
                                ->where('first_page', '=', null)
                                ->select('first_page', 'usage_left', 'usage_right')
                                ->get()->toArray();
                        if(count($presets)){
                            // ページ数の登録なし
                            $prop_preset = Prop::where('id', $request->prop_id)
                                            ->where('preset', '>=', 0)
                                            ->select('preset')
                                            ->first();
                            if($prop_preset){
                                // 小道具にプリセットの登録があった、念のため変えておく
                                $preset_scene = 1;
                            }  
                        }
                    }
                }else{
                    // 新規投稿にページ数なし
                    $presets = Scene::where('id', '<>', $scene->id)
                                ->where('prop_id', $request->prop_id)
                                ->where('first_page', '>=', 1)
                                ->select('first_page', 'usage_left', 'usage_right')
                                ->get()->toArray();
                    if(!count($presets)){
                        // 使用シーンなしまたはページ数の登録なし
                        $presets = Scene::where('id', '<>', $scene->id)
                                ->where('prop_id', $request->prop_id)
                                ->select('first_page', 'usage_left', 'usage_right')
                                ->get()->toArray();
                        if(count($presets)){
                            // ページ数の登録なし
                            $prop_preset = Prop::where('id', $request->prop_id)
                                            ->where('preset', '>', 0)
                                            ->select('preset')
                                            ->first();
                            if($prop_preset){
                                // 小道具にプリセットの登録があった、念のため変えておく
                                $preset_scene = 1;
                            }else{
                                // 小道具にプリセットの登録がない、登録しない
                                $first_preset = 4;
                            }
                        }else{
                            // 使用シーンなし
                            $first_preset = 4;
                        }
                    }else{
                        // 使用シーンがある、登録しない
                        $first_preset = 4;
                    }
                }

                if($first_preset === 0){
                    if($usage_left){
                        $preset = 1;
                    }else if($usage_right){
                        $preset = 2;
                    }else{
                        $preset = 0;
                    }
                }else if($first_preset !==4){
                    if($usage_left){
                        $preset = 1;
                    }else if($usage_right){
                        $preset = 2;
                    }else{
                        $preset = 0;
                    }
                }

                if($first_preset !== 4){
                    $preset_affect = Prop::where('id', $request->prop_id)
                                        ->update(['preset'=> $preset]);
                }

                DB::commit();

                if($preset_scene){
                    // プリセットの位置が指示通り
                    // ただし使用シーンなしまたはページ数なし
                    // レスポンスコードは205(Reset Content)を返却する
                    return response($scene, 205) ?? abort(404);
                }else{
                    // プリセットの位置が指示通り
                    // リソースの新規作成なので
                    // レスポンスコードは201(CREATED)を返却する
                    return response($scene, 201) ?? abort(404);
                }
                
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
        $quantity = !empty($request->quantity) ? $request->quantity : 1;
        $decision = !empty($request->decision) ? 1 : 0;
        $usage = !empty($request->usage) ? 1 : 0;
        $usage_guraduation = !empty($request->usage_guraduation) ? 1 : 0;
        $usage_left = !empty($request->usage_left) ? 1 : 0;
        $usage_right = !empty($request->usage_right) ? 1 : 0;
        $setting_id = !empty($request->setting_id) ? $request->setting_id : null;

        try {
            $affected = Scene::where('id', $id)
                   ->update(['character_id' => $request->character_id, 'prop_id' => $request->prop_id, 'first_page' => $first_page, 'final_page' => $final_page, 'quantity' => $quantity, 'decision' => $decision, 'usage' => $usage, 'usage_guraduation' => $usage_guraduation, 'usage_left' => $usage_left, 'usage_right' => $usage_right, 'setting_id' => $setting_id]);
                       
            // 決定か
            if(!$decision){
                // decision: 0
                $decision = Scene::where('id', '<>', $id)
                        ->where('prop_id', $request->prop_id)->where('decision', 1)->first();
                if(empty($decision)){
                    $affected_prop = Prop::where('id', $request->prop_id)
                         ->update(['decision' => 0]);
                }
            }else{
                // decision: 1
                $affected_prop = Prop::where('id', $request->prop_id)
                         ->update(['decision' => 1]);
            }
            
            // プリセットについて
            $first_preset = 0; // 4は変えない
            $preset_scene = 0; // 登録ページがないのに変えたかどうか 
            if($first_page){
                // 編集シーンはページ登録有り
                $presets = Scene::where('id', '<>', $id)
                            ->where('prop_id', $request->prop_id)
                            ->where('first_page', '>=', 1)
                            ->select('first_page', 'usage_left', 'usage_right')
                            ->get()->toArray();

                if(count($presets)){
                    // 使用ページ有り
                    // ソートしたい要素の値を配列に入れる
                    foreach ($presets as $preset => $colum) {
                        $ArrPage[] = $colum['first_page'];
                    }            
                    // ソートする
                    array_multisort($ArrPage, SORT_ASC, SORT_NUMERIC, $presets);

                    if($presets[0]['first_page'] >= $first_page){
                        // 既存の登録ページよりも前、つまり変更する可能性あり
                        if($presets[0]['usage_left']){
                            $first_preset = 1;
                        }else if($presets[0]['usage_right']){
                            $first_preset = 2;
                        }else{
                            $first_preset = 0;
                        }
                    }else{
                        $first_preset = 4;
                    }    
                }else{
                    // 使用シーンなしまたはページ数の登録なし
                    $presets = Scene::where('id', '<>', $id)
                            ->where('prop_id', $request->prop_id)
                            ->where('first_page', '=', null)
                            ->select('first_page', 'usage_left', 'usage_right')
                            ->get()->toArray();
                    if(count($presets)){
                        // ページ数の登録なし
                        $prop_preset = Prop::where('id', $request->prop_id)
                                        ->where('preset', '>=', 0)
                                        ->select('preset')
                                        ->first();
                        if($prop_preset){
                            // 小道具にプリセットの登録があった、念のため変えておく
                            $preset_scene = 1;
                        }  
                    }
                }
            }else{
                // 編集シーンにページ数なし
                $presets = Scene::where('id', '<>', $id)
                            ->where('prop_id', $request->prop_id)
                            ->where('first_page', '>=', 1)
                            ->select('first_page', 'usage_left', 'usage_right')
                            ->get()->toArray();
                if(!count($presets)){
                    // 使用シーンなしまたはページ数の登録なし
                    $presets = Scene::where('id', '<>', $id)
                            ->where('prop_id', $request->prop_id)
                            ->select('first_page', 'usage_left', 'usage_right')
                            ->get()->toArray();
                    if(count($presets)){
                        // ページ数の登録なし
                        $prop_preset = Prop::where('id', $request->prop_id)
                                        ->where('preset', '>', 0)
                                        ->select('preset')
                                        ->first();
                        if($prop_preset){
                            // 小道具にプリセットの登録があった、念のため変えておく
                            $preset_scene = 1;
                        }else{
                            // 小道具にプリセットの登録がない、登録しない
                            $first_preset = 4;
                        }
                    }else{
                        // 使用シーンなし
                        $first_preset = 4;
                    }
                }else{
                    // 使用シーンがある、登録しない
                    $first_preset = 4;
                }
            }

            if($first_preset === 0){
                if($usage_left){
                    $preset = 1;
                }else if($usage_right){
                    $preset = 2;
                }else{
                    $preset = 0;
                }
            }else if($first_preset !==4){
                if($usage_left){
                    $preset = 1;
                }else if($usage_right){
                    $preset = 2;
                }else{
                    $preset = 0;
                }
            }

            if($first_preset !== 4){
                $preset_affect = Prop::where('id', $request->prop_id)
                                    ->update(['preset'=> $preset]);
            }
            DB::commit();
        }catch (\Exception $exception) {
            DB::rollBack();
            
            throw $exception;
        }

        if($preset_scene){
            // プリセットの位置が指示通り
            // ただし使用シーンなしまたはページ数なし
            // レスポンスコードは205(Reset Content)を返却する
            return response($scene, 205) ?? abort(404);
        }else{
            // プリセットの位置が指示通り
            // リソースの新規作成なので
            // レスポンスコードは204(No Content)を返却する
           return response($affected, 204) ?? abort(404);
        }
    }
    
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update_many(Request $request, $id_s)
    {
        $ids_string = explode(',', $id_s);
        $ids = [];
        foreach ($ids_string as $id) {
            $ids[] = (int) $id;
        }
        $yes_no = !empty($request->yes_no) ? 1 : 0;
        $prop_ids = $request->prop_ids;
        
        if($request->method == 'decision'){
            // これで決定か
            $affected= Scene::whereIn('id', $ids)
                    ->update(['decision' => $yes_no]);

            // Prop
            if(!$yes_no){
                // decision: 0
                $decision_scene_ids = Scene::whereIn('prop_id', $prop_ids)
                           ->where('decision', 1)->select('prop_id')->get()->toArray(); // こうすると配列に
                
                $decision_ids = '';
                if(count($decision_scene_ids)){
                    if(is_array($decision_scene_ids[0])){
                        // 複数の$prop_idsについて検索すると、各$prop_idsの結果が配列になる
                        $decision_scene_ids_dupli = $decision_scene_ids;
                        $decision_scene_ids = [];
                        foreach($decision_scene_ids_dupli as $decision_scene_each_ids){
                          if(is_array($decision_scene_each_ids)){
                            // 一つの$prop_idsにつき複数の使用シーンのdecisionが1の場合、配列になる
                            foreach($decision_scene_each_ids as $id){
                                $decision_scene_ids[] = $id;
                            }
                          }else{
                            $decision_scene_ids = $decision_scene_each_ids;
                          }
                        }
                    }

                    $decision_ids = array_diff($prop_ids, $decision_scene_ids); // 差分（重複していたら取得しない）
                }else{
                    $decision_ids = $prop_ids;
                }
                
                if(count($decision_ids)){
                    $affected_prop = Prop::whereIn('id', $decision_ids)
                         ->update(['decision' => 0]);
                }
            }else{
                // decision: 1
                $affected_prop = Prop::whereIn('id', $prop_ids)
                         ->update(['decision' => 1]);
            }

            // レスポンスコードは204(No Content)を返却する
            return response($affected, 204) ?? abort(404);
        }else if($request->method == 'usage'){
            // 中間発表で使用するか
            $affected= Scene::whereIn('id', $ids)
                    ->update(['usage' => $yes_no]);

            // Prop
            if(!$yes_no){
                // usage: 0
                $usage_scene_ids = Scene::whereIn('prop_id', $prop_ids)
                                 ->where('usage', 1)->select('prop_id')->get()->toArray();
                
                $usage_ids = '';
                if(count($usage_scene_ids)){
                    if(is_array($usage_scene_ids[0])){
                        // 複数の$prop_idsについて検索すると、各$prop_idsの結果が配列になる
                        $usage_scene_ids_dupli = $usage_scene_ids;
                        $usage_scene_ids = [];
                        foreach($usage_scene_ids_dupli as $usage_scene_each_ids){
                          if(is_array($usage_scene_each_ids)){
                            // 一つの$prop_idsにつき複数の使用シーンのusageが1の場合、配列になる
                            foreach($usage_scene_each_ids as $id){
                                $usage_scene_ids[] = $id;
                            }
                          }else{
                            $usage_scene_ids = $usage_scene_each_ids;
                          }
                        }
                    }

                    $usage_ids = array_diff($prop_ids, $usage_scene_ids); // 差分（重複していたら取得しない）
                }else{
                    $usage_ids = $prop_ids;
                }
                
                if(count($usage_ids)){
                    $affected_prop = Prop::whereIn('id', $usage_ids)
                         ->update(['usage' => 0]);
                }
            }else{
                // usage: 1
                $affected_prop = Prop::whereIn('id', $prop_ids)
                         ->update(['usage' => 1]);
            }

            // レスポンスコードは204(No Content)を返却する
            return response($affected, 204) ?? abort(404);
        }else if($request->method == 'usage_guraduation'){
            // 卒業公演で使用するか
            if(!$yes_no){
                // usage_guraduation: 0 
                $affected= Scene::whereIn('id', $ids)
                    ->update(['usage_guraduation' => 0, 'usage_left' => 0, 'usage_right' => 0]);
                
                // Prop
                $usage_guraduation_scene_ids = Scene::whereIn('prop_id', $prop_ids)
                        ->where('usage_guraduation', 1)->select('prop_id')->get()->toArray();
                $usage_left_scene_ids = Scene::whereIn('prop_id', $prop_ids)
                        ->where('usage_left', 1)->select('prop_id')->get()->toArray();
                $usage_right_scene_ids = Scene::whereIn('prop_id', $prop_ids)
                        ->where('usage_right', 1)->select('prop_id')->get()->toArray();

                $usage_guraduation_ids = ''; // usage_guraduation、usage_left、usage_rightすべて0にする
                $usage_left_ids = ''; // usage_leftだけ0にする
                $usage_right_ids = ''; // usage_rightだけ0にする
                if(count($usage_guraduation_scene_ids)){
                    // 卒業公演、上手、下手全て0
                    if(is_array($usage_guraduation_scene_ids[0])){
                        // 複数の$prop_idsについて検索すると、各$prop_idsの結果が配列になる
                        $usage_guraduation_scene_ids_dupli = $usage_guraduation_scene_ids;
                        $usage_guraduation_scene_ids = [];
                        foreach($usage_guraduation_scene_ids_dupli as $usage_guraduation_scene_each_ids){
                            if(is_array($usage_guraduation_scene_each_ids)){
                                // 一つの$prop_idsにつき複数の使用シーンのdecisionが1の場合、配列になる
                                foreach($usage_guraduation_scene_each_ids as $id){
                                    $usage_guraduation_scene_ids[] = $id;
                                }
                            }else{
                                $usage_guraduation_scene_ids = $usage_guraduation_scene_each_ids;
                            }
                        }
                    }
        
                    $usage_guraduation_ids = array_diff($prop_ids, $usage_guraduation_scene_ids); // 差分（重複していたら取得しない）
                }else{
                    $usage_guraduation_ids = $prop_ids;
                }

                if(count($usage_left_scene_ids)){
                    // 上手0
                    if(is_array($usage_left_scene_ids[0])){
                        // 複数の$prop_idsについて検索すると、各$prop_idsの結果が配列になる
                        $usage_left_scene_ids_dupli = $usage_left_scene_ids;
                        $usage_left_scene_ids = [];
                        foreach($usage_left_scene_ids_dupli as $usage_left_scene_each_ids){
                            if(is_array($usage_left_scene_each_ids)){
                                // 一つの$prop_idsにつき複数の使用シーンのdecisionが1の場合、配列になる
                                foreach($usage_left_scene_each_ids as $id){
                                    $usage_left_scene_ids[] = $id;
                                }
                            }else{
                                $usage_left_scene_ids = $usage_left_scene_each_ids;
                            }
                        }
                    }
        
                    $usage_left_ids = array_diff($prop_ids, $usage_left_scene_ids); // 差分（重複していたら取得しない）
                }else{
                    $usage_left_ids = $prop_ids;
                }

                if(count($usage_right_scene_ids)){
                    // 下手0
                    if(is_array($usage_right_scene_ids[0])){
                        // 複数の$prop_idsについて検索すると、各$prop_idsの結果が配列になる
                        $usage_right_scene_ids_dupli = $usage_right_scene_ids;
                        $usage_right_scene_ids = [];
                        foreach($usage_right_scene_ids_dupli as $usage_right_scene_each_ids){
                            if(is_array($usage_right_scene_each_ids)){
                                // 一つの$prop_idsにつき複数の使用シーンのdecisionが1の場合、配列になる
                                foreach($usage_right_scene_each_ids as $id){
                                    $usage_right_scene_ids[] = $id;
                                }
                            }else{
                                $usage_right_scene_ids = $usage_right_scene_each_ids;
                            }
                        }
                    }
        
                    $usage_right_ids = array_diff($prop_ids, $usage_right_scene_ids); // 差分（重複していたら取得しない）
                }else{
                    $usage_right_ids = $prop_ids;
                }
                      
                if(count($usage_guraduation_ids)){
                    // 卒業公演、上手、下手全て0
                    $affected_prop = Prop::whereIn('id', $usage_guraduation_ids)
                        ->update(['usage_guraduation' => 0, 'usage_left' => 0, 'usage_right' => 0]);
                }

                if(count($usage_left_ids)){
                    // 上手0
                    $affected_prop = Prop::whereIn('id', $usage_left_ids)
                        ->update(['usage_left' => 0]);
                }

                if(count($usage_right_ids)){
                    // 下手0
                    $affected_prop = Prop::whereIn('id', $usage_right_ids)
                        ->update(['usage_right' => 0]);
                }
                
            }else{
                // usage_guraduation: 0 
                $affected= Scene::whereIn('id', $ids)
                    ->update(['usage_guraduation' => 1]);
                
                // usage_guraduation: 1
                $affected_prop = Prop::whereIn('id', $prop_ids)
                         ->update(['usage_guraduation' => 1]);
            }

            // レスポンスコードは204(No Content)を返却する
            return response($affected, 204) ?? abort(404);
        }else if($request->method == 'usage_left'){
            // 上手で使用するか    
            if(!$yes_no){
                // usage_left: 0
                $affected= Scene::whereIn('id', $ids)
                    ->update(['usage_left' => 0]);
                
                $usage_left_scene_ids = Scene::whereIn('prop_id', $prop_ids)
                    ->where('usage_left', 1)->select('prop_id')->get()->toArray(); // こうすると配列に
                
                $usage_left_ids = '';
                if(count($usage_left_scene_ids)){
                    if(is_array($usage_left_scene_ids[0])){
                        // 複数の$prop_idsについて検索すると、各$prop_idsの結果が配列になる
                        $usage_left_scene_ids_dupli = $usage_left_scene_ids;
                        $usage_left_scene_ids = [];
                        foreach($usage_left_scene_ids_dupli as $usage_left_scene_each_ids){
                            if(is_array($usage_left_scene_each_ids)){
                                // 一つの$prop_idsにつき複数の使用シーンのdecisionが1の場合、配列になる
                                foreach($usage_left_scene_each_ids as $id){
                                    $usage_left_scene_ids[] = $id;
                                }
                            }else{
                                $usage_left_scene_ids = $usage_left_scene_each_ids;
                            }
                        }
                    }
    
                    $usage_left_ids = array_diff($prop_ids, $usage_left_scene_ids); // 差分（重複していたら取得しない）
                }else{
                    $usage_left_ids = $prop_ids;
                }
                 
                if(count($usage_left_ids)){
                    $affected_prop = Prop::whereIn('id', $usage_left_ids)
                        ->update(['usage_left' => 0]);
                }
            }else{
                // usage_left: 1
                $affected= Scene::whereIn('id', $ids)
                    ->where('usage_right', 0)->update(['usage_guraduation' => 1, 'usage_left' => 1]);

                $affected_prop = Prop::whereIn('id', $prop_ids)
                    ->update(['usage_guraduation' => 1, 'usage_left' => 1]);         
            }

            // レスポンスコードは204(No Content)を返却する
            return response($affected, 204) ?? abort(404);
        }else if($request->method == 'usage_right'){
            // 下手で使用するか
            if(!$yes_no){
                // usage_right: 0
                $affected= Scene::whereIn('id', $ids)
                    ->update(['usage_right' => 0]);
                
                $usage_right_scene_ids = Scene::whereIn('prop_id', $prop_ids)
                    ->where('usage_right', 1)->select('prop_id')->get()->toArray(); // こうすると配列に
                
                $usage_right_ids = '';
                if(count($usage_right_scene_ids)){
                    if(is_array($usage_right_scene_ids[0])){
                        // 複数の$prop_idsについて検索すると、各$prop_idsの結果が配列になる
                        $usage_right_scene_ids_dupli = $usage_right_scene_ids;
                        $usage_right_scene_ids = [];
                        foreach($usage_right_scene_ids_dupli as $usage_right_scene_each_ids){
                            if(is_array($usage_right_scene_each_ids)){
                                // 一つの$prop_idsにつき複数の使用シーンのdecisionが1の場合、配列になる
                                foreach($usage_right_scene_each_ids as $id){
                                    $usage_right_scene_ids[] = $id;
                                }
                            }else{
                                $usage_right_scene_ids = $usage_right_scene_each_ids;
                            }
                        }
                    }
    
                    $usage_right_ids = array_diff($prop_ids, $usage_right_scene_ids); // 差分（重複していたら取得しない）
                }else{
                    $usage_right_ids = $prop_ids;
                }
                 
                if(count($usage_right_ids)){
                    $affected_prop = Prop::whereIn('id', $usage_right_ids)
                        ->update(['usage_right' => 0]);
                }
            }else{
                // usage_right: 1
                $affected= Scene::whereIn('id', $ids)
                    ->where('usage_left', 0)->update(['usage_guraduation' => 1, 'usage_right' => 1]);

                $affected_prop = Prop::whereIn('id', $prop_ids)
                    ->update(['usage_guraduation' => 1, 'usage_right' => 1]);         
            }

            // レスポンスコードは204(No Content)を返却する
            return response($affected, 204) ?? abort(404);
        }else if($request->method == 'setting'){
            if(!$yes_no){
                // setting: 0
                $affected= Scene::whereIn('id', $ids)
                                          ->update(['setting_id' => null]);
            }

            // レスポンスコードは204(No Content)を返却する
            return response($affected, 204) ?? abort(404);
        }
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

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy_many($id_s)
    {
        $ids = explode(',', $id_s);
        DB::beginTransaction();

        try {
            $scene = Scene::whereIn('id', $ids)
                        ->delete(); 
                

            DB::commit();
        }catch (\Exception $exception) {
            DB::rollBack();
            
            throw $exception;
        }

        return response($scene, 204) ?? abort(404);
    }
}
