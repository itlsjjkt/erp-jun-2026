<?php

namespace App\Http\Controllers\Approval;

use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use App\Models\PurchaseRequestNotes;
use App\Models\PurchaseRequestHistory;
use App\Models\PurchaseRequisition;
use App\Models\MasterItemProduct;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use Vinkla\Hashids\Facades\Hashids;
use App\Mail\SendMailable;

use Auth;

class PurchaseController extends Controller
{

    /**
     * Display a listing of User.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (! Gate::allows('approval_dpm')) {
            return abort(401);
        }
        $user_id = Auth::user()->id;
        return view('approval.purchase.index');
    }

    public function datatables()
    {
        if (! Gate::allows('approval_dpm')) {
            return abort(401);
        }

        if(Auth::user()->id == 1){
            $result= PurchaseRequest::
            selectRaw('purchases.*,departments.name AS department,users.name AS created, locations.name as location')
            ->leftJoin('locations','locations.id','=','purchases.location_id')
            ->leftJoin('departments', 'departments.id', '=', 'purchases.department_id')
            ->leftJoin('users', 'users.id', '=', 'purchases.created_by')
            ->where('purchases.status', 1)
            ->whereHas('PurchaseRequestItem', function($q){
                $q->where('pr_status', 0)
                ->where('status', 1)
               ;
            });
        }else{
            $result= PurchaseRequest::
            selectRaw('purchases.*,departments.name AS department,users.name AS created, locations.name as location')
            ->leftJoin('locations','locations.id','=','purchases.location_id')
            ->leftJoin('departments', 'departments.id', '=', 'purchases.department_id')
            ->leftJoin('users', 'users.id', '=', 'purchases.created_by')
            ->where('purchases.status', 1)
            ->whereHas('PurchaseRequestItem', function($q){
                $q->where('position', Auth::user()->id)
                ->where('pr_status', 0)
                ->where('status', 1);
            });
        }

       return  DataTables::of($result)
        ->addColumn('action', function ($result) {
            $url_approval = "<a href='".route('approval.purchase.set', ['id' => Hashids::encode($result->id)])."' title='".trans('app.show_title')."' data-toggle='tooltip' class='btn btn-outline'><span class='ti-thumb-up icon-lg'></span> </a>";
            return '<div class="btn-group">'.$url_approval.'</div>';
        })
        ->editColumn('type', function ($result) {
            return ($result->type) ? strtoupper($result->type) : '-';
        })
        ->editColumn('updated_at', function ($result) {
            return $result->updated_at ? with(new Carbon($result->updated_at))->format('d/m/Y H:i:s') : '';
        })
        ->rawColumns(['doc_no','action'])
        ->make(true);

    }

    public function set($id)
    {
        if (! Gate::allows('approval_dpm')) {
            return abort(401);
        }

        $id = Hashids::decode($id);
        $pr = PurchaseRequest::findOrFail($id['0']);

        if ($pr) {

            $notification = Notification::where(['user_id' => Auth::user()->id, 'data_id' => $pr->id, 'status' => 0])->first();
            if($notification){
                $data['status'] = 1;
                $notification->update($data);
            }

            $user = '';
            if (!isAdministrator()) {
                $user = Auth::user()->id;
            }

            $pr_items = DB::table('purchase_items')
            ->select('purchase_items.*', 'master_item_brands.name AS productBrand', 'master_items.name AS item', 'master_item_products.name as product', 'master_item_products.name AS product', 'master_item_products.code AS productCode', 'master_item_products.part_number AS productPartNumber', 'measures.name AS measure','master_item_products.id as produkId')
            ->leftJoin('master_item_products', 'master_item_products.id', '=', 'purchase_items.product_id')
            ->leftJoin('master_item_brands', 'master_item_brands.id', '=', 'master_item_products.brand_id')
            ->leftJoin('master_items', 'master_items.id', '=', 'master_item_products.item_id')
            ->leftJoin('measures', 'master_item_products.measure_id', '=', 'measures.id')
            ->when(!empty($user), function ( $pr_items) use ($user) {
                return $pr_items->where('purchase_items.position', $user);
            })
            ->where('purchase_items.purchase_id', $id['0'])
            ->where('purchase_items.status', 1)
            ->where('purchase_items.pr_status', 0)
            ->get();

            return view('approval.purchase.set', compact('pr', 'pr_items'));
        }else{
            return redirect()->route('approval.purchase.index')->with(['error' => 'Data tidak ditemukan!']);
        }
    }


    public function update(Request $request, $id)
    {
        if (! Gate::allows('approval_dpm')) {
            return abort(401);
        }

        $status  = $request->get('status');
        $product = $request->get('pr_item');
        $pr_item_step = PurchaseRequestItem::whereIn('id',$product)->distinct()->get('step')->toArray();

        $pr_step = $pr_item_step['0']['step'];

        $dpm = PurchaseRequest::findOrFail($id);

        $data = $request->all();
        $message = [];

        if($pr_step+1 > count(getAllApprovalLogistic($dpm->location_id)) && $status == 1){

            $increment = DB::table('purchase_requisitions')
            ->whereYear("created_at", date('Y'))
            ->where('location_id',$dpm->location_id)
            ->count();

            $doc_no = explode('-',$dpm->doc_no);
            $num = sprintf("%'.05d", $increment + 1) ;
            $no_pr = "PR-".$doc_no['1']."-".$doc_no['2']."-".date('my')."-".$num;

            $pr = PurchaseRequisition::create([
                'doc_no' => $no_pr,
                'dpm_no' => $dpm->doc_no,
                'purchase_id' => $dpm->id,
                'location_id' => $dpm->location_id,
                'type' => $dpm->type,
                'department_id' => $dpm->department_id,
                'project_id' => $dpm->project_id,
                'status' => ($dpm->type == 'po') ? NULL : 1,
                'created_at' => Carbon::now()
            ]);

            $dataPRNotes = [];
            $product = $request->get('pr_item');
            
            $approvalFilePath = null;
            // cek jika file diupload
            if ($request->hasFile('approval_dpm_file')) 
            {
                $file = $request->file('approval_dpm_file');

                // validasi file
                $request->validate([
                    'approval_dpm_file' => 'required|mimes:pdf|max:5120' 
                ]);

                // buat nama file
                $randomNumber = mt_rand(1000000000, 9999999999); 
                $filename = 'Approval-DPM-' . $randomNumber . '.' . $file->getClientOriginalExtension();

                // buat path direktori
                $year = date('Y');
                $month = date('M');
                $folder = "uploads/approval_dpm/{$year}/{$month}";

                // simpan ke storage
                $path = $request->file('approval_dpm_file')->storeAs(
                    'uploads/approval_dpm/' . date('Y') . '/' . date('M'),
                    $filename,
                    'public'
                );

                // simpan path di DB
                $approvalFilePath = "/{$folder}/{$filename}";
            }


            for($i=0;$i < count($product);$i++) {

                $pr_item = PurchaseRequestItem::findOrFail($request->get('pr_item')[$i]);
                $notesMessage = '';
                if($pr_item->qty != $request->get('qty')[$i]){
                    $notesMessage = "Merubah QTY dari ".$pr_item->qty." ke ".$request->get('qty')[$i];
                }

                $dataPR['pr_status']    = 1;
                $dataPR['status']       = 4;
                $dataPR['pr_id']        = $pr->id;
                $dataPR['qty']  = $request->get('qty')[$i];
                $dataPR['last_approved_at'] =  date('Y-m-d H:i:s');
                $dataPR['last_approved']    = Auth::user()->id;
                $dataPR['assigned_id']  = ($dpm->type == 'po') ? NULL : config('app.purchaser_admin_id');

                $dataPRNotes[] = [
                    'user_id'       => Auth::user()->id,
                    'notes'         => $request->get('reason')[$i],
                    'message'       => $notesMessage,
                    'pr_item_id'    => $request->get('pr_item')[$i],
                    'created_at'    =>  date('Y-m-d H:i:s'),
                    'approval_dpm_file' => $approvalFilePath
                ];
                $pr_item->update($dataPR);

            }

            $purchase_notes = PurchaseRequestNotes::insert($dataPRNotes);
            $purchasing = getAdminPurchasing();

            $email = [];
            $dataNotification = [];
            foreach($purchasing as $val){
                if($val->notification_email == 1){
                    $email[] = $val->email;
                }
                $dataNotification[] = array(
                    'title'     => "Konfirmasi PR",
                    'link'      => "/purchasing/pr_show/".Hashids::encode($pr->id),
                    'data_id'   => $pr->id,
                    'content'   => "Terdapat pengajuan PR dengan nomor: ". $no_pr,
                    'user_id'   => $val->id
                );
            }
            $notifications = Notification::insert($dataNotification);

            $cekDPM = cekDPM($id);
            if($cekDPM == 0){
                $data_purchase['status'] = 4;
                $dpm->update($data_purchase);
            }

            $content = "Terdapat pengajuan PR dengan Nomor: ". $no_pr. " yang menunggu pembuatan PO anda. Mohon segara untuk menindaklanjuti dengan login kedalam aplikasi ERP Shipping.";
            $msgData = array(
                'title'     => 'Konfirmasi Purchase Requisition (PR)',
                'content'   => $content,
                'no_pr'     => $no_pr,
                'email'     => $email,
            );
            // if (config('app.mail_status')=='on') {
            //     Mail::send('emails.notification', $msgData, function ($message) use ($msgData) {
            //         $message->to($msgData['email'])->subject('Pengajuan PR dengan no: '. $msgData['no_pr']);
            //     });
            // }


        }
        else{

            if($status == 1){
                $approval = getApprovalLogistic($dpm->location_id, $pr_step+1);
                $position = $approval->user_id;

                // API WA APPROVAL
                $user_db = DB::table('users')->where('id',$position)->first();
                $pr_items = DB::table('purchase_items')
                    ->select('purchase_items.*',
                        'master_item_brands.name AS productBrand',
                        'master_items.name AS item',
                        'master_item_products.name as product',
                        'master_item_products.name AS product',
                        'master_item_products.code AS productCode',
                        'master_item_products.part_number AS productPartNumber',
                        'measures.name AS measure'
                    )
                    ->leftJoin('master_item_products', 'master_item_products.id', '=', 'purchase_items.product_id')
                    ->leftJoin('master_item_brands', 'master_item_brands.id', '=', 'master_item_products.brand_id')
                    ->leftJoin('master_items', 'master_items.id', '=', 'master_item_products.item_id')
                    ->leftJoin('measures', 'master_item_products.measure_id', '=', 'measures.id')
                    ->whereIn('purchase_items.id',$product)
                    ->get();

                $body = "```Dear Pak ".getUserByID($user_db->id).",```\n\n```Mohon Lakukan Approval DPM Berikut:```\n";
                $body .= "```".$dpm->doc_no."```\n";
                $body .= "```Tipe : ".strtoupper($dpm->type)."```\n\n";
                $body .= "```Daftar Item :```\n";
                foreach($pr_items as $a){
                    $body .= "- ```".$a->product;
                    $body .= " [".$a->qty;
                    $body .= " ".$a->measure."]```\n";
                }
                $body .= "\n> ```Link Approval Via Web:``` _https://erp.haritashipping.com/approval/purchase_";
                $bodyS = $body;
                if($user_db->is_whatsapp === true && $user_db->telp != null && $dpm->type != 'petty_cash'){
                    sendWhatsapp($user_db->telp, $bodyS);
                }

            }else{
                $position = Auth::user()->id;

                // NOTIF WHATSAPP REJECT
                $user_db_ = DB::table('users')->where('id',$dpm->creator->id)->first();
                if($user_db_){
                    $body = "```Dear User ".getUserByID($user_db_->id).",```\n\n```Mohon Lakukan Pengecekan Reject Item DPM Berikut:```\n";
                    $body .= "```".$dpm->doc_no."```\n";
                    $body .= "```Tipe : ".strtoupper($dpm->type)."```\n";
                    $bodyS = $body;
                    if($user_db_->is_whatsapp === true && $user_db_->telp != null){
                        sendWhatsapp($user_db_->telp, $bodyS);
                    }
                }
            }

            $product = $request->get('pr_item');
            $dataPRNotes = [];

            for($i=0;$i < count($product);$i++) {

                $dataPR['status']           = $request->get('status');
                $dataPR['qty']              = $request->get('qty')[$i];
                $dataPR['last_approved_at'] =  date('Y-m-d H:i:s');
                $dataPR['last_approved']    = Auth::user()->id;
                $dataPR['position']         = $position;
                $dataPR['step']             = $pr_step+1;

                if($status == 2) $dataPR['reason'] =  $request->get('reason')[$i];

                $pr_item = PurchaseRequestItem::findOrFail($request->get('pr_item')[$i]);

                $notesMessage = '';
                if($pr_item->qty != $request->get('qty')[$i]){
                    $notesMessage = "Merubah QTY dari ".$pr_item->qty." ke ".$request->get('qty')[$i];
                }

                $dataPRNotes[] = [
                    'user_id'       => Auth::user()->id,
                    'notes'         => $request->get('reason')[$i],
                    'message'       => $notesMessage,
                    'pr_item_id'    => $request->get('pr_item')[$i],
                    'created_at'    =>  date('Y-m-d H:i:s')
                ];
                $pr_item->update($dataPR);

            }

            $purchase_notes = PurchaseRequestNotes::insert($dataPRNotes);

            if($status == 1){
                $content = "Terdapat pengajuan DPM dengan Nomor: ". $dpm->doc_no. " yang menunggu approval anda. Mohon segara untuk melakukan approval dengan login kedalam aplikasi ERP Shipping.";
                $msgData = array(
                    'title'         => 'Konfirmasi Approval DPM',
                    'content'       => $content,
                    'name'          => $approval->name,
                    'email'         => $approval->email,
                    'no_dpm'        => $dpm->doc_no
                );
                $dataNotification['title']      = "Approval DPM";
                $dataNotification['link']       = "/approval/purchase_set/".Hashids::encode($dpm->id);
                $dataNotification['data_id']    = $dpm->id;
                $dataNotification['content']    =  "Terdapat pengajuan DPM dengan nomor: ". $dpm->doc_no;
                $dataNotification['user_id']    = $approval->user_id;
                $notifications = Notification::create($dataNotification);
                // if (config('app.mail_status')=='on' && $approval->notification_email == 1 ) {
                //     Mail::send('emails.notification', $msgData, function ($message) use ($msgData) {
                //         $message->to($msgData['email'], $msgData['name'])->subject('Pengajuan DPM dengan no: '.  $msgData['no_dpm']);
                //     });
                // }
            }else{
                $dataNotification['title']      = "Reject DPM";
                $dataNotification['link']       = "/logistic/monitoring_detail/".Hashids::encode($dpm->id);
                $dataNotification['content']    = "Reject pengajuan DPM dengan nomor: ". $dpm->doc_no;
                $dataNotification['user_id']    = $dpm->created_by;
                $dataNotification['data_id']    = $dpm->id;
                $notifications = Notification::create($dataNotification);
            }
        }


        return redirect()->route('approval.purchase.index')->with(['success' => 'Approval Data Berhasil!']);

    }


    public function hold(Request $request)
    {
        if (! Gate::allows('approval_dpm')) {
            return abort(401);
        }

        $dpm = PurchaseRequest::findOrFail($request->get('dpm_id'));
        $approval = getApprovalLogistic($dpm->location_id, 1);

        DB::beginTransaction();

        try {

            if($dpm->creator){
                $content = "Terdapat Hold DPM dengan Nomor: ". $dpm->doc_no. " yang menunggu perbaikan anda. Mohon segara untuk melakukan perbaikan dengan login kedalam aplikasi ERP Shipping.";
                $msgData = array(
                    'title'         => 'Konfirmasi Hold DPM',
                    'content'       => $content,
                    'name'          => $dpm->creator->name,
                    'email'         => $dpm->creator->email,
                    'no_dpm'        => $dpm->doc_no
                );

                // if (config('app.mail_status')=='on' && $dpm->creator->notification_email == 1 ) {
                //     Mail::send('emails.notification', $msgData, function ($message) use ($msgData) {
                //         $message->to($msgData['email'], $msgData['name'])->subject('Hold DPM dengan no: '.  $msgData['no_dpm']);
                //     });
                // }

                // NOTIF WA HOLD
                $user_db_ = DB::table('users')->where('id',$dpm->creator->id)->first();
                if($user_db_){
                    $body = "```Dear User ".getUserByID($user_db_->id).",```\n\n```Mohon Lakukan Perbaikan Hold DPM Berikut:```\n";
                    $body .= "```".$dpm->doc_no."```\n";
                    $body .= "```Tipe : ".strtoupper($dpm->type)."```\n";
                    $body .= "```Alasan Hold: ".($request->get('message') ?? '-')."```\n\n";
                    $bodyS = $body;
                    if($user_db_->is_whatsapp === true && $user_db_->telp != null){
                        sendWhatsapp($user_db_->telp, $bodyS);
                    }
                }
            }

            $dataPRNotes = [];

            foreach($dpm->PurchaseRequestItem as $value) {
                $dataPRNotes[] = [
                    'user_id'       => Auth::user()->id,
                    'notes'         => $request->get('message'),
                    'message'       => 'Hold DPM',
                    'pr_item_id'    => $value->id,
                    'created_at'    =>  date('Y-m-d H:i:s')
                ];
            }

            $purchase_notes = PurchaseRequestNotes::insert($dataPRNotes);

            $data_dpm['status'] =  3;
            $dpm->update($data_dpm);

            $dataHistory['purchase_id'] = $dpm->id;
            $dataHistory['user_id'] = Auth::user()->id;
            $dataHistory['jenis'] = 'hold';
            $dataHistory['message'] = $request->get('message');

            $dataNotification['title']      = "Hold DPM";
            $dataNotification['link']       = "/purchase_request/".Hashids::encode($dpm->id);
            $dataNotification['content']    = "Hold DPM dengan nomor: ". $dpm->doc_no;
            $dataNotification['user_id']    = $dpm->created_by;
            $dataNotification['data_id']    = $dpm->id;
            
            $pr_item = PurchaseRequestItem::where('purchase_id',$dpm->id)
            ->update([
                'step' => 1,
                'position' => $approval->user_id
            ]);

            PurchaseRequestHistory::create($dataHistory);
            Notification::create($dataNotification);

            DB::commit();
            return redirect()->route('approval.purchase.index')->with(['success' => 'Hold Data Berhasil!']);

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->route('approval.purchase.index')->withErrors(['error' => 'Hold tidak berhasil!']);
        }

    }

}
