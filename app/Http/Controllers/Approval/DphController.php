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
use App\Models\Company;
use App\Models\DphItem;
use App\Models\DphSupplier;
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
        ->where('dph.position', Auth::user()->id)
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
        try {
            $dph = Dph::findOrFail($id);
            if($request->get('save') == 'Perbaiki'){
                $request->validate([
                    'message' => 'required'
                ]);

                $dataDph['message'] = $request->get('message');
                $dataDph['step'] = 1;
                $dataDph['status'] = 3;
                $dataDph['position']= getApprovalDph($dph->company_id, $dataDph['step'])->user_id;
                $dataDph['updated_by'] = Auth::user()->id;
                $dph->update($dataDph);

                // update dph_histories
                $dataDphHistory['jenis']  = 'revisi';
                $dataDphHistory['message']  = 'Mengajukan perbaikan dokumen ' . $dph->doc_no . ' : ' . $request->get('message');
                $dataDphHistory['dph_id'] = $dph->id;
                $dataDphHistory['user_id'] = Auth::user()->id;
                DphHistory::create($dataDphHistory);

                return redirect()->route('approval.dph.index')->with(['success' => 'Berhasil Memperbaiki DPH!']);
            }
            else{
                if($dph->step+1 > count(getAllApprovalDph($dph->company_id))){
                    $dph_suppliers =  DphSupplier::where('dph_id', $dph->id)->get();
                    foreach ($dph_suppliers as $dph_supply){
                        $dph_items = DphItem::where('dph_supplier_id', $dph_supply->id)->where('is_recomendation', 1)->get();

                        if($dph_items->isEmpty()){
                            continue;
                        }

                        $dph_suppliers_recommendation = DphSupplier::where('id', $dph_items['0']->dph_supplier_id)->first()->toArray();
                        
                        // create new po
                        $new_po = $dph_suppliers_recommendation;
                        $company = Company::where('id', $dph->company_id)->select('code')->first();
                        $increment = DB::table('po')
                        ->whereYear("created_at", date('Y'))
                        ->where('status','!=', 0)
                        ->where('company_id',$dph->company_id)
                        ->count();

                        $num = sprintf("%'.05d", $increment + 1) ;
                        $no_po = "PO-".$company->code."-JKT-".date('my')."-".$num;

                        $new_po['doc_no'] =  $no_po;
                        $new_po['company_id'] =  $dph->company_id;
                        $new_po['purchase_id'] =  $dph->purchase_id;
                        $new_po['dph_id'] =  $dph->id;
                        $new_po['status'] =  2;
                        $new_po['step'] =  1;
                        $new_po['po_note'] =  9;
                        $new_po['position'] =  getApprovalPurchasing($dph->company_id, $new_po['step'])->user_id;
                        $new_po['created_by'] =  $dph->created_by;
                        $new_po['created_at'] =  Carbon::now();
                        $new_po['due_date_payment'] =  null;
                        $new_po['discount_amount'] =  $dph_suppliers_recommendation['discount_amount'];
                        $new_po['delivery_date'] =  null;
                        $new_po['estimated_receipt'] = Carbon::now()->addDays($dph_suppliers_recommendation['estimated_delivery_day'])->toDateString('yyyy-mm-dd');
                        $new_po['approved'] = Carbon::now()->format('Y-m-d H:i');
                        $new_po['approved_by'] = Auth::user()->id;
                        
                        unset($new_po['id']);
                        $po = PurchaseOrder::create($new_po);
                        $total = 0;
                        foreach($dph_items as $dph_item){
                            // create new po_items
                            $new_po_items = $dph_item->toArray();
                            $new_po_items['po_id'] = $po->id;
                            $new_po_items['created_at'] = Carbon::now();;
                            unset($new_po_items['id']);

                            $po_items = PurchaseOrderItem::create($new_po_items);
                            DB::table('po_items')->where('id', $po_items->id)->update([
                                'pr_item_id' => $new_po_items['pr_item_id'],
                                'measure' => $new_po_items['measure'],
                            ]);

                            $pr_items = DB::table('purchase_items')->where('id', $new_po_items['pr_item_id'])->first();

                            $po_status = $pr_items->po_status;

                            if($po_status == 2){
                                $qty_parsial = $pr_items->qty_parsial - $po_items->qty;
                            } else {
                                $qty_parsial = $pr_items->qty - $po_items->qty;
                            }

                            if($qty_parsial > 0) {
                                $po_status = 2;
                                $status = 4;
                            } else {
                                $po_status = 1;
                                $status = 4;
                            }

                            DB::table('purchase_items')->where('id', $new_po_items['pr_item_id'])->update([
                                'qty_parsial' => $qty_parsial,
                                'po_status' => $po_status,
                                'status' => $status,
                            ]);

                            $total += $po_items->price * $po_items->qty - (($po_items->price * $po_items->qty) *  $po_items->discount / 100);
                        }

                        if($po->discount_type == 1){
                            if($po->discount_item == true){
                                $discount_amount = 0;
                            } else {
                                $discount_amount = $total * ($po->discount_amount/100);
                            }
                        }else{
                            $discount_amount = $dph_suppliers_recommendation['discount_amount'];
                        }

                        $netto = $total - (float)$discount_amount;

                        if ($po->send_expense_ppn == 1) {
                            $send_expense_ppn = (11 / 100) * (float)$po->send_expense;
                            $po->send_expense = (float)$send_expense_ppn + (float)$po->send_expense;
                        }
                        $ppn = $netto * ((float)$po->ppn/100);
                        $pph = $netto * ((float)$po->pph/100);
                        $payment_amount = $netto - (float)$pph + (float)$ppn + (float)$po->send_expense;
                        $dataPo['payment_amount'] = $payment_amount ;
                        $dataPo['down_payment'] =  ($po->dp_percentage/100) * $dataPo['payment_amount'];
                        $po->update($dataPo);

                        // create po_histories

                        $dataPoHistory0['jenis']  = 'approval';
                        $dataPoHistory0['po_id'] = $po->id;
                        $dataPoHistory0['user_id'] = getFirstApprovalDph($dph->id)->user_id;
                        $dataPoHistory0['created_at'] = getFirstApprovalDph($dph->id)->last_approval_time;
                        PurchaseOrderHistory::create($dataPoHistory0);

                        $dataPoHistory['jenis']  = 'approval_dph';
                        $dataPoHistory['po_id'] = $po->id;
                        $dataPoHistory['user_id'] = Auth::user()->id;
                        PurchaseOrderHistory::create($dataPoHistory);


                        if (checkParsialPR($po->purchase_id) == true) {
                            PurchaseRequisition::findOrFail($po->purchase_id)->update(array('status' => 2));
                        } else {
                            if (checkParsialClosePR($po->purchase_id) == true) {
                                PurchaseRequisition::findOrFail($po->purchase_id)->update(array('status' => 6));
                            } else {
                                PurchaseRequisition::findOrFail($po->purchase_id)->update(array('status' => 4));
                            }
                        }                        
                    }
                    // update dph_histories
                    $dataDphHistory['jenis']  = 'approval';
                    $dataDphHistory['message']  = 'melakukan approval dokumen ' . $dph->doc_no;
                    $dataDphHistory['dph_id'] = $dph->id;
                    $dataDphHistory['user_id'] = Auth::user()->id;

                    DphHistory::create($dataDphHistory);

                    $dataDph['status'] = 4;
                    $dataDph['approved_by'] = Auth::user()->id;
                    $dataDph['approved'] = Carbon::now()->format('Y-m-d H:i:s');
                    $dataDph['updated_by'] = Auth::user()->id;
                    $dph->update($dataDph);

                    return redirect()->route('approval.dph.index')->with(['success' => 'Berhasil Approve DPH!']);
                }
                else{
                    $dataDph['step'] = $dph->step + 1;
                    $dataDph['position']= getApprovalDph($dph->company_id, $dataDph['step'])->user_id;
                    $dataDph['approved_by'] = Auth::user()->id;
                    $dataDph['approved'] = Carbon::now()->format('Y-m-d H:i:s');
                    $dataDph['updated_by'] = Auth::user()->id;
                    $dph->update($dataDph);

                    // update dph_histories
                    $dataDphHistory['jenis']  = 'approval';
                    $dataDphHistory['message']  = 'melakukan approval dokumen ' . $dph->doc_no;
                    $dataDphHistory['dph_id'] = $dph->id;
                    $dataDphHistory['user_id'] = Auth::user()->id;
                    DphHistory::create($dataDphHistory);

                    return redirect()->route('approval.dph.index')->with(['success' => 'Berhasil Approve DPH!']);
                }
            }
        } catch (\Exception $e) {

            DB::rollback();
            return redirect()->back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
