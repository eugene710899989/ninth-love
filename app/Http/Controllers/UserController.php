<?php
/**
 * Created by PhpStorm.
 * User: eugene
 * Date: 2019/3/9
 * Time: 11:20 PM
 */

namespace App\Http\Controllers;


use App\Models\Users;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserController
{

    //用户同意协议接口
    function agree(Users $user)
    {
        if ($user->agree != 0) {
            return errorResp("你已同意协议");
        }
        $user->agree = 1;
        $user->save();
        return noContentResp();
    }

    //用户信息获取接口
    function info(Users $user)
    {
        return dataResp($user);
    }

    //用户信息注册接口
    function init(Request $request, Users $user)
    {
        if ($user->agree != 1) {
            return errorResp("未同意隐私协议", 403);
        }
        $validator = Validator::make($request->all(), [
            'nickname'     => 'required|string',
            'birthday'     => 'required|date',
            'avatar'       => 'required|url',
            'student_id_f' => 'required|url',
            'student_id_b' => 'required|url',
            'school'       => 'required|string',
            'major'        => 'required|string',
            'gender'       => 'required|in:0,1,2',
            'tags'         => 'array',
            'time_areas'   => 'array',
        ]);

        if ($validator->fails()) {
            return errorResp($validator->errors()->first());
        }
        $user->insertInfo($request);
        return noContentResp();
    }

    //用户信息更新接口
    function update(Request $request, Users $user)
    {
        if ($user->agree != 1) {
            return errorResp("未同意隐私协议", 403);
        }
        $request->validate([
            'nickname'   => 'string',
            'tags'       => 'array',
            'time_areas' => 'array',
        ]);
        $user->updatbeInfo($request);
        return noContentResp();
    }

    //问卷内容接口 todo
    function question()
    {
        return dataResp([]);
    }
}