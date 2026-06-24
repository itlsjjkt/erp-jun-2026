<?php

namespace App\Http\Controllers\Logistic;

use App\Models\Inventory;
use App\Models\InventoryAdjustment;
use App\Models\InventoryWriteOff;
use App\Models\InventoryReturn;
use App\Models\InventoryHistory;
use App\Models\InventoryProcess;
use App\Models\Workarea;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Vinkla\Hashids\Facades\Hashids;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Redirect;
use Carbon\Carbon;
use Rap2hpoutre\FastExcel\FastExcel;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use App\Traits\UploadTrait;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Str;
use App\Imports\InventoryImport;
use Maatwebsite\Excel\Facades\Excel;

use File;
use Storage;
use PDF;
use Auth;

class InventoryController extends Controller
{
    use UploadTrait;

    /**
     * Display a listing of Items.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (! Gate::allows('inventory')) {
            return abort(401);
        }

        $month = date('m');
        $year = date('Y');
        $item = DB::table('master_items')->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');

        if(isAdministrator() || isAdmin() ){
            $location    = DB::table('locations')
            ->selectRaw("CONCAT (locations.name,' - ', companies.alias) as name, locations.id")
            ->leftjoin('companies','companies.id','=','locations.company_id')
            ->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
            return view('logistic.inventory.index',compact('location','item'));

        }elseif(isAdministratorCompany()){
            $cek = InventoryProcess::where('location_id',Auth::user()->location_id)
            ->where('month',$month)
            ->where('year',$year)->first();

            if($cek){
                $location = DB::table('locations')->where('company_id', Auth::user()->company_id)->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
                return view('logistic.inventory.index',compact('location','item'));
            } else{
                $location = Auth::user()->location_id;
                return view('logistic.inventory.process',compact('location'));
            }

        } elseif(isAdministratorLocation()){
            $cek = InventoryProcess::where('location_id',Auth::user()->location_id)
            ->where('month',$month)
            ->where('year',$year)->first();

            if($cek){
                $location = DB::table('locations')->where('id', Auth::user()->location_id)->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
                return view('logistic.inventory.index',compact('location','item'));
            } else{
                $location = Auth::user()->location_id;
                return view('logistic.inventory.process',compact('location'));
            }

        }else{
            $cek = InventoryProcess::where('location_id',Auth::user()->location_id)
            ->where('month',$month)
            ->where('year',$year)->first();

            if($cek){
                $location = Auth::user()->location_id;
                return view('logistic.inventory.index',compact('location','item'));
            } else{
                return view('logistic.inventory.process');
            }
         }

    }

    public function datatables(Request $request)
    {
        if (! Gate::allows('inventory')) {
            return abort(401);
        }


        $result  = DB::table('inventories')
        ->select('inventories.*',
        'master_item_products.name AS productName', 
        'master_item_brands.name AS productBrand',
        'master_item_products.code AS productCode', 
        'master_item_products.part_number AS productPartNumber',
        'locations.name AS location',
        'companies.alias AS companyCode',
        'measures.name AS unit')
        ->leftJoin('locations', 'locations.id', '=', 'inventories.location_id')
        ->leftJoin('master_item_products', 'master_item_products.id', '=', 'inventories.product_id')
        ->leftJoin('master_item_brands', 'master_item_brands.id', '=', 'master_item_products.brand_id')
        ->leftJoin('measures', 'measures.id', '=', 'inventories.measure_id')
        ->when(!empty($request->get('amp;location_id')), function ($query) use ($request) {
            return $query->where('inventories.location_id',$request->get('amp;location_id'));
        })
        ->when(!empty($request->get('amp;item_id')), function ($query) use ($request) {
            return $query->where('master_item_products.item_id',$request->get('amp;item_id'));
        })
        ->when(!empty($request->get('status')), function ($query) use ($request) {
            return $query->where('inventories.stock_status',$request->get('status'));
        });
        if (isAdministratorCompany()) {
            $result->leftJoin('companies', 'companies.id', '=', 'locations.company_id')
            ->where('locations.company_id', Auth::user()->company_id)
            ->where('inventories.is_local', false);
        }elseif(isAdministratorLocation() || isEmployee() ){
            $location = Auth::user()->location_id;
            $result->leftJoin('companies', 'companies.id', '=', 'locations.company_id')
            ->where('inventories.location_id',$location )
            ->where('inventories.is_local', false);
        } else{
            $result->leftJoin('companies', 'companies.id', '=', 'locations.company_id')
            ->where('inventories.is_local', false);
        }

       return  DataTables::of($result)
        ->addColumn('action', function ($result) {

            if($result->status == 3){
                $action = "<a href='".route('logistic.inventory.history', ['id' => Hashids::encode($result->id)])."' class='dropdown-item'><span class='ti-server mr-2'></span> Kartu Stock</a>";
                if(Gate::allows('writeoff')){
                    $action .= "<a href='".route('logistic.inventory.writeoff', ['id' => Hashids::encode($result->id)])."' class='dropdown-item'><span class='fa fa-eraser text-danger mr-2'></span> Write Off</a>";
                }
            }else{
                $action = "<a href='".route('logistic.inventory.edit', Hashids::encode($result->id))."' class='dropdown-item'><span class='ti-pencil mr-2'></span> Edit</a>";
                if(Gate::allows('adjustment')){
                    $action .= "<a href='".route('logistic.inventory.adjustment', ['id' => Hashids::encode($result->id)])."' class='dropdown-item'><span class='ti-write mr-2'></span> Adjustment</a>";
                }
                if(Gate::allows('writeoff')){
                    $action .= "<a href='".route('logistic.inventory.writeoff', ['id' => Hashids::encode($result->id)])."' class='dropdown-item'><span class='fa fa-eraser text-danger mr-2'></span> Write Off</a>";
                }
                $action .= "<a href='".route('logistic.inventory.history', ['id' => Hashids::encode($result->id)])."' class='dropdown-item'><span class='ti-server mr-2'></span> Kartu Stock</a>";
            }

            $url = 'printExternal("'.route('logistic.inventory.print_qr',['id' => Hashids::encode($result->id)]).'")';

            $action .= "<a href='#' title='Print Data' onclick='".$url."' class='dropdown-item' ><span class='ti-search mr-2'></span> Print QR</a>";

            return
            '<div class="dropdown">
                <a class="btn btn-outline bd dropdown-toggle" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                Aksi
                </a>
                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">'
                    .$action.
                '</div>
            </div>';
        })
        ->editColumn('price', function ($result) {
            return "<span class='currency'>".number_format($result->price,2,".",',')."</span>";
        })
        ->editColumn('location', function ($result) {
            if (isAdministrator()) return $result->location.'-'.$result->companyCode;
            else return $result->location;
        })
        ->editColumn('status', function ($result) {
            if($result->status == 3){
                return "<label class='badge badge-primary'>Write Off</label>";
            }else{
               return statusInventory($result->stock_status);
            }
        })
        ->editColumn('productName', function ($result) {
            if($result->kind == 'Fast Moving'){
                $kind = "<label class='badge badge-success mr-2' style='padding: .1em .4em;' data-toggle='tooltip' title='Fast Moving' >&nbsp;</label>";
            }elseif($result->kind == 'Dead Stock'){
                $kind = "<label class='badge badge-dark mr-2' style='padding: .1em .4em;' data-toggle='tooltip' title='Dead Stock' >&nbsp;</label>";
            }else{
                $kind = "<label class='badge badge-danger mr-2' style='padding: .1em .4em;' data-toggle='tooltip' title='Slow Moving' >&nbsp;</label>";
            }
            $brand  =  $result->productBrand != NULL ? ' Brand: '.$result->productBrand : 'Brand: -';
            return $kind.$result->productName."<br><small>". $brand."</small>";
        })
        ->editColumn('updated_at', function ($result) {
            return $result->updated_at ? with(new Carbon($result->updated_at))->format('d/m/Y H:i:s') : '';
        })
        ->rawColumns(['action', 'status','productName','kind','price'])
        ->make(true);

    }

    public function create()
    {
        if (! Gate::allows('inventory')) {
            return abort(401);
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
            $location = DB::table('locations')->where('id', Auth::user()->location_id)->orderBy('name','ASC')->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
        }

        $item       = DB::table('master_items')->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');

        $kind_inv = array('Slow Moving','Fast Moving','Dead Stock');
        $kind = [];

        foreach($kind_inv as $val){
            $kind[$val]= $val;
        }
        return view('logistic.inventory.create',compact('location','item','kind'));
    }



    public function store(Request $request)
    {
        if (! Gate::allows('inventory')) {
            return abort(401);
        }

        $query = Inventory::where('product_id', $request->get('product_id'))
        ->where('location_id', $request->get('location_id'))
        ->first();

        if($query){
            return redirect()->back()
            ->withInput($request->input())
            ->withErrors(['Terdapat Data Inventory yang sama']);
        }else{

            $data = $request->all();
            $data['price']         =  str_replace(",", "", $request->get('price'));
            $data['created_by']      = Auth::user()->id;
            $data['initial']         = $request->get('stock_onhand');
            $data['stock_status']    = getStatusInventory( $request->get('stock_max'),  $request->get('stock_min'), $request->get('stock_onhand'),'raw');
            $data['uuid']            = Str::uuid();

            DB::beginTransaction();

            try {
                $inv = Inventory::create($data);

                $invHistory = [
                    'inventory_id'  => $inv->id,
                    'qty_in'        => $request->get('stock_onhand'),
                    'qty_awal'      => 0,
                    'message'       => $request->get('description'),
                    'description'   => "Input Stok Awal bedasarkan ". $request->get('description')
                ];

                InventoryHistory::insert($invHistory);
                DB::commit();
                return redirect()->route('logistic.inventory.index')->with(['success' => 'Add was successful!']);
            } catch (\Exception $e) {

                DB::rollback();
                return redirect()->back()->withInput()->withErrors(['error' => $e->getMessage()]);
            }

        }

    }


    public function edit($id)
    {
        if (! Gate::allows('inventory')) {
            return abort(401);
        }

        $id = Hashids::decode($id);

        $inventory = DB::table('inventories')
        ->select('inventories.*',
        'master_item_products.name AS productName', 
        'master_item_products.code AS productCode',
        'master_item_products.part_number AS productPartNumber',
        'locations.id AS locationID',
        'locations.name AS location',
        'measures.name AS unit')
        ->leftJoin('locations', 'locations.id', '=', 'inventories.location_id')
        ->leftJoin('master_item_products', 'master_item_products.id', '=', 'inventories.product_id')
        ->leftJoin('measures', 'measures.id', '=', 'inventories.measure_id')
        ->where('inventories.id', $id['0'])
        ->first();

        $kind_inv = array('Slow Moving','Fast Moving','Dead Stock');
        $kind = [];

        foreach($kind_inv as $val){
            $kind[$val]= $val;
        }
        return view('logistic.inventory.edit',compact('inventory','kind'));
    }

    public function update(Request $request, $id)
    {
        if (! Gate::allows('inventory')) {
            return abort(401);
        }
        $inventory = Inventory::findOrFail($id);

        $data = $request->all();
        $data['price']          = str_replace(",", "", $request->get('price'));
        $data['updated_by']     = Auth::user()->id;
        if($request->get('kind') != "Dead Stock"){
            $data['notes'] = null;
        }
        if($inventory->uuid == NULL){
            $data['uuid'] = Str::uuid();
        }
        $data['stock_status']   = getStatusInventory($request->get('stock_max'), $request->get('stock_min'), $inventory->stock_onhand,'raw');
        $inventory->update($data);

        return redirect()->route('logistic.inventory.index')->with(['success' => 'Edit was successful!']);

    }

    public function search(Request $request)
    {
        if (! Gate::allows('inventory')) {
            return abort(401);
        }
        $query = 'status='.$request->get('status').'&item_id='. $request->get('item_id')."&location_id=".$request->get('location_id');
        $data = $request->all();
        $search = "Cari Berdasarkan: ";

        if($request->get('location_id')) $search .= "<strong> Lokasi: </strong>".getDataByID('locations',$request->get('location_id'))->name;
        if($request->get('item_id')) $search .= "<strong> Lokasi: </strong>".getDataByID('master_items',$request->get('item_id'))->name;
        if($request->get('status')) $search .= "<strong> Status: </strong>".$request->get('status');

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

        $item   = DB::table('master_items')->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');

        return view('logistic.inventory.search', compact('data','location', 'search','item','query'));
    }


    public function export(Request $request)
    {
        $data = $request->all();

        $query = DB::table('inventories')
        ->select('inventories.*',
        'master_item_products.name AS productName', 
        'master_item_products.code AS productCode', 
        'master_item_products.part_number AS productPartNumber',
        'master_item_brands.name AS productBrand',
        'measures.name AS unit',
        'locations.name AS location',
        DB::raw('ROW_NUMBER () OVER (ORDER BY inventories.id) as number'))
        ->leftJoin('master_item_products', 'master_item_products.id', '=', 'inventories.product_id')
        ->leftJoin('master_item_brands', 'master_item_brands.id', '=', 'master_item_products.brand_id')
        ->leftJoin('locations', 'locations.id', '=', 'inventories.location_id')
        ->leftJoin('measures', 'measures.id', '=', 'inventories.measure_id')
        ->when(!empty($data['location_id']), function ($query) use ($data) {
            return $query->where('inventories.location_id',$data['location_id']);
        })
        ->when(!empty($data['item_id']), function ($query) use ($data) {
            return $query->where('master_item_products.item_id',$data['item_id']);
        })
        ->when(!empty($data['status']), function ($query) use ($data) {
            return $query->where('inventories.stock_status',$data['status']);
        })
        ->where('inventories.is_local', false)
        ->get();


        if( $query->isEmpty() ){
            return redirect()->route('logistic.inventory.index')->with(['error' => 'Tidak terdapat data untuk di Export']);
        }else{

            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="your_name.xls"');
            header('Cache-Control: max-age=0');
            return (new FastExcel($query))->download('inventory-'.date('d-m-Y').'.xlsx', function ($inv) {
                return [
                    'No'            => $inv->number,
                    'Nomor Rak'     => $inv->code_rack,
                    'Kode Produk'   => $inv->productCode,
                    'Nama Produk'   => $inv->productName,
                    'PN/SPEC'   => $inv->productPartNumber,
                    'Brand'         => $inv->productBrand,
                    'Satuan'        => $inv->unit,
                    'Saldo Awal'    => $inv->initial,
                    'In'            => $inv->in,
                    'Out'           => $inv->out,
                    'SOH'           => $inv->stock_onhand,
                    'Min'           => $inv->stock_min,
                    'Max'           => $inv->stock_max,
                    'Harga Satuan'  => currencyRupiahFormat($inv->price),
                    'Harga Satuan Sebelum Diskon' => currencyRupiahFormat(($inv->price_after_discount != '0') ? $inv->price_after_discount : $inv->price),
                    'Status'        => statusInventory($inv->stock_status,'raw'),
                    'Lokasi'        => $inv->location,
                    'Tipe Barang'   => $inv->kind,
                    'Keterangan'    => $inv->notes,
                ];
            });
        }


    }

    public function export_aging(Request $request)
    {
        $data = $request->all();

        $query = DB::table('inventories')
        ->select('inventories.*',
            'master_item_products.name AS productName',
            'master_item_products.code AS productCode', 
            'master_item_products.part_number AS productPartNumber',
            'master_item_brands.name AS productBrand',
            'measures.name AS unit',
            'locations.name AS location',
            'inventory_histories.message as inventory_history_doc_no',
            'inventory_histories.created_at as inventory_history_created',
        DB::raw('ROW_NUMBER () OVER (ORDER BY inventories.id) as number'))
        ->leftJoin('master_item_products', 'master_item_products.id', '=', 'inventories.product_id')
        ->leftJoin('master_item_brands', 'master_item_brands.id', '=', 'master_item_products.brand_id')
        ->leftJoin('locations', 'locations.id', '=', 'inventories.location_id')
        ->leftJoin('measures', 'measures.id', '=', 'inventories.measure_id')
        ->leftJoin('inventory_histories', function($query) {
            $query->on('inventory_histories.inventory_id','=','inventories.id')
                ->whereRaw("inventory_histories.id IN (select MAX(ih.id) from inventory_histories as ih join inventories as inv on inv.id = ih.inventory_id where substring(ih.message,1,3)='BPB' group by inv.id)");
        })
        ->when(!empty($data['location_id']), function ($query) use ($data) {
            return $query->where('inventories.location_id',$data['location_id']);
        })
        ->when(!empty($data['item_id']), function ($query) use ($data) {
            return $query->where('master_item_products.item_id',$data['item_id']);
        })
        ->when(!empty($data['status']), function ($query) use ($data) {
            return $query->where('inventories.stock_status',$data['status']);
        })
        ->where('inventories.is_local', false)
        ->get();


        if( $query->isEmpty() ){
            return redirect()->route('logistic.inventory.index')->with(['error' => 'Tidak terdapat data untuk di Export']);
        }else{

            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="your_name.xls"');
            header('Cache-Control: max-age=0');
            setlocale(LC_TIME, 'id_ID.utf8');
            return (new FastExcel($query))->download('Aging-inventory-'.date('d-m-Y').'.xlsx', function ($inv) {
                return [
                    'No'                                    => $inv->number,
                    'Nomor Rak'                             => $inv->code_rack,
                    'Kode Produk'                           => $inv->productCode,
                    'Nama Produk'                           => $inv->productName,
                    'PN/SPEC'                           => $inv->productPartNumber,
                    'Brand'                                 => $inv->productBrand,
                    'Satuan'                                => $inv->unit,
                    'Saldo Awal'                            => $inv->initial,
                    'In'                                    => $inv->in,
                    'Out'                                   => $inv->out,
                    'SOH'                                   => $inv->stock_onhand,
                    'Min'                                   => $inv->stock_min,
                    'Max'                                   => $inv->stock_max,
                    'Harga Satuan'                          => currencyRupiahFormat($inv->price),
                    'Harga Satuan Sebelum Diskon'    => currencyRupiahFormat(($inv->price_after_discount != '0') ? $inv->price_after_discount : $inv->price),
                    'Status'                                => statusInventory($inv->stock_status,'raw'),
                    'Lokasi'                                => $inv->location,
                    'No BPB Terakhir'        => $inv->inventory_history_doc_no,
                    'Tgl BPB Terakhir'       => ($inv->inventory_history_created ? with(new Carbon($inv->inventory_history_created))->formatLocalized('%A, %d %B %Y Pukul %H:%M') : ''),
                    'Aging Inventory'        => agingInventory(Carbon::now()->diffInDays(new Carbon($inv->inventory_history_created)))
                ];
            });
        }


    }


    public function stock_opname(Request $request)
    {
        $data = $request->all();

        $query = DB::table('inventories')
        ->select('inventories.*',
            'master_item_products.name AS productname', 
            'master_item_products.code AS productcode', 
            'master_item_products.part_number AS productpartnumber',
            'master_item_brands.name AS productbrand',
            'measures.name AS unit',
            'master_items.code AS item_code',
            'master_items.name AS item_name')
        ->leftJoin('master_item_products', 'master_item_products.id', '=', 'inventories.product_id')
        ->leftJoin('master_item_brands', 'master_item_brands.id', '=', 'master_item_products.brand_id')
        ->leftJoin('master_items', 'master_items.id', '=', 'master_item_products.item_id')
        ->leftJoin('measures', 'measures.id', '=', 'inventories.measure_id')
        ->when(!empty($data['location_id']), function ($query) use ($data) {
            return $query->where('inventories.location_id',$data['location_id']);
        })
        ->when(!empty($data['item_id']), function ($query) use ($data) {
            return $query->where('master_items.id',$data['item_id']);
        })->get();


        $company = DB::table('locations')
        ->select('companies.name AS companyName','companies.logo AS companyLogo','companies.address AS companyAddress',
        'locations.name AS location'
        )
        ->leftJoin('companies', 'companies.id', '=', 'locations.company_id')
        ->leftJoin('inventories', 'inventories.location_id','=','location_id')
        ->where('locations.id', $data['location_id'])
        ->where('inventories.is_local', false)
        ->first();


        if( $query->isEmpty() ){
            return redirect()->route('logistic.inventory.index')->with(['error' => 'Tidak terdapat data untuk di Export']);
        }else{

            $location = getDataByID('locations',$request->input('location_id'));

            if($location){
                $location = $location->name;
            }else{
                $location = '';
            }

            $result = [];
            $result_item = [];

            foreach ($query as $element) {
                $result_item[$element->item_code] = [
                    'item_name'     => $element->item_name,
                    'item_code'     => $element->item_code,
                ];
                $result[$element->item_code][] =  $element ;
            }

            $spreadsheet = new Spreadsheet();
            $drawing = new Drawing();

            $sheet = $spreadsheet->getActiveSheet();
            $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
            $sheet->getPageSetup()->setScale(80);

            $sheet->getPageMargins()->setTop(0.24);
            $sheet->getPageMargins()->setRight(0.2);
            $sheet->getPageMargins()->setLeft(0.2);
            $sheet->getPageMargins()->setBottom(0.24);

            $sheet->getColumnDimension('A')->setWidth(5);
            $sheet->getColumnDimension('B')->setWidth(10);
            $sheet->getColumnDimension('C')->setWidth(15);
            $sheet->getColumnDimension('D')->setWidth(40);
            $sheet->getColumnDimension('E')->setWidth(30);
            $sheet->getColumnDimension('F')->setWidth(8);
            $sheet->getColumnDimension('G')->setWidth(10);
            $sheet->getColumnDimension('H')->setWidth(10);
            $sheet->getColumnDimension('I')->setWidth(10);

            if($company->companyLogo){
                $drawing->setPath('storage'.$company->companyLogo);
                $drawing->setCoordinates('B2');
                $drawing->setWorksheet($spreadsheet->getActiveSheet());
                $drawing->setWidthAndHeight(80, 80);
            }

            $sheet->setCellValue('C2', strtoupper($company->companyName));
            $sheet->getStyle('C2')->getFont()->setBold(true)->setSize(14);
            $sheet->setCellValue('C3', $company->companyAddress);
            $sheet->setCellValue('C4', 'Site: '. $company->location);

            $sheet->setCellValue('G2', 'BLANGKO STOCK OPNAME');
            $sheet->getStyle('G2')->getFont()->setBold(true)->setSize(14)->setUnderline(true);
            $sheet->mergeCells('G2:J2');
            $sheet->getStyle('G2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);

            $sheet->setCellValue('G3', 'Periode: '.date('d M Y'));
            $sheet->getStyle('G3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
            $sheet->mergeCells('G3:J3');

            $sheet->getStyle('A7:J7')->getFont()->setBold(true);
            $sheet->getStyle('A8:J8')->getFont()->setBold(true);

            $sheet->mergeCells('H7:I7');
            $sheet->setCellValue('H7', 'STOCK OPNAME');

            $sheet->mergeCells('A7:A8');
            $sheet->mergeCells('B7:B8');
            $sheet->mergeCells('C7:C8');
            $sheet->mergeCells('D7:D8');
            $sheet->mergeCells('E7:E8');
            $sheet->mergeCells('F7:F8');
            $sheet->mergeCells('G7:G8');
            $sheet->mergeCells('J7:J8');

            $sheet->setCellValue('A7', 'NO');
            $sheet->setCellValue('B7', 'LOKASI');
            $sheet->setCellValue('C7', 'KODE');
            $sheet->setCellValue('D7', 'NAMA BARANG');
            $sheet->setCellValue('E7', 'SPESIFIKASI');
            $sheet->setCellValue('F7', 'STN');
            $sheet->setCellValue('G8', 'ADMIN');
            $sheet->setCellValue('H8', 'PHISIK');
            $sheet->setCellValue('I7', '+/-');

            $sheet->getStyle('A7')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('A7')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            $sheet->getStyle('B7')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('B7')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            $sheet->getStyle('C7')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('C7')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            $sheet->getStyle('D7')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('D7')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            $sheet->getStyle('E7')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('E7')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            $sheet->getStyle('F7')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('F7')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            $sheet->getStyle('G7')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('G7')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            $sheet->getStyle('J7')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('J7')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

            $sheet->getStyle('H7')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('H8')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('I8')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            $rows = 9;
            foreach($result_item as $val){

                $sheet->setCellValue('A' . $rows, $val['item_code']." - ".$val['item_name']);
                $sheet->getStyle('A'. $rows.':J'. $rows)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
                $sheet->getStyle('A'. $rows.':J'. $rows)->getFill()->getStartColor()->setARGB('E6EDF5');
                $sheet->getStyle('A'. $rows)->getFont()->setBold(true);

                $rows_item = $rows + 1;
                $i = 1;

                foreach($result[$val['item_code']] as $item){

                    $sheet->setCellValue('A' . $rows_item, $i);
                    $sheet->setCellValue('B' . $rows_item, $rows_item);
                    $sheet->setCellValue('B' . $rows_item, $item->code_rack);
                    $sheet->setCellValue('C' . $rows_item, $item->productcode);
                    $sheet->setCellValue('D' . $rows_item, $item->productname);
                    $sheet->setCellValue('E' . $rows_item, $item->productpartnumber);
                    $sheet->setCellValue('F' . $rows_item, $item->unit);
                    $sheet->setCellValue('G' . $rows_item, $item->stock_onhand);
                    $sheet->setCellValue('H' . $rows_item, '');
                    $rows_item++;
                    $i++;
                }

                $sheet->mergeCells('A'. $rows_item.':H'. $rows_item);
                $rows = $rows_item+1;
            }

            $writer = new Xlsx($spreadsheet);
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="Daftar Stock Opname -'.date('Y-m-d').'.xlsx"');
            header('Cache-Control: max-age=0');
            $writer->save("php://output");
            die();
        }


    }



    public function proses(Request $request)
    {
        $month = $request->input('month');

        $last_month = date("F", strtotime("first day of previous month"));
        $cur_month = date("F");

        $data['created_by'] = Auth::user()->id;
        $data['location_id']= $request->get('location_id');
        $data['month']      = $request->input('month');
        $data['year']       = date("Y");
        $data['created_at'] = date("Y-m-d H:i:s");

        DB::beginTransaction();
        try {
            InventoryProcess::create($data);

            InventoryProcess::process($request->get('location_id'));

            DB::commit();
            return redirect()->route('logistic.inventory.index')->with(['success' => 'Perpindahan Saldo dari Bulan '. $last_month.' ke '.$cur_month.' berhasil']);
        } catch (\Exception $e) {

            DB::rollback();
            return redirect()->back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    // ADJ

    public function adjustment($id)
    {
        if (! Gate::allows('adjustment')) {
            return abort(401);
        }
        $id = Hashids::decode($id);

        $inventory = DB::table('inventories')
        ->select('inventories.*',
        'master_item_products.name AS productName', 
        'master_item_products.code AS productCode', 
        'master_item_products.part_number AS productPartNumber',
        'locations.id AS locationID',
        'locations.name AS location',
        'measures.name AS unit')
        ->leftJoin('locations', 'locations.id', '=', 'inventories.location_id')
        ->leftJoin('master_item_products', 'master_item_products.id', '=', 'inventories.product_id')
        ->leftJoin('measures', 'measures.id', '=', 'inventories.measure_id')
        ->where('inventories.id', $id['0'])
        ->first();

        return view('logistic.inventory.adjustment',compact('inventory'));
    }

    public function adjustmentDatatables($id)
    {
        if (! Gate::allows('adjustment')) {
            return abort(401);
        }
        $id = Hashids::decode($id);

        $result  = DB::table('inventory_adjustments')
        ->select('inventory_adjustments.*')
        ->where('inventory_adjustments.inventory_id',$id['0']);

       return  DataTables::of($result)
        ->editColumn('created_at', function ($result) {
            return $result->created_at ? with(new Carbon($result->created_at))->format('d/m/Y') : '';
        })
        ->addColumn('action', function ($result) {
            $url = 'printExternal("/logistic/adjustment_print/'.Hashids::encode($result->id).'")';
            $url_print = "<a onclick='".$url."' data-toggle='Print' class='btn btn-outline'><span class='ti-printer icon-lg'></span> </a>";
            $url_download = "<a download href='".Storage::url($result->file)."' data-toggle='Download BA' class='btn btn-outline'><span class='fa fa-file-pdf-o icon-lg text-danger'></span> </a>";

            if($result->file != NULL){
                return $url_print. $url_download;
            }else{
                return $url_print;
            }
        })
        ->make(true);

    }

    public function getAdjustment($id)
    {
        if (! Gate::allows('adjustment')) {
            return abort(401);
        }

        $id = Hashids::decode($id);

        $inventory = DB::table('inventories')
        ->select('inventories.*',
        'master_item_products.name AS productName', 'master_item_products.code AS productCode', 'master_item_products.part_number AS productPartNumber',
        'locations.id AS locationID','locations.name AS location','measures.name AS unit')
        ->leftJoin('locations', 'locations.id', '=', 'inventories.location_id')
        ->leftJoin('master_item_products', 'master_item_products.id', '=', 'inventories.product_id')
        ->leftJoin('measures', 'measures.id', '=', 'inventories.measure_id')
        ->where('inventories.id', $id['0'])
        ->first();

        return view('logistic.inventory.adjustment_form', compact('inventory'));
    }

    public function storeAdjustment(Request $request)
    {
        if (! Gate::allows('adjustment')) {
            return abort(401);
        }

        $increment = DB::table('inventory_adjustments')
        ->leftJoin('inventories', 'inventories.id', '=', 'inventory_adjustments.inventory_id')
        ->whereYear("inventory_adjustments.created_at",  date('Y'))
        ->where('inventories.location_id',$request->get('location_id'))
        ->get();

        $code = DB::table('locations')
        ->select('locations.alias AS locationCode', 'companies.alias AS companyCode')
        ->leftJoin('companies', 'companies.id', '=', 'locations.company_id')
        ->where('locations.id',$request->get('location_id'))
        ->first();

        $num = sprintf("%'.05d", count($increment) + 1) ;
        $no = "ADJ-".$code->companyCode."-".$code->locationCode."-".date('my')."-".$num;
        $data['inventory_id']   = $request->get('inventory_id');
        $data['reason']         = $request->get('reason');
        $data['doc_no']         = $no;
        $data['qty_awal']       = $request->get('qty_awal');
        $data['operator']       = $request->get('operator');
        $data['qty_fisik']      = $request->get('qty_fisik');
        $data['created_by']     = Auth::user()->id;

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $name = 'adj_'.time();
            $folder = '/uploads/inventory/'.date('Y').'/'.date('M').'/';
            $filePath = $folder . $name. '.' . $file->getClientOriginalExtension();
            $this->uploadOne($file, $folder, 'public', $name);
            $data['file'] = $filePath;
        }
        DB::beginTransaction();

        try {
            $inventory  = Inventory::findOrFail($request->get('inventory_id'));
            $adjustment = InventoryAdjustment::insert($data);

            $dataInv['stock_onhand'] = $request->get('qty_fisik');
            if($request->get('qty_fisik') > $request->get('qty_awal') ){
                $dataInv['in'] = $inventory->in + $request->get('qty_fisik') - $request->get('qty_awal');
            }else{
                $dataInv['out']= $inventory->out + $request->get('qty_awal') - $request->get('qty_fisik');
            }
            $dataInv['stock_status']   = getStatusInventory($inventory->stock_max, $inventory->stock_min, $request->get('qty_fisik'),'raw');
            $inventory->update($dataInv);

            if($request->get('qty_fisik') > $request->get('qty_awal') ){
                $invHistory = [
                    'inventory_id'  => $request->get('inventory_id'),
                    'qty_in'        => $request->get('qty_fisik') - $request->get('qty_awal'),
                    'qty_awal'      => $request->get('qty_awal'),
                    'message'       => $no,
                    'description'   => $request->get('reason')
                ];
            }else{
                $invHistory = [
                    'inventory_id'  => $request->get('inventory_id'),
                    'qty_out'       => $request->get('qty_awal') - $request->get('qty_fisik'),
                    'qty_awal'      => $request->get('qty_awal'),
                    'message'       => $no,
                    'description'   => $request->get('reason')
                ];
            }

            InventoryHistory::insert($invHistory);

            DB::commit();
            return redirect()->route('logistic.inventory.adjustment',['id' => Hashids::encode($request->get('inventory_id'))])->with(['success' => 'Berhasil melakukan Adjustment Stock!']);
        } catch (\Exception $e) {

            DB::rollback();
            return redirect()->back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function getPrintAdjustment($id)
    {
        if (! Gate::allows('adjustment')) {
            return abort(401);
        }

        $id = Hashids::decode($id);

        $inventory = DB::table('inventory_adjustments')
        ->select('inventories.code_rack','inventories.id As inv_id', 'inventory_adjustments.*',
        'master_item_products.name AS productName', 'master_item_products.code AS productCode', 'master_item_products.part_number AS productPartNumber','measures.name AS unit',
        'locations.name AS location','locations.address AS locationAddress','locations.telp AS locationTelp','companies.name AS company','companies.logo AS companyLogo')
        ->leftJoin('inventories', 'inventories.id', '=', 'inventory_adjustments.inventory_id')
        ->leftJoin('master_item_products', 'master_item_products.id', '=', 'inventories.product_id')
        ->leftJoin('measures', 'measures.id', '=', 'inventories.measure_id')
        ->leftJoin('locations', 'locations.id', '=', 'inventories.location_id')
        ->leftJoin('companies', 'companies.id', '=', 'locations.company_id')
        ->where('inventory_adjustments.id', $id['0'])
        ->first();
        return view('logistic.inventory.adjustment_print', compact('inventory'));
    }

    // WO

    public function writeoff($id)
    {
        if (! Gate::allows('writeoff')) {
            return abort(401);
        }

        $id = Hashids::decode($id);

        $inventory = DB::table('inventories')
        ->select('inventories.*',
        'master_item_products.name AS productName', 'master_item_products.code AS productCode', 'master_item_products.part_number AS productPartNumber',
        'locations.id AS locationID','locations.name AS location','measures.name AS unit')
        ->leftJoin('locations', 'locations.id', '=', 'inventories.location_id')
        ->leftJoin('master_item_products', 'master_item_products.id', '=', 'inventories.product_id')
        ->leftJoin('measures', 'measures.id', '=', 'inventories.measure_id')
        ->where('inventories.id', $id['0'])
        ->first();

        $wo = DB::table('inventory_writeoffs')
        ->select('inventory_writeoffs.*')
        ->where('inventory_writeoffs.inventory_id', $id['0'])
        ->first();

        return view('logistic.inventory.writeoff', compact('inventory','wo'));
    }

    public function storeWriteOff(Request $request)
    {
        if (! Gate::allows('inventory')) {
            return abort(401);
        }

        $increment = DB::table('inventory_writeoffs')
        ->leftJoin('inventories', 'inventories.id', '=', 'inventory_writeoffs.inventory_id')
        ->whereYear("inventory_writeoffs.created_at",  date('Y'))
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
        $no = "WO-".$company."-".$location->alias."-".date('my')."-".$num;
        $data['inventory_id']   = $request->get('inventory_id');
        $data['reason']         = $request->get('reason');
        $data['operator']       = $request->get('operator');
        $data['doc_no']         = $no;
        $data['created_by']     = Auth::user()->id;

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $name = 'wo_'.time();
            $folder = 'uploads/inventory/'.date('Y').'/'.date('M').'/';
            $filePath = $folder . $name. '.' . $file->getClientOriginalExtension();
            $this->uploadOne($file, $folder, 'public', $name);
            $data['file'] = $filePath;
        }

        DB::beginTransaction();
        try {

            $writeoff = InventoryWriteOff::insert($data);
            $inventory = Inventory::findOrFail($request->get('inventory_id'));

            $dataInv['stock_onhand']   = 0;
            $dataInv['out']   = $inventory->out+$request->get('stock');
            $dataInv['status']   = 3;
            $inventory->update($dataInv);

            $invHistory = [
                'inventory_id'  => $request->get('inventory_id'),
                'qty_out'       => $request->get('stock'),
                'qty_awal'      => $request->get('stock'),
                'message'       => $no,
                'description'   => $request->get('reason')
            ];

            InventoryHistory::insert($invHistory);

            DB::commit();
            return redirect()->route('logistic.inventory.writeoff',['id' => Hashids::encode($request->get('inventory_id')) ])->with(['success' => 'Berhasil melakukan Write Off Stock!']);
        } catch (\Exception $e) {

            DB::rollback();
            return redirect()->back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function getPrintWriteOff($id)
    {
        if (! Gate::allows('writeoff')) {
            return abort(401);
        }

        $id = Hashids::decode($id);

        $inventory = DB::table('inventory_writeoffs')
        ->select('inventories.code_rack','inventories.id As inv_id',
         'inventory_writeoffs.*',
        'master_item_products.name AS productName', 'master_item_products.code AS productCode', 'master_item_products.part_number AS productPartNumber','measures.name AS unit',
        'locations.name AS location','locations.address AS locationAddress','locations.telp AS locationTelp','companies.name AS company','companies.logo AS companyLogo')
        ->leftJoin('inventories', 'inventories.id', '=', 'inventory_writeoffs.inventory_id')
        ->leftJoin('master_item_products', 'master_item_products.id', '=', 'inventories.product_id')
        ->leftJoin('measures', 'measures.id', '=', 'inventories.measure_id')
        ->leftJoin('locations', 'locations.id', '=', 'inventories.location_id')
        ->leftJoin('companies', 'companies.id', '=', 'locations.company_id')
        ->where('inventory_writeoffs.id', $id['0'])
        ->first();

        return view('logistic.inventory.writeoff_print', compact('inventory'));
    }


    // HISTORY

    public function history($id)
    {
        if (! Gate::allows('inventory')) {
            return abort(401);
        }
        $id = Hashids::decode($id);

        $inventory = DB::table('inventories')
        ->select('inventories.*',
        'master_item_products.name AS productName', 
        'master_item_products.code AS productCode', 
        'master_item_products.part_number AS productPartNumber',
        'locations.id AS locationID',
        'locations.name AS location',
        'companies.name AS company',
        'measures.name AS unit')
        ->leftJoin('locations', 'locations.id', '=', 'inventories.location_id')
        ->leftJoin('companies', 'companies.id', '=', 'locations.company_id')
        ->leftJoin('master_item_products', 'master_item_products.id', '=', 'inventories.product_id')
        ->leftJoin('measures', 'measures.id', '=', 'inventories.measure_id')
        ->where('inventories.id', $id['0'])
        ->first();

        return view('logistic.inventory.history',compact('inventory'));
    }

    public function historyDatatables($id)
    {
        if (! Gate::allows('inventory')) {
            return abort(401);
        }
        $id = Hashids::decode($id);

        $result = Inventory::getHistoryStock($id['0']);

        return  DataTables::of($result)
        ->editColumn('stock_awal', function ($result) {
            return format_number($result->stock_awal);
        })
        ->editColumn('qty_in', function ($result) {
            return format_number($result->qty_in);
        })
        ->editColumn('qty_out', function ($result) {
            return format_number($result->qty_out);
        })
        ->editColumn('stock', function ($result) {
            return format_number($result->stock_awal+$result->qty_in-$result->qty_out);
        })
        ->editColumn('created_at', function ($result) {
            return $result->created ? with(new Carbon($result->created))->format('d M Y  H:i') : '';
        })
        ->make(true);

    }

    public function history_export(Request $request,$id)
    {


        $inventory = DB::table('inventories')
        ->select('inventories.*',
        'master_item_products.name AS productName', 'master_item_products.code AS productCode', 'master_item_products.part_number AS productPartNumber',
        'locations.id AS locationID','locations.name AS location','measures.name AS unit')
        ->leftJoin('locations', 'locations.id', '=', 'inventories.location_id')
        ->leftJoin('master_item_products', 'master_item_products.id', '=', 'inventories.product_id')
        ->leftJoin('measures', 'measures.id', '=', 'inventories.measure_id')
        ->where('inventories.id', $id)
        ->first();

        $inventory_history = Inventory::getHistoryStock($id, $request->input('start_date'), $request->input('end_date'));

        $styleArrayTabel = array(
        'alignment' => array(
                'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    'rotation'   => 0,
                    'wrap'       => true
        ),
        'borders' => array(
            'allBorders' => array(
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, //BORDER_THIN BORDER_MEDIUM BORDER_HAIR
                    'color' => array('rgb' => '000000')
            )
            )
        );


        $styleArrayItem = array(
            'alignment' => array(
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    'rotation'   => 0,
                    'wrap'       => true
            ),
            'borders' => array(
                'allBorders' => array(
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, //BORDER_THIN BORDER_MEDIUM BORDER_HAIR
                        'color' => array('rgb' => '000000')
                )
                )
        );

        $styleArrayBorder = [
            'borders' => [
                'outline' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => array('rgb' => '000000')
                ],
            ],
        ];

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
        $sheet->getPageSetup()->setScale(80);

        $sheet->getPageMargins()->setTop(0.24);
        $sheet->getPageMargins()->setRight(0.2);
        $sheet->getPageMargins()->setLeft(0.2);
        $sheet->getPageMargins()->setBottom(0.24);

        $sheet->getColumnDimension('A')->setWidth(4);
        $sheet->getColumnDimension('B')->setWidth(16);
        $sheet->getColumnDimension('C')->setWidth(25);
        $sheet->getColumnDimension('D')->setWidth(25);
        $sheet->getColumnDimension('E')->setAutoSize(true);
        $sheet->getColumnDimension('F')->setAutoSize(true);
        $sheet->getColumnDimension('G')->setAutoSize(true);
        $sheet->getColumnDimension('H')->setAutoSize(true);

        $sheet->mergeCells('A2:H2');
        $sheet->setCellValue('A2', 'KARTU STOCK - '. $inventory->location);
        $sheet->getStyle('A2')->getFont()->setBold(true)->setUnderline(true);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->mergeCells('A3:H3');
        $sheet->setCellValue('A3', 'Periode: '.date('d M Y', strtotime($request->input('start_date'))).' - '. date('d M Y', strtotime($request->input('end_date'))));
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sheet->setCellValue('B6', 'Nomor Rak');
        $sheet->setCellValue('C6', ': '. $inventory->code_rack);

        $sheet->setCellValue('B7', 'Nama Produk');
        $sheet->setCellValue('C7', ': ['. $inventory->productCode.']'.$inventory->productName);

        $sheet->setCellValue('B8', 'PN/SPEC / Tipe');
        $sheet->setCellValue('C8', ': '. $inventory->productPartNumber);

        $sheet->setCellValue('B9', 'Stock On Hand');
        $sheet->setCellValue('C9', ': '. $inventory->stock_onhand);

        $sheet->getStyle('A11:H12')->getFont()->setBold(true);
        $sheet->getStyle('A11:H12')->applyFromArray($styleArrayItem);

        $sheet->setCellValue('A11', 'NO');
        $sheet->mergeCells('A11:A12');
        $sheet->setCellValue('B11', 'TGL INPUT');
        $sheet->mergeCells('B11:B12');
        $sheet->setCellValue('C11', 'NO. DOKUMEN');
        $sheet->mergeCells('C11:C12');
        $sheet->setCellValue('D11', 'DESKRIPSI');
        $sheet->mergeCells('D11:D12');
        $sheet->setCellValue('E11', 'QTY');
        $sheet->mergeCells('E11:H11');
        $sheet->getStyle('E11')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);


        $sheet->setCellValue('E12', 'STOCK AWAL');
        $sheet->getStyle('E12')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->setCellValue('F12', 'IN');
        $sheet->getStyle('F12')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->setCellValue('G12', 'OUT');
        $sheet->getStyle('G12')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->setCellValue('H12', 'STOCK ON HAND');
        $sheet->getStyle('H12')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $rows = 13;
        $i = 1;
        foreach($inventory_history as $items){
            $stock = $items->stock_awal+$items->qty_in-$items->qty_out;

            $sheet->setCellValue('A' . $rows, $i);
            $sheet->setCellValue('B' . $rows, date('d M Y H:i', strtotime($items->created)));
            $sheet->setCellValue('C' . $rows, $items->doc_no);
            $sheet->setCellValue('D' . $rows, $items->description);
            $sheet->setCellValue('E' . $rows, $items->stock_awal);
            $sheet->setCellValue('F' . $rows, $items->qty_in);
            $sheet->setCellValue('G' . $rows, $items->qty_out);
            $sheet->setCellValue('H' . $rows, $stock);

            $sheet->getStyle('C' . $rows)->getAlignment()->setWrapText(true);
            $sheet->getStyle('E' . $rows)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('F' . $rows)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('G' . $rows)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('H' . $rows)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('A' . $rows.':H'.$rows)->applyFromArray($styleArrayTabel);
            $rows++;
            $i++;
        }

        $sheet->setShowGridLines(false);
        $writer = new Xlsx($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="Kartu-Stock Produk ID-'.$inventory->product_id.'.xlsx"');
        header('Cache-Control: max-age=0');
        $writer->save("php://output");
        die();

    }

    public function getQty($product_id, $location_id)
    {
        $query = DB::table('inventories')
        ->select('inventories.stock_onhand','inventories.stock_min','inventories.stock_max','master_items.type', 'inventories.updated_at')
        ->leftJoin('master_item_products', 'master_item_products.id', '=', 'inventories.product_id')
        ->leftJoin('master_items', 'master_items.id', '=', 'master_item_products.item_id')
        ->where('inventories.product_id', $product_id)
        ->where('inventories.location_id', $location_id)
        ->first();
        if($query){
            return response()->json($query);
        }else{
            return 0;
        }
    }


    public function loadData($location_id = null,Request $request)
    {

        $query = DB::table('inventories')
        ->select('inventories.*',
            'master_item_products.name AS productName', 
            'master_item_products.code AS productCode',
            'master_item_products.part_number AS productPartNumber',
            'measures.name AS unit'
        )
        ->leftJoin('master_item_products', 'master_item_products.id', '=', 'inventories.product_id')
        ->leftJoin('measures', 'measures.id', '=', 'inventories.measure_id')
        ->where('master_item_products.name', 'ilike','%'.$request->q.'%')
        ->where('stock_onhand','!=',"0")
        ->where('location_id', $location_id)
        ->where(function ($query) use ($request) {
            $query->where('master_item_products.name', 'ilike','%'.$request->q.'%')
            ->orWhere('master_item_products.code', 'ilike','%'.$request->q.'%')
            ->orWhere('master_item_products.part_number', 'ilike','%'.$request->q.'%');
        })
        ->get();

        $result = array();
        foreach ($query as $val) {
            $result[] = array(
                'id'          => $val->id,
                'name'        => $val->productName,
                'code'        => $val->productCode,
                'rack'        => $val->code_rack,
                'part_number' => $val->productPartNumber,
                'unit'        => $val->unit,
                'stock'       => $val->stock_onhand,
                'stock_min'   => $val->stock_min,
                'stock_max'   => $val->stock_max,
                'location_id'   => $val->location_id
            );
        }
        return response()->json($result);
    }



    public function getData($location_id = null,Request $request)
    {

        $query = DB::table('inventories')
        ->select('inventories.*',
            'master_item_products.name AS productName', 
            'master_item_products.code AS productCode',
            'master_item_products.part_number AS productPartNumber',
            'measures.name AS unit'
        )
        ->leftJoin('master_item_products', 'master_item_products.id', '=', 'inventories.product_id')
        ->leftJoin('measures', 'measures.id', '=', 'inventories.measure_id')
        ->where('location_id', $location_id)
        ->where(function ($query) use ($request) {
            $query->where('master_item_products.name', 'ilike','%'.$request->q.'%')
            ->orWhere('master_item_products.code', 'ilike','%'.$request->q.'%')
            ->orWhere('master_item_products.part_number', 'ilike','%'.$request->q.'%');
        })
        ->get();

        $result = array();
        foreach ($query as $val) {
            $result[] = array(
                'id'          => $val->id,
                'name'        => $val->productName,
                'code'        => $val->productCode,
                'rack'        => $val->code_rack,
                'part_number' => $val->productPartNumber,
                'unit'        => $val->unit,
                'stock'       => $val->stock_onhand,
                'stock_min'   => $val->stock_min,
                'stock_max'   => $val->stock_max,
                'in'         => $val->in,
                'out'         => $val->out,
                'location_id' => $val->location_id
            );
        }
        return response()->json($result);
    }



    public function detail($uuid){

        $inv = DB::table('inventories')
        ->select('inventories.*','master_item_products.name AS productName', 'master_item_products.code AS productCode','master_item_products.part_number AS productPartNumber')
        ->leftJoin('master_item_products', 'master_item_products.id', '=', 'inventories.product_id')
        ->where('inventories.uuid', $uuid)
        ->first();

        return view('logistic.inventory.detail', compact('inv'));
    }


    public function print_qr(Request $request){

        if($request->get('type') == 'filtered'){
             $data = $request->all();

            $qr = DB::table('inventories')
            ->select('inventories.*','master_item_products.name AS productName', 'master_item_products.code AS productCode', 'master_item_products.part_number AS productPartNumber',
            DB::raw('ROW_NUMBER () OVER (ORDER BY inventories.id) as number'))
            ->leftJoin('master_item_products', 'master_item_products.id', '=', 'inventories.product_id')
            ->when(!empty($data['location_id']), function ($query) use ($data) {
                return $query->where('inventories.location_id',$data['location_id']);
            })
            ->when(!empty($data['item_id']), function ($query) use ($data) {
                return $query->where('master_item_products.item_id',$data['item_id']);
            })
            ->get();


        }elseif($request->get('type') == 'selected'){
            $invID = explode(',',$request->get('inv_id'));
            $qr = DB::table('inventories')
            ->select('inventories.*','master_item_products.name AS productName', 'master_item_products.code AS productCode','master_item_products.part_number AS productPartNumber')
            ->leftJoin('master_item_products', 'master_item_products.id', '=', 'inventories.product_id')
            ->whereIn('inventories.id',$invID)
            ->get();
        }else{
            $id = Hashids::decode($request->get('id'));

            $qr = DB::table('inventories')
            ->select('inventories.*','master_item_products.name AS productName', 'master_item_products.code AS productCode','master_item_products.part_number AS productPartNumber')
            ->leftJoin('master_item_products', 'master_item_products.id', '=', 'inventories.product_id')
            ->where('inventories.id', $id['0'])
            ->get();
        }

        return view('logistic.inventory.qr', compact('qr'));
    }


    public function getStock($product_id, $location_id)
    {

        $query = DB::table('inventories')
        ->select('inventories.stock_onhand','inventories.stock_min','inventories.stock_max','master_items.type', 'inventories.updated_at')
        ->leftJoin('master_item_products', 'master_item_products.id', '=', 'inventories.product_id')
        ->leftJoin('master_items', 'master_items.id', '=', 'master_item_products.item_id')
        ->where('inventories.product_id', $product_id)
        ->where('inventories.location_id', $location_id)
        ->orderBy('inventories.product_id','ASC')
        ->latest('inventories.id')
        ->first();
        return response()->json($query);
    }


    public function import(Request $request){

        if($request->isMethod('get')){
            $id      = Hashids::decode($request->id);
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
            return view('logistic.inventory.import', compact('location'));
        }else{

            $this->validate($request, array(
                'file'      => 'required'
            ));

            if($request->hasFile('file')){
                $extension = File::extension($request->file->getClientOriginalName());
                if ($extension == "xlsx" || $extension == "xls" || $extension == "csv") {

                    $file   = $request->file('file');
                    $userID = Auth::user()->id;

                    try {
                        Excel::import(new InventoryImport($request->get('location_id'), $userID), $file);
                    } catch (\Exception $e) {
                        return redirect()->back()
                        ->withInput($request->input())
                        ->withErrors($e->getMessage());
                    }
                    return redirect()->route('logistic.inventory.index')->with(['success' => 'Success inserting the data..']);

                } else {
                    return redirect()->route('logistic.inventory.index')->with(['error' =>' File is a '.$extension.' file.!! Please upload a valid xls/csv file..!!']);
                }
            }
        }
    }

}
