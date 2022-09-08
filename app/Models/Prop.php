<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class Prop extends Model
{
    /** 取得時にJSONに含める属性 */
    protected $visible = [
        'id', 'name', 'kana', 'owner_id', 'public_id',
        'url', 'usage', 'usage_guraduation','usage_left', 'usage_right', 'created_at', 'updated_at',
        'owner', 'prop_comments', 'scenes',
    ];
 
    /** 登録時にJSONに含める属性 */
    protected $fillable = [
        'name', 'kana', 'owner_id', 'public_id',
        'url', 'usage', 'usage_guraduation', 'usage_left', 'usage_right'
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
     * リレーションシップ - ownersテーブル
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function owner()
    {
        return $this->belongsTo('App\Models\Owner');
    }
    
    /**
     * リレーションシップ - props_commetsテーブル
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function prop_comments()
    {
        return $this->hasMany('App\Models\Props_Comment');
    }

    /**
     * リレーションシップ - scenesテーブル
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function scenes()
    {
        return $this->hasMany('App\Models\Scene');
    }

    /**
     * リレーションシップ - sceneps_commentsテーブル
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function scene_comments()
    {
        return $this->hasManyThrough('App\Models\Scene', 'App\Models\Scenes_Comment');
    }
    
}
