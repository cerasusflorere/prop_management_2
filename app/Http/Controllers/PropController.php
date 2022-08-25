<?php

namespace App\Http\Controllers;

use Cloudinary;
use App\Models\Prop;
use App\Models\Props_Comment;
use App\Http\Requests\StorePhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PropController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $props = Prop::select('id', 'name', 'kana')->orderBy('kana')->get();

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
        if($request->photo){
            // Cloudinaryにファイルを保存する
            $result = $request->photo->storeOnCloudinary('prop_management');
            $url = $result->getSecurePath(); 
            $public_id = $result->getPublicId();
        } else {
            $public_id = null;
            $url = null;
        }
        $owner_id = !empty($request->owner_id)? $request->owner_id : null; // nullで送ると文字列になる
        $usage = !empty($request->usage) ? 1 : null;
        

        DB::beginTransaction();

        try {
            $prop = Prop::create(['name' => $request->name, 'kana' => $request->kana, 'owner_id' => $owner_id, 'public_id' => $public_id, 'url' => $url, 'usage' => $usage]);
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
        return response($prop, 201);
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
              ->with(['owner', 'prop_comments', 'scenes', 'scenes.character', 'scenes.scene_comments'])->first();

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
        if($request->method == 'usage_change'){
            $usage = Prop::where('id', $id)
              ->select('usage')->first();
            if($usage !== 1){
                $affected = Prop::where('id', $id)
                   ->update(['usage' => 1]);
            }        

            return $affected;
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
        //
    }
}
