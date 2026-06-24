<?php

namespace App\Exports;

use App\Modal\Purchase_Request;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\Exportable;
use Illuminate\Support\Facades\DB;

class PrExportMerge implements FromView
{


    public function __construct($doc_no = null)
    {
        $this->doc_no  = explode(',',$doc_no);
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
        ->whereIn('purchase_requisitions.id',$this->doc_no)
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
