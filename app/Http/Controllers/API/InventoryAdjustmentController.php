<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class InventoryAdjustmentController extends Controller
{

    public function store(Request $request)
    {
        $data = $request->validate([
            // 'status_difference'     => ['prohibited'],
            'status_scanning'       => ['prohibited'],
            'inventory_id'          => ['required','integer','exists:inventories,id'],
            'uuid'                  => ['required','uuid'],
            'id'                  => ['required','integer'],
            'product_id'            => ['required','integer','exists:master_item_products,id'],
            'stock_onhand'          => ['required','numeric','min:0'],
            'in'                    => ['required','numeric','min:0'],
            'out'                   => ['required','numeric','min:0'],
            'actual_qty'            => ['required','numeric','min:0'],
            'location'              => ['nullable','string','max:100'],
            'image'                 => ['nullable','string'],
            'note'                  => ['nullable','string'],
            'created_by'            => ['required','integer'],
            'created_at'            => ['nullable','date'],
            'updated_at'            => ['nullable','date'],
            'revision'              => ['nullable','numeric'],
            'difference'            => ['nullable','numeric'],
            'status_difference'     => ['nullable','string'],
            'stock_opname_event_id' => ['required','integer'],
            'doc_no'                => ['required','string','max:100'],
            'scan_mode'             => ['nullable','string'],
        ]);

        $res = $this->persistOne($data);
        return response()->json(['ok' => true] + $res, 200);
    }

    public function storeBulk(Request $request)
    {
        $validated = $request->validate([
            'data'                                 => ['required','array','min:1','max:1000'],
            // 'data.*.status_difference'             => ['prohibited'],
            'data.*.status_scanning'               => ['prohibited'],
            'data.*.inventory_id'                  => ['required','integer','exists:inventories,id'],
            'data.*.uuid'                          => ['required','uuid'],
            'data.*.id'                          => ['required','integer'],
            'data.*.product_id'                    => ['required','integer','exists:master_item_products,id'],
            'data.*.stock_onhand'                  => ['required','numeric','min:0'],
            'data.*.in'                            => ['required','numeric','min:0'],
            'data.*.out'                           => ['required','numeric','min:0'],
            'data.*.actual_qty'                    => ['required','numeric','min:0'],
            'data.*.location'                      => ['nullable','string','max:100'],
            'data.*.image'                         => ['nullable','string'],
            'data.*.note'                          => ['nullable','string'],
            'data.*.created_by'                    => ['required','integer'],
            'data.*.created_at'                    => ['nullable','date'],
            'data.*.updated_at'                    => ['nullable','date'],
            'data.*.revision'                      => ['nullable','numeric'],
            'data.*.difference'                    => ['nullable','numeric'],
            'data.*.status_difference'             => ['nullable','string'],
            'data.*.stock_opname_event_id'         => ['required','integer'],
            'data.*.doc_no'                        => ['required','string','max:100'],
            'data.*.scan_mode'                     => ['nullable','string'],
        ]);

        $ok = 0; $fail = 0; $errs = []; $samples = [];

        foreach ($validated['data'] as $i => $row) {
            try {
                $r = $this->persistOne($row);
                $ok++;
                if (count($samples) < 10) $samples[] = $r;
            } catch (\Throwable $e) {
                $fail++;
                if (count($errs) < 10) {
                    $errs[] = [
                        'index'        => $i,
                        'inventory_id' => $row['inventory_id'] ?? null,
                        'doc_no'       => $row['doc_no'] ?? null,
                        'error'        => $e->getMessage(),
                    ];
                }
            }
        }

        return response()->json([
            'ok'                  => true,
            'inserted_or_updated' => $ok,
            'failed'              => $fail,
            'sample_results'      => $samples,
            'errors_sample'       => $errs,
        ], 200);
    }

    protected function persistOne(array $d): array
    {
        return DB::transaction(function () use ($d) {
            $docNo    = mb_substr(trim((string)($d['doc_no'] ?? '')), 0, 100);
            $uuid     = mb_substr(trim((string)($d['uuid'] ?? '')), 0, 100);
            $location = isset($d['location']) ? mb_substr((string)$d['location'], 0, 100) : null;
            
            // Cek ID Dulu
            $inv = DB::table('inventories')->lockForUpdate()
                ->where('id', (int)$d['inventory_id'])->first();

            // Kemudian Cek UUID (Kalau Id Tidak Ada)
            if (!$inv && !empty($d['uuid'])) {
                $inv = DB::table('inventories')->lockForUpdate()
                    ->where('uuid', (string)$d['uuid'])->first();
            }

            // Fallback
            if (!$inv) {
                abort(422, 'Inventory Not Found ID/UUID');
            }

            $productId = isset($d['product_id']) ? (int)$d['product_id'] : 0;
            if ($productId <= 0 && isset($inv->product_id)) {
                $productId = (int)$inv->product_id;
            }
            if ($productId <= 0) {
                abort(422, 'Product id missing/invalid');
            }

            $stockOnhand = isset($d['stock_onhand']) ? (float)$d['stock_onhand'] : 0.0;
            $qtyIn       = isset($d['in'])           ? (float)$d['in']           : 0.0;
            $qtyOut      = isset($d['out'])          ? (float)$d['out']          : 0.0;
            $actualQty   = isset($d['actual_qty'])   ? (float)$d['actual_qty']   : 0.0;
            $stockOnhand = max(0.0, $stockOnhand);
            $qtyIn       = max(0.0, $qtyIn);
            $qtyOut      = max(0.0, $qtyOut);
            $actualQty   = max(0.0, $actualQty);
            $expected   = $stockOnhand + $qtyIn - $qtyOut;
            $difference = $actualQty - $expected;
            $now = now()->format('Y-m-d H:i:s');
            $createdAt = !empty($d['created_at']) ? Carbon::parse($d['created_at'])->format('Y-m-d H:i:s') : $now;
            $updatedAt = !empty($d['updated_at']) ? Carbon::parse($d['updated_at'])->format('Y-m-d H:i:s') : $now;
            $keys = [
                'inventory_id'          => (int)$d['inventory_id'],
                'stock_opname_event_id' => (int)$d['stock_opname_event_id'],
                'doc_no'                => $docNo,
                'uuid'                  => $uuid,
            ];

            $row = [
                'id'              => $d['id'] ?? null,
                'uuid'              => $uuid,
                'product_id'        => $productId,
                'stock_onhand'      => $stockOnhand,            
                'in'                => $qtyIn,                 
                'out'               => $qtyOut,                
                'actual_qty'        => $actualQty,             
                'location'          => $location,
                'image'             => $d['image'] ?? null,
                'note'              => $d['note'] ?? null,
                'created_by'        => $d['created_by'] ?? null,
                'revision'          => isset($d['revision'])
                                        ? (int) round((float)$d['revision'])
                                        : null,
                'difference'        => $difference,             
                'status_difference' => $d['status_difference'] ?? null,
                'updated_at'        => $updatedAt,
                'scan_mode'         => $d['scan_mode'] ?? null,
            ];

            $exists = DB::table('inventory_stock_opnames')->where($keys)->exists();
            if ($exists) {
                DB::table('inventory_stock_opnames')->where($keys)->update($row);
            } else {
                DB::table('inventory_stock_opnames')->insert($keys + $row + [
                    'created_at' => $createdAt,
                ]);
            }

            return [
                'id'                  => $d['id'] ?? null,
                'inventory_id'          => (int)$d['inventory_id'],
                'uuid'                  => $uuid,
                'product_id'            => $productId,
                'stock_onhand'          => $stockOnhand,         
                'in'                    => $qtyIn,                
                'out'                   => $qtyOut,              
                'actual_qty'            => $actualQty,            
                'expected_qty'          => $expected,           
                'difference'            => $difference,        
                'status_difference'     => $d['status_difference'] ?? null,
                'location'              => $location,
                'image'                 => $d['image'] ?? null,
                'note'                  => $d['note'] ?? null,
                'created_by'            => $d['created_by'] ?? null,
                'created_at'            => $createdAt,
                'updated_at'            => $updatedAt,
                'revision'              => isset($d['revision'])
                                            ? (int) round((float)$d['revision'])
                                            : null,
                'stock_opname_event_id' => (int)$d['stock_opname_event_id'],
                'doc_no'                => $docNo,
                'scan_mode'             => $d['scan_mode'] ?? null,
            ];
        });
    }

    // Replace Stock Ohhan Table Inventories With History (Update)
    public function applySoh(Request $request)
    {
        // Log Initial Request
        Log::info('ERP Apply SOH - Request received', [
            'user_id'       => auth()->id(),
            'ip'            => $request->ip(),
            'total_payload' => is_array($request->input('data')) ? count($request->input('data')) : 0,
        ]);

        // Validasi Playload
        $validated = $request->validate([
            'data'                         => ['required', 'array', 'min:1'],
            'data.*.inventory_id'          => ['nullable', 'integer'],
            'data.*.uuid'                  => ['nullable', 'string'],
            'data.*.actual_qty'            => ['required', 'numeric', 'min:0'],
            'data.*.doc_no'                => ['nullable', 'string'],
            'data.*.reason'                => ['nullable', 'string'],
            'data.*.stock_opname_event_id' => ['nullable', 'integer'],
        ]);

        $updated = 0;
        $failed  = 0;
        $errors  = [];

        try {
            DB::transaction(function () use ($validated, &$updated, &$failed, &$errors) {

                foreach ($validated['data'] as $i => $row) {

                    $invId = isset($row['inventory_id']) ? (int) $row['inventory_id'] : null;
                    $uuid  = trim($row['uuid'] ?? '');

                    // Minimal harus punya salah satu: ID atau UUID
                    if (!$invId && $uuid === '') {
                        $failed++;
                        $errors[] = [
                            'index'  => $i,
                            'reason' => 'inventory_id or UUID Required',
                        ];
                        continue;
                    }

                    // Serach by Id
                    $inv = null;

                    if ($invId) {
                        $inv = DB::table('inventories')
                            ->lockForUpdate()
                            ->where('id', $invId)
                            ->first();
                    }

                    // Jika Tidak Ada, Search by UUID
                    if (!$inv && $uuid !== '') {
                        $inv = DB::table('inventories')
                            ->lockForUpdate()
                            ->where('uuid', $uuid)
                            ->first();
                    }

                    // Tidak Ada Id & UUID, Fallback
                    if (!$inv) {
                        $failed++;
                        $errors[] = [
                            'index'        => $i,
                            'inventory_id' => $invId,
                            'uuid'         => $uuid,
                            'reason'       => 'Inventory Not Found By Id & UUID',
                        ];
                        continue;
                    }

                    // Hitung Selesih SOH 
                    $oldSohRaw = $inv->stock_onhand ?? 0;
                    $oldSoh    = (float) $oldSohRaw;

                    $newSohRaw = $row['actual_qty'] ?? 0;
                    $newSoh    = max(0.0, (float) $newSohRaw);

                    $diff = $newSoh - $oldSoh;

                    // Normalisasi Nilai Kecil Ke 0 Untuk $diff Float (Not Value 1.0000000001)
                    if (abs($diff) < 0.0000001) {
                        $diff = 0.0;
                    }

                    // qty_in / qty_out Untuk Histories & Akumulasi Table Inventories
                    $qtyIn  = $diff > 0 ? $diff : 0.0;
                    $qtyOut = $diff < 0 ? abs($diff) : 0.0;

                    // Description
                    $docNo   = trim($row['doc_no'] ?? '');
                    $reason  = trim($row['reason'] ?? '');
                    $eventId = $row['stock_opname_event_id'] ?? null;

                    $message = $docNo !== ''
                        ? $docNo
                        : 'APPLY_SOH_EVENT_' . ($eventId ?? '-');

                    $narasiEvent   = $eventId ? "Stock Opname Event $eventId" : "Stock Opname";
                    $narasiDoc     = $docNo !== '' ? "Dokumen : $docNo" : "";
                    $narasiSelisih = "Adjustment Stock Opname : {$oldSoh} Ke {$newSoh} (Difference " . ($diff >= 0 ? "+{$diff}" : "{$diff}") . ")";
                    $narasiUuid    = $uuid !== '' ? "UUID : $uuid" : "";
                    $narasiUser    = $reason !== '' ? "Catatan : $reason" : "";

                    $description = trim(implode(' - ', array_filter([
                        $narasiEvent,
                        $narasiDoc,
                        $narasiSelisih,
                        $narasiUuid,
                        $narasiUser,
                    ])));

                    // Hitung Nilai Column qty_in & qty_out Table inventory_histories
                    $invInOld  = (float) ($inv->in ?? 0);
                    $invOutOld = (float) ($inv->out ?? 0);

                    $invInNew  = $invInOld  + $qtyIn;
                    $invOutNew = $invOutOld + $qtyOut;

                    // Save Data Ke inventory_histories
                    DB::table('inventory_histories')->insert([
                        'inventory_id' => $inv->id,
                        'qty_in'       => $qtyIn,
                        'qty_out'      => $qtyOut,
                        'qty_awal'     => $oldSoh,
                        'message'      => $message,
                        'description'  => $description,
                        'notes'        => json_encode([
                            'event_id'  => $eventId,
                            'uuid'      => $uuid !== '' ? $uuid : null,
                            'source'    => 'apply_soh',
                        ]),
                        'created_at'   => now(),
                        'updated_at'   => now(),
                    ]);

                    // Update Data Column stock_onhand Table inventories
                    DB::table('inventories')
                        ->where('id', $inv->id)
                        ->update([
                            'stock_onhand' => $newSoh,
                            'in'           => $invInNew,
                            'out'          => $invOutNew,
                            'updated_at'   => now(),
                        ]);

                    $updated++;
                }

                Log::info('ERP Apply SOH - Transaction committed', [
                    'updated' => $updated,
                    'failed'  => $failed,
                    'errors'  => $errors,
                ]);
            });

            return response()->json([
                'ok'      => true,
                'updated' => $updated,
                'failed'  => $failed,
                'errors'  => $errors,
            ], 200);

        } catch (\Throwable $e) {
            Log::error('ERP Apply SOH - Transaction failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'ok'      => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

}
