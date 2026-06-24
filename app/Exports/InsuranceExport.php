<?php

namespace App\Exports;

use App\Modal\Purchase_Request;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\Exportable;
use Illuminate\Support\Facades\DB;

class InsuranceExport implements FromView
{


    public function __construct($start_date = null,$end_date = null)
    {
        $this->start_date      = $start_date;
        $this->end_date        = $end_date;
    }

    public function view(): View
    {

        $start = date("Y-m-d",strtotime($this->start_date));
        $end   = date("Y-m-d",strtotime($this->end_date."+1 day"));

        $query = DB::table('insurance_items')
          ->select('insurance_items.*',
          'spb_kolis.annotation',
          'insurances.*',
          'po.doc_no AS noPO','purchase_requisitions.dpm_no AS noDPM', 'purchase_requisitions.doc_no AS noPR',
          'spb.doc_no AS noSPB',
          'lpb.doc_no AS noLPB',
          'spb_kolis.id AS idKoli',
          'spb_kolis.qty AS qtyKoli',
          'suppliers.name AS supplier',
          'po_items.price as price','po_items.measure',
          'master_item_products.name AS productName','purchase_items.notes', 
          'master_item_products.code AS productCode', 'master_item_products.part_number AS productPartNumber', 'master_item_brands.name AS productBrand')
          ->leftJoin('spb_kolis', 'insurance_items.spb_item_id', '=', 'spb_kolis.id')
          ->leftJoin('lpb_items', 'lpb_items.id', '=', 'spb_kolis.spb_item_id')
          ->leftJoin('master_item_products', 'master_item_products.id', '=', 'lpb_items.product_id')
          ->leftJoin('master_item_brands', 'master_item_brands.id', '=', 'master_item_products.brand_id')
          ->leftJoin('po_items', 'po_items.id', '=', 'lpb_items.po_item_id')
          ->leftJoin('insurances', 'insurance_items.insurance_id', '=', 'insurances.id')
          ->leftJoin('purchase_items', 'purchase_items.id', '=', 'po_items.pr_item_id')
          ->leftJoin('po', 'po_items.po_id', '=', 'po.id')
          ->leftJoin('spb', 'spb_kolis.spb_id', '=', 'spb.id')
          ->leftJoin('lpb', 'spb_kolis.lpb_id', '=', 'lpb.id')
          ->leftJoin('suppliers', 'suppliers.id', '=', 'po.supplier_id')
          ->leftJoin('purchase_requisitions', 'purchase_requisitions.id', '=', 'po.purchase_id')
          ->whereBetween('insurances.created_at', [$start , $end])
          ->get();

        $result = [];
        foreach ($query as $element) {
            $result[$element->doc_no][] = $element;
        }

        return view('exports.insurance', [
            'insurance'  => $result ,
        ]);
    
    }

  
}
