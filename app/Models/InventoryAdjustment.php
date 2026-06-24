<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;


class InventoryAdjustment extends Model
{

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'inventory_adjustments';
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
    protected $fillable = ['inventory_id', 'doc_no','reason','operator','qty_fisik','status','created_by','file','publish'];

    public function searchableAs()
    {
        return 'inventory_adjustments';
    }

    public function inventory(){
        return $this->hasOne(Inventory::class,'id','inventory_id');
     }
     

     public static function getData($request, $company = null, $location = null){

        $query =DB::table('inventory_adjustments')
        ->select('inventory_adjustments.*', 
        'users.name AS created',
        'inventories.code_rack',
        'measures.name as measure',
        'master_item_products.name AS productName', 
        'master_item_products.code AS productCode', 
        'master_item_products.part_number AS productPartNumber')
        ->leftJoin('inventories', 'inventories.id', '=', 'inventory_adjustments.inventory_id')
        ->leftJoin('locations', 'locations.id', '=', 'inventories.location_id')
        ->leftJoin('master_item_products', 'master_item_products.id', '=', 'inventories.product_id')
        ->leftJoin('measures', 'measures.id', '=', 'inventories.measure_id')
        ->leftJoin('users', 'users.id', '=', 'inventory_adjustments.created_by')
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
            return $query->whereBetween('inventory_adjustments.created_at', [$start , $end]);
        });

        return $query;

    }

     

}
