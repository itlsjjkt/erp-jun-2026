<?php

namespace App\Http\Controllers\Logistic;

use App\Models\PurchaseRequest;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequestItem;
use App\Models\PurchaseRequestHistory;
use App\Models\PurchaseRequisition;
use App\Models\Lpb;
use App\Models\Spb;
use App\Models\Bpb;
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

class MonitoringController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:dpm_monitoring', ['only' => ['index','export']]);
    }
    /**
     * Display a listing of User.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        if(isAdministrator() || isAdmin() || isPurchasing() ){
            $location    = DB::table('locations')
                ->selectRaw("CONCAT (locations.name,' - ', companies.alias) as name, locations.id")
                ->leftjoin('companies','companies.id','=','locations.company_id')
                ->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
            $department  = DB::table('departments')
                ->selectRaw("CONCAT (departments.name,' - ', companies.alias) as name, departments.id")
                ->leftjoin('companies','companies.id','=','departments.company_id')
                ->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
        }elseif(isAdministratorCompany()){
            $location   = DB::table('locations')->where('company_id', Auth::user()->company_id)->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
            $department = DB::table('departments')->where('company_id', Auth::user()->company_id)->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
        }elseif(isAdministratorLocation()){
            $location   = DB::table('locations')->where('id', Auth::user()->location_id)->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
            $department = DB::table('departments')->where('company_id', Auth::user()->company_id)->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
        }else{
            $location = DB::table('locations')->where('id', Auth::user()->location_id)->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
            $department = DB::table('departments')->where('company_id', Auth::user()->company_id)->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
        }

        $type = array(
            "im" => "IM",
            "petty_cash" => "Petty Cash",
            "po" => "PO",
        );

        return view('logistic.monitoring.dpm.index',compact('location','department','type'));
    }

    public function datatables()
    {

        if(isAdministrator() || isAdmin() || isPurchasing()){
            $result = DB::table('purchases')
            ->select('purchases.*','departments.name AS department','projects.name AS project','locations.name AS locationnn')
            ->leftJoin('projects', 'projects.id', '=', 'purchases.project_id')
            ->leftJoin('departments', 'departments.id', '=', 'purchases.department_id')
            ->leftJoin('locations', 'locations.id', '=', 'purchases.location_id')
            ->where('purchases.status', '!=' ,0);
        }elseif(isAdministratorCompany()){
            $result = DB::table('purchases')
            ->select('purchases.*','departments.name AS department','projects.name AS project','locations.name AS locationnn')
            ->leftJoin('projects', 'projects.id', '=', 'purchases.project_id')
            ->leftJoin('departments', 'departments.id', '=', 'purchases.department_id')
            ->leftJoin('locations', 'locations.id', '=', 'purchases.location_id')
            ->leftJoin('companies', 'companies.id', '=', 'locations.company_id')
            ->where('companies.id', Auth::user()->company_id)
            ->where('purchases.status', '!=' ,0);
        } else{
            $result = DB::table('purchases')
            ->select('purchases.*','departments.name AS department','projects.name AS project','locations.name AS locationnn')
            ->leftJoin('projects', 'projects.id', '=', 'purchases.project_id')
            ->leftJoin('departments', 'departments.id', '=', 'purchases.department_id')
            ->leftJoin('locations', 'locations.id', '=', 'purchases.location_id')
            ->where('purchases.location_id', Auth::user()->location_id)
            ->where('purchases.status', '!=' ,0);
        }

       return  DataTables::of($result)
        ->addColumn('action', function ($result) {
            $url_view = "<a href='".route('logistic.monitoring.dpm.detail', ['id' => Hashids::encode($result->id)])."' title='".trans('app.show_title')."' data-toggle='tooltip' class='btn btn-outline'><span class='ti-eye icon-lg'></span> </a>";
            return '<div class="btn-group">'.$url_view.'</div>';

        })
		->editColumn('doc_no', function ($result) {
        $doc_no = "<a target='_blank' href='".route('logistic.monitoring.dpm.detail', ['id' => Hashids::encode($result->id)])."' title='Detail DPM' data-toggle='tooltip' >".$result->doc_no."</a>";
        return $doc_no;
        })
        ->editColumn('type', function ($result) {
        return strtoupper($result->type);
        })
        ->editColumn('created_at', function ($result) {
            return $result->created_at ? with(new Carbon($result->created_at))->format('d/m/Y H:i:s') : '';
        })
        ->rawColumns(['action', 'status','doc_no'])
        ->make(true);

    }


    public function detail($id)
    {

        $id = Hashids::decode($id);
        $dpm        = PurchaseRequest::getByID($id['0']);
        $dpm_items  = PurchaseRequest::getProductItem($id['0']);
        $pr_history = PurchaseRequestHistory::where('purchase_id',$dpm->id)->where('jenis','hold')->latest()->first();

        $pr         = PurchaseRequisition::getByDPM($id['0']);
        $po         = PurchaseOrder::getByDPM($id['0']);
        $lpb        = Lpb::getByDPM($id['0']);

        $lpb_id = $spb = [];

        foreach($lpb as $val){
            $lpb_id []= $val->id;
        }

        $spb = Spb::getByLPBItem($lpb_id);

        $spb_data = [];
        $spb_item_id = [];
        foreach($spb as $val){
            $spb_item_id[] = $val->spb_item_id;
            $spb_data[$val->spb_id]['doc_no']  = $val->doc_no;
            $spb_data[$val->spb_id]['spb_id']  = $val->spb_id;
            $spb_data[$val->spb_id]['lpb_id']  = $val->lpb_id;
            $spb_data[$val->spb_id]['created'] = $val->created;
            $spb_data[$val->spb_id]['created_at'] = $val->created_at;
            $spb_data[$val->spb_id]['type']    = $val->type;
            $spb_data[$val->spb_id]['is_pickup']    = $val->is_pickup;
            $spb_data[$val->spb_id]['dpm_id']    = $dpm->id;
            $spb_data[$val->spb_id]['status_spb']    = $val->status_spb;
        }

        $bpb = Bpb::getByLPBItem($spb_item_id);

        $bpb_data = $spb_kolis_id = [];
        foreach($bpb as $val){
            $spb_kolis_id[] = $val->spb_item_id;
            $bpb_data[$val->bpb_id]['doc_no']  = $val->doc_no;
            $bpb_data[$val->bpb_id]['bpb_id']  = $val->bpb_id;
            $bpb_data[$val->bpb_id]['spb_item_id'] = $spb_kolis_id;
            $bpb_data[$val->bpb_id]['spb_no']  = $val->spb_no;
            $bpb_data[$val->bpb_id]['created'] = $val->created;
            $bpb_data[$val->bpb_id]['created_at'] = $val->created_at;
            $bpb_data[$val->bpb_id]['received_by']    = $val->received_by;
        }
        $po_id = [];
        foreach($po as $val){
            $po_id []= $val->id;
        }
        $bpb_franco = Bpb::getBPBFranco($po_id);

        return view('logistic.monitoring.dpm.show', compact('dpm','dpm_items','pr','po','lpb','spb_data','bpb_data','pr_history','bpb_franco'));

    }


    public function search(Request $request)
    {

        $data = $request->all();
        $search = "Cari Berdasarkan: ";
        if($request->input('product_name')){
            $search .= "<strong> Nama Product: </strong> ".$request->input('product_name');
            $product_name = $request->input('product_name');
        }else{
            $product_name = "";
        }

        if($request->input('type_dpm')){
            $search .= "<strong> Tipe DPM: </strong> ".strtoupper($request->input('type_dpm'));
            $type_dpm = $request->input('type_dpm');
        }else{
            $type_dpm = "";
        }

        if($request->input('pr_no')){
            $search .= "<strong> No. PR: </strong> ".$request->input('pr_no');
            $pr_no = $request->input('pr_no');
        }else{
            $pr_no = "";
        }

        if($request->input('description')){
            $search .= "<strong> Deskripsi: </strong> ".$request->input('description');
            $description = $request->input('description');
        }else{
            $description = "";
        }

        if($request->input('location_id')){
            $search .= "<strong> Lokasi: </strong>".getDataByID('locations',$request->input('location_id'))->name;
            $location_id = $request->input('location_id');
        }else{
            $location_id = "";
        }
        if($request->input('start_date') || $request->input('end_date')){
            $search .= "<strong> Periode: </strong>".$request->input('start_date'). " - ". $request->input('end_date');
            $start_date = $request->input('start_date');
            $end_date = $request->input('end_date');
        }else{
            $start_date = "";
            $end_date = "";
        }

        $query = DB::table('purchases')
        ->select(
            'purchases.*',
            'departments.name AS department',
            'projects.name AS project'
        )
        ->leftJoin('projects', 'projects.id', '=', 'purchases.project_id')
        ->leftJoin('departments', 'departments.id', '=', 'purchases.department_id')
        ->when(!empty($data['product_name']), function ($query) use ($data) {
            return $query->leftJoin('purchase_items', 'purchase_items.purchase_id', '=', 'purchases.id')
                ->leftJoin('master_item_products', 'master_item_products.id', '=', 'purchase_items.product_id')
                ->where('master_item_products.name','ilike', '%' . $data['product_name'] . '%');
        })
        ->when(!empty($data['pr_no']), function ($query) use ($data) {
            return $query->leftJoin('purchase_requisitions', 'purchase_requisitions.purchase_id', '=', 'purchases.id')
                ->where('purchase_requisitions.doc_no','ilike', '%' . $data['pr_no'] . '%');
        })
        ->when(!empty($data['description']), function ($query) use ($data) {
            return $query->where('purchases.description','ilike', '%' . $data['description'] . '%');
        })
        ->when(!empty($data['type_dpm']), function ($query) use ($data) {
            return $query->where('purchases.type',$data['type_dpm']);
        })
        ->when(!empty($data['location_id']), function ($query) use ($data) {
            return $query->where('purchases.location_id',$data['location_id']);
        })
        ->when(!empty($data['start_date']), function ($query) use ($data) {
            if($data['end_date']){
                $start = date("Y-m-d",strtotime($data['start_date']));
                $end   = date("Y-m-d",strtotime($data['end_date']."+1 day"));
                return $query->whereBetween('purchases.created_at', [$start , $end]);
            }else{
                return $query->where('purchases.created_at', $data['start_date']);
            }
        })
        ->orderBy('purchases.created_at','DESC')
        ->paginate(25);

        $pr = $query->appends(array(
            'product_name' => $product_name,
            'description' => $description,
            'type_dpm' => $type_dpm,
            'pr_no' => $pr_no,
            'location_id' => $location_id,
            'start_date' => $start_date,
            'end_date' => $end_date,
        ));

        if(isAdministrator() || isAdmin()){
            $location    = DB::table('locations')
                ->selectRaw("CONCAT (locations.name,' - ', companies.alias) as name, locations.id")
                ->leftjoin('companies','companies.id','=','locations.company_id')
                ->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
            $department  = DB::table('departments')
                ->selectRaw("CONCAT (departments.name,' - ', companies.alias) as name, departments.id")
                ->leftjoin('companies','companies.id','=','departments.company_id')
                ->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
        }elseif(isAdministratorCompany()){
            $location   = DB::table('locations')->where('company_id', Auth::user()->company_id)->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
            $department = DB::table('departments')->where('company_id', Auth::user()->company_id)->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
        }elseif(isAdministratorLocation()){
            $location   = DB::table('locations')->where('id', Auth::user()->location_id)->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
            $department = DB::table('departments')->where('company_id', Auth::user()->company_id)->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
        }else{
            $location = DB::table('locations')->where('id', Auth::user()->location_id)->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
            $department = DB::table('departments')->where('company_id', Auth::user()->company_id)->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
        }
        $type = array(
            "im" => "IM",
            "petty_cash" => "Petty Cash",
            "po" => "PO",
        );
        return view('logistic.monitoring.dpm.search', compact('pr','location', 'search','start_date','end_date','type','type_dpm'));
    }


    public function export(Request $request)
    {
        $date = date('Y-m-d');
        return Excel::download(new MonitoringExport($request->get('location_id'), $request->get('department_id'), $request->get('start_date'), $request->get('end_date')), 'Report-Monitoring-DPM-'.$date.'.xlsx');
    }

    public function datatables_logistic(Request $request)
    {
        $data = $request->all();
        $result = DB::table('purchase_items')
        ->select(
            'purchases.id AS id',
            'purchases.doc_no AS doc_no',
            'departments.name AS kd',
            'purchases.created_at AS created_at',
            'purchases.created_by AS created_by',
            'users.name AS created'
        )
        ->leftJoin('po_items', 'po_items.pr_item_id', '=', 'purchase_items.id')
        ->leftJoin('po', 'po_items.po_id', '=', 'po.id')
        ->rightJoin('purchases', 'purchases.id', '=', 'purchase_items.purchase_id')
        ->leftJoin('purchase_requisitions', 'purchase_requisitions.id', '=', 'purchase_items.pr_id')
        ->leftJoin('locations', 'locations.id', '=', 'purchases.location_id')
        ->leftJoin('users', 'users.id', '=', 'purchases.created_by')
        ->leftJoin('departments', 'departments.id', '=', 'purchases.department_id')
        ->leftJoin('projects', 'projects.id', '=', 'purchases.project_id')
        ->leftJoin('lpb_items', 'lpb_items.pr_item_id', '=', 'purchase_items.id')
        ->leftJoin('lpb', 'lpb_items.lpb_id', '=', 'lpb.id')
        ->leftJoin('spb_kolis', 'spb_kolis.pr_item_id', '=', 'purchase_items.id')
        ->leftJoin('spb', 'spb_kolis.spb_id', '=', 'spb.id')
        ->leftJoin('bpb_items', 'bpb_items.pr_item_id', '=', 'purchase_items.id')
        ->leftJoin('bpb', 'bpb_items.bpb_id', '=', 'bpb.id')
        ->whereNotIn('purchases.status', [2]) // Reject, Cancel
        ->where('purchases.type', 'po') //type dpm
        ->whereNotIn('purchase_items.status',[2,3])
        ->whereNotIn('purchase_items.po_status',[3,4])
        ->where(function ($query) {
            $query->whereNull('purchase_requisitions.status')
                  ->orWhereNotIn('purchase_requisitions.status', [5,6]);
        })
        ->where(function ($query) {
            $query->whereNull('po.status')
                  ->orWhereNotIn('po.status', [6,8]);
        })
        ->where(function ($query) {
            $query->whereNull('lpb.status')
                  ->orWhereNotIn('lpb.status', [3,4]);
        })
        ->where(function ($query) {
            $query->whereNull('spb.status')
                  ->orWhere('spb.status', '!=', 4);
        })
        ->whereNull('bpb.id')
        ->when(Gate::allows('admin_dpm'), function ($query) {
            return $query->where('purchases.created_by', Auth::user()->id);
        })
        ->where('purchases.created_at', '>=', '2024-01-01')
        ->groupBy('purchases.id', 'purchases.doc_no', 'departments.name', 'purchases.created_at', 'purchases.created_by', 'users.name');

        return DataTables::of($result)
        ->addColumn('action', function ($result) {
            return "<a target='_blank' href='" . route('logistic.monitoring.dpm.detail', ['id' => Hashids::encode($result->id)]) . "' title='" . trans('app.show_title') . "' data-toggle='tooltip' class='btn btn-outline'><span class='ti-eye icon-lg'></span></a>";
        })
        ->editColumn('doc_no', function ($result) {
            if(strtotime($result->created_at)<=strtotime('-720 hours')){
                return "<a title='".$result->doc_no." sudah lebih dari 30 hari'>".$result->doc_no." <i class='ti-alert icon-lg text-danger' style='font-weight:bold;'></i></a>";
            }
            elseif(strtotime($result->created_at)<=strtotime('-336 hours')){
                return "<a title='".$result->doc_no." sudah lebih dari 14 hari'>".$result->doc_no." <i class='ti-alert icon-lg text-warning' style='font-weight:bold;'></i></a>";
            }
            else{
                return $result->doc_no;
            }
        })
        ->editColumn('created_at', function ($result) {
            return date('[d-M-Y H:i]', strtotime($result->created_at));
        })
        ->rawColumns(['action', 'created_at', 'doc_no'])
        ->make(true);
    }

    public function index_pending()
    {
        return view('logistic.monitoring.dpm.index_pending');
    }

    public function datatables_pending()
    {

        $result = DB::table('purchase_items')
        ->select(
            'purchases.id AS id',
            'purchases.doc_no AS doc_no',
            'departments.name AS kd',
            'purchases.created_at AS created_at',
            'purchases.created_by AS created_by',
            'users.name AS created',
            'projects.name AS project'
        )
        ->leftJoin('po_items', 'po_items.pr_item_id', '=', 'purchase_items.id')
        ->leftJoin('po', 'po_items.po_id', '=', 'po.id')
        ->rightJoin('purchases', 'purchases.id', '=', 'purchase_items.purchase_id')
        ->leftJoin('purchase_requisitions', 'purchase_requisitions.id', '=', 'purchase_items.pr_id')
        ->leftJoin('locations', 'locations.id', '=', 'purchases.location_id')
        ->leftJoin('users', 'users.id', '=', 'purchases.created_by')
        ->leftJoin('departments', 'departments.id', '=', 'purchases.department_id')
        ->leftJoin('projects', 'projects.id', '=', 'purchases.project_id')
        ->leftJoin('lpb_items', 'lpb_items.pr_item_id', '=', 'purchase_items.id')
        ->leftJoin('lpb', 'lpb_items.lpb_id', '=', 'lpb.id')
        ->leftJoin('spb_kolis', 'spb_kolis.pr_item_id', '=', 'purchase_items.id')
        ->leftJoin('spb', 'spb_kolis.spb_id', '=', 'spb.id')
        ->leftJoin('bpb_items', 'bpb_items.pr_item_id', '=', 'purchase_items.id')
        ->leftJoin('bpb', 'bpb_items.bpb_id', '=', 'bpb.id')
        ->whereNotIn('purchases.status', [2]) // Reject, Cancel
        ->where('purchases.type', 'po') //type dpm
        ->whereNotIn('purchase_items.status',[2,3])
        ->whereNotIn('purchase_items.po_status',[3,4])
        ->where(function ($query) {
            $query->whereNull('purchase_requisitions.status')
                  ->orWhereNotIn('purchase_requisitions.status', [5,6]);
        })
        ->where(function ($query) {
            $query->whereNull('po.status')
                  ->orWhereNotIn('po.status', [6,8]);
        })
        ->where(function ($query) {
            $query->whereNull('lpb.status')
                  ->orWhereNotIn('lpb.status', [3,4]);
        })
        ->where(function ($query) {
            $query->whereNull('spb.status')
                  ->orWhere('spb.status', '!=', 4);
        })
        ->whereNull('bpb.id')
        ->when(Gate::allows('admin_dpm'), function ($query) {
            return $query->where('purchases.created_by', Auth::user()->id);
        })
        ->where('purchases.created_at', '>=', '2024-01-01')
        ->groupBy('purchases.id',
            'purchases.doc_no',
            'departments.name',
            'purchases.created_at',
            'purchases.created_by',
            'users.name',
            'projects.name');

        return DataTables::of($result)
        ->addColumn('action', function ($result) {
            return "<a target='_blank' href='" . route('logistic.monitoring.dpm.detail', ['id' => Hashids::encode($result->id)]) . "' title='" . trans('app.show_title') . "' data-toggle='tooltip' class='btn btn-outline'><span class='ti-eye icon-lg'></span></a>";
        })
        ->editColumn('doc_no', function ($result) {
            if(strtotime($result->created_at)<=strtotime('-720 hours')){
                return "<a title='".$result->doc_no." sudah lebih dari 30 hari'>".$result->doc_no." <i class='ti-alert icon-lg text-danger' style='font-weight:bold;'></i></a>";
            }
            elseif(strtotime($result->created_at)<=strtotime('-336 hours')){
                return "<a title='".$result->doc_no." sudah lebih dari 14 hari'>".$result->doc_no." <i class='ti-alert icon-lg text-warning' style='font-weight:bold;'></i></a>";
            }
            else{
                return $result->doc_no;
            }
        })
        ->editColumn('created_at', function ($result) {
            return date('[d-M-Y H:i]', strtotime($result->created_at));
        })
        ->editColumn('created', function ($result) {
            return $result->created;
        })
        ->editColumn('project', function ($result) {
            return $result->project?$result->project:'-';
        })
        ->rawColumns(['action', 'created_at', 'doc_no','created_by','project'])
        ->make(true);
    }

    public function getDPMPending(){
        $query = DB::table('purchases')
        ->select('purchases.doc_no','purchases.created_by','purchases.created_at','purchases.status','users.name AS name','purchases.id AS idDpm')
        ->leftJoin('users','users.id','=','purchases.created_by')
        ->whereIn('purchases.status', [1,3,11])
        ->when(GATE::allows('admin_dpm'), function ($query) {
            return $query->where('purchases.created_by', Auth::user()->id);
        })
        ->whereExists(function ($query) {
            $query->select(DB::raw(1))
            ->from('purchase_items')
            ->whereColumn('purchase_items.purchase_id', 'purchases.id')
            ->where('purchase_items.pr_status', 0)
            ->where('purchase_items.status', 1);
        })
        ->where('purchases.created_at', '>=', '2024-01-01')
        ->where('purchases.type','=','po');
        return DataTables::of($query)
        ->editColumn('created_by', function($query){
            return $query->name;
        })
        ->editColumn('created_at', function ($query) {
            return date('[d-M-Y H:i]', strtotime($query->created_at));
        })
        ->editColumn('status',function ($query){
            return getStatusDPM($query->status);
        })
        ->editColumn('doc_no', function ($query) {
            $doc_no = "<a target='_blank' href='".route('logistic.monitoring.dpm.detail', ['id' => Hashids::encode($query->idDpm)])."' title='Detail DPM' data-toggle='tooltip' >".$query->doc_no."</a>";
            return $doc_no;
        })
        ->rawColumns(['created_by','created_at','status','doc_no'])
        ->make(true);
    }

    public function getPRPending(){
        $query = DB::table('purchase_requisitions')
        ->select('purchase_requisitions.doc_no AS doc_no_pr',
        'purchases.doc_no AS doc_no_dpm',
        'purchases.created_by',
        'purchase_requisitions.created_at',
        'purchase_requisitions.status',
        'users.name',
        'purchases.id AS idDpm')
        ->leftJoin('purchases', 'purchases.id', '=', 'purchase_requisitions.purchase_id')
        ->leftJoin('users','users.id','=','purchases.created_by')
        ->where('purchases.type','=','po')
        ->whereNotIn('purchase_requisitions.status', [4,5,6]) //Done, Close
        ->when(GATE::allows('admin_dpm'), function ($query) {
            return $query->where('purchases.created_by', Auth::user()->id);
        })
        ->where('purchases.created_at', '>=', '2024-01-01');
        return DataTables::of($query)
        ->editColumn('created_by', function($query){
            return $query->name;
        })
        ->editColumn('created_at', function ($query) {
            return date('[d-M-Y H:i]', strtotime($query->created_at));
        })
        ->editColumn('status',function ($query){
            return getStatusPR($query->status);
        })
        ->editColumn('doc_no_dpm', function ($query) {
            $doc_no = "<a target='_blank' href='".route('logistic.monitoring.dpm.detail', ['id' => Hashids::encode($query->idDpm)])."' title='Detail DPM' data-toggle='tooltip' >".$query->doc_no_dpm."</a>";
            return $doc_no;
        })
        ->rawColumns(['created_by','created_at','status','doc_no_dpm'])
        ->make(true);
    }

    public function getPOLPending(){
        $query = DB::table('po')
        ->select('po.doc_no AS doc_no_po','purchases.doc_no AS doc_no_dpm','users.name AS name','po.created_at AS created_at','po.status AS status','purchases.id AS idDpm')
        ->leftJoin('purchase_requisitions','purchase_requisitions.id','=','po.purchase_id')
        ->leftJoin('purchases', 'purchases.id', '=', 'purchase_requisitions.purchase_id')
        ->leftJoin('users','users.id','=','purchases.created_by')
        ->when(GATE::allows('admin_dpm'), function ($query) {
            return $query->where('purchases.created_by', Auth::user()->id);
        })
        ->whereIn('po.status',[0,1,2,3,4,9,10]) //draft,onprog,poissued,perbaikan,lpb parsial
        ->where('po.type','=','non_lpb')
        ->where('purchases.type', 'po')
        ->where('purchases.created_at', '>=', '2024-01-01');
        return DataTables::of($query)
        ->editColumn('created_by', function($query){
            return $query->name;
        })
        ->editColumn('created_at', function ($query) {
            return date('[d-M-Y H:i]', strtotime($query->created_at));
        })
        ->editColumn('status',function ($query){
            return getStatusPO($query->status);
        })
        ->editColumn('doc_no_dpm', function ($query) {
            $doc_no = "<a target='_blank' href='".route('logistic.monitoring.dpm.detail', ['id' => Hashids::encode($query->idDpm)])."' title='Detail DPM' data-toggle='tooltip' >".$query->doc_no_dpm."</a>";
            return $doc_no;
        })
        ->rawColumns(['created_by','created_at','status','doc_no_dpm'])
        ->make(true);
    }

    public function getPOJPending(){
        $query = DB::table('po')
        ->select('po.doc_no AS doc_no_po','purchases.doc_no AS doc_no_dpm','users.name AS name','po.created_at AS created_at','po.status AS status','purchases.id AS idDpm')
        ->leftJoin('purchase_requisitions','purchase_requisitions.id','=','po.purchase_id')
        ->leftJoin('purchases', 'purchases.id', '=', 'purchase_requisitions.purchase_id')
        ->leftJoin('users','users.id','=','purchases.created_by')
        ->when(GATE::allows('admin_dpm'), function ($query) {
            return $query->where('purchases.created_by', Auth::user()->id);
        })
        ->whereIn('po.status',[0,1,2,3,4,9,10]) //draft,onprog,poissued,perbaikan,lpb parsial
        ->where('po.type','=','lpb')
        ->where('purchases.type', 'po')
        ->where('purchases.created_at', '>=', Auth::user()->id == 304 ? '2024-01-01' : '2024-01-01');
        return DataTables::of($query)
        ->editColumn('created_by', function($query){
            return $query->name;
        })
        ->editColumn('created_at', function ($query) {
            return date('[d-M-Y H:i]', strtotime($query->created_at));
        })
        ->editColumn('status',function ($query){
            return getStatusPO($query->status);
        })
        ->editColumn('doc_no_dpm', function ($query) {
            $doc_no = "<a target='_blank' href='".route('logistic.monitoring.dpm.detail', ['id' => Hashids::encode($query->idDpm)])."' title='Detail DPM' data-toggle='tooltip' >".$query->doc_no_dpm."</a>";
            return $doc_no;
        })
        ->rawColumns(['created_by','created_at','status','doc_no_dpm'])
        ->make(true);
    }

    public function getLPBPending(){
        $query = DB::table('lpb')
        ->select('lpb.doc_no AS doc_no_lpb','purchases.doc_no AS doc_no_dpm','users.name AS name','lpb.created_at AS created_at','lpb.status AS status','lpb.spb_status AS spb_status','purchases.id AS idDpm')
        ->leftJoin('po','po.id','=','lpb.po_id')
        ->leftJoin('purchase_requisitions','purchase_requisitions.id','=','po.purchase_id')
        ->leftJoin('purchases', 'purchases.id', '=', 'purchase_requisitions.purchase_id')
        ->leftJoin('users','users.id','=','purchases.created_by')
        ->when(GATE::allows('admin_dpm'), function ($query) {
            return $query->where('purchases.created_by', Auth::user()->id);
        })
        ->whereIn('lpb.status', [0,1,2])
        ->whereIn('lpb.spb_status', [0,2])
        ->where('purchases.type', 'po')
        ->where('purchases.created_at', '>=', Auth::user()->id == 304 ? '2024-01-01' : '2024-01-01');
        return DataTables::of($query)
        ->editColumn('created_by', function($query){
            return $query->name;
        })
        ->editColumn('created_at', function ($query) {
            return date('[d-M-Y H:i]', strtotime($query->created_at));
        })
        ->editColumn('status',function ($query){
            return getStatusLPB($query->status,$query->spb_status);
        })
        ->editColumn('doc_no_dpm', function ($query) {
            $doc_no = "<a target='_blank' href='".route('logistic.monitoring.dpm.detail', ['id' => Hashids::encode($query->idDpm)])."' title='Detail DPM' data-toggle='tooltip' >".$query->doc_no_dpm."</a>";
            return $doc_no;
        })
        ->rawColumns(['created_by','created_at','status','doc_no_dpm'])
        ->make(true);
    }

    public function getSPBPending(){
        $query = DB::table('purchase_items')
        ->selectRaw('
            spb.doc_no AS doc_no_spb,
            STRING_AGG(DISTINCT CONCAT(purchases.doc_no, \' [ \', users.name, \']\'), E\'\n\') AS doc_no_dpm,
            STRING_AGG(DISTINCT CONCAT(purchases.doc_no, \' [ \', users.name, \']\'), E\'\n\') AS name,
            spb.created_at AS created_at,
            spb.status AS status
        ')
        ->rightJoin('po_items', 'po_items.pr_item_id', '=', 'purchase_items.id')
        ->rightJoin('po', 'po.id', '=', 'po_items.po_id')
        ->rightJoin('purchases', 'purchases.id', '=', 'purchase_items.purchase_id')
        ->leftJoin('users', 'users.id', '=', 'purchases.created_by')
        ->rightJoin('purchase_requisitions', 'purchase_requisitions.id', '=', 'purchase_items.pr_id')
        ->rightJoin('lpb_items', 'lpb_items.pr_item_id', '=', 'purchase_items.id')
        ->rightJoin('lpb', 'lpb.id', '=', 'lpb_items.lpb_id')
        ->rightJoin('spb_kolis', 'spb_kolis.pr_item_id', '=', 'purchase_items.id')
        ->rightJoin('spb', 'spb.id', '=', 'spb_kolis.spb_id')
        ->whereIn('spb.status', [0, 1, 2])
        ->where('purchases.type', 'po')
        ->where('purchases.created_at', '>=', '2024-01-01')
        ->when(Gate::allows('admin_dpm'), function ($query) {
            return $query->where('purchases.created_by', Auth::user()->id);
        })
        ->groupBy('spb.doc_no', 'spb.status', 'spb.created_at');
        return DataTables::of($query)
            ->editColumn('created_at', function ($query) {
                return date('[d-M-Y H:i]', strtotime($query->created_at));
            })
            ->editColumn('doc_no_dpm', function($query) {
                $formatted = str_replace("\n", "<br><hr>", e($query->doc_no_dpm));
                $formatted = preg_replace('/\[([^\]]+)\]/', '<div style="float: right; ">[$1]</div>', $formatted);
                return $formatted;
            })
            ->editColumn('status', function ($query){
                return getStatusSPB($query->status);
            })
            ->rawColumns(['created_at', 'status', 'doc_no_dpm'])
            ->make(true);
    }

    public function export_lpb_pending(Request $request){
        $data = $request->all();
        $query = DB::table('lpb')
            ->select(
            'lpb.doc_no AS no_lpb',
            'lpb.created_at AS created',
            'lpb.received_by AS received',
            'purchases.doc_no AS no_dpm',
            'master_item_products.name AS product',
            'master_item_products.part_number AS pn',
            'lpb_items.qty AS qty',
            'departments.name AS dept',
            'locations.name AS loc',
            'areas.name AS area',
            'po.doc_no AS no_po',
            'master_item_products.code AS code',
            'po_items.measure AS measure'
            )
            ->leftJoin('po','po.id','=','lpb.po_id')
            ->leftJoin('purchase_requisitions','purchase_requisitions.id','=','po.purchase_id')
            ->leftJoin('purchases', 'purchases.id', '=', 'purchase_requisitions.purchase_id')
            ->leftJoin('locations', 'locations.id', '=', 'purchases.location_id')
            ->leftJoin('departments', 'departments.id', '=', 'purchases.department_id')
            ->leftJoin('areas','areas.id','=','locations.area_id')
            ->leftJoin('users','users.id','=','purchases.created_by')
            ->leftJoin('lpb_items','lpb.id','=','lpb_items.lpb_id')
            ->leftJoin('po_items','po_items.id','=','lpb_items.po_item_id')
            ->leftJoin('master_item_products','master_item_products.id','=','lpb_items.product_id')
            ->when(GATE::allows('admin_dpm'), function ($query) {
                return $query->where('purchases.created_by', Auth::user()->id);
            })
            ->whereIn('lpb.status', [0, 1, 2])
            ->whereIn('lpb.spb_status', [0,2])
            ->where('purchases.created_at', '>=', Auth::user()->id == 304 ? '2024-01-01' : '2024-01-01')
            ->groupBy(
                'lpb.doc_no',
                'lpb.created_at',
                'lpb.received_by',
                'purchases.doc_no',
                'master_item_products.name',
                'master_item_products.part_number',
                'lpb_items.qty',
                'departments.name',
                'locations.name',
                'areas.name',
                'po.doc_no',
                'master_item_products.code',
                'po_items.measure',
                'po.created_at'
            )
            ->orderBy('lpb.created_at', 'DESC')
            ->get();
            if ($query->isEmpty()) {
                return redirect()->back()->with('error', 'Tidak terdapat data untuk di Export');
            }else{
            $header_style = (new Style())->setFontBold()->setBackgroundColor("F2F2F2");
            $rows_style = (new Style())
            ->setFontSize(10)
            ->setShouldWrapText(false);
            return (new FastExcel($query))->headerStyle($header_style)
            ->rowsStyle($rows_style)
            ->sheet('DATA LPB', function ($sheet) {
                $sheet->getDelegate()->getColumnDimension('A')->setWidth(500);
            })
            ->download('Report-Item-LPB-'.date('d-m-Y H-i').'.xlsx', function ($data) {
                return [
                    'No LPB'            => $data->no_lpb,
                    'No PO'             => $data->no_po,
                    'No DPM'            => $data->no_dpm,
                    'Area'              => $data->area,
                    'Location'          => $data->loc,
                    'Department'        => $data->dept,
                    'Code'              => $data->code,
                    'Product'           => $data->product,
                    'Part Number'       => $data->pn,
                    'QTY'               => (float)$data->qty,
                    'Satuan'            => $data->measure,
                    'Tanggal Input LPB' => $data->created,
                    'Penerima'          => $data->received,
                ];
            });
        }
    }

    public function datatables_lpb_dashboard(Request $request)
    {
        $data = $request->all();
        $result = DB::table('lpb_items')
            ->select(
                DB::raw('SUM(lpb_items.qty) AS in'),
                DB::raw('SUM(CASE WHEN lpb.spb_status = 1 THEN lpb_items.qty ELSE 0 END) AS out'),
                DB::raw('SUM(CASE WHEN lpb.spb_status = 0 THEN lpb_items.qty ELSE 0 END) AS soh'),
                'master_item_products.name AS product',
                'master_item_products.id AS product_id',
                'master_item_products.part_number AS part_number',
                'master_item_products.code AS code',
                'master_item_brands.name AS brand',
                'satuan_beli.name AS satuanBeli',
                'companies.name AS company',
                'companies.code AS companyCode',
                'companies.id AS company_id'
            )
            ->leftJoin('master_item_products', 'master_item_products.id', '=', 'lpb_items.product_id')
            ->join('lpb', 'lpb.id', '=', 'lpb_items.lpb_id')
            ->leftJoin('master_item_brands', 'master_item_brands.id', '=', 'master_item_products.brand_id')
            ->leftJoin('measures AS satuan_beli', 'satuan_beli.id', '=', 'master_item_products.measure_id')
            ->leftJoin('locations', 'locations.id', '=', 'lpb.location_id')
            ->leftJoin('companies', 'companies.id', '=', 'locations.company_id')
            ->whereIn('lpb.status', [0, 1, 2])
            ->groupBy(
                'master_item_products.name',
                'master_item_products.id',
                'master_item_products.part_number',
                'master_item_brands.name',
                'master_item_products.code',
                'satuan_beli.name',
                'companies.name',
                'companies.code',
                'companies.id'
            )
            ->having(DB::raw('SUM(CASE WHEN lpb.spb_status = 0 THEN lpb_items.qty ELSE 0 END)'), '>', 0) // Use the same expression
            ->orderBy('master_item_products.name', 'ASC');
        return DataTables::of($result)
        ->editColumn('product', function ($result) {
            return '<div style="font-size: 8pt;">' .
                       '[' . $result->code . '] ' . $result->product .
                       '<br><small>' .
                       ($result->part_number ? ('PN: ' . $result->part_number) : ('PN: -')) .
                       '<br>' .
                       ($result->brand ? ('Brand: ' . $result->brand) : ('Brand: -')) .
                       '</small>' .
                   '</div>';
        })
        ->editColumn('soh', function ($result) {
            if (floor($result->soh) == $result->soh) {
                $formattedSoh = number_format($result->soh, 0, ',', '.'); // e.g., 12.000
            } else {
                $formattedSoh = number_format($result->soh, 2, ',', '.'); // e.g., 12.000,00
            }
            return '<div style="text-align: left; font-size: 8pt;">
                        ' . $formattedSoh . ' <small>' . $result->satuanBeli . '</small>
                    </div>';
        })
        ->editColumn('companyCode', function ($result) {
            return '<div style="text-align: center; font-size: 8pt;">' . $result->companyCode . '</div>';
        })
        ->addColumn('action', function ($result) {
            $url_show = '<div style="text-align:center"><a class="btn btnShow" href="#" data-product_id="'.$result->product_id.'" data-product_info="'.$result->product.' - '.$result->company.'" data-company_id="'.$result->company_id.'" type="button" data-toggle="modal" data-target="#modalShow"><span class="ti-eye icon-lg"></span></a></div>';
            return '<div style="text-align: center;">'.$url_show.'</div>';
        })
        ->rawColumns(['product', 'soh', 'companyCode','action'])
        ->make(true);
    }
}
