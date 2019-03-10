<?php
/**
 * Created by PhpStorm.
 * User: eugene
 * Date: 2019/3/10
 * Time: 12:18 AM
 */

namespace App\Http\Controllers;


use App\Models\Tags;

class TagController
{
    function list(){
        return dataResp(Tags::ofDisplay()->get());
    }
}