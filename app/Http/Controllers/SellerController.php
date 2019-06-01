<?php
/**
 * Created by PhpStorm.
 * User: eugene
 * Date: 2019/3/10
 * Time: 11:41 PM
 */

namespace App\Http\Controllers;


use App\Http\Middleware\UserHelper;
use App\Models\ProductionCates;
use App\Models\Sellers;
use App\Models\Users;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

class SellerController extends Controller
{
    function list(Request $request)
    {
        $user = Users::findOrFail(UserHelper::$instance->user_id);
        $shops = Sellers::where("school", "like", "%{$user->school}%")->get();
        $longitude = $request->input("longitude");
        $latitude = $request->input("latitude");
        foreach ($shops as $shop) {
            $shop->distance = $shop->getDistance($latitude, $longitude);
            $shop->human_distantce = humanizeDistance($shop->distance);
        }
        $shops = $shops->sortBy("distance")->values()->all();
        $perPage = 10;
        $offset = (Paginator::resolveCurrentPage() * $perPage) - $perPage;
        $pageData = new LengthAwarePaginator(array_slice($shops, $offset, $perPage), count($shops), $perPage);
        return dataResp(["list" => $pageData->items(), 'total' => count($shops), 'total_page' => $pageData->lastPage(), 'current_page' => $pageData->currentPage()]);
    }


    function productions(Sellers $seller, Request $request)
    {
        $cate_id = $request->input("cate_id");
        $cates = $seller->cates()->get();
        if (!$cate_id) {
            $cate = $cates->first();
        } else {
            $cate = ProductionCates::findOrFail($cate_id);
        }
        if ($cate) {
            $productions = $cate->productions()->get();
        } else {
            $productions = [];
        }
        return dataResp(["seller" => $seller, "productions" => $productions, "cates" => $cates]);
    }
}