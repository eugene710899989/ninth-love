<?php
/**
 * Created by PhpStorm.
 * User: eugene
 * Date: 2019/3/17
 * Time: 8:40 AM
 */

namespace App\Http\Controllers;


use App\Http\Middleware\UserHelper;
use App\Models\Productions;
use App\Models\Sellers;
use App\Models\UserInvites;
use App\Models\UserOrders;
use App\Models\Users;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    function list()
    {
        $userOrders = UserOrders::OfUser(UserHelper::$user)->paid()->get();
        foreach ($userOrders as $userOrder) {
            $userOrder->productions = Productions::whereIn('id', collect($userOrder->production_ids)->pluck('production_id')->all())->get();
        }
        return dataResp(["order" => $userOrders]);
    }

    function create(Request $request, Sellers $seller)
    {
        $productions = $request->input('productions');
        if (empty($productions)) {
            abort(400, "产品数据未空");
        }
        $invite_user = Users::find($request->input('invited_user_id'));
        if(!$invite_user){
            abort(404, "未找到邀请用户");
        }
        $user = UserHelper::$user;
        $invite = UserInvites::where(['user_id' => $user->id, 'invitee_id' => $invite_user->id, 'result' => 1])->first();
        if(!$invite){
            abort(400, "未找到邀请记录");
        }
        $userOrder = UserOrders::firstOrCreate(["user_id" => $user->id, "amount" => $request->input('amount') * 100, "is_paid" => 0, 'seller_id' => $seller->id, 'invite_id' => $invite->id], ["production_ids" => $productions]);
        if ($userOrder->transaction_id == "") {
            $userOrder->transaction_id = $userOrder->genTransaction();
            $userOrder->save();
        }
        return dataResp(["order" => ['order_id' => $userOrder->id, 'transaction_id' => $userOrder->transaction_id, 'openid' => $user->openid]]);
    }

    function callback(Request $request)
    {
        // ['order_id' => $userOrder->id, 'transaction_id' => $userOrder->transaction_id, 'openid' => $user->openid,'result'=>'success']
        $obj = $request->input("data");
        if (!$obj) {
            abort(401);
        }
        $data = json_decode($obj, 1);
        if (empty($data["openid"])) {
            abort(404);
        }
        $user = Users::where('openid', $data["openid"])->firstOrFail();
        $order = UserOrders::OfUser($user)->where(['id' => $data["order_id"], 'transaction_id' => $data["transaction_id"]])->firstOrFail();
        if (empty($data["result"])) {
            abort(400);
        }
        if ($data["result"] == 'success') {
            $order->is_paid = 1;
            $order->save();
        }
        return dataResp([]);
    }
}