<?php

namespace App\Http\Controllers\Approval;

use App\Models\PurchaseRequest;
use App\Models\Dph;
use App\Models\PurchaseRequisition;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseOrderHistory;
use App\Models\DphHistory;
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

class DphController extends Controller
{

    /**
     * Display a listing of User.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (! Gate::allows('approval_dph')) {
            return abort(401);
        }
        $user_id = Auth::user()->id;
        return view('approval.dph.index');
    }

    public function datatables()
    {
        if (! Gate::allows('approval_dph')) {
            return abort(401);
        }

        $result = DB::table('dph')
        ->select(
            'dph.*',
            'users.name AS created',
            DB::raw('COUNT(dph_suppliers.id) AS supplier_count')
        )
        ->leftJoin('dph_suppliers', 'dph_suppliers.dph_id', '=', 'dph.id')
        ->leftJoin('purchase_requisitions', 'purchase_requisitions.id', '=', 'dph.purchase_id')
        ->leftJoin('users', 'users.id', '=', 'dph.created_by')
        // ->where('dph.position', Auth::user()->id)
        ->where('dph.status', 2)
        ->groupBy('dph.id', 'users.name')
        ->orderBy('dph.created_at', 'DESC');

       return  DataTables::of($result)
        ->addColumn('action', function ($result) {
            $url_approval = "<a href='".route('approval.dph.set', ['id' => Hashids::encode($result->id)])."' title='".trans('app.show_title')."' data-toggle='tooltip' class='btn btn-outline'><span class='ti-thumb-up icon-lg'></span> </a>";
            return '<div class="btn-group">'.$url_approval.'</div>';
        })
        ->editColumn('supplier_count', function ($result) {
            return $result->supplier_count.' Supplier';
        })
        ->rawColumns(['action','supplier_count'])
        ->make(true);

    }


    public function set($id)
    {
        if (! Gate::allows('approval_dph')) {
            return abort(401);
        }
        $id = Hashids::decode($id);
        $dph   = Dph::getByID($id['0']);

        return view('approval.dph.set', compact('dph'));
    }

    

    public function update(Request $request, $id)
    {   
        $status = $request->get('status');
        $company = DB::table('companies')->select('companies.*')->where('companies.id','=',$request->get('company_id'))->first();
        if(!$company){
            return redirect()->route('purchasing.dph.show', Hashids::encode($id))->with(['warning' => 'data Company Kosong']);
        }
        $dph_data = DB::table('dph')->select('dph_suppliers.*')
        ->leftJoin('dph_suppliers','dph_suppliers.dph_id','=','dph.id')
        ->where('dph.id',$id)
        ->get();
            //APPROV
            if($status == 1){
                //TERBIT PO
                $data_dph = getDataApprovalDphSupplierByDph($id);

                $approval = getApprovalPurchasing($request->get('company_id'),1);

                if(count($data_dph) > 0){

                    $increment_awal = DB::table('po')
                    ->whereYear("publish", date('Y'))
                    ->where('status','!=', 0)
                    ->where('company_id',$request->get('company_id'))
                    ->count();
                    $i=1;
                    foreach($data_dph as $val){
                        $num = sprintf("%'.05d", $increment_awal+$i);
                        $data['doc_no'] = "PO-".$company->code."-JKT-".date('my')."-".$num;
                        $data['supplier_id'] = $val->supplier_id;
                        $data['delivery_date'] = null;
                        $data['currency'] = $val->currency;
                        $data['payment_method'] = 'BANK TRANSFER';
                        $data['dp_percentage'] = $val->dp_percentage;
                        $data['discount_type'] = $val->discount_type;
                        $data['discount_amount'] = $val->discount_amount;
                        $data['payment_term_id'] = $val->payment_term_id;
                        $data['po_term_id'] = $val->po_term_id;
                        $data['price_term'] = $val->price_term;
                        $data['price_term_location'] = $val->price_term_location;
                        $data['due_date_payment'] = null;
                        $data['position'] = $approval->user_id;
                        $data['pph'] = null;
                        $data['ppn'] = $val->ppn;
                        $data['notes'] = null;
                        $data['lpb_status'] = 0;
                        $data['created_by'] = $val->created_by;
                        $data['updated_by'] = $val->updated_by;
                        $data['status'] = 11;
                        $data['send_expense_ppn'] = $val->send_expense_ppn;
                        $data['purchase_id'] = $val->pr_id;
                        $data['supplier_contact_id'] = $val->supplier_contact_id;
                        $data['step'] = 1;
                        $data['company_id'] = $val->company;
                        $data['type'] = $val->type;

                        $data['approved_by'] = null;
                        $data['approved'] = null;
                        $data['publish'] = null;
                        $data['last_print'] = null ;

                        //DEFAULT
                        $data['po_note'] = 9;
                        $data['po_term_id'] = 1 ;

                        // $data['payment_amount'] = ;
                        // $data['down_payment'] = ;???
                        // $data['payment_status'] = ;
                        // $data['discount_item'] = ;

                        $po = null;
                        $po = PurchaseOrder::create($data);
                        $items = getItemApprovalDph($val->id);

                        $dataItem = null;
                        foreach($items as $item){
                            $dataItem[] = [
                                'product_id' => $item->product_id,
                                'qty' => $item->qty,
                                'price' => $item->price,
                                'status' => $item->status,
                                'isReady' => null,
                                'pr_item_id' => $item->pr_item_id,
                                'po_id' => $po->id,
                                'lpb_status' => 0 ,
                                'measure' => $item->measure,
                                'inventory_status' => null,
                                'qty_parsial' => 0 ,
                                'discount' => $item->discount,
                                'specification' => $item->specification,
                                'price_discount' => $item->price_discount,
                            ];
                        }
                        PurchaseOrderItem::insert($dataItem);
                        $i += 1;
                    }
                    $dph   = Dph::getByID($id);
                    return view('approval.dph.index')->with(['success' => 'Approval Berhasil Terbit No PO']);

                }else{
                    return redirect()->route('purchasing.dph.show', Hashids::encode($id))->with(['success' => 'Tidak Terdapat Data Item Yang Dipilih Untuk Terbit PO']);
                }
                if($request->get('step')+1 > count(getAllApprovalPurchasing($request->get('company_id')))){
            }
            //NEXT APPROVAL
            else{

            }
        }
        //PERBAIKAN
        else{
        }
    }
}
