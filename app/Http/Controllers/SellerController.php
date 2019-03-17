<?php
/**
 * Created by PhpStorm.
 * User: eugene
 * Date: 2019/3/10
 * Time: 11:41 PM
 */

namespace App\Http\Controllers;


use App\Http\Middleware\UserHelper;
use App\Models\Sellers;
use App\Models\Users;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

class SellerController extends Controller
{
    function list()
    {
        $user = Users::findOrFail(UserHelper::$user->user_id);
        $shops = Sellers::where("school","like","%{$user->school}%")->get();
        $perPage = 10;
        $offset = (Paginator::resolveCurrentPage() * $perPage) - $perPage;
        $pageData = new LengthAwarePaginator(array_slice($shops->toArray(), $offset, $perPage), count($shops), $perPage);
        return dataResp(["list" => $pageData->items(), 'total' => count($shops), 'total_page' => $pageData->lastPage(), 'current_page' => $pageData->currentPage()]);
    }

    function productions(Sellers $seller){

    }
}