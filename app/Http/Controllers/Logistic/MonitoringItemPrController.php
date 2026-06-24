<?php

namespace App\Http\Controllers\Logistic;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use App\Traits\UploadTrait;
use Vinkla\Hashids\Facades\Hashids;
use App\Models\Notification;
use App\Exports\MonitoringExport;
use Auth;
use Storage;
use Rap2hpoutre\FastExcel\FastExcel;
use Maatwebsite\Excel\Facades\Excel;
use OpenSpout\Common\Entity\Style\Style;
use PhpOffice\PhpSpreadsheet\Worksheet\ColumnDimension;


class MonitoringItemPrController extends Controller
{

    public function index()
    {
        if (!Gate::allows('pr_monitoring_item')) {
            return abort(401);
        }
        return view('logistic.monitoring.item_pr.index');
    }

    public function datatables(Request $request)
    {
        $result = DB::table('purchase_items')
        ->select(
            'purchase_items.*',
            'purchase_requisitions.doc_no AS no_pr',
            'purchase_requisitions.type',
            'departments.name AS department',
            'master_item_products.part_number',
            'purchases.created_at AS created',
            'purchase_requisitions.status AS statusPR',
            'master_item_products.name AS product',
            'users.name AS purchaser',
			'master_item_products.part_number AS PN',
            'master_item_products.code AS code',
            'master_item_brands.name AS brand',
            'purchases.doc_no AS no_dpm',
        )
        ->leftJoin('master_item_products', 'master_item_products.id', '=', 'purchase_items.product_id')
        ->leftJoin('master_item_brands', 'master_item_brands.id', '=', 'master_item_products.brand_id')
        ->leftJoin('purchase_requisitions', 'purchase_requisitions.id', '=', 'purchase_items.pr_id')
        ->leftJoin('departments', 'purchase_requisitions.department_id', '=', 'departments.id')
        ->leftJoin('purchases', 'purchases.id', '=', 'purchase_items.purchase_id')
        ->leftJoin('users', 'users.id', '=', 'purchase_items.assigned_id')
        ->whereNotNull('purchase_requisitions.id')
		->when(Auth::user()->data_access == 2, function ($query) {
            return $query->where('users.id', Auth::user()->id);
		})
        ->when(!empty($request->get('assigned_id')), function ($result) use ($request) {
            return $result->where('purchase_items.assigned_id',$request->get('assigned_id'));
        })
        ->when(!empty($request->get('amp;type')), function ($result) use ($request) {
            return $result->where('purchase_requisitions.type',$request->get('amp;type'));
        })
        ->when(!empty($request->get('amp;project_id')), function ($result) use ($request) {
            return $result->where('purchase_requisitions.project_id',$request->get('amp;project_id'));
        })
        ->when(!empty($request->get('amp;location_id')), function ($result) use ($request){
            return $result->where('purchase_requisitions.location_id',$request->get('amp;location_id'));
        })
        ->when(!empty($request->get('amp;start_date')), function ($result) use ($request) {
            $start = date("Y-m-d",strtotime($request->get('amp;start_date')));
            $end   = date("Y-m-d",strtotime($request->get('amp;end_date')."+1 day"));
            return $result->whereBetween('purchase_requisitions.created_at', [$start , $end]);
        })
        ->orderBy('purchase_requisitions.created_at','DESC');

        return DataTables::of($result)
            ->addColumn('action', function ($result) {
                $url_view = "<a href='" . route('purchasing.pr.show', Hashids::encode($result->pr_id)) . "' target='_blank' title='" . trans('app.show_title') . "' data-toggle='tooltip' class='btn btn-outline'><span class='ti-eye icon-lg'></span></a>";
                return '<div class="btn-group">' . $url_view . '</div>';
            })
            ->addColumn('product', function ($result) {
                return '[' . $result->code . '] ' . $result->product . '<br><small>' .
                    'PN: ' . ($result->part_number ?? '-') . '<br>' .
                    'Brand: ' . ($result->brand ?? '-') . '</small>';
            })
            ->editColumn('created_at', function ($result) {
                return $result->created_at ? with(new Carbon($result->created_at))->format('d/m/Y H:i:s') : '';
            })
            ->editColumn('type', function ($result) {
                return strtoupper($result->type);
            })
            ->addColumn('status', function ($result) {
                return getStatusItemPR($result->pr_status, $result->po_status, $result->qty_parsial, $result->type);
            })
            ->rawColumns(['action', 'status','product','no_pr'])
            ->make(true);
    }

}
