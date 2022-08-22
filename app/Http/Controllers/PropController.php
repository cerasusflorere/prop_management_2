<?php

namespace App\Http\Controllers;

use Cloudinary;
use App\Models\Props;
use App\Models\Props_Comments;
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
        $props = Props::all();

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
        if($request->photo !== 'null'){
            // Cloudinaryにファイルを保存する
            $result = $request->photo->storeOnCloudinary('prop_management');
            $url = $result->getSecurePath(); 
            $public_id = $result->getPublicId();
        } else {
            $public_id = null;
            $url = null;
        }
        $owner_id = $request->owner_id !== 'null' ? $request->owner_id : null; // nullで送ると文字列になる
        $usage = $request->usage !== 'null' ? 1 : null;
        

        DB::beginTransaction();

        try {
            $prop = Props::create(['name' => $request->name, 'owner_id' => $owner_id, 'public_id' => $public_id, 'url' => $url, 'usage' => $usage]);
            if($request->memo !== 'null'){
                $prop_comment = Props_Comments::create(['prop_id' => $prop->id, 'memo' => $request->memo]);
            }            

            DB::commit();
        }catch (\Exception $exception) {
            DB::rollBack();
            if($request->photo !== "null"){
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
        $prop = Props::where('id', $id)
              ->with(['owner', 'prop_comment'])->first();


        return $prop;
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
