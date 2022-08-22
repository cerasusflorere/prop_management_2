<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Characters extends Model
{
    /** JSONに含める属性 */
    protected $characters = [
        'id', 'name', 
    ];

    /**
     * リレーションシップ - scenesテーブル
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function scene()
    {
        return $this->hasMany('App\Models\Scenes')->orderBy('id', 'desc');
    }
}
