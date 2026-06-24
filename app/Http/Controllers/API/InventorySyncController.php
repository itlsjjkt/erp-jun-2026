<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\Locations;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventorySyncController extends Controller
{

    public function pushByLocation(Request $request)
    {
        $validated = $request->validate([
            'location_id'           => ['required', 'integer', 'exists:locations,id'],
            'stock_opname_event_id' => ['nullable', 'integer', 'exists:stock_opname_events,id'],
            'items'                 => ['required', 'array', 'min:1'],
            'items.*.uuid'          => ['required', 'string', 'max:191'],
            'items.*.product_id'    => ['required', 'integer'],
            'items.*.stock_onhand'  => ['required', 'integer'],
            'items.*.measure_id'    => ['nullable','integer'],
            'items.*.code_rack'     => ['nullable','string'],
            'items.*.notes'         => ['nullable','string'],
        ]);

        $locationId = (int) $validated['location_id'];
        $eventId    = $validated['stock_opname_event_id'] ?? null;

        $created = 0;
        $updated = 0;

        foreach ($validated['items'] as $row) {
            $payload = [
                'product_id'            => $row['product_id'],
                'stock_onhand'          => $row['stock_onhand'] ?? 0,
                'measure_id'            => $row['measure_id'] ?? null,
                'code_rack'             => $row['code_rack'] ?? null,
                'notes'                 => $row['notes'] ?? null,
                'stock_opname_event_id' => $eventId,
                'created_by'            => auth()->id(),
            ];

            $inventory = Inventory::where('uuid', $row['uuid'])
                ->where('location_id', $locationId)
                ->where('deleted', false)
                ->where('status', '!=', 3)
                ->first();

            if ($inventory) {
                $inventory->update($payload);
                $updated++;
            } else {
                Inventory::create(array_merge([
                    'uuid'        => $row['uuid'],
                    'location_id' => $locationId,
                    'deleted'     => false,
                ], $payload));
                $created++;
            }
        }

        return response()->json([
            'message'                => 'Sync success',
            'location_id'            => $locationId,
            'stock_opname_event_id'  => $eventId,
            'created'                => $created,
            'updated'                => $updated,
            'total_received'         => count($validated['items']),
        ], 200);
    }

    // public function locations()
    // {
    //     $list = DB::table('locations')
    //         ->leftJoin('companies as c', 'c.id', '=', 'locations.company_id') 
    //         ->orderBy('locations.id')
    //         ->select([
    //             'locations.id',
    //             'locations.name',
    //             'locations.alias',
    //             'locations.area_id',
    //             'locations.company_id',
    //             'locations.address',
    //             'locations.telp',
    //             'locations.email',
    //             // 'c.name as company_name',
    //             DB::raw('UPPER(c.name) as company_name'),
    //         ])
    //         ->get();

    //     return response()->json($list, 200);
    // }

    public function inventoriesByLocation(Request $request)
    {
        $validated = $request->validate([
            'location_id' => ['required','integer','exists:locations,id'],
            'per_page'    => ['nullable','integer','min:1','max:8000'],
        ]);

        $q = \App\Models\Inventory::query()
            ->where('location_id', $validated['location_id'])
            ->where('deleted', false) // ADDON
            ->where('status', '!=', 3); // ADDON

        // optional: eager load relasi product, measure dsb
        $q->with(['product', 'measure']);

        $perPage = $validated['per_page'] ?? 100;
        $data = $q->paginate($perPage);

        return response()->json($data, 200);
    }

    public function location_company()
    {
        $list = DB::table('locations as l')
            ->leftJoin('companies as c', 'c.id', '=', 'l.company_id')
            ->orderBy('l.id')
            ->select([
                'l.id',
                'l.name',
                'l.alias',
                'l.company_id',
                'l.area_id',                 
                'l.address',
                'l.telp',
                'l.email as location_email', 
                DB::raw('UPPER(c.name) as company_name'),
                'c.email as company_email', 
            ])
            ->get();

        return response()->json($list, 200);
    }

}

?>