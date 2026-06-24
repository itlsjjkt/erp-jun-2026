<?php

namespace App\Exports;

use App\Modal\Purchase_Request;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\Exportable;
use Illuminate\Support\Facades\DB;

class DpmExportNew implements FromView
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
        'purchases.doc_no AS no_dpm',
        'purchases.created_at AS tgl_dpm',
        'purchase_requisitions.doc_no AS no_pr',
        'purchase_requisitions.created_at AS tgl_pr',
        'purchaser.name AS purchaser',
        'master_item_products.code AS product_code',
        'master_item_products.name AS product_name',
        'master_item_products.part_number AS product_part_number',
        'master_item_brands.name AS product_brand',
        'purchase_items.qty AS dpm_qty',
        'purchase_items.measure AS dpm_satuan',
        'purchase_items.notes AS dpm_notes',
        'purchase_items.flag AS dpm_flag',
        'po.price_term AS po_price_term',
        'po.price_term_location AS po_price_term_location',
        'purchase_items.needed_on_date AS dpm_needed',
        'purchase_items.last_approved_at AS last_approval',
        'departments.name AS department',
        'projects.name AS project',
        'purchase_items.reason AS alasan_reject_dpm',
        'rejected.name AS close_pr_by',
        'purchase_requisitions.notes AS alasan_close_pr',
        'users.name AS created_by',
        'locations.name AS location',
        'po.doc_no AS no_po',
        'po_items.qty AS qty_po',
        'suppliers.name AS supplier',
        'lpb.doc_no AS no_lpb',
        'lpb.publish AS publish_lpb',
        'lpb_items.qty AS qty_lpb',
        'spb.doc_no AS no_spb',
        'spb.publish AS publish_spb',
        'spb_kolis.qty AS qty_spb',
        'bpb.doc_no AS no_bpb',
        'bpb.publish AS publish_bpb',
        'bpb_items.qty AS qty_bpb',
        'bpb.received_by AS received_bpb',
        'purchase_items.status AS status',
        'purchase_items.pr_status AS pr_status',
        'purchase_items.po_status AS po_status',
        'purchase_items.qty_parsial AS qty_parsial',
        'po_items.lpb_status AS po_lpb_status',
        'po_items.qty_parsial AS po_qty_parsial',
        'lpb.spb_status AS spb_status',
        'spb.status AS bpb_status'
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
        ->orderby('purchase_items.id', 'DESC')
        ->get();
            
        return view('exports.dpm_new', [
            'dpm_item'  => $query
        ]);

    }


}
