<?php

namespace App\Http\Controllers\Logistic;

use App\Models\Inventory;
use App\Models\MasterItem;
use App\Models\Workarea;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Vinkla\Hashids\Facades\Hashids;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Redirect;
use Carbon\Carbon;
use Rap2hpoutre\FastExcel\FastExcel;
use App\Traits\UploadTrait;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Storage;
use PDF;
use Auth;

class StockOpnameController extends Controller
{


    /**
     * Display a listing of Items.
     *
     * @return \Illuminate\Http\Response
     */

    public function index(Request $request)
    {
        if (! Gate::allows('stock_opname')) {
            return abort(401);
        }

        return view('logistic.stock_opname.index');
    }

    public function datatables(Request $request)
    {
        if (! Gate::allows('stock_opname')) {
            return abort(401);
        }

        $result  = DB::table('inventory_stock_opnames')
            ->select(
                'inventory_stock_opnames.*',
                'users.name AS scandby',
                'locations.name as location',
                'companies.code as companyCode',
                'master_item_products.name AS produk',
                'master_item_products.part_number AS produk_pn',
                'master_item_products.code AS produk_code',
                'measures.name AS measure'
                )
            ->leftJoin('inventories', 'inventories.id', '=', 'inventory_stock_opnames.inventory_id')
            ->leftJoin('measures', 'measures.id', '=', 'inventories.measure_id')
            ->leftJoin('locations', 'locations.id', '=', 'inventories.location_id')
            ->leftJoin('companies', 'companies.id', '=', 'locations.company_id')
            ->leftJoin('master_item_products', 'master_item_products.id', '=', 'inventories.product_id')
            ->leftJoin('users', 'users.id', '=', 'inventory_stock_opnames.created_by')
            ;
        return  DataTables::of($result)
        ->editColumn('stock_onhand', function ($result) {
            return '<div class="text-center">'.$result->stock_onhand.'<div>';
        })
        ->editColumn('actual_qty', function ($result) {
            return '<div class="text-center">'.$result->actual_qty.'<div>';
        })
        ->editColumn('measure', function ($result) {
            return '<div class="text-center">'.$result->measure.'<div>';
        })
        ->editColumn('note', function ($result) {
            return $result->note ??' -';
        })
        ->editColumn('status_difference', function ($result) {
            if (strtolower($result->status_difference) === 'match') {
                return '<span style="color: green; font-weight:bold">'.$result->status_difference.'</span>';
            } else {
                return '<span style="color: red; font-weight:bold">'.$result->status_difference.'</span>';
            }
        })
        ->editColumn('produk', function ($result) {
            return '<strong>'.$result->doc_no.'</strong><br>['.$result->produk_code.'] '.$result->produk . '<br><small>PN/SPEC: '. ($result->produk_pn ?? '-') .'</small>';
        })
        ->addColumn('action', function ($result) {
            $filename = $result->image;

            // buat URL lengkap
            $url_image = $filename 
                ? asset('storage/' . $filename)
                : '';

            $html = '<button type="button" class="btn btn-sm btn-success btn-image"
                            onclick="previewImage(this.dataset.imgUrl)"
                            data-img-url="'.e($url_image).'"
                            title="Show Image">
                            <i class="ti-image"></i>
                        </button>';

            $out = $filename ? $html : '-';
            return '<div>'.$out.'</div>';
        })

        ->rawColumns(['action','produk','status_difference','stock_onhand','actual_qty','measure'])
        ->make(true);

    }

}
