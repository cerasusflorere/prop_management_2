<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Scene extends Model
{
    /** 取得時にJSONに含める属性 */
    protected $visible = [
        'id', 'character_id', 'prop_id', 
        'first_page', 'final_page', 'decision', 'usage', 'usage_guraduation', 'usage_left', 'usage_right', 'setting_id', 'quantity', 'created_at', 'updated_at',
        'character', 'prop', 'setting', 'scene_comments'
    ];

    /** 登録時にJSONに含める属性 */
    protected $fillable = [
        'character_id', 'prop_id', 
        'first_page', 'final_page', 'decision', 'usage', 'usage_guraduation', 'usage_left', 'usage_right', 'setting_id', 'quantity'
    ];

    public function getCreatedAtAttribute()
    {
        return Carbon::parse($this->attributes['created_at'])->format('Y/m/d H:i');
    }

    public function getUpdatedAtAttribute()
    {
        return Carbon::parse($this->attributes['updated_at'])->format('Y/m/d H:i');
    }

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
     * リレーションシップ - ownersテーブル
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function setting()
    {
        return $this->belongsTo('App\Models\Owner');
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
