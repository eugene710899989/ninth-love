<?php
/**
 * Created by PhpStorm.
 * User: chotow
 * Date: 2019-01-10
 * Time: 22:24
 */

namespace App\Models;

class AssistantBind extends BaseModel
{
    protected $table = 'ai_assistant_binds';

    protected $fillable = [
        'operator_id',
        'assistant_id',
    ];

    // ========== 作用域 ==========

    public function scopeOfOperator($query, int $operatorId)
    {
        return $query->where('operator_id', $operatorId);
    }

    public function scopeOfTerm($query, int $termId)
    {
        return $query->where('term_id', $termId);
    }
}
