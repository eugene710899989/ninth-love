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
 * @property mixed price
 */
class Productions extends BaseModel
{
    protected $table = 'productions';

    protected $guarded = ["id"];

    function cate()
    {
        return $this->hasOne(ProductionCates::class, "id", "cate_id");
    }

}
