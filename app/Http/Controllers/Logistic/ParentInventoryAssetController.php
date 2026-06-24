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

class ParentInventoryAssetController extends Controller
{
    public function index()
    {
        if (! Gate::allows('inventory_asset')) {
            return abort(401);
        }
        $company = DB::table('companies')->get()->pluck('name', 'id')->prepend('Silahkan pilih company', '');
        return view('logistic.parent_inventory_asset.index',compact('company'));
    }
    public function datatables()
    {
        if (! Gate::allows('inventory_asset')) {
            return abort(401);
        }
        $result = DB::table('parent_inventory_assets')->orderBy('created_at','DESC');
        return  DataTables::of($result)

        ->addColumn('action', function ($result) {
            $url_show = route('logistic.parent_inventory_asset.show', ['parent_inventory_asset' => Hashids::encode($result->id)]);
            $url_print = route('logistic.parent_inventory_asset.print', ['id' => Hashids::encode($result->id)]);

            return '
                <a href="' . $url_show . '" class="icon-lg" style="padding:5px;" title="Show Data">
                    <span class="ti-eye icon-lg"></span>
                </a>
                <a href="' . $url_print . '" class="icon-lg" target="_blank" style="padding:5px;" title="Print">
                    <i class="ti-printer icon-lg"></i>
                </a>
            ';
        })
        
        ->editColumn('status', function ($result){
            return getStatusDia($result->id);
        })
        ->editColumn('created_by', function ($result){
            return getUserByID($result->created_by);
        })
        ->editColumn('created_at', function ($result){
            return getDateId($result->created_at);
        })
        ->rawColumns(['action', 'status', 'created_at'])
        ->make(true);
    }

    public function show($idx){
        $id  = Hashids::decode($idx);
        $dia = DB::table('parent_inventory_assets')->where('id','=',$id)->first();
        $ast = DB::table('inventory_assets')
            ->select(
                'inventory_assets.*',
                'master_item_products.name AS produk',
                'master_item_products.part_number AS produkpn',
                'master_item_products.code AS produkcode',
                'locations.name AS lokasi',
                'user_assets.name AS user',
                'parent_inventory_assets.doc_no AS parent_doc_no',
                'master_item_brands.name AS brand'
            )
            ->leftJoin('parent_inventory_assets','parent_inventory_assets.id','=','inventory_assets.parent_inventory_asset_id')
            ->leftJoin('master_item_products','master_item_products.id','=','inventory_assets.product_id')
            ->leftJoin('locations','locations.id','=','inventory_assets.location_id')
            ->leftJoin('user_assets','user_assets.id','=','inventory_assets.user_asset_id')
            ->leftJoin('master_item_brands','master_item_brands.id','=','master_item_products.brand_id')
            ->where('inventory_assets.parent_inventory_asset_id','=',$id)
            ->whereNull('inventory_assets.deleted_at')
            ->orderBy('inventory_assets.id','ASC')
            ->get();
            return view('logistic.parent_inventory_asset.show', compact('dia', 'ast'));
    }

    public function print($id, $type = null)
    {
        // Decode ID
        $decoded = Hashids::decode($id);
        if (!isset($decoded[0])) {
            abort(404);
        }
        $parentId = $decoded[0];

        // Data Parent Inventory Asset
        $parent = DB::table('parent_inventory_assets')
            ->where('id', $parentId)
            ->first();
        abort_if(!$parent, 404);

        // Data Join
        $items = DB::table('inventory_assets as ia')
            ->leftJoin('master_item_products as mip', 'ia.product_id', '=', 'mip.id')
            ->leftJoin('master_item_brands as mib', 'mip.brand_id', '=', 'mib.id')
            ->leftJoin('users as u', 'ia.user_asset_id', '=', 'u.id') 
            ->leftJoin('locations as l', 'ia.location_id', '=', 'l.id')
            ->leftJoin('user_assets as ua', 'ia.user_asset_id', '=', 'ua.id')
            ->leftJoin('po', 'ia.relation_item_id', '=', 'po.id')
            ->leftJoin('bpb', 'ia.relation_item_id', '=', 'bpb.id') 
            ->leftJoin('departments', 'ia.department_id', '=', 'departments.id')
            ->leftJoin('companies', 'ia.company_id', '=', 'companies.id')
            ->select(
                'ia.*',
                'mip.name as product_name',
                'mip.part_number',  
                'mip.part_number as product_part_number',
                'mib.name as product_brand',
                'u.name as user_name',
                'l.name as location_name',
                'ua.*',
                'po.doc_no as po_number',
                'bpb.doc_no as bpb_number',
                'departments.name as dept_name',
                'companies.name as comp_name'
            )
            ->where('ia.parent_inventory_asset_id', $parentId)
            ->get();

        // QR Code 
        foreach($items as $data){
            $qrCodeData = QrCode::size(133)->generate('https://erp.haritashipping.com/inventory_asset/' . Hashids::encode($data->id) . '/' . $data->uuid);
            // $qrCodeData = QrCode::size(133)->generate('http://192.168.1.84:8001//inventory_asset/' . Hashids::encode($data->id) . '/' . $data->uuid);
            $qrCodeBase64 = base64_encode($qrCodeData);
            $qrCodeImage = 'data:image/png;base64,' . $qrCodeBase64;
            $data->id = $qrCodeImage;
        }

        // Data View
        $data = [
            'parent' => $parent,
            'items' => $items,
        ];

        // Generate PDF
        $pdf = PDF::loadView('logistic.parent_inventory_asset.pdf', $data);
        $pdf->setPaper('A4', 'landscape');

        return $pdf->download($parent->doc_no . '.pdf');
    }
}
