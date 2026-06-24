<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;
use Vinkla\Hashids\Facades\Hashids;

class ShipHrTransferController extends Controller
{
    public function transferAssetData(){
        try{
            $dataQuery = DB::table('inventory_assets')
                ->select(
                    'inventory_assets.*',
                    'master_item_products.name AS produk',
                    'master_item_products.part_number AS produkpn',
                    'master_item_products.code AS produkcode',
                    'locations.name AS lokasi',
                    'companies.code AS companycode',
                    'parent_inventory_assets.doc_no AS parent_doc_no',
                    'master_item_brands.name AS brand',
                    'departments.name AS depttt'
                )
                ->leftJoin('parent_inventory_assets','parent_inventory_assets.id','=','inventory_assets.parent_inventory_asset_id')
                ->leftJoin('master_item_products','master_item_products.id','=','inventory_assets.product_id')
                ->leftJoin('locations','locations.id','=','inventory_assets.location_id')
                ->leftJoin('departments','departments.id','=','inventory_assets.department_id')
                ->leftJoin('companies','companies.id','=','inventory_assets.company_id')
                ->leftJoin('master_item_brands','master_item_brands.id','=','master_item_products.brand_id')
                ->whereNull('inventory_assets.deleted_at')
                ->orderBy('master_item_products.name','ASC')
                ->get();
                foreach($dataQuery as $val){
                    $outData []= [
                        'hash_asset_id' => Hashids::encode($val->id),
                        'id' => $val->id,
                        'asset_no' => $val->doc_no,
                        'produkcode' => $val->produkcode,
                        'uuid' => $val->uuid,
                        'companycode' => $val->companycode,
                        'produk' => $val->produk,
                        'produkpn' => $val->produkpn,
                        'location' => $val->lokasi,
                        'brand'=>$val->brand,
                        'department' => $val->depttt,
                        'measure' => $val->measure,

                    ];
                }
                if (is_array($dataQuery) && empty($dataQuery)) {
                    return response()->json([
                        'message' => 'Data not found.',
                    ], 404);
                }
            return response()->json($outData);
        }catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred while fetching PO Items data.',
                'details' => $e->getMessage()
            ], 500);
        }
    }
    public function getAssetDataById($idHash = null){
        $idHash = Hashids::decode($idHash);
        try{
            $dataQuery = DB::table('inventory_assets')
                ->select(
                    'inventory_assets.doc_no AS doc_no',
                    'inventory_assets.measure AS measure',
                    'master_item_products.name AS produk',
                    'master_item_products.part_number AS produkpn',
                    'master_item_products.code AS produkcode'
                )
                ->leftJoin('master_item_products','master_item_products.id','=','inventory_assets.product_id')
                ->whereNull('inventory_assets.deleted_at')
                ->where('inventory_assets.id',$idHash)
                ->orderBy('master_item_products.name','ASC')
                ->get();
                foreach($dataQuery as $val){
                    $outData []= [
                        'doc_no' => $val->doc_no,
                        'produk' => $val->produk,
                        'produkpn' => $val->produkpn,
                        'measure' => $val->measure,
                        'produkcode' => $val->produkcode

                    ];
                }
                if (is_array($dataQuery) && empty($dataQuery)) {
                    return response()->json([
                        'message' => 'Data not found.',
                    ], 404);
                }
            return response()->json($outData);
        }catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred while fetching PO Items data.',
                'details' => $e->getMessage()
            ], 500);
        }
    }
}
