<?php

namespace App\Exports;

use App\Modal\Purchase_Request;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\Exportable;
use Illuminate\Support\Facades\DB;

class MonitoringPrExport implements FromView
{


    public function __construct( $assigned_id = null, $type = null, $location_id = null,$project_id = null, $start_date = null,$end_date = null)
    {
        $this->assigned_id = $assigned_id;
        $this->type = $type;
        $this->project_id = $project_id;
        $this->location_id = $location_id;
        $this->start_date = $start_date;
        $this->end_date  = $end_date;
    }

    public function view(): View
    {

        
        $query = DB::table('purchase_items')
        ->select(
            'purchase_items.*',
            'purchase_requisitions.doc_no',
            'purchase_requisitions.dpm_no',
            'purchase_requisitions.created_at',
            'master_item_products.name AS productName', 
            'master_item_products.code AS productCode', 
            'master_item_products.part_number AS productPartNumber',
            'locations.name AS location',
            'master_item_brands.name AS productBrand',
            'users.name AS purchaser'
        )
        ->leftJoin('purchase_requisitions', 'purchase_requisitions.id', '=', 'purchase_items.pr_id')
        ->leftJoin('locations', 'locations.id', '=', 'purchase_requisitions.location_id')
        ->leftJoin('master_item_products', 'master_item_products.id', '=', 'purchase_items.product_id')
        ->leftJoin('master_item_brands', 'master_item_products.brand_id', '=', 'master_item_brands.id')
        ->leftJoin('users', 'users.id', '=', 'purchase_items.assigned_id')
        ->when(!empty($this->assigned_id), function ($query) {
            return $query->where('purchase_items.assigned_id',$this->assigned_id);
        })
        ->when(!empty($this->typ), function ($query) {
            return $query->where('purchase_requisitions.type',$this->type);
        })
        ->when(!empty($this->project_id), function ($query) {
            return $query->where('purchase_requisitions.project_id',$this->project_id);
        })
        ->when(!empty($this->location_id), function ($query) {
            return $query->where('purchase_requisitions.location_id',$this->location_id);
        })
        ->when(!empty($this->start_date), function ($query) {
                $start = date("Y-m-d",strtotime($this->start_date));
                $end   = date("Y-m-d",strtotime($this->end_date."+1 day"));
                return $query->whereBetween('purchase_requisitions.created_at', [$start , $end]);
        })  
        ->whereIn('purchase_requisitions.status', array(1,2))
        ->where('purchase_items.status', 4)
        ->where('purchase_items.po_status', 0)
        ->whereNotNull('purchase_items.assigned_id')
        ->get();

        $result = [];
        foreach ($query as $element) {
            $result[$element->doc_no][] = $element;
        }

        return view('exports.monitoring_item_pr', [
            'pr'  => $result ,
        ]);
    
    }

  
}
