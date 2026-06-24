<?php

namespace App\Http\Controllers\Logistic;

use App\Models\Inventory;
use App\Models\InventoryHistory;
use App\Models\InventoryConversion;
use App\Models\InventoryConversionItem;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Vinkla\Hashids\Facades\Hashids;
use Yajra\DataTables\Facades\DataTables;
use Rap2hpoutre\FastExcel\FastExcel;
use App\Exports\ConversionExport;
use App\Traits\UploadTrait;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use App\Mail\SendMailable;
use Illuminate\Support\Facades\Mail;
use Auth;
use Storage;
use Illuminate\Support\Facades\Redirect;


class ConversionController extends Controller
{
   
    use UploadTrait;
    /**
     * Display a listing of Items.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct()
    {
         $this->middleware('permission:adjustment');
    }
    
    public function index()
    {
  
        if(isAdministrator() || isAdmin() ){
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
        }elseif(isAdministratorLocation()){
            $location   = DB::table('locations')->where('id', Auth::user()->location_id)->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
        }else{
            $location = DB::table('locations')->where('id', Auth::user()->location_id)->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
        }
        
        return view('logistic.conversion.index',compact('location'));
    }



    public function datatables(Request $request)
    {
        $data = $request->all();
        if(isAdministrator() || isAdmin() ) $result  = InventoryConversion::getData($data);
        elseif(isAdministratorCompany() ) $result  = InventoryConversion::getData($data, Auth::user()->company_id);
        else $result = InventoryConversion::getData($data,null,Auth::user()->location_id);


        return  DataTables::of($result)

        ->addColumn('action', function ($result) {
            $url = 'printExternal("/logistic/conversion_print/'.Hashids::encode($result->id).'")';
            $url_print = "<a onclick='".$url."' data-toggle='Print' class='btn btn-outline'><span class='ti-printer icon-lg'></span> </a>";  
            $url_show  = "<a href='".route('logistic.conversion.show',Hashids::encode($result->id))."'class='btn btn-outline'><span class='ti-eye icon-lg'></span> </a>";  
            $url_edit = "<a href='".route('logistic.conversion.edit', Hashids::encode($result->id))."' title='Edit' data-toggle='tooltip' class='btn btn-outline'><span class='ti-pencil icon-lg'></span> </a>";  
           

            if($result->status==0){
                return $url_edit.$url_show;
            }else{
                return $url_show.$url_print;
            }
        })
        ->addColumn('status', function ($result) {
            return getStatusData($result->status);
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
            ->select(
                'inventories.*',
                'locations.name AS locationName',
                'companies.name AS companyName',
                'master_item_products.name AS productName', 
                'master_item_products.code AS productCode', 
                'master_item_products.part_number AS productPartNumber',
                'measures.name AS unit'
            )
            ->leftJoin('master_item_products', 'master_item_products.id', '=', 'inventories.product_id')
            ->leftJoin('locations', 'locations.id', '=', 'inventories.location_id')
            ->leftJoin('companies', 'locations.company_id', '=', 'companies.id')
            ->leftJoin('measures', 'measures.id', '=', 'master_item_products.measure_inventory')
            ->whereIn('inventories.id',$invID)
            ->get();  

            $location = [];
            foreach($inventory as $item){
                $location [] = $item->location_id;
                $locationID   = $item->location_id;
                $locationName = $item->locationName;
                $companyName = $item->companyName;
            }

            if(count(array_unique($location)) === 1 ){

                $locationID = $locationID;
                $locationName = $locationName;
                return view('logistic.conversion.create', compact('item','locationID','locationName', 'inventory','companyName'));

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
        if (! Gate::allows('conversion')) {
            return abort(401);
        }

        $location  = DB::table('locations')
        ->select('locations.name AS name','locations.alias AS code','companies.alias AS companyCode','locations.email AS email')
        ->leftJoin('companies','companies.id','=','locations.company_id')
        ->where("locations.id", $request->get('location_id'))->first();
       
        $increment = DB::table('inventory_conversions')
        ->whereYear("publish",  date('Y'))
        ->where('status', '!=', 0)
        ->where('location_id',$request->get('location_id'))
        ->count();
        $num = sprintf("%'.05d", $increment + 1) ;
        $no = "KNV-".$location->companyCode."-".$location->code."-".date('my')."-".$num;
        $data['status']  = 1;
        $data['publish'] = date('Y-m-d');

        $data['operator']       = $request->get('operator');
        $data['location_id']    = $request->get('location_id');
        $data['doc_no']         = $no;
        $data['created_by']     = Auth::user()->id;   

        DB::beginTransaction();

        try {

            $knv = InventoryConversion::create($data);

            $dataKNV = [];
            $product = $request->get('inv_id');

            for($i=0;$i < count($product);$i++) {
                $dataKNV[] = [
                    'inventory_conversion_id'  => $knv->id,
                    'inventory_id_from' => $request->get('inv_id')[$i],
                    'inventory_id_to'   => $request->get('conversion_inv_id')[$i],
                    'qty_from'          => $request->get('stock_qty')[$i],
                    'qty_to'            => $request->get('conversion_qty')[$i],
                ];
            }

            InventoryConversionItem::insert($dataKNV);

            $ids    = [];
            $onhand = [];
            $out    = [];
            $status = [];

            $conversion_ids    = [];
            $conversion_onhand = [];
            $conversion_in    = [];
            $conversion_status = [];
            
            for($i=0;$i < count($product);$i++) {
                $stock_onhand   = 0;
                $stock_out      = $request->get('stock_qty')[$i] + $request->get('stock_out')[$i];
                $stock_status   = getStatusInventory($request->get('stock_max')[$i], $request->get('stock_min')[$i], $stock_onhand,'raw');
                
                $ids[]          = $request->get('inv_id')[$i];
                $out[]          = "WHEN id = {$request->get('inv_id')[$i]} THEN $stock_out";
                $onhand[]       = "WHEN id = {$request->get('inv_id')[$i]} THEN $stock_onhand";
                $status[]       = "WHEN id = {$request->get('inv_id')[$i]} THEN '".$stock_status."'";

                $con_onhand   = $request->get('conversion_stock')[$i] + $request->get('conversion_qty')[$i];
                $con_in       = $request->get('conversion_qty')[$i] + $request->get('conversion_stock_in')[$i];
                $con_status   = getStatusInventory($request->get('conversion_stock_max')[$i], $request->get('conversion_stock_min')[$i], $con_onhand,'raw');
            
                $conversion_ids[]         = $request->get('conversion_inv_id')[$i];
                $conversion_in[]          = "WHEN id = {$request->get('conversion_inv_id')[$i]} THEN $con_in";
                $conversion_onhand[]      = "WHEN id = {$request->get('conversion_inv_id')[$i]} THEN $con_onhand";
                $conversion_status[]      = "WHEN id = {$request->get('conversion_inv_id')[$i]} THEN '".$con_status."'";

            }
            $ids        = implode(',', $ids);
            $onhand     = implode(' ', $onhand);
            $out        = implode(' ', $out);
            $status     = implode(' ', $status);

            $conversion_ids        = implode(',', $conversion_ids);
            $conversion_onhand     = implode(' ', $conversion_onhand);
            $conversion_in         = implode(' ', $conversion_in);
            $conversion_status     = implode(' ', $conversion_status);

            \DB::update("UPDATE inventories SET stock_onhand = CASE {$onhand} END, out = CASE {$out} END, stock_status = CASE {$status} END WHERE id in ({$ids})");
            \DB::update('UPDATE inventories SET stock_onhand = CASE '.$conversion_onhand.' END, "in" = CASE '.$conversion_in.' END, stock_status = CASE '.$conversion_status.' END WHERE id in ('.$conversion_ids.')');

            $invHistoryStock = $invHistoryConversion = [];

            for($i=0;$i < count($product);$i++) {

                $invHistoryStock[] = [
                    'inventory_id'  => $request->get('inv_id')[$i],
                    'qty_out'       => $request->get('stock_qty')[$i],
                    'qty_awal'      => $request->get('stock_qty')[$i],
                    'message'       => $knv->doc_no,
                    'description'   => '',
                ];

                $invHistoryConversion[] = [
                    'inventory_id'  => $request->get('conversion_inv_id')[$i],
                    'qty_in'        => $request->get('conversion_qty')[$i],
                    'qty_awal'      => $request->get('conversion_stock')[$i],
                    'message'       => $knv->doc_no,
                    'description'   => '',
                ];
            }

            InventoryHistory::insert($invHistoryStock);
            InventoryHistory::insert($invHistoryConversion);

            DB::commit();
            return redirect()->route('logistic.conversion.show',Hashids::encode($knv->id))->with(['success' => 'Berhasil melakukan Konversi!']);
        } catch (\Exception $e) {

            DB::rollback();
            return redirect()->back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function show($id)
    {

        $id = Hashids::decode($id);
        $conversion      = InventoryConversion::getById($id['0']);
        $conversion_items= InventoryConversionItem::getByConversionId($conversion->id);
        return view('logistic.conversion.show', compact('conversion','conversion_items'));
    }


    public function edit($id)
    {
       
        $id = Hashids::decode($id);
        $conversion      = InventoryConversion::getById($id['0']);
        $conversion_items= InventoryConversionItem::getByConversionId($conversion->id);

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
            $department  = DB::table('departments')
                        ->selectRaw("CONCAT (departments.name,' - ', companies.alias) as name, departments.id")
                        ->leftjoin('companies','companies.id','=','departments.company_id')
                        ->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
        }elseif(isAdministratorCompany()){
            $location = DB::table('locations')->where('company_id', Auth::user()->company_id)->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
            $department = DB::table('departments')->where('company_id', Auth::user()->company_id)->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
        }elseif(isAdministratorLocation()){
            $location = DB::table('locations')->where('id', Auth::user()->location_id)->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
            $department = DB::table('departments')->where('company_id', Auth::user()->company_id)->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
        }else{
            $location = DB::table('locations')->where('id', Auth::user()->location_id)->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
            $department = DB::table('departments')->where('company_id', Auth::user()->company_id)->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
        }

        return view('logistic.conversion.edit', compact('conversion','conversion_items','department','location','usage'));
    }

    
    public function update(Request $request, $id)
    {
        $conversion = InventoryConversion::findOrFail($id);
    
        if ($request->get('status')==1) {
            $increment = DB::table('inventory_conversions')
            ->whereYear("publish",  date('Y'))
            ->where('status', '!=', 0)
            ->where('location_id',$request->get('location_id'))
            ->count();

            $num = sprintf("%'.05d", $increment + 1) ;
            $doc_no     = explode('-',$conversion->doc_no);
            $location   = $doc_no['2'];
            $company    = $doc_no['1'];
            $data['doc_no'] = "TTB-".$company."-".$location."-".date('my')."-".$num;
            $data['status']  = 1;
            $data['publish'] = date('Y-m-d');
        }else{
            $data['status'] = 0;
        }

        $data['operator']       = $request->get('operator');
        $data['received']       = $request->get('received');
        $data['coa']            = $request->get('coa');
        $data['department_id']  = $request->get('department_id');
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
        $conversion->update($data);

        $conversion_itemID = $request->get('conversion_itemID');
        for($i=0;$i < count($conversion_itemID);$i++) {
            $ids[]      = $request->get('conversion_itemID')[$i];
            $notes[]    = "WHEN id = {$request->get('conversion_itemID')[$i]} THEN '".$request->get('notes')[$i]."'";
            $qty[]      = "WHEN id = {$request->get('conversion_itemID')[$i]} THEN ".$request->get('qty')[$i];
            $usage[]    = "WHEN id = {$request->get('conversion_itemID')[$i]} THEN '".$request->get('usage')[$i]."'";
            $date_of_issue[]    = "WHEN id = {$request->get('conversion_itemID')[$i]} THEN '".$request->get('date_of_issue')[$i]."'";
        }

        $ids    = implode(',', $ids);
        $notes  = implode(' ', $notes);
        $qty    = implode(' ', $qty);
        $usage  = implode(' ', $usage);
        $date_of_issue  = implode(' ', $date_of_issue);

        \DB::update("UPDATE inventory_conversion_items SET qty = CASE {$qty} END, description = CASE {$notes} END, usage = CASE {$usage} END, date_of_issue = CASE {$date_of_issue} END WHERE id in ({$ids})");

        $ids    =[];
        $update =[];
        $product = $request->get('inv_id');
        for($i=0;$i < count($product);$i++) {
            if($request->get('qty')[$i] > $request->get('qty_conversion')[$i]){
                $stock_onhand   = $request->get('qty_stock')[$i] -  $request->get('qty')[$i];
            }elseif($request->get('qty')[$i] < $request->get('qty_conversion')[$i]){
                $qty_retur      = $request->get('qty_conversion')[$i] - $request->get('qty')[$i];
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
            $location  = DB::table('locations')
            ->select('locations.name AS name','locations.alias AS code','companies.alias AS companyCode','locations.email AS email')
            ->leftJoin('companies','companies.id','=','locations.company_id')
            ->where("locations.id", $request->get('location_id'))->first();

            $productUrgentStock = [];
            for($i=0;$i < count($conversion_itemID);$i++) {
                $onhand   = $request->get('qty_stock')[$i] -  $request->get('qty')[$i];
                $max      = $request->get('stock_max')[$i];
                $min      = $request->get('stock_min')[$i];
               
                if(getStatusInventory($max, $min, $onhand,'raw') == 'Urgent Order'){
                    $productUrgentStock [] = [
                        'productCode' => $request->get('productCode')[$i],
                        'productName' => $request->get('productName')[$i],
                        'rack'        => $request->get('code_rack')[$i],
                        'stock'       => $onhand,
                        'min'         => $min,
                        'max'         => $max
                    ];
                    $email_inventory = config('app.mail_inventory');
                    $emailCC         = explode(",",$email_inventory);
                    $msgData = array(
                        'title'         => 'Informasi Stock Urgent Order',
                        'emailCC'       => $emailCC,
                        'no_conversion'        => $conversion->doc_no,
                        'email'         => $location->email,
                        'name'          => $location->name,
                        'product'       => $productUrgentStock
                    );
                }
                $invHistory[] = [
                    'inventory_id'  => $request->get('inv_id')[$i],
                    'qty_out'       => $request->get('qty')[$i],
                    'qty_awal'      => $request->get('qty_stock')[$i],
                    'message'       => $conversion->doc_no,
                    'description'   => $request->get('notes')[$i],
                ];
            }

            InventoryHistory::insert($invHistory);

            if (config('app.mail_status')=='on' && count($productUrgentStock) > 0) {
                Mail::send('emails.inv_stock', $msgData, function ($message) use ($msgData) {
                    $message->to($msgData['email'], $msgData['name'])
                    ->subject('Informasi Stock Urgent Order');
                    $message->cc($msgData['emailCC'], $name = null)
                    ->subject('Informasi Stock Urgent Order');
                });
            }
        }
       
        return redirect()->route('logistic.conversion.index')->with(['success' => 'Berhasil melakukan TTB!']);
    }


    public function delete(Request $request)
    {

      
        $conversion  = InventoryConversion::findOrFail($request->id);

        $conversion_items   = InventoryConversionItem::getByConversionId($request->id);

        $ids    = [];
        $data   = [];
        foreach($conversion_items as $val){
            $qty = $val->stock_onhand + $val->qty;
            $data = array (
                'qty'  => $qty
            );
            DB::table('inventories')
            ->where('id', $val->inventory_id)
            ->update($data);
        }

        $po->delete();
        return redirect()->route('purchasing.po.index')->with(['success' => 'Delete Data Berhasil!']);

    }


    public function print($id)
    {

        $id = Hashids::decode($id);
        $conversion      = InventoryConversion::getById($id['0']);
        $conversion_items= InventoryConversionItem::getByConversionId($conversion->id);
   
        return view('logistic.conversion.print', compact('conversion','conversion_items'));
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
            $location   = DB::table('locations')->where('company_id', Auth::user()->company_id)->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
        }elseif(isAdministratorLocation()){
            $location   = DB::table('locations')->where('id', Auth::user()->location_id)->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
        }else{
            $location = DB::table('locations')->where('id', Auth::user()->location_id)->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
        }

        return view('logistic.conversion.search', compact('data','location','search','query'));
    }

    public function export(Request $request)
    {
        $date = date('Y-m-d');
        return Excel::download(new ConversionExport($request->get('location_id'), $request->get('start_date'), $request->get('end_date')), 'Report-Konversi-'.$date.'.xlsx');
    }


}
