<?php

namespace App\Http\Controllers\Approval;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Purchasing\PaymentCompletionController as PurchasingPC;
use App\Models\PaymentCompletion;
use App\Enums\Component;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use Vinkla\Hashids\Facades\Hashids;
use Auth;
use App\Models\PaymentCompletionDetail;

class PaymentCompletionController extends Controller
{
    // =========================================================
    // INDEX
    // =========================================================

    public function index()
    {
        $allpayment = PaymentCompletion::count();
        $payment1   = PaymentCompletion::where('type_payment', 1)->count();
        $payment2   = PaymentCompletion::where('type_payment', 2)->count();

        return view('approval.check_pc.index', compact('allpayment', 'payment1', 'payment2'));
    }

    // =========================================================
    // DATATABLES — hanya tampilkan PC status = 1 (On Progress)
    // =========================================================

    public function datatables(Request $request)
    {
        $verifySub = DB::table('payment_completion_details')
            ->selectRaw('pc_id, COUNT(*) AS total_detail, SUM(CASE WHEN verify_status = 1 THEN 1 ELSE 0 END) AS verified_detail')
            ->groupBy('pc_id');

        $q = PaymentCompletion::query()
            ->select([
                'payment_completions.*',
                'po.doc_no as no_po',
                'po.id as id_po',
                'pr.doc_no as no_pr',
                'c.name as company_nama',
                'u.name as user_nama',
                'suppliers.name AS supplier',
                DB::raw('COALESCE(vstat.total_detail, 0) AS total_detail'),
                DB::raw('COALESCE(vstat.verified_detail, 0) AS verified_detail'),
            ])
            ->leftJoin('po', 'po.id', '=', 'payment_completions.po_id')
            ->leftJoin('suppliers', 'suppliers.id', '=', 'po.supplier_id')
            ->leftJoin('purchase_requisitions as pr', 'pr.id', '=', 'po.purchase_id')
            ->leftJoin('companies as c', 'c.id', '=', 'po.company_id')
            ->leftJoin('users as u', 'u.id', '=', 'payment_completions.created_by')
            ->leftJoinSub($verifySub, 'vstat', 'vstat.pc_id', '=', 'payment_completions.id')
            ->where('payment_completions.status', 1)
            ->orderByDesc('payment_completions.id');

        if ($request->filled('type_payment') && $request->type_payment != '0') {
            $q->where('payment_completions.type_payment', $request->type_payment);
        }

        return DataTables::of($q)
            ->editColumn('no_po', function ($r) {
                return "<a target='_blank' href='" . route('purchasing.po.show', Hashids::encode($r->id_po)) . "' title='Detail PO' data-toggle='tooltip'>{$r->no_po}</a>";
            })
            ->addColumn('verify_progress', function ($r) {
                $prog = PurchasingPC::calcVerifyProgress($r);
                if ($prog['total'] === 0) return '-';
                return "{$prog['verified']} / {$prog['total']}";
            })
            ->addColumn('tgl_pembuatan', function ($r) {
                return $r->created_at ? Carbon::parse($r->created_at)->format('Y-m-d H:i') : '-';
            })
            ->addColumn('type_payment', function ($r) {
                return getTypePC(strtolower((string) $r->type_payment));
            })
            ->addColumn('status', function ($r) {
                return getStatusPC(strtolower($r->status));
            })
            ->addColumn('action', function ($r) {
                if (!Gate::allows('approval_pc') || $r->status != 1) {
                    return '';
                }

                $prog     = PurchasingPC::calcVerifyProgress($r);
                $verified = $prog['verified'];
                $total    = $prog['total'];

                $urlTambah = route('purchasing.payment_completion.tambahkelengkapan', Hashids::encode($r->id));
                $urlVerify = route('approval.verify_pc.page', Hashids::encode($r->id));
                $urlDone   = route('approval.verify_pc.done', Hashids::encode($r->id));

                $btnTambah = '<button type="button" class="btn btn-outline text-primary btn-tambahkelengkapan"
                    data-url="' . $urlTambah . '"
                    data-id="' . Hashids::encode($r->id) . '"
                    title="Returned to Pending Purchasing" data-toggle="tooltip">
                    <i class="ti-back-left icon-lg"></i>
                </button>';

                $btnVerif = '<a class="btn btn-outline text-info" href="' . $urlVerify . '" title="Check" data-toggle="tooltip">
                    <i class="ti-check icon-lg"></i>
                </a>';

                // Hitung total nilai_invoice untuk PC ini
                $totalNilaiInvoice = DB::table('payment_completion_details')
                    ->where('pc_id', $r->id)
                    ->where('component', 'nilai_invoice')
                    ->sum('value_number');

                // Hitung payment_amount dari PO
                $poData = DB::table('po')->where('id', $r->po_id)->first();
                $invoiceSufficient = $poData
                    ? $totalNilaiInvoice >= (float) $poData->payment_amount
                    : false;

                $btnDone = '';
                if ($total > 0 && $total == $verified && $invoiceSufficient) {
                    $btnDone = '<button type="button" class="btn btn-outline text-success btn-done"
                        data-url="' . $urlDone . '"
                        data-doc="' . e($r->doc_no) . '"
                        title="Done" data-toggle="tooltip">
                        <i class="ti-thumb-up icon-lg"></i>
                    </button>';
                }

                return $btnVerif . ' ' . $btnTambah . ' ' . $btnDone;
            })
            ->rawColumns(['action', 'type_payment', 'status', 'verify_progress', 'no_po'])
            ->make(true);
    }

