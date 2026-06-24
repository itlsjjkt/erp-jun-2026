<?php

namespace App\Exports;

use App\Modal\Purchase_Request;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\Exportable;
use Illuminate\Support\Facades\DB;

class ConversionExport implements FromView
{


    public function __construct( $location_id = null,$start_date = null,$end_date = null)
    {
        $this->location        = $location_id;
        $this->start_date      = $start_date;
        $this->end_date        = $end_date;
    }

    public function view(): View
    {

        $start = date("Y-m-d",strtotime($this->start_date));
        $end   = date("Y-m-d",strtotime($this->end_date."+1 day"));
        $date= " WHERE inventory_conversions.created_at::date >= to_date('".$start."','YYYY-MM-DD') AND inventory_conversions.created_at::date <= to_date('".$end."','YYYY-MM-DD') ";

        $loc='';
        if($this->location != null ){
            $loc = " AND inventory_conversions.location_id = ". $this->location;
        }

        $sql = "
        SELECT 
        inventory_conversions.doc_no, 
        inventory_conversions.created_at, 
        inventory_conversions.operator,
        inventories.code_rack AS coderack1, master_item_products.code AS productcode1, master_item_products.name AS productname1, 
        master_item_products.part_number AS productpartnumber1, measures.name AS productunit1, t1.qty_from AS qty_stock,
        t2.coderack2, t2.productcode2,
        t2.productname2, t2.productpartnumber2, t2.productunit2, t2.qty_conversion
        
        FROM 
         inventory_conversion_items AS t1
        
        INNER JOIN (
            SELECT 
            t2.qty_to AS qty_conversion, t2.inventory_conversion_id,
            inventories.code_rack AS coderack2,
            master_item_products.name AS productname2,master_item_products.code AS productcode2, master_item_products.part_number AS productpartnumber2, measures.name AS productunit2
            FROM 
            inventory_conversion_items AS t2
            LEFT JOIN inventory_conversions ON inventory_conversions.id =  t2.inventory_conversion_id
            LEFT JOIN inventories ON inventories.id = t2.inventory_id_to
            LEFT JOIN master_item_products ON master_item_products.id =  inventories.product_id
            LEFT JOIN measures ON measures.id = master_item_products.measure_id
        ) AS t2 ON t2.inventory_conversion_id = t1.inventory_conversion_id 
        
        LEFT JOIN inventory_conversions ON inventory_conversions.id =  t1.inventory_conversion_id
        LEFT JOIN inventories ON inventories.id =  t1.inventory_id_from
        LEFT JOIN master_item_products ON master_item_products.id =  inventories.product_id
        LEFT JOIN measures ON measures.id = master_item_products.measure_id
        $date
        $loc
        ";
        $query=DB::select($sql);


        $result = [];
        foreach ($query as $element) {
            $result[$element->doc_no][] = $element;
        }

        return view('exports.conversion', [
            'conversion'  => $result ,
        ]);
    
    }

  
}
