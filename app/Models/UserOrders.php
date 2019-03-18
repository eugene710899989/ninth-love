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
class UserOrders extends BaseModel
{
    protected $table = 'user_orders';

    protected $guarded = ["id"];

    protected $casts = ['production_ids' => 'json'];

    public static function OfUser(Users $user)
    {
        return UserOrders::where("user_id", $user->id);
    }

    public function scopeUnpaid($query)
    {
        return $query->where('is_paid', 0);
    }

    public function scopePaid($query)
    {
        return $query->where('is_paid', 1);
    }

    public function seller()
    {
        return $this->hasOne(Sellers::class, "id");
    }


    public function genTransaction()
    {
        return md5($this->id . time());
    }
}
