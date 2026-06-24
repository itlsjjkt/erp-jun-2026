<?php

namespace App\Exports;

use App\Modal\Purchase_Request;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\Exportable;
use Illuminate\Support\Facades\DB;
use Auth;

class PrExport implements FromView
{


    public function __construct($filter)
    {
        $this->filter = $filter;
    }

    public function view(): View
    {

        $filter = $this->filter; 

        $query = DB::table('purchase_items')
        ->select('purchase_items.*',
        'purchase_requisitions.doc_no',
        'purchase_requisitions.dpm_no',
        'purchase_requisitions.type',
        'purchase_requisitions.created_at',
        'master_item_products.name AS productName', 
        'master_item_products.part_number AS productPartNumber',
        'purchaser.name AS purchaser',
        'departments.name AS department',
        'projects.name AS project',
        'master_item_brands.name AS productBrand'
        )
        ->leftJoin('purchase_requisitions', 'purchase_requisitions.id', '=', 'purchase_items.pr_id')
        ->leftJoin('master_item_products', 'master_item_products.id', '=', 'purchase_items.product_id')
        ->leftJoin('master_item_brands', 'master_item_products.brand_id', '=', 'master_item_brands.id')
        ->leftJoin('users as purchaser', 'purchaser.id', '=', 'purchase_items.assigned_id')
        ->leftJoin('departments', 'departments.id', '=', 'purchase_requisitions.department_id')
        ->leftJoin('projects', 'projects.id', '=', 'purchase_requisitions.project_id')
        ->when(!empty($filter['type']), function ($result) use ($filter) {
            return $result->where('purchase_requisitions.type',$filter['type']);
        })
        ->when(!empty($filter['status']), function ($result) use ($filter) {
            $status = ($filter['status']=='null') ? 0 : $filter['status'];
            return $result->where('purchase_requisitions.status',$status);
        })
        ->when(!empty($filter['project_id']), function ($result) use ($filter) {
            return $result->where('purchase_requisitions.project_id',$filter['project_id']);
        })
        ->when(!empty($filter['location_id']), function ($result) use ($filter){
            return $result->where('purchase_requisitions.location_id',$filter['location_id']);
        })
        ->when(!empty($filter['start_date']), function ($result) use ($filter) {
            $start = date("Y-m-d",strtotime($filter['start_date']));
            $end   = date("Y-m-d",strtotime($filter['end_date']."+1 day"));
            return $result->whereBetween('purchase_requisitions.created_at', [$start , $end]);
        })
        ->when(Auth::user()->data_access!=1, function ($result) {
            return $result->where('purchase_items.assigned_id', Auth::user()->id);
        })
        ->orderBy('purchase_requisitions.created_at','DESC')
        ->orderBy('purchase_items.id','ASC')
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
