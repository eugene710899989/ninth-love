<?php

use App\Models\CommonConfig;
use Firebase\JWT\JWT;
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
    $ret = ['message' => $message];

    if ($code) {
        $ret['code'] = $code;
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
    return response(['data' => $data], 201);
}

/**
 * 无内容的返回 # HTTP Status Code 204
 *
 * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
 */
function noContentResp()
{
    return response('', 204);
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

function getCommonConfig(string $key, $default = null)
{
    $ret = CommonConfig::findByKey($key);

    if ($ret && $ret->value) {
        return $ret->value;
    }

    Log::warning('出现数据库未有的配置项 # ' . $key);

    return $default;
}

function shortenURL(string $rawURL): string
{
    $params = [
        'source'   => '31641035',
        'url_long' => $rawURL,
    ];

    $url = 'https://api.t.sina.com.cn/short_url/shorten.json?' . http_build_query($params);

    try {
        $resp = (new Client())->get($url)->getBody()->getContents();
        $ret = json_decode($resp)[0];
        return $ret->url_short ?? $rawURL;
    } catch (Exception $e) {
        return $rawURL;
    }
}

/**
 * 风变短链接转换服务
 *
 * @param string $long_url
 * @param int $domain_id
 *
 * @return string
 */
function fcShortenUrl(string $long_url, int $domain_id = 0): string
{
    if (env('APP_ENV') !== 'production') {
        return $long_url;
    }

    try {
        $resp = (new Client(['timeout' => 3]))->post(config('forchange.shorten_url_domain') . '/e/', [
            'json' => [
                'domain_id' => $domain_id,
                'path'      => $long_url,
            ],
        ])->getBody()->getContents();
        return json_decode($resp)->data->url ?? $long_url;
    } catch (Exception $e) {
        Log::error('短链接转换失败 # ' . $e->getMessage(), [$long_url]);
        return $long_url;
    }
}

function getQiniuURL(string $content): string
{
    try {
        $content = json_decode($content, true);
        $resp = (new Client())->post(config('forchange.image2qn'), ['json' => $content])->getBody()->getContents();
        $key = json_decode($resp)->key;
        return config('forchange.static_domain') . '/' . $key;
    } catch (Exception $e) {
        Log::error('图片生成失败 # ' . $e->getMessage(), $e->getTrace());
        throw new BadRequestHttpException('图片生成失败');
    }
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

function createToken(array $payload): string
{
    try {
        return JWT::encode($payload, config('jwt.secret'), config('jwt.algorithm'));
    } catch (Exception $e) {
        throw new ServiceUnavailableHttpException(null, '生成 Token 失败');
    }
}

function decodeToken(string $token)
{
    try {
        return JWT::decode($token, config('jwt.secret'), [config('jwt.algorithm')]);
    } catch (Exception $e) {
        throw new UnauthorizedHttpException('', 'Token 无效');
    }
}

function getProjectUrl(): string
{
    if ($domain = config('forchange.ci_domain')) {
        return $domain . config('forchange.ci_path');
    }

    return url();
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

/**
 * 数据库事务
 *
 * @param Closure $callback
 *
 * @return mixed
 * @throws Exception
 */
function fcTransaction(Closure $callback)
{
    DB::beginTransaction();
    DB::connection('python')->beginTransaction();
    DB::connection('for_os')->beginTransaction();

    try {
        return tap($callback(), function () {
            DB::commit();
            DB::connection('python')->commit();
            DB::connection('for_os')->commit();
        });
    } catch (Exception $e) {
        DB::rollBack();
        DB::connection('python')->rollBack();
        DB::connection('for_os')->rollBack();
        throw $e;
    }
}

/**
 * 替换内网链接
 *
 * @param string $originalUrl
 *
 * @return mixed
 */
function replaceInternalUrl(string $originalUrl)
{
    return preg_replace('/http\:\/\/api-ai-boss.*ninth-studio\.svc\:8080/', getProjectUrl(), $originalUrl);
}

function http2https(?string $originalUrl)
{
    return is_null($originalUrl) ? null : str_replace('http://', 'https://', $originalUrl);
}

if (!function_exists('db_esc')) {
    /**
     * 数据库escape字符串
     * @param string $str
     * @return string
     */
    function db_esc(string $str): string
    {
        $ret = str_replace(['\\','%', '_', '@', '""', "'",';'], ['\\\\','\%', '\_', '\@', '\"', "\'",'\;'], $str);
        return $ret;
    }
}