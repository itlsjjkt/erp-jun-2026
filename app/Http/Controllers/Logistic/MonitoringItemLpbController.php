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


class MonitoringItemLpbController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:lpb_monitoring_item');

        $this->status = array(
            'null' => 'IN',
            '0' => 'SOH',
            '1'  => 'OUT',
        );
    }
    /**
     * Display a listing of User.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $status = $this->status;
        return view('logistic.monitoring.item_lpb.index',compact(['status']));
    }

    public function datatables(Request $request)
    {
        $result = DB::table('lpb_items')
        ->select(
            DB::raw('SUM(lpb_items.qty) AS in'),
            DB::raw('SUM(CASE WHEN lpb.spb_status = 1 THEN lpb_items.qty ELSE 0 END) AS out'),
            DB::raw('SUM(CASE WHEN lpb.spb_status = 0 THEN lpb_items.qty ELSE 0 END) AS soh'),
            'master_item_products.name AS product',
            'master_item_products.id AS product_id',
            'master_item_products.part_number AS part_number',
            'master_item_products.code AS code',
            'master_item_brands.name AS brand',
            'satuan_beli.name AS satuanBeli',
            'companies.name AS company',
            'companies.id AS company_id'
        )
        ->leftJoin('master_item_products', 'master_item_products.id', '=', 'lpb_items.product_id')
        ->join('lpb', 'lpb.id', '=', 'lpb_items.lpb_id')
        ->leftJoin('master_item_brands', 'master_item_brands.id', '=', 'master_item_products.brand_id')
        ->leftJoin('measures AS satuan_beli', 'satuan_beli.id', '=', 'master_item_products.measure_id')
        ->leftJoin('locations','locations.id','=','lpb.location_id')
        ->leftJoin('companies','companies.id','=','locations.company_id')
        ->whereIn('lpb.status', [1, 2])
        ->groupBy(
            'master_item_products.name',
            'master_item_products.id',
            'master_item_products.part_number',
            'master_item_brands.name',
            'master_item_products.code',
            'satuan_beli.name',
            'companies.name',
            'companies.id'
            )
        ->orderBy('master_item_products.name', 'ASC');
        return DataTables::of($result)
        ->editColumn('product', function ($result) {
            return '[' . $result->code . '] ' . $result->product . '<br><small>' . ($result->part_number ? ('PN: ' . $result->part_number) : ('PN: -')) . '<br>' . ($result->brand ? ('Brand: ' . $result->brand) : ('Brand: -')) . '</small>';
        })
        ->editColumn('in', function ($result) {
            return '<div style="text-align:center">' . $result->in . '</div>';
        })
        ->editColumn('out', function ($result) {
            return '<div style="text-align:center">' . $result->out . '</div>';
        })
        ->editColumn('soh', function ($result) {
            return '<div style="text-align:center">' . $result->soh . '</div>';
        })
        ->editColumn('status', function ($result) {
            return getStatusItemLpbSunkel($result->in, $result->out);
        })
        ->addColumn('action', function ($result) {
            $url_show = '<div style="text-align:center"><a class="btn btnShow" href="#" data-product_id="'.$result->product_id.'" data-product_info="'.$result->product.' - '.$result->company.'" data-company_id="'.$result->company_id.'" type="button" data-toggle="modal" data-target="#modalShow"><span class="ti-eye icon-lg"></span></a></div>';
            return '<div style="text-align: center;">'.$url_show.'</div>';
        })
        ->addColumn('company', function ($result) {
            return $result->company;
        })
        ->addColumn('satuanBeli', function ($result) {
            return '<div style="text-align: center;">' .$result->satuanBeli. '</div>';
        })
        ->rawColumns(['product', 'in', 'out', 'soh', 'status','satuanBeli','action'])
        ->make(true);
    }

    public function export(Request $request) {
        $data = $request->all();
        $query = DB::table('lpb_items')
            ->select(
                DB::raw('SUM(lpb_items.qty) AS in'),
                DB::raw('SUM(CASE WHEN lpb.spb_status = 1 THEN lpb_items.qty ELSE 0 END) AS out'),
                DB::raw('SUM(CASE WHEN lpb.spb_status = 0 THEN lpb_items.qty ELSE 0 END) AS soh'),
                'master_item_products.name AS product',
                'master_item_products.part_number AS part_number',
                'master_item_products.code AS code',
                'master_item_brands.name AS brand',
                'satuan_beli.name AS satuanBeli',
                'companies.name AS company'
            )
            ->leftJoin('master_item_products', 'master_item_products.id', '=', 'lpb_items.product_id')
            ->join('lpb', 'lpb.id', '=', 'lpb_items.lpb_id')
            ->leftJoin('master_item_brands', 'master_item_brands.id', '=', 'master_item_products.brand_id')
            ->leftJoin('measures AS satuan_beli', 'satuan_beli.id', '=', 'master_item_products.measure_id')
            ->leftJoin('locations', 'locations.id', '=', 'lpb.location_id')
            ->leftJoin('companies', 'companies.id', '=', 'locations.company_id')
            ->whereIn('lpb.status', [1, 2])
            ->groupBy(
                'master_item_products.name',
                'master_item_products.part_number',
                'master_item_brands.name',
                'master_item_products.code',
                'satuan_beli.name',
                'companies.name'
            )
            ->orderBy('master_item_products.name', 'ASC')
            ->get();

        if ($query->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak terdapat data untuk di Export');
        } else {
            $header_style = (new Style())->setFontBold()->setBackgroundColor("F2F2F2");
            $rows_style = (new Style())
                ->setFontSize(10)
                ->setShouldWrapText(false);
            return (new FastExcel($query))
                ->headerStyle($header_style)
                ->rowsStyle($rows_style)
                ->sheet('DATA ITEM LPB', function ($sheet) {
                    $sheet->getDelegate()->getColumnDimension('A')->setWidth(500);
                    // Set the height of all rows to 75px
                    $sheet->getDelegate()->getDefaultRowDimension()->setRowHeight(75);
                })
                ->download('DATA ITEM LPB ' . date('d M Y') . ' Pukul ' . date('H-i') . '.xlsx', function ($data) {
                    return [
                        'CODE' => $data->code ?? '-',
                        'PRODUCT' => $data->product ?? '-',
                        'PART NUMBER' => $data->part_number ?? '-',
                        'BRAND' => $data->brand ?? '-',
                        'COMPANY' => $data->company ?? '-',
                        'IN' => (float)$data->in ?? '-',
                        'OUT' => (float)$data->out ?? '-',
                        'SOH' => (float)$data->soh ?? '-',
                        'SATUAN' => $data->satuanBeli ?? '-'
                    ];
                });
        }
    }

    public function getDataMonitoringItemLpb($product_id,$company_id){
        $query = DB::table('lpb')
        ->select(
            'lpb.*',
            'lpb_items.qty AS qty',
            'po.doc_no AS no_po'
        )
        ->leftJoin('lpb_items','lpb_items.lpb_id','=','lpb.id')
        ->leftJoin('locations','locations.id','=','lpb.location_id')
        ->leftJoin('po','po.id','lpb.po_id')
        ->where('lpb_items.product_id','=',$product_id)
        ->where('locations.company_id','=',$company_id)
        ->whereIn('lpb.status', [1, 2])
        ;
        return DataTables::of($query)
        ->editColumn('status', function ($result) {
            return '<div style="text-align:center">'.getStatusMonitoringItemLpb($result->spb_status).'</div>';
        })
        ->editColumn('created_by', function ($result) {
            return getUserByID($result->created_by);
        })
        ->editColumn('created_at', function ($result) {
            return with(new Carbon($result->created_at))->format('d M Y H:i:s');
        })
        ->rawColumns(['status','created_by','created_at'])
        ->make(true);
    }
}
