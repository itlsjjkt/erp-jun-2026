<?php

namespace App\Http\Controllers\Purchasing;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Vinkla\Hashids\Facades\Hashids;


class ReceiptPoController extends Controller
{
    public function index()
    {
        return view('purchase.receipt-po.index');
    }

    public function datatables(Request $request)
    {
        $data = $request->all();

        if (isAdministrator() || Auth::user()->data_access == 1)
            $result = PurchaseOrder::getData($data);
        else
            $result = PurchaseOrder::getData($data, Auth::user()->id);

        $result->whereIn('po.status', [2, 4, 5]);

        return DataTables::of($result)
            ->addColumn('action', function ($result) {
                $url_view = "<a href='" . route('purchasing.receipt-po.show', \Hashids::encode($result->id)) . "'
                                title='Detail' data-toggle='tooltip' class='btn btn-outline'>
                                <span class='ti-eye icon-lg'></span>
                            </a>";

                return '<div class="btn-group">' . $url_view . '</div>';
            })
            ->editColumn('doc_no', function ($result) {
                return "<a target='_blank' href='" . route('purchasing.receipt-po.show', \Hashids::encode($result->id)) . "'
                            title='Detail Receipt PO' data-toggle='tooltip'>" . $result->doc_no . "</a>";
            })
            ->editColumn('status', function ($result) {
                return getStatusPO($result->status);
            })
            ->editColumn('created_at', function ($result) {
                return $result->created_at ? with(new Carbon($result->created_at))->format('Y/m/d H:i:s') : '';
            })
            ->editColumn('type', function ($result) {
                if ($result->type == 'lpb') {
                    return '<span class="badge badge-primary">LPB</span>';
                } else {
                    return '<span class="badge badge-info">BPB</span>';
                }
            })
            ->rawColumns(['action', 'status', 'doc_no','type'])
            ->make(true);
    }
    public function show($id)
    {
        $id = Hashids::decode($id);
        $po              = PurchaseOrder::getByID($id[0]);
        $po_items        = PurchaseOrder::getProductItem($id[0]);
        $po_history      = PurchaseOrder::getHistory($id[0]);
        $po_type_histories = DB::table('po_change_type_histories')
                            ->where('po_id', $po->id)
                            ->orderBy('changed_at', 'desc')
                            ->get();

        // Receipt Data berdasarkan type
        $receipt_data = collect();
        if ($po->type == 'lpb') {
            $receipt_data = DB::table('lpb')
                ->select('lpb.*', 'po.doc_no AS po_no', 'purchase_requisitions.doc_no AS pr_no',
                        'purchase_requisitions.dpm_no AS dpm_no', 'purchases.created_at AS created_dpm')
                ->leftJoin('po', 'po.id', '=', 'lpb.po_id')
                ->leftJoin('purchase_requisitions', 'po.purchase_id', '=', 'purchase_requisitions.id')
                ->leftJoin('purchases', 'purchases.id', '=', 'purchase_requisitions.purchase_id')
                ->leftJoin('locations', 'locations.id', '=', 'lpb.location_id')
                ->leftJoin('users', 'users.id', '=', 'lpb.created_by')
                ->where('lpb.po_id', $po->id)
                ->get();
        } else {
            $receipt_data = DB::table('bpb')
                ->select('bpb.*', 'users.name AS created', 'po.doc_no AS noPO')
                ->leftJoin('po', 'po.id', '=', 'bpb.po_id')
                ->leftJoin('users', 'users.id', '=', 'bpb.created_by')
                ->whereNull('bpb.spb_id')
                ->where('bpb.po_id', $po->id)
                ->get();
        }

        return view('purchase.receipt-po.show', compact('po', 'po_items', 'po_history', 'po_type_histories', 'receipt_data'));
    }
}
