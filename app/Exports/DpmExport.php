<?php

namespace App\Exports;

use App\Modal\Purchase_Request;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\Exportable;
use Illuminate\Support\Facades\DB;

class DpmExport implements FromView
{


    public function __construct($company_id = null, $location_id = null, $department_id = null, $project_id = null, $start_date = null,$end_date = null)
    {
        $this->company_id = $company_id;
        $this->location_id = $location_id;
        $this->department_id = $department_id;
        $this->project_id = $project_id;
        $this->start_date = $start_date;
        $this->end_date  = $end_date;
    }

    public function view(): View
    {
        $query = DB::table('purchase_items')
        ->distinct('purchase_items.id')
	    ->select('purchase_items.*',
            'purchases.doc_no AS dpm_no','purchases.publish AS dpm_publish', 'purchases.created_at AS dpm_created','purchases.location_id',
            'purchase_requisitions.doc_no AS pr_no', 'purchase_requisitions.created_at AS pr_publish','purchase_requisitions.id AS pr_id',
            'purchase_requisitions.notes AS reject_reason',
            'rejected.name AS reject_by',
            'master_item_brands.name AS productBrand','master_item_products.name AS productName', 'master_item_products.code AS productCode', 'master_item_products.part_number AS productPartNumber',
            'users.name AS created', 
            'approved.name AS approved', 
            'locations.name AS location',
            'departments.name AS department',
            'projects.name AS project',
            'po_items.id AS po_item_id','po.doc_no AS po_no','po_items.price AS po_price', 'po_items.discount AS po_discount', 'po.ppn AS po_ppn', 'po_items.qty AS po_qty', 'po_items.qty_parsial AS po_qty_parsial','po_items.lpb_status AS po_lpb_status', 'po.publish AS po_publish','po.delivery_date AS po_delivery_date',
            'suppliers.name AS po_supplier', 
            'purchaser.name AS purchaser',
            'lpb.doc_no AS lpb_no', 'lpb.publish AS lpb_publish', 'lpb_items.qty AS lpb_qty', 'lpb.spb_status AS spb_status',
            'spb.doc_no AS spb_no', 'spb.publish AS spb_publish', 'spb_kolis.qty AS spb_qty', 'spb.status AS bpb_status', 'spb_kolis.pr_item_id AS spb_pr_item', 'spb_kolis.id AS spb_kolis_id', 'spb_kolis.spb_item_id AS spb_item_id',
            'bpb.doc_no AS bpb_no', 'bpb.publish AS bpb_publish', 'bpb_items.qty AS bpb_qty', 'bpb_items.id as bpb_items_id'

        )
        ->leftJoin('po_items', 'po_items.pr_item_id', '=', 'purchase_items.id')
        ->leftJoin('po', 'po_items.po_id', '=', 'po.id')
        ->leftJoin('suppliers', 'suppliers.id', '=', 'po.supplier_id')
        ->leftJoin('purchases', 'purchases.id', '=', 'purchase_items.purchase_id')
	    ->leftJoin('purchase_requisitions','purchase_requisitions.id', '=' ,'purchase_items.pr_id')
        ->leftJoin('users as purchaser', 'purchaser.id', '=', 'purchase_items.assigned_id')
        ->leftJoin('locations', 'locations.id', '=', 'purchases.location_id')
        ->leftJoin('master_item_products', 'master_item_products.id', '=', 'purchase_items.product_id')
        ->leftJoin('master_item_brands', 'master_item_products.brand_id', '=', 'master_item_brands.id')
        ->leftJoin('users', 'users.id', '=', 'purchases.created_by')
        ->leftJoin('users as approved', 'users.id', '=', 'purchase_items.last_approved')
        ->leftJoin('users as rejected', 'users.id', '=', 'purchase_requisitions.rejected_by')
        ->leftJoin('departments', 'departments.id', '=', 'purchases.department_id')
        ->leftJoin('projects', 'projects.id', '=', 'purchases.project_id')
        ->leftJoin('lpb_items', 'lpb_items.pr_item_id', '=', 'purchase_items.id')
        ->leftJoin('lpb', 'lpb_items.lpb_id', '=', 'lpb.id')
        ->leftJoin('spb_kolis', 'spb_kolis.pr_item_id', '=', 'purchase_items.id')
        ->leftJoin('spb', 'spb_kolis.spb_id', '=', 'spb.id')
        ->leftJoin('bpb_items', 'bpb_items.pr_item_id', '=', 'purchase_items.id')
        ->leftJoin('bpb', 'bpb_items.bpb_id', '=', 'bpb.id')

        ->when(!empty($this->company_id), function ($query) {
            return $query->where('locations.company_id',$this->company_id);
        })
        ->when(!empty($this->location_id), function ($query) {
            return $query->where('purchases.location_id',$this->location_id);
        })
        ->when(!empty($this->department_id), function ($query) {
            return $query->where('purchases.department_id',$this->department_id);
        })
        ->when(!empty($this->project_id), function ($query) {
            return $query->where('purchases.project_id',$this->project_id);
        })
        ->when(!empty($this->start_date), function ($query) {
            $start = date("Y-m-d",strtotime($this->start_date));
            $end   = date("Y-m-d",strtotime($this->end_date."+1 day"));
            return $query->whereBetween('purchases.created_at', [$start , $end]);
        })
        ->orderby('purchase_items.id')
        ->orderby('purchases.created_at')
        ->orderby('productCode')
        ->orderBy('lpb.publish',  'ASC')
        ->orderBy('spb.publish', 'ASC')
        ->get();


        $result_item = $po =  $lpb = $spb = $bpb = [];

        foreach ($query as $element) {
            $result_item[$element->dpm_no][$element->id][] = $element;


            $po[$element->dpm_no][$element->id][] = [
                'po_no'         =>  $element->po_no,
                'po_delivery_date' => $element->po_delivery_date,
                'po_qty'        =>  $element->po_qty,
                'po_qty_parsial'=>  $element->po_qty_parsial,
                'po_ppn'        =>  $element->po_ppn,
                'po_supplier'   =>  $element->po_supplier,
            ];

            $lpb[$element->dpm_no][$element->id][$element->po_no][] = [
                'lpb_no'        =>  $element->lpb_no,
                'lpb_publish'   =>  $element->lpb_publish,
                'lpb_qty'       =>  $element->lpb_qty
            ];

            $spb[$element->dpm_no][$element->id][$element->po_no][$element->lpb_no][] = [
                'spb_no'        =>  $element->spb_no,
                'spb_publish'   =>  $element->spb_publish,
                'spb_qty'       =>  $element->spb_qty,
                'spb_pr_id'     =>  $element->spb_pr_item,
                'spb_item_id'   =>  $element->spb_item_id,
                'spb_kolis_id'  =>  $element->spb_kolis_id,
            ];

            $bpb[$element->dpm_no][$element->id][$element->po_no][$element->lpb_no][$element->spb_no][] = [
                'bpb_no'        =>  $element->bpb_no,
                'bpb_publish'   =>  $element->bpb_publish,
                'bpb_qty'       =>  $element->bpb_qty,
                'bpb_items_id'  =>  $element->bpb_items_id,
            ];
        }



        $tempPO = $tempLPB = $tempSPB = $tempBPB = [];

        foreach($result_item as $val){
            foreach($val as $item) {

                $tempPO[$item[0]->dpm_no][$item[0]->id] = array_unique($po[$item[0]->dpm_no][$item[0]->id], SORT_REGULAR);
                $tempPO[$item[0]->dpm_no][$item[0]->id] = array_values( $tempPO[$item[0]->dpm_no][$item[0]->id]);

                $tempLPB[$item[0]->dpm_no][$item[0]->id][$item[0]->po_no] = array_unique($lpb[$item[0]->dpm_no][$item[0]->id][$item[0]->po_no], SORT_REGULAR);
                $tempLPB[$item[0]->dpm_no][$item[0]->id][$item[0]->po_no] = array_values( $tempLPB[$item[0]->dpm_no][$item[0]->id][$item[0]->po_no]);

                $tempSPB[$item[0]->dpm_no][$item[0]->id][$item[0]->po_no][$item[0]->lpb_no] = array_unique($spb[$item[0]->dpm_no][$item[0]->id][$item[0]->po_no][$item[0]->lpb_no], SORT_REGULAR);
                $tempSPB[$item[0]->dpm_no][$item[0]->id][$item[0]->po_no][$item[0]->lpb_no] = array_values( $tempSPB[$item[0]->dpm_no][$item[0]->id][$item[0]->po_no][$item[0]->lpb_no]);

                $tempBPB[$item[0]->dpm_no][$item[0]->id][$item[0]->po_no][$item[0]->lpb_no][$item[0]->spb_no] = array_unique($bpb[$item[0]->dpm_no][$item[0]->id][$item[0]->po_no][$item[0]->lpb_no][$item[0]->spb_no] , SORT_REGULAR);
                $tempBPB[$item[0]->dpm_no][$item[0]->id][$item[0]->po_no][$item[0]->lpb_no][$item[0]->spb_no] = array_values( $tempBPB[$item[0]->dpm_no][$item[0]->id][$item[0]->po_no][$item[0]->lpb_no][$item[0]->spb_no] );

            }
        }

        return view('exports.dpm', [
            'dpm_item'  => $result_item,
            'po'   => $tempPO,
            'lpb'  => $tempLPB,
            'spb'  => $tempSPB,
            'bpb'  => $tempBPB,
        ]);

    }


}
