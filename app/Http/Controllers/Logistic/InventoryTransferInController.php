<?php

namespace App\Http\Controllers\Logistic;

use App\Models\Inventory;
use App\Models\InventoryHistory;
use App\Models\InventoryTransferIn;
use App\Models\InventoryTransferInItem;
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
use App\Exports\InventoryTransferInExport;
use App\Traits\UploadTrait;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use App\Mail\SendMailable;
use Illuminate\Support\Facades\Mail;
use Auth;
use Storage;
use Illuminate\Support\Facades\Redirect;


class InventoryTransferInController extends Controller
{

    use UploadTrait;

    function __construct()
    {
        $this->middleware('permission:transfer');
    }

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

        return view('logistic.transfer_in.index',compact('location','type'));
    }

    public function datatables(Request $request)
    {
        $data = $request->all();

        if(isAdministrator() || isAdmin() ) $result  = InventoryTransferIn::getData($data);
        elseif(isAdministratorCompany() ) $result  = InventoryTransferIn::getData($data, Auth::user()->company_id);
        else $result = InventoryTransferIn::getData($data,null,Auth::user()->location_id);

        return  DataTables::of($result)
        ->addColumn('action', function ($result) {
            $url = 'printExternal("'.route('logistic.transfer_in.print', ['id' => Hashids::encode($result->id)]).'")';
            $url_print = "<a onclick='".$url."' data-toggle='Print' class='btn btn-outline' title='Cetak' data-toggle='tooltip'><span class='ti-printer icon-lg'></span> </a>";
            $url_show  = "<a href='".route('logistic.transfer_in.show',Hashids::encode($result->id))."'class='btn btn-outline' title='Detail' data-toggle='tooltip'><span class='ti-eye icon-lg'></span> </a>";
            $url_edit = "<a href='".route('logistic.transfer_in.edit', Hashids::encode($result->id))."' title='Edit' data-toggle='tooltip' class='btn btn-outline'><span class='ti-pencil icon-lg'></span> </a>";
            $url_check  = "<a href='".route('logistic.transfer_in.check',Hashids::encode($result->id))."'class='btn btn-outline' title='Check' data-toggle='tooltip'><span class='ti-thumb-up icon-lg'></span> </a>";
            if($result->status==0){
                return $url_edit.$url_show;
            }else if($result->status==1){
                return $url_show.$url_print;
            }else if($result->status==2 && $result->type == 1 && Gate::allows('checker_wti')){
                return $url_show.$url_print.$url_check;
            }else{
                return $url_show.$url_print;
            }
        })
        ->editColumn('created_at', function ($result) {
            return $result->created_at ? with(new Carbon($result->created_at))->format('d/m/Y H:i:s') : '';
        })
        ->editColumn('type', function ($result) {
            return getTypeWto($result->type) ?? '';
        })
        ->addColumn('status', function ($result) {
            return getStatusTransferIn($result->status,$result->type,$result->type_status);
        })
        ->rawColumns(['action', 'status'])
        ->make(true);

    }


    public function show($id)
    {

        $id = Hashids::decode($id);

        $transfer      = InventoryTransferIn::getById($id['0']);
        $transfer_items= InventoryTransferInItem::getByTransferId($id['0']);
        return view('logistic.transfer_in.show', compact('transfer','transfer_items'));
    }


    public function edit($id)
    {

        $id = Hashids::decode($id);

        $transfer      = InventoryTransferIn::getById($id['0']);
        $transfer_out  = InventoryTransferOut::getById($transfer->transfer_out_id);
        $transfer_items= InventoryTransferInItem::getByTransferId($transfer->id);

        return view('logistic.transfer_in.edit', compact('transfer','transfer_items','transfer_out'));
    }


    public function update(Request $request, $id)
    {

        $transfer = InventoryTransferIn::findOrFail($id);

        if ($request->get('status')==1) {
            $increment = DB::table('inventory_transfer_in')
            ->whereYear("publish", date('Y'))
            ->where('status', '!=', 0)
            ->where('location_id',$request->get('location_id'))
            ->count();

            $num = sprintf("%'.05d", $increment + 1) ;
            $doc_no     = explode('-',$transfer->doc_no);
            $location   = $doc_no['2'];
            $company    = $doc_no['1'];
            $document_no = "WTI-".$company."-".$location."-".date('my')."-".$num;
            $data['doc_no'] = $document_no;
            $data['status']  = 1;
            $data['publish'] = date('Y-m-d');
        }

        $data['received']       = $request->get('received');
        $data['received_date']  = $request->get('received_date');

        DB::beginTransaction();
        try {
            $transfer->update($data);
            $transfer_itemID = $request->get('transfer_itemID');

            for($i=0;$i < count($transfer_itemID);$i++) {
                $ids[]      = $request->get('transfer_itemID')[$i];
                $notes[]    = "WHEN id = {$request->get('transfer_itemID')[$i]} THEN '".$request->get('notes')[$i]."'";
            }

            $ids    = implode(',', $ids);
            $notes  = implode(' ', $notes);

            \DB::update("UPDATE inventory_transfer_in_items SET notes = CASE {$notes} END WHERE id in ({$ids})");

            $invHistory =  $dataTransferItem = $parsial = $parsialItem = $qty_parsial =  $ids =  $status = [];

            if ($request->get('status')==1) {

                for($i=0;$i < count($transfer_itemID);$i++) {

                    $inv  = Inventory::where('location_id',$transfer->location_id)
                    ->where('product_id',$request->get('product_id')[$i])->first();

                    if($inv){
                        $invHistory []= [
                            'inventory_id'  => $inv->id,
                            'qty_in'        => $request->get('qty')[$i],
                            'qty_awal'      => $inv->stock_onhand,
                            'message'       => $document_no,
                            'description'   => 'dari '.$request->get('transfer_out_doc_no'),
                        ];

                        $data['price']        = $inv->price;
                        $data['in']           = $inv->in + $request->get('qty')[$i];
                        $stock_onhand         = $inv->stock_onhand + $request->get('qty')[$i];
                        $data['stock_onhand'] = $stock_onhand;
                        $data['stock_status'] = getStatusInventory($inv->stock_max, $inv->stock_min, $stock_onhand,'raw');
                        $inv->update($data);

                    }else{
                        $dataInv = [
                            'product_id'    => $request->get('product_id')[$i],
                            'location_id'   => $transfer->location_id,
                            'stock_onhand'  => $request->get('qty')[$i],
                            'initial'       => 0,
                            'in'            => $request->get('qty')[$i],
                            'stock_status'  => 'Over Stock',
                            'uuid'          => Str::uuid(),
                            'price'         => $request->get('product_price')[$i],
                            'price_after_discount'         => $request->get('product_price_after_discount')[$i]
                        ];

                        $inv = Inventory::create($dataInv);

                        $invHistory [] = [
                            'inventory_id'  => $inv->id,
                            'qty_in'        => $request->get('qty')[$i],
                            'qty_awal'      => 0,
                            'message'       => $document_no,
                            'description'   => 'dari '.$request->get('transfer_out_doc_no'),
                        ];
                    }
                }
                InventoryHistory::insert($invHistory);
            }

            DB::commit();
            return redirect()->route('logistic.transfer_in.show', Hashids::encode($id))->with(['success' => 'Berhasil melakukan Warehouse Transfer In!']);
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }

    }


    public function delete(Request $request)
    {


        $transfer         = InventoryTransferIn::findOrFail($request->id);
        $transfer_items   = InventoryTransferInItem::getByTransferId($request->id);


        $dataItem = [];

        foreach($transfer_items as $val){
            $transfer_out = InventoryTransferOutItem::findOrFail($val->inventory_transfer_out_item_id);

            $item = InventoryTransferInItem::
            select('*')
            ->where('inventory_transfer_out_item_id',$val->inventory_transfer_out_item_id)
            ->where('id','!=',$val->id )
            ->get()
            ->count();
            if($item > 0){
                $qty_parsial = $val->qty + $transfer_out->qty_parsial;
                $dataItem = array (
                    'status'  => '2',
                    'qty_parsial' => $qty_parsial
                );
                $dataTransfer['status'] = '4';
            }else{
                $qty_parsial = 0;
                $dataItem = array (
                    'status'  => '0',
                );
                $dataTransfer['status'] = '2';
            }
            DB::table('inventory_transfer_out_items')
            ->where('id', $val->inventory_transfer_out_item_id)
            ->update($dataItem);
        }

        $TransferOut = InventoryTransferOut::findOrFail($transfer->transfer_out_id);
        $TransferOut->update($dataTransfer);

        $transfer->delete();

        return redirect()->route('logistic.transfer_in.index')->with(['success' => 'Delete Data Berhasil!']);

    }


    public function print($id)
    {

        $id = Hashids::decode($id);
        $transfer      = InventoryTransferIn::getById($id['0']);
        $transfer_items= InventoryTransferInItem::getByTransferId($transfer->id);

        return view('logistic.transfer_in.print', compact('transfer','transfer_items'));
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

        if($request->input('location_id')) $search .= "<strong> Lokasi: </strong>".getDataByID('locations',$request->input('location_id'))->name;
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
        return view('logistic.transfer_in.search', compact('data','location','search','query','type'));
    }


    public function export(Request $request)
    {
        return Excel::download(new InventoryTransferInExport($request->get('location_id'), $request->get('type'), $request->get('start_date'), $request->get('end_date')), 'Report-Warehous-Transfer-In.xlsx');
    }


    public function received(Request $request)
    {

        if($request->isMethod('get')){
            $id            = Hashids::decode($request->get('id'));
            $transfer      = InventoryTransferOut::getById($id['0']);
            $transfer_items= InventoryTransferOutItem::getByTransferId($id['0'],'status');
            return view('logistic.transfer_in.received', compact('transfer','transfer_items'));
        }else{


            if ($request->get('status')==1) {
                $increment = InventoryTransferIn::whereYear("publish", date('Y'))
                ->where('status', '!=', 0)
                ->where('location_id',$request->get('location_destination'))
                ->count();

                $num = sprintf("%'.05d", $increment + 1) ;
                $no = "WTI-".$request->get('companyCode')."-".$request->get('locationCode')."-".date('my')."-".$num;
                $data['publish'] = date('Y-m-d');
                $data['status']    = 1;
                $data['publish']   = date('Y-m-d');
            }else{
                $no = "WTI-".$request->get('companyCode')."-".$request->get('locationCode')."-".date('my')."-DRAFT";
                $data['status']        = 0;
            }

            $data['received_date']   = $request->get('received_date');
            $data['received']        = $request->get('received');
            $data['transfer_out_id'] = $request->get('transfer_out_id');
            $data['location_id']     = $request->get('location_destination');
            $data['doc_no']          = $no;
            $data['type']            = $request->get('wtotype');
            $data['created_by']      = Auth::user()->id;

            DB::beginTransaction();
            try {

                $trf = InventoryTransferIn::create($data);

                $invHistory =  $dataTransferItem = $parsial = $parsialItem = $qty_parsial =  $ids =  $status = [];

                $transfer = InventoryTransferOut::findOrFail($request->get('transfer_out_id'));
                $product = $request->get('transfer_item_id');

                for($i=0;$i < count($product);$i++) {
                    if (in_array($request->get('transfer_item_id')[$i], $request->get('iscreate'))) {

                        $dataTransferItem[] = [
                            'inventory_transfer_id'   => $trf->id,
                            'inventory_transfer_out_item_id'  => $request->get('transfer_item_id')[$i],
                            'qty'           => $request->get('qty')[$i],
                            'notes'         => $request->get('notes')[$i],
                        ];

                        $ids[] = $request->get('transfer_item_id')[$i];

                        if($request->get('qty_transfer')[$i] == $request->get('qty')[$i]){
                            $status[]       = "WHEN id = {$request->get('transfer_item_id')[$i]} THEN 1";
                            $qty_parsial [] = "WHEN id = {$request->get('transfer_item_id')[$i]} THEN 0";
                            $parsial[]      = '1';
                        }else{
                            $qtyParsial     = $request->get('qty_transfer')[$i] - $request->get('qty')[$i];
                            $status[]       = "WHEN id = {$request->get('transfer_item_id')[$i]} THEN 2";
                            $qty_parsial [] = "WHEN id = {$request->get('transfer_item_id')[$i]} THEN ".$qtyParsial;
                            $parsial[]      = '2';
                        }
                        $parsialItem[]  = '1';

                        $inv  = Inventory::where('location_id',$request->get('location_destination'))
                            ->where('product_id',$request->get('product_id')[$i])->first();

                        if($inv){
                            $invHistory = [
                                'inventory_id'  => $inv->id,
                                'qty_in'        => $request->get('qty')[$i],
                                'qty_awal'      => $inv->stock_onhand,
                                'message'       => $no,
                                'description'   => 'dari '.$request->get('transfer_out_doc_no'),
                            ];

                            $data['price']        = $inv->price;
                            $data['in']           = $inv->in + $request->get('qty')[$i];
                            $stock_onhand         = $inv->stock_onhand + $request->get('qty')[$i];
                            $data['stock_onhand'] = $stock_onhand;
                            $data['stock_status'] = getStatusInventory($inv->stock_max, $inv->stock_min, $stock_onhand,'raw');
                            $inv->update($data);

                        }else{

                            $dataInv = [
                                'product_id'    => $request->get('product_id')[$i],
                                'location_id'   => $request->get('location_destination'),
                                'stock_onhand'  => $request->get('qty')[$i],
                                'initial'       => 0,
                                'in'            => $request->get('qty')[$i],
                                'measure_id'    => $request->get('productsatuanidinv')[$i],
                                'stock_status'  => 'Over Stock',
                                'uuid'          => Str::uuid(),
                                'price'         => $request->get('product_price')[$i]
                            ];

                            $inv = Inventory::create($dataInv);

                            $invHistory = [
                                'inventory_id'  => $inv->id,
                                'qty_in'        => $request->get('qty')[$i],
                                'qty_awal'      => 0,
                                'message'       => $no,
                                'description'   => 'dari '.$request->get('transfer_out_doc_no'),
                            ];
                        }
                        if ($request->get('status')==1) {
                            InventoryHistory::insert($invHistory);
                        }

                    }else{
                        $parsialItem[]  = '2';
                    }
                }

                $ids        = implode(',', $ids);
                $status     = implode(' ', $status);
                $qty_parsial= implode(' ', $qty_parsial);

                \DB::update("UPDATE inventory_transfer_out_items SET status = CASE {$status} END, qty_parsial = CASE {$qty_parsial} END WHERE id in ({$ids})");

                if (in_array('2', $parsial) || in_array('2', $parsialItem)) {
                    $dataTransferOut['status'] = '4';
                } else {
                    $dataTransferOut['status'] = '5';
                }

                $transfer->update($dataTransferOut);
                InventoryTransferInItem::insert($dataTransferItem);

                DB::commit();
                return redirect()->route('logistic.transfer_in.index')->with(['success' => 'Berhasil melakukan Penerimaan Warehouse Transfer Stock!']);
            } catch (\Exception $e) {
                DB::rollback();
                return redirect()->back()->withInput()->withErrors(['error' => $e->getMessage()]);
            }
        }
    }


    public function add()
    {
        return view('logistic.transfer_in.add');
    }

    public function add_datatables()
    {

        if(isAdministrator()){
            $result  = DB::table('inventory_transfer_out')
            ->select('inventory_transfer_out.*','users.name AS created')
            ->leftJoin('users', 'users.id', '=', 'inventory_transfer_out.created_by')
            ->whereIn('inventory_transfer_out.status',[2,4])
            ->orderBy('inventory_transfer_out.created_at', 'DESC');
        }elseif(isAdministratorCompany() ){
            $result  = DB::table('inventory_transfer_out')
            ->select('inventory_transfer_out.*','users.name AS created')
            ->leftJoin('locations', 'locations.id', '=', 'inventory_transfer_out.location_destination')
            ->leftJoin('companies', 'companies.id', '=', 'locations.company_id')
            ->leftJoin('users', 'users.id', '=', 'inventory_transfer_out.created_by')
            ->where('companies.id', Auth::user()->company_id)
            ->whereIn('inventory_transfer_out.status',[2,4])
            ->orderBy('inventory_transfer_out.created_at', 'DESC');
        }elseif(isAdministratorLocation()){
            $result  = DB::table('inventory_transfer_out')
            ->select('inventory_transfer_out.*','users.name AS created')
            ->leftJoin('users', 'users.id', '=', 'inventory_transfer_out.created_by')
            ->where('inventory_transfer_out.location_id', Auth::user()->location_id)
            ->whereIn('inventory_transfer_out.status',[2,4])
            ->orderBy('inventory_transfer_out.created_at', 'DESC');
        }else{
            $result  = DB::table('inventory_transfer_out')
            ->select('inventory_transfer_out.*','users.name AS created')
            ->leftJoin('users', 'users.id', '=', 'inventory_transfer_out.created_by')
            ->where('inventory_transfer_out.location_id', Auth::user()->location_id)
            ->whereIn('inventory_transfer_out.status',[2,4])
            ->orderBy('inventory_transfer_out.created_at', 'DESC');
        }


        return  DataTables::of($result)

        ->addColumn('action', function ($result) {
            $url_accept = "<a href='".route('logistic.transfer_in.received', ['id' => Hashids::encode($result->id)])."' title='Terima Barang' data-toggle='tooltip' class='btn btn-danger btn-sm btn-outline'>Terima Barang </a>";
            return $url_accept;

        })
        ->editColumn('created_at', function ($result) {
            return $result->created_at ? with(new Carbon($result->created_at))->format('d/m/Y H:i:s') : '';
        })
        ->rawColumns(['action', 'status'])
        ->make(true);

    }

    public function check($id)
    {
        if(!Gate::allows('checker_wti')){
            return redirect()->back()->with('error', 'Akunmu tidak diberi akses untuk melakukan pengecekan');
        }
        $id = Hashids::decode($id);
        $type_replacement = array(
            '1' => 'Ya',
            '0' => 'Tidak'
        );
        $transfer      = InventoryTransferIn::getById($id['0']);
        if($transfer->type != 1){
            return redirect()->back()->with('error', 'Type dokumen WTI bukan WTI Peminjaman');
        }
        if($transfer->status != 2){
            return redirect()->back()->with('error', 'Status dokumen WTI bukan dalam proses pengecekan');
        }
        $transfer_items= InventoryTransferInItem::getByTransferId($id['0']);
        return view('logistic.transfer_in.check', compact('transfer','transfer_items','type_replacement'));
    }
    public function check_store(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            $inData = InventoryTransferIn::findOrFail($id);
            $dataIn['status'] = 1;
            $inData->update($dataIn);
            $items  = $request->get('id_inv_in_item');

            for ($i = 0; $i < count($items); $i++) {
                $data['type_replacement'] = $request->get('type_replacement')[$i];
                $data['type_replacement_notes'] = $request->get('type_replacement_notes')[$i];
                $data['checked_by'] = Auth::user()->id;
                $data['checked_at'] = now();

                $inItem = InventoryTransferInItem::findOrFail($items[$i]);
                $inItem->update($data);
            }
            $transfer      = InventoryTransferIn::getById($id);
            $transfer_items= InventoryTransferInItem::getByTransferId($id);
            DB::commit();
            return view('logistic.transfer_in.show', compact('transfer','transfer_items'))->with('success', 'Pengecekan Data Berhasil!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
