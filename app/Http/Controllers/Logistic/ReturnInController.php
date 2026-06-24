<?php

namespace App\Http\Controllers\Logistic;

use App\Models\InventoryReturnIn;
use App\Models\InventoryReturnInItem;
use App\Models\InventoryReturnOut;
use App\Models\InventoryReturnOutItem;
use App\Models\InventoryHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Vinkla\Hashids\Facades\Hashids;
use Yajra\DataTables\Facades\DataTables;
use Rap2hpoutre\FastExcel\FastExcel;
use App\Exports\ReturnInExport;
use App\Traits\UploadTrait;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use App\Mail\SendMailable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;

use Storage;
use Auth;

class ReturnInController extends Controller
{

    function __construct()
    {
        $this->middleware('permission:return');
    }
    
    /**
     * Display a listing of Items.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
    
        if(isAdministrator()|| isAdmin() ){
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
        return view('logistic.return_in.index',compact('location'));
    }
    

    public function datatables(Request $request)
    {
        $data = $request->all();

        if(isAdministrator() || isAdmin() ) $result  = InventoryReturnIn::getData($data);
        elseif(isAdministratorCompany() ) $result  = InventoryReturnIn::getData($data, Auth::user()->company_id);
        else $result = InventoryReturnIn::getData($data,null,Auth::user()->location_id);
        
       return  DataTables::of($result)
        ->addColumn('action', function ($result) {
            $url = 'printExternal("/logistic/return_in_print/'.Hashids::encode($result->id).'")';
            $url_print = "<a onclick='".$url."' data-toggle='Print' class='btn btn-outline'><span class='ti-printer icon-lg'></span> </a>";  
            $url_show  = "<a href='".route('logistic.return_in.show', Hashids::encode($result->id))."'class='btn btn-outline'><span class='ti-eye icon-lg'></span> </a>";  
            $url_edit = "<a href='".route('logistic.return_in.edit', Hashids::encode($result->id))."' title='Edit' data-toggle='tooltip' class='btn btn-outline'><span class='ti-pencil icon-lg'></span> </a>";  
          
            $url_approval = "<a href='".route('logistic.return_in.approve', ['id' => Hashids::encode($result->id)])."' title='Approve' data-toggle='tooltip' class='btn btn-outline'><span class='ti-thumb-up icon-lg'></span> </a>";  
        
            if($result->status==0){
                return $url_edit.$url_show;
            }elseif($result->status==1 || $result->status==2) {
                return $url_show.$url_print.$url_approval;
            }else{
                return $url_show.$url_print;
            }
        })
        ->addColumn('status', function ($result) {
            return getStatusDataRIN($result->status);
        })
        ->editColumn('created_at', function ($result) {
            return $result->created_at ? with(new Carbon($result->created_at))->format('d/m/Y H:i:s') : '';
        })
        ->rawColumns(['action', 'status'])
        ->make(true);

    }


    public function list()
    {
       
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
        return view('logistic.return_in.list',compact('location'));
    }
    

    public function listDatatables()
    {
       
        if(isAdministratorLocation()){
            $location = Auth::user()->location_id;
        }elseif(isEmployee()){
            $location = Auth::user()->location_id;
        } else{
            $location = 0;
        }

        if(isAdministrator()){
            $result  = DB::table('inventory_return_out')
            ->select('inventory_return_out.*','users.name AS created')
            ->leftJoin('users', 'users.id', '=', 'inventory_return_out.created_by')
            ->whereIn('inventory_return_out.status', [1,2])
            ->orderBy('inventory_return_out.created_at', 'DESC');
        }elseif(isAdministratorCompany() ){
            $result  = DB::table('inventory_return_out')
            ->select('inventory_return_out.*','users.name AS created')
            ->leftJoin('locations', 'locations.id', '=', 'inventory_return_out.location_id')
            ->leftJoin('companies', 'companies.id', '=', 'locations.company_id')
            ->leftJoin('users', 'users.id', '=', 'inventory_return_out.created_by')
            ->where('companies.id', Auth::user()->company_id)
            ->whereIn('inventory_return_out.status', [1,2])
            ->orderBy('inventory_return_out.created_at', 'DESC');
        }elseif(isAdministratorLocation()){
            $result  = DB::table('inventory_return_out')
            ->select('inventory_return_out.*','users.name AS created')
            ->leftJoin('users', 'users.id', '=', 'inventory_return_out.created_by')
            ->where('inventory_return_out.location_id', Auth::user()->location_id)
            ->whereIn('inventory_return_out.status', [1,2])
            ->orderBy('inventory_return_out.created_at', 'DESC');
        }else{
            $result  = DB::table('inventory_return_out')
            ->select('inventory_return_out.*','users.name AS created')
            ->leftJoin('users', 'users.id', '=', 'inventory_return_out.created_by')
            ->where('inventory_return_out.location_id', Auth::user()->location_id)
            ->whereIn('inventory_return_out.status', [1,2])
            ->orderBy('inventory_return_out.created_at', 'DESC');
        }

       return  DataTables::of($result)
       ->addColumn('action', function ($result) {
            $vrl = "<a href='#' value='".action('Logistic\ReturnOutController@popup',['id'=>Hashids::encode($result->id)])."' title='Detail' data-toggle='modal' data-target='#modal' class='btn btn-success btn-sm font-weight-bold modalDoc'><span class='ti-eye icon-lg'></span> </a>";  
            $url_add = "<a href='".route('logistic.return_in.create', ['id' => Hashids::encode($result->id)])."' data-toggle='tooltip' class='btn btn-danger btn-sm font-weight-bold mr-1'><span class='ti-file'></span> BUAT RIN </a>";  
            return '<div>'.$url_add.$vrl.'</div>';
        })
        ->editColumn('created_at', function ($result) {
            return $result->created_at ? with(new Carbon($result->created_at))->format('d/m/Y H:i:s') : '';
        })
        ->rawColumns(['action', 'status'])
        ->make(true);

    }

    public function create(Request $request)
    {
        $id = Hashids::decode($request->id);

        $return_out       = InventoryReturnOut::getById($id['0']);
        $return_out_items = InventoryReturnOutItem::getByReturnOutId($return_out->id,'status');
   
        return view('logistic.return_in.create', compact('return_out','return_out_items'));
       
    }


    public function store(Request $request)
    {
       
        if ($request->get('status')==1) {
            $increment = DB::table('inventory_return_in')
            ->whereYear("publish", date('Y'))
            ->where('status', '!=', 0)
            ->where('location_id',$request->get('locationID'))
            ->count();
            $num = sprintf("%'.05d", $increment + 1) ;
            $no  = "RIN-". $request->get('companyCode')."-". $request->get('locationCode')."-".date('my')."-".$num;
            $data['status']  = 1;
            $data['publish'] = date('Y-m-d');

        }else{
            $no = "RIN-". $request->get('companyCode')."-". $request->get('locationCode')."-".date('my')."-DRAFT";
            $data['status'] = 0;
        }

        $data['operator']       = $request->get('operator');
        $data['location_id']    = $request->get('locationID');
        $data['inventory_return_out_id']    = $request->get('inventory_return_out_id');
        $data['doc_no']         = $no;
        $data['created_by']     = Auth::user()->id;   

        DB::beginTransaction();

        try {

            $return_in = InventoryReturnIn::create($data);

            $dataRTN = [];

            $cases  = [];
            $parsial = [];
            $ids = [];

            $product = $request->get('id');
            for($i=0;$i < count($product);$i++) {

                if (in_array($request->get('id')[$i], $request->get('iscreateRIN'))) {

                    $ids[]          = $request->get('id')[$i];
                    $cases[]        = "WHEN id = {$request->get('id')[$i]} THEN 1";
                    $dataRTN[] = [
                        'inventory_return_in_id'    => $return_in->id,
                        'inventory_id'              => $request->get('inv_id')[$i],
                        'inventory_return_item_id'  => $request->get('id')[$i],
                        'notes'                     => $request->get('notes')[$i],
                        'qty'                       => $request->get('qty')[$i],
                    ];
                    $parsial[]  = '1';
                }else{
                    $parsial[]  = '2';
                }
                
            }

            $ids        = implode(',', $ids);
            $cases      = implode(' ', $cases);

            \DB::update("UPDATE inventory_return_items SET status = CASE {$cases} END WHERE id in ({$ids})");

            InventoryReturnInItem::insert($dataRTN);

            if (in_array('2', $parsial)) {
                $dataROT['status'] = '2';
            } else {
                $dataROT['status'] = '3';
            }

            $rot = InventoryReturnOut::findOrFail($request->get('inventory_return_out_id'));
            $rot->update($dataROT);
            DB::commit();
            
            return redirect()->route('logistic.return_in.show',Hashids::encode($return_in->id))->with(['success' => 'Berhasil melakukan RIN!']);
        } catch (\Exception $e) {

            DB::rollback();
            return redirect()->back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }


    public function show($id)
    {
        $id = Hashids::decode($id);

        $return_in      = InventoryReturnIn::getById($id['0']);
        $return_in_items= InventoryReturnInItem::getByReturnInId($return_in->id);
        return view('logistic.return_in.show', compact('return_in','return_in_items'));
    }


    public function edit($id)
    {
        $id = Hashids::decode($id);

        $return_in      = InventoryReturnIn::getById($id['0']);
        $return_in_items= InventoryReturnInItem::getByReturnInId($return_in->id);

        $usage_data = array('Perusahaan','Perorangan');
        $usage = array();
        $uno=1;
        foreach($usage_data as $val){
            $usage[$uno]= $val;
            $uno++;
        }

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

        return view('logistic.return_in.edit', compact('return_in','return_in_items','location','usage'));
    }


    public function update(Request $request, $id)
    {
       
        $return_in = InventoryReturnIn::findOrFail($id);
    
        if ($request->get('status')==1) {
            $increment = DB::table('inventory_return_in')
            ->whereYear("publish", date('Y'))
            ->where('status', '!=', 0)
            ->where('location_id',$request->get('location_id'))
            ->count();

            $num = sprintf("%'.05d", $increment + 1) ;
            $doc_no     = explode('-',$return_in->doc_no);
            $location   = $doc_no['2'];
            $company    = $doc_no['1'];
            $data['doc_no'] = "RIN-".$company."-".$location."-".date('my')."-".$num;
            $data['status']  = 1;
            $data['publish'] = date('Y-m-d');
        }else{
            $data['status'] = 0;
        }

        $data['operator']       = $request->get('operator');
        $data['location_id']    = $request->get('location_id');
        $data['created_by']     = Auth::user()->id;   

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $name = 'inv_ba_'.time();
            $folder = 'uploads/inventory/'.date('Y').'/'.date('M').'/';
            $filePath = $folder . $name. '.' . $file->getClientOriginalExtension();
            $this->uploadOne($file, $folder, 'public', $name);
            $data['file'] = $filePath;
        }
        $return_in->update($data);

        $return_in_itemID = $request->get('return_in_itemID');
        for($i=0;$i < count($return_in_itemID);$i++) {
            $ids[]      = $request->get('return_in_itemID')[$i];
            $notes[]    = "WHEN id = {$request->get('return_in_itemID')[$i]} THEN '".$request->get('notes')[$i]."'";
            $qty[]      = "WHEN id = {$request->get('return_in_itemID')[$i]} THEN ".$request->get('qty')[$i];
        }

        $ids    = implode(',', $ids);
        $notes  = implode(' ', $notes);
        $qty    = implode(' ', $qty);

        \DB::update("UPDATE inventory_return_items SET qty = CASE {$qty} END, reason = CASE {$notes}  END WHERE id in ({$ids})");

        return redirect()->route('logistic.return_in.index')->with(['success' => 'Berhasil melakukan RIN!']);
    }



    public function delete(Request $request)
    {
       
        $return_in  = InventoryReturnOut::findOrFail($request->id);
        $return_in_items   = InventoryReturnOutItem::getByReturnOutId($request->id);

        $ids    = [];
        $data   = [];
        foreach($return_in_items as $val){
            $qty = $val->stock_onhand + $val->qty;
            $data = array (
                'stock_onhand'  => $qty
            );
            DB::table('inventories')
            ->where('id', $val->inventory_id)
            ->update($data);
        }

        $po->delete();
        return redirect()->route('logistic.return_in.index')->with(['success' => 'Delete Data Berhasil!']);

    }


    public function print($id)
    {
        $id = Hashids::decode($id);
        $return_in      = InventoryReturnIn::getById($id['0']);
        $return_in_items= InventoryReturnInItem::getByReturnInId($return_in->id);
   
        return view('logistic.return_in.print', compact('return_in','return_in_items'));
    }


    public function search(Request $request)
    { 
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
        return view('logistic.return_in.search', compact('data','location', 'search','query'));
    }


    public function export(Request $request)
    {
        $date = date('Y-m-d');
        return Excel::download(new ReturnInExport($request->get('doc_no'),$request->get('location_id'), $request->get('start_date'), $request->get('end_date')), 'Report-RIN-'.$date.'.xlsx');
    }


    public function approve($id)
    {
        $id = Hashids::decode($id);

        $return_in      = InventoryReturnIn::getById($id['0']);
        $return_in_items= InventoryReturnInItem::getByReturnInId($return_in->id,'status');
        return view('logistic.return_in.approve', compact('return_in','return_in_items'));
    }


    public function approve_store(Request $request)
    {
       
        $id = $request->get('inventory_return_in_id');

        $rin = InventoryReturnIn::getById($id);

        $itemID = $request->get('id');
        $invHistory = [];
        $rin_ids = $rin_status = $rin_received = $rin_received_by = [];
        $ids = $in = $update = $status = [];

        for($i=0;$i < count($itemID);$i++) {

            if (in_array($request->get('id')[$i], $request->get('iscreateRIN'))) {
                $date               = date("Y-m-d",strtotime($request->get('received_date')));
                $rin_ids[]          = $request->get('id')[$i];
                $rin_status[]       = "WHEN id = {$request->get('id')[$i]} THEN 1";
                $rin_received[]     = "WHEN id = {$request->get('id')[$i]} THEN '".$request->get('received')."'";
                $rin_received_date[]= "WHEN id = {$request->get('id')[$i]} THEN TO_DATE('".$date."','YYYY-MM-DD')";
                $parsial[]      = '1';

                $invHistory[] = [
                    'inventory_id'  => $request->get('inv_id')[$i],
                    'qty_in'        => $request->get('qty')[$i],
                    'qty_awal'      => $request->get('qty_stock')[$i],
                    'message'       => $rin->doc_no,
                    'description'   => "dari ". $rin->doc_rot,
                ];

                $ids[]          = $request->get('inv_id')[$i];
                $stock_onhand   = $request->get('qty_stock')[$i] +  $request->get('qty')[$i];
                $stock_in       = $request->get('qty_in')[$i] +  $request->get('qty')[$i];
                $stock_status   = getStatusInventory($request->get('stock_max')[$i], $request->get('stock_min')[$i], $stock_onhand,'raw');
                $in[]           = "WHEN id = {$request->get('inv_id')[$i]} THEN $stock_in";
                $update[]       = "WHEN id = {$request->get('inv_id')[$i]} THEN $stock_onhand";
                $status[]       = "WHEN id = {$request->get('inv_id')[$i]} THEN '".$stock_status."'";

            }else{
                $parsial[]  = '2';
            }
          
        }

        $rin_ids        = implode(',', $rin_ids);
        $rin_status     = implode(' ', $rin_status);
        $rin_received   = implode(' ', $rin_received);
        $rin_received_date     = implode(' ', $rin_received_date);

        \DB::update("UPDATE inventory_return_in_items SET status = CASE {$rin_status} END, received = CASE {$rin_received} END, received_date = CASE {$rin_received_date} END WHERE id in ({$rin_ids})");

        if (in_array('2', $parsial)) {
            $dataRIN['status'] = '2';
        } else {
            $dataRIN['status'] = '3';
        }
        $rin = InventoryReturnIn::find($id);
        $rin->update($dataRIN);

        $ids        = implode(',', $ids);
        $update     = implode(' ', $update);
        $in         = implode(' ', $in);
        $status     = implode(' ', $status);

        \DB::update('UPDATE inventories SET stock_onhand = CASE '.$update.' END, "in" = CASE '.$in.' END, stock_status = CASE '.$status.' END WHERE id in ('.$ids.')');

        InventoryHistory::insert($invHistory);
      
        return redirect()->route('logistic.return_in.index')->with(['success' => 'Approve Return In Data Berhasil!']);
        
    }


}
