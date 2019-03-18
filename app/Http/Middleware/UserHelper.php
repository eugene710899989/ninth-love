<?php

namespace App\Http\Middleware;

use App\Exceptions\Jwt\NoTokenException;
use App\Models\Users;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Request;

class UserHelper
{
    /**
     * @var UserHelper
     */
    public static $instance;
    public static $user;

    public $openid;
    public $user_id;
    public $payload = [];

    const IOS = 'ios';
    const ANDROID = 'android';

    protected $attr = [
        "oid" => "openid",
        "uid" => "user_id",
    ];

    private function __construct($payload = [])
    {
        try {
            $this->payload = $payload;
            foreach ($this->attr as $key => $value) {
                if (isset($payload[$key])) {
                    $this->$value = $payload[$key];
                }
            }
            static::$user = Users::find($this->user_id);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), 401);
        }

    }

    /**
     * 单例
     *
     * @param array $payload
     *
     * @return null|UserHelper
     * @throws NoTokenException
     */
    public static function instance($payload = [])
    {
        if (is_null(self::$instance)) {
            if (count($payload) == 0) {
                throw new NoTokenException("no authorization", 401);
            }
            self::$instance = new self($payload);
        }
        return self::$instance;
    }

    /**
     * @return mixed
     */
    public function getOpenid()
    {
        return $this->openid;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->user_id;
    }

    public function toArray()
    {
        $array = [];
        foreach ($this->attr as $k => $v) {
            $array["$v"] = $this->$v;
        }
        return $array;
    }


}
