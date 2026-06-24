<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\DB;

class InventoryReturnInItem extends Model
{

    

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'inventory_return_in_items';
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
    protected $fillable = ['inventory_return_in_id', 'reason','qty','inventory_return_item_id','qty','inventory_id','received','received_by'];

    public function searchableAs()
    {
        return 'inventory_return_in_items';
    }


    public static function getByReturnInId($id,$status = null){
        $query = DB::table('inventory_return_in_items')
        ->select('inventories.code_rack','inventories.id AS inv_id','inventories.stock_onhand',
        'inventories.stock_min','inventories.stock_max','inventories.in AS stock_in',
        'inventory_return_in_items.*',
        'master_item_products.name AS productName', 'master_item_products.code AS productCode', 'master_item_products.part_number AS productPartNumber','measures.name AS unit'
        )
        ->leftJoin('inventories', 'inventories.id', '=', 'inventory_return_in_items.inventory_id')
        ->leftJoin('master_item_products', 'master_item_products.id', '=', 'inventories.product_id')
        ->leftJoin('measures', 'measures.id', '=', 'inventories.measure_id')
        ->where('inventory_return_in_items.inventory_return_in_id', $id)
        ->when(!empty($status), function ($query)  {
            return $query->where('inventory_return_in_items.status',0);
        })
        ->get();
        
        return $query;
        
    }

}
