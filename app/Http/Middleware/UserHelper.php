<?php
/**
 * Created by PhpStorm.
 * User: raina
 * Date: 2017/10/9
 * Time: 上午11:43
 */

namespace App\Helpers;

use App\Exceptions\Jwt\AuthAttrErrorException;
use App\Exceptions\Jwt\NoTokenException;
use App\Models\Campa\User;
use App\Models\Campa\UserExtend;
use App\Models\Edu\Student;
use App\Models\Task\StudentCamp;
use App\Services\CreditService;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;

class UserHelper
{
    /**
     * @var UserHelper
     */
    public static $user;
    
    public $openid;
    public $unionid;
    public $id;
    public $plan_id;
    public $start_time;
    public static $student;
    public $student_id;
    public $wx_app_id = 0;
    //开学第几天
    public $day_index;
    //开学第几周
    public $week_index;
    public static $credit_privilege;
    /**
     * @var StudentCamp
     */
    public static $student_camp;
    public $app;
    public $from_app = false;
    public $payload = [];
    public $app_version;
    public $app_system;
    public $app_system_version;
    public $from_wx_app_id;//最近来源公众号ID
    
    const IOS = 'ios';
    const ANDROID = 'android';
    
    protected $attr = [
        "oid"       => "openid",
        "sub"       => "unionid",
        "id"        => "id",
        "app"       => "app",
        'wx_app_id' => "wx_app_id"
//        'pid' => "plan_id"
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
            static::$student = Student::where('unionid', $this->unionid)->first();
            $this->student_id = static::$student->id ?? 0;
            $this->plan_id = Request::route()[2]['plan_id'] ?? 0;
            $this->from_wx_app_id = User::where('id', $this->id)->value('from_wx_app_id') ?? 0;
            self::$credit_privilege = $this->student_id ? CreditService::getPrivilege($this->student_id)->keyBy('key') : null;//学分特权
            if ($this->plan_id && $this->student_id) {
                self::$student_camp = StudentCamp::where([
                    'plan_id'    => $this->plan_id,
                    'student_id' => $this->student_id,
                ])->first();
                if (self::$student_camp) {
                    $this->start_time = self::$student_camp->start_time;
                    $this->day_index = $this->getDayIndex($this->start_time);
                    $this->week_index = PlanHelper::getWeekIndex($this->start_time, Carbon::now());
                }
            }
            $this->from_app = strpos($this->app, 'app') !== false;
            if (Request::hasHeader('pandaclass')) {
                $app_information = explode('|', strtolower(Request::header('pandaclass')));
                if (count($app_information) > 0) {
                    $this->app_version = $app_information[1] ?? '0.0.0'; //app版本
                    $this->app_system = $app_information[2] ?? '';//app系统 ios or android
                    $this->app_system_version = $app_information[3] ?? '0.0.0';//app系统版本
                }
                
            }
        } catch (\Exception $e) {
            throw new AuthAttrErrorException($e->getMessage(), 401);
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
        if (is_null(self::$user)) {
            if (count($payload) == 0) {
                throw new NoTokenException("no authorization", 401);
            }
            self::$user = new self($payload);
        }
        return self::$user;
    }
    
    /**
     * @return mixed
     */
    public function getOpenid()
    {
        return $this->openid;
    }
    
    public function getApp()
    {
        return $this->app;
    }
    
    /**
     * @return mixed
     */
    public function getUnionid()
    {
        return $this->unionid;
    }
    
    public function getPlanId()
    {
        return $this->plan_id;
    }
    
    public function getStartTime()
    {
        return $this->start_time;
    }
    
    public function getStudentId()
    {
        return $this->student_id;
    }
    
    //获取公众号openid
    public function getWebOpenid()
    {
        $openid = UserExtend::where('user_id', $this->id)->where('plan_id', '>', 5)->where('openid', 'like', 'ooXv%')->value('openid') ?? $this->openid;
        return $openid;
    }
    
    /**
     * 设置plan_id
     *
     * @param $plan_id
     */
    public function setPlanId($plan_id)
    {
        $this->plan_id = $plan_id;
    }
    
    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }
    
    public function toArray()
    {
        $array = [];
        foreach ($this->attr as $k => $v) {
            $array["$v"] = $this->$v;
        }
        return $array;
    }
    
    /**
     * 获取当前开学第几天
     * @param $start_time
     * @return int
     */
    public static function getDayIndex($start_time)
    {
        return Carbon::now()->diffInDays(Carbon::parse($start_time)) + 1;
    }
    
    private function processLog($payload)
    {
        $content = Request::getContent();
        $content = json_decode($content, 1) ? json_decode($content, 1) : $content;
        $log = [
            'type'    => 'request-log',
            'time'    => date('Y-m-d H:i:s'),
            'method'  => Request::method(),
            'path'    => Request::path(),
            'content' => $content,
            'route'   => Request::route(),
            'jwt'     => $payload,
        ];
        
        $log_file_path = storage_path('logs/request.log');
        
        File::append($log_file_path, json_encode($log, JSON_UNESCAPED_UNICODE) . "\n");
//        Log::info(json_encode($log, JSON_UNESCAPED_UNICODE));

//        $log_string = 'request-log [' . date('Y-m-d H:i:s') . '][' . Request::method() . ']' . '[' . Request::path() . ']';
//        $log_string .= "| content:" . $content;
//        $log_string .= "| route:" . json_encode(Request::route(), JSON_UNESCAPED_UNICODE);
//        $log_string .= "| jwt: " . json_encode($payload);
//        Log::info($log_string);
    }
    
    public function getWeekIndex()
    {
        return $this->week_index;
    }
    
    public function getFromWxAppId()
    {
        return $this->from_wx_app_id;
    }
}
