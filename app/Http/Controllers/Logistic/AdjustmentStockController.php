<?php

namespace App\Http\Controllers\Logistic;

use App\Models\Inventory;
use App\Models\InventoryAdjustment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Vinkla\Hashids\Facades\Hashids;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use Rap2hpoutre\FastExcel\FastExcel;
use Auth;

class AdjustmentStockController extends Controller
{


    function __construct()
    {
         $this->middleware('permission:adjustment');
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

        return view('logistic.adjustment_stock.index',compact('location'));
    }

    public function datatables(Request $request)
    {
        $data = $request->all();

        if(isAdministrator() || isAdmin() ) $result  = InventoryAdjustment::getData($data);
        elseif(isAdministratorCompany() ) $result  = InventoryAdjustment::getData($data, Auth::user()->company_id);
        else $result = InventoryAdjustment::getData($data,null,Auth::user()->location_id);

        return  DataTables::of($result)

        ->addColumn('action', function ($result) {
            $url = 'printExternal("/logistic/adjustment_print/'.Hashids::encode($result->id).'")';
            $action = "<a onclick='".$url."' data-toggle='Print' class='btn btn-outline'><span class='ti-printer icon-lg'></span> </a>";  
            $action .= "<a href='".route('logistic.adjustment_stock.show',Hashids::encode($result->id))."' class='btn btn-outline'><span class='ti-eye icon-lg'></span> </a>";  
        
            return $action;
        })
        ->editColumn('created_at', function ($result) {
            return $result->created_at ? with(new Carbon($result->created_at))->format('d/m/Y') : '';
        })
        ->editColumn('productName', function ($result) {
            return $result->productName."<br> <small>Kode: ".$result->productCode."</small>";
        })
        ->rawColumns(['productName','action'])
        ->make(true);

    }


    public function store(Request $request)
    {
        $increment = DB::table('inventory_adjustments')
        ->leftJoin('inventories', 'inventories.id', '=', 'inventory_adjustments.inventory_id')
        ->whereYear("inventory_adjustments.created_at", Carbon::today()->toDateString())
        ->where('inventories.location_id',$request->get('location_id'))
        ->get();

        $location  = Workarea::where("id", $request->get('location_id'))->first();
    
        if(isAdministrator()){
            $company   = Company::where('id', $location->company_id)->first()->alias;
        }elseif(isAdministratorLocation()){
            $company   = Company::where('id', Auth::user()->company_id)->first()->alias;
        }else{
            $company   = Company::where('id', Auth::user()->employee()->company_id)->first()->alias;
        }

        $num = sprintf("%'.05d", count($increment) + 1) ;
        $no = "ADC-".$company."-".$location->alias."-".date('my')."-".$num;
        $data['inventory_id']   = $request->get('inventory_id');
        $data['reason']         = $request->get('reason');
        $data['doc_no']         = $no;
        $data['qty_awal']       = $request->get('qty_awal');
        $data['qty_fisik']      = $request->get('qty_fisik');
        $data['created_by']     = Auth::user()->id;   

        $adjustment = InventoryAdjustment::insert($data);
        
        $dataInv['stock_onhand']   = $request->get('qty_fisik');
        $inventory = Inventory::findOrFail($request->get('inventory_id'));
        $inventory->update($dataInv);

        return redirect()->route('logistic.inventory.adjustment',['id' => Hashids::encode($request->get('inventory_id'))])->with(['success' => 'Berhasil melakukan Adjustment Stock!']);
    }


    public function search(Request $request)
    { 
       
        $data = $request->all();
        $search = "Cari Berdasarkan: ";
        $query = "location_id=".$request->get('location_id')."&start_date=".$request->get('start_date')."&end_date=". $request->get('end_date');

        if($request->input('location_id')) $search .= "<strong> Lokasi: </strong>".getDataByID('locations',$request->input('location_id'))->name;
        if($request->input('start_date') || $request->input('end_date')) $search .= "<strong> Periode: </strong>".$request->input('start_date'). " - ". $request->input('end_date');

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

        return view('logistic.adjustment_stock.search', compact('data','location', 'search','query'));
    }


    public function export(Request $request)
    {
        
        $data = $request->all();

        $query = DB::table('inventory_adjustments')
        ->select('inventory_adjustments.*'
        ,'users.name AS created',
        'inventories.code_rack',
        'locations.name AS warehouse','master_item_products.name AS productName', 'master_item_products.code AS productCode', 'master_item_products.part_number AS productPartNumber',
        DB::raw('ROW_NUMBER () OVER (ORDER BY inventory_adjustments.id) as number'))
        ->leftJoin('inventories', 'inventories.id', '=', 'inventory_adjustments.inventory_id')
        ->leftJoin('master_item_products', 'master_item_products.id', '=', 'inventories.product_id')
        ->leftJoin('locations', 'locations.id', '=', 'inventories.location_id')
        ->leftJoin('users', 'users.id', '=', 'inventory_adjustments.created_by')
        ->when(!empty($data['location_id']), function ($query) use ($data) {
            return $query->where('inventories.location_id',$data['location_id']);
        })
        ->when(!empty($data['start_date']), function ($query) use ($data) {
            if($data['end_date']){
                $start = date("Y-m-d",strtotime($data['start_date']));
                $end   = date("Y-m-d",strtotime($data['end_date']."+1 day"));
                return $query->whereBetween('inventory_adjustments.created_at', [$start , $end]);
            }else{
                return $query->where('inventory_adjustments.created_at', $data['start_date']);
            }

        })
        ->get();
        if( $query->isEmpty() ){
            return redirect()->route('logistic.adjustment_stock.index')->with(['error' => 'Tidak terdapat data untuk di Export']);
        }else{
            
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="your_name.xls"');
            header('Cache-Control: max-age=0');
            return (new FastExcel($query))->download('inventory-adjustment-'.date('d-m-Y').'.xlsx', function ($inv) {
                return [
                    'No'            => $inv->number,
                    'Nomor Dokumen' => $inv->doc_no,
                    'Kode Produk'   => $inv->productCode,
                    'Nama Produk'   => $inv->productName,
                    'PN/SPEC'   => $inv->productPartNumber,
                    'No.Rak'        => $inv->code_rack,
                    'Warehouse'     => $inv->warehouse,
                    'QTY Awal'      => $inv->qty_awal,
                    'QTY Adjustment'=> $inv->qty_fisik,
                    'Alasan'        => $inv->reason,
                    'Input Oleh'    => $inv->created,
                    'Tgl Input'     => date('d/m/Y',strtotime( $inv->created_at)),
                ];
            });
        }
    }


    public function show($id)
    { 
        $id = Hashids::decode($id);
        $data = InventoryAdjustment::findOrFail($id['0']);
        return view('logistic.adjustment_stock.show', compact('data'));
    }

}
