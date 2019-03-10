<?php
/**
 * Created by PhpStorm.
 * User: chotow
 * Date: 2018/9/4
 * Time: 下午5:38
 */

namespace App\Models;

use Carbon\Carbon;
use Closure;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class BaseModel extends Model
{
    protected $connection = 'mysql';

    protected static $cacheMinutes;

    protected static function boot()
    {
        parent::boot();

        // 保存模型时进行一些前置操作
        self::saving(function ($model) {
            // 清除自我缓存
            $model->forgetSelfCache();
        });
    }

    protected function asJson($value)
    {
        return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    public function cacheSelf()
    {
        if ($this->id) {
            Cache::put($this->getSelfCacheKey(), $this, Carbon::now()->endOfDay());
        }
    }

    public function forgetSelfCache()
    {
        Cache::forget($this->getSelfCacheKey());
    }

    public static function getTableName()
    {
        $instance = new static();

        return join('.', [$instance->getConnection()->getDatabaseName(), $instance->getTable()]);
    }

    public static function cacheFind($id, int $minutes = null, bool $refresh = false, bool $throw = true)
    {
        $cacheKey = self::getTableName() . '_' . $id;

        return self::remember($cacheKey, function () use ($id) {
            return self::findOrFail($id);
        }, $minutes, $refresh, $throw);
    }

    public static function cacheFirstWhere(array $conditions, int $minutes = null, bool $refresh = false, bool $throw = true)
    {
        $cacheKey = join('_', [self::getTableName(), 'whereFirst', self::parseConditions($conditions)]);

        return self::remember($cacheKey, function () use ($conditions) {
            return self::where($conditions)->firstOrFail();
        }, $minutes, $refresh, $throw);
    }

    public static function cacheAllWhere(array $conditions, int $minutes = null, bool $refresh = false)
    {
        $cacheKey = join('_', [self::getTableName(), 'whereAll', self::parseConditions($conditions)]);

        return self::remember($cacheKey, function () use ($conditions) {
            return self::where($conditions)->get();
        }, $minutes, $refresh);
    }

    private static function remember(string $key, Closure $callback, int $minutes = null, bool $refresh = false, bool $throw = true)
    {
        if ($refresh) {
            Cache::forget($key);
        }

        $minutes = $minutes ?: static::$cacheMinutes ?: Carbon::now()->endOfDay();

        try {
            return Cache::remember($key, $minutes, function () use ($callback) {
                return $callback();
            });
        } catch (Exception $e) {
            if ($throw) {
                throw $e;
            } else {
                return null;
            }
        }
    }

    /**
     * 将条件数组转为字符串
     *
     * @param array $conditions
     *
     * @return string
     */
    private static function parseConditions(array $conditions)
    {
        $conditions_value = [];

        foreach ($conditions as $key => $value) {
            $conditions_value[] = "${key}_${value}";
        }

        return join('_', $conditions_value);
    }

    private function getSelfCacheKey()
    {
        return self::getTableName() . '_' . $this->id;
    }
}
