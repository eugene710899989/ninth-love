<?php
/**
 * Created by PhpStorm.
 * User: eugene
 * Date: 2019/3/17
 * Time: 9:33 PM
 */

namespace App\Http\Controllers;


use App\Http\Middleware\UserHelper;
use App\Models\CommentReplys;
use App\Models\UserComments;
use App\Models\UserPosts;
use App\Models\Users;
use App\Models\UserZans;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PostController
{
    function create(Request $request)
    {
        $content = $request->input('content');
        $images = $request->input('images');
        $longitude = $request->input('longitude');
        $latitude = $request->input('latitude');
        $user = UserHelper::$user;
        $cacheKey = "post_list_user_" . UserHelper::$user->id;
        Cache::forget($cacheKey);
        UserPosts::create(['user_id' => $user->id, 'content' => $content, 'longitude' => $longitude, 'latitude' => $latitude,"images"=>$images]);
        return noContentResp();
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
        $user = Users::find($post->user_id)->get(['nickname', 'avatar', 'username', 'desc', 'school'])->first();
        $post->user = $user;
        $post->like_count = $post->userLike()->count();
        $post->dislike_count = $post->userDislike()->count();
        $post->comment_count = $post->comments()->count();
        return dataResp(['post' => $post, 'comments' => $comments]);
    }

    function list()
    {
        $posts = UserPosts::orderBy('id', 'desc')->get(['id', 'content', 'user_id',"images"]);
        $perPage = 10;
        $offset = (Paginator::resolveCurrentPage() * $perPage) - $perPage;
        if (count($posts) > 0) {
            $cacheKey = "post_list_user_" . UserHelper::$user->id;
            if (Cache::get($cacheKey)) {
                return Cache::get($cacheKey);
            }
            foreach ($posts->slice($offset, $perPage) as &$post) {
                $user = Users::find($post->user_id)->get(['nickname', 'avatar', 'username', 'desc', 'school'])->first();
                $post->user = $user;
                $post->like_count = $post->userLike()->count();
                $post->dislike_count = $post->userDislike()->count();
                $post->comment_count = $post->comments()->count();
            }
            $pageData = new LengthAwarePaginator($posts->slice($offset, $perPage), count($posts), $perPage);
            $return = dataResp(["list" => $pageData->items(), 'total' => count($posts), 'total_page' => $pageData->lastPage(), 'current_page' => $pageData->currentPage()]);
            Cache::put($cacheKey, $return, 1);
            return $return;
        } else {
            return dataResp(["list" => [], 'total' => 0, 'total_page' => 0, 'current_page' => 1]);
        }
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
        return noContentResp();
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
        return noContentResp();
    }

    function comment(Request $request, UserPosts $post)
    {
        $content = $request->input('content');

        if (empty($content)) {
            abort(400, 'empty content');
        }
        $comment = UserComments::where(['user_id' => UserHelper::$user->id, 'post_id' => $post->id, 'content' => $content])->orderByDesc('id')->first();
        if ($comment && Carbon::now()->diffInSeconds(Carbon::parse($comment->created_at)) <= 2) {
            return errorResp("请求过于频繁", 400);
        }

        UserComments::create(['user_id' => UserHelper::$user->id, 'post_id' => $post->id, 'content' => $content]);
        return noContentResp();
    }

    function reply(Request $request, UserComments $comments)
    {
        $content = $request->input('content');

        if (empty($content)) {
            abort(400, 'empty content');
        }
        $comment = CommentReplys::where(['user_id' => UserHelper::$user->id, 'comment_id' => $comments->id, 'content' => $content])->orderByDesc('id')->first();
        if ($comment && Carbon::now()->diffInSeconds(Carbon::parse($comment->created_at)) <= 2) {
            return errorResp("请求过于频繁", 400);
        }

        CommentReplys::create(['user_id' => UserHelper::$user->id, 'comment_id' => $comments->id, 'content' => $content]);
        return noContentResp();
    }

}