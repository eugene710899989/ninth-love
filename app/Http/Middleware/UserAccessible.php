<?php
/**
 * Created by PhpStorm.
 * User: raina
 * Date: 2017/10/9
 * Time: 上午11:40
 */

namespace App\Http\Middleware;

use EasyWeChat\Kernel\Exceptions\BadRequestException;
use Firebase\JWT\JWT;

trait UserAccessible
{
    public function user()
    {
        if (UserHelper::$user) {
            return UserHelper::$user;
        }
        /** @var \Illuminate\Http\Request $request */
        $request = app('request');
        $jwt_secret = config("jwt.secret");
        $algorithm = config("jwt.algorithm", 'HS256');
        $header = $request->header();
        $cookie = $request->cookie();
        if (isset($header["authorization"])) {
            $token = $header["authorization"][0];
            $array = explode(" ", $token);
            $len = count($array);
            if ($len < 1) {
                throw new BadRequestException("Missing Token", 401);
            }
            $t_token = $array[$len - 1];
        } else if (isset($cookie['token'])) {
            $t_token = $cookie['token'];
        } else if ($request->input('token')) {
            $t_token = $request->input('token');
        }
        $payload = [];
        if (isset($t_token)) {
            $payload = JWT::decode($t_token, $jwt_secret, [$algorithm]);

        }
        return UserHelper::instance((array)$payload);
    }
}
