<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Owners extends Model
{
    /** JSONに含める属性 */
    protected $owners = [
        'id', 'name',
    ];    

    /**
     * リレーションシップ - propsテーブル
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function prop()
    {
        return $this->hasMany('App\Models\Props')->orderBy('id', 'desc');
    }
}
