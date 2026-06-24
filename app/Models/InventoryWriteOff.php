<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class InventoryWriteOff extends Model
{

    

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'inventory_writeoffs';
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
    protected $fillable = ['inventory_id','reason','doc_no','created_by','operator','file','publish'];

    public function searchableAs()
    {
        return 'inventory_writeoffs';
    }


    public static function getData($request, $company = null, $location = null){

        $query = DB::table('inventory_writeoffs')
        ->select(
            'master_item_products.name AS productName', 
            'master_item_products.code AS productCode', 
            'inventory_writeoffs.*', 
            'inventories.code_rack',
            'users.name AS created',
        )
        ->leftJoin('inventories', 'inventories.id', '=', 'inventory_writeoffs.inventory_id')
        ->leftJoin('master_item_products', 'master_item_products.id', '=', 'inventories.product_id')
        ->leftJoin('locations', 'locations.id', '=', 'inventories.location_id')
        ->leftJoin('users', 'users.id', '=', 'inventory_writeoffs.created_by')
        ->when(!empty($company), function ($query) use ($company) {
            return $query->where('locations.company_id', $company);
        })
        ->when(!empty($location), function ($query) use ($location) {
            return $query->where('locations.id',$location);
        })
        ->when(!empty($request['location_id']), function ($query) use ($request){
            return $query->where('locations.id',$request['location_id']);
        }) 
        ->when(!empty($request['amp;start_date']), function ($query) use ($request) {
            $start = date("Y-m-d",strtotime($request['amp;start_date']));
            $end   = date("Y-m-d",strtotime($request['amp;end_date']."+1 day"));
            return $query->whereBetween('inventory_writeoffs.created_at', [$start , $end]);
        });
        return $query;
    }

}
