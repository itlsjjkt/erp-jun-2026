<?php

namespace App\Exports;

use App\Modal\Purchase_Request;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\Exportable;
use Illuminate\Support\Facades\DB;

class TtbExportCostCenter implements FromView
{


    public function __construct($item_id = null, $location_id = null,$start_date = null,$end_date = null)
    {
        $this->item_id   = $item_id;
        $this->location_id     = $location_id;
        $this->start_date      = $start_date;
        $this->end_date        = $end_date;
    }

    public function view(): View
    {

        
        $query = DB::table('inventory_ttb_items')
        ->select(
            'inventory_ttb_items.*',
            'inventory_ttbs.doc_no','inventory_ttbs.operator','inventory_ttbs.received','departments.name AS department','inventory_ttbs.coa',
            'inventories.code_rack','inventories.id AS inv_id', 'cost_pengambil.code AS cc_pengambil_code',
            'cost_pengambil.name AS cc_pengambil_name', 'cost_centre.code AS cc_pemakai_code','cost_centre.name AS cc_pemakai_name',
            'master_item_products.name AS productName', 'master_item_products.code AS productCode', 'master_item_products.part_number AS productPartNumber','measures.name AS measure'
        )
        ->leftJoin('inventory_ttbs', 'inventory_ttbs.id', '=', 'inventory_ttb_items.inventory_ttb_id')
        ->leftJoin('cost_centre as cost_pengambil', 'cost_pengambil.code', '=', 'inventory_ttbs.coa')
        ->leftJoin('inventories', 'inventories.id', '=', 'inventory_ttb_items.inventory_id')
        ->leftJoin('cost_centre', 'cost_centre.id', '=', 'inventory_ttb_items.cost_center')
        ->leftJoin('departments', 'departments.id', '=', 'inventory_ttbs.department_id')
        ->leftJoin('master_item_products', 'master_item_products.id', '=', 'inventories.product_id')
        ->leftJoin('measures', 'measures.id', '=', 'inventories.measure_id')
        ->when(!empty($this->item_id), function ($query) {
            return $query->where('master_item_products.item_id',$this->item_id);
        })->when(!empty($this->location_id), function ($query) {
            return $query->where('inventory_ttbs.location_id',$this->location_id);
        })->when(!empty($this->start_date), function ($query) {
            if($this->end_date){
                $start = date("Y-m-d",strtotime($this->start_date));
                $end   = date("Y-m-d",strtotime($this->end_date."+1 day"));
                return $query->whereBetween('inventory_ttbs.created_at', [$start , $end]);
            }else{
                return $query->where('inventory_ttbs.created_at', $this->start_date);
            }
        })
        ->where('inventory_ttbs.status',1)
        ->orderBy('inventory_ttbs.created_at','DESC')
        ->get();

        $result = [];
        $result_item = [];

        foreach ($query as $element) {
            $result[$element->cc_pemakai_code] = [
                'coa'           => $element->cc_pemakai_code,
                'coa_name'      => $element->cc_pemakai_name,
            ];
            $result_item[$element->cc_pemakai_code][] = $element;
        }

        $periode =  date("d/m/Y",strtotime($this->start_date))." - ".date("d/m/Y",strtotime($this->end_date));
        if(!empty($this->item_id)){
            $item    = DB::table('master_items')->where('id',$this->item_id)->first()->name;
        }else{
            $item   ='All';
        }

        return view('exports.ttb_costcenter', [
            'ttb'       => $result,
            'ttb_item'  => $result_item,
            'periode'   => $periode,
            'item'      => $item,
        ]);
    
    }

  
}
