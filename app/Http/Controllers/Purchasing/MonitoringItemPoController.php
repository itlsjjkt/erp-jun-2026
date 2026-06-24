<?php

namespace App\Http\Controllers\Purchasing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use App\Models\PurchaseRequisition;
use App\Models\Project;
use App\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use App\Traits\UploadTrait;
use Vinkla\Hashids\Facades\Hashids;
use App\Models\Notification;
use App\Exports\MonitoringPoExport;
use Auth;
use Storage;
use Rap2hpoutre\FastExcel\FastExcel;
use Maatwebsite\Excel\Facades\Excel;



class MonitoringItemPoController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:monitoring_po', ['only' => ['index','show','datatables','close']]);
    }
    public function index(Request $request)
    {
        $purchaser = User::where('type',4)->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Pilih Purchaser…', '');
        $supplier = DB::table('suppliers')->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Pilih Supplier...', '');
        $data2 = DB::table('po_items')
        ->select('users.name','users.type',DB::raw('COUNT(po.created_by) as count'))
        ->leftJoin('master_item_products', 'po_items.product_id', '=', 'master_item_products.id')
        ->leftJoin('master_item_brands','master_item_products.brand_id','=','master_item_brands.id')
        ->leftJoin('measures','master_item_products.measure_id','=','measures.id')
        ->leftJoin('po', 'po_items.po_id', '=', 'po.id')
        ->leftJoin('purchase_requisitions', 'po.purchase_id', '=', 'purchase_requisitions.id')
        ->leftJoin('users','po.created_by','=','users.id')
        ->groupBy('po.created_by', 'users.name','users.type')
        ->when(Auth::user()->data_access == 2, function ($query) {
            return $query->where('users.id', Auth::user()->id);
        })
        ->get();
        return view('purchase.monitoringPo.index', compact('purchaser','data2','supplier'));
    }
    public function datatables(Request $request)
    {
        $result = DB::table('po_items')
            ->select(
                'po_items.*',
                'po.doc_no AS doc_noPo',
                'purchase_requisitions.doc_no as doc_noPr',
                'po.created_at AS createddd',
                'po.status as statusss',
                'po.created_by as createdBy',
                'po.type as typePo',
                'master_item_products.name AS nameProduct',
                'master_item_products.part_number as partNumberProduct',
                'master_item_brands.name as brandProduct',
                'users.name as userName',
                'po.id as poId',
                'purchase_requisitions.id as prId',
                'po.last_print as lastPrint',
                'po.approved as approveddd',
                'master_item_products.id as produkId',
                'users.id as user_id',
                'suppliers.name as supplierName',
                'suppliers.id as supplier_id',
                'currencies.symbol AS symb',
    
                // --------------------------------------------------
                // SUBQUERY 1: Ganti getQtyLpbByPoItemId()
                // Menghitung total qty dari lpb_items berdasarkan po_item_id
                // --------------------------------------------------
                DB::raw('(
                    SELECT COALESCE(SUM(lpb_items.qty), 0)
                    FROM lpb_items
                    LEFT JOIN lpb ON lpb.id = lpb_items.lpb_id
                    WHERE lpb_items.po_item_id = po_items.id
                    AND lpb.status IN (1, 2)
                ) AS qty_lpb'),
    
                // --------------------------------------------------
                // SUBQUERY 2: Ganti getQtySpbByPoItemId()
                // Menghitung total qty dari spb_kolis berdasarkan po_item_id
                // --------------------------------------------------
                DB::raw('(
                    SELECT COALESCE(SUM(spb_kolis.qty), 0)
                    FROM spb_kolis
                    LEFT JOIN spb ON spb.id = spb_kolis.spb_id
                    LEFT JOIN lpb_items ON lpb_items.id = spb_kolis.spb_item_id
                    WHERE lpb_items.po_item_id = po_items.id
                    AND spb.status IN (1, 2, 3)
                ) AS qty_spb'),
    
                // --------------------------------------------------
                // SUBQUERY 3: Ganti getQtyBpbJktByPoItemId()
                // Menghitung total qty BPB Jakarta berdasarkan po_item_id
                // --------------------------------------------------
                DB::raw('(
                    SELECT COALESCE(SUM(bpb_items.qty), 0)
                    FROM bpb_items
                    LEFT JOIN bpb ON bpb.id = bpb_items.bpb_id
                    LEFT JOIN spb_kolis ON spb_kolis.id = bpb_items.spb_item_id
                    LEFT JOIN lpb_items ON lpb_items.id = spb_kolis.spb_item_id
                    WHERE lpb_items.po_item_id = po_items.id
                    AND bpb.status IN (1, 2)
                ) AS qty_bpb_jkt'),
    
                // --------------------------------------------------
                // SUBQUERY 4: Ganti getQtyBpbLklByPoItemId()
                // Menghitung total qty BPB Lokal berdasarkan po_item_id
                // --------------------------------------------------
                DB::raw('(
                    SELECT COALESCE(SUM(bpb_items.qty), 0)
                    FROM bpb_items
                    LEFT JOIN bpb ON bpb.id = bpb_items.bpb_id
                    LEFT JOIN po AS po_bpb ON po_bpb.id = bpb.po_id
                    LEFT JOIN po_items AS po_items_bpb ON po_items_bpb.po_id = po_bpb.id
                    WHERE po_items_bpb.id = po_items.id
                    AND bpb.status IN (1, 2)
                ) AS qty_bpb_lkl')
            )
            ->leftJoin('master_item_products', 'po_items.product_id', '=', 'master_item_products.id')
            ->leftJoin('master_item_brands', 'master_item_products.brand_id', '=', 'master_item_brands.id')
            ->join('po', 'po_items.po_id', '=', 'po.id')
            ->leftJoin('currencies', 'currencies.name', '=', 'po.currency')
            ->leftJoin('purchase_requisitions', 'po.purchase_id', '=', 'purchase_requisitions.id')
            ->leftJoin('users', 'po.created_by', '=', 'users.id')
            ->leftJoin('suppliers', 'suppliers.id', '=', 'po.supplier_id')
            ->orderByRaw("CASE WHEN po.approved IS NOT NULL AND po.last_print IS NULL AND po.status = 2 THEN 0 ELSE 1 END")
            ->when(Auth::user()->data_access == 2, function ($query) {
                return $query->where('po.created_by', Auth::user()->id);
            })
            ->when(!empty($request->get('assigned_id')), function ($result) use ($request) {
                return $result->where('po.created_by', $request->get('assigned_id'));
            })
            ->when(!empty($request->get('supplier_id')), function ($result) use ($request) {
                return $result->where('suppliers.id', $request->get('supplier_id'));
            })
            ->when(!empty($request->get('start_date')), function ($result) use ($request) {
                $start = date("Y-m-d", strtotime($request->get('start_date')));
                $end   = date("Y-m-d", strtotime($request->get('end_date') . "+1 day"));
                return $result->whereBetween('po.created_at', [$start, $end]);
            });
    
        return DataTables::of($result)
            ->addColumn('action', function ($result) {
                $url_view = "<a href='" . route('purchasing.po.show', Hashids::encode($result->poId)) . "' title='" . trans('app.show_title') . "' data-toggle='tooltip' class='btn btn-outline'><span class='ti-eye icon-lg'></span> </a>";
                return '<div class="btn-group">' . $url_view . '</div>';
            })
            ->editColumn('createddd', function ($result) {
                return date('Y/m/d H:i', strtotime($result->createddd));
            })
            ->editColumn('nameProduct', function ($result) {
                $brand = $result->brandProduct ? 'Brand : ' . $result->brandProduct : 'Brand : -';
                $pn    = $result->partNumberProduct ? 'PN : ' . $result->partNumberProduct : 'PN : -';
                return '<span>' . $result->nameProduct . '<br><small>' . $pn . '<br>' . $brand . '</small></span>';
            })
            ->editColumn('statusss', function ($result) {
                // ✅ Tidak ada query di sini lagi — semua sudah dari subquery di atas
                return getStatusPoItemByQty(
                    $result->typePo,
                    $result->statusss,
                    $result->qty,
                    $result->qty_lpb,      // dari subquery 1
                    $result->qty_spb,      // dari subquery 2
                    $result->qty_bpb_jkt,  // dari subquery 3
                    $result->qty_bpb_lkl   // dari subquery 4
                );
            })
            ->editColumn('qty', function ($result) {
                return $result->qty . ' <small>' . $result->measure . '</small>';
            })
            ->editColumn('price', function ($result) {
                return $result->symb . '. ' . number_format($result->price, 2, ',', '.');
            })
            ->editColumn('doc_noPo', function ($result) {
                $doc_no = "<a target='_blank' href='" . route('purchasing.po.show', Hashids::encode($result->poId)) . "' title='Detail PO' data-toggle='tooltip' ";
                if ($result->lastPrint == null && $result->approveddd != null) {
                    $doc_no .= "style='font-weight:bold;'";
                }
                $doc_no .= ">" . $result->doc_noPo . "</a>";
                return $doc_no;
            })
            ->editColumn('doc_noPr', function ($result) {
                $doc_no = "<a target='_blank' href='" . route('purchasing.pr.show', Hashids::encode($result->prId)) . "' title='Detail PR' data-toggle='tooltip' >" . $result->doc_noPr . "</a>";
                return $doc_no;
            })
            ->rawColumns(['doc_noPr', 'createddd', 'action', 'statusss', 'nameProduct', 'price', 'price_discount', 'doc_noPo', 'qty', 'status'])
            ->make(true);
    }
    public function search(Request $request)
    {
        $data = $request->all();
        $queryyy = 'po.created_by='.$request->get('assigned_id')."&suppliers_id=".$request->get('supplier_id')."&start_date=".$request->get('start_date')."&end_date=". $request->get('end_date');
        $search = "Cari Berdasarkan: ";
        if($request->input('assigned_id')) $search .= "<strong> Purchaser: </strong>".getDataByID('users',$request->input('assigned_id'))->name;
        if($request->input('supplier_id')) $search .= "<strong> Supplier: </strong>".getDataByID('suppliers',$request->input('supplier_id'))->name;
        $search .= "<strong> Periode: </strong>".$request->input('start_date'). " - ". $request->input('end_date');
        $purchaser = User::where('type',4)->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Pilih Purchaser…', '');
        $supplier = DB::table('suppliers')->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Pilih Supplier...', '');
        $data2 = DB::table('po_items')
            ->select('users.name','users.type',DB::raw('COUNT(po.created_by) as count'))
            ->leftJoin('master_item_products', 'po_items.product_id', '=', 'master_item_products.id')
            ->leftJoin('master_item_brands','master_item_products.brand_id','=','master_item_brands.id')
            ->leftJoin('po', 'po_items.po_id', '=', 'po.id')
            ->leftJoin('purchase_requisitions', 'po.purchase_id', '=', 'purchase_requisitions.id')
            ->leftJoin('users','po.created_by','=','users.id')
            ->groupBy('po.created_by', 'users.name','users.type')
            ->when(Auth::user()->data_access == 2, function ($query) {
                return $query->where('users.id', Auth::user()->id);
            })
            ->get();
        return view('purchase.monitoringPo.search', compact('data','search','purchaser','queryyy','data2','supplier'));
    }
    public function show($id)
    {
        $id = Hashids::decode($id);
        $po   = PurchaseOrder::getByID($id['0']);
        $po_items   = PurchaseOrder::getProductItem($id['0']);
        $po_history = PurchaseOrder::getHistory($id['0']);

        return view('purchase.monitoringPo.show', compact('po', 'po_items','po_history'));
    }
    public function export(Request $request)
    {
        $date = date('Y-m-d');
        return Excel::download(new MonitoringPoExport( $request->get('assigned_id'),$request->get('supplier_id'),$request->get('start_date'), $request->get('end_date')), 'Report-Monitoring-Item-PO-'.$date.'.xlsx');
    }

}
