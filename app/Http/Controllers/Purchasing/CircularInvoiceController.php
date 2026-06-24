<?php

namespace App\Http\Controllers\Purchasing;

use App\Models\CircularInvoice;
use App\User;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use App\Traits\UploadTrait;
use Vinkla\Hashids\Facades\Hashids;
use App\Mail\SendMailable;
use App\Models\Notification;
use App\Exports\PoExport;
use App\Mail\poPostMail;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Maatwebsite\Excel\Facades\Excel;
use Rap2hpoutre\FastExcel\FastExcel;
use Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Dompdf\Options;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class CircularInvoiceController extends Controller
{
    use UploadTrait;

    function __construct()
    {
        $this->middleware('permission:circular_invoice', ['only' => ['index','datatables','create','store','publish','show','cancel','edit','update','show','print','printMultiple','list','list_datatables']]);
    }

    public function index()
    {
        // Hitung Item
        $counts = DB::table('circular_invoices')
            ->selectRaw("
                SUM(CASE WHEN type_payment = 1 THEN 1 ELSE 0 END) AS cbd,
                SUM(CASE WHEN type_payment = 2 THEN 1 ELSE 0 END) AS cod,
                SUM(CASE WHEN type_payment = 3 THEN 1 ELSE 0 END) AS dp,
                SUM(CASE WHEN type_payment = 4 THEN 1 ELSE 0 END) AS selesai
            ")
            ->first();

        $totalCBD     = (int)($counts->cbd ?? 0);
        $totalCOD     = (int)($counts->cod ?? 0);
        $totalDP      = (int)($counts->dp ?? 0);
        $totalSelesai = (int)($counts->selesai ?? 0);

        // Ambil Filter Flash; Jika Tidak Ada → null (Tampil Semua Data)
        $filters = session()->pull('filters', [
            'status'       => '',
            'type_payment' => '',
            'company_id'   => '',
            'po_up_20m'    => '',
        ]);

        // Data Companies
        $companies = DB::table('companies')
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();

        return view('purchase.circular_invoice.index', compact(
            'totalCBD', 'totalCOD', 'totalDP', 'totalSelesai', 'filters', 'companies'));
    }

    public function datatables(Request $request)
    {
        $result = DB::table('circular_invoices')
        ->select(
                'circular_invoices.doc_no as no_si',
                'circular_invoices.id AS CIID',
                'circular_invoices.due_date_payment as tgl_tempo_pembayaran',
                'circular_invoices.status AS status',
                'circular_invoices.created_at AS tgl_pembuatan',
                'circular_invoices.invoice_number_ext AS no_invoice_ext',
                'circular_invoices.date_invoice_ext AS date_invoice_ext',
                'circular_invoices.type_payment AS type_payment',
                'po.doc_no as no_po',
                'po.id as POID',
                'users.name AS user_nama',
                'purchase_requisitions.doc_no AS no_pr',
                'purchase_requisitions.id AS PRID',
                'companies.name as company_nama',
                )
                ->leftJoin('users', 'users.id', '=', 'circular_invoices.created_by')
                ->leftJoin('po', 'po.id', '=', 'circular_invoices.po_id')
                ->leftJoin('companies', 'companies.id', '=', 'po.company_id')
                ->leftJoin('purchase_requisitions', 'purchase_requisitions.id', '=', 'po.purchase_id')
                ->leftJoin('purchases', 'purchase_requisitions.purchase_id', '=', 'purchases.id')
                ->orderBy('circular_invoices.created_at', 'DESC');

        // ====== Filter type_payment ======
        if ($request->filled('type_payment')) {
            $result->where('circular_invoices.type_payment', (int) $request->type_payment);
        }

        // ====== Filter companies ======
        if ($request->filled('company_id')) {
            $result->where('companies.id', (int) $request->company_id);
            // $result->where('po.company_id', (int) $request->company_id);
        }

        // ====== Filter status ======
        if ($request->status !== null && $request->status !== '') {
            $result->where('circular_invoices.status', (int) $request->status);
        }

        // ====== Filter PO payment_amount < & > 20.000.000 ======
        if ($request->has('po_up_20m') && $request->po_up_20m !== '') {
            if ((string)$request->po_up_20m === '1') {
                $result->where('po.payment_amount', '>', 20000000);
            } elseif ((string)$request->po_up_20m === '0') {
                $result->where('po.payment_amount', '<', 20000000);
            }
        }

        return datatables()->of($result)
        ->addColumn('action', function ($result) {
            $hash_id = \Vinkla\Hashids\Facades\Hashids::encode($result->CIID);

            // Route Tombol Fungsi
            $url_show       = route('purchasing.circular_invoice.show', $hash_id);
            $url_edit       = route('purchasing.circular_invoice.edit', $hash_id);
            $url_print      = route('purchasing.circular_invoice.print', $hash_id);
            $url_publish    = route('purchasing.circular_invoice.publish', $hash_id);
            $url_cancel     = route('purchasing.circular_invoice.cancel', $hash_id);

            // Tombol Fungsi
            $btn_show = '
                <a href="' . $url_show . '" target="_blank" title="Lihat Data" data-toggle="tooltip" class="btn btn-outline">
                    <i class="ti-eye icon-lg"></i>
                </a>';

            $btn_print = '
                <a href="#" onclick="openPrintWindow(\'' . $url_print . '\')" title="Print Data" data-toggle="tooltip" class="btn btn-outline">
                    <i class="ti-printer icon-lg"></i>
                </a>
                <script>
                    function openPrintWindow(url) {
                        var printWindow = window.open(url, "_blank", "width=1000,height=800");
                        printWindow.focus();
                        printWindow.onload = function() {
                            printWindow.print();
                        };
                    }
                </script>
            ';

            $btn_edit = '
                <a href="' . $url_edit . '" title="Edit Data" data-toggle="tooltip" class="btn btn-outline">
                    <i class="ti-pencil-alt icon-lg"></i>
                </a>';

            $btn_publish = '
                <button type="button"
                        class="btn btn-outline text-success btn-publish"
                        data-url="'. $url_publish .'"
                        data-doc="'. e($result->no_si) .'"
                        title="Publish Data" data-toggle="tooltip">
                    <i class="ti-new-window icon-lg"></i>
                </button>';

            $btn_cancel = '
                <button type="button"
                        class="btn btn-outline text-danger btn-cancel"
                        data-url="'. $url_cancel .'"
                        data-doc="'. e($result->no_si) .'"
                        title="Cancel Data" data-toggle="tooltip">
                    <i class="ti-power-off icon-lg"></i>
                </button>';

            $btn_selesai = '<button type="button" class="btn btn-outline text-primary btn-set-selesai" title="Selesai"
                                data-toggle="modal" data-target="#modalSetSelesai" data-id="'. $hash_id .'" data-doc="'. e($result->no_si) .'">
                                <i class="ti-thumb-up icon-lg"></i>
                            </button>';
            
            // Kondisi Tombol Berdasarkan Status
            $buttons = '<div>';
            $buttons .= $btn_show;
            
            switch ($result->status) {
                case 0: // Draft
                    $buttons .= $btn_edit;
                    $buttons .= $btn_publish;
                    break;
                case 1: // Publish
                    $buttons .= $btn_print;
                    $buttons .= $btn_cancel;
                    $buttons .= $btn_selesai;
                    break;
                case 2: // Done
                    $buttons .= $btn_print;
                    break;
                default:
                    // $buttons .= '<span class="badge badge-secondary">Tidak Ada</span>';
                    break;
            }

            $buttons .= '</div>';

            return $buttons;
        })

        ->editColumn('no_po', function ($result) {
            $no_po = "<a target='_blank' href='".route('purchasing.po.show',Hashids::encode($result->POID))."' title='Detail PO' data-toggle='tooltip' ";
            if ($result == null && $result->approved != null) {
                $no_po .= "style='font-weight:bold;'";
            }
            $no_po .= ">".$result->no_po."</a>";
            return $no_po;
        })

        ->editColumn('no_pr', function ($result) {
            $no_pr = "<a target='_blank' href='".route('purchasing.pr.show',Hashids::encode($result->PRID))."' title='Detail PR' data-toggle='tooltip' ";
            if ($result == null && $result->approved != null) {
                $no_pr .= "style='font-weight:bold;'";
            }
            $no_pr .= ">".$result->no_pr."</a>";
            return $no_pr;
        })

        ->addColumn('status', function ($result) {
            $status = strtolower($result->status); 
                switch ($status) {
                    case 'publish':
                    case '1':
                        $badge = '<span class="badge badge-primary">Publish</span>';
                        break;
                    case 'draft':
                    case '0':
                        $badge = '<span class="badge badge-warning">Draft</span>';
                        break;
                    case 'cancle':
                    case '2':
                        $badge = '<span class="badge badge-success">Selesai</span>';
                        break;
                    default:
                        $badge = '<span class="badge badge-secondary">Unknown</span>';
                        break;
                }
            return $badge;
        })

        ->addColumn('type_payment', function ($result) {
            return getTypeBodyEmal($result->type_payment, null);
        })

        ->addColumn('CIID', function ($result) {
            return Hashids::encode($result->CIID); 
        })

        ->editColumn('date_invoice_ext', function ($result) {
            return $result->date_invoice_ext ? \Carbon\Carbon::parse($result->date_invoice_ext)->format('d/m/Y') : '';
        })

        ->editColumn('tgl_pembuatan', function ($result) {
            return $result->tgl_pembuatan ? \Carbon\Carbon::parse($result->tgl_pembuatan)->format('d/m/Y') : '';
        })

        ->editColumn('tgl_tempo_pembayaran', function ($result) {
            return $result->tgl_tempo_pembayaran ? \Carbon\Carbon::parse($result->tgl_tempo_pembayaran)->format('d/m/Y') : '';
        })

        ->rawColumns(['action','status', 'no_po','no_pr','type_payment'])
        ->make(true);
    }

    public function create(Request $request)
    {
        // Parsing Nilai PO Ynag Di Pilih
        $po_id = \Vinkla\Hashids\Facades\Hashids::decode($request->po);
        if (!$po_id) {
            return redirect()->route('purchase.circular_invoice')->with('error', 'PO Tidak Ada');
        }

        // Get Data PO Sesuai Yang Di Pilih
        // $list_po = DB::table('po')
        //     ->leftJoin('companies', 'companies.id', '=', 'po.company_id')
        //     ->leftJoin('suppliers', 'po.supplier_id', '=', 'suppliers.id')
        //     ->leftJoin('purchase_requisitions', 'purchase_requisitions.id', '=', 'po.purchase_id')
        //     ->leftJoin('circular_invoices', 'circular_invoices.po_id', '=', 'po.id')
        //     ->where('po.id', $po_id[0])
        //     ->select(
        //         'po.*',
        //         'po.payment_amount as harga_po',
        //         'companies.name as company_nama',
        //         'suppliers.name as supplier_nama',
        //         'purchase_requisitions.doc_no as pr_no',
        //     )
        //     ->first();

        
        $list_po = DB::table('po')
        ->leftJoinSub(
            DB::table('circular_invoices')
                ->select('po_id', DB::raw('SUM(payment_amount) AS total_ci'))
                ->groupBy('po_id'),
            'ci',
            'ci.po_id',
            '=',
            'po.id'
        )
        ->leftJoin('companies', 'companies.id', '=', 'po.company_id')
        ->leftJoin('suppliers', 'suppliers.id', '=', 'po.supplier_id')
        ->leftJoin('purchase_requisitions', 'purchase_requisitions.id', '=', 'po.purchase_id')
        ->where('po.id', $po_id[0])
        ->select(
            'po.*',
            'po.payment_amount AS harga_po',
            'companies.name AS company_nama',
            'suppliers.name AS supplier_nama',
            'purchase_requisitions.doc_no AS pr_no',
            // sisa bayar = harga_po - total CI; clamp ke 0 jika sudah over
            DB::raw('GREATEST(po.payment_amount - COALESCE(ci.total_ci, 0), 0) AS sisa_bayar'),
            DB::raw('COALESCE(ci.total_ci, 0) AS total_ci')
        )
        ->first();

        
        $currency = DB::table('po')
            ->where('id', $po_id[0])
            ->value('currency');

        // Get Data Sirkular Invoices Yang Berelasi PO Yang Di Pilih
        $invoices = DB::table('circular_invoices')
            ->leftJoin('users', 'circular_invoices.created_by', '=', 'users.id')
            ->leftJoin('po', 'po.id', '=', 'circular_invoices.po_id')
            ->leftJoin('purchase_requisitions', 'purchase_requisitions.id', '=', 'po.purchase_id')
            ->select(
                'circular_invoices.*',
                'users.name as nama_pembuat',
                'po.doc_no as po_no',
                'po.currency as mata_uang',
                'purchase_requisitions.doc_no as pr_no',
            )
            ->where('circular_invoices.po_id', $po_id[0])
            ->orderBy('circular_invoices.created_at', 'DESC')
            ->get();

        return view('purchase.circular_invoice.create', [
            'list_po' => $list_po,
            'po_id' => $po_id[0],
            'invoices' => $invoices,
            'currency' => $currency
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'po_id' => 'required|exists:po,id',
            'payment_amount' => 'required|numeric',
            'invoice_number_ext' => 'nullable|string',
            'date_invoice_ext' => 'nullable|date',
            'due_date_payment' => 'required|date',
            'date_received_invoice' => 'required|date',
            'tax_invoice' => 'required|string',
            'type_payment' => 'required|string'
            // 'date_delivery_note' => 'required|date',
        ]);

        // Inisial Perusahaan Relasi Table PO & Companies
        $company = DB::table('po')
            ->join('companies', 'companies.id', '=', 'po.company_id')
            ->select('companies.alias')
            ->where('po.id', $request->po_id)
            ->first();

        if (!$company) {
            return redirect()->back()->with('error', 'Perusahaan Tidak Ada');
        }

        $alias = strtoupper($company->alias);
        $periode = Carbon::now()->format('my');

        // Kondisi Hitung Jumlah Dokumen Dengan Perusahaan Sama
        $prefix = "SI-{$alias}-{$periode}-";

        $count = DB::table('circular_invoices')
            ->where('doc_no', 'like', $prefix . '%')
            ->count() + 1;

        // Auto Generate No Circular Invoice
        $doc_no = $prefix . str_pad($count, 5, '0', STR_PAD_LEFT);

        // Simpan Table Circular Invoices
        $invoice_id = DB::table('circular_invoices')->insertGetId([
            'doc_no'                => $doc_no,
            'po_id'                 => $request->po_id,
            'payment_amount'        => $request->payment_amount,
            'invoice_number_ext'    => $request->invoice_number_ext,
            'date_invoice_ext'      => $request->date_invoice_ext,
            'due_date_payment'      => $request->due_date_payment,
            'note'                  => $request->note,
            'date_received_invoice' => $request->date_received_invoice,
            'tax_invoice'           => $request->tax_invoice,
            'date_delivery_note'    => $request->due_date_payment,
            'type_payment'          => $request->type_payment,
            'status'                => 0,
            'created_by'            => auth()->user()->id,
            'created_at'            => now(),
            'updated_at'            => now(),
        ]);

        // Simpan Table Circular Invoice Histories
        DB::table('circular_invoice_histories')->insert([
            'circular_invoice_id' => $invoice_id,
            'message'             => 'Membuat Circular Invoice ' . $doc_no,
            'type'                => 'create',
            'created_by'          => auth()->id(),
            'created_at'          => now(),
        ]);

        return redirect()->route('purchasing.circular_invoice')->with('success', 'Sirkular Invoice berhasil di Tambah');
    }

    public function publish($hash_id)
    {
        $id = Hashids::decode($hash_id)[0] ?? null;

        if (!$id) {
            return redirect()->back()->with('error', 'ID tidak valid');
        }

        // Update Status -> Publish
        DB::table('circular_invoices')
            ->where('id', $id)
            ->update([
                'status' => 1,
                'updated_by' => auth()->id(),
                'updated_at' => now(),
            ]);

        // Get Nomor Sirkular Invoice
        $doc_no = DB::table('circular_invoices')
            ->where('id', $id)
            ->value('doc_no');

        // Simpan Table Circular Invoice Histories
        DB::table('circular_invoice_histories')->insert([
            'circular_invoice_id' => $id,
            'message'             => 'Mempublish Circular Invoice ' . ($doc_no ?? ''),
            'type'                => 'publish',
            'created_by'          => auth()->id(),
            'created_at'          => now(),
        ]);

        return redirect()->back()->with('success', 'Status berhasil diubah ke Publish');
    }

    public function cancel($id)
    {
        $id = \Vinkla\Hashids\Facades\Hashids::decode($id)[0];

        $invoice = DB::table('circular_invoices')->where('id', $id)->first();

        if (!$invoice) {
            return redirect()->back()->with('error', 'Data tidak ditemukan');
        }

        // Update Status -> Cancle
        DB::table('circular_invoices')
            ->where('id', $id)
            ->update([
                'status' => 0, // draft
                'updated_at' => now(),
                'updated_by' => auth()->user()->id ?? 0,
            ]);
        
        // Get Nomor Sirkular Invoice
        $doc_no = DB::table('circular_invoices')
            ->where('id', $id)
            ->value('doc_no');

        // Simpan Table Circular Invoice Histories
        DB::table('circular_invoice_histories')->insert([
            'circular_invoice_id' => $id,
            'message'             => 'Mencancel Circular Invoice ' . ($doc_no ?? ''),
            'type'                => 'cancel',
            'created_by'          => auth()->id(),
            'created_at'          => now(),
        ]);

        return redirect()->back()->with('success', 'Status berhasil diubah ke Draft');
    }

    public function edit($id)
    {
        $decoded = \Vinkla\Hashids\Facades\Hashids::decode($id);
        if (empty($decoded)) abort(404, 'ID tidak valid.');
        $ciId = $decoded[0];

        // Data Circular Invoice
        $invoice = DB::table('circular_invoices')->where('id', $ciId)->first();
        if (!$invoice) abort(404, 'Circular Invoice tidak ditemukan');

        // Dropdown PO 
        $po_options = DB::table('po')
            ->orderBy('created_at', 'desc')
            ->pluck('doc_no', 'id')
            ->toArray();

        // Detail PO Yang Dipilih 
        $po_detail = DB::table('po')
            ->leftJoin('companies', 'companies.id', '=', 'po.company_id')
            ->leftJoin('suppliers', 'suppliers.id', '=', 'po.supplier_id')
            ->leftJoin('purchase_requisitions', 'purchase_requisitions.id', '=', 'po.purchase_id')
            ->where('po.id', $invoice->po_id)
            ->select(
                'po.id',
                'po.doc_no as po_no',
                'po.payment_amount as harga_po',
                'po.currency',
                'companies.name as company_nama',
                'suppliers.name as supplier_nama',
                'purchase_requisitions.doc_no as pr_no',
                'purchase_requisitions.created_at as pr_tgl'
            )
            ->first();

        $currency = DB::table('po')
            ->where('id', $invoice->po_id)
            ->value('currency');

        return view('purchase.circular_invoice.edit', [
            'invoice'   => $invoice,
            'list_po'   => $po_options,  
            'po_detail' => $po_detail,   
            'currency'  => $currency,
        ]);
    }

    public function update(Request $request, $id)
    {
        // Decode ID
        $decoded = Hashids::decode($id);
        if (empty($decoded)) {
            abort(404, 'ID tidak valid.');
        }
        $id = $decoded[0];

        // Cek Data Invoice
        $invoice = DB::table('circular_invoices')->where('id', $id)->first();
        if (!$invoice) {
            abort(404, 'Circular Invoice tidak ditemukan');
        }

        // Validasi
        $request->validate([
            'po_id'                 => 'required|exists:po,id', 
            'invoice_number_ext'    => 'nullable|string|max:255',
            'date_invoice_ext'      => 'nullable|date',
            'due_date_payment'      => 'nullable|date',
            'payment_amount'        => 'nullable|numeric',
            'note'                  => 'nullable|string',
            'date_received_invoice' => 'required|date',
            'tax_invoice'           => 'required|string',
            'date_delivery_note'    => 'required|date',
            'type_payment'          => 'required',
        ]);

        // Update Data
        DB::table('circular_invoices')
            ->where('id', $id)
            ->update([
                'po_id'                 => $request->po_id,
                'invoice_number_ext'    => $request->invoice_number_ext,
                'date_invoice_ext'      => $request->date_invoice_ext,
                'due_date_payment'      => $request->due_date_payment,
                'payment_amount'        => $request->payment_amount,
                'note'                  => $request->note,
                'date_received_invoice' => $request->date_received_invoice,
                'tax_invoice'           => $request->tax_invoice,
                'date_delivery_note'    => $request->date_delivery_note,
                'type_payment'          => $request->type_payment,
                'updated_by'            => auth()->user()->id,
                'updated_at'            => now(),
            ]);
        
        // Get Nomor Sirkular Invoice
        $doc_no = DB::table('circular_invoices')
            ->where('id', $id)
            ->value('doc_no');

        // Simpan Table Circular Invoice Histories
        DB::table('circular_invoice_histories')->insert([
            'circular_invoice_id' => $id,
            'message'             => 'Mengedit Circular Invoice ' . ($doc_no ?? ''),
            'type'                => 'edit',
            'created_by'          => auth()->id(),
            'created_at'          => now(),
        ]);

        return redirect()
            ->route('purchasing.circular_invoice')->with('success', 'Sirkular Invoice berhasil di Edit');
    }

    public function show($id)
    {
        // Decode
        $decoded = Hashids::decode($id);
        if (empty($decoded)) {
            abort(404, 'ID tidak valid.');
        }

        $id = $decoded[0];

        // Data
        $invoice = DB::table('circular_invoices')
            ->leftJoin('po', 'circular_invoices.po_id', '=', 'po.id')
            ->leftJoin('users', 'circular_invoices.created_by', '=', 'users.id')
            ->leftJoin('companies', 'po.company_id', '=', 'companies.id')
            ->leftJoin('suppliers', 'po.supplier_id', '=', 'suppliers.id')
            ->leftJoin('purchase_requisitions', 'purchase_requisitions.id', '=', 'po.purchase_id')
            ->leftJoin('purchases', 'purchase_requisitions.purchase_id', '=', 'purchases.id')
            ->leftJoin('payment_terms', 'payment_terms.id', '=', 'po.payment_term_id')
            ->select(
                'circular_invoices.*',
                'circular_invoices.status as status_si',
                'po.doc_no as po_number',
                'po.created_at as po_tgl',
                'po.currency as po_mata_uang',
                'users.name as nama_pembuat',
                'companies.name AS nama_pt',
                'suppliers.name AS nama_supplier',
                'purchase_requisitions.doc_no AS pr_no',
                'purchase_requisitions.created_at AS pr_tgl',
                'payment_terms.name as payment_terms_nama',
            )
            ->where('circular_invoices.id', $id)
            ->first();

        // Jika Tidak Ada
        if (!$invoice) {
            abort(404, 'Circular Invoice tidak Ada');
        }

        // Data Histories
        $histories = DB::table('circular_invoice_histories')
            ->leftJoin('users', 'circular_invoice_histories.created_by', '=', 'users.id')
            ->where('circular_invoice_id', $id)
            ->orderBy('circular_invoice_histories.created_at', 'desc')
            ->select(
                'circular_invoice_histories.message',
                'circular_invoice_histories.type',
                'circular_invoice_histories.created_at',
                'users.name as user_name'
            )
            ->get();

        return view('purchase.circular_invoice.show', compact('invoice','histories'));
    }

    public function print($id)
    {
        // Decode
        $decoded = Hashids::decode($id);
        if (empty($decoded)) {
            abort(404, 'ID tidak valid.');
        }
        $id = $decoded[0]; 

        // Data
        $invoice = DB::table('circular_invoices')
            ->leftJoin('po', 'circular_invoices.po_id', '=', 'po.id')
            ->leftJoin('users', 'circular_invoices.created_by', '=', 'users.id')
            ->leftJoin('companies', 'po.company_id', '=', 'companies.id')
            ->leftJoin('suppliers', 'po.supplier_id', '=', 'suppliers.id')
            ->leftJoin('purchase_requisitions', 'purchase_requisitions.id', '=', 'po.purchase_id')
            ->leftJoin('purchases', 'purchase_requisitions.purchase_id', '=', 'purchases.id')
            ->leftJoin('payment_terms', 'payment_terms.id', '=', 'po.payment_term_id')
            ->select(
                'circular_invoices.*',
                'po.doc_no as po_number',
                'po.created_at as po_tgl',
                'po.currency as po_mata_uang',
                'users.name as nama_pembuat',
                'companies.name AS nama_pt',
                'suppliers.name AS nama_supplier',
                'purchase_requisitions.doc_no AS pr_no',
                'purchase_requisitions.created_at AS pr_tgl',
                'payment_terms.name as payment_terms_nama',
            )
            ->where('circular_invoices.id', $id)
            ->first();

        if (!$invoice) {
            abort(404, 'Circular Invoice tidak ditemukan.');
        }

        $pdf = Pdf::loadView('purchase.circular_invoice.print', compact('invoice'))->setPaper('a4');
        return $pdf->stream('Sirkular-Invoice-' . $invoice->id . '.pdf');
    }

    public function printMultiple(Request $request)
    {
        $ids = explode(',', $request->input('ids', ''));

        // Decode 
        $decodedIds = collect($ids)->map(function ($hashid) {
            return \Hashids::decode($hashid)[0] ?? null;
        })->filter()->all();

        if (empty($decodedIds)) {
            return redirect()->back()->with('error', 'Tidak Ada Data Terpilih');
        }

        $invoices = DB::table('circular_invoices')
            ->whereIn('circular_invoices.id', $decodedIds)
            ->leftJoin('po', 'circular_invoices.po_id', '=', 'po.id')
            ->leftJoin('users', 'circular_invoices.created_by', '=', 'users.id')
            ->leftJoin('companies', 'po.company_id', '=', 'companies.id')
            ->leftJoin('suppliers', 'po.supplier_id', '=', 'suppliers.id')
            ->leftJoin('purchase_requisitions', 'purchase_requisitions.id', '=', 'po.purchase_id')
            ->leftJoin('purchases', 'purchase_requisitions.purchase_id', '=', 'purchases.id')
            ->leftJoin('payment_terms', 'payment_terms.id', '=', 'po.payment_term_id')
            ->select(
                'circular_invoices.*',
                'po.doc_no as po_number',
                'po.created_at as po_tgl',
                'po.currency as po_mata_uang',
                'users.name as nama_pembuat',
                'companies.name AS nama_pt',
                'suppliers.name AS nama_supplier',
                'purchase_requisitions.doc_no AS pr_no',
                'purchase_requisitions.created_at AS pr_tgl',
                'payment_terms.name as payment_terms_nama'
            )
            ->get();

        if ($invoices->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak Ada Data Terpilih');
        }

        $pdf = \PDF::loadView('purchase.circular_invoice.print_multiple', compact('invoices'))->setPaper('a4');
        return $pdf->stream('Sirkular-Invoices-Multiple.pdf');
    }

    public function search(Request $request)
    {
        // Ambil Data Filter Dari Form
        $filters = [
            'type_payment' => $request->input('type_payment') !== '' ? $request->input('type_payment') : '',
            'status'       => $request->input('status')       !== '' ? $request->input('status')       : '',
            'company_id'   => $request->input('company_id')   !== '' ? $request->input('company_id')   : '',
            'po_up_20m'    => $request->input('po_up_20m')    !== '' ? $request->input('po_up_20m')    : '',
        ];

        return redirect()->route('purchasing.circular_invoice')->with('filters', $filters);
    }

    public function selesai(Request $request)
    {
        $validated = $request->validate([
            'id'             => 'required|string',
            'recipient_name' => 'required|string|max:255',
            'receipt_date'   => 'required|date_format:Y-m-d',
            'receipt_note'   => 'nullable|string',
        ]);

        // Decode Hashid 
        $decoded = Hashids::decode($validated['id']);
        $id = $decoded[0] ?? null;

        if (!$id) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'ID tidak valid'], 422);
            }
            return back()->with('error', 'ID tidak valid');
        }

        DB::beginTransaction();
        try {
            $exists = DB::table('circular_invoices')->where('id', $id)->exists();
            if (!$exists) {
                DB::rollBack();
                if ($request->expectsJson()) {
                    return response()->json(['success' => false, 'message' => 'Data Tidak Ditemukan'], 404);
                }
                return back()->with('error', 'Data Tidak Ditemukan');
            }

            DB::table('circular_invoices')
                ->where('id', $id)
                ->update([
                    'status'         => 2, 
                    'recipient_name' => $validated['recipient_name'],
                    'receipt_date'   => $validated['receipt_date'],
                    'receipt_note'   => $validated['receipt_note'] ?? null,
                    'updated_by'     => auth()->id(),
                    'updated_at'     => now(),
                ]);

            $doc_no = DB::table('circular_invoices')->where('id', $id)->value('doc_no');

            DB::table('circular_invoice_histories')->insert([
                'circular_invoice_id' => $id,
                'message'             => 'Menyelesaikan Circular Invoice ' . ($doc_no ?? ''),
                'type'                => 'selesai',
                'created_by'          => auth()->id(),
                'created_at'          => now(),
            ]);

            DB::commit();

            if ($request->expectsJson()) {
                return response()->json([
                    'success'  => true,
                    'message'  => 'Sirkular Invoice berhasil di Selesaikan',
                    'redirect' => route('purchasing.circular_invoice'), 
                ]);
            }

            return redirect()
                ->route('purchasing.circular_invoice')
                ->with('success', 'Sirkular Invoice berhasil di Selesaikan');

        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('Selesai CI error', ['id_hash' => $validated['id'] ?? null, 'decoded_id' => $id, 'e' => $e]);

            $msg = config('app.debug') ? $e->getMessage() : 'Gagal Simpan Data';

            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 500);
            }
            return back()->with('error', $msg);
        }
    }

    // ========== HALAMAN PILIH PO ==========

    // Index
    public function list()
    {   
        // Ambil Filter Flash; Jika Tidak Ada → null (Tampil Semua Data)
        $filters = session()->pull('filters', [
            'status'       => '',
            'po_up_20m'    => '',
        ]);

        return view('purchase.circular_invoice.list', compact('filters'));
    }

    public function list_po()
    {
        $result = DB::table('po')
        ->select(
            'suppliers.name AS supplier_nama',
            'companies.name AS company_nama',
        )
        ->leftJoin('suppliers', 'suppliers.id', '=', 'po.supplier_id')
        ->leftJoin('companies', 'companies.id', '=', 'po.company_id')
        ->get();
    }

    public function search_po(Request $request)
    {
        // Ambil Data Filter Dari Form
        // $filters = [
        //     'status'       => $request->input('status')       !== '' ? $request->input('status')       : '',
        //     'po_up_20m'    => $request->input('po_up_20m')    !== '' ? $request->input('po_up_20m')    : '',
        // ];

        $filters = [
            'status'    => $request->has('status') ? (string)$request->input('status') : '',
            'po_up_20m' => $request->has('po_up_20m') ? (string)$request->input('po_up_20m') : '',
        ];

        return redirect()->route('purchasing.circular_invoice.list')->with('filters', $filters);
    }

    public function list_datatables(Request $request)
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
                DB::raw('COUNT(circular_invoices.id) as total_si'),
                DB::raw('SUM(circular_invoices.payment_amount) as total_harga_si'),
                DB::raw('(COUNT(circular_invoices.id) - SUM(circular_invoices.payment_amount)) as total_harga_selisih'),
                )
                ->leftJoin('suppliers', 'suppliers.id', '=', 'po.supplier_id')
                ->leftJoin('companies', 'companies.id', '=', 'po.company_id')
                ->leftJoin('purchase_requisitions', 'purchase_requisitions.id', '=', 'po.purchase_id')
                ->leftJoin('circular_invoices', 'circular_invoices.po_id', '=', 'po.id')
                ->whereIn('po.status', [2, 4, 5])
                ->groupBy(
                    'po.id', 'po.doc_no', 'po.created_at',
                    'suppliers.name', 'companies.name',
                    'purchase_requisitions.doc_no', 'purchase_requisitions.created_at'
                )
                ->havingRaw('COALESCE(SUM(circular_invoices.payment_amount), 0) != po.payment_amount') // Menampilkan PO Yang Status UnMatch
                ->orderBy('po.created_at', 'DESC');
        
        // ====== Filter status ======
        if ($request->status !== null && $request->status !== '') {
            $result->where('po.status', (int) $request->status);
        }

        // === Filter PO < & > 20.000.000 ===
        if ($request->has('po_up_20m') && $request->po_up_20m !== '') {
            if ((string)$request->po_up_20m === '1') {
                $result->where('po.payment_amount', '>', 20000000);
            } elseif ((string)$request->po_up_20m === '0') {
                $result->where('po.payment_amount', '<', 20000000);
            }
        }

        return datatables()->of($result)
        ->addColumn('jumlah_si', function ($result) {
                return $result->total_si . ' SI';
        })

        ->addColumn('action', function ($result) {
            $hash_id = \Vinkla\Hashids\Facades\Hashids::encode($result->POID);

            // Route Tombol
            $url_create = route('purchasing.circular_invoice.create', ['po' => $hash_id]);
            // $url_view = route('purchasing.po.show', ['po' => $hash_id]);

            // Tombol
            $btn_create = '
                <a href="' . $url_create . '" title="Buat Sirkular Invoice" data-toggle="tooltip" class="btn btn-success btn-sm">
                    <i class="ti-file"></i> BUAT SI
                </a>';

            return $btn_create;
        })

        ->editColumn('po_no', function ($result) {
            $po_no = "<a target='_blank' href='".route('purchasing.po.show',Hashids::encode($result->POID))."' title='Detail PO' data-toggle='tooltip' ";
            if ($result == null && $result->approved != null) {
                $po_no .= "style='font-weight:bold;'";
            }
            $po_no .= ">".$result->po_no."</a>";
            return $po_no;
        })

        ->editColumn('harga_po', function ($result) {
            return $result->mata_uang . ' ' . number_format($result->harga_po, 2, ',', '.');
        })

        ->editColumn('total_harga_si', function ($result) {
            return $result->mata_uang . ' ' . number_format($result->total_harga_si, 2, ',', '.');
        })

        ->editColumn('status_harga', function ($result) {
            if ((float) $result->total_harga_si == (float) $result->harga_po) {
                return '<span style="display:inline-block; width:12px; height:12px; background-color:#28a745; border-radius:50%;"></span> Match';
            } else {
                return '<span style="display:inline-block; width:12px; height:12px; background-color:#dc3545; border-radius:50%;"></span> Unmatch'; // Merah solid
            }
        })

        ->addColumn('po_status', function ($result) {
            $po_status = strtolower($result->po_status); 
                switch ($po_status) {
                    case 'issued':
                    case '2':
                        $badge = '<span class="badge badge-primary">Issued</span>';
                        break;
                    case 'parsial':
                    case '4':
                        $badge = '<span class="badge badge-warning">Parsial</span>';
                        break;
                    case 'done':
                    case '5':
                        $badge = '<span class="badge badge-success">Done</span>';
                        break;
                    default:
                        $badge = '<span class="badge badge-secondary">Unknown</span>';
                        break;
                }
            return $badge;
        })

        ->rawColumns(['action','po_no','status_harga','po_status'])
        ->make(true);
    }

}