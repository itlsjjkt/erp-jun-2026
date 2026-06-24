<?php

namespace App\Http\Controllers\Purchasing;

use App\Models\PurchaseRequest;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequestItem;
use App\Models\PurchaseRequisition;
use App\Models\Project;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use App\Traits\UploadTrait;
use Vinkla\Hashids\Facades\Hashids;
use App\Models\Notification;
use App\Exports\MonitoringPrExport;
use Auth;
use Storage;
use Rap2hpoutre\FastExcel\FastExcel;
use Maatwebsite\Excel\Facades\Excel;

class MonitoringItemController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:monitoring_pr', ['only' => ['index','show','datatables','close']]);

        $this->type = array(
            '' => 'Silahkan Pilih',
            'po' => 'PO',
            'im'  => 'IM',
            'petty_cash' => 'Petty Cash'
        );
    }
    /**
     * Display a listing of User.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
      
        $location    = DB::table('locations')
        ->selectRaw("CONCAT (locations.name,' - ', companies.alias) as name, locations.id")
        ->leftjoin('companies','companies.id','=','locations.company_id')
        ->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
        $type = $this->type;
        $project = Project::where('status', 1)->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
        $purchaser = User::where('type',4)->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Pilih Purchaser…', '');
		$cekData = DB::table('purchase_items')
            ->leftJoin('users', 'users.id', '=', 'purchase_items.assigned_id')
            ->leftJoin('purchase_requisitions', 'purchase_requisitions.id', '=', 'purchase_items.pr_id')
            ->whereIn('purchase_requisitions.status', array(1,2))
            ->where('purchase_items.status', 4)
            ->where('purchase_items.po_status','=',0)
            ->whereNotNull('purchase_items.assigned_id')
            ->where('users.type',4)
            ->select('users.name','users.type', 'purchase_items.assigned_id', DB::raw('COUNT(purchase_items.assigned_id) as count'))
            ->orderBy('users.name','ASC')
            ->when(Auth::user()->data_access == 2, function ($query) {
                return $query->where('users.id', Auth::user()->id);
            })
            ->groupBy('purchase_items.assigned_id', 'users.name','users.type');
        if(isAdministratorCompany()){
            $cekData->leftJoin('locations','locations.id','=','purchase_requisitions.location_id')
            ->where('locations.company_id','=',Auth::user()->company_id);
        }
        $data = $cekData->get();
        return view('purchase.monitoring.index',compact('location','type','project','purchaser','data'));
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
			'master_item_products.part_number AS PN'
        )
        ->leftJoin('master_item_products', 'master_item_products.id', '=', 'purchase_items.product_id')
        ->leftJoin('purchase_requisitions', 'purchase_requisitions.id', '=', 'purchase_items.pr_id')
        ->leftJoin('departments', 'purchase_requisitions.department_id', '=', 'departments.id')
        ->leftJoin('purchases', 'purchases.id', '=', 'purchase_items.purchase_id')
        ->leftJoin('users', 'users.id', '=', 'purchase_items.assigned_id')
        ->whereIn('purchase_requisitions.status', array(1,2))
        ->where('purchase_items.status', 4)
        ->where('purchase_items.po_status', 0)
        ->whereNotNull('purchase_items.assigned_id')
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
        });

        if(isAdministratorCompany()){
            $result->leftJoin('locations','locations.id','=','purchase_requisitions.location_id')
            ->where('locations.company_id','=',Auth::user()->company_id);
        }

       return  DataTables::of($result)
        ->addColumn('action', function ($result) {
            // $url_view = "<a href='".route('purchasing.monitoring_pr.detail', Hashids::encode($result->pr_id))."' title='".trans('app.show_title')."' data-toggle='tooltip' class='btn btn-outline'><span class='ti-eye icon-lg'></span> </a>";
            $url_view = "<a href='".route('purchasing.pr.show', Hashids::encode($result->pr_id))."' target='_blank' title='".trans('app.show_title')."' data-toggle='tooltip' class='btn btn-outline'><span class='ti-eye icon-lg'></span> </a>";
            return '<div class="btn-group">'.$url_view.'</div>';
        })
        ->editColumn('no_pr', function ($result) {
            $url_pr = "<a target='_blank' href='".route('purchasing.monitoring_pr.detail', Hashids::encode($result->pr_id))."' title='Detail PR' data-toggle='tooltip'>".$result->no_pr."</a>";
            return $url_pr;
        })
        ->editColumn('product', function ($result) {
            $part_number = '';
            if($result->part_number) $part_number = '<br><small>Part Number: '.$result->part_number .'</small>';
            return $result->product.$part_number;
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


    public function detail($id)
    {
       
        $id = Hashids::decode($id);
        $pr = PurchaseRequisition::findOrFail($id['0']);
        $pr_item = PurchaseRequestItem::getItem($id['0']);

        return view('purchase.monitoring.show', compact('pr','pr_item'));

    }


    public function search(Request $request)
    {
        $data = $request->all();
        $query = 'assigned_id='.$request->get('assigned_id').'&type='.$request->get('type').'&project_id='. $request->get('project_id')."&location_id=".$request->get('location_id')."&start_date=".$request->get('start_date')."&end_date=". $request->get('end_date');
        $search = "Cari Berdasarkan: ";
        
        if($request->input('assigned_id')) $search .= "<strong> Purchaser: </strong>".getDataByID('users',$request->input('assigned_id'))->name;
        if($request->input('type')) $search .= "<strong> Tipe DPM: </strong> ".strtoupper($request->input('type'));
        if($request->input('location_id')) $search .= "<strong> Lokasi: </strong>".getDataByID('locations',$request->input('location_id'))->name;
        if($request->input('project_id')) $search .= "<strong> Project: </strong>".getDataByID('projects',$request->input('project_id'))->name;
       
        $search .= "<strong> Periode: </strong>".$request->input('start_date'). " - ". $request->input('end_date');

        $location    = DB::table('locations')
        ->selectRaw("CONCAT (locations.name,' - ', companies.alias) as name, locations.id")
        ->leftjoin('companies','companies.id','=','locations.company_id')
        ->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
        $type = $this->type;
        $project = Project::where('status', 1)->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
        $purchaser = User::where('type',4)->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Pilih Purchaser…', '');
		$cekData2 = DB::table('purchase_items')
            ->leftJoin('users', 'users.id', '=', 'purchase_items.assigned_id')
            ->leftJoin('purchase_requisitions', 'purchase_requisitions.id', '=', 'purchase_items.pr_id')
            ->whereIn('purchase_requisitions.status', array(1,2))
            ->where('purchase_items.status', 4)
            ->where('purchase_items.po_status','=',0)
            ->whereNotNull('purchase_items.assigned_id')
            ->where('users.type',4)
            ->when(Auth::user()->data_access == 2, function ($query) {
                return $query->where('users.id', Auth::user()->id);
            })
            ->select('users.name', 'purchase_items.assigned_id', DB::raw('COUNT(purchase_items.assigned_id) as count'))
            ->orderBy('users.name','ASC')
            ->groupBy('purchase_items.assigned_id', 'users.name');
        if(isAdministratorCompany()){
            $cekData2->leftJoin('locations','locations.id','=','purchase_requisitions.location_id')
            ->where('locations.company_id','=',Auth::user()->company_id);
        }
        $data2 = $cekData2->get();
        return view('purchase.monitoring.search', compact('data','location', 'search','type','purchaser','query','project','data2'));
    }


    public function export(Request $request)
    {
        $date = date('Y-m-d');
        return Excel::download(new MonitoringPrExport( $request->get('assigned_id'),  $request->get('type'), $request->get('location_id'), $request->get('project_id'), $request->get('start_date'), $request->get('end_date')), 'Report-Monitoring-DPM-'.$date.'.xlsx');
    }

}
