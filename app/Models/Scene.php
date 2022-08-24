<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Scene extends Model
{
    /** 取得時にJSONに含める属性 */
    protected $visible = [
        'id', 'character_id', 'prop_id', 
        'first_page', 'final_page', 'usage', 'character', 'scene_comments'
    ];

    /** JSONに含める属性 */
    protected $fillable = [
        'character_id', 'prop_id', 
        'first_page', 'final_page', 'usage',
    ];

    /**
     * リレーションシップ - charactersテーブル
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function character()
    {
        return $this->belongsTo('App\Models\Character');
    }

    /**
     * リレーションシップ - propsテーブル
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function prop()
    {
        return $this->belongsTo('App\Models\Prop');
    }

    /**
     * リレーションシップ - scenes_commentsテーブル
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function scene_comments()
    {
        return $this->hasMany('App\Models\Scenes_Comment');
    }
}
