<?php

namespace App\Http\Controllers\Purchasing;

use App\Models\PurchaseRequest;
use App\Models\PurchaseRequisition;
use App\Models\PurchaseRequestItem;
use App\Models\PurchaseRequestHistory;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseOrderHistory;
use App\Models\Workarea;
use App\Models\Project;
use App\User;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use App\Traits\UploadTrait;
use Vinkla\Hashids\Facades\Hashids;
use App\Mail\SendMailable;
use App\Models\Notification;
use App\Exports\PoExport;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Maatwebsite\Excel\Facades\Excel;

use Rap2hpoutre\FastExcel\FastExcel;
use Auth;
use PDF;


class PoController extends Controller
{
    use UploadTrait;

    function __construct()
    {
        $this->middleware('permission:purchase_order', ['only' => ['index','create','destroy','edit','show','datatables','close','revision']]);
    }
    /**
     * Display a listing of User.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $supplier = DB::table('suppliers')->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
        $purchaser = User::where('type',4)->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Pilih Purchaser…', '');
        $project = Project::where('status', 1)->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');

        if(Auth::user()->data_access==1) $statistic = PurchaseOrder::getStats();
        else $statistic = PurchaseOrder::getStats(Auth::user()->id);

        return view('purchase.po.index',compact('supplier','project','purchaser','statistic'));
    }

    public function datatables(Request $request)
    {
        $data = $request->all();

        if(isAdministrator()  || Auth::user()->data_access == 1) $result = PurchaseOrder::getData($data);
        else $result  = PurchaseOrder::getData($data, Auth::user()->id);

        return  DataTables::of($result)
        ->addColumn('action', function ($result) {
            $url = 'printExternal("/purchasing/po_print/'.Hashids::encode($result->id).'/print")';

            $url_revision   = "<form class='delete' action='".route('purchasing.po.revision', ['id' => $result->id])."' method='POST'>
                                <input name='id' type='hidden' value='".$result->id."'>
                                ".csrf_field()."
                                <button class='btn btn-outline text-primary btn-revision' title='Perbaikan' data-toggle='tooltip'><i class='ti-pencil icon-lg'></i></button>
                            </form>";

            $url_cancel   = "<form class='delete' action='".route('purchasing.po.cancel', ['id' => $result->id])."' method='POST'>
                            <input name='id' type='hidden' value='".$result->id."'>
                            <input name='isPR' type='hidden' value='0'>
                            ".csrf_field()."
                            <button class='btn btn-outline text-danger btn-cancel' title='Cancel PO' data-toggle='tooltip'><i class='ti-power-off icon-lg'></i></button>
                        </form>";

            $url_edit       = "<a href='".route('purchasing.po.edit', Hashids::encode($result->id))."' title='".trans('Edit')."' data-toggle='tooltip' class='btn btn-outline'><span class='ti-pencil icon-lg'></span> </a>";
            $url_view       = "<a href='".route('purchasing.po.show', Hashids::encode($result->id))."' title='".trans('app.show_title')."' data-toggle='tooltip' class='btn btn-outline'><span class='ti-eye icon-lg'></span> </a>";
            $url_print      = "<a class='btn btn-outline' href='#' title='Print Data' onclick='".$url."' data-toggle='tooltip' ><i class='ti-printer icon-lg'></i></a>";
            $url_delete     = "<form class='delete' action='".route('purchasing.po.delete', ['id' => $result->id])."' method='POST'>
                                ".csrf_field()."
                                <button class='btn btn-outline text-danger' title='".trans('app.delete_title')."' data-toggle='tooltip'><i class='ti-trash icon-lg'></i></button>
                            </form>";
            /* 0 = draft */
            if ($result->status==0) {
                if(auth()->user()->can('purchase_admin') ) {
                    return '<div class="btn-group">'.$url_edit.$url_view.$url_delete.'</div>';
                } else {
                    return '<div class="btn-group">'.$url_view.'</div>';
                }
            }
            /* 1 = on progress */
            elseif ($result->status==1) {
                if(auth()->user()->can('purchase_admin') ) {
                    if(isAdministrator() || (isPurchasing() && Auth::user()->data_access == 1)) {
                        return '<div class="btn-group">' . $url_view . $url_revision . $url_cancel . '</div>';
                    } else if (isPurchasing() && Auth::user()->data_access==2  && $result->created_by == Auth::user()->id) {
                        return '<div class="btn-group">' . $url_view . $url_revision . $url_cancel . '</div>';
                    } else {
                        return '<div class="btn-group">'.$url_view.'</div>';
                    }
                }else{
                    return '<div class="btn-group">'.$url_view.'</div>';
                }
            }
            /* 2 = PO Issued */
            elseif ($result->status==2) {
                if(auth()->user()->can('purchase_admin') ) {
                    if(isAdministrator() || (isPurchasing() && Auth::user()->data_access == 1)) {
                        return '<div class="btn-group">' . $url_view . $url_print . $url_revision . $url_cancel . '</div>';
                    } else if (isPurchasing() && Auth::user()->data_access==2  && $result->created_by == Auth::user()->id) {
                        return '<div class="btn-group">' . $url_view . $url_print . $url_revision . $url_cancel . '</div>';
                    } else {
                        return '<div class="btn-group">'.$url_view.$url_print.'</div>';
                    }
                } else {
                    return '<div class="btn-group">'.$url_view.'</div>';
                }
            }
            /* 3 = perbaikan */
            elseif ($result->status==3) {
                if(auth()->user()->can('purchase_admin') ) {
                    if(isAdministrator() || (isPurchasing() && Auth::user()->data_access==1)) {
                        return '<div class="btn-group">'.$url_edit.$url_view.'</div>';
                    } else if (isPurchasing() && Auth::user()->data_access==2 && $result->created_by==Auth::user()->id) {
                        return '<div class="btn-group">'.$url_edit.$url_view.'</div>';
                    } else {
                        return '<div class="btn-group">'.$url_view.'</div>';
                    }
                } else {
                    return '<div class="btn-group">'.$url_view.'</div>';
                }
            }
            /* 4 = lpb parsial, 5 = done */
            elseif ($result->status==4 || $result->status==5 ) {
                if(auth()->user()->can('purchase_admin') ) {
                    return '<div class="btn-group">'.$url_view.$url_print.'</div>';
                }else{
                    return '<div class="btn-group">'.$url_view.'</div>';
                }
            }
            else {
                return '<div class="btn-group">'.$url_view.'</div>';
            }
        })
		->editColumn('doc_no', function ($result) {
			$doc_no = "<a target='_blank' href='".route('purchasing.po.show',Hashids::encode($result->id))."' title='Detail PO' data-toggle='tooltip' ";
				if ($result->last_print == null && $result->approved != null) {
					$doc_no .= "style='font-weight:bold;'";
				}
				$doc_no .= ">".$result->doc_no."</a>";
            return $doc_no;
		//	$doc_no = "<a target='_blank' href='".route('purchasing.po.show',Hashids::encode($result->id))."' title='Detail PO' data-toggle='tooltip' >".$result->doc_no."</a>";
		//	return $doc_no;
        })
		->editColumn('no_pr', function ($result) {
			$no_pr = "<a target='_blank' href='".route('purchasing.pr.show', ['id' =>Hashids::encode($result->purchase_id)])."' title='Detail PR' data-toggle='tooltip' >".$result->no_pr."</a>";
			return $no_pr;
        })
        ->editColumn('payment_amount', function ($result) {
            $amount = format_number($result->payment_amount);
            return "<span class='currency' data-content='".getCurrencySymbol($result->currency)."'>".$amount."</span>";
        })
        ->editColumn('status', function ($result) {
            return getStatusPO($result->status);
        })
        ->editColumn('created_at', function ($result) {
            return $result->created_at ? with(new Carbon($result->created_at))->format('d/m/Y H:i:s') : '';
        })
        ->rawColumns(['action', 'status','payment_amount','check','doc_no','no_pr'])
        ->make(true);

    }

    /**
     * Show the form for creating new User.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $id       = Hashids::decode($request->get('id'));
        $pr       = PurchaseRequisition::getByID($id['0']);
        $workarea = Workarea::findOrFail($pr->location_id);
        $approval = getApprovalPurchasing($workarea->company_id,1);

        if ($approval) {
            if(Auth::user()->data_access==1) $pr_items = PurchaseRequisition::getProductItem($pr->id);
            else $pr_items = PurchaseRequisition::getProductItem($pr->id,Auth::user()->id);
            $discount_type = array(
                '1' => 'Percent',
                '0' => 'Currency'
            );
    
            $currency       = DB::table('currencies')->orderBy('name','ASC')->get()->pluck('name', 'name')->prepend('Silahkan pilih...', '');
            $payment_term   = DB::table('payment_terms')->orderBy('name','ASC')->where('status',1)->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
            $po_term        = DB::table('po_terms')->orderBy('name','ASC')->where('status',1)->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
            $po_note        = DB::table('po_notes')->orderBy('name','ASC')->where('status',1)->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
            $payment_method = DB::table('payment_methods')->orderBy('name','ASC')->where('status',1)->get()->pluck('name', 'name')->prepend('Tipe Pembayaran', '');
            $price_term     = DB::table('price_terms')->orderBy('name','ASC')->where('status',1)->get()->pluck('name', 'name')->prepend('Silahkan pilih...', '');
            $price_term_location  = DB::table('cities')->orderBy('name','ASC')->get()->pluck('name', 'name')->prepend('Silahkan pilih...', '');
    
            return view('purchase.po.create', compact('payment_method','payment_term','price_term','po_term','pr','pr_items','currency','price_term_location','discount_type','po_note'));
        }else{
            return redirect()->back()
            ->withInput($request->input())
            ->withErrors(['Approval Rule tidak ditemukan']);
        }
      
    }

    /**
     * Store a newly created User in storage.
     *
     * @param  \App\Http\Requests\StoreUsersRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {   
       

        DB::beginTransaction();

        try {

            $data = $request->all();
            $approval = getApprovalPurchasing($request->get('company_id'),1);

            if ($request->get('status')==1) {
                $increment = DB::table('po')
                ->whereYear("publish", date('Y'))
                ->where('status','!=', 0)
                ->where('company_id',$request->get('company_id'))
                ->count();
    
                $num = sprintf("%'.05d", $increment + 1) ;
    
                $no_po = "PO-".$request->get('company_code')."-JKT-".date('my')."-".$num;
    
                $data['step']       = 1;
                $data['position']  = $approval->user_id;
                $data['status']    = 1;
                $data['publish']   = date('Y-m-d');
                $dataHistory['jenis']   = 'insert';
    
            } else {
                $no_po = "PO-".$request->get('company_code')."-JKT-".date('my')."-DRAFT";
                $data['status']  = 0;
                $dataHistory['jenis']  = 'draft';
            }
    
            $discount_amount = floatval(str_replace(",", "", $request->get('discount_amount')));

            $data['doc_no'] = $no_po;
            $data['discount_amount']  = $discount_amount;
            $data['send_expense'] = str_replace(",", "", $request->get('send_expense'));
    
            if($request->get('price_term') == 'FRANCO' && $request->get('price_term_location') != 'JAKARTA') $data['type'] = 'non_lpb';
            else $data['type'] = 'lpb';
    
            $payment_amount = 0;
            $product  = $request->get('product_id');
    
            for ($i=0;$i < count($product);$i++) {
                if (in_array($request->get('pr_item_id')[$i], $request->get('iscreatePO'))) {
                    if ($request->get('is_item_ppn')==1) {
                        $price      = str_replace(",", "", $request->get('price')[$i]);
                        $itemprice  =  ($price * 100/110);
                    }else{
                        $itemprice  = str_replace(",", "", $request->get('price')[$i]);
                    }
                    $diskon     = $request->get('qty')[$i] *  $itemprice * ($request->get('diskon_item')[$i] / 100);
                    $payment_amount += $request->get('qty')[$i] * floatval($itemprice) - $diskon;
                }
            }
    
            if ($request->get('discount_type')==1) {
                $discount_amount = $payment_amount * ($discount_amount/100);
            }
    
            $netto = $payment_amount - $discount_amount;
    
            $ppn = 0;
            if ($request->get('ppn')==1 || $request->get('is_item_ppn')==1) {
                $data['ppn'] = 11;
                $ppn = (11/100) * $netto;
            }
    
            $pph = 0;
            if ($request->get('pph')) {
                $data['pph'] = $request->get('pph');
                $pph = ($request->get('pph')/100) * $netto;
            }
    
            $send_expense = 0;
            if ($request->get('send_expense')) {
                if ($request->get('send_expense_ppn') == 1) {
                    $send_expense_ppn = (11/100) * $send_expense ;
                    $send_expense = $send_expense_ppn + $send_expense ;
                }else{
                    $send_expense = str_replace(",", "", $request->get('send_expense'));
                }
            }
    
            $dp = DB::table('payment_terms')
                ->where("id", $request->get('payment_term_id'))
                ->first();
    
            $data['payment_amount']  = $netto + $ppn + $pph + $send_expense;
            $data['dp_percentage']   = $dp->dp_percentage;
            $data['down_payment']    = floatval(($data['payment_amount'] * $dp->dp_percentage)/100) ;
            $data['created_by']      = Auth::user()->id;
            $data['discount_item']   = ($request->get('discount_item')=='1') ? true : false;
    
            $discount_item = 0;
            if ($request->get('discount_item')=='1') {
                $discountTotal = floatval(str_replace(",", "", $request->get('discount_amount')));
                $discount_item = $discountTotal/count( $request->get('iscreatePO'));
            }

            $po = PurchaseOrder::create($data);
            $dataPO  = $dataPR  = $cases = $qty_parsial = $ids = [];


            for ($i=0;$i < count($product);$i++) {
                if (in_array($request->get('pr_item_id')[$i], $request->get('iscreatePO'))) {
                    if ($request->get('is_item_ppn')==1) {
                        $price      = str_replace(",", "", $request->get('price')[$i]);
                        $item_price  = ($price * 100/110);
                    }else{
                        $item_price  = str_replace(",", "", $request->get('price')[$i]);
                    }

                    if ($request->get('discount_type')==1) {
                        $price = str_replace(",", "", $request->get('price')[$i]);
                        $price_after_discount  = $price - ($price * ($discount_item/100));
                    }else{
                        $price_after_discount = str_replace(",", "", $request->get('price')[$i])-$discount_item;
                    }

                    $dataPO[] = [
                        'po_id'         => $po->id,
                        'pr_item_id'    => $request->get('pr_item_id')[$i],
                        'product_id'    => $request->get('product_id')[$i],
                        'qty'           => $request->get('qty')[$i],
                        'measure'       => $request->get('measure_id')[$i],
                        'specification' => $request->get('specification')[$i],
                        'discount'      => $request->get('diskon_item')[$i],
                        'price'         => $item_price,
                        'price_discount'  => $price_after_discount
                    ];

                    $ids[]    = $request->get('pr_item_id')[$i];

                    if ($request->get('qty')[$i] == $request->get('qty_pr')[$i] || $request->get('qty_pr')[$i] < $request->get('qty')[$i]) {
                        $cases[]        = "WHEN id = {$request->get('pr_item_id')[$i]} THEN 1";
                        $qty_parsial [] = "WHEN id = {$request->get('pr_item_id')[$i]} THEN 0";
                    } else {
                        $qtyParsial = ($request->get('qty_pr')[$i] - $request->get('qty')[$i]);
                        $cases[]        = "WHEN id = {$request->get('pr_item_id')[$i]} THEN 2";
                        $qty_parsial [] = "WHEN id = {$request->get('pr_item_id')[$i]} THEN ".$qtyParsial;
                    }
                }
            }

            $ids        = implode(',', $ids);
            $cases      = implode(' ', $cases);
            $qty_parsial= implode(' ', $qty_parsial);

            \DB::update("UPDATE purchase_items SET po_status = CASE {$cases} END, qty_parsial = CASE {$qty_parsial} END WHERE id in ({$ids})");

            PurchaseOrderItem::insert($dataPO);

            $dataHistory['po_id'] = $po->id;
            $dataHistory['user_id'] = Auth::user()->id;
            PurchaseOrderHistory::create($dataHistory);

            if ($request->get('status')==1) {

                $content = "Terdapat pengajuan PO dengan Nomor: ".  $no_po. " yang menunggu approval anda. Mohon segara untuk melakukan approval dengan login kedalam aplikasi ERP Shipping.";
                $msgData = array(
                    'title'         => 'Konfirmasi Approval PO',
                    'content'       => $content,
                    'name'          => $approval->name,
                    'email'         => $approval->email,
                    'no_po'         => $no_po
                );
                if (config('app.mail_status')=='on' && $approval->notification_email == '1') {
                    Mail::send('emails.notification', $msgData, function ($message) use ($msgData) {
                        $message->to($msgData['email'], $msgData['name'])->subject('Pengajuan PO dengan no: '.  $msgData['no_po']);
                    });
                }
                $dataNotification['title']      = "Approval PO";
                $dataNotification['link']       = "/approval/po_set/".Hashids::encode($po->id);
                $dataNotification['data_id']    = $po->id;
                $dataNotification['content']    =  "Terdapat pengajuan PO dengan nomor: ". $no_po;
                $dataNotification['user_id']    = $approval->user_id;
                $notifications = Notification::create($dataNotification);
            }

            DB::commit();

            if(checkParsialPR($request->get('purchase_id')) == true) PurchaseRequisition::findOrFail($request->get('purchase_id'))->update(array('status' => 2));
            else PurchaseRequisition::findOrFail($request->get('purchase_id'))->update(array('status' => 4));

            return redirect()->route('purchasing.po.show', Hashids::encode($po->id))->with(['success' => 'Pembuatan Data PO Berhasil!']);

        } catch (\Exception $e) {

            DB::rollback();
            return redirect()->back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }

    }


public function show($id)
    {
        
        $id = Hashids::decode($id);
        $po   = PurchaseOrder::getByID($id['0']);
        $po_items   = PurchaseOrder::getProductItem($id['0']);
        $po_history = PurchaseOrder::getHistory($id['0']);

        return view('purchase.po.show', compact('po', 'po_items','po_history'));
	}

    /**
     * Show the form for editing User.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        
        $id = Hashids::decode($id);

        $notification = Notification::where(['user_id' => Auth::user()->id, 'data_id' => $id, 'status' => 0])->first();
        if($notification){
            $data['status'] = 1;
            $notification->update($data);
        }
      
        $po = PurchaseOrder::findOrFail($id['0']);
        $po_items   = PurchaseOrder::getProductItem($id['0']);

        $currency       = DB::table('currencies')->orderBy('name','ASC')->get()->pluck('name', 'name')->prepend('Silahkan pilih...', '');
        $payment_term   = DB::table('payment_terms')->orderBy('name','ASC')->where('status',1)->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
        $po_term        = DB::table('po_terms')->orderBy('name','ASC')->where('status',1)->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
        $payment_method = DB::table('payment_methods')->orderBy('name','ASC')->where('status',1)->get()->pluck('name', 'name')->prepend('Silahkan pilih...', '');
        $price_term     = DB::table('price_terms')->orderBy('name','ASC')->where('status',1)->get()->pluck('name', 'name')->prepend('Silahkan pilih...', '');
        $price_term_location  = DB::table('cities')->orderBy('name','ASC')->get()->pluck('name', 'name')->prepend('Silahkan pilih...', '');
        $measure        = DB::table('measures')->orderBy('name','ASC')->get()->pluck('name', 'name');
        $po_note        = DB::table('po_notes')->orderBy('name','ASC')->where('status',1)->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
		$po2            = PurchaseOrder::getByID($id['0']);
		
        $discount_type = array(
            '1' => 'Percent',
            '0' => 'Currency'
        );


        return view('purchase.po.edit', compact('measure','po','po_items','payment_method','payment_term','price_term','po_term','currency','price_term_location','discount_type','po_note','po2'));
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

        $po = PurchaseOrder::findOrFail($id);

        DB::beginTransaction();

        try {
                $data = $request->all();
                $approval = getApprovalPurchasing($po->company_id,1);

                if($request->get('status')==1 ){
                    $increment = DB::table('po')
                    ->whereYear("publish", date('Y'))
                    ->where('status','!=', 0)
                    ->where('company_id',$po->company_id)
                    ->count();

                    $num    = sprintf("%'.05d", $increment + 1) ;
                    $no_po  = str_replace("DRAFT",$num,$po->doc_no);
                    $data['doc_no']    = $no_po;

                    $data['step']      = 1;
                    $data['position']  = $approval->user_id;
                    $data['status']    = 1;
                    $data['publish']   = date('Y-m-d');

                    $dataHistory['jenis']   = 'publish';

                    $dataNotification['title']      = "Approval PO";
                    $dataNotification['link']       = "/approval/po_set/".Hashids::encode($po->id);
                    $dataNotification['data_id']    = $po->id;
                    $dataNotification['content']    =  "Terdapat pengajuan PO dengan nomor: ". $po->doc_no;
                    $dataNotification['user_id']    = $approval->user_id;
                    $notifications = Notification::create($dataNotification);

                }else{
                    $data['status']        = 0;
                    $dataHistory['jenis']  = 'update';
                }

            
                $data['send_expense'] = str_replace(",", "", $request->get('send_expense'));
                $discount_amount = floatval( str_replace(",", "", $request->get('discount_amount')));
                $data['discount_amount']  = $discount_amount;
                $data['discount_item'] = ($request->get('discount_item')=='1') ? true : false;

                if($request->get('price_term') == 'FRANCO' && $request->get('price_term_location') != 'JAKARTA') $data['type'] = 'non_lpb';
                else $data['type'] = 'lpb';

                $payment_amount     = 0;
                $product            = $request->get('product_id');
                for($i=0;$i < count($product);$i++) {
                    if ($request->get('is_item_ppn') == 1) {
                        $price      = str_replace(",", "", $request->get('price')[$i]);
                        $itemprice  = ($price * 100/110);
                    } else {
                        $itemprice  = str_replace(",", "", $request->get('price')[$i]);
                    }
                    $diskon     = $request->get('qty')[$i] *  $itemprice * ($request->get('diskon_item')[$i] / 100);
                    $payment_amount  += $request->get('qty')[$i] * floatval($itemprice) - $diskon;
                }

                if($request->get('discount_type')==1){
                    $discount_amount = $payment_amount * ($discount_amount/100);
                }

                $netto = $payment_amount - $discount_amount;

                $ppn = 0;
                if($request->get('ppn')==11){
                    $data['ppn'] = 11;
                    $ppn = (11/100) * $netto;
                }else{
                    $data['ppn'] = 0;
                }

                $pph = 0;
                if($request->get('pph')){
                    $data['pph'] = $request->get('pph');
                    $pph = ($request->get('pph')/100) * $netto;
                }

                $send_expense = 0;
                if ($request->get('send_expense')) {
                    if ($request->get('send_expense_ppn') == 1) {
                        $send_expense_ppn = (11/100) * $send_expense ;
                        $send_expense = $send_expense_ppn + $send_expense ;
                    }else{
                        $send_expense = str_replace(",", "", $request->get('send_expense'));
                    }
                }

                $dp = DB::table('payment_terms')
                        ->where("id", $request->get('payment_term_id'))
                        ->first();

                $data['payment_amount'] =  $netto + $ppn + $pph + $send_expense;
                $data['dp_percentage'] =  $dp->dp_percentage;
                $data['down_payment'] =  floatval(( $data['payment_amount'] * $dp->dp_percentage)/100) ;
                $data['updated_by'] = Auth::user()->id;
                $data['payment_amount'] =  $netto + $ppn + $pph + $send_expense;
				$data['last_print'] = null;
                $data['approved'] = null;
                $po->update($data);


                $dataPR = $cases = $qty_parsial = $ids = [];

                $po_ids = $po_qty = $po_specification = $po_price = $po_diskon = [];
                $product = $request->get('product_id');

                $discount_item = 0;
                if ($request->get('discount_item')=='1') {
                    $discountTotal = floatval(str_replace(",", "", $request->get('discount_amount')));
                    $discount_item = $discountTotal/count($product);
                }


                for($i=0;$i < count($product);$i++) {
                    if ($request->get('is_item_ppn')==1) {
                        $price      = str_replace(",", "", $request->get('price')[$i]);
                        $item_price  = ($price * 100/110);
                    } else {
                        $item_price  = str_replace(",", "", $request->get('price')[$i]);
                    }
                    
                    if ($request->get('discount_type')==1) {
                        $price = str_replace(",", "", $request->get('price')[$i]);
                        $price_after_discount  = $price - ($price * ($discount_item/100));
                    }else{
                        $price_after_discount = str_replace(",", "", $request->get('price')[$i])-$discount_item;
                    }

                    $diskon_item  = ($request->get('diskon_item')[$i]) ? $request->get('diskon_item')[$i] : 0;

                    $po_ids[]               = $request->get('po_item_id')[$i];
                    $po_qty[]               = "WHEN id = {$request->get('po_item_id')[$i]} THEN ".$request->get('qty_po')[$i];
                    $po_specification[]     = "WHEN id = {$request->get('po_item_id')[$i]} THEN '".$request->get('specification')[$i]."'";
                    $po_price[]             = "WHEN id = {$request->get('po_item_id')[$i]} THEN ". str_replace(",", "", $item_price);
                    $po_price_discount[]    = "WHEN id = {$request->get('po_item_id')[$i]} THEN ".$price_after_discount;
                    $po_diskon[]            = "WHEN id = {$request->get('po_item_id')[$i]} THEN ".$diskon_item;

                    $ids[]    = $request->get('pr_item_id')[$i];

                    if ($request->get('qty')[$i] == $request->get('qty_po')[$i] || $request->get('qty_po')[$i] > $request->get('qty')[$i]) {
                        $cases[]        = "WHEN id = {$request->get('pr_item_id')[$i]} THEN 1";
                        $qty_parsial [] = "WHEN id = {$request->get('pr_item_id')[$i]} THEN 0";
                    } else {
                        $qtyParsial = ($request->get('qty_po')[$i] - $request->get('qty')[$i]);
                        $cases[]        = "WHEN id = {$request->get('pr_item_id')[$i]} THEN 2";
                        $qty_parsial [] = "WHEN id = {$request->get('pr_item_id')[$i]} THEN ".$qtyParsial;
                    }

                }

                $po_ids             = implode(',', $po_ids);
                $po_qty             = implode(' ', $po_qty);
                $po_specification   = implode(' ', $po_specification);
                $po_price           = implode(' ', $po_price);
                $po_price_discount  = implode(' ', $po_price_discount);
                $po_diskon          = implode(' ', $po_diskon);

                \DB::update("UPDATE po_items SET qty  = CASE {$po_qty} END, discount = CASE {$po_diskon} END, specification = CASE {$po_specification} END,  price = CASE {$po_price} END, price_discount = CASE {$po_price_discount} END WHERE id in ({$po_ids})");

                $ids        = implode(',', $ids);
                $cases      = implode(' ', $cases);
                $qty_parsial= implode(' ', $qty_parsial);

                \DB::update("UPDATE purchase_items SET po_status  = CASE {$cases} END, qty_parsial = CASE {$qty_parsial} END WHERE id in ({$ids})");
    

                $dataHistory['po_id']   = $po->id;
                $dataHistory['user_id'] = Auth::user()->id;
                $po_history = PurchaseOrderHistory::create($dataHistory);

                if($request->get('status')==1 ){

                    $content = "Terdapat pengajuan PO dengan Nomor: ".  $po->doc_no. " yang menunggu approval anda. Mohon segara untuk melakukan approval dengan login kedalam aplikasi ERP Shipping.";
                    $msgData = array(
                        'title'         => 'Konfirmasi Approval PO',
                        'content'       => $content,
                        'name'          => $approval->name,
                        'email'         => $approval->email,
                        'no_po'         => $po->doc_no
                    );
                    if (config('app.mail_status')=='on' && $approval->notification_email == '1') {
                        Mail::send('emails.notification', $msgData, function ($message) use ($msgData) {
                            $message->to($msgData['email'], $msgData['name'])->subject('Pengajuan PO dengan no: '.  $msgData['no_po']);
                        });
                    }
                }

            DB::commit();

            if(checkParsialPR($po->purchase_id) == true) PurchaseRequisition::findOrFail($po->purchase_id)->update(array('status' => 2));
            else PurchaseRequisition::findOrFail($po->purchase_id)->update(array('status' => 4));

            return redirect()->route('purchasing.po.show', Hashids::encode($po->id))->with(['success' => 'Pembuatan PO Data Berhasil!']);
        } catch (\Exception $e) {

            DB::rollback();
            return redirect()->back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }

    }


    public function publish(Request $request, $id)
    {
      

        $po = PurchaseOrder::findOrFail($id);
        DB::beginTransaction();

        try {
            $data = $request->all();
            $approval = getApprovalPurchasing($po->company_id,1);


            $increment = DB::table('po')
            ->whereYear("publish", date('Y'))
            ->where('status','!=', 0)
            ->where('company_id',$po->company_id)
            ->count();

            $num    = sprintf("%'.05d", $increment + 1) ;
            $no_po  = str_replace("DRAFT",$num,$po->doc_no);
            $data['doc_no']    = $no_po;
            $data['step']      = 1;
            $data['position']  = $approval->user_id;
            $data['status']    = 1;
            $data['publish']   = date('Y-m-d');
			$data['last_print'] = null;
            $data['approved'] = null;

            $dataNotification['title']      = "Approval PO";
            $dataNotification['link']       = "approval.po.index";
            $dataNotification['content']    =  "Terdapat pengajuan PO dengan nomor: ". $po->doc_no;
            $dataNotification['user_id']    = $approval->user_id;
            Notification::create($dataNotification);
            $po->update($data);

            $dataHistory['jenis']   = 'publish';
            $dataHistory['po_id']   = $po->id;
            $dataHistory['user_id'] = Auth::user()->id;
            PurchaseOrderHistory::create($dataHistory);


            $content = "Terdapat pengajuan PO dengan Nomor: ".  $po->doc_no. " yang menunggu approval anda. Mohon segara untuk melakukan approval dengan login kedalam aplikasi ERP Shipping.";
            $msgData = array(
                'title'         => 'Konfirmasi Approval PO',
                'content'       => $content,
                'name'          => $approval->name,
                'email'         => $approval->email,
                'no_po'         => $po->doc_no
            );

            if (config('app.mail_status')=='on' && $approval->notification_email == '1') {
                Mail::send('emails.notification', $msgData, function ($message) use ($msgData) {
                    $message->to($msgData['email'], $msgData['name'])->subject('Pengajuan PO dengan no: '.  $msgData['no_po']);
                });
            }

            DB::commit();

            return redirect()->route('purchasing.po.show', Hashids::encode($po->id))->with(['success' => 'Pembuatan PO Data Berhasil!']);
        } catch (\Exception $e) {

            DB::rollback();
            return redirect()->back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }

    }


    public function revision(Request $request)
    {

        $po = PurchaseOrder::findOrFail($request->get('id'));
        $data['status']    = 3;
		$data['last_print'] = null;
        $data['approved'] = null;
        $po->update($data);

        $dataHistory['po_id']   = $po->id;
        $dataHistory['user_id'] = Auth::user()->id;
        $dataHistory['jenis']   = 'revisi';
        $dataHistory['message']   = 'Revisi';
        PurchaseOrderHistory::create($dataHistory);

        return redirect()->route('purchasing.po.index')->with(['success' => 'Input Perbaikan PO Berhasil!']);

    }


    public function remove(Request $request)
    {

        $po = PurchaseOrderItem::findOrFail($request->get('id'));
        $pr = PurchaseRequestItem::findOrFail($po->pr_item_id);

        if($pr->po_status == 2){
            $data['qty_parsial'] = $po->qty + $pr->qty_parsial;
            $pr->update($data);
        }else{
            $data['po_status'] = 0;
            $pr->update($data);
        }
        $po->delete();
        return redirect()->back()->with(['success' => 'Delete Data Berhasil!']);

    }


    public function delete(Request $request)
    {

        $po  = PurchaseOrder::findOrFail($request->id);

        $dataPR['status'] = '0';
        $pr = PurchaseRequisition::findOrFail($po->purchase_id);
        $pr->update($dataPR);

        $po_items   = PurchaseOrder::getProductItem($request->id);

        $ids = [];
        $dataPO = [];
        foreach($po_items as $val){
            $dataPO = array (
                'po_status'    => '0',
                'qty_parsial'  => '0'
            );
            DB::table('purchase_items')
            ->where('id', $val->pr_item_id)
            ->update($dataPO);
        }

        $po->delete();
        return redirect()->route('purchasing.po.index')->with(['success' => 'Delete Data Berhasil!']);

    }


    public function search(Request $request)
    {
       
        $data = $request->all();
        $query = 'purchaser_id='.$request->get('purchaser_id').'&project_id='.$request->get('project_id')."&supplier_id=".$request->get('supplier_id')."&start_date=".$request->get('start_date')."&end_date=". $request->get('end_date');
       
        $search = "Cari Berdasarkan: ";
        if($request->input('purchaser_id')) $search .= "<strong> Purchaser: </strong>".getDataByID('users',$request->input('purchaser_id'))->name;
        if($request->input('project_id')) $search .= "<strong> Project: </strong>".getDataByID('projects',$request->input('project_id'))->name;
        if($request->input('supplier_id')) $search .= "<strong> Supplier: </strong>".getDataByID('suppliers',$request->input('supplier_id'))->name;
        if($request->input('start_date') || $request->input('end_date')) $search .= "<strong> Periode: </strong>".$request->input('start_date'). " - ". $request->input('end_date');


        $supplier = DB::table('suppliers')->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
        $purchaser = User::where('type',4)->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Pilih Purchaser…', '');
        $project = Project::where('status', 1)->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');

        return view('purchase.po.search', compact('data','supplier','project','search','purchaser','query'));
    }


public function print($id, $type)
    {

        $id = Hashids::decode($id);
        $po = PurchaseOrder::getByID($id['0']);
        $po_items   = PurchaseOrder::getProductItem($id['0']);

        if($type == 'print') {
            if(Auth::user()->type==4){
                $item['last_print'] = Carbon::now();
                $PurchaseOrder = PurchaseOrder::where('id', $id['0']);
                $PurchaseOrder->update($item);
            }
                return view('purchase.po.print', compact('po', 'po_items'));
        }else{

            $data['po'] = $po;
            $data['po_items'] = $po_items;

            $pdf = PDF::loadView('purchase.po.pdf', $data);
            $pdf->setPaper('letter', 'potrait');
            return $pdf->download($po->doc_no.'.pdf');
		}
	}

    public function loadData(Request $request)
    {
        if ($request->has('q')) {
            $data = PurchaseOrder::search($request->q)
                ->where('status',2)
                ->get();
            $result = array();
            foreach ($data as $val) {
                $result[] = array('id' => Hashids::encode($val->id), 'doc_no' =>$val->doc_no);
            }
            return response()->json($result);
        }
    }


    public function print_merge(Request $request)
    {
        $id = explode(',', $request->get('po_id'));
		if(Auth::user()->type == 4){
            $item['last_print'] = Carbon::now();
            $PurchaseOrder = PurchaseOrder::findMany($id);
            foreach ($PurchaseOrder as $order) {
                $order->update($item);
			}
		}
        $po_data = PurchaseOrder::with(['poNote', 'supplierContact'])
            ->whereIn('id', $id)
            ->orderBy('created_at', 'DESC')
            ->get();
        foreach ($po_data as $po) {
            $telp = '';
            if ($po->supplierContact) {
                $telp = $po->supplierContact->telp;
                if (strpos($telp, '||') !== false) {
                    $telp = str_replace('||', '<br> Mobile Phone :', $telp);
                }
            }
            $po->supplierContact->telp = $telp;
        }
        return view('purchase.po.print_multiple', compact('po_data'));
	}


    public function export(Request $request)
    {

        $data = $request->all();

        if ($request->get('statusExport')==0) {
            $date = date('d-m-Y', strtotime($request->get('start_date')));
            return Excel::download(new PoExport($request->get('project_id'), $request->get('supplier_id'), $request->get('start_date'), $request->get('end_date')), 'Report-PO-'.$date.'.xlsx');
        } else{

            $query = DB::table('po')
            ->select('po.*','purchase_requisitions.doc_no AS pr_no', 'purchase_requisitions.dpm_no AS dpm_no',
            'supplier_contacts.name AS picName','supplier_contacts.telp AS picTelp','supplier_contacts.title AS picTitle','supplier_contacts.email AS picEmail',
            'suppliers.name AS supplier')
            ->leftJoin('purchase_requisitions', 'purchase_requisitions.id', '=', 'po.purchase_id')
            ->leftJoin('suppliers', 'suppliers.id', '=', 'po.supplier_id')
            ->leftJoin('supplier_contacts', 'suppliers.id', '=', 'supplier_contacts.supplier_id')
            ->when(!empty($data['doc_no']), function ($query) use ($data) {
                return $query->where('po.doc_no',$data['doc_no']);
            })->when(!empty($data['project_id']), function ($query) use ($data) {
                return $query->where('purchase_requisitions.project_id',$data['project_id']);
            })->when(!empty($data['supplier_id']), function ($query) use ($data) {
                return $query->where('po.supplier_id',$data['supplier_id']);
            })
            ->when(!empty($data['start_date']), function ($query) use ($data) {
                if($data['end_date']){
                    $start = date("Y-m-d",strtotime($data['start_date']));
                    $end   = date("Y-m-d",strtotime($data['end_date']."+1 day"));
                    return $query->whereBetween('po.created_at', [$start , $end]);
                }else{
                    return $query->where('po.created_at', $data['start_date']);
                }
            })
            ->whereIn('po.status',[2,4,5])
            ->orderBy('po.doc_no','ASC')
            ->orderBy('po.created_at','ASC')
            ->get();

            if( $query->isEmpty() ){
                return redirect()->route('purchasing.po.index')->with(['error' => 'Tidak terdapat data untuk di Export']);
            }else{


                header('Content-Type: application/vnd.ms-excel');
                header('Content-Disposition: attachment;filename="your_name.xls"');
                header('Cache-Control: max-age=0');
                return (new FastExcel($query))->download('REPORT-PO-'.date('d-m-Y').'.xlsx', function ($data) {
                    return [
                        'Nomor PO'          => $data->doc_no,
                        'Nomor PR'          => $data->pr_no,
                        'Nomor DPM'         => $data->dpm_no,
                        'Total Amount'      => $data->payment_amount,
                        'Price Term'        => $data->price_term,
                        'Price Term Location'   => $data->price_term_location,
                        'Metode Pembayaran'     => $data->payment_method,
                        'Tanggal Jatuh Tempo'   => date('d/m/Y',strtotime( $data->due_date_payment)),
                        'Supplier'          => $data->supplier,
                        'Supllier PIC'      => $data->picTitle." ".$data->picName,
                        'Supplier Telp'     => $data->picTelp,
                        'Supplier Email'    => $data->picEmail,
                        'Tanggal Input'     => dateTextMySQL2ID($data->created_at),
                        'Status'            => getStatusPO($data->status,'raw'),
                    ];
                });
            }

        }
    }

    public function getItems($id)
    {
        $items  = PurchaseOrder::getProductItemHistory($id);
        return view('purchase.po.history_items',compact('items'))->renderSections()['content'];
    }


    public function cancel(Request $request)
    {
        $po = PurchaseOrder::findOrFail($request->get('id'));
        $pr = PurchaseRequisition::findOrFail($po->purchase_id);
        $items =  PurchaseOrder::getProductItem($po->id);

        $ids = $po_status = $parsial = [];

        if($request->get('isPR') == 1){

            foreach($items as $item){
                $ids[]      = $item->pr_item_id;
                $po_status[]= "WHEN id = {$item->pr_item_id} THEN 3";
            }

            $ids        = implode(',', $ids);
            $status     = implode(' ', $po_status);

            \DB::update("UPDATE purchase_items SET po_status = CASE {$status} END WHERE id in ({$ids})");

            $dataPR['status'] = 3;

        }else{

            foreach($items as $item){

                if($item->qty > $item->qty_pr || $item->qty == $item->qty_pr){
                    $qty_parsial = 0;
                    $postatus =  0;
                    $parsial_pr[] = '1';
                }else{
                    $qty_parsial = $item->qty_pr - $item->qty;
                    $postatus =  2;
                    $parsial_pr[] = '2';
                }

                $ids[]      = $item->pr_item_id;
                $parsial[]  = "WHEN id = {$item->pr_item_id} THEN ".$qty_parsial;
                $po_status[]= "WHEN id = {$item->pr_item_id} THEN ".$postatus;
            }

            $ids     = implode(',', $ids);
            $parsial = implode(' ', $parsial);
            $status  = implode(' ', $po_status);

            \DB::update("UPDATE purchase_items SET qty_parsial = CASE {$parsial} END, po_status = CASE {$status} END WHERE id in ({$ids})");

            if (in_array('2', $parsial_pr)) {
                $dataPR['status'] = 2;
            } else {
                $dataPR['status'] = 0;
            }

        }

        $pr->update($dataPR);

        $dataPO['status']    = 6;
        $po->update($dataPO);

        $dataHistory['po_id']   = $po->id;
        $dataHistory['user_id'] = Auth::user()->id;
        $dataHistory['jenis']   = 'cancel';
        PurchaseOrderHistory::create($dataHistory);

        return redirect()->route('purchasing.po.index')->with(['success' => 'Cancel PO Berhasil!']);

    }


}
