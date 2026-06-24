<?php

namespace App\Http\Controllers\Logistic;

use App\Models\Bpb;
use App\Models\Spb;
use App\Models\SpbKoli;
use App\Models\BpbItem;
use App\Models\BpbHistory;
use App\Models\Inventory;
use App\Models\InventoryHistory;
use App\Models\InventoryTransferOut;
use App\Models\InventoryTransferOutItem;
use App\Models\InventoryTransferIn;
use App\Models\InventoryTransferInItem;
use Illuminate\Http\Request;
use App\Models\InventoryProcess;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Vinkla\Hashids\Facades\Hashids;
use App\Models\Notification;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Exports\BpbExport;
use Illuminate\Support\Str;

use Auth;
use Maatwebsite\Excel\Facades\Excel;
use Rap2hpoutre\FastExcel\FastExcel;
use App\Traits\UploadTrait;


class BpbController extends Controller
{
    use UploadTrait;
    /**
     * Display a listing of User.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (Gate::allows('bpb') || Gate::allows('bpb_monitoring')) {
            return view('logistic.bpb.index');
        }else{
            return abort(401);
        }
    }

    public function datatables(Request $request)
    {
        if (Gate::allows('bpb') || Gate::allows('bpb_monitoring')) {
            $data = $request->all();

            $result  = Bpb::getData($data);

           return  DataTables::of($result)
            ->addColumn('action', function ($result) {
                $url_edit = "<a href='".route('logistic.bpb.edit', Hashids::encode($result->id))."' title='".trans('Edit')."' data-toggle='tooltip' class='btn btn-outline'><span class='ti-pencil icon-lg'></span> </a>";
                $url_view = "<a href='".route('logistic.bpb.show', Hashids::encode($result->id))."' title='".trans('app.show_title')."' data-toggle='tooltip' class='btn btn-outline'><span class='ti-eye icon-lg'></span> </a>";
                $url_delete = "<form class='delete' action='".route('logistic.bpb.delete', ['id' => $result->id])."' method='POST'>
                                    ".csrf_field()."
                                    <button class='btn btn-outline text-danger' title='".trans('app.delete_title')."' data-toggle='tooltip'><i class='ti-trash icon-lg'></i></button>
                                </form>";
                if(Gate::allows('bpb_monitoring')){
                    return '<div class="btn-group">'.$url_view.'</div>';
                }else{
                    if($result->status==0){
                        return '<div class="btn-group">'.$url_edit .$url_view .$url_delete.'</div>';
                    }else{
                        return '<div class="btn-group">'.$url_view.'</div>';
                    }
                }
            })
            ->editColumn('status', function ($result) {
                return getStatusData($result->status);
            })
            ->editColumn('created_at', function ($result) {
                return $result->created_at  ? with(new Carbon($result->created_at ))->format('d/m/Y H:i:s' ) : '';
            })
            ->rawColumns(['action', 'status'])
            ->make(true);
        }else{
            return abort(401);
        }
    }

    /**
     * Show the form for creating new User.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $id = Hashids::decode($request->id);
        $spb = Spb::findOrFail($id['0']);
        $spb_items  = Spb::getBPBItem($id['0']);

        return view('logistic.bpb.create', compact('spb','spb_items'));
    }

    /**
     * Store a newly created User in storage.
     *
     * @param  \App\Http\Requests\StoreUsersRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        if (! Gate::allows('bpb')) {
            return abort(401);
        }

        $data = $request->all();

        if ($request->get('status')==1) {
            $increment = Bpb::whereYear("created_at", date('Y'))
            ->whereNull('po_id')
            ->where('status','!=', 0)
            ->count();

            $nomor = "BPB-JKT-".date('my')."-".sprintf("%'.05d", $increment + 1) ;
            $data['status'] = 1;
            $data['publish'] = date('Y-m-d H:i:s');

        }else{
            $nomor = "BPB-JKT-".date('my')."-DRAFT";
            $data['status'] = 0;
        }

        $data['doc_no']  = $nomor;
        $data['created_by']  = Auth::user()->id;

        $data['verified_by']     = Auth::user()->id;
        $data['verified_at']     = now();

        DB::beginTransaction();

        try {

            $bpb = Bpb::create($data);

            $dataBPB = $cases =  $parsial = $parsialItem =  $qty_parsial =  $ids = [];
            $item = $request->get('spb_item_id');

            for($i=0;$i < count($item);$i++) {

                if (in_array($request->get('spb_item_id')[$i], $request->get('iscreateBPB'))) {
                    $dataBPB[] = [
                        'bpb_id'        => $bpb->id,
                        'spb_item_id'   => $request->get('spb_item_id')[$i],
                        'pr_item_id'    => $request->get('pr_item_id')[$i],
                        'qty'           => $request->get('qty_bpb')[$i],
                        'description'   => $request->get('description')[$i],
                    ];

                    $ids[] = $request->get('spb_item_id')[$i];

                    if($request->get('qty_spb')[$i] == $request->get('qty_bpb')[$i]){
                        $cases[]        = "WHEN id = {$request->get('spb_item_id')[$i]} THEN 1";
                        $qty_parsial [] = "WHEN id = {$request->get('spb_item_id')[$i]} THEN 0";
                        $parsial[]      = '1';
                    }else{
                        $qtyParsial     = $request->get('qty_spb')[$i] - $request->get('qty_bpb')[$i];
                        $cases[]        = "WHEN id = {$request->get('spb_item_id')[$i]} THEN 2";
                        $qty_parsial [] = "WHEN id = {$request->get('spb_item_id')[$i]} THEN ".$qtyParsial;
                        $parsial[]      = '2';
                    }
                    $parsialItem[]  = '1';
                }else{
                    $parsialItem[]  = '2';
                }

            }

            $ids = implode(',', $ids);
            $cases = implode(' ', $cases);
            $qty_parsial= implode(' ', $qty_parsial);

            if ($request->get('status')==1) {
                \DB::update("UPDATE spb_kolis SET bpb_status = CASE {$cases} END, qty_parsial = CASE {$qty_parsial} END WHERE id in ({$ids})");
            }

            BpbItem::insert($dataBPB);

            if (in_array('2', $parsial) || in_array('2', $parsialItem)) {
                $dataSPB['status'] = '2';
            } else {
                $dataSPB['status'] = '3';
            }

            $spb = Spb::findOrFail($request->get('spb_id'));
            $spb->update($dataSPB);

            if ($request->get('status')==1) {
                $dataInv = [];
                for($i=0;$i < count($item);$i++) {
                    if (in_array($request->get('spb_item_id')[$i], $request->get('iscreateBPB'))) {

                        $inv  = Inventory::where('location_id',$request->get('location_id')[$i])
                                ->where('product_id',$request->get('product_id')[$i])
                                ->where('measure_id',$request->get('measure_id')[$i])
                                ->first();

                        $price_item = $request->get('price')[$i] - ($request->get('price')[$i] * $request->get('discount')[$i]/100);
                        $price_after_discount = $request->get('price_after_discount')[$i];

                        $notes = array(
                            'po'    => $request->get('no_po')[$i],
                            'price' => $price_item,
                            'price_after_discount' => $price_after_discount,
                        );

                        $qtyConversion = $request->get('qty_bpb')[$i]*$request->get('conversion')[$i];

                        if($inv){
                            InventoryHistory::create([
                                'inventory_id'  => $inv->id,
                                'qty_in'        => $qtyConversion,
                                'qty_awal'      => $inv->stock_onhand,
                                'message'       => $nomor,
                                'notes'         => json_encode($notes)
                            ]);

                            $dataInventory['price'] = $price_item;
                            $dataInventory['price_after_discount'] = $request->get('price_after_discount')[$i];
                            $dataInventory['in'] = $inv->in + $qtyConversion;
                            $stock_onhand  = $inv->stock_onhand + $qtyConversion;
                            $dataInventory['stock_onhand'] = $stock_onhand;
                            $dataInventory['stock_status'] = getStatusInventory($inv->stock_max, $inv->stock_min, $stock_onhand,'raw');
                            $inv->update($dataInventory);

                            if($request->get('request_type_item')[$i] == 1 ){
                                // WTO
                                $wto_increment = InventoryTransferOut::whereYear("publish", date('Y'))
                                    ->where('location_id',$request->get('location_id')[$i])
                                    ->count();
                                $wto_num                                = sprintf("%'.05d", $wto_increment + 1) ;
                                $wto_no                                 = "WTO-".getCompanyByLocationId($request->get('location_id')[$i])->code."-".getLocationByID($request->get('location_id')[$i])->alias."-".date('my')."-".$wto_num;
                                $wto_data['publish']                    = date('Y-m-d');
                                $wto_data['status']                     = 5; //SELESAI
                                $wto_data['operator']                   = Auth::user()->name;
                                $wto_data['location_id']                = $request->get('location_id')[$i];
                                $wto_data['location_destination']       = $request->get('return_location')[$i];
                                $wto_data['doc_no']                     = $wto_no;
                                $wto_data['created_by']                 = Auth::user()->id;
                                $wto_data['type']                       = 3;
                                $wto_data['file']                       = $request->get('file_dpm')[$i];
                                $wto_trf = InventoryTransferOut::create($wto_data);

                                $wto_dataTRF = [
                                    'inventory_transfer_id'  => $wto_trf->id,
                                    'inventory_id'  => $inv->id,
                                    'qty'           => $qtyConversion,
                                    'notes'         => $request->get('description')[$i],
                                    'status'        => 1,
                                ];
                                InventoryTransferOutItem::insert($wto_dataTRF);
                                $wto_item = InventoryTransferOutItem::where('inventory_transfer_id', $wto_trf->id)->get();

                                // WTI
                                $wti_increment = InventoryTransferIn::whereYear("publish", date('Y'))
                                    ->where('location_id',$request->get('return_location')[$i])
                                    ->count();
                                $wti_num = sprintf("%'.05d", $wti_increment + 1) ;
                                $wti_no = "WTI-".getCompanyByLocationId($request->get('return_location')[$i])->code."-".getLocationByID($request->get('return_location')[$i])->alias."-".date('my')."-".$wti_num;
                                $wti_data['publish']    = date('Y-m-d');
                                $wti_data['status']    = 1;
                                $wti_data['received_date']   = date('Y-m-d');
                                $wti_data['received']        = $request->get('received_by'); //PENERIMA
                                $wti_data['transfer_out_id'] = $wto_trf->id;
                                $wti_data['location_id']     = $request->get('return_location')[$i];
                                $wti_data['doc_no']          = $wti_no;
                                $wti_data['created_by']      = Auth::user()->id;
                                $wti_data['type']            = 3;

                                $wti_trf = InventoryTransferIn::create($wti_data);
                                $wti_dataTRF = [];
                                $wti_product = $request->get('inv_id');
                                for ($j = 0; $j < count($wto_item); $j++) {
                                    $woval = $wto_item[$j];
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
                                $wto_inv            = Inventory::find($inv->id);
                                $wto_stock_onhand   = $wto_inv->stock_onhand - $qtyConversion;
                                $wto_stock_out      = $wto_inv->out + $qtyConversion;
                                $wto_stock_status   = getStatusInventory($wto_inv->stock_max, $wto_inv->stock_min, $wto_stock_onhand,'raw');

                                InventoryHistory::create([
                                    'inventory_id'  => $wto_inv->id,
                                    'qty_out'       => $qtyConversion,
                                    'qty_awal'      => $wto_inv->stock_onhand,
                                    'message'       => $wto_trf->doc_no,
                                    'description'   => 'OUT TO '.$wti_trf->doc_no,
                                ]);

                                $wto_inv->update([
                                    'stock_onhand'  => $wto_stock_onhand,
                                    'out'           => $qtyConversion,
                                    'stock_status'  => $wto_stock_status,
                                ]);

                                // HISTORY WTI
                                $wti_inv = Inventory::find($inv->id);
                                $wti_inv = Inventory::where('location_id',$request->get('return_location')[$i])
                                    ->where('product_id',$request->get('product_id')[$i])
                                    ->where('measure_id',$request->get('measure_id')[$i])
                                    ->first();
                                if($wti_inv){
                                    $wti_stock_onhand   = $wti_inv->stock_onhand + $qtyConversion;
                                    $wti_stock_in       = $wti_inv->in + $qtyConversion;
                                    $wti_stock_status   = getStatusInventory($wti_inv->stock_max, $wti_inv->stock_min, $wti_stock_onhand,'raw');

                                    $dataInvIn = [
                                        'stock_onhand' => $wti_stock_onhand,
                                        'in' => $wti_stock_in,
                                        'stock_status' => $wti_stock_status
                                    ];

                                    $wti_invHistory = [
                                        'inventory_id' => $wti_inv->id,
                                        'qty_in' => $qtyConversion,
                                        'qty_awal' => $wti_inv->stock_onhand,
                                        'message' => $wti_trf->doc_no,
                                        'description' => 'IN FROM ' . $wto_trf->doc_no
                                    ];
                                    $wti_inv->update($dataInvIn);
                                    InventoryHistory::insert($wti_invHistory);
                                }else{
                                    $dataInvIn = [
                                        'product_id'    => $request->get('product_id')[$i],
                                        'measure_id'    => $request->get('measure_id')[$i],
                                        'location_id'   => $request->get('return_location')[$i],
                                        'created_by'    => Auth::user()->id,
                                        'stock_onhand'  => $qtyConversion,
                                        'initial'       => 0,
                                        'in'            => $qtyConversion,
                                        'stock_status'  => 'Over Stock',
                                        'uuid'          => Str::uuid(),
                                        'price'         => $request->get('price')[$i] ?? '',
                                        'price_after_discount' => $request->get('price_after_discount')[$i] ?? ''
                                    ];
                                    $dataCreateInvIn = Inventory::create($dataInvIn);
                                    InventoryHistory::create([
                                        'inventory_id'  => $dataCreateInvIn->id ?? '',
                                        'qty_in'        => $qtyConversion ?? '',
                                        'qty_awal'      => 0,
                                        'message'       => $wti_trf->doc_no ?? '',
                                        'description' => ('IN FROM ' . $wto_trf->doc_no) ?? ''
                                    ]);
                                }
                            }
                        }else{
                            $dataInv = [
                                'product_id'    => $request->get('product_id')[$i],
                                'measure_id'    => $request->get('measure_id')[$i],
                                'location_id'   => $request->get('location_id')[$i],
                                'stock_onhand'  => $qtyConversion,
                                'initial'       => 0,
                                'in'            => $qtyConversion,
                                'stock_status'  => 'Over Stock',
                                'uuid'          => Str::uuid(),
                                'price'         => $price_item,
                                'price_after_discount' => $price_after_discount
                            ];
                            $inv = Inventory::create($dataInv);

                            InventoryHistory::create([
                                'inventory_id'  => $inv->id,
                                'qty_in'        => $qtyConversion,
                                'qty_awal'      => 0,
                                'message'       => $nomor,
                                'notes'         => json_encode($notes)
                            ]);

                            if($request->get('request_type_item')[$i] == 1 ){
                                // WTO
                                $wto_increment = InventoryTransferOut::whereYear("publish", date('Y'))
                                    ->where('location_id',$request->get('location_id')[$i])
                                    ->count();
                                $wto_num                                = sprintf("%'.05d", $wto_increment + 1) ;
                                $wto_no                                 = "WTO-".getCompanyByLocationId($request->get('location_id')[$i])->code."-".getLocationByID($request->get('location_id')[$i])->alias."-".date('my')."-".$wto_num;
                                $wto_data['publish']                    = date('Y-m-d');
                                $wto_data['status']                     = 5; //SELESAI
                                $wto_data['operator']                   = Auth::user()->name;
                                $wto_data['location_id']                = $request->get('location_id')[$i];
                                $wto_data['location_destination']       = $request->get('return_location')[$i];
                                $wto_data['doc_no']                     = $wto_no;
                                $wto_data['created_by']                 = Auth::user()->id;
                                $wto_data['type']                       = 3;
                                $wto_data['file']                       = $request->get('file_dpm')[$i];
                                $wto_trf = InventoryTransferOut::create($wto_data);

                                $wto_dataTRF = [
                                    'inventory_transfer_id'  => $wto_trf->id,
                                    'inventory_id'  => $inv->id,
                                    'qty'           => $qtyConversion,
                                    'notes'         => $request->get('description')[$i],
                                    'status'        => 1,
                                ];
                                InventoryTransferOutItem::insert($wto_dataTRF);
                                $wto_item = InventoryTransferOutItem::where('inventory_transfer_id', $wto_trf->id)->get();
                                // WTI
                                $wti_increment = InventoryTransferIn::whereYear("publish", date('Y'))
                                    ->where('location_id',$request->get('return_location')[$i])
                                    ->count();
                                $wti_num = sprintf("%'.05d", $wti_increment + 1) ;
                                $wti_no = "WTI-".getCompanyByLocationId($request->get('return_location')[$i])->code."-".getLocationByID($request->get('return_location')[$i])->alias."-".date('my')."-".$wti_num;
                                $wti_data['publish']    = date('Y-m-d');
                                $wti_data['status']    = 1;
                                $wti_data['received_date']   = date('Y-m-d');
                                $wti_data['received']        = $request->get('received_by');
                                $wti_data['transfer_out_id'] = $wto_trf->id;
                                $wti_data['location_id']     = $request->get('return_location')[$i];
                                $wti_data['doc_no']          = $wti_no;
                                $wti_data['created_by']      = Auth::user()->id;
                                $wti_data['type']            = 3;
                                $wti_trf = InventoryTransferIn::create($wti_data);
                                $wti_dataTRF = [];
                                $wti_product = $request->get('inv_id');
                                for ($j = 0; $j < count($wto_item); $j++) {
                                    $woval = $wto_item[$j];
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
                                $wto_inv            = Inventory::find($inv->id);
                                $wto_stock_onhand   = $wto_inv->stock_onhand - $qtyConversion;
                                $wto_stock_out      = $wto_inv->out + $qtyConversion;
                                $wto_stock_status   = getStatusInventory($wto_inv->stock_max, $wto_inv->stock_min, $wto_stock_onhand,'raw');

                                InventoryHistory::create([
                                    'inventory_id'  => $wto_inv->id,
                                    'qty_out'       => $qtyConversion,
                                    'qty_awal'      => $wto_inv->stock_onhand,
                                    'message'       => $wto_trf->doc_no,
                                    'description'   => 'OUT TO '.$wti_trf->doc_no,
                                ]);
                                $wto_inv->update([
                                    'stock_onhand'  => $wto_stock_onhand,
                                    'out'           => $qtyConversion,
                                    'stock_status'  => $wto_stock_status,
                                ]);
                                // HISTORY WTI
                                $wti_inv = Inventory::find($inv->id);
                                $wti_inv = Inventory::where('location_id',$request->get('return_location')[$i])
                                    ->where('product_id',$request->get('product_id')[$i])
                                    ->where('measure_id',$request->get('measure_id')[$i])
                                    ->first();
                                if($wti_inv){
                                    $wti_stock_onhand   = $wti_inv->stock_onhand + $qtyConversion;
                                    $wti_stock_in       = $wti_inv->in + $qtyConversion;
                                    $wti_stock_status   = getStatusInventory($wti_inv->stock_max, $wti_inv->stock_min, $wti_stock_onhand,'raw');

                                    $dataInvIn = [
                                        'stock_onhand' => $wti_stock_onhand,
                                        'in' => $wti_stock_in,
                                        'stock_status' => $wti_stock_status
                                    ];

                                    $wti_invHistory = [
                                        'inventory_id' => $wti_inv->id,
                                        'qty_in' => $qtyConversion,
                                        'qty_awal' => $wti_inv->stock_onhand,
                                        'message' => $wti_trf->doc_no,
                                        'description' => 'IN FROM ' . $wto_trf->doc_no
                                    ];
                                    $wti_inv->update($dataInvIn);
                                    InventoryHistory::insert($wti_invHistory);
                                }else{
                                    $dataInvIn = [
                                        'product_id'    => $request->get('product_id')[$i],
                                        'measure_id'    => $request->get('measure_id')[$i],
                                        'location_id'   => $request->get('return_location')[$i],
                                        'created_by'    => Auth::user()->id,
                                        'stock_onhand'  => $qtyConversion,
                                        'initial'       => 0,
                                        'in'            => $qtyConversion,
                                        'stock_status'  => 'Over Stock',
                                        'uuid'          => Str::uuid(),
                                        'price'         => $request->get('price')[$i] ?? '',
                                        'price_after_discount' => $request->get('price_after_discount')[$i] ?? ''
                                    ];
                                    $dataCreateInvIn = Inventory::create($dataInvIn);
                                    InventoryHistory::create([
                                        'inventory_id'  => $dataCreateInvIn->id ?? '',
                                        'qty_in'        => $qtyConversion ?? '',
                                        'qty_awal'      => 0,
                                        'message'       => $wti_trf->doc_no ?? '',
                                        'description' => ('IN FROM ' . $wto_trf->doc_no) ?? ''
                                    ]);
                                }
                            }
                        }
                    }
                }
            }
            DB::commit();
            return redirect()->route('logistic.bpb.show',Hashids::encode($bpb->id))->with(['success' => 'Berhasil Input Data.']);
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }



    public function show($id)
    {
        if (Gate::allows('bpb') || Gate::allows('bpb_monitoring')) {
            $id = Hashids::decode($id);
            $bpb = Bpb::findOrFail($id['0']);
            $bpb_items = Bpb::getProductItem($id['0']);
            return view('logistic.bpb.show', compact('bpb', 'bpb_items'));
        }else{
            return abort(401);
        }
    }

    /**
     * Show the form for editing User.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (! Gate::allows('bpb')) {
            return abort(401);
        }

        $id = Hashids::decode($id);
        $bpb = Bpb::findOrFail($id['0']);
        $bpb_items = Bpb::getProductItem($id['0']);
        return view('logistic.bpb.edit', compact('bpb','bpb_items'));
    }

    /**
     * Update User in storage.
     *
     * @param  \App\Http\Requests\  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if (! Gate::allows('bpb')) {
            return abort(401);
        }

        $bpb = Bpb::findOrFail($id);
        $data = $request->all();

        DB::beginTransaction();

        try {

            if ($request->get('status')==1) {

                $increment = Bpb::whereYear("created_at", date('Y'))
                ->whereNull('po_id')
                ->where('status','!=', 0)
                ->count();

                $nomor = "BPB-JKT-".date('my')."-".sprintf("%'.05d", $increment + 1) ;
                $data['doc_no']    = $nomor;
                $data['status']    = 1;
                $data['publish']   = date('Y-m-d H:i:s');
            }else{
                $data['status'] = 0;
            }

            $data['updated_by']  = Auth::user()->id;
            $bpb->update($data);

            $dataBPB = $cases  = $parsial =  $parsialItem =  $qty_parsial = $ids = [];

            BpbItem::where('bpb_id',$id)->delete();

            $item = $request->get('spb_item_id');
            for($i=0;$i < count($item);$i++) {

                $dataBPB[] = [
                    'bpb_id'        => $bpb->id,
                    'spb_item_id'   => $request->get('spb_item_id')[$i],
                    'pr_item_id'    => $request->get('pr_item_id')[$i],
                    'qty'           => $request->get('qty_bpb')[$i],
                    'description'   => $request->get('description')[$i],
                ];

                $ids[] = $request->get('spb_item_id')[$i];

                if($request->get('qty_spb')[$i] == $request->get('qty_bpb')[$i]){
                    $cases[]        = "WHEN id = {$request->get('spb_item_id')[$i]} THEN 1";
                    $qty_parsial [] = "WHEN id = {$request->get('spb_item_id')[$i]} THEN 0";
                    $parsial[]      = '1';
                }else{
                    $qtyParsial  = $request->get('qty_spb')[$i] - $request->get('qty_bpb')[$i];
                    $cases[]        = "WHEN id = {$request->get('spb_item_id')[$i]} THEN 2";
                    $qty_parsial [] = "WHEN id = {$request->get('spb_item_id')[$i]} THEN ".$qtyParsial;
                    $parsial[]      = '2';
                }
            }

            $ids        = implode(',', $ids);
            $cases      = implode(' ', $cases);
            $qty_parsial= implode(' ', $qty_parsial);

            if ($request->get('status')==1) {
                \DB::update("UPDATE spb_kolis SET bpb_status = CASE {$cases} END, qty_parsial = CASE {$qty_parsial} END WHERE id in ({$ids})");
            }

            BpbItem::insert($dataBPB);

            if (in_array('2', $parsial)) {
                $dataSPB['status'] = '2';
            } else {
                $dataSPB['status'] = '3';
            }
            $spb = Spb::findOrFail($request->get('spb_id'));
            $spb->update($dataSPB);


            if ($request->get('status')==1) {
                $dataInv = $invHistory = [];

                for($i=0;$i < count($item);$i++) {
                    $inv  = Inventory::where('location_id',$request->get('location_id')[$i])
                            ->where('product_id',$request->get('product_id')[$i])
                            ->where('measure_id',$request->get('measure_id')[$i])
                            ->first();

                    $price_item = $request->get('price')[$i] - ($request->get('price')[$i] * $request->get('discount')[$i]/100);

                    $notes = array(
                        'po'    => $request->get('no_po')[$i],
                        'price' => $price_item,
                    );

                    $qtyConversion = $request->get('qty_bpb')[$i]*$request->get('conversion')[$i];

                    if($inv){
                        $invHistory[] = [
                            'inventory_id'  => $inv->id,
                            'qty_in'        => $qtyConversion,
                            'qty_awal'      => $inv->stock_onhand,
                            'message'       => $nomor,
                            'notes'         => json_encode($notes)
                        ];

                        if ($inv->stock_onhand == 0 ){
                            $dataInvExist['price'] = $price_item;
                        }else{
                            $dataInvExist['price'] = ($price_item + $inv->price) / 2;
                        }

                        $dataInvExist['in'] = $inv->in + $qtyConversion;
                        $stock_onhand = $inv->stock_onhand + $qtyConversion;
                        $dataInvExist['stock_onhand'] = $stock_onhand;
                        $dataInvExist['stock_status'] = getStatusInventory($inv->stock_max, $inv->stock_min, $stock_onhand,'raw');

                        $inv->update($dataInvExist);

                    }else{
                        $dataInv = [
                            'product_id'    => $request->get('product_id')[$i],
                            'measure_id'    => $request->get('measure_id')[$i],
                            'location_id'   => $request->get('location_id')[$i],
                            'stock_onhand'  => $qtyConversion,
                            'initial'       => 0,
                            'in'            => $qtyConversion,
                            'stock_status'  => 'Over Stock',
                            'uuid'          => Str::uuid(),
                            'price'         => $request->get('price')[$i]
                        ];
                        $inv = Inventory::create($dataInv);

                        $invHistory[] = [
                            'inventory_id'  => $inv->id,
                            'qty_in'        => $qtyConversion,
                            'qty_awal'      => 0,
                            'message'       => $nomor,
                            'notes'         => json_encode($notes)
                        ];
                    }
                }
                InventoryHistory::insert($invHistory);
            }

            DB::commit();
            return redirect()->route('logistic.bpb.show',Hashids::encode($bpb->id))->with(['success' => 'Update Data Berhasil!']);
        } catch (\Exception $e) {

            DB::rollback();
            return redirect()->back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }

    }


    public function delete(Request $request)
    {

        if (! Gate::allows('bpb')) {
            return abort(401);
        }
        $bpb  = Bpb::findOrFail($request->id);

        $bpb_item = DB::table('bpb_items')
        ->select('*')
        ->where('bpb_id', $bpb->id)
        ->get();


        $ids = [];
        $dataKoli = [];
        foreach($bpb_item as $val){
            $kolis = SpbKoli::findOrFail($val->spb_item_id);

            $item = DB::table('bpb_items')
            ->select('*')
            ->where('spb_item_id',$val->spb_item_id)
            ->where('bpb_id', $bpb->id)
            ->where('id','!=',$val->id )
            ->get()
            ->count();

            if($item > 0){
                $qty_parsial = $val->qty + $kolis->qty_parsial;
                $dataKoli = array (
                    'bpb_status'  => '2',
                    'qty_parsial' => $qty_parsial
                );
                $dataSPB['status'] = '2';
            }else{
                $qty_parsial = 0;
                $dataKoli = array (
                    'bpb_status'  => '0',
                    'qty_parsial' => $qty_parsial
                );
                $dataSPB['status'] = '1';
            }
            DB::table('spb_kolis')
            ->where('id', $val->spb_item_id)
            ->update($dataKoli);
        }

        $spb = Spb::findOrFail($bpb->spb_id);
        $spb->update($dataSPB);

        $bpb->delete();
        return redirect()->route('logistic.bpb.index')->with(['success' => 'Delete Data Berhasil!']);

    }


    public function publish(Request $request, $id)
    {
        if (! Gate::allows('bpb')) {
            return abort(401);
        }

        $bpb = Bpb::findOrFail($id);
        $data = $request->all();

        $increment = Bpb::whereYear("created_at", date('Y'))
        ->whereNull('po_id')
        ->where('status','!=', 0)
        ->count();

        $nomor = "BPB-JKT-".date('my')."-".sprintf("%'.05d", $increment + 1) ;
        $data['doc_no'] = $nomor;
        $data['status'] = 1;
        $data['publish'] = date('Y-m-d H:i:s');
        $data['updated_by']  = Auth::user()->id;

        DB::beginTransaction();

        try {

            $item = $request->get('spb_item_id');
            $dataInv = $invHistory = [];

            for($i=0;$i < count($item);$i++) {
                $inv  = Inventory::where('location_id',$request->get('location_id')[$i])
                        ->where('product_id',$request->get('product_id')[$i])
                        ->where('measure_id',$request->get('measure_id')[$i])
                        ->first();

                $price_item = $request->get('price')[$i] - ($request->get('price')[$i] * $request->get('discount')[$i]/100);

                $notes = array(
                    'po'    => $request->get('no_po')[$i],
                    'price' => $price_item,
                );

                $qtyConversion = $request->get('qty')[$i]*$request->get('conversion')[$i];

                if($inv){
                    $invHistory[] = [
                        'inventory_id'  => $inv->id,
                        'qty_in'        => $qtyConversion,
                        'qty_awal'      => $inv->stock_onhand,
                        'message'       => $nomor,
                        'notes'         => json_encode($notes)
                    ];

                    if ($inv->stock_onhand == 0 ){
                        $dataInvExist['price'] = $price_item;
                    }else{
                        $dataInvExist['price'] = ($price_item + $inv->price) / 2;
                    }

                    $dataInvExist['in'] = $inv->in + $qtyConversion;
                    $stock_onhand = $inv->stock_onhand + $qtyConversion;
                    $dataInvExist['stock_onhand'] = $stock_onhand;
                    $dataInvExist['stock_status'] = getStatusInventory($inv->stock_max, $inv->stock_min, $stock_onhand,'raw');

                    $inv->update($dataInvExist);

                }else{
                    $dataInv = [
                        'product_id'    => $request->get('product_id')[$i],
                        'measure_id'    => $request->get('measure_id')[$i],
                        'location_id'   => $request->get('location_id')[$i],
                        'stock_onhand'  => $qtyConversion,
                        'initial'       => 0,
                        'in'            => $qtyConversion,
                        'stock_status'  => 'Over Stock',
                        'uuid'          => Str::uuid(),
                        'price'         => $request->get('price')[$i]
                    ];
                    $inv = Inventory::create($dataInv);

                    $invHistory[] = [
                        'inventory_id'  => $inv->id,
                        'qty_in'        => $qtyConversion,
                        'qty_awal'      => 0,
                        'message'       => $nomor,
                        'notes'         => json_encode($notes)
                    ];
                }
            }
            InventoryHistory::insert($invHistory);

            $bpb->update($data);

            DB::commit();
            return redirect()->route('logistic.bpb.show',Hashids::encode($bpb->id))->with(['success' => 'Publish Data Berhasil!']);
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }


    public function search(Request $request)
    {
        if (Gate::allows('bpb') || Gate::allows('bpb_monitoring')) {
            $query = 'start_date='.$request->get('start_date').'&end_date='. $request->get('end_date');
            $data = $request->all();
            $search = "Cari Berdasarkan: ";
            if($request->input('start_date') || $request->input('end_date')) $search .= "<strong> Periode: </strong>".$request->input('start_date'). " - ". $request->input('end_date');

            return view('logistic.bpb.search', compact('query', 'search','data'));
        }else{
            return abort(401);
        }

    }


    public function list()
    {
        if (! Gate::allows('bpb')) {
            return abort(401);
        }
        $month = date('m');
        $year = date('Y');

        if(isAdministrator()){
            $cek = true;
        }else{
            $cek = InventoryProcess::where('location_id',Auth::user()->location_id)
            ->where('month',$month)
            ->where('year',$year)->first();
        }

        if($cek){
           return view('logistic.bpb.list');
        } else{
            $location = Auth::user()->location_id;
            return view('logistic.inventory.process',compact('location'));
        }
    }

    public function listDatatables()
    {
        if (! Gate::allows('bpb')) {
            return abort(401);
        }

        $result = Spb::whereIn('status', [1,2])->where('receipt_type','=','bpb')
        ->orderBy('created_at', 'DESC');

        return  DataTables::of($result)
        ->editColumn('updated_at', function ($result) {
            return $result->updated_at ? with(new Carbon($result->updated_at))->format('d/m/Y') : '';
        })
        ->addColumn('action', function ($result) {
            $url = "";
            if($result->receipt_type == 'bpb'){
                $url = "<a href='".route('logistic.bpb.create', ['id' => Hashids::encode($result->id)])."' data-toggle='tooltip' class='btn btn-danger btn-sm font-weight-bold mr-1'><span class='ti-file'></span> BUAT BPB </a>";
            }
            return '<div>'.$url.'</div>';
        })
        ->editColumn('type', function ($result) {
            $pickup = null;
            if($result->is_pickup == true) $pickup = ' [Pick Up]';
            $typeee = $result->type. ' '.$pickup;
            return $typeee;
        })
        ->rawColumns(['action','type'])
        ->make(true);
    }


    public function print($id, $type)
    {
        $id = Hashids::decode($id);
        $bpb = Bpb::getByID($id['0']);
        $bpb_items   = Bpb::getProductItem($id['0']);
        return view('logistic.bpb.print', compact('bpb', 'bpb_items'));
    }


    public function getBpb()
    {
        $result = Bpb::
        selectRaw('bpb.*')
        ->whereHas('BpbItem', function($q){
            $q->where('status', 1);
        })
        ->distinct('id')->pluck('bpb.doc_no','bpb.id');
        return $result;
    }

    public function export(Request $request)
    {
        $date = date('Y-m-d');
        return Excel::download(new BpbExport($request->get('doc_no'),$request->get('location_id'), $request->get('department_id'), $request->get('start_date'), $request->get('end_date')), 'Report-BPB-'.$date.'.xlsx');
    }

    public function getDokumenBpbById($id){
        $id_ = Hashids::decode($id);
        $result = DB::table('bpb')
        ->select('*')
        ->where('id','=',$id_)
        ->first();
        return response()->json($result);
    }
    public function uploadDokumenBpb(Request $request)
    {
        if (! Gate::allows('bpb')) {
            return abort(401);
        }

        $id = $request->get('bpb_id');
        $bpb = Bpb::findOrFail($id);
        $data = $request->all();
        if ($request->hasFile('attachment_file')) {
            $file = $request->file('attachment_file');
            $name = 'BPB-'.time();
            $folder = '/uploads/bpb/attachment_bpb/'.date('Y').'/';
            $filePath = $folder . $name. '.' . $file->getClientOriginalExtension();
            $this->uploadOne($file, $folder, 'public', $name);
            $data['attachment_file'] = $filePath;

            //TIDAK ADA BPB HISTORIES
        }
        $bpb->update($data);

        return redirect()->route('logistic.bpb.index')->with('success', 'Update Attachment Dokumen <a href="' . route('logistic.bpb.show', Hashids::encode($bpb->id)) . '" title="' . trans('app.show_title') . '" data-toggle="tooltip">'.$bpb->doc_no.'</a> Berhasil');
    }

}
