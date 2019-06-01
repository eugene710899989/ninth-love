<?php

namespace App\Http\Controllers;

use App\Models\Users;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class WeChatController extends Controller
{

    /**
     * 处理微信的请求消息
     *
     * @return string
     */
    public function serve()
    {
        Log::info('request arrived.'); # 注意：Log 为 Laravel 组件，所以它记的日志去 Laravel 日志看，而不是 EasyWeChat 日志

        $app = app('wechat.mini_program');
        return $app->server->serve();
    }

    public function auth(Request $request){
        $app = app('wechat.mini_program');
        $code = $request->input("code");
        $re = $app->auth->session( $code);
        if(!empty($re["errcode"])){
            abort(401,"错误的数据:{$re["errmsg"]}");
        }
//        $re = ["openid"=>"oKJrM4o7gzv8l0pe5MD2UfJOOlNM"];
        //"session_key" => "v0Rm7ALadiP/EaAGGKgzdg=="
        //  "openid" => "oKJrM4o7gzv8l0pe5MD2UfJOOlNM"
        $user =  Users::cacheFirstWhere(["openid"=>$re["openid"]],100,false,false);
        if (!$user){
            $user= Users::create(["openid"=>$re["openid"]]);
        }
        $jwt = JWT::encode(["uid"=>$user->id,"oid"=>$re["openid"]],config("jwt.secret"));
        return dataResp(["token"=>$jwt]);
    }
}