    // =========================================================
    // VERIFY PAGE — build $details per index
    // Berlaku sama untuk TEMPO & CBD (keduanya multi-row)
    // =========================================================

    public function verifyPage($id)
    {
        if (!Gate::allows('approval_pc')) {
            return redirect()->back()->with(['error' => 'Anda tidak memiliki izin akses']);
        }

        $decoded = Hashids::decode($id);
        $id = $decoded[0] ?? null;
        if (!$id) abort(404);

        $pc = PaymentCompletion::query()
            ->leftJoin('po', 'payment_completions.po_id', '=', 'po.id')
            ->leftJoin('suppliers', 'po.supplier_id', '=', 'suppliers.id')
            ->leftJoin('companies', 'po.company_id', '=', 'companies.id')
            ->leftJoin('purchase_requisitions as pr', 'pr.id', '=', 'po.purchase_id')
            ->leftJoin('purchases as pu', 'pr.purchase_id', '=', 'pu.id')
            ->leftJoin('users as u', 'payment_completions.created_by', '=', 'u.id')
            ->leftJoin('payment_terms as pt', 'pt.id', '=', 'po.payment_term_id')
            ->select(
                'payment_completions.*',
                'po.doc_no as no_po',
                'po.id as po_id',
                'po.created_at as tgl_po',
                'po.currency as po_mata_uang',
                'po.payment_amount AS harga_po',
                'companies.name as nama_company',
                'suppliers.name as nama_supplier',
                'pr.doc_no as no_pr',
                'pr.created_at as tgl_pr',
                'u.name as nama_pembuat',
                'pt.name AS payment_term'
            )
            ->where('payment_completions.id', $id)
            ->firstOrFail();

        // Ambil semua detail sekaligus (1 query)
        $rawDetails = PaymentCompletionDetail::where('pc_id', $id)
            ->leftJoin('users as vu', 'vu.id', '=', 'payment_completion_details.verify_by')
            ->select('payment_completion_details.*', 'vu.name as verify_user_name')
            ->orderBy('payment_completion_details.index')
            ->orderBy('payment_completion_details.id')
            ->get();

        // Group by index → by component key
        $grouped = [];
        foreach ($rawDetails as $d) {
            $key = is_object($d->component) ? $d->component->value : (string) $d->component;
            $grouped[$d->index][$key] = $d;
        }

        ksort($grouped);

        // Susun $details array untuk view
        $details = [];

        foreach ($grouped as $index => $comps) {
            $get = fn(string $k) => $comps[$k] ?? null;
            $val = function ($row) {
                if (!$row) return null;
                return $row->value_number ?? $row->value_date ?? $row->value_text ?? $row->value_integer;
            };

            $details[] = [
                'index' => $index,

                // IDs per komponen (untuk form hidden)
                'id_invoice'           => $get('invoice')?->id,
                'id_faktur_pajak'      => $get('faktur_pajak')?->id,
                'id_nilai_invoice'     => $get('nilai_invoice')?->id,
                'id_file_invoice'      => $get('file_invoice')?->id,
                'id_file_faktur_pajak' => $get('file_faktur_pajak')?->id,
                'id_tgl_jatuh_tempo'   => $get('tgl_jatuh_tempo')?->id,
                'id_tgl_surat_jalan'   => $get('tgl_surat_jalan')?->id,

                // Values
                'no_si'              => $val($get('no_si')),
                'invoice'            => $val($get('invoice')) ?? '',
                'tgl_invoice'        => $val($get('tgl_invoice')) ?? '',
                'tgl_terima_invoice' => $val($get('tgl_terima_invoice')) ?? '',
                'faktur_pajak'       => $val($get('faktur_pajak')) ?? '',
                'nilai_invoice'      => $val($get('nilai_invoice')),
                'file_invoice'       => $val($get('file_invoice')),
                'file_faktur_pajak'  => $val($get('file_faktur_pajak')),
                'detail_notes'       => $val($get('detail_notes')),
                'tgl_surat_jalan'    => $val($get('tgl_surat_jalan')) ?? '',
                'tgl_jatuh_tempo'    => $val($get('tgl_jatuh_tempo')) ?? '',
                'periode_tempo'      => $val($get('periode_tempo')) ?? 0,

                // Lock status
                'islock_invoice'          => (bool) ($get('invoice')?->islock),
                'islock_faktur_pajak'     => (bool) ($get('faktur_pajak')?->islock),
                'islock_tgl_surat_jalan'  => (bool) ($get('tgl_surat_jalan')?->islock),
                'islock_proforma_invoice' => (bool) ($get('proforma_invoice')?->islock),
                'islock_tgl_jatuh_tempo'  => (bool) ($get('tgl_jatuh_tempo')?->islock),

                // Values proforma
                'proforma_invoice'       => $val($get('proforma_invoice')),
                'nilai_proforma_invoice' => $val($get('nilai_proforma_invoice')),
                'file_proforma_invoice'  => $val($get('file_proforma_invoice')),

                // Verify notes
                'verify_notes_invoice'          => $get('invoice')?->verify_note ?? '',
                'verify_notes_faktur_pajak'     => $get('faktur_pajak')?->verify_note ?? '',
                'verify_notes_tgl_surat_jalan'  => $get('tgl_surat_jalan')?->verify_note ?? '',
                'verify_notes_proforma_invoice' => $get('proforma_invoice')?->verify_note ?? '',
                'verify_notes_tgl_jatuh_tempo'  => $get('tgl_jatuh_tempo')?->verify_note ?? '',
            ];
        }

        return view('approval.check_pc.verify', compact('pc', 'details'));
    }

