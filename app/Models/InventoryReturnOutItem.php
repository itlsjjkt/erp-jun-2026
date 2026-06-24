<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\DB;

class InventoryReturnOutItem extends Model
{

    

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'inventory_return_items';
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
    protected $fillable = ['inventory_id', 'reason','qty','inventory_return_out_id','status'];

    public function searchableAs()
    {
        return 'inventory_returns';
    }


    public static function getByReturnOutId($id,$status = null){
        $query = DB::table('inventory_return_items')
        ->select('inventories.code_rack','inventories.id AS inv_id', 'inventories.stock_onhand',
        'inventories.stock_min','inventories.stock_max','inventories.in AS stock_in',
        'inventory_return_items.*',
        'master_item_products.name AS productName', 'master_item_products.code AS productCode', 'master_item_products.part_number AS productPartNumber','measures.name AS unit'
        )
        ->leftJoin('inventories', 'inventories.id', '=', 'inventory_return_items.inventory_id')
        ->leftJoin('master_item_products', 'master_item_products.id', '=', 'inventories.product_id')
        ->leftJoin('measures', 'measures.id', '=', 'inventories.measure_id')
        ->where('inventory_return_items.inventory_return_out_id', $id)
        ->when(!empty($status), function ($query)  {
            return $query->where('inventory_return_items.status',0);
        })
        ->get();
        
        return $query;
        
    }

}
