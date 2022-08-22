<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sections extends Model
{
    /** JSONに含める属性 */
    protected $visible = [
        'id', 'section', 
    ];

    /** 登録時にJSONに含める属性 */
    protected $fillable = [
        'section',
    ];

    /**
     * リレーションシップ - charactersテーブル
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function character()
    {
        return $this->hasMany('App\Models\Characters')->orderBy('id', 'desc');
    }
}
