<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Characters extends Model
{
    /** JSONに含める属性 */
    protected $visible = [
        'id', 'section_id', 'name', 
    ];

    /** 登録時にJSONに含める属性 */
    protected $fillable = [
        'section_id', 'name',
    ];

    /**
     * リレーションシップ - sectionsテーブル
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function section()
    {
        return $this->belongsTo('App\Models\Sections');
    }


    /**
     * リレーションシップ - scenesテーブル
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function scene()
    {
        return $this->hasMany('App\Models\Scenes')->orderBy('id', 'desc');
    }
}
