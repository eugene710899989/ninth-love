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
class UserComments extends BaseModel
{
    protected $table = 'user_comments';

    protected $guarded = ["id"];

    public function user()
    {
        return $this->belongsTo(Users::class, 'id', 'user_id');
    }
}
