<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\DB;

class InventoryTransferOutItem extends Model
{



    /**
     * The table associated with the model.
     *
     * @var string
     */
    public $timestamps = false;
    protected $table = 'inventory_transfer_out_items';
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
    protected $fillable = ['id',
    'inventory_id',
    'inventory_transfer_id',
    'status',
    'notes',
    'qty',
    'qty_parsial'
    ];

    public function searchableAs()
    {
        return 'inventory_transfer_out_items';
    }

    public static function getByTransferId($id, $status = null){

        $where = '';
        if($status == 'status'){
            $where  = " AND t1.status != 1";
        }

        $sql = "
        SELECT
        inventories.code_rack AS code_rack,
        inventories.product_id AS product_id,
        inventories.price AS price,
        inventories.price_after_discount AS price_after_discount,
        master_item_products.code AS productcode,
        master_item_products.name AS productname,
        master_item_products.measure_inventory AS productsatuanidinv,
        inventories.stock_onhand AS stock_onhand,
        master_item_products.part_number AS productpartnumber,
        measures.name AS productunit,
        t1.*

        FROM inventory_transfer_out_items AS t1

        LEFT JOIN inventory_transfer_out ON inventory_transfer_out.id =  t1.inventory_transfer_id
        LEFT JOIN inventories ON inventories.id =  t1.inventory_id
        LEFT JOIN master_item_products ON master_item_products.id =  inventories.product_id
        LEFT JOIN measures ON measures.id = master_item_products.measure_inventory
        WHERE t1.inventory_transfer_id = $id
        $where
        ";
        return DB::select( $sql);

    }

}
