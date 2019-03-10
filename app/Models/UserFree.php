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
class UserFree extends BaseModel
{
    protected $table = 'user_free';

    protected $guarded = ["id"];

}
