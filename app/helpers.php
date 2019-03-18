<?php

use App\Http\Middleware\UserHelper;
use GuzzleHttp\Client;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

/**
 * 带数据的成功返回 # HTTP Status Code 200
 *
 * @param $data
 * @param null|string $message 可选字段
 *
 * @return array
 */
function dataResp($data, string $message = null): array
{
    $ret = ['data' => $data];

    if ($message) {
        $ret['message'] = $message;
    }
    $ret['errno'] = 0;
    $ret['errmsg'] = 0;
    $ret['use_memory_usage'] = convert(memory_get_peak_usage());
    $ret['assign_memory_usage'] = convert(memory_get_usage());
    $ret['real_memory_usage'] = convert(memory_get_usage(true));

    return $ret;
}

/**
 * 带消息的错误返回
 *
 * @param string $message
 * @param null $code 可选字段
 * @param int|null $statusCode HTTP Status Code
 *
 * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
 */
function errorResp(string $message, $code = null, ?int $statusCode = 400)
{
    $ret = ['errmsg' => $message];

    if ($code) {
        $ret['code'] = $code;
    } else {
        $ret['errno'] = -1;
    }
    return response($ret, $statusCode ?? 400);
}

/**
 * 带数据的成功创建返回 # HTTP Status Code 201
 *
 * @param $data
 *
 * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
 */
function createdResp($data)
{
    return response(['data' => $data, "errno" => 0, "errmsg" => "ok"], 200);
}

/**
 * 无内容的返回 # HTTP Status Code 204
 *
 * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
 */
function noContentResp()
{
    return response(["errno" => 0, "errmsg" => "ok"], 200);
}

function isProdEnv()
{
    return app()->environment('production') === true;
}

function isRouteWithoutAuth(string $path): bool
{
    return in_array($path, config('app.route_without_auth'));
}

function getChineseDate(string $date): string
{
    return date('Y年n月j日', strtotime($date));
}

function parsePagination(LengthAwarePaginator $data, Closure $closure, array $attachData = [], string $listKey = 'list')
{
    $list = [];

    foreach ($data as $item) {
        $list[] = $closure($item);
    }

    return array_merge($attachData, [
        'current_page' => $data->currentPage(),
        'total_page'   => $data->lastPage(),
        $listKey       => $list,
    ]);
}


function convert($size)
{
    $unit = ['b', 'kb', 'mb', 'gb', 'tb', 'pb'];
    $i = floor(log($size, 1024));
    return @round($size / pow(1024, $i), 2) . ' ' . $unit[$i];
}

/**
 * 返回数组1对象中keys对应属性不存在于数组2对象的相应属性中
 *
 * @param array $arr1
 * @param array $arr2
 * @param array $keys
 *
 * @return array
 */
function object_diff(array $arr1, array $arr2, array $keys): array
{
    if (empty($keys) || empty($arr2) || empty($arr1)) {
        return $arr1;
    }
    foreach ($keys as $key) {
        foreach ($arr2 as $v2) {
            if (isset($v2->$key)) {
                $arr2_values[$key][] = $v2->$key;
            }
        }
    }
    foreach ($arr1 as $k1 => $v1) {
        foreach ($keys as $key) {
            if (isset($v1->$key) && in_array($v1->$key, $arr2_values[$key])) {
                unset($arr1[$k1]);
            }
        }
    }
    return $arr1;
}

function file_path($key)
{
    $user = UserHelper::$instance;
    $md5 = md5("{$user->openid}-{$key}");
    return "/{$user->user_id}/{$md5}";
}

function file_ext($flie_path)
{
    $arr = pathinfo($flie_path);
    return $arr['extension'];
}

//
///**
// * 数据库事务
// *
// * @param Closure $callback
// *
// * @return mixed
// * @throws Exception
// */
//function fcTransaction(Closure $callback)
//{
//    DB::beginTransaction();
//    DB::connection('python')->beginTransaction();
//    DB::connection('for_os')->beginTransaction();
//
//    try {
//        return tap($callback(), function () {
//            DB::commit();
//            DB::connection('python')->commit();
//            DB::connection('for_os')->commit();
//        });
//    } catch (Exception $e) {
//        DB::rollBack();
//        DB::connection('python')->rollBack();
//        DB::connection('for_os')->rollBack();
//        throw $e;
//    }
//}

function getDistance($lat1, $lng1, $lat2, $lng2)
{
    $earthRadius = 6367000; //approximate radius of earth in meters
    $lat1 = ($lat1 * pi()) / 180;
    $lng1 = ($lng1 * pi()) / 180;
    $lat2 = ($lat2 * pi()) / 180;
    $lng2 = ($lng2 * pi()) / 180;
    $calcLongitude = $lng2 - $lng1;
    $calcLatitude = $lat2 - $lat1;
    $stepOne = pow(sin($calcLatitude / 2), 2) + cos($lat1) * cos($lat2) * pow(sin($calcLongitude / 2), 2);
    $stepTwo = 2 * asin(min(1, sqrt($stepOne)));
    $calculatedDistance = $earthRadius * $stepTwo;
    return round($calculatedDistance);
}

function humanizeDistance($m)
{
    return $m > 1000 ? ($m / 1000) . "千米" : ($m . "米");
}