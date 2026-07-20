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

    public function getProductByLocation (Request $request)
    {
        $query = DB::table('master_item_products as p')
            ->select([
                'p.*',
                'i.name as category_name',
                'b.name as brand_name',
                'm.name as measure_name',
            ])
            ->where('p.status', 1)
            ->whereIn('p.id', function ($sub) use ($request) {
                $sub->select('product_id')
                    ->from('inventories')
                    ->where('location_id', $request->input('location_id'));
            })
            ->leftJoin('master_items as i', function ($join) {
                $join->on('i.id', '=', 'p.item_id');
            })
            ->leftJoin('master_item_brands as b', function ($join) {
                $join->on('b.id', '=', 'p.brand_id')
                    ->where('b.status', 1);
            })
            ->leftJoin('measures as m', function ($join) {
                $join->on('m.id', '=', 'p.measure_id')
                    ->where('m.status', 1);
            });

        return response()->json($query->orderBy('p.id')->get(), 200);
    }

    public function getItemDetails(Request $request)
    {
        $query = DB::table('master_item_products as p')
            ->select([
                'p.*',
                'i.name as category_name',
                'b.name as brand_name',
                'm.name as measure_name',
            ])
            ->where('p.status', 1)
            ->leftJoin('master_items as i', function ($join) {
                $join->on('i.id', '=', 'p.item_id');
            })
            ->leftJoin('master_item_brands as b', function ($join) {
                $join->on('b.id', '=', 'p.brand_id')
                    ->where('b.status', 1);
            })
            ->leftJoin('measures as m', function ($join) {
                $join->on('m.id', '=', 'p.measure_id')
                    ->where('m.status', 1);
            });

        if ($request->filled('search')) {
            $keyword = '%' . strtolower($request->input('search')) . '%';
            $query->where(function ($q) use ($keyword) {
                $q->whereRaw('LOWER(p.name) like ?', [$keyword])
                  ->orWhereRaw('LOWER(p.description) like ?', [$keyword])
                  ->orWhereRaw('LOWER(p.part_number) like ?', [$keyword]);
            });
        }

        if ($request->filled('item_id')) {
            $query->where('p.item_id', $request->input('item_id'));
        }

        if ($request->filled('brand_id')) {
            $query->where('p.brand_id', $request->input('brand_id'));
        }

        if ($request->filled('measure_id')) {
            $query->where('p.measure_id', $request->input('measure_id'));
        }

        return response()->json($query->orderBy('p.id')->get(), 200);
    }
}

?>