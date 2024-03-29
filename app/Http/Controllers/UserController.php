<?php
/**
 * Created by PhpStorm.
 * User: eugene
 * Date: 2019/3/9
 * Time: 11:20 PM
 */

namespace App\Http\Controllers;


use App\Http\Middleware\UserHelper;
use App\Models\UserInvites;
use App\Models\Users;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{

    //用户同意协议接口
    function agree()
    {
        $user = Users::findOrFail(UserHelper::$instance->user_id);
        if ($user->agree != 0) {
            return errorResp("你已同意协议");
        }
        $user->agree = 1;
        $user->save();
        return noContentResp();
    }

    //用户信息获取接口
    function info()
    {
        $user = UserHelper::$instance;
        unset($user->payload);
        $userModel = Users::with("tags")->find($user->user_id);

        return dataResp($userModel);
    }

    //用户信息注册接口
    function init(Request $request)
    {
        $user = Users::findOrFail(UserHelper::$instance->user_id);
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
            'city'         => 'required|string',
            'tags'         => 'array',
            'time_areas'   => 'array',
        ]);

        if ($validator->fails()) {
            return errorResp($validator->errors()->first());
        }
        $user->insertInfo($request);
        return createdResp();
    }

    //用户信息更新接口
    function update(Request $request)
    {
        $user = Users::findOrFail(UserHelper::$instance->user_id);
        if ($user->agree != 1) {
            return errorResp("未同意隐私协议", 403);
        }
        $request->validate([
            'nickname'   => 'string',
            'tags'       => 'array',
            'avatar'     => 'required|url',
            'time_areas' => 'array',
        ]);
        $user->updatbeInfo($request);
        return noContentResp();
    }

    function invites()
    {
        $user = Users::findOrFail(UserHelper::$instance->user_id);
        $invites = $user->invites()->limit(100)->orderBy("id", "desc")->get();
        $re = [];
        foreach ($invites as $invite) {
            $invited = Users::find($invite->user_id);
            $re[] = ["created" => $invite->created_at, "invite" => $invited->nickname, 'id' => $invite->id];
        }
        $user = Users::find(UserHelper::$instance->getId());
        return dataResp(["list" => $re, "user" => $user]);
    }

    function inviteInfo()
    {

    }

    function invite(Users $invited_user)
    {
        UserInvites::firstOrCreate(['user_id' => UserHelper::$instance->getId(), 'invitee_id' => $invited_user->id, 'result' => UserInvites::INVITING]);
        return noContentResp();
    }

    function agree_invite(UserInvites $invitee)
    {
        $user_id = UserHelper::$instance->getId();

        if ($invitee->invitee_id != $user_id) {
            return errorResp("不属于该用户", 403);
        }
        if ($invitee->result != 0) {
            return errorResp("邀请已经同意或拒绝", 403);
        }
        $invitee->result = 1;
        $invitee->save();
        return noContentResp();
    }

    function refuse_invite(UserInvites $invitee)
    {
        $user_id = UserHelper::$instance->getId();
        if ($invitee->invitee_id != $user_id) {
            return errorResp("不属于该用户", 403);
        }
        if ($invitee->result != 0) {
            return errorResp("邀请已经同意或拒绝", 403);
        }
        $invitee->result = -1;
        $invitee->save();
        return noContentResp();
    }

    function dateList()
    {
        $user = Users::findOrFail(UserHelper::$instance->user_id);
        $invites = $user->inviteByOther()->limit(100)->orderBy("id", "desc")->get();
        $re = [];
        foreach ($invites as $invite) {
            $invited = Users::find($invite->user_id);
            $re[] = ["created" => $invite->created_at, "invite" => $invited->nickname, 'id' => $invite->id];
        }
        return dataResp(["list" => $re, "user" => $user]);
    }

    //问卷内容接口 todo
    function question()
    {
        return dataResp([]);
    }
}