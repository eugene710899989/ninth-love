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
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

class RecommendController extends Controller
{
    function list(Users $user)
    {
        $result = Users::where("gender", "<>", $user->gender)->orderBy('updated_at', 'desc');
        $perPage = 10;
        $offset = (Paginator::resolveCurrentPage() * $perPage) - $perPage;
        $pageData = new LengthAwarePaginator(array_slice($result->toArray(), $offset, $perPage), count($result), $perPage);
        return dataResp(["user_list" => Tags::ofDisplay()->get(), 'total' => count($result), 'total_page' => $pageData->lastPage(), 'current_page' => $pageData->currentPage()]);
    }
}