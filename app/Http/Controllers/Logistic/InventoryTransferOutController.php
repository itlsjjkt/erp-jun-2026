<?php

namespace App\Http\Controllers\Logistic;

use App\Models\Inventory;
use App\Models\InventoryHistory;
use App\Models\InventoryTransferOut;
use App\Models\InventoryTransferOutItem;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Vinkla\Hashids\Facades\Hashids;
use Yajra\DataTables\Facades\DataTables;
use Rap2hpoutre\FastExcel\FastExcel;
use App\Exports\InventoryTransferOutExport;
use App\Traits\UploadTrait;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use App\Mail\SendMailable;
use Illuminate\Support\Facades\Mail;
use Auth;
use Storage;
use Illuminate\Support\Facades\Redirect;
use App\Models\Locations;
use App\Models\InventoryTransferIn;
use App\Models\InventoryTransferInItem;
use App\Models\InventoryTtb;
use App\Models\InventoryTtbItem;

class InventoryTransferOutController extends Controller
{

    function __construct()
    {
        $this->middleware('permission:transfer');
    }

    use UploadTrait;
    /**
     * Display a listing of Items.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

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

        $type = array(
            ''  => 'Silahkan Pilih',
            '0' => 'PEMINDAHAN',
            '1' => 'PEMINJAMAN',
            '3' => 'PENGEMBALIAN',
            '2' => 'PENJUALAN'
        );

        return view('logistic.transfer_out.index',compact('location','type'));
    }

    public function datatables(Request $request)
    {
        $data = $request->all();

        if(isAdministrator() || isAdmin() ) $result  = InventoryTransferOut::getData($data);
        elseif(isAdministratorCompany() ) $result  = InventoryTransferOut::getData($data, Auth::user()->company_id);
        else $result = InventoryTransferOut::getData($data,null,Auth::user()->location_id);

        return  DataTables::of($result)
        ->addColumn('action', function ($result) {
            $url = 'printExternal("'.route('logistic.transfer_out.print', ['id' => Hashids::encode($result->id)]).'")';
            $url_print = "<a onclick='".$url."' data-toggle='Print' class='btn btn-outline' title='Cetak' data-toggle='tooltip'><span class='ti-printer icon-lg'></span> </a>";
            $url_show  = "<a href='".route('logistic.transfer_out.show',Hashids::encode($result->id))."'class='btn btn-outline' title='Detail' data-toggle='tooltip'><span class='ti-eye icon-lg'></span> </a>";
            $url_edit = "<a href='".route('logistic.transfer_out.edit', Hashids::encode($result->id))."' title='Edit' data-toggle='tooltip' class='btn btn-outline'><span class='ti-pencil icon-lg'></span> </a>";
            $url_approval = "<a href='".route('logistic.transfer_out.approval', ['id' => Hashids::encode($result->id)])."' title='Approval' data-toggle='tooltip' class='btn btn-outline'><span class='ti-thumb-up icon-lg'></span> </a>";
            if($result->status==0){
                return $url_edit.$url_show;
            }else if($result->status==1){
                if (Gate::allows('inventory_transfer_approval')) {
                    return $url_approval.$url_show.$url_print;
                }else{
                    return $url_show.$url_print;
                }
            }else{
                return $url_show.$url_print;
            }
        })
        ->addColumn('status', function ($result) {
            return getStatusTransferInventory($result->status);
        })
        ->editColumn('created_at', function ($result) {
            return $result->created_at ? with(new Carbon($result->created_at))->format('d/m/Y H:i:s') : '';
        })
        ->editColumn('type', function ($result) {
            return getTypeWto($result->type) ?? '';
        })
        ->rawColumns(['action', 'status'])
        ->make(true);

    }

    public function create(Request $request)
    {
        $type = array(
            0 => 'PEMINDAHAN',
            1 => 'PEMINJAMAN',
            2 => 'PENJUALAN'
        );

        $department = array(
            '' => 'Please select a location first'
        );
        $project  = DB::table('projects')->whereNull('deleted_at')->where('status',1)->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');

        $invID = explode(',',$request->get('inv_id'));

        $inventory = DB::table('inventories')
        ->select('inventories.*',
            'locations.name AS locationName',
            'locations.company_id',
            'locations.alias AS locationCode',
            'companies.alias AS companyCode',
            'master_item_products.name AS productName',
            'master_item_products.code AS productCode',
            'master_item_products.part_number AS productPartNumber',
            'measures.name AS unit',
            'measures.id AS measure_id',
            'spembelian.name AS unitpembelian',
            'master_item_products.conversion AS produkKonversi'
        )
        ->leftJoin('master_item_products', 'master_item_products.id', '=', 'inventories.product_id')
        ->leftJoin('measures AS spembelian', 'spembelian.id', '=', 'master_item_products.measure_id')
        ->leftJoin('locations', 'locations.id', '=', 'inventories.location_id')
        ->leftJoin('companies', 'companies.id', '=', 'locations.company_id')
        ->leftJoin('measures', 'measures.id', '=', 'inventories.measure_id')
        ->whereIn('inventories.id',$invID)
        ->get();

        $location = [];
        foreach($inventory as $item){
            $location [] = $item->location_id;
            $locationID  = $item->location_id;
            $companyCode = $item->companyCode;
            $companyID   = $item->company_id;
            $locationCode= $item->locationCode;
            $locationName= $item->locationName;
        }

        if(count(array_unique($location)) === 1 ){
            $location = DB::table('locations')
                ->leftJoin('companies', 'companies.id', '=', 'locations.company_id')
                ->where('companies.id','=', $companyID)
                ->select(DB::raw("CONCAT(companies.code, ' - ', locations.name) as location_name"), 'locations.id AS id')
                ->orderBy('companies.name','ASC')
                ->get()
                ->pluck('location_name', 'id')
                ->prepend('Silahkan pilih...', '');

            return view('logistic.transfer_out.create', compact('location','inventory','locationName','locationCode','companyCode','locationID','type','companyID','department','project'));
        }else{
            return redirect()->back()
            ->withErrors(['Lokasi Stock Gudang berbeda pada Item yang dipilih']);
        }
    }


    public function store(Request $request)
    {
        if($request->get('type') == 0 || $request->get('type') == 2){ //PEMINDAHAN=0 PENJUALAN = 2
            $increment = InventoryTransferOut::whereYear("publish", date('Y'))
                ->where('location_id',$request->get('location_id'))
                ->count();
            $num = sprintf("%'.05d", $increment + 1) ;
            $no = "WTO-".$request->get('companyCode')."-".$request->get('locationCode')."-".date('my')."-".$num;
            $data['publish'] = date('Y-m-d');
            $data['status']    = 1;
            $data['publish']   = date('Y-m-d');
            $data['operator']       = $request->get('operator');
            $data['location_id']    = $request->get('location_id');
            $data['location_destination']    = $request->get('location_destination');
            $data['doc_no']         = $no;
            $data['created_by']     = Auth::user()->id;
            $data['type']     = $request->get('type');

            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $name = $no.'-'.time();
                $folder = 'uploads/inventory/'.date('Y').'/'.date('M').'/';
                $filePath = $folder . $name. '.' . $file->getClientOriginalExtension();
                $this->uploadOne($file, $folder, 'public', $name);
                $data['file'] = $filePath;
            }

            DB::beginTransaction();
            try {
                $trf = InventoryTransferOut::create($data);
                $dataTRF = [];
                $product = $request->get('inv_id');

                for($i=0;$i < count($product);$i++) {
                    $dataTRF[] = [
                        'inventory_transfer_id'  => $trf->id,
                        'inventory_id'  => $request->get('inv_id')[$i],
                        'qty'           => $request->get('qty')[$i],
                        'notes'         => $request->get('notes')[$i],
                    ];
                }
                InventoryTransferOutItem::insert($dataTRF);
                if ($request->get('status')==1) {
                    $ids    = [];
                    $onhand = [];
                    $out    = [];
                    $status = [];
                    $invHistory = [];

                    for($i=0;$i < count($product);$i++) {
                        $inv        = Inventory::find($request->get('inv_id')[$i]);
                        $stock_onhand   = $inv->stock_onhand - $request->get('qty')[$i];
                        $stock_out      = $inv->out + $request->get('qty')[$i];
                        $stock_status   = getStatusInventory($inv->stock_max, $inv->stock_min, $stock_onhand,'raw');

                        $ids[]          = $request->get('inv_id')[$i];
                        $out[]          = "WHEN id = {$request->get('inv_id')[$i]} THEN $stock_out";
                        $onhand[]       = "WHEN id = {$request->get('inv_id')[$i]} THEN $stock_onhand ";
                        $status[]       = "WHEN id = {$request->get('inv_id')[$i]} THEN '".$stock_status."'";

                        $invHistory[] = [
                            'inventory_id'  => $request->get('inv_id')[$i],
                            'qty_out'       => $request->get('qty')[$i],
                            'qty_awal'      => $inv->stock_onhand,
                            'message'       => $trf->doc_no,
                            'description'   => '',
                        ];
                    }
                    $ids        = implode(',', $ids);
                    $onhand     = implode(' ', $onhand);
                    $out        = implode(' ', $out);
                    $status     = implode(' ', $status);
                    \DB::update("UPDATE inventories SET stock_onhand = CASE {$onhand} END, out = CASE {$out} END, stock_status = CASE {$status} END WHERE id in ({$ids})");
                    InventoryHistory::insert($invHistory);
                }
                DB::commit();
                return redirect()->route('logistic.transfer_out.show', Hashids::encode($trf->id))->with(['success' => 'Berhasil melakukan Transfer!']);
            } catch (\Exception $e) {
                DB::rollback();
                return redirect()->back()->withInput()->withErrors(['error' => $e->getMessage()]);
            }
        }else{ //PEMINJAMAN/PENGEMBALIAN
            $wto_increment = InventoryTransferOut::whereYear("publish", date('Y'))
                ->where('location_id',$request->get('location_id'))
                ->count();
            $wto_num = sprintf("%'.05d", $wto_increment + 1) ;
            $wto_no = "WTO-".$request->get('companyCode')."-".$request->get('locationCode')."-".date('my')."-".$wto_num;
            $wto_data['publish'] = date('Y-m-d');
            $wto_data['status']    = 5; //Selesai

            $wto_data['operator']       = $request->get('operator');
            $wto_data['location_id']    = $request->get('location_id');
            $wto_data['location_destination']    = $request->get('location_destination');
            $wto_data['doc_no']         = $wto_no;
            $wto_data['created_by']     = Auth::user()->id;
            $wto_data['type']     = 1;
            if ($request->hasFile('file')) {
                $wto_file = $request->file('file');
                $wto_name = $wto_no.'-'.time();
                $wto_folder = 'uploads/inventory/'.date('Y').'/'.date('M').'/';
                $wto_filePath = $wto_folder . $wto_name. '.' . $wto_file->getClientOriginalExtension();
                $this->uploadOne($wto_file, $wto_folder, 'public', $wto_name);
                $wto_data['file'] = $wto_filePath;
            }

            DB::beginTransaction();
            try {

                // WTO
                $wto_trf = InventoryTransferOut::create($wto_data);
                $wto_dataTRF = [];
                $wto_product = $request->get('inv_id');
                for($wto_i=0;$wto_i < count($wto_product);$wto_i++) {
                    $wto_dataTRF[] = [
                        'inventory_transfer_id'  => $wto_trf->id,
                        'inventory_id'  => $request->get('inv_id')[$wto_i],
                        'qty'           => $request->get('qty')[$wto_i],
                        'notes'         => $request->get('notes')[$wto_i],
                        'status'        => 1,
                    ];
                }
                InventoryTransferOutItem::insert($wto_dataTRF);
                $wto_item = InventoryTransferOutItem::where('inventory_transfer_id', $wto_trf->id)->get();

                // WTI
                $wti_increment = InventoryTransferIn::whereYear("publish", date('Y'))
                    ->where('location_id',$request->get('location_destination'))
                    ->count();

                $wti_num = sprintf("%'.05d", $wti_increment + 1) ;
                $wti_no = "WTI-".getCompanyByLocationId($request->get('location_destination'))->code."-".getLocationByID($request->get('location_destination'))->alias."-".date('my')."-".$wti_num;
                $wti_data['publish']    = date('Y-m-d');
                $wti_data['status']    = 2;
                $wti_data['received_date']   = date('Y-m-d');
                $wti_data['received']        = $request->get('received');
                $wti_data['transfer_out_id'] = $wto_trf->id;
                $wti_data['location_id']     = $request->get('location_destination');
                $wti_data['doc_no']          = $wti_no;
                $wti_data['created_by']      = Auth::user()->id;
                $wti_data['type']            = 1;

                $wti_trf = InventoryTransferIn::create($wti_data);

                $wti_dataTRF = [];
                $wti_product = $request->get('inv_id');
                for ($i = 0; $i < count($wto_item); $i++) {
                    $woval = $wto_item[$i];
                    $wti_dataTRF[] = [
                        'inventory_transfer_id'             => $wti_trf->id,
                        'inventory_transfer_out_item_id'    => $woval->id,
                        'qty'                               => $woval->qty,
                        'notes'                             => $woval->notes,
                    ];
                }
                InventoryTransferInItem::insert($wti_dataTRF);
                $wti_item_trf = InventoryTransferInItem::where('inventory_transfer_id', $wti_trf->id)->get();

                // HISTORY WTO
                $wto_ids    = [];
                $wto_onhand = [];
                $wto_out    = [];
                $wto_status = [];
                $wto_invHistory = [];

                for($wto_i=0;$wto_i < count($wto_product);$wto_i++) {
                    $wto_inv        = Inventory::find($request->get('inv_id')[$wto_i]);
                    $wto_stock_onhand   = $wto_inv->stock_onhand - $request->get('qty')[$wto_i];
                    $wto_stock_out      = $wto_inv->out + $request->get('qty')[$wto_i];
                    $wto_stock_status   = getStatusInventory($wto_inv->stock_max, $wto_inv->stock_min, $wto_stock_onhand,'raw');

                    $wto_ids[]          = $request->get('inv_id')[$wto_i];
                    $wto_out[]          = "WHEN id = {$request->get('inv_id')[$wto_i]} THEN $wto_stock_out";
                    $wto_onhand[]       = "WHEN id = {$request->get('inv_id')[$wto_i]} THEN $wto_stock_onhand ";
                    $wto_status[]       = "WHEN id = {$request->get('inv_id')[$wto_i]} THEN '".$wto_stock_status."'";

                    $wto_invHistory[] = [
                        'inventory_id'  => $request->get('inv_id')[$wto_i],
                        'qty_out'       => $request->get('qty')[$wto_i],
                        'qty_awal'      => $wto_inv->stock_onhand,
                        'message'       => $wto_trf->doc_no,
                        'description'   => 'OUT TO '.$wti_trf->doc_no,
                    ];
                }
                $wto_ids        = implode(',', $wto_ids);
                $wto_onhand     = implode(' ', $wto_onhand);
                $wto_out        = implode(' ', $wto_out);
                $wto_status     = implode(' ', $wto_status);

                \DB::update("UPDATE inventories SET stock_onhand = CASE {$wto_onhand} END, out = CASE {$wto_out} END, stock_status = CASE {$wto_status} END WHERE id in ({$wto_ids})");

                InventoryHistory::insert($wto_invHistory);

                // HISTORY WTI
                $wti_ids    = [];
                $wti_onhand = [];
                $wti_out    = [];
                $wti_status = [];
                $wti_invHistory = [];

                $allWtiInvHistory = []; // tampung semua history di luar loop
                $allCreatedOrUpdatedInventory = []; // opsional: tampung semua data inventory hasil update/create

                for($wti_i = 0; $wti_i < count($wti_product); $wti_i++) {

                    // cari inventory berdasarkan lokasi dan produk
                    $wti_inv = Inventory::where('location_id', $request->get('location_destination'))
                        ->where('product_id', $request->get('product_id')[$wti_i])
                        ->where('measure_id', $request->get('measure_id')[$wti_i])
                        ->first();


                    if ($wti_inv) {
                        // update stok existing
                        $wti_stock_onhand = $wti_inv->stock_onhand + $request->get('qty')[$wti_i];
                        $wti_stock_in     = $wti_inv->in + $request->get('qty')[$wti_i];
                        $wti_stock_status = getStatusInventory($wti_inv->stock_max, $wti_inv->stock_min, $wti_stock_onhand, 'raw');
                        $dataInvIn = [
                            'stock_onhand'  => $wti_stock_onhand,
                            'in'            => $wti_stock_in,
                            'stock_status'  => $wti_stock_status
                        ];

                        $wti_inv->update($dataInvIn);

                        // simpan data inventory yang diupdate
                        $allCreatedOrUpdatedInventory[] = [
                            'id'            => $wti_inv->id,
                            'location_id'   => $wti_inv->location_id,
                            'product_id'    => $wti_inv->product_id,
                            'stock_onhand'  => $wti_stock_onhand,
                            'in'            => $wti_stock_in,
                            'out'           => $wti_inv->out ?? 0,
                            'status'        => $wti_stock_status
                        ];


                        // simpan history ke array
                        $allWtiInvHistory[] = [
                            'inventory_id'  => $wti_inv->id,
                            'qty_in'        => $request->get('qty')[$wti_i],
                            'qty_awal'      => ($wti_inv->stock_onhand - $request->get('qty')[$wti_i]),
                            'message'       => $wti_trf->doc_no,
                            'description'   => 'IN FROM ' . $wto_trf->doc_no
                        ];
                    } else {
                        // jika belum ada inventory di lokasi tujuan
                        $dataInvIn = [
                            'product_id'    => $request->get('product_id')[$wti_i],
                            'measure_id'    => $request->get('measure_id')[$wti_i],
                            'location_id'   => $request->get('location_destination'),
                            'created_by'    => Auth::user()->id,
                            'stock_onhand'  => $request->get('qty')[$wti_i],
                            'initial'       => 0,
                            'in'            => $request->get('qty')[$wti_i],
                            'out'           => 0,
                            'stock_status'  => 'Over Stock',
                            'uuid'          => Str::uuid(),
                            'price'         => $request->get('price')[$wti_i] ?? '',
                            'price_after_discount' => $request->get('price_after_discount')[$wti_i] ?? ''
                        ];

                        $dataCreateInvIn = Inventory::create($dataInvIn);

                        $allCreatedOrUpdatedInventory[] = [
                            'id'            => $dataCreateInvIn->id,
                            'location_id'   => $dataCreateInvIn->location_id,
                            'product_id'    => $dataCreateInvIn->product_id,
                            'stock_onhand'  => $dataCreateInvIn->stock_onhand,
                            'in'            => $dataCreateInvIn->in,
                            'out'           => $dataCreateInvIn->out,
                            'status'        => $dataCreateInvIn->stock_status
                        ];
                        $allWtiInvHistory[] = [
                            'inventory_id'  => $dataCreateInvIn->id,
                            'qty_in'        => $request->get('qty')[$wti_i],
                            'qty_awal'      => 0,
                            'message'       => $wti_trf->doc_no,
                            'description'   => 'IN FROM ' . $wto_trf->doc_no
                        ];
                    }
                }
                if (!empty($allWtiInvHistory)) {
                    InventoryHistory::insert($allWtiInvHistory);
                }

                if ($request->get('created_ttb') == 1) {

                    // CONTROLLER TTB
                    $location  = DB::table('locations')
                        ->select(
                            'locations.name AS name',
                            'locations.alias AS code',
                            'companies.alias AS companyCode',
                            'locations.email AS email'
                        )
                        ->leftJoin('companies','companies.id','=','locations.company_id')
                        ->where("locations.id", $request->get('location_destination'))
                        ->first();

                    $increment = DB::table('inventory_ttbs')
                        ->whereYear("created_at", date('Y'))
                        ->where('is_local', false)
                        ->where('status', '!=', 0)
                        ->where('location_id',$request->get('location_destination'))
                        ->count();

                    $num = sprintf("%'.05d", $increment + 1);
                    $no = "TTB-".$location->companyCode."-".$location->code."-".date('my')."-".$num;

                    $data = [
                        'status'            => 1,
                        'publish'           => date('Y-m-d'),
                        'operator'          => $request->get('operator'),
                        'project_id'        => $request->get('project_id'),
                        'received'          => $request->get('received'),
                        'department_id'     => $request->get('department_id'),
                        'location_id'       => $request->get('location_destination'),
                        'doc_no'            => $no,
                        'created_by'        => Auth::user()->id,
                        'date_transaction'  => $request->get('date_transaction'),
                        'notes'             => 'TTB FROM ' . $wto_trf->doc_no.' => '.$wti_trf->doc_no
                    ];

                    if ($request->hasFile('file')) {
                        $file = $request->file('file');
                        $name = 'ttb_'.time();
                        $folder = '/uploads/inventory/'.date('Y').'/'.date('M').'/';
                        $filePath = $folder . $name. '.' . $file->getClientOriginalExtension();
                        $this->uploadOne($file, $folder, 'public', $name);
                        $data['file'] = $filePath;
                    }

                    $ttb = InventoryTtb::create($data);

                    // 🔹 Ganti sumber data dari hasil WTI
                    // pastikan $allCreatedOrUpdatedInventory dikirim dari bagian sebelumnya (WTI)
                    $dataTTB = $invHistory = $productUrgentStock = $ids = $update = $out = $status = [];
                    foreach ($allCreatedOrUpdatedInventory as $key => $inv) {
                        $qty_request = $request->get('qty')[$key] ?? 0;  // ambil qty dari request berdasarkan index
                        $notesitem_request = $request->get('notes')[$key] ?? '';

                        $dataTTB[] = [
                            'inventory_ttb_id'  => $ttb->id,
                            'inventory_id'      => $inv['id'],
                            'description'       => 'Auto generated from WTI ' . ($wti_trf->doc_no ?? ''),
                            'qty'               => $qty_request, // gunakan qty dari request, bukan dari inventory in
                            'description'       => $notesitem_request,
                            'qty_awal'          => $inv['stock_onhand'] - $qty_request,
                            'price'             => $inv['price'] ?? 0,
                        ];

                        $invHistory[] = [
                            'inventory_id'  => $inv['id'],
                            'qty_out'       => $qty_request,
                            'qty_awal'      => $inv['stock_onhand'],
                            'message'       => $ttb->doc_no,
                            'description'   => 'Auto TTB From WTI ' . ($wti_trf->doc_no ?? '')
                        ];

                        $stock_onhand = $inv['stock_onhand'] - $qty_request;
                        $stock_out = ($inv['out'] ?? 0) + $qty_request;
                        $stock_status = getStatusInventory(
                            $inv['stock_max'] ?? 0,
                            $inv['stock_min'] ?? 0,
                            $stock_onhand,
                            'raw'
                        );

                        $ids[] = $inv['id'];
                        $out[] = "WHEN id = {$inv['id']} THEN $stock_out";
                        $update[] = "WHEN id = {$inv['id']} THEN $stock_onhand";
                        $status[] = "WHEN id = {$inv['id']} THEN '".$stock_status."'";

                        if ($stock_status == 'Urgent Order') {
                            $productUrgentStock[] = [
                                'productCode' => $inv['product_code'] ?? '',
                                'productName' => $inv['product_name'] ?? '',
                                'stock'       => $stock_onhand,
                                'min'         => $inv['stock_min'] ?? 0,
                                'max'         => $inv['stock_max'] ?? 0,
                            ];
                        }
                    }

                    InventoryTtbItem::insert($dataTTB);

                    if (!empty($ids)) {
                        $idsStr = implode(',', $ids);
                        $updateStr = implode(' ', $update);
                        $outStr = implode(' ', $out);
                        $statusStr = implode(' ', $status);

                        DB::update("
                            UPDATE inventories
                            SET stock_onhand = CASE {$updateStr} END,
                                \"out\" = CASE {$outStr} END,
                                stock_status = CASE {$statusStr} END
                            WHERE id IN ({$idsStr})
                        ");
                    }

                    InventoryHistory::insert($invHistory);
                }

                DB::commit();
                return redirect()->route('logistic.transfer_out.show', Hashids::encode($wto_trf->id))->with(['success' => 'Berhasil melakukan Transfer!']);
            } catch (\Exception $e) {
                DB::rollback();

                $errorMessage = $e->getMessage();
                $errorFile = $e->getFile();
                $errorLine = $e->getLine();
                $fullError = "Error: {$errorMessage} in file {$errorFile} on line {$errorLine}";
                return redirect()->back()->withInput()->withErrors(['error' => $fullError]);
            }
        }
    }

    public function show($id)
    {

        $id = Hashids::decode($id);

        $transfer      = InventoryTransferOut::getById($id['0']);
        $transfer_items= InventoryTransferOutItem::getByTransferId($id['0']);
        return view('logistic.transfer_out.show', compact('transfer','transfer_items'));
    }


    public function edit($id)
    {
        $id = Hashids::decode($id);

        $transfer      = InventoryTransferOut::getById($id['0']);
        $transfer_items= InventoryTransferOutItem::getByTransferId($transfer->id);

        $location  = DB::table('locations')
        ->where('locations.company_id',$transfer->companyID)
        ->where("locations.id",'!=' , $transfer->location_id)->where('locations.status','=',1)->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');

        return view('logistic.transfer_out.edit', compact('transfer','transfer_items','location'));
    }


    public function update(Request $request, $id)
    {

        $transfer = InventoryTransferOut::findOrFail($id);

        if ($request->get('status')==1) {
            $increment = DB::table('inventory_transfer_out')
            ->whereYear("publish", date('Y'))
            ->where('status', '!=', 0)
            ->where('location_id',$request->get('location_id'))
            ->count();

            $num = sprintf("%'.05d", $increment + 1) ;
            $doc_no     = explode('-',$transfer->doc_no);
            $location   = $doc_no['2'];
            $company    = $doc_no['1'];
            $document_no = "WTO-".$company."-".$location."-".date('my')."-".$num;
            $data['doc_no'] = $document_no;
            $data['status']  = 1;
            $data['publish'] = date('Y-m-d');
        }else{
            $data['status'] = 0;
        }

        $data['operator']               = $request->get('operator');
        $data['location_destination']   = $request->get('location_destination');
        $data['created_by']             = Auth::user()->id;

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $name = 'file_transfer_out_'.time();
            $folder = 'uploads/inventory/'.date('Y').'/'.date('M').'/';
            $filePath = $folder . $name. '.' . $file->getClientOriginalExtension();
            $this->uploadOne($file, $folder, 'public', $name);
            $data['file'] = $filePath;
        }

        DB::beginTransaction();
        try {
            $transfer->update($data);

            $transfer_itemID = $request->get('transfer_itemID');
            for($i=0;$i < count($transfer_itemID);$i++) {
                $ids[]      = $request->get('transfer_itemID')[$i];
                $notes[]    = "WHEN id = {$request->get('transfer_itemID')[$i]} THEN '".$request->get('notes')[$i]."'";
                $qty[]      = "WHEN id = {$request->get('transfer_itemID')[$i]} THEN ".$request->get('qty')[$i];
            }

            $ids    = implode(',', $ids);
            $notes  = implode(' ', $notes);
            $qty    = implode(' ', $qty);

            \DB::update("UPDATE inventory_transfer_out_items SET qty = CASE {$qty} END, notes = CASE {$notes} END WHERE id in ({$ids})");

            if ($request->get('status')==1) {

                $ids    = [];
                $onhand = [];
                $out    = [];
                $status = [];
                $invHistory = [];
                $productUrgentStock = [];
                $product = $request->get('inv_id');

                for($i=0;$i < count($product);$i++) {
                    $inv  = Inventory::getByID($request->get('inv_id')[$i]);
                    $stock_onhand   = $inv->stock_onhand - $request->get('qty')[$i];
                    $stock_out      = $inv->out + $request->get('qty')[$i];
                    $stock_status   = getStatusInventory($inv->stock_max, $inv->stock_min, $stock_onhand,'raw');

                    $ids[]          = $request->get('inv_id')[$i];
                    $out[]          = "WHEN id = {$request->get('inv_id')[$i]} THEN $stock_out";
                    $onhand[]       = "WHEN id = {$request->get('inv_id')[$i]} THEN $stock_onhand ";
                    $status[]       = "WHEN id = {$request->get('inv_id')[$i]} THEN '".$stock_status."'";

                    $invHistory[] = [
                        'inventory_id'  => $request->get('inv_id')[$i],
                        'qty_out'       => $request->get('qty')[$i],
                        'qty_awal'      => $inv->stock_onhand,
                        'message'       => $document_no,
                        'description'   => '',
                    ];
                }

                $ids        = implode(',', $ids);
                $onhand     = implode(' ', $onhand);
                $out        = implode(' ', $out);
                $status     = implode(' ', $status);

                \DB::update("UPDATE inventories SET stock_onhand = CASE {$onhand} END, out = CASE {$out} END, stock_status = CASE {$status} END WHERE id in ({$ids})");

                InventoryHistory::insert($invHistory);
                for($i=0;$i < count($product);$i++) {
                    $inv  = Inventory::getByID($request->get('inv_id')[$i]);
                    if($inv->status == 'Urgent Order'){
                        $productUrgentStock [] = [
                            'productCode' => $inv->productCode,
                            'productName' => $inv->productName,
                            'rack'        => $inv->code_rack,
                            'stock'       => $inv->stock_onhand,
                        ];
                    }
                    $locationEmail = $inv->locationEmail;
                    $locationName  = $inv->location;
                }
            }

            DB::commit();
            return redirect()->route('logistic.transfer_out.show', Hashids::encode($transfer->id))->with(['success' => 'Berhasil melakukan Warehouse Transfer Stock!']);
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }

    }


    public function delete(Request $request)
    {

        $transfer  = InventoryTransferOut::findOrFail($request->id);
        $transfer->delete();
        return redirect()->route('logistic.transfer_out.index')->with(['success' => 'Delete Data Berhasil!']);

    }


    public function print($id)
    {

        $id = Hashids::decode($id);
        $transfer      = InventoryTransferOut::getById($id['0']);
        $transfer_items= InventoryTransferOutItem::getByTransferId($transfer->id);

        return view('logistic.transfer_out.print', compact('transfer','transfer_items'));
    }


    public function search(Request $request)
    {
        $query = [
            'location_id' => $request->get('location_id'),
            'type'        => $request->get('type'),
            'start_date'  => $request->get('start_date'),
            'end_date'    => $request->get('end_date'),
        ];

        $data = $request->all();
        $search = "Cari Berdasarkan: ";

        if($request->input('location_id')) $search .= "<strong> Lokasi: </strong>".getDataByID('locations',$request->input('location_id'))->name.' - '.getDataByID('companies',getDataByID('locations',$request->input('location_id'))->company_id)->alias;
        if($request->input('type') || $request->input('type') == 0) $search .= "<strong> Type: </strong>".getTypeWto($request->input('type'));
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

        $type = array(
            ''  => 'Silahkan Pilih',
            '0' => 'PEMINDAHAN',
            '1' => 'PEMINJAMAN',
            '3' => 'PENGEMBALIAN',
            '2' => 'PENJUALAN'
        );

        return view('logistic.transfer_out.search', compact('data','location','search','query','type'));
    }

    public function export(Request $request)
    {
        return Excel::download(new InventoryTransferOutExport($request->get('location_id'),$request->get('type'), $request->get('start_date'), $request->get('end_date')), 'Report-Warehous-Transfer-Out.xlsx');
    }


    public function approval(Request $request)
    {
        if($request->isMethod('get')){
            $id            = Hashids::decode($request->get('id'));
            $transfer      = InventoryTransferOut::getById($id['0']);
            $transfer_items= InventoryTransferOutItem::getByTransferId($id['0'],0);
            return view('logistic.transfer_out.approval', compact('transfer','transfer_items'));
        }else{
            $data['approved_at'] = date('Y-m-d H:i:s');
            $data['approved_by'] = Auth::user()->id;
            $inventory = InventoryTransferOut::findOrFail($request->get('transfer_id'));

            if($request->get('status')==2){
                $data['status'] = 2;
                $inventory->update($data);
                return redirect()->route('logistic.transfer_out.index')->with(['success' => 'Berhasil melakukan Approval Warehouse Transfer Stock!']);
            }else{
                $data['status'] = 6;

                DB::beginTransaction();
                try {

                    $ids    = [];
                    $onhand = [];
                    $in     = [];
                    $status = [];
                    $invHistory = [];

                    $product = $request->get('inv_id');

                    for($i=0;$i < count($product);$i++) {
                        $inv  = Inventory::getByID($request->get('inv_id')[$i]);
                        $stock_onhand   = $inv->stock_onhand + $request->get('qty')[$i];
                        $stock_in       = $inv->in + $request->get('qty')[$i];
                        $stock_status   = getStatusInventory($inv->stock_max, $inv->stock_min, $stock_onhand,'raw');

                        $ids[]          = $request->get('inv_id')[$i];
                        $in[]           = "WHEN id = {$request->get('inv_id')[$i]} THEN $stock_in";
                        $onhand[]       = "WHEN id = {$request->get('inv_id')[$i]} THEN $stock_onhand ";
                        $status[]       = "WHEN id = {$request->get('inv_id')[$i]} THEN '".$stock_status."'";

                        $invHistory[] = [
                            'inventory_id'  => $request->get('inv_id')[$i],
                            'qty_in'        => $request->get('qty')[$i],
                            'qty_awal'      => $inv->stock_onhand,
                            'message'       => $request->get('doc_no'),
                            'description'   => 'Pembatalan Warehouse Transfer Out dari '. $request->get('doc_no'),
                        ];
                    }

                    $ids        = implode(',', $ids);
                    $onhand     = implode(' ', $onhand);
                    $in         = implode(' ', $in);
                    $status     = implode(' ', $status);

                    if($inventory->status != 6){
                        \DB::update('UPDATE inventories SET stock_onhand = CASE '.$onhand.' END,
                        "in" = CASE '.$in.' END,
                        stock_status = CASE '.$status.' END
                        WHERE id in ('.$ids.')');
    
                        InventoryHistory::insert($invHistory);
    
                        $inventory->update($data);
                    }

                    DB::commit();
                    return redirect()->route('logistic.transfer_out.index')->with(['success' => 'Berhasil melakukan Reject Warehouse Transfer Stock!']);
                } catch (\Exception $e) {
                    DB::rollback();
                    return redirect()->back()->withInput()->withErrors(['error' => $e->getMessage()]);
                }
            }
        }
    }

    public function getLocationsByTypeTransferOut($type,$companyId)
    {
        if($type == 0){
            $result = DB::table('locations')
                ->leftJoin('companies', 'companies.id', '=', 'locations.company_id')
                ->where('companies.id','=', $companyId)
                ->where('locations.status','=',1)
                ->select(DB::raw("CONCAT(companies.code, ' - ', locations.name) as location_name"), 'locations.id AS id')
                ->orderBy('companies.name','ASC')
                ->get()
                ->pluck('location_name', 'id');
        }else{
            $result = DB::table('locations')
                ->leftJoin('companies', 'companies.id', '=', 'locations.company_id')
                ->where('companies.id','!=', $companyId)
                ->where('locations.status','=',1)
                ->select(DB::raw("CONCAT(companies.code, ' - ', locations.name) as location_name"), 'locations.id AS id')
                ->orderBy('companies.name','ASC')
                ->get()
                ->pluck('location_name', 'id');
        }
        return $result;
    }

    public function getDepartmentsByTypeTransferOut($type,$companyId)
    {
        if($type == 0){
            $result = DB::table('departments')
                ->leftJoin('companies', 'companies.id', '=', 'departments.company_id')
                ->where('companies.id','=', $companyId)
                ->select(DB::raw("CONCAT(companies.code, ' - ', departments.name) as department_name"), 'departments.id AS id')
                ->where('departments.status','=',1)
                ->orderBy('companies.name','ASC')
                ->get()
                ->pluck('department_name', 'id');
        }else{
            $result = DB::table('departments')
                ->leftJoin('companies', 'companies.id', '=', 'departments.company_id')
                ->where('companies.id','!=', $companyId)
                ->select(DB::raw("CONCAT(companies.code, ' - ', departments.name) as department_name"), 'departments.id AS id')
                ->where('departments.status','=',1)
                ->orderBy('companies.name','ASC')
                ->get()
                ->pluck('department_name', 'id');
        }
        return $result;
    }
    public function getDepartmentByLocation($lokasi_destinasi)
    {
        $locations = DB::table('locations')->where('id','=',$lokasi_destinasi)->first();
        $result = DB::table('departments')
            ->leftJoin('companies', 'companies.id', '=', 'departments.company_id')
            ->where('companies.id','=', $locations->company_id)
            ->select(DB::raw("CONCAT(companies.code, ' - ', departments.name) as department_name"), 'departments.id AS id')
            ->where('departments.status','=',1)
            ->orderBy('companies.name','ASC')
            ->get()
            ->pluck('department_name', 'id');
        return $result;
    }
}
