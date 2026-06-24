<?php

namespace App\Http\Controllers\Approval;

use App\Models\PurchaseRequest;
use App\Models\PurchaseRequisition;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseOrderHistory;
use App\Models\Notification;
use App\User;
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

class PoController extends Controller
{

    /**
     * Display a listing of User.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (! Gate::allows('approval_po')) {
            return abort(401);
        }
        $user_id = Auth::user()->id;

        return view('approval.po.index');
    }

    public function datatables()
    {
        if (! Gate::allows('approval_po')) {
            return abort(401);
        }

        $result = DB::table('po')
        ->select('po.*','users.name AS created','suppliers.name AS supplier')
        ->leftJoin('purchase_requisitions', 'purchase_requisitions.id', '=', 'po.purchase_id')
        ->leftJoin('suppliers', 'suppliers.id', '=', 'po.supplier_id')
        ->leftJoin('users', 'users.id', '=', 'po.created_by')
        ->where('po.position', Auth::user()->id)
        ->where('po.status', 1)
        ->orderBy('po.created_at', 'DESC');

       return  DataTables::of($result)
        ->addColumn('action', function ($result) {
            $url_approval = "<a href='".route('approval.po.set', ['id' => Hashids::encode($result->id)])."' title='".trans('app.show_title')."' data-toggle='tooltip' class='btn btn-outline'><span class='ti-thumb-up icon-lg'></span> </a>";
            return '<div class="btn-group">'.$url_approval.'</div>';

        })->editColumn('doc_no', function ($result) {
            $url_view = "<a href='".route('purchasing.po.show', Hashids::encode($result->id))."' title='".trans('app.show_title')."' data-toggle='tooltip' target='_blank'>".$result->doc_no."</a>";
            return $url_view;
        })
        ->editColumn('payment_amount', function ($result) {
            return "<span class='currency' data-content='".getCurrencySymbol($result->currency)."'>".number_format($result->payment_amount,2,".",',')."</span>";
        })
        ->editColumn('updated_at', function ($result) {
            return $result->updated_at ? with(new Carbon($result->updated_at))->format('m/d/Y') : '';
        })
        ->editColumn('created_at', function ($result) {
            return $result->created_at ? with(new Carbon($result->created_at))->format('m/d/Y') : '';
        })
        ->rawColumns(['doc_no','action', 'status','payment_amount'])
        ->make(true);

    }


    public function set($id)
    {
        if (! Gate::allows('approval_po')) {
            return abort(401);
        }

        $id = Hashids::decode($id);

        $po         = PurchaseOrder::getByID($id['0']);
        $po_items   = PurchaseOrder::getProductItem($id['0']);
        $po_history = PurchaseOrder::getHistory($id['0']);

        $notification = Notification::where(['user_id' => Auth::user()->id, 'data_id' => $po->id, 'status' => 0])->first();
        if($notification){
            $data['status'] = 1;
            $notification->update($data);
        }

        return view('approval.po.set', compact('po','po_items','po_history'));
    }

    public function update(Request $request, $id)
    {
        if (! Gate::allows('approval_po')) {
            return abort(401);
        }

        $po = PurchaseOrder::findOrFail($id);
        $data = $request->all();

        if ($po->step+1 > count(getAllApprovalPurchasing($po->company_id)) && $request->get('status')==1) {
            $data_po['approved_by']    = Auth::user()->id;
            $data_po['approved']       = Carbon::today()->toDateString();
            $data_po['status']         = 2;
            $dataHistory['jenis']      = 'approval';
            if($po->estimated_delivery_day != 0){
                $data_po['estimated_receipt'] = Carbon::now()->addDays($po->estimated_delivery_day)->toDateString('yyyy-mm-dd');
            }
        }else{
                if($request->get('status') == 1){
                    $approval = getApprovalPurchasing($po->company_id, $po->step+1);
                    $data_po['step']     = $po->step+1;
                    $data_po['position'] = $approval->user_id;
                    $dataHistory['jenis']= 'approval';

                    $dataNotification['title']      = "Approval PO";
                    $dataNotification['link']       = "/approval/po_set/".Hashids::encode($po->id);
                    $dataNotification['data_id']    = $po->id;
                    $dataNotification['content']    =  "Terdapat pengajuan PO dengan nomor: ". $po->doc_no;
                    $dataNotification['data_id']    = $po->id;
                    $dataNotification['user_id']    =  $approval->user_id;
                }else{
                    $users = User::findOrFail($po->created_by);

                    $data_po['status']  = 3;
                    $data_po['approved_by']    = NULL;
                    $data_po['approved']       = Carbon::today()->toDateString();

                    $message =  $request->get('message');
                    $content = "Perbaikan PO dengan Nomor: ". $po->doc_no. ". <br> Berikut isi perbaikan:
                    ".$message."
                    . Mohon segara untuk melakukan perbaikan dengan login kedalam aplikasi ERP Shipping.";
                    $msgData = array(
                        'title'         => 'Perbaikan PO',
                        'content'       => $content,
                        'name'          => $users->name,
                        'email'         => $users->email,
                        'no_po'         => $po->doc_no
                    );
                    $dataNotification['title']      = "Perbaikan PO";
                    $dataNotification['link']       = "/purchasing/po/".Hashids::encode($po->id);
                    $dataNotification['data_id']    = $po->id;
                    $dataNotification['content']    =  "Terdapat perbaikan PO dengan nomor: ". $po->doc_no;
                    $dataNotification['user_id']    = $po->created_by;
                    if (config('app.mail_status')=='on' && $users->notification_email == 1) {
                        Mail::send('emails.notification', $msgData, function ($message) use ($msgData) {
                            $message->to($msgData['email'], $msgData['name'])->subject('Pengajuan PO dengan no: '.  $msgData['no_po']);
                        });
                    }
                    $dataHistory['jenis'] = 'revisi';
                    $position = Auth::user()->id;
                }

                $notifications = Notification::create($dataNotification);

        }

        $data_po['updated_by']     = Auth::user()->id;

        $po->update($data_po);

        $dataHistory['po_id']           = $id;
        $dataHistory['user_id']         = Auth::user()->id;
        $dataHistory['date_approved']   = date('Y-m-d H:i:s');
        $dataHistory['message']         = $request->get('message');

        $po = PurchaseOrderHistory::create($dataHistory);

        if ($request->get('status')==1) {
            return redirect()->route('approval.po.index')->with(['success' => 'Approval Data Berhasil!']);
        } else {
            return redirect()->route('approval.po.index')->with(['success' => 'Perbaikan Data Berhasil!']);
        }

    }

    public function updateMultiple(Request $request)
    {
        if (! Gate::allows('approval_po')) {
            return abort(401);
        }

        $po_id      = $request->get('id');

        for($i=0;$i < count($po_id);$i++) {

            $po = PurchaseOrder::findOrFail($request->get('id')[$i]);

            if ($po->step+1 > count(getAllApprovalPurchasing($po->company_id))) {
                $data_po['approved_by']    = Auth::user()->id;
                $data_po['approved']       = Carbon::today()->toDateString();
                $data_po['status']         = 2;
                if($po->estimated_delivery_day != 0){
                    $data_po['estimated_receipt'] = Carbon::now()->addDays($po->estimated_delivery_day)->toDateString('yyyy-mm-dd');
                }
            }else{
                $approval = getApprovalPurchasing($po->company_id, $po->step+1);
                $data_po['step']     = $po->step+1;
                $data_po['position'] = $approval->user_id;
            }

            $po->update($data_po);

            $dataHistory[] = [
                'po_id'         => $request->get('id')[$i],
                'user_id'       => Auth::user()->id,
                'date_approved' => date('Y-m-d H:i:s'),
                'created_at'    => date('Y-m-d H:i:s'),
                'jenis'         => 'approval',
            ];
        }

        PurchaseOrderHistory::insert($dataHistory);

    }
}
