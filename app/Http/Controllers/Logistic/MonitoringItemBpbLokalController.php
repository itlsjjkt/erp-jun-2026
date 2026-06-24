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


class MonitoringItemBpbLokalController extends Controller
{

    public function index()
    {
        return view('logistic.monitoring.item_bpb_lokal.index');
    }

    public function datatables(Request $request)
    {
        $id = $request->get('bpb_id'); 

        $result = DB::table('bpb_items')
            ->select(
                'bpb_items.*',
                'bpb.id AS BPBID',
                'po_items.id AS idPO',
                'po_items.qty AS qtyPO',
                'po_items.lpb_status',
                'po_items.qty_parsial',
                'po_items.price',
                'po_items.discount AS price_discount',
                'po_items.measure',
                'master_item_products.id AS product_id',
                'po_items.specification',
                'master_item_products.name AS product',
                'master_item_products.code AS code',
                'master_item_products.part_number',
                'master_item_products.measure_inventory AS measure',
                'master_item_products.conversion AS productConversion',
                'master_item_brands.name AS productBrand',
                'purchase_requisitions.doc_no AS noPR',
                'purchase_requisitions.dpm_no AS noDPM',
                'purchase_requisitions.location_id',
                'bpb.doc_no AS bpb_doc_no',
                'bpb.created_at AS bpb_created_at',
                'bpb.received_by AS bpb_penerima',
                'po_items.measure AS measure_name',
                'companies.name AS company',
            )

            ->leftJoin('bpb', 'bpb.id', '=', 'bpb_items.bpb_id')
            ->leftJoin('spb_kolis', 'bpb_items.spb_item_id', '=', 'spb_kolis.id')
            ->leftJoin('locations', 'locations.id', '=', 'spb_kolis.location_id')
            ->leftJoin('companies', 'companies.id', '=', 'locations.company_id')
            ->leftJoin('po_items', 'po_items.id', '=', 'bpb_items.spb_item_id')
            ->leftJoin('purchase_items', 'purchase_items.id', '=', 'po_items.pr_item_id')
            ->leftJoin('master_item_products', 'master_item_products.id', '=', 'purchase_items.product_id')
            ->leftJoin('master_item_brands', 'master_item_brands.id', '=', 'master_item_products.brand_id')
            ->leftJoin('purchase_requisitions', 'purchase_requisitions.id', '=', 'purchase_items.pr_id')
            
            ->when($id, fn($q) => $q->where('bpb_items.bpb_id', $id))
            ->whereNotNull('bpb.po_id') 
            ->orderBy('idPO', 'ASC');

        return datatables()->of($result)
            ->addColumn('product', function ($result) {
                return '[' . $result->code . '] ' . $result->product . '<br><small>' .
                    ($result->part_number ? ('PN: ' . $result->part_number) : 'PN: -') . '<br>' .
                    ($result->productBrand ? ('Brand: ' . $result->productBrand) : 'Brand: -') . '</small>';
            })

            ->addColumn('measure_name', function ($result) {
                return $result->measure_name ?? '-';
            })

            ->addColumn('company', function ($result) {
                return $result->company ?? '-';
            })

            ->addColumn('action', function ($result) {
                $hash_id = \Vinkla\Hashids\Facades\Hashids::encode($result->BPBID ?? $result->id);
                $url_show = url('/logistic/bpb_franco/' . $hash_id);
                return '<div style="text-align:center">
                            <a href="'.$url_show.'" target="_blank">
                                <span class="ti-eye icon-lg"></span>
                            </a>
                        </div>';
            })
            ->rawColumns(['product', 'action'])
            ->make(true);
    }

}
