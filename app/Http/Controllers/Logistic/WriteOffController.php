<?php

namespace App\Http\Controllers\Logistic;

use App\Models\Inventory;
use App\Models\InventoryWriteOff;
use App\Models\MasterItemProduct;
use App\Models\MasterItemCategory;
use App\Models\MasterItem;
use App\Models\Workarea;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Vinkla\Hashids\Facades\Hashids;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use Rap2hpoutre\FastExcel\FastExcel;
use Auth;

class WriteOffController extends Controller
{

    function __construct()
    {
        $this->middleware('permission:writeoff');
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
         
        return view('logistic.write_off.index',compact('location'));
    }

    public function datatables(Request $request)
    {
        $data = $request->all();
        if(isAdministrator() || isAdmin() ) $result  = InventoryWriteOff::getData($data);
        elseif(isAdministratorCompany() ) $result  = InventoryWriteOff::getData($data, Auth::user()->company_id);
        else $result = InventoryWriteOff::getData($data,null,Auth::user()->location_id);

       return  DataTables::of($result)
        ->editColumn('created_at', function ($result) {
            return $result->created_at ? with(new Carbon($result->created_at))->format('d/m/Y') : '';
        })
        ->editColumn('productName', function ($result) {
            return $result->productName."<br> <small>Kode: ".$result->productCode."</small>";
        })
        ->rawColumns(['productName'])
        ->make(true);

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

        return view('logistic.write_off.search', compact('data','location', 'search','query'));
    }


    public function export(Request $request)
    {
        
        $data = $request->all();

        $query = DB::table('inventory_writeoffs')
        ->select('inventory_writeoffs.*','users.name AS created','inventories.code_rack','locations.name AS warehouse','master_item_products.name AS productName', 'master_item_products.code AS productCode', 'master_item_products.part_number AS productPartNumber',
        DB::raw('ROW_NUMBER () OVER (ORDER BY inventory_writeoffs.id) as number'))
        ->leftJoin('inventories', 'inventories.id', '=', 'inventory_writeoffs.inventory_id')
        ->leftJoin('master_item_products', 'master_item_products.id', '=', 'inventories.product_id')
        ->leftJoin('locations', 'locations.id', '=', 'inventories.location_id')
        ->leftJoin('users', 'users.id', '=', 'inventory_writeoffs.created_by')
        ->when(!empty($data['location_id']), function ($query) use ($data) {
            return $query->where('inventories.location_id',$data['location_id']);
        })
        ->when(!empty($data['start_date']), function ($query) use ($data) {
            if($data['end_date']){
                $start = date("Y-m-d",strtotime($data['start_date']));
                $end   = date("Y-m-d",strtotime($data['end_date']."+1 day"));
                return $query->whereBetween('inventory_writeoffs.created_at', [$start , $end]);
            }else{
                return $query->where('inventory_writeoffs.created_at', $data['start_date']);
            }

        })
        ->get();
        if( $query->isEmpty() ){
            return redirect()->route('logistic.write_off.index')->with(['error' => 'Tidak terdapat data untuk di Export']);
        }else{
            
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="your_name.xls"');
            header('Cache-Control: max-age=0');
            return (new FastExcel($query))->download('inventory-writeoff-'.date('d-m-Y').'.xlsx', function ($inv) {
                return [
                    'No'            => $inv->number,
                    'Nomor Dokumen' => $inv->doc_no,
                    'Kode Produk'   => $inv->productCode,
                    'Nama Produk'   => $inv->productCode.'-'.$inv->productName,
                    'PN/SPEC'   => $inv->productPartNumber,
                    'No.Rak'        => $inv->code_rack,
                    'Warehouse'     => $inv->warehouse,
                    'Alasan'        => $inv->reason,
                    'Input Oleh'    => $inv->created,
                    'Tgl Input'     => date('d/m/Y',strtotime( $inv->created_at)),
                ];
            });
        }
      

    }



}
