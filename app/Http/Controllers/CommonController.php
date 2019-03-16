<?php
/**
 * Created by PhpStorm.
 * User: eugene
 * Date: 2019/3/10
 * Time: 8:37 AM
 */

namespace App\Http\Controllers;


use App\Models\Users;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CommonController  extends Controller
{
    function upload(Request $request)
    {
        Log::info("upload request", $request->all());
        $store_path = file_path($request->input("name"));
        $files = $request->file($request->input("file"));
        if (is_array($files) && !empty($files["file"])) {
            $path = $files["file"]->storeAs($store_path,"{$request->input("name")}.". file_ext($files["file"]->getClientOriginalName()));
        }else{
            return errorResp("upload fail");
        }
        return dataResp(["src" => Storage::url($path)]);
    }

    function schools(Request $request){
        $school_search_name = $request->input("name");
        if($school_search_name){
            $school_names= Users::where("school","like","%{$school_search_name}%")->pluck("school")->unique();
        }else{
            $school_names = Users::all()->pluck("school")->unique();
        }
        return dataResp($school_names);
    }

    function majors(Request $request){
        $school_search_name = $request->input("name");
        if($school_search_name){
            $school_names= Users::where("major","like","%{$school_search_name}%")->pluck("major")->unique();
        }else{
            $school_names = Users::all()->pluck("major")->unique();
        }
        return dataResp($school_names);
    }
}