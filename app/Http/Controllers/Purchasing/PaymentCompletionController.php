<?php

namespace App\Http\Controllers\Purchasing;

use App\Enums\Component;
use App\User;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use App\Traits\UploadTrait;
use Vinkla\Hashids\Facades\Hashids;
use App\Mail\SendMailable;
use App\Models\Notification;
use App\Models\PaymentCompletion;
use App\Models\PaymentCompletionDetail;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PaymentCompletionController extends Controller
{
    use UploadTrait;

    public function __construct()
    {
        $this->middleware('permission:payment_completion', ['only' => [
            'index', 'datatables', 'create', 'store', 'publish', 'show',
            'cancel', 'edit', 'update', 'print', 'printMultiple', 'list', 'list_datatables',
        ]]);
        $this->middleware('permission:approval_pc', ['only' => ['verifyPage', 'toggleVerify', 'bulkVerify']]);
    }

    // =========================================================
    // PRIVATE HELPERS
    // =========================================================

    /**
     * Upsert detail lama (tanpa index) — untuk komponen single/header.
     */
    private function upsertDetail(
        int $pcId,
        Component $component,
        ?string $valueText = null,
        ?string $valueDate = null,
        ?float $valueNumber = null,
        ?int $valueInteger = null
    ): void {
        $row = PaymentCompletionDetail::where('pc_id', $pcId)
            ->where('component', $component->value)
            ->first();

        $payload = [
            'pc_id'         => $pcId,
            'component'     => $component,
            'value_text'    => $valueText,
            'value_date'    => $valueDate,
            'value_number'  => $valueNumber,
            'value_integer' => $valueInteger,
        ];

        $row ? $row->update($payload) : PaymentCompletionDetail::create($payload);
    }

    /**
     * Upsert detail dengan index — dipakai untuk semua row multi (TEMPO & CBD).
     */
    private function newUpsertDetail(
        int $pcId,
        Component $component,
        ?string $valueText = null,
        ?string $valueDate = null,
        ?float $valueNumber = null,
        ?int $valueInteger = null,
        int $index = 0
    ): void {
        PaymentCompletionDetail::updateOrCreate(
            [
                'pc_id'     => $pcId,
                'component' => $component->value,
                'index'     => $index,
            ],
            [
                'pc_id'         => $pcId,
                'component'     => $component->value,
                'index'         => $index,
                'value_text'    => $valueText,
                'value_date'    => $valueDate,
                'value_number'  => $valueNumber,
                'value_integer' => $valueInteger,
                'created_by'    => Auth::id(),
            ]
        );
    }

    /**
     * Generate Base No SI per PC (hanya sekali per PC).
     * Format base: SI-{ALIAS}-{mmyy}-{00001}
     * Format per row: SI-{ALIAS}-{mmyy}-{00001}-{001}
     *
     * Cek existing hanya berdasarkan bagian nnnnn (bukan suffix -001).
     */
    private function generateBaseNoSi(int $poId): string
    {
        $row = DB::table('po')
            ->join('companies', 'companies.id', '=', 'po.company_id')
            ->where('po.id', $poId)
            ->select('companies.alias')
            ->first();

        if (!$row || empty($row->alias)) {
            throw new \RuntimeException('Company Not Found');
        }

        $alias   = strtoupper($row->alias);
        $periode = Carbon::now()->format('my');
        $prefix  = "SI-{$alias}-{$periode}-";

        // Ambil existing, abaikan suffix -001, -002, dst
        // Format: SI-ALIAS-mmyy-00001-001 → parts[3] = 00001
        $existing = PaymentCompletionDetail::query()
            ->where('component', Component::NO_SI->value)
            ->where('value_text', 'LIKE', $prefix . '%')
            ->whereYear('created_at', date('Y'))
            ->pluck('value_text');

        $maxSeq = 0;
        foreach ($existing as $doc) {
            $parts = explode('-', $doc);
            // SI=0, ALIAS=1, mmyy=2, nnnnn=3, rrr=4
            $num = isset($parts[3]) ? (int) $parts[3] : 0;
            if ($num > $maxSeq) {
                $maxSeq = $num;
            }
        }

        return $prefix . str_pad($maxSeq + 1, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Buat No SI lengkap dengan suffix urutan row dalam PC.
     * Format: SI-{ALIAS}-{mmyy}-{00001}-{001}
     *
     * @param string $baseSi   hasil generateBaseNoSi()
     * @param int    $rowOrder urutan row dalam PC ini (1-based)
     */
    private function makeNoSi(string $baseSi, int $rowOrder): string
    {
        return $baseSi . '-' . str_pad($rowOrder, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Generate No PC.
     * Format: PC-{ALIAS}-{mmyy}-{00001}
     */
    private function generateNoPc(int $poId): string
    {
        $company = DB::table('po')
            ->join('companies', 'companies.id', '=', 'po.company_id')
            ->select('companies.alias', 'companies.id')
            ->where('po.id', $poId)
            ->first();

        if (!$company || empty($company->alias)) {
            throw new \RuntimeException('Company Not Found');
        }

        $alias     = strtoupper($company->alias);
        $increment = DB::table('payment_completions')
            ->leftJoin('po', 'po.id', '=', 'payment_completions.po_id')
            ->whereYear('payment_completions.created_at', date('Y'))
            ->where('po.company_id', $company->id)
            ->count();

        $num = sprintf("%'.05d", $increment + 1);

        return "PC-{$alias}-" . date('my') . "-{$num}";
    }

    /**
     * Helper terpusat hitung verify_progress untuk 1 PC.
     * TEMPO : per row × (invoice + faktur? + surat_jalan)
     * CBD   : per row × (invoice + faktur?)
     *
     * @return array ['verified' => int, 'total' => int]
     */
    public static function calcVerifyProgress(object $r): array
    {
        $pcd = DB::table('payment_completion_details')
            ->where('pc_id', $r->id)
            ->get();

        $verified = 0;
        $total    = 0;

        // Ambil semua index berdasarkan component invoice
        $indexes = $pcd->whereIn('component', ['invoice'])->pluck('index')->unique();

        foreach ($indexes as $idx) {
            $rowData = $pcd->where('index', $idx);

            // invoice — selalu ada
            $total++;
            if ($rowData->where('component', 'invoice')->where('verify_status', 1)->count()) {
                $verified++;
            }

            // faktur_pajak — hanya jika ada di DB
            if ($rowData->where('component', 'faktur_pajak')->count()) {
                $total++;
                if ($rowData->where('component', 'faktur_pajak')->where('verify_status', 1)->count()) {
                    $verified++;
                }
            }

            // tgl_surat_jalan — hanya untuk TEMPO
            if ($r->type_payment == 1) {
                if ($rowData->where('component', 'tgl_surat_jalan')->count()) {
                    $total++;
                    if ($rowData->where('component', 'tgl_surat_jalan')->where('verify_status', 1)->count()) {
                        $verified++;
                    }
                }
            }
        }

        return compact('verified', 'total');
    }

    // =========================================================
    // INDEX
    // =========================================================

    public function index()
    {
        $allpayment = PaymentCompletion::count();
        $payment1   = PaymentCompletion::where('type_payment', 1)->count();
        $payment2   = PaymentCompletion::where('type_payment', 2)->count();
        $companies = DB::table('companies')->orderBy('name')->get();

        // Count per status
        $statusCounts = PaymentCompletion::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');
        $countNeedChangePo = PaymentCompletion::whereNotIn('status', [3, 5])
            ->where('status_changed_relation', 0)
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('po')
                    ->whereColumn('po.id', 'payment_completions.po_id')
                    ->where('po.status', 8);
            })
            ->count();

        $countDraft     = $statusCounts[0] ?? 0;
        $countProgress  = $statusCounts[1] ?? 0;
        $countDone      = $statusCounts[2] ?? 0;
        $countRejected  = $statusCounts[3] ?? 0;
        $countPending   = $statusCounts[4] ?? 0;
        $countPoChanged = $statusCounts[5] ?? 0;

        return view('purchase.payment_completion.index', compact(
            'allpayment', 'payment1', 'payment2',
            'countDraft', 'countProgress', 'countDone',
            'countRejected', 'countPending', 'countPoChanged','countNeedChangePo','companies'
        ));
    }

    // =========================================================
    // DATATABLES
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
                'po.id as po_id',
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
            ->orderByRaw("
                CASE
                    WHEN payment_completions.status = 4 THEN 1
                    WHEN payment_completions.status = 0 THEN 2
                    WHEN payment_completions.status = 1 THEN 3
                    WHEN payment_completions.status = 2 THEN 4
                    WHEN payment_completions.status = 3 THEN 5
                    WHEN payment_completions.status = 5 THEN 6
                    ELSE 0
                END
            ")
            ->orderByDesc('payment_completions.id');

        if ($request->filled('type_payment') && $request->type_payment != '0') {
            $q->where('payment_completions.type_payment', $request->type_payment);
        }
        if ($request->filled('status') && $request->status != 'NULL') {
            $q->where('payment_completions.status', $request->status);
        }
        if ($request->filled('need_change_po') && $request->need_change_po == '1') {
            $q->whereNotIn('payment_completions.status', [3, 5])
            ->where('payment_completions.status_changed_relation', 0)
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('po')
                    ->whereColumn('po.id', 'payment_completions.po_id')
                    ->where('po.status', 8);
            });
        }

        return DataTables::of($q)
            ->editColumn('no_po', function ($r) {
                return "<a target='_blank' href='" . route('purchasing.po.show', Hashids::encode($r->id_po)) . "' title='Detail PO' data-toggle='tooltip'>{$r->no_po}</a>";
            })
            ->addColumn('verify_progress', function ($r) {
                $prog = self::calcVerifyProgress($r);
                if ($prog['total'] === 0) return '-';

                $verified = $prog['verified'];
                $total    = $prog['total'];
                $pct      = round(($verified / $total) * 100);

                if ($pct == 100) {
                    $color = 'success';
                } elseif ($pct >= 50) {
                    $color = 'info';
                } elseif ($pct > 0) {
                    $color = 'warning';
                } else {
                    $color = 'danger';
                }

                return '
                    <div style="min-width:100px;">
                        <div class="d-flex justify-content-between mb-1">
                            <small>' . $verified . ' / ' . $total . '</small>
                            <small>' . $pct . '%</small>
                        </div>
                        <div class="progress" style="height:8px; border-radius:4px;">
                            <div class="progress-bar bg-' . $color . '"
                                role="progressbar"
                                style="width:' . $pct . '%;"
                                aria-valuenow="' . $pct . '"
                                aria-valuemin="0"
                                aria-valuemax="100">
                            </div>
                        </div>
                    </div>';
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
            ->addColumn('jumlah', function ($r) {
                return is_null($r->jumlah ?? null) ? '' : number_format($r->jumlah, 2);
            })
            ->addColumn('action', function ($r) {
                $urlShow    = route('purchasing.payment_completion.show', Hashids::encode($r->id));
                $urlEdit    = route('purchasing.payment_completion.edit', Hashids::encode($r->id));
                $urlPrint   = route('purchasing.payment_completion.print', Hashids::encode($r->id));
                $urlPublish = route('purchasing.payment_completion.publish', Hashids::encode($r->id));
                $urlDraft   = route('purchasing.payment_completion.draft', Hashids::encode($r->id));
                $urlReject  = route('purchasing.payment_completion.reject', Hashids::encode($r->id));
                $urlDone    = route('approval.verify_pc.done', Hashids::encode($r->id));

                $btnShow = '<a class="btn btn-outline" href="' . $urlShow . '" title="View" data-toggle="tooltip">
                                <i class="ti-eye icon-lg"></i>
                            </a>';
                $btnEdit = '<a class="btn btn-outline" href="' . $urlEdit . '" title="Edit" data-toggle="tooltip">
                                <i class="ti-pencil-alt icon-lg"></i>
                            </a>';
                $btnPrint = '<a class="btn btn-outline text-dark" href="' . $urlPrint . '" target="_blank" title="Print" data-toggle="tooltip">
                                <i class="ti-printer icon-lg"></i>
                            </a>';
                $btnReject = '<button type="button" class="btn btn-outline text-danger btn-reject"
                                data-url="' . $urlReject . '"
                                data-doc="' . e($r->doc_no) . '"
                                data-id="' . Hashids::encode($r->id) . '"
                                title="Reject" data-toggle="tooltip">
                                <i class="ti-power-off icon-lg"></i>
                            </button>';
                $btnPublish = '<button type="button" class="btn btn-outline text-success btn-publish"
                                data-url="' . $urlPublish . '"
                                data-doc="' . e($r->doc_no) . '"
                                title="Publish" data-toggle="tooltip">
                                <i class="ti-new-window icon-lg"></i>
                            </button>';
                $btnDraft = '<button type="button" class="btn btn-outline text-danger btn-cancel"
                                data-url="' . $urlDraft . '"
                                data-doc="' . e($r->doc_no) . '"
                                title="Draft" data-toggle="tooltip">
                                <i class="ti-download icon-lg"></i>
                            </button>';

                // Tombol Change PO
                $btnChangePo = '';
                $checkPo = DB::table('po')->where('id', $r->po_id)->whereIn('status', [8])->exists();
                if ($checkPo && $r->status != 5 && $r->status != 3 && $r->status_changed_relation == 0) {
                    $urlChangePo = route('purchasing.payment_completion.change_relation_po', Hashids::encode($r->id));
                    $btnChangePo = '<button type="button" class="btn btn-outline text-danger btn-change-po"
                        data-url="' . $urlChangePo . '"
                        data-po-id="' . Hashids::encode($r->po_id) . '"
                        data-po-no="' . e($r->no_po) . '"
                        title="Change Relation PO">
                        <i class="ti-reload icon-sm"></i>
                    </button>';
                }

                // Tombol Set Done — hanya untuk status=1 (OnProgress), semua locked, total invoice >= total PO
                $btnDone = '';
                if (($r->status == 1 || $r->status == 4) && Gate::allows('payment_completion_admin')) {
                    $allDetailLocked = $r->total_detail > 0 && $r->total_detail == $r->verified_detail;
                    if ($allDetailLocked) {
                        $totalNilaiInvoice = DB::table('payment_completion_details')
                            ->where('pc_id', $r->id)
                            ->where('component', 'nilai_invoice')
                            ->sum('value_number');

                        $poData   = DB::table('po')->where('id', $r->po_id)->first();
                        $poItems  = \App\Models\PurchaseOrder::getProductItem($r->po_id);
                        $poTotal  = 0;
                        foreach ($poItems as $item) {
                            $poTotal += $item->price * $item->qty - (($item->price * $item->qty) * $item->discount / 100);
                        }
                        if ($poData && !$poData->discount_item) {
                            $discAmt = $poData->discount_type == 1
                                ? $poTotal * ((float)$poData->discount_amount / 100)
                                : (float)$poData->discount_amount;
                            $netto = $poTotal - $discAmt;
                        } else {
                            $netto = $poTotal;
                        }
                        if ($poData && ((float)$poData->send_expense_ppn == 1 || (float)$poData->send_expense_ppn == 11)) {
                            $poData->send_expense += (11 / 100) * (float)$poData->send_expense;
                        }
                        $ppn           = $poData ? $netto * (float)$poData->ppn / 100 : 0;
                        $pph           = $poData ? $netto * (float)$poData->pph / 100 : 0;
                        $sendExpense   = $poData ? (float)$poData->send_expense : 0;
                        $paymentAmount = $netto - $pph + $ppn + $sendExpense;

                        $urlDoneAction = route('purchasing.payment_completion.done', Hashids::encode($r->id));

                        if ($totalNilaiInvoice >= $paymentAmount) {
                            $btnDone = '<button type="button" class="btn btn-outline text-success btn-done-index"
                                data-url="' . $urlDoneAction . '"
                                data-doc="' . e($r->doc_no) . '"
                                title="Set Done" data-toggle="tooltip">
                                <i class="ti-thumb-up icon-lg"></i>
                            </button>';
                        } else {
                            $btnDone = '<span class="badge badge-warning" style="padding:5px 7px;"
                                title="Total Invoice belum mencapai Total Harga PO" data-toggle="tooltip">
                                <i class="ti-alert"></i>
                            </span>';
                        }
                    }
                }

                if (!Gate::allows('payment_completion_admin')) {
                    return $btnShow;
                }

                if ($r->status == 0) {
                    return $btnShow . $btnEdit . $btnPublish . $btnReject . $btnPrint . $btnChangePo;
                } elseif ($r->status == 1) {
                    return $btnShow . $btnDone . $btnPrint . $btnChangePo;
                } elseif ($r->status == 2) {
                    return $btnShow . $btnPrint . $btnChangePo;
                } elseif ($r->status == 4) {
                    return $btnShow . $btnEdit . $btnPublish . $btnDone . $btnReject . $btnPrint . $btnChangePo;
                } else {
                    return $btnShow . $btnChangePo;
                }
            })
            ->rawColumns(['action', 'type_payment', 'status', 'verify_progress', 'no_po'])
            ->make(true);
    }

    // =========================================================
    // CREATE
    // =========================================================

    public function create(Request $request)
    {
        if (!Gate::allows('payment_completion_admin')) {
            return redirect()->back()->with(['error' => 'Anda tidak memiliki izin akses']);
        }

        $po_id = Hashids::decode($request->po);
        if (!$po_id) {
            return redirect()->route('purchase.payment_completion')->with('error', 'PO Tidak Ada');
        }

        $list_po = DB::table('po')
            ->leftJoin('companies', 'companies.id', '=', 'po.company_id')
            ->leftJoin('suppliers', 'suppliers.id', '=', 'po.supplier_id')
            ->leftJoin('payment_terms', 'payment_terms.id', '=', 'po.payment_term_id')
            ->leftJoin('purchase_requisitions', 'purchase_requisitions.id', '=', 'po.purchase_id')
            ->where('po.id', $po_id[0])
            ->select(
                'po.*',
                'po.payment_amount AS harga_po',
                'companies.name AS company_nama',
                'suppliers.name AS supplier_nama',
                'purchase_requisitions.doc_no AS no_pr',
                'purchase_requisitions.created_at as tgl_pr',
                'payment_terms.name AS payment_term_name',
                'payment_terms.type_body_email AS type_body_email_payment'
            )
            ->first();

        $currency = DB::table('po')->where('id', $po_id[0])->value('currency');

        return view('purchase.payment_completion.create', [
            'list_po'  => $list_po,
            'po_id'    => $po_id[0],
            'currency' => $currency,
        ]);
    }

    // =========================================================
    // STORE
    // =========================================================

    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $data = $request->validate([
                'po_id'            => ['required', 'integer', 'exists:po,id'],
                'doc_no'           => ['nullable', 'string', 'max:255'],
                'type_payment'     => ['required', Rule::in([1, 2])],
                'is_form_faktur'   => ['required', 'integer', Rule::in([0, 1])],
                'is_form_proforma' => ['required', 'integer', Rule::in([0, 1])],
            ]);

            $docNo = trim($data['doc_no'] ?? '');
            if (!$docNo) {
                $docNo = $this->generateNoPc($data['po_id']);
            }

            $userId   = Auth::id() ?? 1;
            $userName = Auth::user()?->name ?? 'System';

            $pcn = PaymentCompletion::create([
                'po_id'            => $data['po_id'],
                'doc_no'           => $docNo,
                'type_payment'     => $data['type_payment'],
                'is_form_faktur'   => $data['is_form_faktur'],
                'is_form_proforma' => $data['is_form_proforma'],
                'created_by'       => $userId,
                'status'           => 0,
            ]);

            // ── TEMPO: buat 1 row awal (index=0) ──
            if ($pcn->type_payment == 1) {
                $baseSi = $this->generateBaseNoSi($pcn->po_id);
                $noSi   = $this->makeNoSi($baseSi, 1); // Row pertama = -001

                $this->newUpsertDetail($pcn->id, Component::NO_SI,             valueText:    $noSi, index: 0);
                $this->newUpsertDetail($pcn->id, Component::INVOICE,            valueText:    null,  index: 0);
                $this->newUpsertDetail($pcn->id, Component::NILAI_INVOICE,      valueNumber:  null,  index: 0);
                $this->newUpsertDetail($pcn->id, Component::TGL_TERIMA_INVOICE, valueDate:    null,  index: 0);
                $this->newUpsertDetail($pcn->id, Component::TGL_INVOICE,        valueDate:    null,  index: 0);
                $this->newUpsertDetail($pcn->id, Component::FILE_INVOICE,       valueText:    null,  index: 0);
                $this->newUpsertDetail($pcn->id, Component::PERIODE_TEMPO,      valueInteger: null,  index: 0);
                $this->newUpsertDetail($pcn->id, Component::TGL_JATUH_TEMPO,    valueDate:    null,  index: 0);
                $this->newUpsertDetail($pcn->id, Component::TGL_SURAT_JALAN,    valueDate:    null,  index: 0);
                $this->newUpsertDetail($pcn->id, Component::DETAIL_NOTES,       valueText:    null,  index: 0);

                if ($pcn->is_form_faktur == 1) {
                    $this->newUpsertDetail($pcn->id, Component::FAKTUR_PAJAK,      valueText: null, index: 0);
                    $this->newUpsertDetail($pcn->id, Component::FILE_FAKTUR_PAJAK, valueText: null, index: 0);
                }
                if ($pcn->is_form_proforma == 1) {
                    $this->newUpsertDetail($pcn->id, Component::PROFORMA_INVOICE,       valueText:   null, index: 0);
                    $this->newUpsertDetail($pcn->id, Component::NILAI_PROFORMA_INVOICE,  valueNumber: null, index: 0);
                    $this->newUpsertDetail($pcn->id, Component::FILE_PROFORMA_INVOICE,   valueText:   null, index: 0);
                }
            }
            // ── CBD/COD/DP: buat 1 row awal (index=0) ──
            else {
                $comps = [];
                $comps[] = [Component::INVOICE,        'value_text'];
                $comps[] = [Component::NILAI_INVOICE,  'value_number'];
                $comps[] = [Component::TGL_JATUH_TEMPO,'value_date'];
                $comps[] = [Component::FILE_INVOICE,   'value_text'];
                $comps[] = [Component::DETAIL_NOTES,   'value_text'];
                if ($pcn->is_form_faktur == 1) {
                    $comps[] = [Component::FAKTUR_PAJAK,      'value_text'];
                    $comps[] = [Component::FILE_FAKTUR_PAJAK, 'value_text'];
                }
                if ($pcn->is_form_proforma == 1) {
                    $comps[] = [Component::PROFORMA_INVOICE,      'value_text'];
                    $comps[] = [Component::NILAI_PROFORMA_INVOICE, 'value_number'];
                    $comps[] = [Component::FILE_PROFORMA_INVOICE,  'value_text'];
                }

                foreach ($comps as [$component, $valueField]) {
                    PaymentCompletionDetail::create([
                        'pc_id'      => $pcn->id,
                        'component'  => $component->value,
                        $valueField  => null,
                        'index'      => 0,
                        'created_by' => $userId,
                    ]);
                }
            }

            DB::table('payment_completion_histories')->insert([
                'pc_id'      => $pcn->id,
                'type'       => 'created',
                'message'    => $userName . ' membuat dokumen PC',
                'created_by' => $userId,
                'created_at' => now(),
            ]);

            DB::commit();

            return redirect()
                ->route('purchasing.payment_completion.edit', Hashids::encode($pcn->id))
                ->with('success', 'Payment Completion Created Successfully. Please Fill in the Details');

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    // =========================================================
    // SHOW
    // =========================================================

     public function show($id)
    {
        $decoded = Hashids::decode($id);
        $id = $decoded[0] ?? null;
        if (!$id) {
            abort(404, 'ID Payment Completion Not Valid');
        }
 
        $payment_completion = DB::table('payment_completions as pc')
            ->leftJoin('po', 'pc.po_id', '=', 'po.id')
            ->leftJoin('users as u', 'pc.created_by', '=', 'u.id')
            ->leftJoin('companies as c', 'po.company_id', '=', 'c.id')
            ->leftJoin('suppliers as s', 'po.supplier_id', '=', 's.id')
            ->leftJoin('purchase_requisitions as pr', 'pr.id', '=', 'po.purchase_id')
            ->leftJoin('purchases as pu', 'pr.purchase_id', '=', 'pu.id')
            ->leftJoin('payment_terms as pt', 'pt.id', '=', 'po.payment_term_id')
            ->select(
                'pc.*',
                'pc.doc_no as no_pc',
                'pc.created_at as tgl_pc',
                DB::raw('pc.status as status_pc'),
                'po.doc_no as no_po',
                'po.id as po_id',
                'po.created_at as tgl_po',
                'po.currency as po_mata_uang',
                'po.payment_amount AS harga_po',
                'u.name as nama_pembuat',
                'c.name as nama_company',
                's.name as nama_supplier',
                'pr.doc_no as no_pr',
                'pr.created_at as tgl_pr',
                'pt.name as payment_terms_nama'
            )
            ->where('pc.id', $id)
            ->first();
 
        if (!$payment_completion) {
            abort(404, 'Payment Completion Not Available');
        }
 
        $details = DB::table('payment_completion_details as d')
            ->leftJoin('users as vu', 'vu.id', '=', 'd.verify_by')
            ->select('d.*', 'vu.name as verify_user')
            ->where('d.pc_id', $id)
            ->orderBy('d.index')
            ->orderBy('d.id')
            ->get();
 
        // Susun components per index per component key
        $components = [];
        foreach ($details as $d) {
            $key = is_object($d->component) ? $d->component->value : (string) $d->component;
            $val = $d->value_number ?? $d->value_date ?? $d->value_text ?? $d->value_integer;
            $components[$d->index][$key] = [
                'id'     => $d->id,
                'value'  => $val,
                'islock' => (bool) $d->islock,
            ];
        }
        ksort($components);
 
        // verifyMeta per index per component
        $verifyMeta = [];
        foreach ($details as $d) {
            $key = is_object($d->component) ? $d->component->value : (string) $d->component;
            $verifyMeta[$d->index][$key] = [
                'status' => $d->verify_status,
                'date'   => $d->verify_date,
                'user'   => $d->verify_user,
            ];
        }
 
        // Pairs untuk CBD/COD/DP
        $pairs = [];
        if ((int) $payment_completion->type_payment !== 1) {
            $invoiceRows      = $details->where('component', Component::INVOICE->value)->keyBy('index');
            $fakturRows       = $details->where('component', Component::FAKTUR_PAJAK->value)->keyBy('index');
            $nilaiInvRows     = $details->where('component', Component::NILAI_INVOICE->value)->keyBy('index');
            $fileInvRows      = $details->where('component', Component::FILE_INVOICE->value)->keyBy('index');
            $fileFpRows       = $details->where('component', Component::FILE_FAKTUR_PAJAK->value)->keyBy('index');
            $tglJatuhRows     = $details->where('component', Component::TGL_JATUH_TEMPO->value)->keyBy('index');
            $notesRows        = $details->where('component', Component::DETAIL_NOTES->value)->keyBy('index');
            $proformaRows     = $details->where('component', 'proforma_invoice')->keyBy('index');
            $nilProformaRows  = $details->where('component', 'nilai_proforma_invoice')->keyBy('index');
            $fileProformaRows = $details->where('component', 'file_proforma_invoice')->keyBy('index');
 
            // Kumpulkan semua index yang ada termasuk proforma
            $allIndexes = collect([
                $invoiceRows->keys(),
                $fakturRows->keys(),
                $nilaiInvRows->keys(),
                $fileInvRows->keys(),
                $fileFpRows->keys(),
                $tglJatuhRows->keys(),
                $notesRows->keys(),
                $proformaRows->keys(),
                $nilProformaRows->keys(),
                $fileProformaRows->keys(),
            ])->flatten()->unique()->sort()->values();
 
            foreach ($allIndexes as $index) {
                $invRow      = $invoiceRows[$index] ?? null;
                $fakRow      = $fakturRows[$index] ?? null;
                $nilRow      = $nilaiInvRows[$index] ?? null;
                $fInvRow     = $fileInvRows[$index] ?? null;
                $fFpRow      = $fileFpRows[$index] ?? null;
                $proformaRow = $proformaRows[$index] ?? null;
 
                $pairs[] = [
                    'index'                         => $index,
                    'invoice'                       => $invRow?->value_text,
                    'nilai_invoice'                 => $nilRow?->value_number,
                    'faktur_pajak'                  => $fakRow?->value_text,
                    'file_invoice'                  => $fInvRow?->value_text,
                    'file_faktur_pajak'             => $fFpRow?->value_text,
                    'tgl_jatuh_tempo'               => $tglJatuhRows[$index]?->value_date ?? null,
                    'detail_notes'                  => $notesRows[$index]?->value_text ?? null,
                    'proforma_invoice'              => $proformaRow?->value_text,
                    'nilai_proforma_invoice'        => $nilProformaRows[$index]?->value_number ?? null,
                    'file_proforma_invoice'         => $fileProformaRows[$index]?->value_text ?? null,
                    'invoice_verify_status'         => $invRow?->verify_status,
                    'invoice_verify_user'           => $invRow?->verify_user ?? '-',
                    'invoice_verify_date'           => $invRow?->verify_date,
                    'invoice_verify_note'           => $invRow?->verify_note ?? '',
                    'faktur_verify_status'          => $fakRow?->verify_status,
                    'faktur_verify_user'            => $fakRow?->verify_user ?? '-',
                    'faktur_verify_date'            => $fakRow?->verify_date,
                    'faktur_verify_note'            => $fakRow?->verify_note ?? '',
                    'proforma_verify_status'        => $proformaRow?->verify_status,
                    'proforma_verify_user'          => $proformaRow?->verify_user ?? '-',
                    'proforma_verify_date'          => $proformaRow?->verify_date,
                    'tgl_jatuh_tempo_verify_status' => $tglJatuhRows[$index]?->verify_status,
                    'tgl_jatuh_tempo_verify_user'   => $tglJatuhRows[$index]?->verify_user ?? '-',
                    'tgl_jatuh_tempo_verify_date'   => $tglJatuhRows[$index]?->verify_date,
                    'islock_invoice'                => (bool) ($invRow?->islock),
                    'islock_faktur_pajak'           => (bool) ($fakRow?->islock),
                    'islock_proforma_invoice'       => (bool) ($proformaRow?->islock),
                    'islock_tgl_jatuh_tempo'        => (bool) ($tglJatuhRows[$index]?->islock),
                ];
            }
        }
 
        $paymentDetails = DB::table('payment_completion_payment_details as ppd')
            ->leftJoin('users as u', 'u.id', '=', 'ppd.created_by')
            ->select('ppd.*', 'u.name as uploader_name')
            ->where('ppd.pc_id', $id)
            ->orderBy('ppd.index')
            ->orderBy('ppd.created_at')
            ->get()
            ->groupBy('index');
 
        $historyPc = DB::table('payment_completion_histories as h')
            ->leftJoin('users as u', 'u.id', '=', 'h.created_by')
            ->select('h.*', 'u.name as employee')
            ->where('h.pc_id', $id)
            ->orderBy('h.created_at', 'desc')
            ->get();
 
        $view = $payment_completion->type_payment == 1
            ? 'purchase.payment_completion.show_tempo'
            : 'purchase.payment_completion.show_cbd';
 
        return view($view, compact(
            'paymentDetails', 'payment_completion', 'components',
            'verifyMeta', 'pairs', 'historyPc'
        ));
    }

    // =========================================================
    // EDIT
    // =========================================================

    public function edit($id)
    {
        $decoded = Hashids::decode($id);
        $id = $decoded[0] ?? null;
        if (!$id) abort(404);

        $pc = DB::table('payment_completions as pc')
            ->leftJoin('po', 'pc.po_id', '=', 'po.id')
            ->leftJoin('users as u', 'pc.created_by', '=', 'u.id')
            ->leftJoin('companies as c', 'po.company_id', '=', 'c.id')
            ->leftJoin('suppliers as s', 'po.supplier_id', '=', 's.id')
            ->leftJoin('purchase_requisitions as pr', 'pr.id', '=', 'po.purchase_id')
            ->leftJoin('payment_terms as pt', 'pt.id', '=', 'po.payment_term_id')
            ->select(
                'pc.id',
                'pc.po_id',
                'pc.doc_no',
                'pc.doc_no as no_pc',
                'pc.type_payment',
                'pc.is_form_faktur',
                'pc.is_form_proforma',
                'pc.status',
                'pc.notes',
                'pc.created_at',
                'pc.updated_at',
                'pc.created_by',
                'pc.reject_reason',
                'pc.status_changed_relation',
                'po.doc_no as no_po',
                'po.created_at as tgl_po',
                'po.currency as po_mata_uang',
                'u.name as nama_pembuat',
                'c.name as nama_company',
                's.name as nama_supplier',
                'pr.doc_no as no_pr',
                'pr.created_at as tgl_pr',
                'pt.name as payment_terms_nama'
            )
            ->where('pc.id', $id)
            ->first();

        if (!$pc) abort(404);

        $po = getDataByID('po', $pc->po_id);

        // Ambil semua detail, susun per index → per component
        $rawDetails = DB::table('payment_completion_details')
            ->where('pc_id', $id)
            ->orderBy('index')
            ->orderBy('id')
            ->get();

        $components = [];
        foreach ($rawDetails as $d) {
            $key = is_object($d->component) ? $d->component->value : (string) $d->component;
            $val = $d->value_number ?? $d->value_date ?? $d->value_text ?? $d->value_integer;

            $components[$d->index][$key] = [
                'id'     => $d->id,
                'value'  => $val,
                'islock' => (bool) $d->islock,
            ];
        }

        if (empty($components)) {
            $components[0] = [];
        }

        ksort($components);

        $view = $pc->type_payment == 1
            ? 'purchase.payment_completion.edit_tempo'
            : 'purchase.payment_completion.edit_cbd';

        return view($view, compact('pc', 'po', 'components'));
    }

    // =========================================================
    // UPDATE
    // =========================================================

    public function update(Request $request, $id)
    {
        $decoded = Hashids::decode($id);
        $id = $decoded[0] ?? null;
        if (!$id) return redirect()->back()->with('error', 'ID tidak valid');

        $pc = PaymentCompletion::findOrFail($id);

        DB::beginTransaction();
        try {
            // Simpan notes header PC
            $pc->update(['notes' => $request->notes]);

            // ── TEMPO MULTI-ROW ──
            if ($pc->type_payment == 1) {
                $indexes = array_keys($request->input('invoice', []));

                foreach ($indexes as $idx) {
                    $idx = (int) $idx;

                    // Skip jika index ini sudah locked
                    $isLocked = PaymentCompletionDetail::where('pc_id', $pc->id)
                        ->where('index', $idx)
                        ->where('islock', false)
                        ->exists();

                    if (!$isLocked) continue;

                    // NO_SI: generate base SI sekali per PC, setiap row pakai suffix urutan (-001, -002, dst)
                    $existingNoSi = PaymentCompletionDetail::where('pc_id', $pc->id)
                        ->where('component', Component::NO_SI->value)
                        ->where('index', $idx)
                        ->value('value_text');

                    if (!$existingNoSi) {
                        // Ambil base SI dari row pertama jika sudah ada, kalau belum generate baru
                        $baseSi = PaymentCompletionDetail::where('pc_id', $pc->id)
                            ->where('component', Component::NO_SI->value)
                            ->orderBy('index')
                            ->value('value_text');

                        if ($baseSi) {
                            // Ambil bagian base (tanpa suffix -001) dari SI yang sudah ada
                            $parts  = explode('-', $baseSi);
                            // Hapus suffix terakhir (-001) untuk dapat base
                            array_pop($parts);
                            $baseSi = implode('-', $parts);
                        } else {
                            // Belum ada sama sekali, generate baru
                            $baseSi = $this->generateBaseNoSi($pc->po_id);
                        }

                        // Hitung urutan row ini (berapa banyak index yang sudah ada + 1)
                        $existingIndexCount = PaymentCompletionDetail::where('pc_id', $pc->id)
                            ->where('component', Component::NO_SI->value)
                            ->count();
                        $rowOrder = $existingIndexCount + 1;

                        $noSi = $this->makeNoSi($baseSi, $rowOrder);
                        $this->newUpsertDetail($pc->id, Component::NO_SI, valueText: $noSi, index: $idx);
                    }

                    // FILE INVOICE: upload baru atau pertahankan lama
                    if ($request->hasFile("file_invoice.{$idx}") && $request->file("file_invoice.{$idx}")->isValid()) {
                        $fileInvoicePath = $request->file("file_invoice.{$idx}")
                            ->store('payment_completion/invoice', 'public');
                    } else {
                        $fileInvoicePath = PaymentCompletionDetail::where('pc_id', $pc->id)
                            ->where('component', Component::FILE_INVOICE->value)
                            ->where('index', $idx)
                            ->value('value_text');
                    }

                    // FILE FAKTUR PAJAK: upload baru atau pertahankan lama
                    $fileFakturPath = null;
                    if ($pc->is_form_faktur == 1) {
                        if ($request->hasFile("file_faktur_pajak.{$idx}") && $request->file("file_faktur_pajak.{$idx}")->isValid()) {
                            $fileFakturPath = $request->file("file_faktur_pajak.{$idx}")
                                ->store('payment_completion/faktur_pajak', 'public');
                        } else {
                            $fileFakturPath = PaymentCompletionDetail::where('pc_id', $pc->id)
                                ->where('component', Component::FILE_FAKTUR_PAJAK->value)
                                ->where('index', $idx)
                                ->value('value_text');
                        }
                    }

                    // Hitung TGL_JATUH_TEMPO otomatis = tgl_terima_invoice + periode_tempo
                    $tglSuratJalan = $request->input("tgl_terima_invoice.{$idx}");
                    $periodeTempo  = (int) $request->input("periode_tempo.{$idx}", 0);
                    $tglJatuhTempo = null;
                    if ($tglSuratJalan && $periodeTempo > 0) {
                        $tglJatuhTempo = Carbon::parse($tglSuratJalan)->addDays($periodeTempo)->format('Y-m-d');
                    }

                    // Bersihkan nilai invoice dari format currency mask
                    $nilaiRaw     = str_replace(',', '', $request->input("nilai_invoice.{$idx}", ''));
                    $nilaiInvoice = is_numeric($nilaiRaw) ? (float) $nilaiRaw : null;

                    // Upsert semua komponen per index
                    $this->newUpsertDetail($pc->id, Component::INVOICE,            valueText:    $request->input("invoice.{$idx}"),            index: $idx);
                    $this->newUpsertDetail($pc->id, Component::NILAI_INVOICE,       valueNumber:  $nilaiInvoice,                                 index: $idx);
                    $this->newUpsertDetail($pc->id, Component::TGL_INVOICE,         valueDate:    $request->input("tgl_invoice.{$idx}"),         index: $idx);
                    $this->newUpsertDetail($pc->id, Component::TGL_TERIMA_INVOICE,  valueDate:    $request->input("tgl_terima_invoice.{$idx}"),  index: $idx);
                    $this->newUpsertDetail($pc->id, Component::FILE_INVOICE,        valueText:    $fileInvoicePath,                              index: $idx);
                    $this->newUpsertDetail($pc->id, Component::PERIODE_TEMPO,       valueInteger: $periodeTempo ?: null,                          index: $idx);
                    $this->newUpsertDetail($pc->id, Component::TGL_SURAT_JALAN,     valueDate:    $tglSuratJalan,                                 index: $idx);
                    $this->newUpsertDetail($pc->id, Component::TGL_JATUH_TEMPO,     valueDate:    $tglJatuhTempo,                                 index: $idx);
                    $this->newUpsertDetail($pc->id, Component::DETAIL_NOTES,        valueText:    $request->input("detail_notes.{$idx}"),         index: $idx);

                    if ($pc->is_form_faktur == 1) {
                        $this->newUpsertDetail($pc->id, Component::FAKTUR_PAJAK,      valueText: $request->input("faktur_pajak.{$idx}"), index: $idx);
                        $this->newUpsertDetail($pc->id, Component::FILE_FAKTUR_PAJAK, valueText: $fileFakturPath,                         index: $idx);
                    }

                    // FILE PROFORMA INVOICE: upload baru atau pertahankan lama
                    if ($pc->is_form_proforma == 1) {
                        if ($request->hasFile("file_proforma_invoice.{$idx}") && $request->file("file_proforma_invoice.{$idx}")->isValid()) {
                            $fileProformaPath = $request->file("file_proforma_invoice.{$idx}")
                                ->store('payment_completion/proforma_invoice', 'public');
                        } else {
                            $fileProformaPath = PaymentCompletionDetail::where('pc_id', $pc->id)
                                ->where('component', Component::FILE_PROFORMA_INVOICE->value)
                                ->where('index', $idx)
                                ->value('value_text');
                        }

                        $nilaiProformaRaw  = str_replace(',', '', $request->input("nilai_proforma_invoice.{$idx}", ''));
                        $nilaiProforma     = is_numeric($nilaiProformaRaw) ? (float) $nilaiProformaRaw : null;

                        $this->newUpsertDetail($pc->id, Component::PROFORMA_INVOICE,      valueText:   $request->input("proforma_invoice.{$idx}"), index: $idx);
                        $this->newUpsertDetail($pc->id, Component::NILAI_PROFORMA_INVOICE, valueNumber: $nilaiProforma,                              index: $idx);
                        $this->newUpsertDetail($pc->id, Component::FILE_PROFORMA_INVOICE,  valueText:   $fileProformaPath,                           index: $idx);
                    }
                }

                // Hapus index yang dihapus user (tidak ada di request & belum locked)
                $requestIndexes = array_map('intval', array_keys($request->input('invoice', [])));
                $dbIndexes = PaymentCompletionDetail::where('pc_id', $pc->id)
                    ->where('component', Component::INVOICE->value)
                    ->pluck('index')
                    ->toArray();

                foreach (array_diff($dbIndexes, $requestIndexes) as $delIdx) {
                    $anyLocked = PaymentCompletionDetail::where('pc_id', $pc->id)
                        ->where('index', $delIdx)
                        ->where('islock', true)
                        ->exists();

                    if (!$anyLocked) {
                        PaymentCompletionDetail::where('pc_id', $pc->id)
                            ->where('index', $delIdx)
                            ->delete();
                    }
                }
            }
            // ── CBD/COD/DP MULTI-ROW ──
            else {
                $rows = $request->input('rows', []);
                
                foreach ($rows as $idx => $row) {
                    $idx = (int) $idx;

                    $isLocked = PaymentCompletionDetail::where('pc_id', $pc->id)
                        ->where('index', $idx)
                        ->where('islock', false)
                        ->exists();
                    if (!$isLocked) continue;

                    // File invoice
                    if ($request->hasFile("file_invoice.{$idx}") && $request->file("file_invoice.{$idx}")->isValid()) {
                        $row['file_invoice'] = $request->file("file_invoice.{$idx}")
                            ->store('payment_completion/invoice', 'public');
                    } else {
                        $row['file_invoice'] = PaymentCompletionDetail::where('pc_id', $pc->id)
                            ->where('component', Component::FILE_INVOICE->value)
                            ->where('index', $idx)->value('value_text');
                    }

                    // File faktur pajak
                    if ($pc->is_form_faktur == 1) {
                        if ($request->hasFile("file_faktur_pajak.{$idx}") && $request->file("file_faktur_pajak.{$idx}")->isValid()) {
                            $row['file_faktur_pajak'] = $request->file("file_faktur_pajak.{$idx}")
                                ->store('payment_completion/faktur_pajak', 'public');
                        } else {
                            $row['file_faktur_pajak'] = PaymentCompletionDetail::where('pc_id', $pc->id)
                                ->where('component', Component::FILE_FAKTUR_PAJAK->value)
                                ->where('index', $idx)->value('value_text');
                        }
                    }

                    $nilaiRaw     = str_replace(',', '', $row['nilai_invoice'] ?? '');
                    $nilaiInvoice = is_numeric($nilaiRaw) ? (float) $nilaiRaw : null;

                    PaymentCompletionDetail::updateOrCreate(
                        ['id' => $row['id_invoice'] ?? null],
                        ['pc_id' => $pc->id, 'component' => Component::INVOICE->value, 'value_text' => $row['invoice'] ?? null, 'index' => $idx, 'created_by' => Auth::id()]
                    );
                    PaymentCompletionDetail::updateOrCreate(
                        ['id' => $row['id_nilai_invoice'] ?? null],
                        ['pc_id' => $pc->id, 'component' => Component::NILAI_INVOICE->value, 'value_number' => $nilaiInvoice, 'index' => $idx, 'created_by' => Auth::id()]
                    );
                    PaymentCompletionDetail::updateOrCreate(
                        ['id' => $row['id_tgl_jatuh_tempo'] ?? null],
                        ['pc_id' => $pc->id, 'component' => Component::TGL_JATUH_TEMPO->value, 'value_date' => $row['tgl_jatuh_tempo'] ?? null, 'index' => $idx, 'created_by' => Auth::id()]
                    );
                    PaymentCompletionDetail::updateOrCreate(
                        ['id' => $row['id_detail_notes'] ?? null],
                        ['pc_id' => $pc->id, 'component' => Component::DETAIL_NOTES->value, 'value_text' => $row['detail_notes'] ?? null, 'index' => $idx, 'created_by' => Auth::id()]
                    );
                    PaymentCompletionDetail::updateOrCreate(
                        ['id' => $row['id_file_invoice'] ?? null],
                        ['pc_id' => $pc->id, 'component' => Component::FILE_INVOICE->value, 'value_text' => $row['file_invoice'] ?? null, 'index' => $idx, 'created_by' => Auth::id()]
                    );

                    if ($pc->is_form_faktur == 1 && isset($row['faktur_pajak'])) {
                        PaymentCompletionDetail::updateOrCreate(
                            ['id' => $row['id_faktur_pajak'] ?? null],
                            ['pc_id' => $pc->id, 'component' => Component::FAKTUR_PAJAK->value, 'value_text' => $row['faktur_pajak'] ?? null, 'index' => $idx, 'created_by' => Auth::id()]
                        );
                        PaymentCompletionDetail::updateOrCreate(
                            ['id' => $row['id_file_faktur_pajak'] ?? null],
                            ['pc_id' => $pc->id, 'component' => Component::FILE_FAKTUR_PAJAK->value, 'value_text' => $row['file_faktur_pajak'] ?? null, 'index' => $idx, 'created_by' => Auth::id()]
                        );
                    }

                    if ($pc->is_form_proforma == 1) {
                        // File proforma
                        if ($request->hasFile("file_proforma_invoice.{$idx}") && $request->file("file_proforma_invoice.{$idx}")->isValid()) {
                            $row['file_proforma_invoice'] = $request->file("file_proforma_invoice.{$idx}")
                                ->store('payment_completion/proforma_invoice', 'public');
                        } else {
                            $row['file_proforma_invoice'] = PaymentCompletionDetail::where('pc_id', $pc->id)
                                ->where('component', Component::FILE_PROFORMA_INVOICE->value)
                                ->where('index', $idx)->value('value_text');
                        }

                        $nilaiProformaRaw = str_replace(',', '', $row['nilai_proforma_invoice'] ?? '');
                        $nilaiProforma    = is_numeric($nilaiProformaRaw) ? (float) $nilaiProformaRaw : null;

                        PaymentCompletionDetail::updateOrCreate(
                            ['id' => $row['id_proforma_invoice'] ?? null],
                            ['pc_id' => $pc->id, 'component' => Component::PROFORMA_INVOICE->value, 'value_text' => $row['proforma_invoice'] ?? null, 'index' => $idx, 'created_by' => Auth::id()]
                        );
                        PaymentCompletionDetail::updateOrCreate(
                            ['id' => $row['id_nilai_proforma_invoice'] ?? null],
                            ['pc_id' => $pc->id, 'component' => Component::NILAI_PROFORMA_INVOICE->value, 'value_number' => $nilaiProforma, 'index' => $idx, 'created_by' => Auth::id()]
                        );
                        PaymentCompletionDetail::updateOrCreate(
                            ['id' => $row['id_file_proforma_invoice'] ?? null],
                            ['pc_id' => $pc->id, 'component' => Component::FILE_PROFORMA_INVOICE->value, 'value_text' => $row['file_proforma_invoice'] ?? null, 'index' => $idx, 'created_by' => Auth::id()]
                        );
                    }
                }
            }

            DB::table('payment_completion_histories')->insert([
                'pc_id'      => $pc->id,
                'type'       => 'updated',
                'message'    => Auth::user()->name . ' update detail dokumen PC',
                'created_by' => Auth::user()->id,
                'created_at' => now(),
            ]);

            // ── Cek apakah request sekaligus hapus row ──
            if ($pc->type_payment == 1 && $request->input('delete_tempo_index') !== null) {
                $delIdx = (int) $request->input('delete_tempo_index');

                // Jangan hapus jika index 0 (row pertama tidak boleh dihapus)
                // Jangan hapus jika ada komponen yang sudah locked ATAU sudah diverifikasi
                $anyLocked = PaymentCompletionDetail::where('pc_id', $pc->id)
                    ->where('index', $delIdx)
                    ->where('islock', true)
                    ->exists();

                $anyVerified = PaymentCompletionDetail::where('pc_id', $pc->id)
                    ->where('index', $delIdx)
                    ->where('verify_status', 1)
                    ->exists();

                if ($delIdx > 0 && !$anyLocked && !$anyVerified) {
                    PaymentCompletionDetail::where('pc_id', $pc->id)
                        ->where('index', $delIdx)
                        ->delete();

                    DB::table('payment_completion_histories')->insert([
                        'pc_id'      => $pc->id,
                        'type'       => 'delete_row',
                        'message'    => Auth::user()->name . ' menghapus form tempo index ke-' . $delIdx,
                        'created_by' => Auth::id(),
                        'created_at' => now(),
                    ]);
                }

                DB::commit();

                return redirect()
                    ->route('purchasing.payment_completion.edit', Hashids::encode($pc->id))
                    ->with('success', 'Form Tempo Berhasil Dihapus');
            }

            // ── Cek apakah request sekaligus tambah row baru ──
            $newIndex = null;
            if ($pc->type_payment == 1 && $request->input('add_tempo_row') == 1) {

                // Ambil index tertinggi yang ada
                $maxIndex = PaymentCompletionDetail::where('pc_id', $pc->id)
                    ->max('index') ?? -1;
                $newIndex = $maxIndex + 1;

                // Tentukan base SI dari row yang sudah ada
                $existingSi = PaymentCompletionDetail::where('pc_id', $pc->id)
                    ->where('component', Component::NO_SI->value)
                    ->lockForUpdate()
                    ->orderBy('index')
                    ->value('value_text');

                if ($existingSi) {
                    $parts  = explode('-', $existingSi);
                    array_pop($parts); // hapus suffix -001
                    $baseSi = implode('-', $parts);
                } else {
                    $baseSi = $this->generateBaseNoSiLocked($pc->po_id);
                }

                // Hitung rowOrder dengan mempertimbangkan recycle:
                // Ambil semua suffix yang ada (-001, -002, dst), cari yang tertinggi + 1
                // Jika row terakhir dihapus, suffix tertinggi akan berkurang → recycle
                $existingSiList = PaymentCompletionDetail::where('pc_id', $pc->id)
                    ->where('component', Component::NO_SI->value)
                    ->pluck('value_text');

                $maxSuffix = 0;
                foreach ($existingSiList as $siVal) {
                    $parts = explode('-', $siVal);
                    $suffix = isset($parts[4]) ? (int) $parts[4] : 0;
                    if ($suffix > $maxSuffix) {
                        $maxSuffix = $suffix;
                    }
                }
                $rowOrder = $maxSuffix + 1;

                $noSi = $this->makeNoSi($baseSi, $rowOrder);

                // Insert semua komponen TEMPO untuk index baru
                $this->newUpsertDetail($pc->id, Component::NO_SI,             valueText:    $noSi, index: $newIndex);
                $this->newUpsertDetail($pc->id, Component::INVOICE,            valueText:    null,  index: $newIndex);
                $this->newUpsertDetail($pc->id, Component::NILAI_INVOICE,      valueNumber:  null,  index: $newIndex);
                $this->newUpsertDetail($pc->id, Component::TGL_TERIMA_INVOICE, valueDate:    null,  index: $newIndex);
                $this->newUpsertDetail($pc->id, Component::TGL_INVOICE,        valueDate:    null,  index: $newIndex);
                $this->newUpsertDetail($pc->id, Component::FILE_INVOICE,       valueText:    null,  index: $newIndex);
                $this->newUpsertDetail($pc->id, Component::PERIODE_TEMPO,      valueInteger: null,  index: $newIndex);
                $this->newUpsertDetail($pc->id, Component::TGL_JATUH_TEMPO,    valueDate:    null,  index: $newIndex);
                $this->newUpsertDetail($pc->id, Component::TGL_SURAT_JALAN,    valueDate:    null,  index: $newIndex);
                $this->newUpsertDetail($pc->id, Component::DETAIL_NOTES,       valueText:    null,  index: $newIndex);

                if ($pc->is_form_faktur == 1) {
                    $this->newUpsertDetail($pc->id, Component::FAKTUR_PAJAK,      valueText: null, index: $newIndex);
                    $this->newUpsertDetail($pc->id, Component::FILE_FAKTUR_PAJAK, valueText: null, index: $newIndex);
                }
                if ($pc->is_form_proforma == 1) {
                    $this->newUpsertDetail($pc->id, Component::PROFORMA_INVOICE,      valueText:   null, index: $newIndex);
                    $this->newUpsertDetail($pc->id, Component::NILAI_PROFORMA_INVOICE, valueNumber: null, index: $newIndex);
                    $this->newUpsertDetail($pc->id, Component::FILE_PROFORMA_INVOICE,  valueText:   null, index: $newIndex);
                }

                DB::table('payment_completion_histories')->insert([
                    'pc_id'      => $pc->id,
                    'type'       => 'add_row',
                    'message'    => Auth::user()->name . ' menambah form tempo baru (No SI: ' . $noSi . ')',
                    'created_by' => Auth::id(),
                    'created_at' => now(),
                ]);
            }

            // ── CBD: hapus row ──
            if ($pc->type_payment == 2 && $request->input('delete_cbd_index') !== null && $request->input('delete_cbd_index') !== '') {
                $delIdx = (int) $request->input('delete_cbd_index');

                $anyLocked = PaymentCompletionDetail::where('pc_id', $pc->id)
                    ->where('index', $delIdx)
                    ->where('islock', true)
                    ->exists();

                $anyVerified = PaymentCompletionDetail::where('pc_id', $pc->id)
                    ->where('index', $delIdx)
                    ->where('verify_status', 1)
                    ->exists();

                if ($delIdx > 0 && !$anyLocked && !$anyVerified) {
                    PaymentCompletionDetail::where('pc_id', $pc->id)
                        ->where('index', $delIdx)
                        ->delete();

                    DB::table('payment_completion_histories')->insert([
                        'pc_id'      => $pc->id,
                        'type'       => 'delete_row',
                        'message'    => Auth::user()->name . ' menghapus form CBD ke-' . ($delIdx + 1),
                        'created_by' => Auth::id(),
                        'created_at' => now(),
                    ]);
                }

                DB::commit();

                return redirect()
                    ->route('purchasing.payment_completion.edit', Hashids::encode($pc->id))
                    ->with('success', 'Form Berhasil Dihapus');
            }

            // ── CBD: tambah row baru ──
            if ($pc->type_payment == 2 && $request->input('add_cbd_row') == 1) {
                $maxIndex = PaymentCompletionDetail::where('pc_id', $pc->id)
                    ->max('index') ?? -1;
                $newIndex = $maxIndex + 1;

                $this->newUpsertDetail($pc->id, Component::INVOICE,          valueText:   null, index: $newIndex);
                $this->newUpsertDetail($pc->id, Component::NILAI_INVOICE,    valueNumber: null, index: $newIndex);
                $this->newUpsertDetail($pc->id, Component::TGL_JATUH_TEMPO,  valueDate:   null, index: $newIndex);
                $this->newUpsertDetail($pc->id, Component::FILE_INVOICE,     valueText:   null, index: $newIndex);
                $this->newUpsertDetail($pc->id, Component::DETAIL_NOTES,     valueText:   null, index: $newIndex);

                if ($pc->is_form_faktur == 1) {
                    $this->newUpsertDetail($pc->id, Component::FAKTUR_PAJAK,      valueText: null, index: $newIndex);
                    $this->newUpsertDetail($pc->id, Component::FILE_FAKTUR_PAJAK, valueText: null, index: $newIndex);
                }
                if ($pc->is_form_proforma == 1) {
                    $this->newUpsertDetail($pc->id, Component::PROFORMA_INVOICE,      valueText:   null, index: $newIndex);
                    $this->newUpsertDetail($pc->id, Component::NILAI_PROFORMA_INVOICE, valueNumber: null, index: $newIndex);
                    $this->newUpsertDetail($pc->id, Component::FILE_PROFORMA_INVOICE,  valueText:   null, index: $newIndex);
                }

                DB::table('payment_completion_histories')->insert([
                    'pc_id'      => $pc->id,
                    'type'       => 'add_row',
                    'message'    => Auth::user()->name . ' menambah form CBD baru',
                    'created_by' => Auth::id(),
                    'created_at' => now(),
                ]);

                DB::commit();

                return redirect()
                    ->route('purchasing.payment_completion.edit', Hashids::encode($pc->id))
                    ->with('success', 'Data tersimpan & Form Baru Ditambahkan')
                    ->with('active_tab_index', $newIndex);
            }

            DB::commit();

            // Jika ada tambah row baru TEMPO, redirect ke edit dengan tab baru aktif
            if ($newIndex !== null) {
                return redirect()
                    ->route('purchasing.payment_completion.edit', Hashids::encode($pc->id))
                    ->with('success', 'Data tersimpan & Form Tempo Baru Ditambahkan')
                    ->with('active_tab_index', $newIndex);
            }

            return redirect()
                ->route('purchasing.payment_completion.show', Hashids::encode($pc->id))
                ->with('success', 'Payment Completion Updated Successfully');

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }

    // =========================================================
    // PUBLISH
    // =========================================================

    public function publish($id)
    {
        $decoded = Hashids::decode($id);
        $id = $decoded[0] ?? null;
        if (!$id) return redirect()->back()->with('error', 'ID tidak valid');

        DB::table('payment_completions')->where('id', $id)->update([
            'status'     => 1,
            'updated_at' => now(),
        ]);

        $doc_no = DB::table('payment_completions')->where('id', $id)->value('doc_no');

        DB::table('payment_completion_histories')->insert([
            'pc_id'      => $id,
            'type'       => 'published',
            'message'    => Auth::user()->name . ' publish dokumen PC ke On Progress Checking',
            'created_by' => Auth::id(),
            'created_at' => now(),
        ]);

        return redirect()->back()->with('success', "Payment Completion {$doc_no} Published");
    }

    // =========================================================
    // DRAFT (kembalikan ke status 0)
    // =========================================================

    public function draft($id)
    {
        $decoded = Hashids::decode($id);
        $id = $decoded[0] ?? null;
        if (!$id) return redirect()->back()->with('error', 'ID tidak valid');

        DB::table('payment_completions')->where('id', $id)->update([
            'status'     => 0,
            'updated_at' => now(),
        ]);

        $doc_no = DB::table('payment_completions')->where('id', $id)->value('doc_no');

        DB::table('payment_completion_histories')->insert([
            'pc_id'      => $id,
            'type'       => 'draft',
            'message'    => Auth::user()->name . ' kembalikan dokumen PC ke Draft',
            'created_by' => Auth::id(),
            'created_at' => now(),
        ]);

        return redirect()->back()->with('success', "Payment Completion {$doc_no} Changed to Draft");
    }

    // =========================================================
    // DONE
    // =========================================================

    public function done($id)
    {
        $decoded = Hashids::decode($id);
        $id = $decoded[0] ?? null;
        if (!$id) return redirect()->back()->with('error', 'ID tidak valid');

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

    // =========================================================
    // REJECT
    // =========================================================

    public function reject(Request $request)
    {
        $decoded = Hashids::decode($request->id);
        $id = $decoded[0] ?? null;
        if (!$id) return redirect()->back()->with('error', 'ID tidak valid');

        DB::table('payment_completions')->where('id', $id)->update([
            'status'        => 3,
            'reject_reason' => $request->receipt_note,
            'updated_at'    => now(),
        ]);

        $doc_no = DB::table('payment_completions')->where('id', $id)->value('doc_no');

        DB::table('payment_completion_histories')->insert([
            'pc_id'      => $id,
            'type'       => 'rejected',
            'message'    => Auth::user()->name . ' reject dokumen PC. Alasan: ' . $request->receipt_note,
            'created_by' => Auth::id(),
            'created_at' => now(),
        ]);

        return redirect()->back()->with('success', "Payment Completion {$doc_no} Rejected");
    }

    // =========================================================
    // TAMBAH KELENGKAPAN (kembalikan ke status 4)
    // =========================================================

    public function tambahkelengkapan(Request $request, $id)
    {
        $decoded = Hashids::decode($id);
        $id = $decoded[0] ?? null;
        if (!$id) return redirect()->back()->with('error', 'ID Tidak Valid');

        if (!DB::table('payment_completions')->where('id', $id)->exists()) {
            return redirect()->back()->with('error', 'Data Tidak Ada');
        }

        DB::table('payment_completions')->where('id', $id)->update([
            'status'     => 4,
            'updated_at' => now(),
        ]);

        $doc_no = DB::table('payment_completions')->where('id', $id)->value('doc_no');

        DB::table('payment_completion_histories')->insert([
            'pc_id'      => $id,
            'type'       => 'return_back',
            'message'    => Auth::user()->name . ' mengembalikan dokumen PC ke pendingan admin purchasing',
            'created_by' => Auth::id(),
            'created_at' => now(),
        ]);

        return redirect()->back()->with('success', "Payment Completion {$doc_no} Status Updated");
    }

    // =========================================================
    // LOCK (dari purchasing show — jika ada)
    // =========================================================

    public function lock(Request $request, $id)
    {
        $decoded = Hashids::decode($id);
        $id = $decoded[0] ?? null;
        if (!$id) return redirect()->back()->with('error', 'ID tidak valid');

        $pc = PaymentCompletion::findOrFail($id);

        DB::table('payment_completion_details')->where('pc_id', $pc->id)->update([
            'islock' => true,
        ]);

        DB::table('payment_completion_histories')->insert([
            'pc_id'      => $pc->id,
            'type'       => 'locked',
            'message'    => Auth::user()->name . ' mengunci dokumen PC',
            'created_by' => Auth::id(),
            'created_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Payment Completion Locked');
    }

    // =========================================================
    // PRINT
    // =========================================================

    public function print($id)
    {
        $decoded = Hashids::decode($id);
        $id = $decoded[0] ?? null;
        if (!$id) abort(404);

        $pc = DB::table('payment_completions as pc')
            ->leftJoin('po', 'pc.po_id', '=', 'po.id')
            ->leftJoin('companies as c', 'po.company_id', '=', 'c.id')
            ->leftJoin('suppliers as s', 'po.supplier_id', '=', 's.id')
            ->leftJoin('purchase_requisitions as pr', 'pr.id', '=', 'po.purchase_id')
            ->leftJoin('payment_terms as pt', 'pt.id', '=', 'po.payment_term_id')
            ->select(
                'pc.*',
                'pc.doc_no as no_pc',
                'po.doc_no as no_po',
                'po.created_at as tgl_po',
                'po.currency as po_mata_uang',
                'c.name as nama_company',
                's.name as nama_supplier',
                'pr.doc_no as no_pr',
                'pr.created_at as tgl_pr',
                'pt.name as payment_terms_nama'
            )
            ->where('pc.id', $id)
            ->first();

        if (!$pc) abort(404);

        $details = DB::table('payment_completion_details')
            ->where('pc_id', $id)
            ->orderBy('index')
            ->orderBy('id')
            ->get();

        // Susun components per index
        $components = [];
        foreach ($details as $d) {
            $key = is_object($d->component) ? $d->component->value : (string) $d->component;
            $val = $d->value_number ?? $d->value_date ?? $d->value_text ?? $d->value_integer;
            $components[$d->index][$key] = $val;
        }
        ksort($components);

        // Flatten components[0] untuk print TEMPO (ambil komponen header dari index 0 atau gabungan)
        $componentFlat = [];
        foreach ($components as $idx => $comp) {
            foreach ($comp as $k => $v) {
                $componentFlat[$k] = $v;
            }
        }

        // Pairs untuk CBD
        $pairs = [];
        if ($pc->type_payment != 1) {
            // Join users untuk ambil nama verify_by
            $detailsWithUser = DB::table('payment_completion_details as d')
                ->leftJoin('users as u', 'u.id', '=', 'd.verify_by')
                ->select('d.*', 'u.name as verify_user_name')
                ->where('d.pc_id', $id)
                ->orderBy('d.index')
                ->get();

            $invoiceRows  = $detailsWithUser->where('component', 'invoice')->keyBy('index');
            $fakturRows   = $detailsWithUser->where('component', 'faktur_pajak')->keyBy('index');
            $nilaiRows    = $detailsWithUser->where('component', 'nilai_invoice')->keyBy('index');
            $fileInvRows  = $detailsWithUser->where('component', 'file_invoice')->keyBy('index');
            $fileFpRows   = $detailsWithUser->where('component', 'file_faktur_pajak')->keyBy('index');
            $notesRows       = $detailsWithUser->where('component', 'detail_notes')->keyBy('index');
            $tglJtRows       = $detailsWithUser->where('component', 'tgl_jatuh_tempo')->keyBy('index');
            $proformaRows    = $detailsWithUser->where('component', 'proforma_invoice')->keyBy('index');
            $nilProformaRows = $detailsWithUser->where('component', 'nilai_proforma_invoice')->keyBy('index');

            $allIndexes = collect([
                $invoiceRows->keys(), $fakturRows->keys(), $nilaiRows->keys(),
            ])->flatten()->unique()->sort()->values();

            foreach ($allIndexes as $index) {
                $invRow      = $invoiceRows[$index] ?? null;
                $fakRow      = $fakturRows[$index] ?? null;
                $nilRow      = $nilaiRows[$index] ?? null;
                $proformaRow = $proformaRows[$index] ?? null;

                $pairs[] = [
                    'index'                  => $index,
                    'invoice'                => $invRow?->value_text,
                    'nilai_invoice'          => $nilRow?->value_number,
                    'faktur_pajak'           => $fakRow?->value_text,
                    'file_invoice'           => $fileInvRows[$index]?->value_text ?? null,
                    'file_faktur_pajak'      => $fileFpRows[$index]?->value_text ?? null,
                    'tgl_jatuh_tempo'        => $tglJtRows[$index]?->value_date ?? null,
                    'detail_notes'           => $notesRows[$index]?->value_text ?? null,
                    'proforma_invoice'       => $proformaRow?->value_text,
                    'nilai_proforma_invoice' => $nilProformaRows[$index]?->value_number ?? null,
                    'invoice_verify_status'  => $invRow?->verify_status,
                    'invoice_verify_user'    => $invRow?->verify_user_name ?? '-',
                    'invoice_verify_date'    => $invRow?->verify_date,
                    'faktur_verify_status'   => $fakRow?->verify_status,
                    'faktur_verify_user'     => $fakRow?->verify_user_name ?? '-',
                    'faktur_verify_date'     => $fakRow?->verify_date,
                ];
            }
        }

        $historyPc = DB::table('payment_completion_histories as h')
            ->leftJoin('users as u', 'u.id', '=', 'h.created_by')
            ->select('h.*', 'u.name as employee')
            ->where('h.pc_id', $id)
            ->orderBy('h.created_at')
            ->get();

        $view = $pc->type_payment == 1
            ? 'purchase.payment_completion.print_tempo'
            : 'purchase.payment_completion.print_cbd';

        $pdf = Pdf::loadView($view, compact('pc', 'components', 'componentFlat', 'pairs', 'historyPc'))
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'defaultFont'     => 'sans-serif',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
            ]);

        return $pdf->stream($pc->no_pc . '.pdf');
    }

    // =========================================================
    // LIST PO (pilih PO untuk buat PC)
    // =========================================================

    public function list()
    {
        if (!Gate::allows('payment_completion_admin')) {
            return redirect()->back()->with(['error' => 'Anda tidak memiliki izin akses']);
        }

        $filters = session()->pull('filters', [
            'status'    => '',
            'po_up_20m' => '',
        ]);

        return view('purchase.payment_completion.list', compact('filters'));
    }

    // =========================================================
    // LIST PO DATATABLES
    // =========================================================

    public function list_po_datatables(Request $request)
    {
        $result = DB::table('po')
            ->select(
                'po.id as POID',
                'po.doc_no as po_no',
                'po.created_at as tgl_po',
                'po.payment_amount as harga_po',
                'po.currency as mata_uang',
                'po.status as po_status',
                'suppliers.name as supplier_nama',
                'companies.name as pt_nama',
                'purchase_requisitions.doc_no as no_pr',
                'purchase_requisitions.created_at as tgl_pr',
                'payment_terms.name AS payment_term'
            )
            ->leftJoin('payment_terms', 'payment_terms.id', '=', 'po.payment_term_id')
            ->leftJoin('suppliers', 'suppliers.id', '=', 'po.supplier_id')
            ->leftJoin('companies', 'companies.id', '=', 'po.company_id')
            ->leftJoin('purchase_requisitions', 'purchase_requisitions.id', '=', 'po.purchase_id')
            ->leftJoin('payment_completions', 'payment_completions.po_id', '=', 'po.id')
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('payment_completions as pc')
                    ->whereColumn('pc.po_id', 'po.id')
                    ->where('pc.status', '!=', 3);
            })
            ->whereIn('po.status', [2, 4, 5]);

        if ($request->filled('status')) {
            $result->where('po.status', (int) $request->status);
        }

        if ($request->filled('po_up_20m')) {
            if ($request->po_up_20m === '0') {
                $result->where('po.payment_amount', '<', 20000000);
            } elseif ($request->po_up_20m === '1') {
                $result->where('po.payment_amount', '>', 20000000);
            }
        }

        $result = $result
            ->groupBy(
                'po.id', 'po.doc_no', 'po.created_at', 'po.payment_amount',
                'po.currency', 'po.status', 'suppliers.name', 'companies.name',
                'purchase_requisitions.doc_no', 'purchase_requisitions.created_at', 'payment_terms.name'
            )
            ->orderBy('po.created_at', 'DESC');

        return datatables()->of($result)
            ->addColumn('action', function ($result) {
                $hash_id   = Hashids::encode($result->POID);
                $url_create = route('purchasing.payment_completion.create', ['po' => $hash_id]);
                return '<a href="' . $url_create . '" class="btn btn-success btn-sm" title="Buat PC">
                    <i class="ti-file"></i> Buat PC
                </a>';
            })
            ->editColumn('po_no', function ($result) {
                return "<a target='_blank' href='" . route('purchasing.po.show', Hashids::encode($result->POID)) . "'>{$result->po_no}</a>";
            })
            ->editColumn('harga_po', function ($result) {
                return $result->mata_uang . ' ' . number_format($result->harga_po, 2, ',', '.');
            })
            ->addColumn('po_status', function ($result) {
                return getStatusPO(strtolower($result->po_status));
            })
            ->rawColumns(['action', 'po_no', 'po_status'])
            ->make(true);
    }

    // =========================================================
    // CHANGE RELATION PO
    // =========================================================

    public function changeRelationPo(Request $request, $id)
    {
        $decoded = Hashids::decode($id);
        $id = $decoded[0] ?? null;

        if (!$id) {
            return redirect()->back()->with('error', 'ID Tidak Valid');
        }

        $oldPc = DB::table('payment_completions')->where('id', $id)->first();
        if (!$oldPc) {
            return redirect()->back()->with('error', 'Data PC Tidak Ada');
        }

        $oldPcDetails   = DB::table('payment_completion_details')->where('pc_id', $oldPc->id)->get();
        $oldPcHistories = DB::table('payment_completion_histories')->where('pc_id', $oldPc->id)->get();

        $newPoId = $request->get('new_po_id');

        DB::beginTransaction();
        try {
            // Buat PC baru sebagai copy dari PC lama dengan po_id baru
            $newPcData = (array) $oldPc;
            unset($newPcData['id'], $newPcData['po_id']);
            $newPcData['po_id']      = $newPoId;
            $newPcData['created_by'] = Auth::id();
            $newPcData['created_at'] = now();

            $newPcId = DB::table('payment_completions')->insertGetId($newPcData);

            // Copy details ke PC baru
            foreach ($oldPcDetails as $detail) {
                $detailData = (array) $detail;
                unset($detailData['id'], $detailData['pc_id']);
                $detailData['pc_id']      = $newPcId;
                $detailData['created_by'] = Auth::id();
                $detailData['created_at'] = now();
                DB::table('payment_completion_details')->insert($detailData);
            }

            // Copy histories ke PC baru
            foreach ($oldPcHistories as $history) {
                $historyData = (array) $history;
                unset($historyData['id'], $historyData['pc_id']);
                $historyData['pc_id']      = $newPcId;
                $historyData['created_by'] = Auth::id();
                $historyData['created_at'] = now();
                DB::table('payment_completion_histories')->insert($historyData);
            }

            // Copy bukti pembayaran ke PC baru
            $oldPaymentDetails = DB::table('payment_completion_payment_details')
                ->where('pc_id', $oldPc->id)
                ->get();

            foreach ($oldPaymentDetails as $payment) {
                $paymentData = (array) $payment;
                unset($paymentData['id'], $paymentData['pc_id']);
                $paymentData['pc_id']      = $newPcId;
                $paymentData['created_by'] = Auth::id();
                $paymentData['created_at'] = now();
                $paymentData['updated_at'] = now();
                DB::table('payment_completion_payment_details')->insert($paymentData);
            }

            // Generate doc_no baru untuk PC lama (tambah -REV-xxx)
            // Generate doc_no untuk PC BARU (PC lama tetap doc_no-nya)
            $baseDocNo = preg_replace('/-REV-\d+$/', '', $oldPc->doc_no);
            $existingRevs = DB::table('payment_completions')
                ->where('doc_no', 'like', $baseDocNo . '-REV-%')
                ->pluck('doc_no');
            $maxNumber = 0;
            foreach ($existingRevs as $docNo) {
                if (preg_match('/-REV-(\d+)$/', $docNo, $m)) {
                    $num = intval($m[1]);
                    if ($num > $maxNumber) $maxNumber = $num;
                }
            }
            $newRevNumber = str_pad($maxNumber + 1, 3, '0', STR_PAD_LEFT);
            $newDocNo     = $baseDocNo . '-REV-' . $newRevNumber;

            // Update PC BARU: set doc_no dengan REV
            DB::table('payment_completions')
                ->where('id', $newPcId)
                ->update(['doc_no' => $newDocNo]);

            // Update PC LAMA: status 5 saja, doc_no tetap
            DB::table('payment_completions')
                ->where('id', $oldPc->id)
                ->update(['status' => 5]);

            $oldPo = getDataByID('po', $oldPc->po_id)->doc_no;
            $newPo = getDataByID('po', $newPoId)->doc_no;

            // History di PC lama
            DB::table('payment_completion_histories')->insert([
                'pc_id'      => $oldPc->id,
                'type'       => 'change_relation_po',
                'message'    => Auth::user()->name . ' perubahan relasi dokumen PO dari ' . $oldPo . ' menjadi ' . $newPo,
                'created_by' => Auth::id(),
                'created_at' => now(),
            ]);

            // History di PC baru
            DB::table('payment_completion_histories')->insert([
                'pc_id'      => $newPcId,
                'type'       => 'change_relation_po',
                'message'    => Auth::user()->name . ' perubahan relasi dokumen PO dari ' . $oldPo . ' menjadi ' . $newPo,
                'created_by' => Auth::id(),
                'created_at' => now(),
            ]);

            DB::commit();

            return redirect()
                ->route('purchasing.payment_completion.show', Hashids::encode($newPcId))
                ->with('success', 'Payment Completion changed relation PO successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    // =========================================================
    // GET PO LIST (untuk modal change relation)
    // =========================================================

    public function getPoList($po)
    {
        $decoded = Hashids::decode($po);
        $poId = $decoded[0] ?? null;
        if (!$poId) return response()->json([]);

        $docPo = DB::table('po')->where('id', $poId)->value('doc_no');
        if (strpos($docPo, '-REV-') !== false) {
            $docPo = substr($docPo, 0, strpos($docPo, '-REV-'));
        }

        $list = DB::table('po')
            ->where('id', '!=', $poId)
            ->where('doc_no', 'like', '%' . $docPo . '%')
            ->whereIn('status', [2, 4, 5])
            ->pluck('doc_no', 'id');

        return response()->json($list);
    }

    // =========================================================
    // ADD TEMPO ROW — tambah row baru ke PC TEMPO
    // Dipanggil via POST dari tombol "+ Tambah Form" di edit_tempo
    // =========================================================

    public function addTempoRow(Request $request, $id)
    {
        $decoded = Hashids::decode($id);
        $pcId    = $decoded[0] ?? null;
        if (!$pcId) return redirect()->back()->with('error', 'ID tidak valid');

        $pc = PaymentCompletion::findOrFail($pcId);

        if ($pc->type_payment != 1) {
            return redirect()->back()->with('error', 'Hanya untuk PC tipe TEMPO');
        }

        DB::transaction(function () use ($pc, &$newIndex) {

            // Ambil index tertinggi yang sudah ada
            $maxIndex = PaymentCompletionDetail::where('pc_id', $pc->id)
                ->max('index') ?? -1;

            $newIndex = $maxIndex + 1;

            // Tentukan base SI dari row yang sudah ada (strip suffix -001)
            $existingSi = PaymentCompletionDetail::where('pc_id', $pc->id)
                ->where('component', Component::NO_SI->value)
                ->lockForUpdate()
                ->orderBy('index')
                ->value('value_text');

            if ($existingSi) {
                $parts  = explode('-', $existingSi);
                array_pop($parts); // hapus suffix -001
                $baseSi = implode('-', $parts);
            } else {
                $baseSi = $this->generateBaseNoSiLocked($pc->po_id);
            }

            // Hitung urutan row baru (count existing SI + 1)
            $rowOrder = PaymentCompletionDetail::where('pc_id', $pc->id)
                ->where('component', Component::NO_SI->value)
                ->count() + 1;

            $noSi = $this->makeNoSi($baseSi, $rowOrder);

            // Insert semua komponen TEMPO untuk index baru
            $this->newUpsertDetail($pc->id, Component::NO_SI,             valueText:    $noSi, index: $newIndex);
            $this->newUpsertDetail($pc->id, Component::INVOICE,            valueText:    null,  index: $newIndex);
            $this->newUpsertDetail($pc->id, Component::NILAI_INVOICE,      valueNumber:  null,  index: $newIndex);
            $this->newUpsertDetail($pc->id, Component::TGL_TERIMA_INVOICE, valueDate:    null,  index: $newIndex);
            $this->newUpsertDetail($pc->id, Component::TGL_INVOICE,        valueDate:    null,  index: $newIndex);
            $this->newUpsertDetail($pc->id, Component::FILE_INVOICE,       valueText:    null,  index: $newIndex);
            $this->newUpsertDetail($pc->id, Component::PERIODE_TEMPO,      valueInteger: null,  index: $newIndex);
            $this->newUpsertDetail($pc->id, Component::TGL_JATUH_TEMPO,    valueDate:    null,  index: $newIndex);
            $this->newUpsertDetail($pc->id, Component::TGL_SURAT_JALAN,    valueDate:    null,  index: $newIndex);
            $this->newUpsertDetail($pc->id, Component::DETAIL_NOTES,       valueText:    null,  index: $newIndex);

            if ($pc->is_form_faktur == 1) {
                $this->newUpsertDetail($pc->id, Component::FAKTUR_PAJAK,      valueText: null, index: $newIndex);
                $this->newUpsertDetail($pc->id, Component::FILE_FAKTUR_PAJAK, valueText: null, index: $newIndex);
            }

            DB::table('payment_completion_histories')->insert([
                'pc_id'      => $pc->id,
                'type'       => 'add_row',
                'message'    => Auth::user()->name . ' menambah form tempo baru (No SI: ' . $noSi . ')',
                'created_by' => Auth::id(),
                'created_at' => now(),
            ]);
        });

        // Redirect ke edit page dengan fragment #component-{newIndex} agar tab baru aktif
        return redirect()
            ->route('purchasing.payment_completion.edit', Hashids::encode($pc->id))
            ->with('success', 'Form Tempo Baru Berhasil Ditambahkan')
            ->with('active_tab_index', $newIndex);
    }

    // =========================================================
    // GENERATE NO SI — AJAX endpoint
    // Dipanggil saat user klik "+ Tambah Form" di edit_tempo
    // Gunakan DB transaction + pessimistic lock untuk hindari double
    // =========================================================

    public function generateNoSiAjax(Request $request, $id)
    {
        $decoded = Hashids::decode($id);
        $pcId    = $decoded[0] ?? null;
        if (!$pcId) return response()->json(['error' => 'ID tidak valid'], 422);

        $pc = PaymentCompletion::findOrFail($pcId);

        // Wrap dalam transaction + lock untuk hindari race condition
        $noSi = DB::transaction(function () use ($pc) {

            // Lock rows no_si milik PC ini agar tidak ada proses lain yang baca/tulis bersamaan
            $existingSiRows = PaymentCompletionDetail::where('pc_id', $pc->id)
                ->where('component', Component::NO_SI->value)
                ->lockForUpdate()
                ->get();

            if ($existingSiRows->count() > 0) {
                // Ambil base dari SI yang sudah ada (strip suffix -001)
                $firstSi = $existingSiRows->sortBy('index')->first()->value_text;
                $parts   = explode('-', $firstSi);
                array_pop($parts); // hapus suffix -001
                $baseSi  = implode('-', $parts);
            } else {
                // Belum ada SI untuk PC ini, generate base baru
                // Lock global untuk hindari 2 PC generate nomor sama di waktu bersamaan
                $baseSi = $this->generateBaseNoSiLocked($pc->po_id);
            }

            $rowOrder = $existingSiRows->count() + 1;

            return $this->makeNoSi($baseSi, $rowOrder);
        });

        return response()->json(['no_si' => $noSi]);
    }

    /**
     * Generate base No SI dengan pessimistic lock.
     * Dipanggil HANYA dari dalam DB::transaction.
     */
    private function generateBaseNoSiLocked(int $poId): string
    {
        $row = DB::table('po')
            ->join('companies', 'companies.id', '=', 'po.company_id')
            ->where('po.id', $poId)
            ->select('companies.alias')
            ->first();

        if (!$row || empty($row->alias)) {
            throw new \RuntimeException('Company Not Found');
        }

        $alias   = strtoupper($row->alias);
        $periode = Carbon::now()->format('my');
        $prefix  = "SI-{$alias}-{$periode}-";

        // Lock semua baris no_si dengan prefix ini agar tidak ada proses lain yang baca
        $existing = PaymentCompletionDetail::where('component', Component::NO_SI->value)
            ->where('value_text', 'LIKE', $prefix . '%')
            ->whereYear('created_at', date('Y'))
            ->lockForUpdate()
            ->pluck('value_text');

        $maxSeq = 0;
        foreach ($existing as $doc) {
            $parts = explode('-', $doc);
            // SI=0, ALIAS=1, mmyy=2, nnnnn=3
            $num = isset($parts[3]) ? (int) $parts[3] : 0;
            if ($num > $maxSeq) {
                $maxSeq = $num;
            }
        }

        return $prefix . str_pad($maxSeq + 1, 5, '0', STR_PAD_LEFT);
    }

    // =========================================================
    // SLA INDEX
    // =========================================================

    public function slaIndex(Request $request)
    {
        $filters = [
            'sla_days'   => $request->input('sla_days', 3),
            'date_from'  => $request->input('date_from', now()->startOfMonth()->format('Y-m-d')),
            'date_to'    => $request->input('date_to', now()->format('Y-m-d')),
            'company_id' => $request->input('company_id', ''),
            'component'  => $request->input('component', ''),
        ];

        $companies  = DB::table('companies')->orderBy('name')->get();
        $components = DB::table('payment_completion_details')
            ->distinct()->pluck('component')->filter()->values();

        return view('purchase.payment_completion.sla', compact('filters', 'companies', 'components'));
    }

    // =========================================================
    // SLA DATATABLES
    // =========================================================

    public function slaDatatables(Request $request)
    {
        $slaDays   = (int) $request->input('sla_days', 3);
        $dateFrom  = $request->input('date_from');
        $dateTo    = $request->input('date_to');
        $companyId = $request->input('company_id');
        $component = $request->input('component');

        $q = DB::table('payment_completion_details as d')
            ->join('payment_completions as pc', 'pc.id', '=', 'd.pc_id')
            ->join('po', 'po.id', '=', 'pc.po_id')
            ->join('companies as c', 'c.id', '=', 'po.company_id')
            ->leftJoin('users as u', 'u.id', '=', 'd.verify_by')
            ->select(
                'pc.doc_no as pc_doc_no',
                'po.doc_no as po_doc_no',
                'c.name as company_name',
                'd.component',
                DB::raw('COALESCE(d.value_text, CAST(d.value_number AS TEXT), CAST(d.value_date AS TEXT)) as value'),
                'd.verify_status',
                'u.name as verified_by',
                'd.created_at',
                'd.verify_date'
            );

        if ($dateFrom) $q->where('d.created_at', '>=', $dateFrom);
        if ($dateTo)   $q->where('d.created_at', '<=', $dateTo . ' 23:59:59');
        if ($companyId) $q->where('c.id', $companyId);
        if ($component) $q->where('d.component', $component);

        return DataTables::of($q)
            ->addColumn('created_at_fmt', fn ($r) =>
                $r->created_at ? Carbon::parse($r->created_at)->format('d/m/Y H:i') : '-')
            ->addColumn('verify_date_fmt', fn ($r) =>
                $r->verify_date ? Carbon::parse($r->verify_date)->format('d/m/Y H:i') : '-')
            ->addColumn('age_to_verify', function ($r) {
                if (!$r->verify_date) return '-';
                $diff = Carbon::parse($r->created_at)->diffInDays(Carbon::parse($r->verify_date));
                return $diff . ' hari';
            })
            ->addColumn('sla_target', fn () => $slaDays . ' hari')
            ->addColumn('sla_result', function ($r) use ($slaDays) {
                if (!$r->verify_date) return '<span class="badge badge-warning">Belum Verify</span>';
                $diff = Carbon::parse($r->created_at)->diffInDays(Carbon::parse($r->verify_date));
                return $diff <= $slaDays
                    ? '<span class="badge badge-success">ON TIME</span>'
                    : '<span class="badge badge-danger">OVER SLA</span>';
            })
            ->addColumn('verify_badge', fn ($r) =>
                $r->verify_status == 1
                    ? '<span class="badge badge-success">Verified</span>'
                    : '<span class="badge badge-danger">Unverified</span>')
            ->rawColumns(['sla_result', 'verify_badge'])
            ->make(true);
    }

    // =========================================================
    // STORE BUKTI PEMBAYARAN
    // =========================================================
    public function storePaymentDetail(Request $request, $id)
    {
        if (!Gate::allows('payment_completion_admin')) {
            return redirect()->back()->with('error', 'Tidak memiliki akses');
        }

        $decoded = Hashids::decode($id);
        $pcId = $decoded[0] ?? null;
        if (!$pcId) return redirect()->back()->with('error', 'ID tidak valid');

        $pc = DB::table('payment_completions')->where('id', $pcId)->first();
        if (!$pc) return redirect()->back()->with('error', 'PC tidak ditemukan');

        // Cek status yang diizinkan
        if (!in_array($pc->status, [0, 1, 2, 4])) {
            return redirect()->back()->with('error', 'Status PC tidak mengizinkan upload bukti pembayaran');
        }

        $request->validate([
            'index' => ['required', 'integer'],
            'title' => ['required', 'string', 'max:255'],
            'file'  => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'notes' => ['nullable', 'string'],
        ]);

        $filePath = null;
        if ($request->hasFile('file') && $request->file('file')->isValid()) {
            $filePath = $request->file('file')->store('payment_completion/payment_details', 'public');
        }

        DB::table('payment_completion_payment_details')->insert([
            'pc_id'      => $pcId,
            'index'      => $request->index,
            'title'      => $request->title,
            'file'       => $filePath,
            'notes'      => $request->notes,
            'created_by' => Auth::id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('payment_completion_histories')->insert([
            'pc_id'      => $pcId,
            'type'       => 'upload_bukti_bayar',
            'message'    => Auth::user()->name . ' menambahkan bukti pembayaran: ' . $request->title,
            'created_by' => Auth::id(),
            'created_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Bukti pembayaran berhasil ditambahkan');
    }

    // =========================================================
    // DESTROY BUKTI PEMBAYARAN
    // =========================================================
    public function destroyPaymentDetail($id)
    {
        if (!Gate::allows('payment_completion_admin')) {
            return redirect()->back()->with('error', 'Tidak memiliki akses');
        }

        $decoded = Hashids::decode($id);
        $detailId = $decoded[0] ?? null;
        if (!$detailId) return redirect()->back()->with('error', 'ID tidak valid');

        $detail = DB::table('payment_completion_payment_details')->where('id', $detailId)->first();
        if (!$detail) return redirect()->back()->with('error', 'Data tidak ditemukan');

        // Cek status PC
        $pc = DB::table('payment_completions')->where('id', $detail->pc_id)->first();
        if (!in_array($pc->status, [0, 1, 2, 4])) {
            return redirect()->back()->with('error', 'Status PC tidak mengizinkan hapus bukti pembayaran');
        }

        // Hapus file dari storage
        if (Storage::disk('public')->exists($detail->file)) {
            Storage::disk('public')->delete($detail->file);
        }

        DB::table('payment_completion_payment_details')->where('id', $detailId)->delete();

        DB::table('payment_completion_histories')->insert([
            'pc_id'      => $detail->pc_id,
            'type'       => 'delete_bukti_bayar',
            'message'    => Auth::user()->name . ' menghapus bukti pembayaran: ' . $detail->title,
            'created_by' => Auth::id(),
            'created_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Bukti pembayaran berhasil dihapus');
    }

    public function searchInvoice(Request $request)
    {
        $keyword = $request->get('q');
        if (!$keyword) return response()->json([]);

        $results = DB::table('payment_completion_details as pcd')
            ->join('payment_completions as pc', 'pc.id', '=', 'pcd.pc_id')
            ->leftJoin('po', 'po.id', '=', 'pc.po_id')
            ->leftJoin('suppliers', 'suppliers.id', '=', 'po.supplier_id')
            ->whereIn('pcd.component', ['invoice', 'proforma_invoice'])
            ->where('pcd.value_text', 'ilike', '%' . $keyword . '%')
            ->select(
                'pc.id as pc_id',
                'pc.doc_no',
                'pc.status',
                'pcd.component',
                'pcd.value_text as no_invoice',
                'pcd.index',
                'po.doc_no as no_po',
                'suppliers.name as supplier'
            )
            ->orderByDesc('pc.id')
            ->limit(20)
            ->get()
            ->map(function ($r) {
                return [
                    'pc_id'      => Hashids::encode($r->pc_id),
                    'doc_no'     => $r->doc_no,
                    'status'     => getStatusPC($r->status),
                    'component'  => $r->component == 'invoice' ? 'Invoice' : 'Proforma Invoice',
                    'no_invoice' => $r->no_invoice,
                    'no_po'      => $r->no_po,
                    'supplier'   => $r->supplier,
                    'url_show'   => route('purchasing.payment_completion.show', Hashids::encode($r->pc_id)),
                ];
            });

        return response()->json($results);
    }

    public function exportExcel(Request $request)
    {
        if (!Gate::allows('payment_completion_admin')) {
            return redirect()->back()->with('error', 'Tidak memiliki akses');
        }

        $filters = [
            'company_id'  => $request->company_id,
            'type_payment'=> $request->type_payment,
            'tgl_dari'    => $request->tgl_dari,
            'tgl_sampai'  => $request->tgl_sampai,
        ];

        $filename = 'Payment_Completion_' . now()->format('Ymd_His') . '.xlsx';

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\PaymentCompletionExport($filters),
            $filename
        );
    }
}
