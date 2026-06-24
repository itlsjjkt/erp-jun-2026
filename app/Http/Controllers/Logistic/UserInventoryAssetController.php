<?php

namespace App\Http\Controllers\Logistic;

use App\Models\InventoryAsset;
use App\Models\InventoryAssetHistory;
use App\Models\UserAsset;
use App\Models\ParentInventoryAsset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Vinkla\Hashids\Facades\Hashids;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Redirect;
use Carbon\Carbon;
use Auth;
use File;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Barryvdh\DomPDF\Facade\Pdf;
use Rap2hpoutre\FastExcel\FastExcel;
use Illuminate\Http\UploadedFile;
use App\Traits\UploadTrait;


class UserInventoryAssetController extends Controller
{
    use UploadTrait;
    public function index()
    {
        if (! Gate::allows('inventory_asset')) {
            return abort(401);
        }
        return view('logistic.user_inventory_asset.index');
    }


    public function datatables()
    {
        if (!Gate::allows('inventory_asset')) {
            return abort(401);
        }

        $query = DB::table('user_assets')
            ->leftJoin('inventory_assets', 'user_assets.id', '=', 'inventory_assets.user_asset_id')
            ->select(
                'user_assets.id',
                'user_assets.name',
                'user_assets.nik',
                'user_assets.status',
                DB::raw('COUNT(inventory_assets.id) as count')
            )
            ->groupBy('user_assets.id', 'user_assets.name', 'user_assets.nik', 'user_assets.status')
            ->orderBy('user_assets.name', 'ASC');

        return DataTables::of($query)
            ->addColumn('action', function ($row) {
                $show   = "<a value='" . route('logistic.user_inventory_asset.show', ['user_inventory_asset' => Hashids::encode($row->id)]) . "' class='icon-lg modalShow'
                            style='padding-top: 5px;padding-left: 5px;'
                            title='Show Data'
                            data-toggle='modal'
                            data-target='#modalShow'>
                            <span class='ti-eye icon-lg'></span>
                        </a>";
                return '<div class="btn-group">' . $show . '</div>';
            })
            ->rawColumns(['action'])
            ->make(true);
    }


    public function show($idx){
        $id  = Hashids::decode($idx);
        $user = DB::table('user_assets')->where('id','=',$id)->first();
        $data = DB::table('inventory_assets')
            ->select(
                'inventory_assets.*',
                'master_item_products.name AS produk',
                'master_item_products.part_number AS produkpn',
                'master_item_products.code AS produkcode',
                'locations.name AS lokasi',
                'companies.name AS companyy',
                'user_assets.name AS user',
                'parent_inventory_assets.doc_no AS parent_doc_no',
                'master_item_brands.name AS brand',
                'departments.name AS depttt'
            )
            ->leftJoin('parent_inventory_assets','parent_inventory_assets.id','=','inventory_assets.parent_inventory_asset_id')
            ->leftJoin('master_item_products','master_item_products.id','=','inventory_assets.product_id')
            ->leftJoin('locations','locations.id','=','inventory_assets.location_id')
            ->leftJoin('departments','departments.id','=','inventory_assets.department_id')
            ->leftJoin('companies','companies.id','=','inventory_assets.company_id')
            ->leftJoin('user_assets','user_assets.id','=','inventory_assets.user_asset_id')
            ->leftJoin('master_item_brands','master_item_brands.id','=','master_item_products.brand_id')
            ->where('inventory_assets.user_asset_id', '=',$id)
            ->orderBy('master_item_products.name','ASC')
            ->get();
        return view('logistic.user_inventory_asset.show',compact('data','user'))->renderSections()['content'];
    }
}
