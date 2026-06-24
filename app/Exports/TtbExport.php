<?php

namespace App\Exports;

use App\Modal\Purchase_Request;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\Exportable;
use Illuminate\Support\Facades\DB;

class TtbExport implements FromView
{


    public function __construct($department_id = null, $location_id = null,$project_id = null, $start_date = null,$end_date = null)
    {
        $this->department_id   = $department_id;
        $this->location_id     = $location_id;
        $this->project_id      = $project_id;
        $this->start_date      = $start_date;
        $this->end_date        = $end_date;
    }

    public function view(): View
    {

        
        $query = DB::table('inventory_ttb_items')
        ->select(
            'inventory_ttb_items.*',
            'inventory_ttbs.doc_no',
            'inventory_ttbs.created_at',
            'inventory_ttbs.date_transaction',
            'inventory_ttbs.operator',
            'inventory_ttbs.received',
            'departments.name AS department',
            'projects.name AS project',
            'inventories.code_rack',
            'inventories.id AS inv_id',
            'inventories.price AS price',
            'inventories.price_after_discount AS price_after_discount',
            'master_item_products.name AS productName', 
            'master_item_products.code AS productCode', 
            'master_item_products.part_number AS productPartNumber',
            'measures.name AS measure'
        )
        ->leftJoin('inventory_ttbs', 'inventory_ttbs.id', '=', 'inventory_ttb_items.inventory_ttb_id')
        ->leftJoin('inventories', 'inventories.id', '=', 'inventory_ttb_items.inventory_id')
        ->leftJoin('projects', 'projects.id', '=', 'inventory_ttbs.project_id')
        ->leftJoin('departments', 'departments.id', '=', 'inventory_ttbs.department_id')
        ->leftJoin('master_item_products', 'master_item_products.id', '=', 'inventories.product_id')
        ->leftJoin('measures', 'measures.id', '=', 'inventories.measure_id')
        ->when(!empty($this->department_id), function ($query) {
            return $query->where('inventory_ttbs.department_id',$this->department_id);
        })
        ->when(!empty($this->project_id), function ($query) {
            return $query->where('inventory_ttbs.project_id',$this->project_id);
        })
        ->when(!empty($this->location_id), function ($query) {
            return $query->where('inventory_ttbs.location_id',$this->location_id);
        })
        ->when(!empty($this->start_date), function ($query) {
            $start = date("Y-m-d",strtotime($this->start_date));
            $end   = date("Y-m-d",strtotime($this->end_date."+1 day"));
            return $query->whereBetween('inventory_ttbs.created_at', [$start , $end]);
        })
        ->where('inventory_ttbs.status',1)
        ->where('inventory_ttbs.is_local',false)
        ->orderBy('inventory_ttbs.created_at','DESC')
        ->get();

        $result = [];
        foreach ($query as $element) {
            $result[$element->doc_no][] = $element;
        }

        return view('exports.ttb', [
            'ttb'  => $result ,
        ]);
    
    }

  
}
