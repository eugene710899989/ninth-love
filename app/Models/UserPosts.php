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
class UserPosts extends BaseModel
{
    protected $table = 'user_posts';

    protected $guarded = ["id"];

    function comments()
    {
        return $this->hasMany(UserComments::class, 'post_id');
    }

    function userLike()
    {
        return $this->userZans()->where('type', 1);
    }

    function userZans()
    {
        return $this->hasMany(UserZans::class, 'post_id');
    }

    function userDislike()
    {
        return $this->userZans()->where('type', 0);
    }

}
