<?php

namespace App\Http\Controllers\Logistic;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Vinkla\Hashids\Facades\Hashids;
use Carbon\Carbon;

class ChangeTypePoController extends Controller
{

    public function index()
    {
        return view('logistic.ctp.index');
    }

    public function datatables(Request $request)
    {
        $data = $request->all();

        $result = DB::table('po')
            ->select(
                'po.*',
                'purchase_requisitions.doc_no AS no_pr',
                'suppliers.name AS supplier',
                'users.name AS created',
                'dph.doc_no AS no_dph',
                'dph.id AS dph_id',
            )
            ->leftJoin('users', 'users.id', '=', 'po.created_by')
            ->leftJoin('suppliers', 'suppliers.id', '=', 'po.supplier_id')
            ->leftJoin('dph', 'dph.id', '=', 'po.dph_id')
            ->leftJoin('purchase_requisitions', 'purchase_requisitions.id', '=', 'po.purchase_id')
            ->leftJoin('lpb', 'lpb.po_id', '=', 'po.id')
            ->where('po.status', 2)
            ->whereNull('lpb.id');

        return DataTables::of($result)
            ->addColumn('action', function ($result) {
                $encodedId = Hashids::encode($result->id);
                $url_change_type = '
                    <button title="Ubah Type PO" class="btn btn-sm btn-change-type text-success" data-toggle="modal" 
                        data-target="#modalChangeTypePO" data-id="' . $result->id . '" data-type="' . $result->type . '" data-doc_no="' . $result->doc_no . '">
                        <i class="ti-exchange-vertical icon-lg"></i>
                    </button>';

                $url_view = "<a href='" . route('purchasing.po.show', $encodedId) . "' title='Lihat Detail' data-toggle='tooltip' class='btn btn-outline' target='_blank'><span class='ti-eye icon-lg'></span></a>";

                switch ($result->status) {
                    case 2:
                        return "<div class='btn-group'>{$url_view}{$url_change_type}</div>";
                    default:
                        return "<div class='btn-group'>{$url_view}</div>";
                }
            })
            ->editColumn('type', function ($row) {
                if ($row->type === 'lpb') {
                    return 'LPB';
                } elseif ($row->type === 'non_lpb') {
                    return 'Non LPB';
                } else {
                    return '-';
                }
            })
            ->editColumn('doc_no', function ($result) {
                $url = route('purchasing.po.show', Hashids::encode($result->id));
                $style = ($result->last_print == null && $result->approved != null) ? "style='font-weight:bold;'" : '';
                return "<a target='_blank' href='{$url}' title='Detail PO' data-toggle='tooltip' {$style}>{$result->doc_no}</a>";
            })
            ->editColumn('no_pr', function ($result) {
                $url = route('purchasing.pr.show', ['id' => Hashids::encode($result->purchase_id)]);
                return "<a target='_blank' href='{$url}' title='Detail PR' data-toggle='tooltip'>{$result->no_pr}</a>";
            })
            ->editColumn('no_dph', function ($result) {
                if ($result->no_dph && auth()->user()->can('dph')) {
                    $url = route('purchasing.dph.show', Hashids::encode($result->dph_id));
                    return "<a target='_blank' href='{$url}' title='Detail DPH' data-toggle='tooltip'>{$result->no_dph}</a>";
                }
                return $result->no_dph ?? ' -';
            })
            ->editColumn('payment_amount', function ($result) {
                return "<span class='currency' data-content='" . getCurrencySymbol($result->currency) . "'>" . format_number($result->payment_amount) . "</span>";
            })
            ->editColumn('status', function ($result) {
                return getStatusPO($result->status);
            })
            ->editColumn('created_at', function ($result) {
                return $result->created_at ? with(new Carbon($result->created_at))->format('Y/m/d H:i:s') : '';
            })
            ->rawColumns(['action', 'status', 'payment_amount', 'check', 'doc_no', 'no_pr', 'no_dph'])
            ->make(true);
    }

    public function changeType(Request $request)
    {
        $request->validate([
            'po_id'    => 'required|exists:po,id',
            'po_type'  => 'required|in:lpb,non_lpb',
            'remark'   => 'nullable|string|max:1000', 
        ]);

        $po = DB::table('po')->where('id', $request->po_id)->first();

        if ($po && $po->type !== $request->po_type) {
            DB::table('po_change_type_histories')->insert([
                'po_id'      => $po->id,
                'old_type'   => $po->type,
                'new_type'   => $request->po_type,
                'remark'     => $request->remark, 
                'changed_by' => auth()->id(),
                'changed_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Update tipe PO
            DB::table('po')
                ->where('id', $po->id)
                ->update(['type' => $request->po_type, 'updated_at' => now()]);
        }

        return redirect()->back()->with('success', 'Type PO berhasil diubah.');
    }

    public function changeTypeMultiple(Request $request)
    {
        $request->validate([
            'po_id'   => 'required|string',
            'po_type' => 'required|in:lpb,non_lpb',
            'remark'  => 'nullable|string|max:1000',
        ]);

        $ids    = explode(',', $request->po_id);
        $type   = $request->po_type;
        $remark = $request->remark;

        $poList = DB::table('po')->whereIn('id', $ids)->get();

        DB::beginTransaction();
        try {
            $poToUpdate = [];
            foreach ($poList as $po) {
                
                DB::table('po_change_type_histories')->insert([
                    'po_id'      => $po->id,
                    'old_type'   => $po->type,
                    'new_type'   => $type,
                    'remark'     => $remark,
                    'changed_by' => auth()->id(),
                    'changed_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                if ($po->type !== $type) {
                    $poToUpdate[] = $po->id;
                }
            }

            if (!empty($poToUpdate)) {
                DB::table('po')
                    ->whereIn('id', $poToUpdate)
                    ->update([
                        'type'       => $type,
                        'updated_at' => now(),
                    ]);
            }

            DB::commit();
            return redirect()->back()->with('success', 'Type PO Multiple berhasil diubah.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal mengubah Type PO: ' . $e->getMessage());
        }
    }

}