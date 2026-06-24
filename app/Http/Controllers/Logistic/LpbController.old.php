<?php

namespace App\Http\Controllers\Logistic;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseOrderHistory;
use App\Models\Lpb;
use App\Models\LpbItem;
use App\Models\LpbHistory;
use App\Models\Workarea;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Vinkla\Hashids\Facades\Hashids;
use App\Models\Notification;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Auth;

use Rap2hpoutre\FastExcel\FastExcel;

class LpbController extends Controller
{

    /**
     * Display a listing of User.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (! Gate::allows('lpb')) {
            return abort(401);
        }
      
        if(isAdministrator() || isAdmin() ){
            $location    = DB::table('locations')
            ->selectRaw("CONCAT (locations.name,' - ', companies.alias) as name, locations.id")
            ->leftjoin('companies','companies.id','=','locations.company_id')
            ->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
        }elseif(isAdministratorCompany()){
            $location = DB::table('locations')->where('company_id', Auth::user()->company_id)->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
        }elseif(isAdministratorLocation()){
            $location = DB::table('locations')->where('id', Auth::user()->location_id)->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
        }else{
            $location = DB::table('locations')->where('id', Auth::user()->location_id)->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
        }

        return view('logistic.lpb.index',compact('location'));
    }

    public function datatables(Request $request)
    {
        if (! Gate::allows('lpb')) {
            return abort(401);
        }

        $data = $request->all();

        if(isAdministrator() || isAdmin() ) $result  = Lpb::getData($data);
        else $result = Lpb::getData($data, Auth::user()->id);

       return  DataTables::of($result)
        ->addColumn('action', function ($result) {
            $url_edit = "<a href='".route('logistic.lpb.edit',Hashids::encode($result->id))."' title='".trans('Edit')."' data-toggle='tooltip' class='btn btn-outline'><span class='ti-pencil icon-lg'></span> </a>";  
            $url_view = "<a href='".route('logistic.lpb.show', Hashids::encode($result->id))."' title='".trans('app.show_title')."' data-toggle='tooltip' class='btn btn-outline'><span class='ti-eye icon-lg'></span> </a>";  
            $url_delete = "<form class='delete' action='".route('logistic.lpb.delete', ['id' => $result->id])."' method='POST'>
                                ".csrf_field()."
                                <button class='btn btn-outline text-danger' title='".trans('app.delete_title')."' data-toggle='tooltip'><i class='ti-trash icon-lg'></i></button>
                            </form>";
            $url_rollback = "<form class='delete' action='".route('logistic.lpb.delete', ['id' => $result->id])."' method='POST'>
                            ".csrf_field()."
                            <button class='btn btn-outline text-danger' title='Kembalikan ke PO' data-toggle='tooltip'><i class='ti-back-left icon-lg'></i></button>
                        </form>";
            if($result->status==0){
                return '<div class="btn-group">'.$url_edit .$url_view .$url_delete.'</div>';
            }else{
                if($result->spb_status==0){
                    return '<div class="btn-group">'.$url_view.$url_rollback.'</div>';
                }else{
                    return '<div class="btn-group">'.$url_view.'</div>';
                }
            }
        })
        ->editColumn('status', function ($result) {
            return getStatusData($result->status);
        })
       
        ->rawColumns(['action', 'status'])
        ->make(true);

    }

    /**
     * Show the form for creating new User.
     *
     * @return \Illuminate\Http\Response
     */
    public function create( Request $request)
    {
        
        $id = Hashids::decode($request->id);

        $po_items = PurchaseOrder::getProductItemLPB($id['0']);
        $po       = PurchaseOrder::getByID($id['0']);

        $location = DB::table('locations')
        ->select('locations.*', 'companies.alias AS companyCode', 'companies.id AS companyID')
        ->leftJoin('companies', 'companies.id', '=', 'locations.company_id')
        ->where('locations.id', $po->locationID)
        ->first();
        return view('logistic.lpb.create', compact('po','po_items','location'));
    }

