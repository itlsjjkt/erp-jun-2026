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


class MonitoringItemBpbJakartaController extends Controller
{

    public function index()
    {
        return view('logistic.monitoring.item_bpb_jakarta.index');
    }

    public function datatables(Request $request)
    {
        $id = $request->get('bpb_id'); 

        $result = DB::table('bpb_items')
            ->select(
                DB::raw('CAST(spb_kolis.no AS INT) as no_koli'),
                'bpb_items.*',
                'bpb.id AS BPBID',
                'bpb_items.qty',
                'spb_kolis.id AS idKoli',
                'spb_kolis.qty AS qtyKoli',
                'spb_kolis.bpb_status',
                'spb_kolis.qty_parsial',
                'spb_kolis.location_id',
                'po.doc_no AS noPO',
                'po_items.price',
                'po_items.discount AS price_discount',
                'suppliers.name AS supplier',
                'purchase_requisitions.doc_no AS noPR',
                'purchase_requisitions.dpm_no AS noDPM',
                'spb.doc_no AS noSPB',
                'lpb.doc_no AS noLPB',
                'master_item_products.id AS product_id',
                'po_items.specification',
                'master_item_products.name AS product',
                'master_item_products.code AS code',
                'master_item_products.part_number',
                'master_item_products.measure_inventory AS measure',
                'master_item_products.conversion AS productConversion',
                'master_item_brands.name AS brand',
                'bpb.doc_no AS bpb_doc_no',
                'bpb.received_by AS bpb_penerima',
                'bpb.created_at AS bpb_created_at',
                'po_items.measure AS measure_name',
                'companies.name AS company',
            )
            ->leftJoin('bpb', 'bpb.id', '=', 'bpb_items.bpb_id')
            ->leftJoin('spb_kolis', 'bpb_items.spb_item_id', '=', 'spb_kolis.id')
            ->leftJoin('locations', 'locations.id', '=', 'spb_kolis.location_id')
            ->leftJoin('companies', 'companies.id', '=', 'locations.company_id')
            ->leftJoin('lpb_items', 'lpb_items.id', '=', 'spb_kolis.spb_item_id')
            ->leftJoin('po_items', 'po_items.id', '=', 'lpb_items.po_item_id')
            ->leftJoin('master_item_products', 'master_item_products.id', '=', 'lpb_items.product_id')
            ->leftJoin('master_item_brands', 'master_item_brands.id', '=', 'master_item_products.brand_id')
            ->leftJoin('purchase_items', 'purchase_items.id', '=', 'po_items.pr_item_id')
            ->leftJoin('po', 'po_items.po_id', '=', 'po.id')
            ->leftJoin('lpb', 'lpb_items.lpb_id', '=', 'lpb.id')
            ->leftJoin('spb', 'spb_kolis.spb_id', '=', 'spb.id')
            ->leftJoin('suppliers', 'suppliers.id', '=', 'po.supplier_id')
            ->leftJoin('purchase_requisitions', 'purchase_requisitions.id', '=', 'po.purchase_id')
            ->when($id, function ($query) use ($id) {
                return $query->where('bpb_items.bpb_id', $id);
            })
            ->whereNotNull('bpb.spb_id') 
            ->orderBy('no_koli', 'ASC');

        return datatables()->of($result)
            ->addColumn('product', function ($result) {
                return '[' . $result->code . '] ' . $result->product . '<br><small>' .
                    ($result->part_number ? ('PN: ' . $result->part_number) : 'PN: -') . '<br>' .
                    ($result->brand ? ('Brand: ' . $result->brand) : 'Brand: -') . '</small>';
            })

            ->addColumn('measure_name', function ($result) {
                return $result->measure_name ?? '-';
            })

            ->addColumn('action', function ($result) {
                $hash_id = \Vinkla\Hashids\Facades\Hashids::encode($result->BPBID);
                $url_show = url('/logistic/bpb/' . $hash_id);
                return '<div style="text-align:center">
                            <a href="'.$url_show.'" target="_blank">
                                <span class="ti-eye icon-lg"></span>
                            </a>
                        </div>';
            })

            ->rawColumns(['product', 'action'])
            ->make(true);
    }

    // public function datatables(Request $request)
    // {
    //     $query = DB::table('bpb_items')
    //         ->select(
    //         DB::raw('cast(spb_kolis.no AS INT) as no_koli'),
    //         'bpb_items.*',
    //         'spb_kolis.id AS idKoli',
    //         'spb_kolis.qty AS qtyKoli',
    //         'spb_kolis.bpb_status',
    //         'spb_kolis.qty_parsial',
    //         'spb_kolis.location_id',
    //         'po.doc_no AS noPO',
    //         'po_items.price',
    //         'po_items.discount AS price_discount',
    //         'po_items.measure',
    //         'suppliers.name AS supplier',
    //         'purchase_requisitions.doc_no AS noPR',
    //         'purchase_requisitions.dpm_no AS noDPM',
    //         'spb.doc_no AS noSPB',
    //         'lpb.doc_no AS noLPB',
    //         'master_item_products.id AS product_id',
    //         'po_items.specification',
    //         'master_item_products.name AS product',
    //         'master_item_products.code AS productCode',
    //         'master_item_products.part_number AS productPartNumber',
    //         'master_item_products.measure_inventory AS productMeasure',
    //         'master_item_products.conversion AS productConversion',
    //         'master_item_brands.name AS productBrand'
    //     )
    //         ->leftJoin('spb_kolis', 'bpb_items.spb_item_id', '=', 'spb_kolis.id')
    //         ->leftJoin('lpb_items', 'lpb_items.id', '=', 'spb_kolis.spb_item_id')
    //         ->leftJoin('po_items', 'po_items.id', '=', 'lpb_items.po_item_id')
    //         ->leftJoin('master_item_products', 'master_item_products.id', '=', 'lpb_items.product_id')
    //         ->leftJoin('master_item_brands', 'master_item_brands.id', '=', 'master_item_products.brand_id')
    //         ->leftJoin('purchase_items', 'purchase_items.id', '=', 'po_items.pr_item_id')
    //         ->leftJoin('po', 'po_items.po_id', '=', 'po.id')
    //         ->leftJoin('lpb', 'lpb_items.lpb_id', '=', 'lpb.id')
    //         ->leftJoin('spb', 'spb_kolis.spb_id', '=', 'spb.id')
    //         ->leftJoin('suppliers', 'suppliers.id', '=', 'po.supplier_id')
    //         ->leftJoin('purchase_requisitions', 'purchase_requisitions.id', '=', 'po.purchase_id')
    //         ->where('bpb_items.bpb_id', $id)
    //         ->orderBy('no_koli', 'ASC');

    //         return datatables()->of($result)

    //         ->rawColumns([])
    //         ->make(true);
    // }

}
