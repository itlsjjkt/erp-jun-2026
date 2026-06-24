<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MasterDataAPIController extends Controller
{
    public function products()
    {
        $list_products = DB::table('master_item_products as p')
        ->select([
            'p.id',
            'p.name',
            'p.measure_id',
            'p.code',
            'p.created_by',
            'p.created_at', 
            'p.status',
            'p.updated_at',
            'p.updated_by',
            'p.brand_id',
            'p.part_number',
            'p.publish',
            'p.description',
            'p.satuan',
            'p.item_id',
            'p.measure_inventory',
            'p.deleted_at',
            'p.conversion',       

        ])
        ->orderBy('p.id')
        ->get();

        return response()->json($list_products, 200);
    }

    public function companies()
    {
        $list_companies = DB::table('companies')
        ->select([
            'companies.id',
            'companies.name',
            'companies.alias',
            'companies.code',
        ])
        ->orderBy('companies.id')
        ->get();

        return response()->json($list_companies, 200);
    }

    public function measures()
    {
        $list_measures = DB::table('measures')
        ->select([
            'measures.id',
            'measures.name',
            'measures.status',
        ])
        ->orderBy('measures.id')
        ->get();

        return response()->json($list_measures, 200);
    }

    public function locations()
    {
        $list_locations = DB::table('locations')
        ->select([
            'locations.id',
            'locations.name',
            'locations.alias',
            'locations.company_id',
            'locations.area_id',
        ])
        ->orderBy('locations.id')
        ->get();

        return response()->json($list_locations, 200);
    }
}

?>