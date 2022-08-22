<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class Props extends Model
{
    /** 取得時にJSONに含める属性 */
    protected $visible = [
        'id', 'name', 'owner_id', 'public_id',
        'url', 'usage',
    ];
 
    /** 登録時にJSONに含める属性 */
    protected $fillable = [
        'name', 'owner_id', 'public_id',
        'url', 'usage',
    ];

    /**
     * リレーションシップ - ownersテーブル
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function owner()
    {
        return $this->belongsTo('App\Models\Owners');
    }

    /**
     * リレーションシップ - scenesテーブル
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function scenes()
    {
        return $this->hasMany('App\Models\Scenes')->orderBy('id', 'desc');
    }

    /**
     * リレーションシップ - props_commetsテーブル
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function prop_comment()
    {
        return $this->hasMany('App\Models\Props_Comments')->orderBy('id', 'desc');
    }
}