    /**
     * Store a newly created User in storage.
     *
     * @param  \App\Http\Requests\StoreUsersRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (! Gate::allows('lpb')) {
            return abort(401);
        }


        if ($request->get('status')==1) {
            $increment = DB::table('lpb')
                ->whereYear("publish", date('Y'))
                ->where('location_id', $request->get('location_id'))
                ->where('status','!=', 0)
                ->get();

            $num = sprintf("%'.05d", count($increment) + 1) ;

            $no_lpb = "LPB-". $request->get('companyCode')."-". $request->get('locationCode')."-".date('my')."-".$num;
            $data['status']    = 1;
            $data['publish']   = date('Y-m-d');

        }else{
            $no_lpb = "LPB-". $request->get('companyCode')."-". $request->get('locationCode')."-".date('my')."-DRAFT";
            $data['status']        = 0;
            $dataHistory['jenis']  = 'draft';
        }

        $data['received_by']    = $request->get('received_by');
        $data['doc_no']         = $no_lpb;
        $data['po_id']          = $request->get('po_id');
        $data['created_by']     = Auth::user()->id;
        $data['location_id']    = $request->get('location_id');
       
        DB::beginTransaction();

        try {

            $lpb = Lpb::create($data);

            $dataLPB = [];
            $cases  = [];
            $parsial = [];
            $parsialItem = [];
            $qty_parsial = [];
            $ids = [];

            $product = $request->get('product_id');

            for($i=0;$i < count($product);$i++) {
            
                if(in_array($request->get('po_item_id')[$i], $request->get('iscreateLPB'))){
                    $dataLPB[] = [
                        'lpb_id'        => $lpb->id,
                        'po_item_id'    => $request->get('po_item_id')[$i],
                        'pr_item_id'    => $request->get('pr_item_id')[$i],
                        'product_id'    => $request->get('product_id')[$i],
                        'qty'           => $request->get('qty')[$i],
                        'notes'         => $request->get('notes')[$i],
                    ];
                    
                    $ids[]    = $request->get('po_item_id')[$i];
                    if($request->get('qty')[$i] == $request->get('qty_po')[$i]){
                        $cases[]        = "WHEN id = {$request->get('po_item_id')[$i]} THEN 1";
                        $qty_parsial [] = "WHEN id = {$request->get('po_item_id')[$i]} THEN 0";
                        $parsial[]      = '1';
                    }else{
                        $qtyParsial     = $request->get('qty_po')[$i] - $request->get('qty')[$i];
                        $cases[]        = "WHEN id = {$request->get('po_item_id')[$i]} THEN 2";
                        $qty_parsial [] = "WHEN id = {$request->get('po_item_id')[$i]} THEN ".$qtyParsial;
                        $parsial[]      = '2';
                    }
                    $parsialItem[]      = '1';
                }else{
                    $parsialItem[]      = '2';
                }

            }

            $ids        = implode(',', $ids);
            $cases      = implode(' ', $cases);
            $qty_parsial= implode(' ', $qty_parsial);

            \DB::update("UPDATE po_items SET lpb_status  = CASE {$cases} END, qty_parsial = CASE {$qty_parsial} END WHERE id in ({$ids})");

            if(in_array('2',$parsial) || in_array('2',$parsialItem)) {
                $dataPO['status'] = '4';
            }else{
                $dataPO['status'] = '5';
            }
            
            $po = PurchaseOrder::findOrFail($request->get('po_id'));
            $po->update($dataPO);

            $lpb_items = LpbItem::insert($dataLPB);

            $dataHistory['lpb_id'] = $lpb->id;
            $dataHistory['user_id'] = Auth::user()->id;
            $po_history = LpbHistory::create($dataHistory);

            DB::commit();
            return redirect()->route('logistic.lpb.show',Hashids::encode($lpb->id))->with(['success' => 'Pembuatan LPB Berhasil!']);
      
        } catch (\Exception $e) {

            DB::rollback();
            return redirect()->back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }


    public function show($id)
    { 
        if (! Gate::allows('lpb')) {
            return abort(401);
        }
        $id = Hashids::decode($id);

        $lpb         = Lpb::getByID($id['0']);
        $lpb_items   = Lpb::getProductItem($id['0']);
        $lpb_history = Lpb::getHistory($id['0']);
     
        return view('logistic.lpb.show', compact('lpb', 'lpb_items','lpb_history'));
    }

 
    public function edit($id)
    {
        if (! Gate::allows('lpb')) {
            return abort(401);
        }

        $id = Hashids::decode($id);

        $lpb_items  = Lpb::getProductItem($id['0']);
        $lpb        = Lpb::getByID($id['0']);
        
        $location = DB::table('locations')
        ->select('locations.*', 'companies.alias AS companyCode', 'companies.id AS companyID')
        ->leftJoin('companies', 'companies.id', '=', 'locations.company_id')
        ->where('locations.id', $lpb->locationID)
        ->first();

        return view('logistic.lpb.edit', compact('lpb','lpb_items','location'));
    }


    public function update(Request $request, $id)
    {
        if (! Gate::allows('lpb')) {
            return abort(401);
        }

        $lpb = Lpb::findOrFail($id);

        $data = $request->all();

        if ($request->get('status')==1) {
            $increment = DB::table('lpb')
                ->whereYear("publish", date('Y'))
                ->where('location_id', $request->get('location_id'))
                ->where('status','!=', 0)
                ->get();

            $num = sprintf("%'.05d", count($increment) + 1) ;
           
            $doc_no     = explode('-',$lpb->doc_no);
            $location   = $doc_no['2'];
            $company    = $doc_no['1'];
            $no_lpb = "LPB-".$company."-".$location."-".date('my')."-".$num;

            $data['doc_no']    = $no_lpb;
            $data['status']    = 1;
            $data['publish']   = date('Y-m-d');
        }else{
            $data['status']        = 0;
            $dataHistory['jenis']  = 'draft';
        }

        $data['received_by']    = $request->get('received_by');
        $data['po_id']          = $request->get('po_id');
        $data['updated_by']     = Auth::user()->id;

       $lpb->update($data);

       $lpb_items_delete = LpbItem::where('lpb_id',$id)->delete();

        $dataLPB = [];
        $cases  = [];
        $parsial = [];
        $parsialItem = [];
        $qty_parsial = [];
        $ids = [];


        $product = $request->get('product_id');

        for($i=0;$i < count($product);$i++) {
            $dataLPB[] = [
                'lpb_id'        => $lpb->id,
                'po_item_id'    => $request->get('po_item_id')[$i],
                'pr_item_id'    => $request->get('pr_item_id')[$i],
                'product_id'    => $request->get('product_id')[$i],
                'qty'           => $request->get('qty')[$i],
                'notes'         => $request->get('notes')[$i],
            ];
            
            $ids[]    = $request->get('po_item_id')[$i];

            if($request->get('qty')[$i] == $request->get('qty_po')[$i]){
                $cases[]        = "WHEN id = {$request->get('po_item_id')[$i]} THEN 1";
                $qty_parsial [] = "WHEN id = {$request->get('po_item_id')[$i]} THEN 0";
                $parsial[]      = '1';
            }else{
                $qtyParsial     = $request->get('qty_po')[$i] - $request->get('qty')[$i];
                $cases[]        = "WHEN id = {$request->get('po_item_id')[$i]} THEN 2";
                $qty_parsial [] = "WHEN id = {$request->get('po_item_id')[$i]} THEN ".$qtyParsial;
                $parsial[]      = '2';
            }
               
        }

        $ids        = implode(',', $ids);
        $cases      = implode(' ', $cases);
        $qty_parsial= implode(' ', $qty_parsial);

        \DB::update("UPDATE po_items SET lpb_status  = CASE {$cases} END, qty_parsial = CASE {$qty_parsial} END WHERE id in ({$ids})");

        if(in_array('2',$parsial)) {
            $dataPO['status'] = '4';
        }else{
            $dataPO['status'] = '5';
        }
        $po = PurchaseOrder::findOrFail($request->get('po_id'));
        $po->update($dataPO);

        $po_items = LpbItem::insert($dataLPB);

        $dataHistory['lpb_id']   = $lpb->id;
        $dataHistory['user_id'] = Auth::user()->id;
        $lpb_history = LpbHistory::create($dataHistory);

        return redirect()->route('logistic.lpb.show', Hashids::encode($lpb->id))->with(['success' => 'Pembuatan LPB Berhasil!']);
        
    }

    public function delete(Request $request)
    {

        if (! Gate::allows('lpb')) {
            return abort(401);
        }
        $lpb  = Lpb::findOrFail($request->id);

        $dataPO['status'] = '2';
        $po = PurchaseOrder::findOrFail($lpb->po_id);
        $po->update($dataPO);

        $lpb_item = DB::table('lpb_items')
        ->select('lpb_items.*','po_items.qty_parsial AS qtyPOParsial','po_items.qty AS qtyPO')
        ->leftJoin('po_items', 'po_items.id', '=', 'lpb_items.po_item_id')
        ->where('lpb_items.lpb_id', $lpb->id)
        ->get();

       
        $ids = [];
        $dataLPB = [];
        foreach($lpb_item as $val){
            if($val->qtyPOParsial !=0){
                $qtyParsialNow =  $val->qtyPOParsial + $val->qty;
                if($qtyParsialNow == $val->qtyPO){
                    $lpb_status    = 0;
                    $qty_parsial   = 0;
                }else{
                    $qty_parsial   = $val->qtyPOParsial + $val->qty;
                    $lpb_status    = 2;
                }
            }else{
                if($val->qty == $val->qtyPO){
                    $lpb_status    = 0;
                    $qty_parsial   = 0;
                }else{
                    $qty_parsial = $val->qty;
                    $lpb_status  = 2 ;
                }
            }
            $dataLPB = array (
                'lpb_status'    => $lpb_status,
                'qty_parsial'   => $qty_parsial
            );
            DB::table('po_items')
            ->where('id', $val->po_item_id)
            ->update($dataLPB);
        }

        $lpb->delete();
        return redirect()->route('logistic.lpb.index')->with(['success' => 'Delete Data Berhasil!']);

    }


    public function search(Request $request)
    { 
        if (! Gate::allows('lpb')) {
            return abort(401);
        }
        
        $query = "location_id=".$request->get('location_id')."&start_date=".$request->get('start_date')."&end_date=". $request->get('end_date');
        $data = $request->all();
        $search = "Cari Berdasarkan: ";
  
        if($request->input('location_id')) $search .= "<strong> Lokasi: </strong>".getDataByID('locations',$request->input('location_id'))->name;
        $search .= "<strong> Periode: </strong>".$request->input('start_date'). " - ". $request->input('end_date');

        if(isAdministrator()){
            $location    = DB::table('locations')
            ->selectRaw("CONCAT (locations.name,' - ', companies.alias) as name, locations.id")
            ->leftjoin('companies','companies.id','=','locations.company_id')
            ->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
        }elseif(isAdministratorCompany()){
            $location = DB::table('locations')->where('company_id', Auth::user()->company_id)->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
        }elseif(isAdministratorLocation()){
            $location = DB::table('locations')->where('id', Auth::user()->location_id)->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
        }else{
            $location = DB::table('locations')->where('id', Auth::user()->location_id)->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
        }

        return view('logistic.lpb.search', compact('data','location', 'search','query'));
    }



    public function print($id, $type)
    { 
        
        $id = Hashids::decode($id);

        $lpb = Lpb::getByID($id);
        $lpb_items   = Lpb::getProductItem($id['0']);
        return view('logistic.lpb.print', compact('lpb', 'lpb_items'));
       
    }


    public function detail($id)
    { 
        if (! Gate::allows('lpb')) {
            return abort(401);
        }

        $lpb         = Lpb::getByID($id);
        $lpb_items   = Lpb::getProductItem($id);
        $lpb_history = Lpb::getHistory($id);
     
        return view('logistic.lpb.detail', compact('lpb', 'lpb_items','lpb_history'))->renderSections()['content'];
    }


    public function export(Request $request)
    {
        
        $data = $request->all();

        $query = DB::table('lpb')
        ->select('lpb.*',
        'po.doc_no as po_no',
        'purchase_requisitions.dpm_no AS dpm_no', 
        'purchase_requisitions.doc_no AS pr_no', 
        'departments.name AS department'
        )
        ->leftJoin('po', 'po.id', '=', 'lpb.po_id')
        ->leftJoin('purchase_requisitions', 'purchase_requisitions.id', '=', 'po.purchase_id')
        ->leftJoin('departments', 'departments.id', '=', 'purchase_requisitions.department_id')
        ->when(!empty($data['location_id']), function ($query) use ($data) {
            return $query->where('purchase_requisitions.location_id',$data['location_id']);
        })->when(!empty($data['start_date']), function ($query) use ($data) {
            if($data['end_date']){
                $start = date("Y-m-d",strtotime($data['start_date']));
                $end   = date("Y-m-d",strtotime($data['end_date']."+1 day"));
                return $query->whereBetween('lpb.created_at', [$start , $end]);
            }else{
                return $query->where('lpb.created_at', $data['start_date']);
            }
        })
        ->orderBy('lpb.updated_at','DESC')
        ->get();

        if( $query->isEmpty() ){
            return redirect()->route('logistic.lpb.index')->with(['error' => 'Tidak terdapat data untuk di Export']);
        }else{

            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="your_name.xls"');
            header('Cache-Control: max-age=0');
            return (new FastExcel($query))->download('REPORT-LPB-'.date('d-m-Y').'.xlsx', function ($data) {
                return [
                    'Nomor DPM'         => $data->dpm_no,
                    'Nomor PR'          => $data->pr_no,
                    'Nomor PO'          => $data->po_no,
                    'Nomor LPB'         => $data->doc_no,
                    'Department'        => $data->department,
                    'Penerima'          => $data->received_by,
                    'Tanggal Input'     => dateTextMySQL2ID($data->created_at),
                ];
            });
        }

    }


    public function list()
    {
        if (! Gate::allows('spb')) {
            return abort(401);
        }
        return view('logistic.lpb.list');
    }


    public function listDatatables()
    {
        if (! Gate::allows('spb')) {
            return abort(401);
        }

        if(isAdministratorCompany()){
            $result = DB::table('po')
            ->select('po.*', 'purchase_requisitions.doc_no AS pr_no', 'purchase_requisitions.dpm_no AS dpm_no', 'departments.name AS department')
            ->leftJoin('purchase_requisitions', 'po.purchase_id', '=', 'purchase_requisitions.id')
            ->leftJoin('locations', 'locations.id', '=', 'purchase_requisitions.location_id')
            ->leftJoin('departments', 'departments.id', '=', 'purchase_requisitions.department_id')
            ->where('locations.company_id', Auth::user()->company_id)
            ->whereIn('po.status', ['2','4'])
            ->where('po.type','lpb')
            ->orderBy('po.created_at', 'DESC') ;
        }elseif(isAdministratorLocation()){
            $result = DB::table('po')
            ->select('po.*', 'purchase_requisitions.doc_no AS pr_no', 'purchase_requisitions.dpm_no AS dpm_no', 'departments.name AS department')
            ->leftJoin('purchase_requisitions', 'po.purchase_id', '=', 'purchase_requisitions.id')
            ->leftJoin('departments', 'departments.id', '=', 'purchase_requisitions.department_id')
            ->where('purchase_requisitions.location_id', Auth::user()->location_id)
            ->whereIn('po.status', ['2','4'])
            ->where('po.type','lpb')
            ->orderBy('po.created_at', 'DESC') ;
        }
        else{
            $result = DB::table('po')
            ->select('po.*', 'purchase_requisitions.doc_no AS pr_no', 'purchase_requisitions.dpm_no AS dpm_no', 'departments.name AS department')
            ->leftJoin('purchase_requisitions', 'po.purchase_id', '=', 'purchase_requisitions.id')
            ->leftJoin('departments', 'departments.id', '=', 'purchase_requisitions.department_id')
            ->whereIn('po.status', ['2','4'])
            ->where('po.type','lpb')
            ->orderBy('po.created_at', 'DESC') ;
        }

        return  DataTables::of($result)
        ->addColumn('action', function ($result) {
            $url_add = "<a href='".route('logistic.lpb.create', ['id' => Hashids::encode($result->id)])."' data-toggle='tooltip' class='btn btn-danger btn-sm font-weight-bold'><span class='ti-file'></span> BUAT LPB </a>";  
            return '<div class="btn-group">'.$url_add.'</div>';
        })
        ->addColumn('status', function ($result) {
            return getStatusPOLPB($result->status);
        })
        ->editColumn('updated_at', function ($result) {
            return $result->updated_at ? with(new Carbon($result->updated_at))->format('d/m/Y') : '';
        })
        ->rawColumns(['action','status'])
        ->make(true);
    }


}
