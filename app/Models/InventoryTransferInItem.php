<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\DB;

class InventoryTransferInItem extends Model
{



    /**
     * The table associated with the model.
     *
     * @var string
     */
    public $timestamps = false;
    protected $table = 'inventory_transfer_in_items';
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
    'inventory_transfer_out_item_id',
    'inventory_transfer_id',
    'notes',
    'qty',
    'type_replacement',
    'checked_by',
    'checked_at',
    'type_replacement_notes'
    ];

    public function searchableAs()
    {
        return 'inventory_transfer_in_items';
    }

    public static function getByTransferId($id, $status = null){

        $status = '';
        if($status != null ){
            $status = " AND inventory_transfer_in_items.status = $status";
        }

        $sql = "
        SELECT
        inventories.product_id AS product_id,
        inventories.price AS price,
        inventories.price_after_discount AS price_after_discount,
        master_item_products.code AS productcode,
        master_item_products.name AS productname,
        inventory_transfer_out_items.inventory_id AS inventory_id,
        inventory_transfer_out_items.qty AS out_qty,
        inventory_transfer_out_items.qty_parsial AS out_qty_parsial,
        inventory_transfer_out_items.status AS out_status,
        master_item_products.part_number AS productpartnumber,
        measures.name AS productunit,
        t1.*

        FROM inventory_transfer_in_items AS t1

        LEFT JOIN inventory_transfer_out_items ON inventory_transfer_out_items.id =  t1.inventory_transfer_out_item_id
        LEFT JOIN inventory_transfer_in ON inventory_transfer_in.id =  t1.inventory_transfer_id
        LEFT JOIN inventories ON inventories.id =  inventory_transfer_out_items.inventory_id
        LEFT JOIN master_item_products ON master_item_products.id =  inventories.product_id
        LEFT JOIN measures ON measures.id = master_item_products.measure_inventory
        WHERE t1.inventory_transfer_id = $id
        $status
        ";
        return DB::select( $sql);

    }

}
