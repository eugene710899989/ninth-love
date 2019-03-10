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
class Users extends BaseModel
{
    protected $table = 'users';

    protected $guarded = ["id"];
//
//    // ========== 作用域 ==========
//
//    public function scopeOfOperator($query, int $operatorId)
//    {
//        return $query->where('operator_id', $operatorId);
//    }
//
//    public function scopeOfTerm($query, int $termId)
//    {
//        return $query->where('term_id', $termId);
//    }
}
