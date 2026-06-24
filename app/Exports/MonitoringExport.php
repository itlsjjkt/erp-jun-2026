<?php

namespace App\Exports;

use App\Modal\Purchase_Request;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\Exportable;
use Illuminate\Support\Facades\DB;

class MonitoringExport implements FromView
{


    public function __construct( $location_id = null,$department_id = null, $start_date = null,$end_date = null)
    {
        $this->department_id   = $department_id;
        $this->location_id     = $location_id;
        $this->start_date      = $start_date;
        $this->end_date        = $end_date;
    }

    public function view(): View
    {

        
        $query = DB::table('purchase_items')
        ->select('purchase_items.*',
        'purchase_requisitions.doc_no',
        'purchase_requisitions.dpm_no',
        'purchase_requisitions.created_at',
        'po.doc_no AS noPO',
        'lpb.doc_no AS noLPB',
        'master_item_products.name AS productName', 
        'master_item_products.code AS productCode', 
        'master_item_products.part_number AS productPartNumber',
        'locations.name AS location',
        'master_item_brands.name AS productBrand')
        ->leftJoin('purchase_requisitions', 'purchase_requisitions.id', '=', 'purchase_items.pr_id')
        ->leftJoin('po', 'po.purchase_id', '=', 'purchase_requisitions.id')
        ->leftJoin('lpb', 'lpb.po_id', '=', 'po.id')
        ->leftJoin('locations', 'locations.id', '=', 'purchase_requisitions.location_id')
        ->leftJoin('master_item_products', 'master_item_products.id', '=', 'purchase_items.product_id')
        ->leftJoin('master_item_brands', 'master_item_products.brand_id', '=', 'master_item_brands.id')
        ->when(!empty($this->department_id), function ($query) {
            return $query->where('purchase_requisitions.department_id',$this->department_id);
        })->when(!empty($this->location_id), function ($query) {
            return $query->where('purchase_requisitions.location_id',$this->location_id);
        })->when(!empty($this->start_date), function ($query) {
            if($this->end_date){
                $start = date("Y-m-d",strtotime($this->start_date));
                $end   = date("Y-m-d",strtotime($this->end_date."+1 day"));
                return $query->whereBetween('purchase_requisitions.created_at', [$start , $end]);
            }else{
                return $query->where('purchase_requisitions.created_at', $this->start_date);
            }
        })
        ->where('purchase_items.pr_status',1)
        ->orderBy('purchase_requisitions.created_at','DESC')
        ->get();

        $result = [];
        foreach ($query as $element) {
            $result[$element->doc_no][] = $element;
        }

        return view('exports.monitoring', [
            'pr'  => $result ,
        ]);
    
    }

  
}
