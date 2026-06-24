<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\DB;

class InventoryConversionItem extends Model
{

    

    /**
     * The table associated with the model.
     *
     * @var string
     */
    public $timestamps = false;
    protected $table = 'inventory_conversion_items';
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
    protected $fillable = ['id','inventory_id', 'inventory_conversion_id','qty_to','qty_from'];

    public function searchableAs()
    {
        return 'inventory_conversions';
    }

    public static function getByConversionId($id){
        $sql = "
        SELECT 
        inventories.code_rack AS coderack1, 
        master_item_products.code AS productcode1, 
        master_item_products.name AS productname1, 
        master_item_products.part_number AS productpartnumber1, 
        measures.name AS productunit1,
         t1.qty_from AS qty_stock,
        t2.coderack2, t2.productcode2,
        t2.productname2, 
        t2.productpartnumber2, 
        t2.productunit2, 
        t2.qty_conversion
        
        FROM 
         inventory_conversion_items AS t1
        
        INNER JOIN (
            SELECT 
            t2.qty_to AS qty_conversion,t2.inventory_conversion_id,
            inventories.code_rack AS coderack2, 
            master_item_products.name AS productname2,master_item_products.code AS productcode2, master_item_products.part_number AS productpartnumber2, measures.name AS productunit2
            FROM 
            inventory_conversion_items AS t2
            LEFT JOIN inventory_conversions ON inventory_conversions.id =  t2.inventory_conversion_id
            LEFT JOIN inventories ON inventories.id = t2.inventory_id_to
            LEFT JOIN master_item_products ON master_item_products.id =  inventories.product_id
            LEFT JOIN measures ON measures.id = master_item_products.measure_inventory
        ) AS t2 ON t2.inventory_conversion_id = t1.inventory_conversion_id 
        
        LEFT JOIN inventory_conversions ON inventory_conversions.id =  t1.inventory_conversion_id
        LEFT JOIN inventories ON inventories.id =  t1.inventory_id_from
        LEFT JOIN master_item_products ON master_item_products.id =  inventories.product_id
        LEFT JOIN measures ON measures.id = master_item_products.measure_inventory
        WHERE t1.inventory_conversion_id = $id
        ";
        return DB::select( $sql);
        
    }

}
