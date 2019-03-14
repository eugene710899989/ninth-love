<?php
/**
 * Created by PhpStorm.
 * User: eugene
 * Date: 2019/3/10
 * Time: 11:41 PM
 */

namespace App\Http\Controllers;


use App\Models\Sellers;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

class SellerController extends Controller
{
    function list()
    {
        $shops = Sellers::all();
        $perPage = 10;
        $offset = (Paginator::resolveCurrentPage() * $perPage) - $perPage;
        $pageData = new LengthAwarePaginator(array_slice($shops->toArray(), $offset, $perPage), count($shops), $perPage);
        return dataResp(["user_list" => Tags::ofDisplay()->get(), 'total' => count($shops), 'total_page' => $pageData->lastPage(), 'current_page' => $pageData->currentPage()]);
    }
}