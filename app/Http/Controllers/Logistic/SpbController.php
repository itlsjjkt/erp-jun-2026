<?php

namespace App\Http\Controllers\Logistic;

use App\Models\Lpb;
use App\Models\Spb;
use App\Models\SpbKoli;
use App\Models\SpbHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use App\Mail\SendMailable;
use App\Models\LpbItem;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Vinkla\Hashids\Facades\Hashids;
use App\Models\Notification;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Carbon\Carbon;
use App\User;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Auth;
use PDF;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Rap2hpoutre\FastExcel\FastExcel;
use App\Traits\UploadTrait;

class SpbController extends Controller
{
    use UploadTrait;

    public function __construct (){
        $this->type = array(
            'SPB Cargo' => 'SPB Cargo',
            'SPB Vendor-Cargo' => 'SPB Vendor-Cargo',
            'SPB Vendor' => 'SPB Vendor',
            'SPB Hand Carry' => 'SPB Hand Carry'
        );
        $this->pickup = array (
            'Non Pick Up' => 'Normal',
            'Pick Up' => 'Pick Up'
        );
        $this->jalur = array(
            ''         => '',
            'VIA DARAT' => 'VIA DARAT',
            'VIA UDARA' => 'VIA UDARA',
            'VIA LAUT' => 'VIA LAUT'
        );
    }
    /**
     * Display a listing of User.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (Gate::allows('spb') || Gate::allows('spb_monitoring')) {
            $type = $this->type;
            $company   = DB::table('companies')->pluck('name','id')->prepend('Silahkan pilih...', '');
            return view('logistic.spb.index', compact('type','company'));
        }else{
            return abort(401);
        }
    }

    public function datatables (Request $request)
    {
        if (Gate::allows('spb') || Gate::allows('spb_monitoring')) {
            $data = $request->all();
            if(isAdministrator() || isAdmin() || Gate::allows('spb_monitoring')) $result  = Spb::getData($data);
            else $result  = Spb::getData($data, Auth::user()->id);
            return  DataTables::of($result)
            ->addColumn('action', function ($result) {
                $url_revision = "<a href='".route('logistic.spb.edit',  Hashids::encode($result->id))."?param=revision' title='Revision' data-toggle='tooltip' class='btn btn-outline'><span class='ti-pencil-alt icon-lg'></span> </a>";
                $url_edit = "<a href='".route('logistic.spb.edit', Hashids::encode($result->id))."' title='".trans('Edit')."' data-toggle='tooltip' class='btn btn-outline'><span class='ti-pencil icon-lg'></span> </a>";
                $url_view = "<a href='".route('logistic.spb.show', Hashids::encode($result->id))."' title='".trans('app.show_title')."' data-toggle='tooltip' class='btn btn-outline'><span class='ti-eye icon-lg'></span> </a>";
                $url_delete = "<form class='delete' action='".route('logistic.spb.delete', ['id' => $result->id])."' method='POST'>
                                    ".csrf_field()."
                                    <button class='btn btn-outline text-danger' title='".trans('app.delete_title')."' data-toggle='tooltip'><i class='ti-trash icon-lg'></i></button>
                                </form>";
                $url_rollback = "<form class='reversal' action='".route('logistic.spb.delete', ['id' => $result->id])."' method='POST'>
                                <input type='hidden' name='action' value='reversal'>
                                ".csrf_field()."
                                <button class='btn btn-outline text-danger' title='Kembalikan ke LPB' data-toggle='tooltip'><i class='ti-back-left icon-lg'></i></button>
                            </form>";
                
                $url_set_selesai = '<button data-id='.Hashids::encode($result->id).' data-doc='.$result->doc_no.' type="submit" title="SET SELESAI" class="btn btn-outline text-primary btn_set_done" style=:"font-weight: bold;" data-toggle="modal" data-target="#modalSetSelesai"><span class="ti-thumb-up icon-lg"></span></button>';
                if(Gate::allows('spb_monitoring')){
                    return '<div class="btn-group">'.$url_view.'</div>';
                }else{
                    if($result->status==0){
                        return '<div class="btn-group">'.$url_edit .$url_view .$url_delete.'</div>';
                    }elseif($result->status==1){
                        if($result->receipt_type == 'non_bpb'){
                            return '<div class="btn-group">'.$url_view.$url_set_selesai.'</div>';
                        }else{
                            return '<div class="btn-group">'.$url_view .$url_rollback.'</div>';
                        }
                    }else{
                        return '<div class="btn-group">'.$url_view.'</div>';
                    }
                }
            })
            ->editColumn('status', function ($result) {
                return getStatusSPB($result->status);
            })
            ->editColumn('type', function ($result) {
                $pickup = null;
                if($result->is_pickup == true) $pickup = ' [Pick Up]';
                $typeee = $result->type. ' '.$pickup;
                return $typeee;
            })
            ->editColumn('created_at', function ($result) {
                return $result->created_at  ? with(new Carbon($result->created_at ))->format('d/m/Y H:i:s' ) : '';
            })
            ->rawColumns(['action', 'status','payment_amount','type'])
            ->make(true);
        }else{
            return abort(401);
        }
    }

    public function list()
    {
        if (! Gate::allows('spb')) {
            return abort(401);
        }
        return view('logistic.spb.list');
    }

    public function listDatatables()
    {

        if (isAdministrator()) {
            $result = DB::table('lpb')
            ->select(
                'lpb.*',
                'po.doc_no AS po_no',
                'departments.name AS department',
                'purchase_requisitions.dpm_no AS dpm_no',
                'suppliers.name AS supplier'
            )
            ->leftJoin('po', 'po.id', '=', 'lpb.po_id')
            ->leftJoin('purchase_requisitions', 'po.purchase_id', '=', 'purchase_requisitions.id')
            ->leftJoin('suppliers', 'suppliers.id', '=', 'po.supplier_id')
            ->leftJoin('departments', 'departments.id', '=', 'purchase_requisitions.department_id')
            ->where('lpb.status', 1)
            ->whereIn('lpb.spb_status', [0,2])
            ->orderBy('lpb.created_at', 'DESC') ;
        }else{
            $result = DB::table('lpb')
            ->select('lpb.*','po.doc_no AS po_no',
            'departments.name AS department',
            'purchase_requisitions.doc_no AS pr_no',
            'purchase_requisitions.dpm_no AS dpm_no',
            'suppliers.name AS supplier'
            )
            ->leftJoin('po', 'po.id', '=', 'lpb.po_id')
            ->leftJoin('purchase_requisitions', 'po.purchase_id', '=', 'purchase_requisitions.id')
            ->leftJoin('departments', 'departments.id', '=', 'purchase_requisitions.department_id')
            ->leftJoin('suppliers', 'suppliers.id', '=', 'po.supplier_id')
            ->where('lpb.location_id', Auth::user()->location_id)
            ->where('lpb.status', 1)
            ->whereIn('lpb.spb_status', [0,2])
            ->orderBy('lpb.created_at', 'DESC');
        }

       return  DataTables::of($result)
        ->make(true);
    }


    public function create(Request $request)
    {

        $lpbID = explode(',',$request->get('lpb_id'));

        $lpb = DB::table('lpb')
        ->select(
            'lpb.*',
            'po.doc_no AS po_no',
            'suppliers.id AS supplierID',
            'suppliers.name AS supplier'
        )
        ->leftJoin('po', 'po.id', '=', 'lpb.po_id')
        ->leftJoin('suppliers', 'suppliers.id', '=', 'po.supplier_id')
        ->leftJoin('purchase_requisitions', 'purchase_requisitions.id', '=', 'po.purchase_id')
        ->whereIn('lpb.id', $lpbID)
        ->orderBy('lpb.doc_no', 'ASC')
        ->get();

        $lpb_id = implode(',', $lpbID);
        $ekspedisi  = DB::table('expeditions')->where('is_handcarry',false)->pluck('name','id')->prepend('Pilih Ekspedisi', '');
        $handcarry  = DB::table('expeditions')->where('is_handcarry',true)->pluck('name','id')->prepend('Pilih Hand Carry', '');
        $operator   = DB::table('spb_operators')->pluck('name','name')->prepend('Silahkan pilih...', '');
        $company   = DB::table('companies')->pluck('name','id')->prepend('Silahkan pilih...', '');
        $type = $this->type;
        $jalur = $this->jalur;
        $pickup = $this->pickup;

        return view('logistic.spb.create', compact('jalur','pickup','handcarry','lpb','ekspedisi','lpb_id','type','operator','company'));

    }


    public function remove(Request $request)
    {
        $lpbID = $request->id;
        $lpb_array = explode(',',$request->lpb_id);
        $lpb_id = array_diff( $lpb_array , [$lpbID]);

        $lpb = DB::table('lpb')
        ->select('lpb.*',
        'po.doc_no AS po_no',
        'suppliers.id AS supplierID',
        'suppliers.name AS supplier',
        )
        ->leftJoin('po', 'po.id', '=', 'lpb.po_id')
        ->leftJoin('suppliers', 'suppliers.id', '=', 'po.supplier_id')
        ->leftJoin('purchase_requisitions', 'purchase_requisitions.id', '=', 'po.purchase_id')
        ->whereIn('lpb.id', $lpb_id)
        ->orderBy('lpb.doc_no', 'ASC')
        ->get();

        $lpb_id = implode(',',$lpb_id);

        $ekspedisi  = DB::table('expeditions')->where('is_handcarry',false)->pluck('name','id')->prepend('Pilih Ekspedisi', '');
        $handcarry  = DB::table('expeditions')->where('is_handcarry',true)->pluck('name','id')->prepend('Pilih Hand Carry', '');
        $operator   = DB::table('spb_operators')->pluck('name','name')->prepend('Silahkan pilih...', '');
        $company   = DB::table('companies')->pluck('name','id')->prepend('Silahkan pilih...', '');
        $type = $this->type;
        $jalur = $this->jalur;
        $pickup = $this->pickup;

        return view('logistic.spb.create', compact('pickup','handcarry','jalur','lpb','ekspedisi','lpb_id','type','operator', 'company'));
    }


    public function store(Request $request)
    {
        if (! Gate::allows('spb')) {
            return abort(401);
        }
        DB::beginTransaction();

        try {
            // Create SPB
            $number_of_spb = $request->get('numberOfSpb') ?? 1;
            for ($i=1; $i<=$number_of_spb; $i++){
                // if ($request->get('status')==1) {
                $increment = Spb::whereYear("created_at", date('Y'))
                ->where('status','!=', 0)
                ->count();

                $num = sprintf("%'.05d", $increment + 1) ;
                if($number_of_spb > 1){
                    $doc_num = sprintf("%'.03d", $i) ;
                    $no_spb = "SPB-JKT-".date('my')."-".$num."-".$doc_num;
                } else{
                    $no_spb = "SPB-JKT-".date('my')."-".$num;
                }

                $data['status'] = 1;
                
                if($i != $number_of_spb && $number_of_spb > 1){
                    $data['receipt_type'] = 'non_bpb';
                }else{
                    $data['receipt_type'] = 'bpb';
                }
                
                $data['publish'] = date('Y-m-d H:i:s');

                if ($request->get('is_pickup')[$i] == 'Pick Up'){
                    $data['pickup_from']= $request->get('pickup_from')[$i];
                    $data['pickup_address']= $request->get('pickup_address')[$i];
                    $data['pickup_pic_name']= $request->get('pickup_pic_name')[$i];
                    $data['pickup_pic_telp']= $request->get('pickup_pic_telp')[$i];
                    $data['is_pickup']= true;
                }
                else {
                    $data['is_pickup']= false;
                }

                if ($request->get('type')[$i] == 'SPB Hand Carry'){
                    $data['delivered_by']=$request->get('delivered_by2')[$i];
                }else {
                    $data['delivered_by']=$request->get('delivered_by')[$i];
                }

                $data['created_by']  = Auth::user()->id;
                $data['doc_no'] = $no_spb;
                $data['type'] = $request->get('type')[$i];
                $data['date_transaction'] = $request->get('date_transaction')[$i];
                $data['estimate_receives'] = $request->get('estimate_receives')[$i];
                $data['delivered_pic'] = $request->get('delivered_pic')[$i];
                $data['delivered_pic_telp'] = $request->get('delivered_pic_telp')[$i];
                $data['jalur_pengiriman'] = $request->get('jalur_pengiriman')[$i];
                $data['operator'] = $request->get('operator')[$i];
                $data['checker'] = $request->get('checker')[$i];
                $data['company_id'] = $request->get('company_id')[$i];
                $data['received_pic'] = $request->get('received_pic')[$i];
                $data['received_pic_telp'] = $request->get('received_pic_telp')[$i];
                $data['address'] = $request->get('address')[$i];
                $data['notes'] = $request->get('notes')[$i];
                $spb = Spb::create($data);


                // Create SPB Koli
                $selectedItems = $request->input('selected_items', []);
                $lpb_ids = [];
                foreach ($selectedItems as $id) {
                    $dataItemSPB = [];
                    $lpb_ids[] = $request->input("lpb_id.$id");
                    $dataItemSPB[] = [
                        'spb_item_id' => $request->input("lpb_item_id.$id"),
                        'qty' => $request->input("qty_spb.$id"),
                        'spb_id' => $spb->id,
                        'lpb_id' => $request->input("lpb_spb_id.$id"),
                        'location_id' => $request->input("location_id.$id"),
                        'pr_item_id' => $request->input("pr_item_id.$id"),
                        'annotation' => $request->input("annotation.$id"),
                        'uuid' => Str::uuid(),
                    ];
                    SpbKoli::insert($dataItemSPB);

                    // Update LPB Item (Status and Qty Parsial)
                    if($i == 1){
                        $lpb_item = LpbItem::findOrFail($request->input("lpb_item_id.$id"));
                        if($lpb_item->status == 0){
                            $qty_remaining = $lpb_item->qty - $request->input("qty_spb.$id");
                        } elseif($lpb_item->status == 2){
                            $qty_remaining = $lpb_item->qty_parsial - $request->input("qty_spb.$id");
                        }
                        if($qty_remaining > 0){
                            $lpb_item_new['qty_parsial'] = $qty_remaining;
                            $lpb_item_new['status'] = 2;
                        } else {
                            $lpb_item_new['qty_parsial'] = 0;
                            $lpb_item_new['status'] = 1;
                        }
                        $lpb_item->update($lpb_item_new);
                    }
                }

                // Create lpb_id for SPB table
                $lpb_ids = array_unique($lpb_ids);

                // Update LPB (Status)
                if($i == 1){
                    foreach($lpb_ids as $item){
                        $getAllLpbItem = LpbItem::where('lpb_id', $item)->pluck('status')->toArray();
                        if (!in_array(0, $getAllLpbItem) && !in_array(2, $getAllLpbItem)) {
                            $lpbData['spb_status'] = 1;
                        } elseif(!in_array(1, $getAllLpbItem) && !in_array(2, $getAllLpbItem)) {
                            $lpbData['spb_status'] = 0;
                        } else{
                            $lpbData['spb_status'] = 2;
                        }
                        $lpb = Lpb::where('id', $item)->first();
                        $lpb->update($lpbData);
                    }
                }
                $lpb_ids = implode(',', $lpb_ids);
                $data['lpb_id'] = $lpb_ids;
                $spb->update($data);

                // \DB::update("UPDATE lpb SET spb_status = 1 WHERE id IN ($lpb_ids)");

                $dataHistory =  array(
                    'spb_id' => $spb->id,
                    'user_id' => Auth::user()->id,
                    'jenis' => 'insert'
                );
                SpbHistory::create($dataHistory);
            }
            DB::commit();

            if($number_of_spb > 1){
                return redirect()->route('logistic.spb.index')->with(['success' => 'Input Data Berhasil!']);
            }
            return redirect()->route('logistic.spb.show', Hashids::encode($spb->id))->with(['success' => 'Input Data Berhasil!']);

        } catch (\Exception $e) {

            DB::rollback();
            return redirect()->back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }

    }


    public function sendEmail($id,$type){

        $spb         = Spb::getByID($id);
        $spb_items   = Spb::getProductItem($id);
        $supplier    = getSupplierByLPB($spb->lpb_id);

        $data['spb'] = $spb;
        $data['spb_items'] = $spb_items;
        $data['supplier']  = $supplier;

        $pdf = PDF::loadView('logistic.spb.pdf', $data);
        $pdf->setPaper('A4', 'landscape');

        $msgData = array(
            'title'         => 'Informasi SPB',
            'no_spb'        => $spb->doc_no,
            'operatorName'  => $spb->operator,
            'vendorName'    => $supplier->name,
            'vendorPIC'     => $supplier->supplierPIC,
            'email'         => $supplier->supplierEmail,
            'companyName'   => $spb->companyName,
            'companyCode'   => $spb->companyCode,
            'locationName'  => $spb->locationName,
        );

        try {
            Mail::send('emails.spb', $msgData, function ($message) use ($msgData,$pdf) {
                $message->to($msgData['email'], $msgData['vendorName'])
                ->cc($msgData['email_cc'])
                ->subject('Informasi '.  $msgData['no_spb'])
                ->attachData($pdf->output(), $msgData['no_spb'].".pdf");
            });
        } catch (Exception $ex) {
            return "We've got errors!";
        }

    }


    public function show($id)
    {

        $id = Hashids::decode($id);

        $spb         = Spb::findOrFail($id['0']);
        $spb_items   = Spb::getProductItem($id['0']);
        $spb_history = Spb::getHistory($id['0']);
        $supplier  = getSupplierByLPB($spb->lpb_id);
        $location = explode('-',$spb->doc_no);

        $not_insurance_spb_item = $spb_items->where('status_insurance', '!=', 1);
        $totalPrice = 0;
        foreach($not_insurance_spb_item as $item){
            $totalPrice += $item->price * $item->qty_kolis * $item->conversion_idr;
        }

        return view('logistic.spb.show', compact('spb', 'spb_items','spb_history','supplier','totalPrice'));
    }

    /**
     * Show the form for editing User.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id, Request $request)
    {
        if (! Gate::allows('spb')) {
            return abort(401);
        }

        $id = Hashids::decode($id);
        $spb  = Spb::getByID($id['0']);
        $lpbID = explode(',',$spb->lpb_id);
        $jalur = $this->jalur;
        $pickup = $this->pickup;

        $lpb = DB::table('lpb')
        ->select(
            'lpb.*',
            'po.doc_no AS po_no',
            'suppliers.id AS supplierID',
            'suppliers.name AS supplier'
        )
        ->leftJoin('po', 'po.id', '=', 'lpb.po_id')
        ->leftJoin('suppliers', 'suppliers.id', '=', 'po.supplier_id')
        ->leftJoin('purchase_requisitions', 'purchase_requisitions.id', '=', 'po.purchase_id')
        ->whereIn('lpb.id', $lpbID)
        ->orderBy('lpb.doc_no', 'ASC')
        ->get();

        $operator = DB::table('spb_operators')->pluck('name','name')->prepend('Silahkan pilih..');
        $ekspedisi = DB::table('expeditions')->where('is_handcarry',false)->pluck('name','id')->prepend('Pilih Ekspedisi', '');
        $handcarry  = DB::table('expeditions')->where('is_handcarry',true)->pluck('name','id')->prepend('Pilih Hand Carry', '');
        $type = $this->type;
        $company   = DB::table('companies')->pluck('name','id')->prepend('Silahkan pilih...', '');

        $param = $request->get('param');
        if(isset($param) && $param !='revision') abort(404);
        if($param =='revision') return view('logistic.spb.revision', compact('pickup','handcarry','jalur','spb','lpb','ekspedisi','type','operator','company',));
        else return view('logistic.spb.edit', compact('pickup','handcarry','jalur','spb','lpb','ekspedisi','type','operator','company',));

    }


    public function update(Request $request, $id)
    {
        if (! Gate::allows('spb')) {
            return abort(401);
        }

        $spb = Spb::findOrFail($id);
        $data = $request->all();

        if ($request->get('status')==1) {
            $increment = Spb::whereYear("created_at", date('Y'))
            ->where('status','!=', 0)
            ->count();
            $num = sprintf("%'.05d", $increment + 1) ;
            $no_spb = "SPB-JKT-".date('my')."-".$num;
            $data['doc_no']    = $no_spb;
            $data['status']    = 1;
            $data['publish']   = date('Y-m-d H:i:s');
        }else{
            $data['status']  = 0;
        }

        $data['updated_by']= Auth::user()->id;
        if($request->get('is_pickup') == 'Pick Up'){
            $data['pickup_from']= $request->get('pickup_from');
            $data['pickup_address']= $request->get('pickup_address');
            $data['pickup_pic_name']= $request->get('pickup_pic_name');
            $data['pickup_pic_telp']= $request->get('pickup_pic_telp');
            $data['is_pickup'] = true;
        }
        else{
            $data['pickup_from']= null;
            $data['pickup_address']= null;
            $data['pickup_pic_name']= null;
            $data['pickup_pic_telp']= null;
            $data['is_pickup'] = false;
        }

        $data['jalur_pengiriman'] = $request->get('jalur_pengiriman');

        if($request->get('type') == 'SPB Hand Carry'){
            $data['delivered_by']=$request->get('delivered_by2');
        }
        else{
            $data['delivered_by']=$request->get('delivered_by');
        }

        $spb->update($data);

        $ids = $annotation = $qty = [];
        $product = $request->get('lpb_item_id');

        for($i=0;$i < count($product);$i++) {
            $ids[] = $request->get('lpb_item_id')[$i];
            $annotation[] = "WHEN spb_item_id = {$request->get('lpb_item_id')[$i]} THEN '".$request->get('annotation')[$i]."'";
            $qty[] = "WHEN spb_item_id = {$request->get('lpb_item_id')[$i]} THEN ".$request->get('qty_spb')[$i];
        }

        $ids = implode(',', $ids);
        $qty = implode(' ', $qty);
        $annotation = implode(' ', $annotation);

        \DB::update("UPDATE spb_kolis SET qty = CASE {$qty} END , annotation = CASE {$annotation} END WHERE id in ({$ids})");

        $dataHistory =  array(
            'spb_id' => $spb->id,
            'user_id' => Auth::user()->id,
            'jenis' => 'update'
        );
        SpbHistory::create($dataHistory);

        return redirect()->route('logistic.spb.show', Hashids::encode($spb->id))->with(['success' => 'Edit Data Berhasil!']);

    }


    public function publish(Request $request, $id)
    {
        if (! Gate::allows('spb')) {
            return abort(401);
        }

        $spb = Spb::findOrFail($id);
        $data = $request->all();

        $increment = Spb::whereYear("created_at", date('Y'))
        ->where('status','!=', 0)
        ->count();
        $num = sprintf("%'.05d", $increment + 1) ;
        $no_spb = "SPB-JKT-".date('my')."-".$num;

        $data['doc_no']  = $no_spb;
        $data['status'] = 1;
        $data['publish'] = date('Y-m-d H:i:s');
        $data['updated_by']  = Auth::user()->id;
        $data['jalur_pengiriman'] = $request->get('jalur_pengiriman');
        if($request->get('is_pickup') == 'Pick Up'){
            $data['pickup_from']= $request->get('pickup_from');
            $data['pickup_address']= $request->get('pickup_address');
            $data['pickup_pic_name']= $request->get('pickup_pic_name');
            $data['pickup_pic_telp']= $request->get('pickup_pic_telp');
            $data['is_pickup'] = true;
        }
        if($request->get('is_pickup') != 'Pick Up'){
            $data['pickup_from']= null;
            $data['pickup_address']= null;
            $data['pickup_pic_name']= null;
            $data['pickup_pic_telp']= null;
            $data['is_pickup'] = false;
        }
        if($request->get('type') == 'SPB Hand Carry'){
            $data['delivered_by']=$request->get('delivered_by2');
        }
        else{
            $data['delivered_by']=$request->get('delivered_by');
        }

        $spb->update($data);

        $dataHistory =  array(
            'spb_id' => $spb->id,
            'user_id' => Auth::user()->id,
            'jenis' => 'publish'
        );
        $spb_history = SpbHistory::create($dataHistory);

        if ($request->get('tipe') == 'SPB Vendor' || $request->get('tipe') == 'SPB Vendor-Cargo') {

            $supplier = getSupplierByLPB($spb->lpb_id);

            if($supplier->email != NULL || $supplier->email != '' ){
                if (config('app.mail_status')=='on') {
                     $this->sendEmail($spb->id,$request->get('tipe'));
                }
                return redirect()->route('logistic.spb.show',Hashids::encode($spb->id))->with(['success' => 'Input Data Berhasil!']);
            }else{
                return redirect()->route('logistic.spb.show',Hashids::encode($spb->id))->with(['success' => 'SPB Berhasil di input, namun email tidak berhasil dikirim ke vendor karena alamat email tidak valid!']);
            }
        }else{
            return redirect()->route('logistic.spb.show',Hashids::encode($spb->id))->with(['success' => 'Input Data Berhasil!']);
        }

    }


    public function revision(Request $request)
    {
        if (! Gate::allows('spb')) {
            return abort(401);
        }

        $spb = Spb::findOrFail($request->get('spb_id'));
        $data = $request->all();
        $data['updated_by'] = Auth::user()->id;
        $data['jalur_pengiriman'] = $request->get('jalur_pengiriman');
        if($request->get('is_pickup') == 'Pick Up'){
            $data['pickup_from']= $request->get('pickup_from');
            $data['pickup_address']= $request->get('pickup_address');
            $data['pickup_pic_name']= $request->get('pickup_pic_name');
            $data['pickup_pic_telp']= $request->get('pickup_pic_telp');
            $data['is_pickup'] = true;
        }
        if($request->get('is_pickup') != 'Pick Up'){
            $data['pickup_from']= null;
            $data['pickup_address']= null;
            $data['pickup_pic_name']= null;
            $data['pickup_pic_telp']= null;
            $data['is_pickup'] = false;
        }
        if($request->get('type') == 'SPB Hand Carry'){
            $data['delivered_by']=$request->get('delivered_by2');
        }
        else{
            $data['delivered_by']=$request->get('delivered_by');

        }

        $spb->update($data);

        $ids = $annotation = $qty = [];
        $product = $request->get('lpb_item_id');

        for($i=0;$i < count($product);$i++) {
            $ids[] = $request->get('lpb_item_id')[$i];
            $annotation[] = "WHEN spb_item_id = {$request->get('lpb_item_id')[$i]} THEN '".$request->get('annotation')[$i]."'";
            $qty[] = "WHEN spb_item_id = {$request->get('lpb_item_id')[$i]} THEN ".$request->get('qty_spb')[$i];
        }

        $ids = implode(',', $ids);
        $qty = implode(' ', $qty);
        $annotation = implode(' ', $annotation);

        \DB::update("UPDATE spb_kolis SET qty = CASE {$qty} END , annotation = CASE {$annotation} END WHERE id in ({$ids})");

        $dataHistory =  array(
            'spb_id' => $spb->id,
            'user_id' => Auth::user()->id,
            'jenis' => 'revision'
        );

        SpbHistory::create($dataHistory);

        return redirect()->route('logistic.spb.show',Hashids::encode($spb->id))->with(['success' => 'Revisi Data Berhasil!']);

    }


    public function remove_lpb(Request $request)
    {

        if (! Gate::allows('spb')) {
            return abort(401);
        }
        $spb = Spb::findOrFail($request->spb_id);
        $lpb = Lpb::findOrFail($request->lpb_id);

        $lpbID = explode(',',$spb->lpb_id);

        if (($key = array_search($request->lpb_id,$lpbID)) !== false) unset($lpbID[$key]);

        $spb->update(array(
            'lpb_id' =>  implode(',', $lpbID)
        ));
        $lpb->update(array(
            'spb_status' => 0
        ));
        SpbKoli::where('spb_id',$request->spb_id)->where('lpb_id',$request->lpb_id)->delete();
        return redirect()->back()->with(['success' => 'Delete Data LPB Berhasil!']);

    }


    public function delete(Request $request)
    {

        if (! Gate::allows('spb')) {
            return abort(401);
        }
        $spb  = Spb::findOrFail($request->id);

        DB::beginTransaction();

        try {

            if ($spb->lpb_id) {
                $lpb = explode(',', $spb->lpb_id);
                
                for ($i = 0; $i < count($lpb); $i++) {
                    $ids[]   = $lpb[$i];
                    $cases[] = "WHEN id = {$lpb[$i]} THEN 0";
                }
                
                $ids   = implode(',', $ids);
                $cases = implode(' ', $cases);
                
                \DB::update("UPDATE lpb SET spb_status = CASE {$cases} END WHERE id in ({$ids})");
            
                $casesItems = [];
                foreach ($lpb as $id) {
                    $casesItems[] = "WHEN lpb_id = {$id} THEN 0";
                }
            
                $casesItems = implode(' ', $casesItems);
            
                \DB::update("UPDATE lpb_items SET status = CASE {$casesItems} END WHERE lpb_id IN ({$ids})");
            }

            $dataHistory =  array(
                'spb_id' => $spb->id,
                'user_id' => Auth::user()->id,
                'jenis' => 'reversal'
            );
            SpbHistory::create($dataHistory);

            $spb->update(array('status' => 4));
            DB::commit();
            return redirect()->route('logistic.spb.index')->with(['success' => 'Delete Data Berhasil!']);
        } catch (\Exception $e) {

            DB::rollback();
            return redirect()->back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }

    }


    public function search(Request $request)
    {
        if (! Gate::allows('spb')) {
            return abort(401);
        }

        $query = 'type='.$request->get('type').'&company_id='. $request->get('company_id').'&start_date='.$request->get('start_date').'&end_date='. $request->get('end_date');

        $data = $request->all();

        $search = "Cari Berdasarkan: ";
        if($request->input('type')) $search .= "<strong> Tipe: </strong>".$request->input('type');
        if($request->input('company_id')) $search .= "<strong> Cost SPB: </strong>".getDataByID('companies',$request->input('company_id'))->name;
        if($request->input('start_date') || $request->input('end_date')) $search .= "<strong> Periode: </strong>".$request->input('start_date'). " - ". $request->input('end_date');

        $type = $this->type;
        $company   = DB::table('companies')->pluck('name','id')->prepend('Silahkan pilih...', '');

        return view('logistic.spb.search', compact('data', 'search','query','type','company'));
    }


    public function print($id, $type)
    {

        $id = Hashids::decode($id);

        $spb         = Spb::findOrFail($id['0']);
        $spb_items   = Spb::getProductItem($id['0']);
        $supplier    = getSupplierByLPB($id['0']);
        $supplier = getSupplierByLPB($spb->lpb_id);
        $location = explode('-',$spb->doc_no);

        $not_insurance_spb_item = $spb_items->where('status_insurance', '!=', 1);
        $totalPrice = 0;
        foreach($not_insurance_spb_item as $item){
            $totalPrice += $item->price;
        }

        $data['spb'] = $spb;
        $data['spb_items'] = $spb_items;
        $data['supplier'] =  $supplier;
        $data['totalPrice'] =  $totalPrice;

        $pdf = PDF::loadView('logistic.spb.pdf', $data);
        $pdf->setPaper('A4', 'landscape');
        return $pdf->download($spb->doc_no.'.pdf');

    }


    public function export(Request $request)
    {

        $data = $request->all();

        $query = DB::table('spb_kolis')
	    ->select(
            'spb_kolis.id AS idKoli',
            'spb_kolis.qty AS qtyKoli',
            'spb_kolis.annotation',
            'suppliers.name AS supplier',
            'po.doc_no AS noPO',
            'purchase_requisitions.dpm_no AS noDPM',
            'purchase_requisitions.doc_no AS noPR',
            'lpb.doc_no AS noLPB',
            'po_items.price as price',
            'master_item_products.name AS product', 'master_item_products.id AS productID',
            'companies.name AS company',
            'po_items.specification',
            'suppliers.id AS supplierID',
            'master_item_products.code AS productCode',
            'master_item_products.part_number AS productPartNumber',
            'master_item_brands.name AS productBrand',
            'po_items.measure',
            'spb.*',
            'users.name AS created',
            'expeditions.name AS expedition',
            'departments.name AS department'

        )
        ->leftJoin('lpb_items', 'lpb_items.id', '=', 'spb_kolis.spb_item_id')
        ->leftJoin('po_items', 'po_items.id', '=', 'lpb_items.po_item_id')
        ->leftJoin('purchase_items', 'purchase_items.id', '=', 'po_items.pr_item_id')
        ->leftJoin('lpb', 'lpb_items.lpb_id', '=', 'lpb.id')
        ->leftJoin('po', 'po_items.po_id', '=', 'po.id')
        ->leftJoin('purchase_requisitions', 'purchase_requisitions.id', '=', 'po.purchase_id')
        ->leftJoin('master_item_products', 'master_item_products.id', '=', 'lpb_items.product_id')
        ->leftJoin('master_item_brands', 'master_item_brands.id', '=', 'master_item_products.brand_id')
        ->leftJoin('suppliers', 'suppliers.id', '=', 'po.supplier_id')
		->leftJoin('departments','departments.id','=','purchase_requisitions.department_id')
        ->leftJoin('spb', 'spb.id', '=', 'spb_kolis.spb_id')
        ->leftJoin('companies', 'companies.id', '=', 'spb.company_id')
 	    ->leftJoin('users', 'users.id', '=', 'spb.created_by')
 	    ->leftJoin('expeditions', 'expeditions.id', '=', 'spb.delivered_by')
        ->when(!empty($data['type']), function ($query) use ($data) {
            return $query->where('spb.type',$data['type']);
        })
        ->when(!empty($data['start_date']), function ($query) use ($data) {
            $start = date("Y-m-d",strtotime($data['start_date']));
            $end   = date("Y-m-d",strtotime($data['end_date']."+1 day"));
            return $query->whereBetween('spb.created_at', [$start , $end]);
        })
        ->orderBy('spb.doc_no','ASC')
        ->orderBy(DB::raw('LENGTH(spb_kolis.no::text), spb_kolis.no'))
	    ->get();
        if( $query->isEmpty() ){
            return redirect()->route('logistic.spb.index')->with(['error' => 'Tidak terdapat data untuk di Export']);
        }else{

            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="your_name.xls"');
            header('Cache-Control: max-age=0');
            return (new FastExcel($query))->download('REPORT-SPB-'.date('d-m-Y').'.xlsx', function ($data) {
                return [
                    'Nomor SPB'         => $data->doc_no,
                    'Nomor DPM'         => $data->noDPM,
                    'Nomor PR'          => $data->noPR,
                    'Nomor PO'          => $data->noPO,
		            'Nomor LPB'	        => $data->noLPB,
                    'Tanggal SPB'     => dateTextMySQL2ID($data->date_transaction),
                    'Jenis SPB'         => $data->type,
                    'Cost SPB' 	        => $data->company,
                    'Nama Ekspedisi'    => $data->expedition,
                    'Supplier'          => $data->supplier,
                    'Dibuat'            => $data->created,
                    'Tanggal Input'     => dateTextMySQL2ID($data->created_at),
		            'Kode Barang'       => $data->productCode,
	                'Nama Barang'       => $data->product,
                    'Spesifikasi'       => trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags($data->specification)))),
                    'Merek'             => $data->productBrand,
                    'Quantity'          => $data->qtyKoli,
                    'Satuan'            => $data->measure,
                    'Kapal/Departemen'  => $data->department,
                    'Catatan' 	    => $data->annotation,
                    'Status' 	    =>  getStatusSPB($data->status,'raw')
                ];
            });
        }

    }


    public function getSpb(Request $request)
    {
        if ($request->has('q')) {
            $data = Spb::search($request->q)
                ->where('status',2)
                ->get();
            $result = array();
            foreach ($data as $val) {
                $result[] = array('id' => $val->id, 'doc_no' =>$val->doc_no);
            }
            return response()->json($result);
        }
    }


    public function getSpbBpb(Request $request)
    {
        if ($request->has('q')) {
            $data = Spb::search($request->q)
                ->where('status',1)
                ->get();
            $result = array();
            foreach ($data as $val) {
                $result[] = array('id' => $val->id, 'doc_no' =>$val->doc_no);
            }
            return response()->json($result);
        }
    }


    public function getJs($id,$qty,$i)
    {
        $id  = $id;
        $i  = $i;
        $qty = $qty;
        return view('logistic.spb.js', compact('id','qty','i'));
    }


    public function popup($id)
    {
        $id = Hashids::decode($id);
        $spb = Spb::getByID($id['0']);
        $ids = explode(',',$spb->lpb_id);
        $lpb = getLPBbySPBID ($ids);
        return view('logistic.spb.popup', compact('lpb','spb'))->renderSections()['content'];
    }

    public function getDokumenSpbById($id){
        $id_ = Hashids::decode($id);
        $result = DB::table('spb')
        ->select('*')
        ->where('id','=',$id_)
        ->first();
        return response()->json($result);
    }
    public function uploadDokumenSpb(Request $request)
    {
        if (! Gate::allows('spb')) {
            return abort(401);
        }

        $id = $request->get('spb_id');
        $spb = Spb::findOrFail($id);
        $data = $request->all();
        if ($request->hasFile('attachment_file')) {
            $file = $request->file('attachment_file');
            $name = 'SPB-'.time();
            $folder = '/uploads/spb/attachment_spb/'.date('Y').'/';
            $filePath = $folder . $name. '.' . $file->getClientOriginalExtension();
            $this->uploadOne($file, $folder, 'public', $name);
            $data['attachment_file'] = $filePath;

            $dataHistory['spb_id'] = $spb->id;
            $dataHistory['user_id'] = Auth::user()->id;
            $dataHistory['jenis'] = 'update_dokumen';
            $history = SpbHistory::create($dataHistory);
        }
        $spb->update($data);

        return redirect()->route('logistic.spb.index')->with('success', 'Update Attachment Dokumen <a href="' . route('logistic.spb.show', Hashids::encode($spb->id)) . '" title="' . trans('app.show_title') . '" data-toggle="tooltip">'.$spb->doc_no.'</a> Berhasil');
    }

    public function set_done(Request $request, $id){

        $id = Hashids::decode($id);
        if (empty($id)) {
            return redirect()->back()->with('error', 'ID tidak valid');
        }
        $spb = Spb::findOrFail($id[0]);

        if (!$spb) {
            return redirect()->back()->with('error', 'SPB Tidak Ditemukan');
        }
        $notes_non_bpb_data = [
            'receipt_by' => $request->get('receipt_by'),
            'receipt_date' => $request->get('receipt_date'),
            'receipt_notes' => $request->get('receipt_notes'),
            'created_receipt_by' => Auth::user()->id
        ];

        $spb->update([
            'status' => 3,
            'notes_receipt_non_bpb' => $notes_non_bpb_data
        ]);

        $dataHistory =  array(
            'spb_id' => $spb->id,
            'user_id' => Auth::user()->id,
            'jenis' => 'set_done'
        );
        SpbHistory::create($dataHistory);

        return redirect()->back()->with('success', 'Update Data ' . $spb->doc_no . ' Berhasil');
    }

    public function getProductItemById($id){
        $id = Hashids::decode($id);
        $query = DB::table('spb_kolis')
            ->select(
                DB::raw('cast(spb_kolis.no AS INT) as no_koli'),
                'spb_kolis.id AS idKoli',
                'spb_kolis.qty AS qtyKoli',
                'spb_kolis.uuid AS uuid',
                'spb_kolis.annotation',
                'spb_kolis.qty AS qty_kolis',
                'spb_kolis.status_insurance AS status_insurance',
                'suppliers.name AS supplier',
                'po.doc_no AS noPO',
                'po.ppn AS ppn',
                'po.currency AS currency',
                'po.discount_type AS discount_type',
                'po.discount_amount AS discount_amount',
                'purchase_requisitions.dpm_no AS noDPM',
                'lpb.doc_no AS noLPB',
                'lpb_items.qty AS qty',
                'po_items.price as price',
                'po_items.discount AS price_discount',
                'po_items.price_discount AS price_after_discount',
                'master_item_products.name AS product',
                'master_item_products.id AS productID',
                'departments.name AS department',
                'po_items.specification',
                'suppliers.id AS supplierID',
                'master_item_products.code AS productCode',
                'master_item_products.part_number AS productPartNumber',
                'master_item_brands.name AS productBrand',
                'po_items.measure',
                'spb.type',
                'currencies.name as currencies_name',
                'currencies.conversion_idr as conversion_idr'
            )
            ->leftJoin('lpb_items', 'lpb_items.id', '=', 'spb_kolis.spb_item_id')
            ->leftJoin('po_items', 'po_items.id', '=', 'lpb_items.po_item_id')
            ->leftJoin('purchase_items', 'purchase_items.id', '=', 'po_items.pr_item_id')
            ->leftJoin('lpb', 'lpb_items.lpb_id', '=', 'lpb.id')
            ->leftJoin('po', 'po_items.po_id', '=', 'po.id')
            ->leftJoin('currencies', 'currencies.name', '=', 'po.currency')
            ->leftJoin('purchase_requisitions', 'purchase_requisitions.id', '=', 'po.purchase_id')
            ->leftJoin('master_item_products', 'master_item_products.id', '=', 'lpb_items.product_id')
            ->leftJoin('master_item_brands', 'master_item_brands.id', '=', 'master_item_products.brand_id')
            ->leftJoin('suppliers', 'suppliers.id', '=', 'po.supplier_id')
            ->leftJoin('departments', 'departments.id', '=', 'purchase_requisitions.department_id')
            ->leftJoin('spb','spb.id','=','spb_kolis.spb_id')
            ->where('spb_kolis.spb_id', $id)
            ->orderBy('no_koli', 'ASC')
            ->get();
        return $query;
    }


}
