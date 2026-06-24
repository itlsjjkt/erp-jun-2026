<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class UserAsset extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_assets';
    protected $guarded  = ['id'];

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    public static function getByID($id){
        $query = DB::table('user_assets')->where('id','=',$id)->first();
        return $query;
    }

    public function searchableAs()
    {
        return 'user_assets';
    }
}
