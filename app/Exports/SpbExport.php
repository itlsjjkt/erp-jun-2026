<?php

namespace App\Exports;

use App\Modal\Purchase_Request;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\Exportable;
use Illuminate\Support\Facades\DB;

class SpbExport implements FromView
{


    public function __construct($doc_no = null, $category_id = null, $location_id = null,$start_date = null,$end_date = null)
    {
        $this->doc_no          = $doc_no;
        $this->category_id     = $category_id;
        $this->location_id     = $location_id;
        $this->start_date      = $start_date;
        $this->end_date        = $end_date;
    }

    public function view(): View
    {

        
        $query = DB::table('purchase_items')
        ->select('purchase_items.*',
        'purchase_requisitions.doc_no','purchase_requisitions.dpm_no','purchase_requisitions.created_at',
        'master_item_products.name AS productName', 'master_item_products.part_number AS productPartNumber',
        'master_item_brands.name AS productBrand')
        ->leftJoin('purchase_requisitions', 'purchase_requisitions.id', '=', 'purchase_items.pr_id')
        ->leftJoin('master_item_products', 'master_item_products.id', '=', 'purchase_items.product_id')
        ->leftJoin('master_item_brands', 'master_item_products.brand_id', '=', 'master_item_brands.id')
        ->when(!empty($this->doc_no), function ($query) {
            return $query->where('purchase_requisitions.doc_no',$this->doc_no);
        })->when(!empty($this->category_id), function ($query) {
            return $query->where('purchase_requisitions.category_id',$this->category_id);
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
        ->where('purchase_requisitions.status',0)
        ->where('purchase_items.pr_status',1)
        ->whereIn('purchase_items.po_status',[0,2])
        ->orderBy('purchase_requisitions.created_at','DESC')
        ->get();

        $result = [];
        foreach ($query as $element) {
            $result[$element->doc_no][] = $element;
        }

        return view('exports.pr', [
            'pr'  => $result ,
        ]);
    
    }

  
}
