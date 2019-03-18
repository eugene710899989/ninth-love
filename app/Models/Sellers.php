<?php
/**
 * Created by PhpStorm.
 * User: chotow
 * Date: 2019-01-10
 * Time: 22:24
 */

namespace App\Models;

use Illuminate\Support\Facades\DB;

/**
 * @property mixed agree
 * @property mixed latitude
 * @property mixed longitude
 */
class Sellers extends BaseModel
{
    protected $table = 'sellers';

    protected $guarded = ["id"];

    public function productions()
    {
        return $this->hasMany(Productions::class, "seller_id");
    }

    function getDistance($latitude, $longitude)
    {
        return getDistance($latitude,$longitude,$this->latitude,$this->longitude);
        dd(getDistance($latitude,$longitude,$this->latitude,$this->longitude));
//        $sql = "select *,3959 * acos (cos ( radians({$latitude})) * cos( radians( {$this->latitude} ) )
//      * cos( radians( {$this->longitude} ) - radians({$longitude}))  + sin ( radians({$latitude}) ) * sin( radians( {$this->tb_latitude} ) )
//    ) distance from sellers";
        return 6371 * acos(cos(deg2rad($latitude))) * cos(deg2rad($this->latitude)) * cos(deg2rad($this->longitude) - deg2rad($longitude)) + sin(deg2rad($latitude)) * sin(deg2rad($this->latitude));
//       return  DB::table('sellers')->raw(DB::raw($sql));
    }

}
