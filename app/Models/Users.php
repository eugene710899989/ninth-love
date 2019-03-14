<?php
/**
 * Created by PhpStorm.
 * User: chotow
 * Date: 2019-01-10
 * Time: 22:24
 */

namespace App\Models;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * @property mixed agree
 * @property mixed username
 * @property mixed id
 * @property mixed gender
 */
class Users extends BaseModel
{
    protected $table = 'users';

    protected $guarded = ["id"];

    function insertInfo(Request $request)
    {
        $user = $this;
        $closure = function () use ($user, $request) {
            $user->setRawAttributes($request->only(["nickname", "student_id_f", "student_id_b", "birthday", "gender", "username", "major", "province", "avatar", "desc", "country"]));
            $user->id = $user->original["id"];
            if ($user->username == "") {
                $user->username = $user->nickname;
            }
            $user->update();
            if (!empty($request->input('tags'))) {
                $user->tagUpdate($request->input('tags'));
            }
            if (!empty($request->input('time_areas'))) {
                $user->freeUpdate($request->input('time_areas'));
            }
        };

        DB::transaction($closure);

    }

    function updateInfo(Request $request)
    {
        $user = $this;
        $closure = function () use ($user, $request) {
            $user->setRawAttributes($request->only(["nickname", "avatar", "desc"]));
            $user->update();
            if (!empty($request->input('tags'))) {
                $user->tagUpdate($request->input('tags'));
            }
            if (!empty($request->input('time_areas'))) {
                $user->freeUpdate($request->input('time_areas'));
            }
        };

        DB::transaction($closure);
    }


    public function tagUpdate(array $tags)
    {
        $this->tags()->delete();
        $batch_tags = [];
        foreach ($tags as $tag_id) {
            $batch_tags[] = ["user_id" => $this->original["id"], 'tag_id' => $tag_id];
        }
        UserTags::insert($batch_tags);
    }

    function freeUpdate(array $time_areas)
    {
        $this->freeTimes()->delete();
        $batchs = [];
        foreach ($time_areas as $time_area) {
            $times = explode('-', $time_area);
            $time_start = explode(':', $times[0]);
            $time_end = explode(':', $times[1]);
            $batchs[] = ["user_id" => $this->original["id"], 'time_start' => 100 * $time_start[0] + $time_start[1], 'time_end' => 100 * $time_end[0] + $time_end[1]];
        }
        UserFree::insert($batchs);
    }

    public function tags()
    {
        return $this->hasMany(UserTags::class, 'user_id');
    }

    public function freeTimes()
    {
        return $this->hasMany(UserFree::class, 'user_id');
    }

    public function invites(){
        return $this->hasMany(UserInvites::class, 'invitee_id');
    }

    public function inviteByOther(){
        return $this->hasMany(UserInvites::class, 'user_id');
    }
//
//    // ========== 作用域 ==========
//
//    public function scopeOfOperator($query, int $operatorId)
//    {
//        return $query->where('operator_id', $operatorId);
//    }
//
//    public function scopeOfTerm($query, int $termId)
//    {
//        return $query->where('term_id', $termId);
//    }
}
