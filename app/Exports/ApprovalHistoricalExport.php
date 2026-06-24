<?php

namespace App\Exports;

use App\Modal\Purchase_Request;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\Exportable;
use Illuminate\Support\Facades\DB;

class ApprovalHistoricalExport implements FromView
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
        //DB::connection()->enableQueryLog();
        $query = DB::table('purchase_items')
        ->distinct('purchase_items.id')
	    ->select('purchase_items.*', 'purchases.doc_no AS dpm_no','purchases.publish AS dpm_publish', 'purchases.created_at AS dpm_created','purchases.location_id', 'master_item_brands.name AS productBrand','master_item_products.name AS productName', 'master_item_products.code AS productCode', 'master_item_products.part_number AS productPartNumber', 'purchase_notes.user_id as approved_id', 'users.name AS approved', 'purchase_notes.created_at as dpm_date_approved', 'locations.name AS location','departments.name AS department')
        ->leftJoin('purchases', 'purchases.id', '=', 'purchase_items.purchase_id')
        ->leftJoin('purchase_notes', 'purchase_notes.pr_item_id', '=', 'purchase_items.id')
        ->leftJoin('master_item_products', 'master_item_products.id', '=', 'purchase_items.product_id')
        ->leftJoin('master_item_brands', 'master_item_products.brand_id', '=', 'master_item_brands.id')
        ->leftJoin('departments', 'departments.id', '=', 'purchases.department_id')
        ->leftJoin('projects', 'projects.id', '=', 'purchases.project_id')
        ->leftJoin('users', 'users.id', '=', 'purchase_notes.user_id')
        ->leftJoin('locations', 'locations.id', '=', 'purchases.location_id')
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
        ->orderby('purchase_notes.created_at')
        ->get();


        $result_item = $approvaldpm = [];

        foreach ($query as $element) {
            $result_item[$element->dpm_no][$element->id][] = $element;


            $po[$element->dpm_no][$element->id][] = [
                'approved'      =>  $element->approved,
                'approved_id'   =>  $element->approved_id,
                'date_approved' =>  $element->dpm_date_approved,
            ];
        }

        $tempDPM = [];

        foreach($result_item as $val){
            foreach($val as $item) {

                $tempDPM[$item[0]->dpm_no][$item[0]->id] = array_unique($po[$item[0]->dpm_no][$item[0]->id], SORT_REGULAR);
                $tempDPM[$item[0]->dpm_no][$item[0]->id] = array_values( $tempDPM[$item[0]->dpm_no][$item[0]->id]);

            }
        }

        //Log::debug(DB::getQueryLog());

        return view('exports.approval_historical', [
            'location'      =>  $this->location_id,
            'dpm_item'      => $result_item,
            'dpm_approve'   => $tempDPM,
        ]);

    }


}
