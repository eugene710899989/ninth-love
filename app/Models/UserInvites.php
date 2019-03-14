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
class UserInvites extends BaseModel
{
    protected $table = 'user_invites';

    const INVITING = 0;
    const REFUSED = -1;
    const PASSED = 1;

    protected $guarded = ["id"];

}
