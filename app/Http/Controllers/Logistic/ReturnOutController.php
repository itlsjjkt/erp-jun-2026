<?php

namespace App\Http\Controllers\Logistic;

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
use App\Exports\ReturnOutExport;
use App\Traits\UploadTrait;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use App\Mail\SendMailable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;

use Storage;
use Auth;

class ReturnOutController extends Controller
{

    use UploadTrait;

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
        return view('logistic.return_out.index',compact('location'));
    }
    

    public function datatables(Request $request)
    {
        $data = $request->all();
        if(isAdministrator() || isAdmin() ) $result  = InventoryReturnOut::getData($data);
        elseif(isAdministratorCompany() ) $result  = InventoryReturnOut::getData($data, Auth::user()->company_id);
        else $result = InventoryReturnOut::getData($data,null,Auth::user()->location_id);

       return  DataTables::of($result)
        ->addColumn('action', function ($result) {
            $url = 'printExternal("/logistic/return_out_print/'.Hashids::encode($result->id).'")';
            $url_print = "<a onclick='".$url."' data-toggle='Print' class='btn btn-outline'><span class='ti-printer icon-lg'></span> </a>";  
            $url_show  = "<a href='".route('logistic.return_out.show', Hashids::encode($result->id))."'class='btn btn-outline'><span class='ti-eye icon-lg'></span> </a>";  
            $url_edit = "<a href='".route('logistic.return_out.edit', Hashids::encode($result->id))."' title='Edit' data-toggle='tooltip' class='btn btn-outline'><span class='ti-pencil icon-lg'></span> </a>";  
        
            $url_download = '';
            if($result->file != NULL){
                $path = Storage::url($result->file);
                $url_download = "<a download href='".$path ."' data-toggle='Download BA' class='btn btn-outline'><span class='fa fa-file-pdf-o icon-lg text-danger'></span> </a>";  
            }

            if($result->status==0){
                return $url_edit.$url_show.$url_download;
            }else{
                return $url_show.$url_print.$url_download;
            }
        })
        ->addColumn('status', function ($result) {
            return getStatusDataROT($result->status);
        })
        ->editColumn('created_at', function ($result) {
            return $result->created_at ? with(new Carbon($result->created_at))->format('d/m/Y H:i:s') : '';
        })
        ->rawColumns(['action', 'status'])
        ->make(true);

    }


    public function create(Request $request)
    {
       
        if($request->get('inv_id')){

            $invID = explode(',',$request->get('inv_id'));

            $inventory = DB::table('inventories')
            ->select('inventories.*','locations.name AS locationName',
            'master_item_products.name AS productName', 'master_item_products.code AS productCode', 'master_item_products.part_number AS productPartNumber', 'master_item_brands.name AS productBrand',
            'measures.name AS unit')
            ->leftJoin('master_item_products', 'master_item_products.id', '=', 'inventories.product_id')
            ->leftJoin('locations', 'locations.id', '=', 'inventories.location_id')
            ->leftJoin('measures', 'measures.id', '=', 'inventories.measure_id')
            ->leftJoin('master_item_brands', 'master_item_products.brand_id', '=', 'master_item_brands.id')
            ->whereIn('inventories.id',$invID)
            ->get();  

            $location = [];
            foreach($inventory as $item){
                $location [] = $item->location_id;
                $locationID   = $item->location_id;
                $locationName = $item->locationName;
            }

            if(count(array_unique($location)) === 1 ){

                $locationID     = $locationID;
                $locationName   = $locationName;

                return view('logistic.return_out.create', compact('item', 'locationID','locationName', 'inventory'));

            }else{
                return redirect()->back()
                ->withErrors(['Lokasi Stock Gudang berbeda pada Item yang dipilih']);
            }

        }else{
                return redirect()->back()
                ->withErrors(['Belum melakukan Checklist Item Inventory']);
        }
    }


    public function store(Request $request)
    {
       

        $location  = DB::table('locations')
        ->select('locations.name AS name','locations.alias AS code','companies.alias AS companyCode','locations.email AS email')
        ->leftJoin('companies','companies.id','=','locations.company_id')
        ->where("locations.id", $request->get('location_id'))->first();
       
        if ($request->get('status')==1) {
            $increment = DB::table('inventory_return_out')
            ->whereYear("publish", date('Y'))
            ->where('status', '!=', 0)
            ->where('location_id',$request->get('location_id'))
            ->count();
            $num = sprintf("%'.05d", $increment + 1) ;
            $no = "ROT-".$location->companyCode."-".$location->code."-".date('my')."-".$num;
            $data['status']  = 1;
            $data['publish'] = date('Y-m-d');

        }else{
            $no = "ROT-".$location->companyCode."-".$location->code."-".date('my')."-DRAFT";
            $data['status'] = 0;
        }

        $data['operator']       = $request->get('operator');
        $data['location_id']    = $request->get('location_id');
        $data['doc_no']         = $no;
        $data['created_by']     = Auth::user()->id;   

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $name = 'inv_ba_'.time();
            $folder = 'uploads/inventory/'.date('Y').'/'.date('M').'/';
            $filePath = $folder . $name. '.' . $file->getClientOriginalExtension();
            $this->uploadOne($file, $folder, 'public', $name);
            $data['file'] = $filePath;
        }

        DB::beginTransaction();

        try {

            $return_out = InventoryReturnOut::create($data);
            $dataRTN = [];
            $invHistory = [];

            $ids    = [];
            $update = [];
            $out    = [];
            $status = [];

            $product = $request->get('inv_id');
            for($i=0;$i < count($product);$i++) {
                $dataRTN[] = [
                    'inventory_return_out_id'  => $return_out->id,
                    'inventory_id'      => $request->get('inv_id')[$i],
                    'reason'            => $request->get('notes')[$i],
                    'qty'               => $request->get('qty')[$i],
                ];

                $ids[]          = $request->get('inv_id')[$i];
                $stock_onhand   = $request->get('qty_stock')[$i] -  $request->get('qty')[$i];
                $stock_out      = $request->get('out')[$i] +  $request->get('qty')[$i];
                $stock_status   = getStatusInventory($request->get('stock_max')[$i], $request->get('stock_min')[$i], $stock_onhand,'raw');
                $out[]          = "WHEN id = {$request->get('inv_id')[$i]} THEN $stock_out";
                $update[]       = "WHEN id = {$request->get('inv_id')[$i]} THEN $stock_onhand";
                $status[]       = "WHEN id = {$request->get('inv_id')[$i]} THEN '".$stock_status."'";
            
            }
            InventoryReturnOutItem::insert($dataRTN);
        
            $ids        = implode(',', $ids);
            $update     = implode(' ', $update);
            $out        = implode(' ', $out);
            $status     = implode(' ', $status);

            \DB::update("UPDATE inventories SET stock_onhand = CASE {$update} END, out = CASE {$out} END, stock_status = CASE {$status} END WHERE id in ({$ids})");

            if ($request->get('status')==1) {
                for($i=0;$i < count($product);$i++) {
                
                    $invHistory[] = [
                        'inventory_id'  => $request->get('inv_id')[$i],
                        'qty_out'       => $request->get('qty')[$i],
                        'qty_awal'      => $request->get('qty_stock')[$i],
                        'message'       => $return_out->doc_no,
                        'description'   => $request->get('notes')[$i],
                    ];
                }
                InventoryHistory::insert($invHistory);
            }
            DB::commit();
            return redirect()->route('logistic.return_out.show', Hashids::encode($return_out->id))->with(['success' => 'Berhasil melakukan ROT!']);
                
        } catch (\Exception $e) {

            DB::rollback();
            return redirect()->back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function show($id)
    {
        
        $id = Hashids::decode($id);
        $return_out      = InventoryReturnOut::getById($id['0']);
        $return_out_items= InventoryReturnOutItem::getByReturnOutId($return_out->id);
        return view('logistic.return_out.show', compact('return_out','return_out_items'));
    }


    public function edit($id)
    {
        
        $id = Hashids::decode($id);

        $return_out      = InventoryReturnOut::getById($id['0']);
        $return_out_items= InventoryReturnOutItem::getByReturnOutId($return_out->id);

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

        return view('logistic.return_out.edit', compact('return_out','return_out_items','location','usage'));
    }

    
    public function update(Request $request, $id)
    {
        

        $return_out = InventoryReturnOut::findOrFail($id);
    
        if ($request->get('status')==1) {
            $increment = DB::table('inventory_return_out')
            ->whereYear("publish", date('Y'))
            ->where('status', '!=', 0)
            ->where('location_id',$request->get('location_id'))
            ->count();

            $num = sprintf("%'.05d", $increment + 1) ;
            $doc_no     = explode('-',$return_out->doc_no);
            $location   = $doc_no['2'];
            $company    = $doc_no['1'];
            $data['doc_no'] = "ROT-".$company."-".$location."-".date('my')."-".$num;
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
        $return_out->update($data);

        $return_out_itemID = $request->get('return_out_itemID');
        for($i=0;$i < count($return_out_itemID);$i++) {
            $ids[]      = $request->get('return_out_itemID')[$i];
            $notes[]    = "WHEN id = {$request->get('return_out_itemID')[$i]} THEN '".$request->get('notes')[$i]."'";
            $qty[]      = "WHEN id = {$request->get('return_out_itemID')[$i]} THEN ".$request->get('qty')[$i];
        }

        $ids    = implode(',', $ids);
        $notes  = implode(' ', $notes);
        $qty    = implode(' ', $qty);

        \DB::update("UPDATE inventory_return_items SET qty = CASE {$qty} END, reason = CASE {$notes}  END WHERE id in ({$ids})");

        $ids    =[];
        $update =[];
        $product = $request->get('inv_id');
        for($i=0;$i < count($product);$i++) {
            if($request->get('qty')[$i] > $request->get('qty_return_out')[$i]){
                $stock_onhand   = $request->get('qty_stock')[$i] -  $request->get('qty')[$i];
            }elseif($request->get('qty')[$i] < $request->get('qty_return_out')[$i]){
                $qty_retur      = $request->get('qty_return_out')[$i] - $request->get('qty')[$i];
                $stock_onhand   = $request->get('qty_real')[$i] + $qty_retur;
            }else{
                $stock_onhand   = $request->get('qty_real')[$i];
            }
            $ids[]      = $request->get('inv_id')[$i];
            $update[]   = "WHEN id = {$request->get('inv_id')[$i]} THEN $stock_onhand";
        }
        $ids        = implode(',', $ids);
        $update     = implode(' ', $update);

        \DB::update("UPDATE inventories SET stock_onhand = CASE {$update} END WHERE id in ({$ids})");

        if ($request->get('status')==1) {

            for($i=0;$i < count($return_out_itemID);$i++) {
                
                $invHistory[] = [
                    'inventory_id'  => $request->get('inv_id')[$i],
                    'qty_out'       => $request->get('qty')[$i],
                    'qty_awal'      => $request->get('qty_stock')[$i],
                    'message'       => $return_out->doc_no,
                    'description'   => $request->get('notes')[$i],
                ];
            }

            InventoryHistory::insert($invHistory);
          
        }
       
        return redirect()->route('logistic.return_out.index')->with(['success' => 'Berhasil melakukan ROT!']);
    }



    public function delete(Request $request)
    {

        $return_out  = InventoryReturnOut::findOrFail($request->id);
        $return_out_items   = InventoryReturnOutItem::getByReturnOutId($request->id);

        $ids    = [];
        $data   = [];
        foreach($return_out_items as $val){
            $qty = $val->stock_onhand + $val->qty;
            $data = array (
                'stock_onhand'  => $qty
            );
            DB::table('inventories')
            ->where('id', $val->inventory_id)
            ->update($data);
        }

        $po->delete();
        return redirect()->route('logistic.return_out.index')->with(['success' => 'Delete Data Berhasil!']);

    }


    public function print($id)
    {
       
        $id = Hashids::decode($id);
        $return_out      = InventoryReturnOut::getById($id['0']);
        $return_out_items= InventoryReturnOutItem::getByReturnOutId($return_out->id);
   
        return view('logistic.return_out.print', compact('return_out','return_out_items'));
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
        return view('logistic.return_out.search', compact('data','location', 'search','query'));
    }


    public function export(Request $request)
    {
        $date = date('Y-m-d');
        return Excel::download(new ReturnOutExport($request->get('doc_no'),$request->get('location_id'), $request->get('start_date'), $request->get('end_date')), 'Report-ROT-'.$date.'.xlsx');
    }



    public function popup($id)
    {
        $id = Hashids::decode($id);

        $return_out      = InventoryReturnOut::getById($id['0']);
        $return_out_items= InventoryReturnOutItem::getByReturnOutId($return_out->id);

        return view('logistic.return_out.popup', compact('return_out','return_out_items'))->renderSections()['content'];

    }



}
