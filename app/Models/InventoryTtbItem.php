<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\DB;

class InventoryTtbItem extends Model
{

    

    /**
     * The table associated with the model.
     *
     * @var string
     */
    public $timestamps = false;
    protected $table = 'inventory_ttb_items';
    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';
    protected $guarded  = ['id'];
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    public function searchableAs()
    {
        return 'inventory_ttbs';
    }

    public static function getByTtbId($id){
        $query = DB::table('inventory_ttb_items')
        ->select('inventories.code_rack',
        'inventories.id AS inv_id', 
        'inventories.stock_onhand',
        'inventories.stock_min',
        'inventories.stock_max',
        'inventories.out',
        'inventories.in',
        'inventory_ttb_items.*',
        'cost_centre.name AS cost_center',
        'cost_centre.code AS coa',
        'inventory_ttb_items.cost_center AS cost_center_id',
        'master_item_products.name AS productName', 
        'master_item_products.code AS productCode', 
        'master_item_products.part_number AS productPartNumber',
        'measures.name AS unit',
        'master_items.name AS item_name', 
        'master_items.code AS item_code'
        )
        ->leftJoin('inventories', 'inventories.id', '=', 'inventory_ttb_items.inventory_id')
        ->leftJoin('master_item_products', 'master_item_products.id', '=', 'inventories.product_id')
        ->leftJoin('master_items', 'master_items.id', '=', 'master_item_products.item_id')
        ->leftJoin('cost_centre', 'cost_centre.id', '=', 'inventory_ttb_items.cost_center')
        ->leftJoin('measures', 'measures.id', '=', 'inventories.measure_id')
        ->where('inventory_ttb_items.inventory_ttb_id', $id)
        ->get();
        
        return $query;
        
    }

    public function ttb(){
        return $this->belongsTo(InventoryTtb::class,'inventory_ttb_id');
    }


}
