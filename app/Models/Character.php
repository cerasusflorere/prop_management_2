<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Character extends Model
{
    /** JSONに含める属性 */
    protected $visible = [
         'section', 'id', 'name', 
         'scenes'
    ];

    /** 登録時にJSONに含める属性 */
    protected $fillable = [
        'order', 'section_id', 'name',
    ];

    /**
     * リレーションシップ - sectionsテーブル
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function section()
    {
        return $this->belongsTo('App\Models\Section', 'section_id', 'id', 'sections');
    }


    /**
     * リレーションシップ - scenesテーブル
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function scenes()
    {
        return $this->hasMany('App\Models\Scene');
    }
}
