<?php

namespace App\Http\Controllers;

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
use Illuminate\Support\Facades\Storage;
use Rap2hpoutre\FastExcel\FastExcel;

class InventoryAssetController extends Controller
{
    public function show($idreq, $uuid)
    {
        $decoded = Hashids::decode($idreq);

        // Pastikan hasil decode valid
        if (empty($decoded)) {
            return abort(404);
        }

        $id = $decoded[0];

        // Ambil data dari DB
        $result = DB::table('inventory_assets')
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
            ->where('inventory_assets.id', '=', $id)
            ->first();

        // Validasi hasil dan UUID
        if (!$result || $result->uuid !== $uuid) {
            return abort(404);
        }

        // Kirim ke view
        return view('logistic.inventory_asset.show_noauth', compact('result'));
    }


    public function downloadLampiran($id)
    {
        $result = InventoryAsset::findOrFail($id);
        $path = ltrim($result->attachment, '/');
        if (!Storage::disk('public')->exists($path)) {
            abort(404, 'File tidak ditemukan.');
        }
        return Storage::disk('public')->download($path, 'lampiran.pdf');
    }
}
