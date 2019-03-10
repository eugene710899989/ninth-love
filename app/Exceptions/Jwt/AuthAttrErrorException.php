<?php
/**
 * Created by PhpStorm.
 * User: raina
 * Date: 2017/10/9
 * Time: 上午11:42
 */

namespace App\Exceptions\Jwt;


class AuthAttrErrorException extends \Exception
{
    public function __construct($message, $code, $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}