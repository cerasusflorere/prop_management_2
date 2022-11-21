<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Owner extends Model
{
    /** JSONに含める属性 */
    protected $visible = [
        'id', 'name', 'props', 'scenes'
    ];

    /** 登録時にJSONに含める属性 */
    protected $fillable = [
        'name'
    ];

    /**
     * リレーションシップ - propsテーブル
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function props()
    {
        return $this->hasMany('App\Models\Prop');
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
