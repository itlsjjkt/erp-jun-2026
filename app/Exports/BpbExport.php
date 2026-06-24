<?php

namespace App\Exports;

use App\Modal\Purchase_Request;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\Exportable;
use Illuminate\Support\Facades\DB;

class BpbExport implements FromView
{


    public function __construct($doc_no = null, $location_id = null, $department_id = null,$start_date = null,$end_date = null)
    {
        $this->doc_no          = $doc_no;
        $this->location_id     = $location_id;
        $this->department_id   = $department_id;
        $this->start_date      = $start_date;
        $this->end_date        = $end_date;
    }

    public function view(): View
    {

        $query = DB::table('bpb_items')
        ->select(
            'bpb_items.*',
            'spb_kolis.qty AS itemSPB',
            'bpb.doc_no AS doc_no',
            'bpb.created_at',
            'po.doc_no AS noPO',
            'purchase_requisitions.doc_no AS noPR',
            'purchase_requisitions.dpm_no AS noDPM',
            'spb.doc_no AS noSPB',
            'lpb.doc_no AS noLPB',
            'departments.name AS department',
            'master_item_products.name AS productName',
            'master_item_products.code AS productCode',
            'purchase_items.notes', 
            'master_item_products.part_number AS productPartNumber', 
            'master_item_brands.name AS productBrand', 
            'po_items.measure',
            'po_items.price',
            'po_items.price_discount'
        )
        ->leftJoin('spb_kolis', 'spb_kolis.id', '=', 'bpb_items.spb_item_id')
        ->leftJoin('lpb_items', 'lpb_items.id', '=', 'spb_kolis.spb_item_id')
        ->leftJoin('po_items', 'po_items.id', '=', 'lpb_items.po_item_id')
        ->leftJoin('purchase_items', 'po_items.pr_item_id', '=', 'purchase_items.id')
        ->leftJoin('master_item_products', 'master_item_products.id', '=', 'lpb_items.product_id')
        ->leftJoin('master_item_brands', 'master_item_brands.id', '=', 'master_item_products.brand_id')
        ->leftJoin('bpb', 'bpb.id', '=', 'bpb_items.bpb_id')
        ->leftJoin('spb', 'spb_kolis.spb_id', '=', 'spb.id')
        ->leftJoin('lpb', 'lpb_items.lpb_id', '=', 'lpb.id')
        ->leftJoin('po', 'po_items.po_id', '=', 'po.id')
        ->leftJoin('purchase_requisitions', 'purchase_requisitions.id', '=', 'po.purchase_id')
        ->leftJoin('departments', 'departments.id', '=', 'purchase_requisitions.department_id')
        ->whereNotNull('spb_kolis.spb_id')
        ->when(!empty($this->start_date), function ($query) {
            if($this->end_date){
                $start = date("Y-m-d",strtotime($this->start_date));
                $end   = date("Y-m-d",strtotime($this->end_date."+1 day"));
                return $query->whereBetween('bpb.created_at', [$start , $end]);
            }else{
                return $query->where('bpb.created_at', $this->start_date);
            }
        })
        ->orderby('bpb.doc_no', 'ASC')
        ->get();

        $result = [];
        foreach ($query as $element) {
            $result[$element->doc_no][] = $element;
        }

        return view('exports.bpb', [
            'bpb'  => $result ,
        ]);
    
    }

  
}
