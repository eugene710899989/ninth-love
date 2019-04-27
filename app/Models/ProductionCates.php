<?php
/**
 * Created by PhpStorm.
 * User: chotow
 * Date: 2019-01-10
 * Time: 22:24
 */

namespace App\Models;

/**
 * @property mixed agree
 */
class ProductionCates extends BaseModel
{
    protected $table = 'production_cates';

    protected $guarded = ["id"];

    function productions()
    {
        return $this->hasMany(Productions::class, "cate_id", "id");
    }

    function seller()
    {
        return $this->hasOne(Sellers::class, "id", "seller_id");
    }

}