    // =========================================================
    // LOCK — proses verify per index
    // TEMPO & CBD keduanya lock per index/row
    // =========================================================

    public function lock(Request $request, $id)
    {
        $decoded = Hashids::decode($id);
        $id = $decoded[0] ?? null;
        if (!$id) return redirect()->back()->with('error', 'ID tidak valid');

        $pc = PaymentCompletion::findOrFail($id);

        DB::beginTransaction();
        try {

            // ── TEMPO (multi-row, lock per index) ──
            if ($pc->type_payment == 1) {

                $indexes = array_keys($request->input('index', []));

                foreach ($indexes as $idx) {
                    $idx = (int) $idx;

                    $checkInvoice    = (int) ($request->input("check_invoice.{$idx}", 0));
                    $checkFaktur     = (int) ($request->input("check_faktur_pajak.{$idx}", 0));
                    $checkSuratJalan = (int) ($request->input("check_tgl_surat_jalan.{$idx}", 0));
                    $checkProforma   = (int) ($request->input("check_proforma_invoice.{$idx}", 0));

                    // Lock group INVOICE
                    if ($checkInvoice == 1) {
                        $groupInvoice = [
                            'no_si', 'invoice', 'nilai_invoice', 'file_invoice',
                            'tgl_invoice', 'tgl_terima_invoice',
                            'periode_tempo', 'tgl_jatuh_tempo', 'detail_notes',
                        ];

                        PaymentCompletionDetail::where('pc_id', $pc->id)
                            ->whereIn('component', $groupInvoice)
                            ->where('index', $idx)
                            ->where('islock', false)
                            ->update([
                                'verify_status' => 1,
                                'verify_date'   => now(),
                                'verify_by'     => Auth::id(),
                                'islock'        => true,
                                'verify_note'   => $request->input("note_invoice.{$idx}", ''),
                            ]);
                    }

                    // Lock group FAKTUR PAJAK
                    if ($checkFaktur == 1 && $pc->is_form_faktur == 1) {
                        PaymentCompletionDetail::where('pc_id', $pc->id)
                            ->whereIn('component', ['faktur_pajak', 'file_faktur_pajak'])
                            ->where('index', $idx)
                            ->where('islock', false)
                            ->update([
                                'verify_status' => 1,
                                'verify_date'   => now(),
                                'verify_by'     => Auth::id(),
                                'islock'        => true,
                                'verify_note'   => $request->input("note_faktur_pajak.{$idx}", ''),
                            ]);
                    }

                    // Lock group SURAT JALAN
                    if ($checkSuratJalan == 1) {
                        PaymentCompletionDetail::where('pc_id', $pc->id)
                            ->where('component', 'tgl_surat_jalan')
                            ->where('index', $idx)
                            ->where('islock', false)
                            ->update([
                                'verify_status' => 1,
                                'verify_date'   => now(),
                                'verify_by'     => Auth::id(),
                                'islock'        => true,
                                'verify_note'   => $request->input("note_surat_jalan.{$idx}", ''),
                            ]);
                    }

                    // Lock group PROFORMA INVOICE
                    $checkProforma = (int) ($request->input("check_proforma_invoice.{$idx}", 0));
                    if ($checkProforma == 1 && $pc->is_form_proforma == 1) {
                        PaymentCompletionDetail::where('pc_id', $pc->id)
                            ->whereIn('component', ['proforma_invoice', 'nilai_proforma_invoice', 'file_proforma_invoice'])
                            ->where('index', $idx)
                            ->where('islock', false)
                            ->update([
                                'verify_status' => 1,
                                'verify_date'   => now(),
                                'verify_by'     => Auth::id(),
                                'islock'        => true,
                                'verify_note'   => $request->input("note_proforma_invoice.{$idx}", ''),
                            ]);
                    }
                }
            }

            // ── CBD/COD/DP (multi-row, lock per index) ──
            else {

                $indexes = array_keys($request->input('index', []));

                foreach ($indexes as $idx) {
                    $idx = (int) $idx;

                    // INVOICE group
                    $checkInvoice = (int) ($request->input("check_invoice.{$idx}", 0));
                    if ($checkInvoice == 1) {
                        PaymentCompletionDetail::where('pc_id', $pc->id)
                            ->whereIn('component', ['invoice', 'nilai_invoice', 'file_invoice'])
                            ->where('index', $idx)
                            ->where('islock', false)
                            ->update([
                                'verify_status' => 1,
                                'verify_date'   => now(),
                                'verify_by'     => Auth::id(),
                                'islock'        => true,
                                'verify_note'   => $request->input("invoice_notes.{$idx}", ''),
                            ]);
                    }

                    // FAKTUR PAJAK group
                    $checkFaktur = (int) ($request->input("check_faktur_pajak.{$idx}", 0));
                    if ($checkFaktur == 1 && $pc->is_form_faktur == 1) {
                        PaymentCompletionDetail::where('pc_id', $pc->id)
                            ->whereIn('component', ['faktur_pajak', 'file_faktur_pajak'])
                            ->where('index', $idx)
                            ->where('islock', false)
                            ->update([
                                'verify_status' => 1,
                                'verify_date'   => now(),
                                'verify_by'     => Auth::id(),
                                'islock'        => true,
                                'verify_note'   => $request->input("faktur_pajak_notes.{$idx}", ''),
                            ]);
                    }

                    // PROFORMA INVOICE group
                    $checkProforma = (int) ($request->input("check_proforma_invoice.{$idx}", 0));
                    if ($checkProforma == 1 && $pc->is_form_proforma == 1) {
                        PaymentCompletionDetail::where('pc_id', $pc->id)
                            ->whereIn('component', ['proforma_invoice', 'nilai_proforma_invoice', 'file_proforma_invoice'])
                            ->where('index', $idx)
                            ->where('islock', false)
                            ->update([
                                'verify_status' => 1,
                                'verify_date'   => now(),
                                'verify_by'     => Auth::id(),
                                'islock'        => true,
                                'verify_note'   => $request->input("proforma_notes.{$idx}", ''),
                            ]);
                    }
                }

                // Lock TGL JATUH TEMPO per index
                if ($request->has('check_tgl_jatuh_tempo')) {
                    foreach ($request->input('check_tgl_jatuh_tempo') as $index => $val) {
                        if ((int)$val != 1) continue;

                        PaymentCompletionDetail::where('pc_id', $pc->id)
                            ->where('component', 'tgl_jatuh_tempo')
                            ->where('index', $index)
                            ->where('islock', false)
                            ->update([
                                'verify_status' => 1,
                                'verify_date'   => now(),
                                'verify_by'     => Auth::id(),
                                'islock'        => true,
                                'verify_note'   => $request->input("tgl_jatuh_tempo_notes.{$index}", ''),
                            ]);
                    }
                }

                // Auto-lock detail_notes jika semua komponen yang ada sudah locked
                // Komponen wajib: invoice, tgl_jatuh_tempo
                // Komponen kondisional: faktur_pajak (is_form_faktur), proforma_invoice (is_form_proforma)
                $compToCheck = ['invoice', 'tgl_jatuh_tempo'];
                if ($pc->is_form_faktur == 1)   $compToCheck[] = 'faktur_pajak';
                if ($pc->is_form_proforma == 1) $compToCheck[] = 'proforma_invoice';

                $detailsCheck = PaymentCompletionDetail::where('pc_id', $pc->id)
                    ->select('component', 'index', 'islock')
                    ->whereIn('component', $compToCheck)
                    ->orderBy('index')
                    ->get();

                // Kumpulkan status islock per index per component
                $lockStatus = [];
                foreach ($detailsCheck as $d) {
                    $compKey = is_object($d->component) ? $d->component->value : (string) $d->component;
                    $lockStatus[$d->index][$compKey] = (bool) $d->islock;
                }

                // Index yang semua komponen-nya sudah locked → auto-lock detail_notes
                $indexesToUpdate = [];
                foreach ($lockStatus as $index => $comps) {
                    $allComp = true;
                    foreach ($compToCheck as $c) {
                        if (!($comps[$c] ?? false)) {
                            $allComp = false;
                            break;
                        }
                    }
                    if ($allComp) $indexesToUpdate[] = $index;
                }

                if (!empty($indexesToUpdate)) {
                    PaymentCompletionDetail::where('pc_id', $pc->id)
                        ->where('component', 'detail_notes')
                        ->where('islock', false)
                        ->whereIn('index', $indexesToUpdate)
                        ->update([
                            'verify_status' => 1,
                            'verify_date'   => now(),
                            'verify_by'     => Auth::id(),
                            'islock'        => true,
                        ]);
                }
            }

            DB::table('payment_completion_histories')->insert([
                'pc_id'      => $pc->id,
                'type'       => 'verified',
                'message'    => Auth::user()->name . ' verifikasi kelengkapan data dokumen PC',
                'created_by' => Auth::id(),
                'created_at' => now(),
            ]);

            DB::commit();

            return back()->with('success', 'Payment Completion Successfully Locked');

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal lock: ' . $e->getMessage());
        }
    }

    // =========================================================
    // DONE — set status = 2, lock semua detail
    // =========================================================

    public function done($id)
    {
        $decoded = Hashids::decode($id);
        $id = $decoded[0] ?? null;
        if (!$id) return redirect()->back()->with('error', 'ID tidak valid');

        if (!DB::table('payment_completions')->where('id', $id)->exists()) {
            return redirect()->back()->with('error', 'Data Tidak Ada');
        }

        DB::table('payment_completions')->where('id', $id)->update([
            'status'     => 2,
            'updated_at' => now(),
        ]);

        DB::table('payment_completion_details')->where('pc_id', $id)->update([
            'verify_status' => 1,
            'islock'        => true,
        ]);

        $doc_no = DB::table('payment_completions')->where('id', $id)->value('doc_no');

        DB::table('payment_completion_histories')->insert([
            'pc_id'      => $id,
            'type'       => 'done',
            'message'    => Auth::user()->name . ' set selesai dokumen PC',
            'created_by' => Auth::id(),
            'created_at' => now(),
        ]);

        return redirect()->back()->with('success', "Payment Completion {$doc_no} Changed Done");
    }
}
