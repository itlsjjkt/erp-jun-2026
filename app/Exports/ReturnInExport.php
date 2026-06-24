<?php

namespace App\Exports;

use App\Modal\Purchase_Request;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\Exportable;
use Illuminate\Support\Facades\DB;

class ReturnInExport implements FromView
{


    public function __construct($doc_no = null,  $location_id = null,$start_date = null,$end_date = null)
    {
        $this->doc_no          = $doc_no;
        $this->location_id     = $location_id;
        $this->start_date      = $start_date;
        $this->end_date        = $end_date;
    }

    public function view(): View
    {

        
        $query = DB::table('inventory_return_in_items')
        ->select(
            'inventory_return_in_items.*',
            'inventory_return_in.doc_no',
            'inventory_return_in.created_at',
            'inventory_return_in.operator',
            'inventories.code_rack','inventories.id AS inv_id',
            'master_item_products.name AS productName', 'master_item_products.code AS productCode', 'master_item_products.part_number AS productPartNumber','measures.name AS measure'
        )
        ->leftJoin('inventory_return_in', 'inventory_return_in.id', '=', 'inventory_return_in_items.inventory_return_in_id')
        ->leftJoin('inventories', 'inventories.id', '=', 'inventory_return_in_items.inventory_id')
        ->leftJoin('master_item_products', 'master_item_products.id', '=', 'inventories.product_id')
        ->leftJoin('measures', 'measures.id', '=', 'master_item_products.measure_id')
        ->when(!empty($this->doc_no), function ($query) {
            return $query->where('inventory_return_in.doc_no',$this->doc_no);
        })->when(!empty($this->location_id), function ($query) {
            return $query->where('inventory_return_in.location_id',$this->location_id);
        })->when(!empty($this->start_date), function ($query) {
            if($this->end_date){
                $start = date("Y-m-d",strtotime($this->start_date));
                $end   = date("Y-m-d",strtotime($this->end_date."+1 day"));
                return $query->whereBetween('inventory_return_in.created_at', [$start , $end]);
            }else{
                return $query->where('inventory_return_in.created_at', $this->start_date);
            }
        })
        /**->where('inventory_return_in.status',1)
        */
        ->orderBy('inventory_return_in.created_at','DESC')
        ->get();

        $result = [];
        foreach ($query as $element) {
            $result[$element->doc_no][] = $element;
        }

        return view('exports.return_in', [
            'return_in'  => $result ,
        ]);
    
    }

  
}
