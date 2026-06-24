<?php

namespace App\Http\Controllers\Logistic;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use App\Traits\UploadTrait;
use Vinkla\Hashids\Facades\Hashids;
use App\Models\Notification;
use App\Exports\MonitoringExport;
use Auth;
use Storage;
use Rap2hpoutre\FastExcel\FastExcel;
use Maatwebsite\Excel\Facades\Excel;
use OpenSpout\Common\Entity\Style\Style;
use PhpOffice\PhpSpreadsheet\Worksheet\ColumnDimension;


class MonitoringItemSpbController extends Controller
{
    // function __construct()
    // {
    //     $this->middleware('permission:lpb_monitoring_item');

    //     $this->status = array(
    //         'null' => 'IN',
    //         '0' => 'SOH',
    //         '1'  => 'OUT',
    //     );
    // }

    public function index()
    {
        return view('logistic.monitoring.item_spb.index');
    }

    public function datatables(Request $request)
    {
        $result = DB::table('spb_kolis')
            ->select(
                'spb.id AS SPBID',
                'spb.doc_no', 
                'spb.created_by',
                'spb.type',
                'spb.created_at',
                'spb_kolis.qty as quantity',
                'spb_kolis.spb_item_id',
                'master_item_products.name AS product',
                'master_item_products.part_number AS part_number',
                'master_item_products.code AS code',
                'master_item_brands.name AS brand',
                'master_item_products.id as product_id',
                'companies.name AS company',
                'users.name as creator_name',
                'measures.name AS satuan',
            )
            ->leftJoin('spb', 'spb.id', '=', 'spb_kolis.spb_id') 
            ->leftJoin('purchase_items', 'purchase_items.id', '=', 'spb_kolis.pr_item_id')
            ->leftJoin('master_item_products', 'master_item_products.id', '=', 'purchase_items.product_id')
            ->leftJoin('master_item_brands', 'master_item_brands.id', '=', 'master_item_products.brand_id')
            ->leftJoin('measures', 'measures.name', '=', 'purchase_items.measure')
            ->leftJoin('locations', 'locations.id', '=', 'spb_kolis.location_id')
            ->leftJoin('companies', 'companies.id', '=', 'locations.company_id')
            ->leftJoin('users', 'users.id', '=', 'spb.created_by')
            ->whereNotNull('spb.id')
            ->orderBy('spb.created_at','DESC');

            return datatables()->of($result)

            ->addColumn('product', function ($result) {
            return '[' . $result->code . '] ' . $result->product . '<br><small>' . 
                ($result->part_number ? ('PN: ' . $result->part_number) : 'PN: -') . '<br>' .
                ($result->brand ? ('Brand: ' . $result->brand) : 'Brand: -') . '</small>';
            })

            ->addColumn('action', function ($result) {
                $hash_id = \Vinkla\Hashids\Facades\Hashids::encode($result->SPBID);
                $url_show = url('/logistic/spb/' . $hash_id);

                return '<div style="text-align:center">
                            <a href="' . $url_show . '" target="_blank">
                                <span class="ti-eye icon-lg"></span>
                            </a>
                        </div>';
            })

            ->rawColumns(['action', 'product'])
            ->make(true);
    }

    // public function getDataMonitoringItemLpb($product_id,$company_id)
    // {
    //     $query = DB::table('lpb')
    //     ->select(
    //         'lpb.*',
    //         'lpb_items.qty AS qty',
    //         'po.doc_no AS no_po'
    //     )
    //     ->leftJoin('lpb_items','lpb_items.lpb_id','=','lpb.id')
    //     ->leftJoin('locations','locations.id','=','lpb.location_id')
    //     ->leftJoin('po','po.id','lpb.po_id')
    //     ->where('lpb_items.product_id','=',$product_id)
    //     ->where('locations.company_id','=',$company_id)
    //     ->whereIn('lpb.status', [1, 2])
    //     ;
    //     return DataTables::of($query)
    //     ->editColumn('status', function ($result) {
    //         return '<div style="text-align:center">'.getStatusMonitoringItemLpb($result->spb_status).'</div>';
    //     })
    //     ->editColumn('created_by', function ($result) {
    //         return getUserByID($result->created_by);
    //     })
    //     ->editColumn('created_at', function ($result) {
    //         return with(new Carbon($result->created_at))->format('d M Y H:i:s');
    //     })
    //     ->rawColumns(['status','created_by','created_at'])
    //     ->make(true);
    // }
}
