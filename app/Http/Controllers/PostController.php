<?php
/**
 * Created by PhpStorm.
 * User: eugene
 * Date: 2019/3/17
 * Time: 9:33 PM
 */

namespace App\Http\Controllers;


use App\Http\Middleware\UserHelper;
use App\Models\UserPosts;
use App\Models\Users;
use App\Models\UserZans;
use Illuminate\Http\Request;

class PostController
{
    function create(Request $request)
    {
        $content = $request->input('content');
        $longitude = $request->input('longitude');
        $latitude = $request->input('latitude');
        $user = UserHelper::$user;
        UserPosts::create(['user_id' => $user->id, 'content' => $content, 'longitude' => $longitude, 'latitude' => $latitude]);
        return dataResp([]);
    }

    function delete(UserPosts $post)
    {
        if ($post->user_id != UserHelper::$user->id) {
            abort(401);
        }
        $post->delete();
        return noContentResp();
    }

    function detail(UserPosts $post)
    {
        $comments = $post->comments()->get();
        if (!empty($comments)) {
            foreach ($comments as &$comment) {
                $comment->user = Users::find($comment->user_id)->get(['id', 'nickname', 'avatar', 'desc']);
            }
        }
        return dataResp(['post' => $post, 'comments' => $comments]);
    }

    function list()
    {

    }

    function like(Request $request, UserPosts $post)
    {
        $type = $request->input('type');
        if (empty($type) || !in_array($type, ['add', 'cancel'])) {
            abort(400);
        }
        if ($type == 'add') {
            UserZans::updateOrCreate(['user_id' => UserHelper::$user->id, 'post_id' => $post->id, 'type' => 1]);
            UserZans::where(['user_id' => UserHelper::$user->id, 'post_id' => $post->id, 'type' => 0])->first()->delete();
        } else {
            UserZans::where(['user_id' => UserHelper::$user->id, 'post_id' => $post->id, 'type' => 1])->first()->delete();
        }
        return;
    }

    function dislike(Request $request, UserPosts $post)
    {
        $type = $request->input('type');
        if (empty($type) || !in_array($type, ['add', 'cancel'])) {
            abort(400);
        }
        if ($type == 'add') {
            UserZans::updateOrCreate(['user_id' => UserHelper::$user->id, 'post_id' => $post->id, 'type' => 0]);
            UserZans::where(['user_id' => UserHelper::$user->id, 'post_id' => $post->id, 'type' => 1])->first()->delete();
        } else {
            UserZans::where(['user_id' => UserHelper::$user->id, 'post_id' => $post->id, 'type' => 0])->first()->delete();
        }
        return;
    }

    function comment(Request $request, UserPosts $post)
    {
        $content = $request->input('content');
        if (empty($content)) {
            abort(400, 'empty content');
        }
        UserZans::firstOrCreate(['user_id' => UserHelper::$user->id, 'post_id' => $post->id, 'content' => $content]);
        return;
    }

}