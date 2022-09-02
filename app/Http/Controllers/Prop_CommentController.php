<?php

namespace App\Http\Controllers;

use App\Models\Props_Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Prop_CommentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StorePhoto $request_photo
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $prop_comment = Props_Comment::create(['prop_id' => $request->prop_id, 'memo' => $request->memo]);
            DB::commit();
        }catch (\Exception $exception) {
            DB::rollBack();
            
            throw $exception;
        }

        // リソースの新規作成なので
        // レスポンスコードは201(CREATED)を返却する
        return response($prop_comment, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        
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
            $affected = Props_Comment::where('id', $id)
                ->update(['memo' => $request->memo]);
                
            DB::commit();
        }catch (\Exception $exception) {
            DB::rollBack();
            
            throw $exception;
        }

        return $affected;
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
            $prop_comment = Props_Comment::where('id', $id)
                        ->delete();      

            DB::commit();

        }catch (\Exception $exception) {
            DB::rollBack();
            
            throw $exception;
        }

        return $prop_comment ?? abort(404);
    }
}
