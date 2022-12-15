<?php

namespace App\Http\Controllers;

use Cloudinary;
use App\Models\Section;
use App\Models\Character;
use App\Models\Owner;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InformationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index_section()
    {
        $sections = Section::all();
        return $sections;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index_character()
    {
        // $characters = Character::with('section')->get();

        // return $characters;
        $sections = Section::with('characters')->get();

        return $sections->toArray();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index_owner()
    {
        $owners = Owner::all();

        return $owners;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store_section(Request $request)
    {
        $section = Section::create(['section' => $request->section]);
        $order = Section::where('id', $section->id)->update(['order' => $section->id]);

        return response($section, 201);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store_character(Request $request)
    {
        $character = Character::create(['order' => 0, 'section_id' => (int)$request->section_id, 'name' => $request->name]);
        $order = Character::where('id', $character->id)->update(['order' => $character->id]);

        return response($character, 201);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store_owner(Request $request)
    {
        $owner = Owner::create(['name' => $request->name]);
        $order = Owner::where('id', $owner->id)->update(['order' => $owner->id]);

        return response($owner, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show_section($id)
    {
        $section = Section::where('id', $id)
                      ->first();

        return $section ?? abort(404);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show_character($id)
    {
        $character = Character::where('id', $id)
                        ->with(['section'])->first();

        return $character ?? abort(404);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show_owner($id)
    {
        $owner = Owner::where('id', $id)
                    ->first();

        return $owner ?? abort(404);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update_section(Request $request, $id)
    {
        $section = Section::where('id', $id)
                     ->update(['section' => $request->section]);

        return $section;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update_character(Request $request, $id)
    {
        $character = Character::where('id', $id)
                          ->update(['section_id' => $request->section_id, 'name' => $request->name]);

        return $character;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update_owner(Request $request, $id)
    {
        $owner = Owner::where('id', $id)
                          ->update(['name' => $request->name]);

        return $owner;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy_section($id)
    {
        $section = Section::where('id', $id)
                          ->delete();

        return $section ?? abort(404);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy_character($id)
    {
        $character = Character::where('id', $id)
                          ->delete();

        return $character ?? abort(404);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy_owner($id)
    {
        DB::beginTransaction();

        try {
            $props = Owner::find($id)
                        ->props->toArray();
            $public_ids_null = array_column($props, 'public_id'); // ない場合$public_id = null
            $public_ids =  array_filter($public_ids_null);

            $owner = Owner::where('id', $id)
                        ->delete();      

            DB::commit();

            if($owner === 0){
                throw new Exception('意図されない処理が実行されました。');
            }

            foreach($public_ids as $public_id){
                if($public_id){
                    Cloudinary::destroy($public_id);
                }                    
            }
        }catch (\Exception $exception) {
            DB::rollBack();
            
            throw $exception;
        }

        return $owner ?? abort(404);
    }
}
