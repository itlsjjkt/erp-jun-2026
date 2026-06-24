<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\DB;

class InventoryProcess extends Model
{

    

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'inventory_process';
    public $timestamps = false;

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['location_id', 'month','year','created_at','created_by','department_id'];

    
    public static function process($location){
       
        $sql = 'UPDATE inventories SET "in" = 0, "out" = 0, initial = stock_onhand WHERE location_id = '.$location;
        return DB::select( $sql);
    }

    public static function process_local($location,$department){
       
        $sql = 'UPDATE inventories SET "in" = 0, "out" = 0, initial = stock_onhand WHERE location_id = '.$location.' AND department_id = '.$department ;
        return DB::select( $sql);
    }
}
