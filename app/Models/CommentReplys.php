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
class CommentReplys extends BaseModel
{
    protected $table = 'comment_replys';

    protected $guarded = ["id"];

    public function user()
    {
        return $this->belongsTo(Users::class, 'id', 'user_id');
    }

    public function comment()
    {
        return $this->belongsTo(UserComments::class, 'id', 'comment_id');
    }
}
