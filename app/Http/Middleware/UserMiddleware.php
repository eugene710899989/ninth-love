<?php

namespace App\Http\Middleware;

use App\Exceptions\Jwt\AuthAttrErrorException;
use App\Exceptions\Jwt\NoTokenException;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use Illuminate\Http\Response;

class UserMiddleware
{
    
    use UserAccessible;
    
    public function handle($request, \Closure $next)
    {
        $message = [];
        $status_code = 0;
        $e = null;
        try {
            $this->user();
        } catch (ExpiredException $e) {  // 过期
            $status_code = 401;
            $message = [
                "message" => $e->getMessage(),
            ];
        } catch (SignatureInvalidException $e) { // 错误的 token
            $status_code = 401;
            $message = [
                "message" => $e->getMessage(),
            ];
        } catch (AuthAttrErrorException $e) {    // 错误的 参数
            $status_code = 401;
            $message = [
                "message" => $e->getMessage(),
            ];
        } catch (NoTokenException $e) {
            $status_code = 401;
            $message = [
                "message" => $e->getMessage(),
            ];
        } catch (\UnexpectedValueException $e) {
            $status_code = 401;
            $message = [
                "message" => $e->getMessage(),
            ];
        }
        if ($status_code) {
            if (config("jwt.debug")) {
                $message["status_code"] = $status_code;
                $message["debug"] = $e->getTrace();
            }
            return new Response($message, $status_code);
        }
        
        return $next($request);
    }
}