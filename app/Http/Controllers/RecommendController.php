<?php
/**
 * Created by PhpStorm.
 * User: eugene
 * Date: 2019/3/10
 * Time: 12:18 AM
 */

namespace App\Http\Controllers;


use App\Models\Tags;
use App\Models\Users;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

class RecommendController extends Controller
{
    function list(Request $request, Users $user)
    {

        $builder = Users::where("gender", "<>", $user->gender);

        if($request->input("school")){
            $builder->where("school","like","%{$request->input("school")}%");
        }

        if($request->input("major")){
            $builder->where("major","like","%{$request->input("major")}%");
        }
        if($request->input("city")){
            $builder->where("city","like","%{$request->input("city")}%");
        }
        $result = $builder->orderBy('updated_at', 'desc')->get();
        $perPage = 10;
        $offset = (Paginator::resolveCurrentPage() * $perPage) - $perPage;
        $pageData = new LengthAwarePaginator(array_slice($result->toArray(), $offset, $perPage), count($result), $perPage);
        return dataResp(["user_list" => $pageData->items(), 'total' => count($result), 'total_page' => $pageData->lastPage(), 'current_page' => $pageData->currentPage()]);
    }
}