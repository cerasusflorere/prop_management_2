<?php

namespace App\Http\Controllers;

use Cloudinary;
use App\Models\Prop;
use App\Models\Props_Comment;
use App\Models\Scene;
use App\Http\Requests\StorePhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;


class PropController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $props = Prop::select('id', 'name', 'kana', 'quantity')->orderBy('kana')->get();

        return $props;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index_all()
    {
        $props = Prop::with('owner', 'prop_comments')->orderBy('kana')->orderBy('owner_id')->orderBy('created_at')->get();

        return $props;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StorePhoto $request_photo
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $folder = 'prop_management_local';
        if($request->photo){
            // Cloudinaryにファイルを保存する
            $result = $request->photo->storeOnCloudinary($folder);
            $url = $result->getSecurePath(); 
            $public_id = $result->getPublicId();
        } else {
            $public_id = null;
            $url = null;
        }
        $owner_id = !empty($request->owner_id)? $request->owner_id : null; // nullで送ると文字列になる
        $quantity = !empty($request->quantity) ? $request->quantity : 0;
        $location = !empty($request->location) ? 1 : 0;
        $handmade = 0;
        if($request->handmade == 1){
            $handmade = 1;
        }else if($request->handmade == 2){
            $handmade = 2;
        }else if($request->handmade == 3){
            $handmade = 3;
        }
        $decision = !empty($request->decision) ? 1 : 0;
        $usage = !empty($request->usage) ? 1 : 0;
        $usage_guraduation = !empty($request->usage_guraduation) ? 1 : 0;
        $usage_left = !empty($request->usage_left) ? 1 : 0;
        $usage_right = !empty($request->usage_right) ? 1 : 0;
        $preset = 0;
        if($request->preset == 1){
            $preset = 1;
        }else if($request->preset == 2){
            $preset = 2;
        }

        DB::beginTransaction();

        try {
            $prop = Prop::create(['name' => $request->name, 'kana' => $request->kana, 'owner_id' => $owner_id, 'quantity' => $quantity, 'location' => $location, 'handmade' => $handmade, 'decision' => $decision, 'public_id' => $public_id, 'url' => $url, 'usage' => $usage, 'usage_guraduation' => $usage_guraduation, 'usage_left' => $usage_left, 'usage_right' => $usage_right, 'preset' => $preset]);
            if($request->memo){
                $prop_comment = Props_Comment::create(['prop_id' => $prop->id, 'memo' => $request->memo]);
            }            

            DB::commit();
        }catch (\Exception $exception) {
            DB::rollBack();
            if($request->photo){
                // DBとの不整合を避けるためアップロードしたファイルを削除
                Cloudinary::destroy($public_id);
            }
            
            throw $exception;
        }

        // リソースの新規作成なので
        // レスポンスコードは201(CREATED)を返却する
        return response($prop, 201) ?? abort(404);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $prop = Prop::where('id', $id)
              ->with(['owner', 'prop_comments', 'scenes', 'scenes.character', 'scenes.character.section', 'scenes.scene_comments'])->first();

        return $prop ?? abort(404);
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
        $folder = 'prop_management_local';
        $first_preset = 0;
        $preset_scene = 0;
        if($request->preset){
            $presets = Scene::where('prop_id', $id)
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

                if($presets[0]['usage_left']){
                    $first_preset = 1;
                }else if($presets[0]['usage_right']){
                    $first_preset = 2;
                }else{
                    $first_preset = 0;
                }
            }else{
                // 使用シーンなしまたはページ数の登録なし
                $preset_scene = 1;
            }
        }

        if($request->method == 'usage_change'){
            // 小道具投稿時にしようとした場合
            $usage = Prop::where('id', $id)
                    ->select('usage')->first();
            if(!empty($usage)){
                $affected = Prop::where('id', $id)
                   ->update(['usage' => 1]);
            }

            // レスポンスコードは204(No Content)を返却する
            return response($affected, 204) ?? abort(404);

        }else if($request->method == 'usage_left_change'){
            // 小道具投稿時にしようとした場合
            $usage_left = Prop::where('id', $id)
                   ->select('usage_left')->first();
            if(!empty($usage_left)){
                $affected = Prop::where('id', $id)
                   ->update(['usage_guraduation' => 1, 'usage_left' => 1]);
            }

            // レスポンスコードは204(No Content)を返却する
            return response($affected, 204) ?? abort(404);

        }else if($request->method == 'usage_right_change'){
            // 小道具投稿時にしようとした場合
            $usage_right = Prop::where('id', $id)
                   ->select('usage_right')->first();
            if(!empty($usage_right)){
                $affected = Prop::where('id', $id)
                   ->update(['usage_guraduation' => 1, 'usage_right' => 1]);
            }

            // レスポンスコードは204(No Content)を返却する
            return response($affected, 204) ?? abort(404);

        }else if($request->method == 'usage_guraduation_change'){
            // 小道具投稿時にしようとした場合
            $usage_guraduation = Prop::where('id', $id)
                   ->select('usage_guraduation')->first();
            if(!empty($usage_guraduation)){
                $affected = Prop::where('id', $id)
                   ->update(['usage_guraduation' => 1]);
            }

            // レスポンスコードは204(No Content)を返却する
            return response($affected, 204) ?? abort(404);

        }else if($request->method == 'photo_non_update'){
            // 写真更新しない
            $owner_id = !empty($request->owner_id)? $request->owner_id : null; // nullで送ると文字列になる
            $quantity = !empty($request->quantity) ? $request->quantity : 1;
            $location = !empty($request->location) ? 1 : 0;
            $handmade = 0;
            if($request->handmade == 1){
                $handmade = 1;
            }else if($request->handmade == 2){
                $handmade = 2;
            }else if($request->handmade == 3){
                $handmade = 3;
            }
            $decision = !empty($request->decision) ? 1 : 0;
            $usage = !empty($request->usage) ? 1 : 0;
            $usage_guraduation = !empty($request->usage_guraduation) ? 1 : 0;
            $usage_left = !empty($request->usage_left) ? 1 : 0;
            $usage_right = !empty($request->usage_right) ? 1 : 0;
            $no_change_preset = 0;
            if($first_preset === 0){
                if($request->preset == 1){
                   $preset = 1;
                }else if($request->preset == 2){
                   $preset = 2;
                }else{
                   $preset = 0;
                }
            }else{
                if($first_preset != $request->preset){
                    $no_change_preset = 1;
                }
                $preset = $first_preset;
            }
            
            $affected = Prop::where('id', $id)
                   ->update(['name' => $request->name, 'kana' => $request->kana, 'owner_id' => $owner_id, 'quantity' => $quantity, 'location' => $location, 'handmade' => $handmade, 'decision' => $decision, 'usage' => $usage, 'usage_guraduation' => $usage_guraduation, 'usage_left' => $usage_left, 'usage_right' => $usage_right, 'preset' => $preset]);

            if(!$decision){
                $affected_scene = Scene::where('prop_id', $id)
                    ->update(['decision' => 0]);
            }
            if(!$usage){
                $affected_scene = Scene::where('prop_id', $id)
                    ->update(['usage' => 0]);
            }
            if(!$usage_guraduation){
                $affected_scene = Scene::where('prop_id', $id)
                    ->update(['usage_guraduation' => 0, 'usage_left' => 0, 'usage_right' => 0]);
            }else if(!$usage_left && !$usage_right){
                $affected_scene = Scene::where('prop_id', $id)
                    ->update(['usage_left' => 0, 'usage_right' => 0]);
            }else if(!$usage_left){
                $affected_scene = Scene::where('prop_id', $id)
                    ->update(['usage_left' => 0]);
            }else if(!$usage_right){
                $affected_scene = Scene::where('prop_id', $id)
                    ->update(['usage_right' => 0]);
            }
            if($no_change_preset){
                // プリセットの位置が指示通りでない
                // レスポンスコードは206(Partial Content)を返却する
                return response($affected, 206) ?? abort(404);
            }else if($preset_scene){
                // プリセットの位置が指示通り
                // ただし使用シーンなしまたはページ数なし
                // レスポンスコードは205(Reset Content)を返却する
                return response($affected, 205) ?? abort(404);
            }else{
                // プリセットの位置が指示通り
                // レスポンスコードは204(No Content)を返却する
                return response($affected, 204) ?? abort(404);
            }
            

        }else if($request->method == 'photo_store'){
            // 写真新規投稿
            if($request->photo){
                // Cloudinaryにファイルを保存する
                $result = $request->photo->storeOnCloudinary($folder);
                $url = $result->getSecurePath(); 
                $public_id = $result->getPublicId();
            } else {
                $public_id = null;
                $url = null;
            }

            $owner_id = !empty($request->owner_id)? $request->owner_id : null; // nullで送ると文字列になる
            $quantity = !empty($request->quantity) ? $request->quantity : 1;
            $location = !empty($request->location) ? 1 : 0;
            $handmade = 0;
            if($request->handmade == 1){
                $handmade = 1;
            }else if($request->handmade == 2){
                $handmade = 2;
            }else if($request->handmade == 3){
                $handmade = 3;
            }
            $decision = !empty($request->decision) ? 1 : 0;
            $usage = !empty($request->usage) ? 1 : 0;
            $usage_guraduation = !empty($request->usage_guraduation) ? 1 : 0;
            $usage_left = !empty($request->usage_left) ? 1 : 0;
            $usage_right = !empty($request->usage_right) ? 1 : 0;
            $no_change_preset = 0;
            if($first_preset === 0){
                if($request->preset == 1){
                   $preset = 1;
                }else if($request->preset == 2){
                   $preset = 2;
                }else{
                   $preset = 0;
                }
            }else{
                if($first_preset != $request->preset){
                    $no_change_preset = 1;
                }
                $preset = $first_preset;
            }

            DB::beginTransaction();

            try {
                $affected = Prop::where('id', $id)
                             ->update(['name' => $request->name, 'kana' => $request->kana, 'owner_id' => $owner_id, 'quantity' => $quantity, 'location' => $location, 'handmade' => $handmade, 'decision' => $decision, 'public_id' => $public_id, 'url' => $url, 'usage' => $usage, 'usage_guraduation' => $usage_guraduation, 'usage_left' => $usage_left, 'usage_right' => $usage_right, 'preset' => $preset]);
                
                if(!$decision){
                    $affected_scene = Scene::where('prop_id', $id)
                       ->update(['decision' => 0]);
                }
                if(!$usage){
                    $affected_scene = Scene::where('prop_id', $id)
                        ->update(['usage' => 0]);
                }
                
                if(!$usage_guraduation){
                    $affected_scene = Scene::where('prop_id', $id)
                        ->update(['usage_guraduation' => 0, 'usage_left' => 0, 'usage_right' => 0]);
                }else if(!$usage_left && !$usage_right){
                    $affected_scene = Scene::where('prop_id', $id)
                        ->update(['usage_left' => 0, 'usage_right' => 0]);
                }else if(!$usage_left){
                    $affected_scene = Scene::where('prop_id', $id)
                        ->update(['usage_left' => 0]);
                }else if(!$usage_right){
                    $affected_scene = Scene::where('prop_id', $id)
                        ->update(['usage_right' => 0]);
                }

                DB::commit();
            }catch (\Exception $exception) {
                DB::rollBack();
                if($request->photo){
                    // DBとの不整合を避けるためアップロードしたファイルを削除
                    Cloudinary::destroy($public_id);
                }
                
                throw $exception;
            }

            if($no_change_preset){
                // プリセットの位置が指示通りでない
                // レスポンスコードは206(Partial Content)を返却する
                return response($affected, 206) ?? abort(404);
            }else if($preset_scene){
                // プリセットの位置が指示通り
                // ただし使用シーンなしまたはページ数なし
                // レスポンスコードは205(Reset Content)を返却する
                return response($affected, 205) ?? abort(404);
            }else{
                // プリセットの位置が指示通り
                // レスポンスコードは204(No Content)を返却する
                return response($affected, 204) ?? abort(404);
            }

        }else if($request->method == 'photo_delete'){
            // 写真削除
            $owner_id = !empty($request->owner_id)? $request->owner_id : null; // nullで送ると文字列になる
            $quantity = !empty($request->quantity) ? $request->quantity : 1;
            $location = !empty($request->location) ? 1 : 0;
            $handmade = 0;
            if($request->handmade == 1){
                $handmade = 1;
            }else if($request->handmade == 2){
                $handmade = 2;
            }else if($request->handmade == 3){
                $handmade = 3;
            }
            $decision = !empty($request->decision) ? 1 : 0;
            $usage = !empty($request->usage) ? 1 : 0;
            $usage_guraduation = !empty($request->usage_guraduation) ? 1 : 0;
            $usage_left = !empty($request->usage_left) ? 1 : 0;
            $usage_right = !empty($request->usage_right) ? 1 : 0;
            $no_change_preset = 0;
            if($first_preset === 0){
                if($request->preset == 1){
                   $preset = 1;
                }else if($request->preset == 2){
                   $preset = 2;
                }else{
                   $preset = 0;
                }
            }else{
                if($first_preset != $request->preset){
                    $no_change_preset = 1;
                }
                $preset = $first_preset;
            }
            
            DB::beginTransaction();

            try {
                $affected = Prop::where('id', $id)
                             ->update(['name' => $request->name, 'kana' => $request->kana, 'owner_id' => $owner_id, 'quantity' => $quantity, 'location' => $location, 'handmade' => $handmade, 'decision' => $decision, 'public_id' => null, 'url' => null, 'usage' => $usage, 'usage_guraduation' => $usage_guraduation, 'usage_left' => $usage_left, 'usage_right' => $usage_right, 'preset' => $preset]);
                
                if(!$decision){
                    $affected_scene = Scene::where('prop_id', $id)
                        ->update(['decision' => 0]);
                }
                if(!$usage){
                    $affected_scene = Scene::where('prop_id', $id)
                        ->update(['usage' => 0]);
                }
            
                if(!$usage_guraduation){
                    $affected_scene = Scene::where('prop_id', $id)
                        ->update(['usage_guraduation' => 0, 'usage_left' => 0, 'usage_right' => 0]);
                }else if(!$usage_left && !$usage_right){
                    $affected_scene = Scene::where('prop_id', $id)
                        ->update(['usage_left' => 0, 'usage_right' => 0]);
                }else if(!$usage_left){
                    $affected_scene = Scene::where('prop_id', $id)
                        ->update(['usage_left' => 0]);
                }else if(!$usage_right){
                    $affected_scene = Scene::where('prop_id', $id)
                    ->update(['usage_right' => 0]);
                }
                
                DB::commit();

                if(!$affected){
                    throw new Exception('意図されない処理が実行されました。');
                }
    
                if($request->public_id){
                    Cloudinary::destroy($request->public_id);
                }
            }catch (\Exception $exception) {
                DB::rollBack();
                
                throw $exception;
            }

            
            if($no_change_preset){
                // プリセットの位置が指示通りでない
                // レスポンスコードは206(Partial Content)を返却する
                return response($affected, 206) ?? abort(404);
            }else if($preset_scene){
                // プリセットの位置が指示通り
                // ただし使用シーンなしまたはページ数なし
                // レスポンスコードは205(Reset Content)を返却する
                return response($affected, 205) ?? abort(404);
            }else{
                // プリセットの位置が指示通り
                // レスポンスコードは204(No Content)を返却する
                return response($affected, 204) ?? abort(404);
            }
            
        }else if($request->method == 'photo_update'){
            //写真アップデート
            if($request->photo){
                // Cloudinaryにファイルを保存する
                $result = $request->photo->storeOnCloudinary($folder);
                $url = $result->getSecurePath(); 
                $public_id = $result->getPublicId();
            } else {
                $public_id = null;
                $url = null;
            }
            $owner_id = !empty($request->owner_id)? $request->owner_id : null; // nullで送ると文字列になる
            $quantity = !empty($request->quantity) ? $request->quantity : 1;
            $location = !empty($request->location) ? 1 : 0;
            $handmade = 0;
            if($request->handmade == 1){
                $handmade = 1;
            }else if($request->handmade == 2){
                $handmade = 2;
            }else if($request->handmade == 3){
                $handmade = 3;
            }
            $decision = !empty($request->decision) ? 1 : 0;
            $usage = !empty($request->usage) ? 1 : 0;
            $usage_guraduation = !empty($request->usage_guraduation) ? 1 : 0;
            $usage_left = !empty($request->usage_left) ? 1 : 0;
            $usage_right = !empty($request->usage_right) ? 1 : 0;
            $no_change_preset = 0;
            if($first_preset === 0){
                if($request->preset == 1){
                   $preset = 1;
                }else if($request->preset == 2){
                   $preset = 2;
                }else{
                   $preset = 0;
                }
            }else{
                if($first_preset != $request->preset){
                    $no_change_preset = 1;
                }
                $preset = $first_preset;
            }
            
            DB::beginTransaction();

            try {
                $affected = Prop::where('id', $id)
                             ->update(['name' => $request->name, 'kana' => $request->kana, 'owner_id' => $owner_id, 'quantity' => $quantity, 'location' => $location, 'handmade' => $handmade, 'decision' => $decision, 'public_id' => $public_id, 'url' => $url, 'usage' => $usage, 'usage_guraduation' => $usage_guraduation, 'usage_left' => $usage_left, 'usage_right' => $usage_right, 'preset' => $preset]);
                
                if(!$decision){
                    $affected_scene = Scene::where('prop_id', $id)
                        ->update(['decision' => 0]);
                }
                if(!$usage){
                    $affected_scene = Scene::where('prop_id', $id)
                        ->update(['usage' => 0]);
                }
                if(!$usage_guraduation){
                    $affected_scene = Scene::where('prop_id', $id)
                        ->update(['usage_guraduation' => 0, 'usage_left' => 0, 'usage_right' => 0]);
                }else if(!$usage_left && !$usage_right){
                    $affected_scene = Scene::where('prop_id', $id)
                        ->update(['usage_left' => 0, 'usage_right' => 0]);
                }else if(!$usage_left){
                    $affected_scene = Scene::where('prop_id', $id)
                        ->update(['usage_left' => 0]);
                }else if(!$usage_right){
                    $affected_scene = Scene::where('prop_id', $id)
                        ->update(['usage_right' => 0]);
                }

                DB::commit();

                if(!$affected){
                    throw new Exception('意図されない処理が実行されました。');
                }
    
                if($request->public_id){
                    Cloudinary::destroy($request->public_id);
                }
            }catch (\Exception $exception) {
                DB::rollBack();
                
                throw $exception;
            }
            
            if($no_change_preset){
                // プリセットの位置が指示通りでない
                // レスポンスコードは206(Partial Content)を返却する
                return response($affected, 206) ?? abort(404);
            }else if($preset_scene){
                // プリセットの位置が指示通り
                // ただし使用シーンなしまたはページ数なし
                // レスポンスコードは205(Reset Content)を返却する
                return response($affected, 205) ?? abort(404);
            }else{
                // プリセットの位置が指示通り
                // レスポンスコードは204(No Content)を返却する
                return response($affected, 204) ?? abort(404);
            }
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update_deep(Request $request, $id)
    {
        // 使用するか
        if($request->method == 'usage_0_change'){
            // 小道具編集時にしようとした場合
            $usage = Scene::where('id', '<>', $request->id)
                        ->where('prop_id', $id)->where('usage', 1)->first();
            if(empty($usage)){
                $affected = Prop::where('id', $id)
                   ->update(['usage' => 0]);
            }else{
                $affected = 0;
            }

            // レスポンスコードは204(No Content)を返却する
            return response($affected, 204) ?? abort(404);

        }else if($request->method == 'usage_guraduation_left_to_right_change'){
            // 小道具編集時にしようとした場合
            $usage_left = Scene::where('id', '<>', $request->id)
                    ->where('prop_id', $id)->where('usage_left', 1)->first();
            if(empty($usage_left)){
                $affected = Prop::where('id', $id)
                   ->update(['usage_guraduation' => 1, 'usage_left' => 0, 'usage_right' => 1]);
            }else{
                $affected = Prop::where('id', $id)
                   ->update(['usage_guraduation' => 1, 'usage_right' => 1]);
            }

            // レスポンスコードは204(No Content)を返却する
            return response($affected, 204) ?? abort(404);

        }else if($request->method == 'usage_guraduation_right_to_left_change'){
            // 小道具編集時にしようとした場合
            $usage_right = Scene::where('id', '<>', $request->id)
                    ->where('prop_id', $id)->where('usage_right', 1)->first();
            if(empty($usage_right)){
                $affected = Prop::where('id', $id)
                   ->update(['usage_guraduation' => 1, 'usage_left' => 1, 'usage_right' => 0]);
            }else{
                $affected = Prop::where('id', $id)
                   ->update(['usage_guraduation' => 1, 'usage_left' => 1]);
            }

            // レスポンスコードは204(No Content)を返却する
            return response($affected, 204) ?? abort(404);

        }else if($request->method == 'usage_guraduation_1_left_0_change'){
            // 小道具編集時にしようとした場合
            $usage_left = Scene::where('id', '<>', $request->id)
                    ->where('prop_id', $id)->where('usage_left', 1)->first();
            if(empty($usage_left)){
                $affected = Prop::where('id', $id)
                   ->update(['usage_guraduation' => 1, 'usage_left' => 0]);
            }else{
                $affected = Prop::where('id', $id)
                   ->update(['usage_guraduation' => 1]);
            }

            // レスポンスコードは204(No Content)を返却する
            return response($affected, 204) ?? abort(404);

        }else if($request->method == 'usage_guraduation_1_right_0_change'){
            // 小道具編集時にしようとした場合
            $usage_right = Scene::where('id', '<>', $request->id)
                    ->where('prop_id', $id)->where('usage_right', 1)->first();
            if(empty($usage_right)){
                $affected = Prop::where('id', $id)
                   ->update(['usage_guraduation' => 1, 'usage_right' => 0]);
            }else{
                $affected = Prop::where('id', $id)
                   ->update(['usage_guraduation' => 1]);
            }

            // レスポンスコードは204(No Content)を返却する
            return response($affected, 204) ?? abort(404);

        }else if($request->method == 'usage_guraduation_0_left_0_change'){
            // 小道具編集時にしようとした場合
            $usage_guraduation = Scene::where('id', '<>', $request->id)
                    ->where('prop_id', $id)->where('usage_guraduation', 1)->first();
            $usage_left = Scene::where('id', '<>', $request->id)
                    ->where('prop_id', $id)->where('usage_left', 1)->first();
            if(empty($usage_guraduation) || empty($usage_left)){
                if(empty($usage_guraduation)){
                    $affected = Prop::where('id', $id)
                        ->update(['usage_guraduation' => 0, 'usage_left' => 0]);
                }else{
                    $affected = Prop::where('id', $id)
                        ->update(['usage_left' => 0]);
                }
            }else{
                $affected = 0;
            }

            // レスポンスコードは204(No Content)を返却する
            return response($affected, 204) ?? abort(404);

        }else if($request->method == 'usage_guraduation_0_right_0_change'){
            // 小道具編集時にしようとした場合
            $usage_guraduation = Scene::where('id', '<>', $request->id)
                    ->where('prop_id', $id)->where('usage_guraduation', 1)->first();
            $usage_right = Scene::where('id', '<>', $request->id)
                    ->where('prop_id', $id)->where('usage_right', 1)->first();
            if(empty($usage_guraduation) || empty($usage_right)){
                if(empty($usage_guraduation)){
                    $affected = Prop::where('id', $id)
                        ->update(['usage_guraduation' => 0, 'usage_right' => 0]);
                }else{
                    $affected = Prop::where('id', $id)
                        ->update(['usage_right' => 0]);
                }
            }else{
                $affected = 0;
            }

            // レスポンスコードは204(No Content)を返却する
            return response($affected, 204) ?? abort(404);

        }else if($request->method == 'usage_guraduation_0_change'){
            // 小道具編集時にしようとした場合
            $usage_guraduation = Scene::where('id', '<>', $request->id)
                    ->where('prop_id', $id)->where('usage_guraduation', 1)->first();
            if(empty($usage_guraduation)){
                $affected = Prop::where('id', $id)
                    ->update(['usage_guraduation' => 0]);
            }else{
                $affected = 0;
            }

            // レスポンスコードは204(No Content)を返却する
            return response($affected, 204) ?? abort(404);

        }else if($request->method == 'usage_left_to_right_change'){
            // 小道具編集時にしようとした場合
            $usage_left = Scene::where('id', '<>', $request->id)
                    ->where('prop_id', $id)->where('usage_left', 1)->first();
            if(empty($usage_left)){
                $affected = Prop::where('id', $id)
                    ->update(['usage_left' => 0, 'usage_right' => 1]);
            }else{
                $affected = Prop::where('id', $id)
                    ->update(['usage_right' => 1]);
            }

            // レスポンスコードは204(No Content)を返却する
            return response($affected, 204) ?? abort(404);

        }else if($request->method == 'usage_right_to_left_change'){
            // 小道具編集時にしようとした場合
            $usage_right = Scene::where('id', '<>', $request->id)
                    ->where('prop_id', $id)->where('usage_right', 1)->first();
            if(empty($usage_right)){
                $affected = Prop::where('id', $id)
                    ->update(['usage_left' => 1, 'usage_right' => 0]);
            }else{
                $affected = Prop::where('id', $id)
                    ->update(['usage_left' => 1]);
            }

            // レスポンスコードは204(No Content)を返却する
            return response($affected, 204) ?? abort(404);

        }else if($request->method == 'usage_left_0_change'){
            // 小道具編集時にしようとした場合
            $usage_left = Scene::where('id', '<>', $request->id)
                    ->where('prop_id', $id)->where('usage_left', 1)->first();
            if(empty($usage_left)){
                $affected = Prop::where('id', $id)
                    ->update(['usage_left' => 0]);
            }else{
                $affected = 0;
            }

            // レスポンスコードは204(No Content)を返却する
            return response($affected, 204) ?? abort(404);

        }else if($request->method == 'usage_right_0_change'){
            // 小道具編集時にしようとした場合
            $usage_right = Scene::where('id', '<>', $request->id)
                    ->where('prop_id', $id)->where('usage_right', 1)->first();
            if(empty($usage_right)){
                $affected = Prop::where('id', $id)
                    ->update(['usage_right' => 0]);
            }else{
                $affected = 0;
            }

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
        $ids = explode(',', $id_s);
        if($request->method !== 'handmade'){
            $yes_no = !empty($request->yes_no) ? 1 : 0;
        }else{
            $yes_no = intval($request->yes_no);
        }
        
        if($request->method == 'location'){
            // ピッコロに持ってきたか
            $affected= Prop::whereIn('id', $ids)
                    ->update(['location' => $yes_no]);

            // レスポンスコードは204(No Content)を返却する
            return response($affected, 204) ?? abort(404);
        }else if($request->method == 'handmade'){
            // 作るかどうか
            $affected= Prop::whereIn('id', $ids)
                    ->update(['handmade' => $yes_no]);

            // レスポンスコードは204(No Content)を返却する
            return response($affected, 204);
        }else if($request->method == 'decision'){
            // これで決定か
            $affected= Prop::whereIn('id', $ids)
                    ->update(['decision' => $yes_no]);
            
            if(!$yes_no){
                $affected_scene = Scene::whereIn('prop_id', $ids)
                    ->update(['decision' => 0]);
            }

            // レスポンスコードは204(No Content)を返却する
            return response($affected, 204) ?? abort(404);
        }else if($request->method == 'usage'){
            // 中間発表で使用するか
            $affected= Prop::whereIn('id', $ids)
                    ->update(['usage' => $yes_no]);

            if(!$yes_no){
                $affected_scene = Scene::whereIn('prop_id', $ids)
                    ->update(['usage' => 0]);
            }

            // レスポンスコードは204(No Content)を返却する
            return response($affected, 204) ?? abort(404);
        }else if($request->method == 'usage_guraduation'){
            // 卒業公演で使用するか
            if($yes_no){
                $affected= Prop::whereIn('id', $ids)
                    ->update(['usage_guraduation' => 1]);
            }else{
                $affected= Prop::whereIn('id', $ids)
                    ->update(['usage_guraduation' => 0, 'usage_left' => 0, 'usage_right' => 0]);

                $affected_scene = Scene::whereIn('prop_id', $ids)
                    ->update(['usage_guraduation' => 0, 'usage_left' => 0, 'usage_right' => 0]);
            }

            // レスポンスコードは204(No Content)を返却する
            return response($affected, 204) ?? abort(404);
        }else if($request->method == 'usage_left'){
            // 上手で使用するか
            if($yes_no){
                $affected= Prop::whereIn('id', $ids)
                    ->update(['usage_guraduation' => 1, 'usage_left' => 1]);
            }else{
                $affected= Prop::whereIn('id', $ids)
                    ->update(['usage_left' => 0]);
                    
                $affected_scene = Scene::whereIn('prop_id', $ids)
                    ->update(['usage_left' => 0]);
            }            

            // レスポンスコードは204(No Content)を返却する
            return response($affected, 204) ?? abort(404);
        }else if($request->method == 'usage_right'){
            // 下手で使用するか
            if($yes_no){
                $affected= Prop::whereIn('id', $ids)
                    ->update(['usage_guraduation' => 1, 'usage_right' => 1]);
            }else{
                $affected= Prop::whereIn('id', $ids)
                    ->update(['usage_right' => 0]);
                    
                $affected_scene = Scene::whereIn('prop_id', $ids)
                        ->update(['usage_right' => 0]);
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
            $public_id = Prop::select('public_id')
                            ->where('id', $id)->first()->toArray();

            $prop = Prop::where('id', $id)
                        ->delete();      

            DB::commit();

            if(!$prop){
                throw new Exception('意図されない処理が実行されました。');
            }

            if($public_id['public_id']){
                Cloudinary::destroy($public_id['public_id']);
            }

        }catch (\Exception $exception) {
            DB::rollBack();
            
            throw $exception;
        }

        return response($prop, 204) ?? abort(404);
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
            $public_ids_all = Prop::select('public_id')
                            ->find($ids)->toArray();
            $public_ids = [];
            foreach($public_ids_all as $public_id){
                if($public_id['public_id']){
                    array_push($public_ids, $public_id['public_id']);
                }
            }

            $prop = '';
            $prop = Prop::whereIn('id', $ids)
                        ->delete(); 
                

            DB::commit();

            if(!$prop){
                throw new Exception('意図されない処理が実行されました。');
            }

            foreach($public_ids as $public_id){
                Cloudinary::destroy($public_id);
            }

        }catch (\Exception $exception) {
            DB::rollBack();
            
            throw $exception;
        }

        return response($prop, 204) ?? abort(404);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
//     public function down(Request $request)
//     {
//         $origin_datas = $request->toArray();
//         $download_data = [];
//         foreach($origin_datas as $data){
//             $name = $data['name'];
//             if($data['owner']){
//                 $owner = $data['owner']['name'];
//             }else{
//                 $owner = null;
//             }
//             if($data['usage']){
//                 $usage = '〇';
//             }else{
//                 $usage = null;
//             }
//             if($data['prop_comments']){
//                 foreach($data['prop_comments'] as $key => $comment){
//                     if($key === 0){
//                         $memo = $comment['memo'];
//                     }else{
//                         $memo .= PHP_EOL.$comment['memo'];
//                     }                    
//                 }
//             }else{
//                 $memo = null;
//             }
//             $download_data[] = ['name' => $name, 'owner' => $owner, 'usage' => $usage, 'prop_comments' => $memo];
//         };
//         // Spreadsheetオブジェクト生成
//         $objSpreadsheet = new Spreadsheet();
//         // シート設定
//         $objSheet = $objSpreadsheet->getActiveSheet();
    
//         // ウィンドウ固定
//         $objSheet->freezePane('A2');

//         // スタイルオブジェクト取得([A1:D1]セル)
//         $objStyle = $objSheet->getStyle('A1:D1');
//         $objBorders = $objStyle->getBorders();
//         $objBorders->getBottom()->setBorderStyle(Border::BORDER_THICK);

//         // 見出しを緑色をバックに緑色の字に([A1:D1]セル)
//         $objStyle = $objSheet->getStyle('A1:D1');
//         $objStyle->getFont()->getColor()->setARGB('169b62');
//         $objFill = $objStyle->getFill();
//         $objFill->setFillType(Fill::FILL_SOLID);
//         $objFill->getStartColor()->setARGB('ddefe3');

//         // 罫線をつける([A1:D1]セル)
//         $objStyle = $objSheet->getStyle('A1:D1');  
//         $arrStyle = array(
//             'borders' => array(
//                 'allBorders' => array(
//                                     'borderStyle' => Border::BORDER_THICK,
//                                     'color' => array( 'rgb' => 'dcdcdc' )
//                                 )
//             )
//         );
//         //  セルの罫線スタイル設定
//         $objStyle->applyFromArray($arrStyle);

//         // [A1]セルに 小道具名
//         $objSheet->setCellValue('A1', '小道具名');
//         $objSpreadsheet->getActiveSheet()->getColumnDimension('A')->setWidth(12);
//         $objSheet->getStyle('A') ->getAlignment() ->setHorizontal('center');
//         $objSheet->getStyle('A') ->getAlignment() ->setVertical('center');

//         // [B1]セルに 持ち主名
//         $objSheet->setCellValue('B1', '持ち主');
//         $objSpreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(12);
//         $objSheet->getStyle('B') ->getAlignment() ->setHorizontal('center');
//         $objSheet->getStyle('B') ->getAlignment() ->setVertical('center');  
        
//         // [C1]セルに 使用状況
//         $objSheet->setCellValue('C1', '使用するか');
//         $objSpreadsheet->getActiveSheet()->getColumnDimension('C')->setWidth(12);
//         $objSheet->getStyle('C') ->getAlignment() ->setHorizontal('center');
//         $objSheet->getStyle('C') ->getAlignment() ->setVertical('center');

//         // [D1]セルに メモ
//         $objSheet->setCellValue('D1', 'メモ');
//         $objSpreadsheet->getActiveSheet()->getColumnDimension('D')->setWidth(24);
//         $objSheet->getStyle('D') ->getAlignment() ->setHorizontal('center');
//         $objSheet->getStyle('D') ->getAlignment() ->setVertical('center');

//         // データを表示
//         $objSheet->fromArray($download_data, null, 'A2');

        
//         // $writer = new Xlsx($objSpreadsheet);
//         // $writer->save('hello world.xlsx');

//         $writer = new XlsxWriter($objSpreadsheet);
// $fileName = 'test.xlsx';
// header("Content-Description: File Transfer");
// header('Content-Disposition: attachment; filename="'.$fileName.'"');
// header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
// header('Content-Transfer-Encoding: binary');
// header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
// header('Expires: 0');
// ob_end_clean();
// $writer->save('php://output');
// die;
//     }
}
