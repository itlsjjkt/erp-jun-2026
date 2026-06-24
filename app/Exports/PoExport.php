<?php

namespace App\Exports;

use App\Modal\Purchase_Request;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\Exportable;
use Illuminate\Support\Facades\DB;
use Auth;

class PoExport implements FromView
{


    public function __construct($project_id = null, $department_id = null, $supplier_id = null,$start_date = null,$end_date = null)
    {
        $this->project_id      = $project_id;
        $this->department_id   = $department_id;
        $this->supplier_id     = $supplier_id;
        $this->start_date      = $start_date;
        $this->end_date        = $end_date;
    }

    public function view(): View
    {

        $query = DB::table('po_items')
            ->select(
                'po_items.*',
                'po.doc_no',
                'po.delivery_date',
                'po.created_at',
                'purchase_requisitions.doc_no AS pr_no',
                'purchase_requisitions.dpm_no AS dpm_no',
                'master_item_products.name AS productName',
                'master_item_products.code AS productCodeNumber',
                'master_item_products.part_number AS productPartNumber',
                'master_item_brands.name AS productBrand',
                'purchase_items.notes', 'purchase_items.notes',
                'suppliers.name AS supplier',
                'purchaser.name AS purchaser',
                'projects.name AS project'
            )
            ->leftJoin('po', 'po.id', '=', 'po_items.po_id')
            ->leftJoin('master_item_products', 'master_item_products.id', '=', 'po_items.product_id')
            ->leftJoin('master_item_brands', 'master_item_products.brand_id', '=', 'master_item_brands.id')
            ->leftJoin('purchase_items', 'purchase_items.id', '=', 'po_items.pr_item_id')
            ->leftJoin('users as purchaser', 'purchaser.id', '=', 'purchase_items.assigned_id')
            ->leftJoin('purchase_requisitions', 'purchase_requisitions.id', '=', 'po.purchase_id')
            ->leftJoin('projects', 'projects.id', '=', 'purchase_requisitions.project_id')
            ->leftJoin('suppliers', 'suppliers.id', '=', 'po.supplier_id')
            ->when(!empty($this->project_id), function ($query) {
                return $query->where('purchase_requisitions.project_id',$this->project_id);
            })
            ->when(!empty($this->department_id), function ($query) {
                return $query->where('purchase_requisitions.department_id',$this->department_id);
            })
            ->when(!empty($this->supplier_id), function ($query) {
                return $query->where('po.supplier_id',$this->supplier_id);
            })
            ->when(!empty($this->start_date), function ($query) {
                $start = date("Y-m-d",strtotime($this->start_date));
                $end   = date("Y-m-d",strtotime($this->end_date."+1 day"));
                return $query->whereBetween('po.created_at', [$start , $end]);
            })
            ->when(Auth::user()->data_access!=1, function ($result) {
                return $result->where('purchase_items.assigned_id', Auth::user()->id);
            })
            ->whereIn('po.status',[1,2,4,5])
	        ->orderBy('po.doc_no','ASC')
            ->orderBy('po.created_at','ASC')
            ->get();

        $result = [];
        foreach ($query as $element) {
            $result[$element->doc_no][] = $element;
        }

        return view('exports.po', [
            'po'  => $result ,
        ]);

    }


}
