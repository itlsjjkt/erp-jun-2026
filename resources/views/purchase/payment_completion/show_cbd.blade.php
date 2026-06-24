@extends('layouts.app')

@section('page-header')
    Payment Completion
@endsection

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('purchasing.payment_completion') }}">Payment Completion</a></li>
        <li class="breadcrumb-item active" aria-current="page">Payment Completion Show</a></li>
    </ol>
@endsection

{{-- Helper --}}
@php
    use Carbon\Carbon;

    $fmtDate = function ($v) {
        return !empty($v) ? Carbon::parse($v)->format('d F Y H:i') : '-';
    };
    $fmtDateDay = function ($v) {
        return !empty($v) ? Carbon::parse($v)->format('d F Y') : '-';
    };

    $fmtNum = function ($v, $dec = 2) {
        return is_numeric($v) ? number_format($v, $dec) : 'N/A';
    };

    $pairs = $pairs ?? [];

    $badgeClass = function ($status) {
        return match ($status) {
            'Verified'         => 'badge badge-success',
            'Partial Verified' => 'badge badge-warning',
            'Unverified', '-'  => 'badge badge-danger',
            'Pending'          => 'badge badge-warning',
            default            => 'badge badge-light text-dark',
        };
    };
@endphp

{{-- CARI HARGA PO --}}
@php
    $total = 0;
    $po = getDataByID('po', $payment_completion->po_id);
    use App\Models\PurchaseOrder;
    $po_items = PurchaseOrder::getProductItem($po->id);
@endphp
@foreach ($po_items as $item)
    @php $total += $item->price * $item->qty - (($item->price * $item->qty) * $item->discount / 100); @endphp
@endforeach
@php
    if ($po->discount_item == false) {
        if ($po->discount_type == 1) {
            $po->discount_amount = $total * ((float)$po->discount_amount / 100);
        }
        $netto = $total - (float)$po->discount_amount;
    } else {
        $netto = $total;
    }
    if ((float)$po->send_expense_ppn == 1 || (float)$po->send_expense_ppn == 11) {
        $send_expense_ppn = (11 / 100) * (float)$po->send_expense;
        $po->send_expense = (float)$send_expense_ppn + (float)$po->send_expense;
    }
    $po->ppn = $netto * (float)$po->ppn / 100;
    $po->pph = $netto * (float)$po->pph / 100;
    $payment_amount = $netto - (float)$po->pph + (float)$po->ppn + (float)$po->send_expense;
@endphp

@push('css')
    <link rel="stylesheet" href="https://unpkg.com/trix@2.0.8/dist/trix.css">
@endpush
@push('js')
    <script src="https://unpkg.com/trix@2.0.8/dist/trix.umd.min.js"></script>
@endpush

@section('content')
<div class="mB-40">
    <div class="bgc-white p-30 bd">
        @php use Illuminate\Support\Facades\Gate; @endphp

        {{-- ── TOMBOL AKSI ── --}}
        <div class="row justify-content-end" style="margin-top:-20px; padding-bottom:10px;">
            <div class="col-sm-6">
                <a title="Kembali" href="{{ route('purchasing.payment_completion') }}" class="nav-link">
                    <i class="ti-arrow-left"></i> Kembali
                </a>
            </div>
            <div class="col-sm-6 d-flex justify-content-end mb-2" style="margin-top:10px;">

                @if (Gate::allows('payment_completion_admin'))
                    @if ($payment_completion->status == 0 || $payment_completion->status == 4)
                        {{-- EDIT --}}
                        <a class="btn btn-outline"
                            href="{{ route('purchasing.payment_completion.edit', Hashids::encode($payment_completion->id)) }}"
                            title="Edit" data-toggle="tooltip">
                            <i class="ti-pencil-alt icon-sm" style="font-weight:bold;"> Edit</i>
                        </a>
                        {{-- PUBLISH --}}
                        <form action="{{ route('purchasing.payment_completion.publish', Hashids::encode($payment_completion->id)) }}"
                            method="POST" style="display:inline;">
                            @csrf
                            <button type="submit" class="btn btn-outline btnPublish" title="Publish">
                                <i class="ti-new-window icon-sm" style="font-weight:bold; color:green;"> Publish</i>
                            </button>
                        </form>
                        {{-- REJECT --}}
                        <button type="button" class="btn btn-outline text-danger btn-reject"
                            data-url="{{ route('purchasing.payment_completion.reject', Hashids::encode($payment_completion->id)) }}"
                            data-doc="{{ $payment_completion->doc_no }}"
                            data-id="{{ Hashids::encode($payment_completion->id) }}"
                            title="Reject" data-toggle="tooltip">
                            <i class="ti-power-off icon-sm" style="font-weight:bold; color:red;"> Reject</i>
                        </button>                        
                    @endif

                    {{-- PRINT --}}
                    <a class="btn btn-outline mr-3 btnPrint" href="#"
                        onclick="openPrintWindow('{{ route('purchasing.payment_completion.print', Hashids::encode($payment_completion->id)) }}')"
                        title="Print">
                        Print <i class="ti-printer icon-sm"></i>
                    </a>

                    {{-- CHANGE RELATION PO --}}
                    @php
                        $CheckPo = DB::table('po')->where('po.id', '=', $payment_completion->po_id)->whereIn('po.status', [8])->get();
                    @endphp
                    @if (count($CheckPo) > 0 && $payment_completion->status != 5 && $payment_completion->status != 3)
                        <button type="button" class="btn btn-outline text-danger btn-change-po"
                            data-url="{{ route('purchasing.payment_completion.change_relation_po', Hashids::encode($payment_completion->id)) }}"
                            title="Change Relation Po" data-toggle="tooltip">
                            <i class="ti-reload icon-sm" style="font-weight:bold; color:rgb(255,0,191);"> Change Relation</i>
                        </button>
                    @endif

                    {{-- SET DONE (untuk payment_completion_admin) --}}
                    @if ($payment_completion->status == 1 || $payment_completion->status == 4)
                        @php
                            $detailStatAdmin = DB::table('payment_completion_details')
                                ->where('pc_id', $payment_completion->id)
                                ->selectRaw('COUNT(*) AS total_detail, SUM(CASE WHEN verify_status = 1 THEN 1 ELSE 0 END) AS verified_detail')
                                ->first();
                            $totalDetailAdmin    = $detailStatAdmin->total_detail ?? 0;
                            $verifiedDetailAdmin = $detailStatAdmin->verified_detail ?? 0;
                            $allLockedAdmin      = $totalDetailAdmin > 0 && $totalDetailAdmin == $verifiedDetailAdmin;

                            $totalNilaiAdmin = DB::table('payment_completion_details')
                                ->where('pc_id', $payment_completion->id)
                                ->where('component', 'nilai_invoice')
                                ->sum('value_number');

                            $poAdmin    = getDataByID('po', $payment_completion->po_id);
                            $poItemsAdm = \App\Models\PurchaseOrder::getProductItem($poAdmin->id);
                            $poTotalAdm = 0;
                            foreach ($poItemsAdm as $item) {
                                $poTotalAdm += $item->price * $item->qty - (($item->price * $item->qty) * $item->discount / 100);
                            }
                            if ($poAdmin->discount_item == false) {
                                $discAmtAdm = $poAdmin->discount_type == 1
                                    ? $poTotalAdm * ((float)$poAdmin->discount_amount / 100)
                                    : (float)$poAdmin->discount_amount;
                                $nettoAdm = $poTotalAdm - $discAmtAdm;
                            } else {
                                $nettoAdm = $poTotalAdm;
                            }
                            if ((float)$poAdmin->send_expense_ppn == 1 || (float)$poAdmin->send_expense_ppn == 11) {
                                $poAdmin->send_expense += (11 / 100) * (float)$poAdmin->send_expense;
                            }
                            $ppnAdm          = $nettoAdm * (float)$poAdmin->ppn / 100;
                            $pphAdm          = $nettoAdm * (float)$poAdmin->pph / 100;
                            $paymentAmtAdmin  = $nettoAdm - $pphAdm + $ppnAdm + (float)$poAdmin->send_expense;
                            $invoiceSuffAdmin = $totalNilaiAdmin >= $paymentAmtAdmin;
                        @endphp

                        @if ($allLockedAdmin && $invoiceSuffAdmin)
                            <button type="button"
                                class="btn btn-outline text-success btn-done"
                                data-url="{{ route('purchasing.payment_completion.done', Hashids::encode($payment_completion->id)) }}"
                                data-doc="{{ $payment_completion->doc_no }}"
                                title="Set Done" data-toggle="tooltip">
                                <i class="ti-thumb-up icon-sm" style="font-weight:bold;color:green;"> Set Done</i>
                            </button>
                        @elseif ($allLockedAdmin && !$invoiceSuffAdmin)
                            <span class="badge badge-warning" style="padding:6px 10px; font-size:0.8rem;"
                                title="Total Invoice belum mencapai Total Harga PO" data-toggle="tooltip">
                                <i class="ti-alert"></i> Invoice Belum Cukup
                            </span>
                        @endif
                    @endif

                @elseif (Gate::allows('approval_pc'))
                    @php
                        $detailStat = DB::table('payment_completion_details')
                            ->where('pc_id', $payment_completion->id)
                            ->selectRaw('COUNT(*) AS total_detail, SUM(CASE WHEN verify_status = 1 THEN 1 ELSE 0 END) AS verified_detail')
                            ->first();
                        $total_detail    = $detailStat->total_detail ?? 0;
                        $verified_detail = $detailStat->verified_detail ?? 0;
                    @endphp

                    @if ($payment_completion->status == 1 && $total_detail == $verified_detail && $total_detail != 0)
                        {{-- TO PURCHASING PENDING --}}
                        <button type="button"
                            class="btn btn-outline text-primary btn-tambahkelengkapan"
                            data-url="{{ route('purchasing.payment_completion.tambahkelengkapan', Hashids::encode($payment_completion->id)) }}"
                            data-id="{{ Hashids::encode($payment_completion->id) }}"
                            title="Returned to Pending Purchasing" data-toggle="tooltip">
                            <i class="ti-back-left icon-sm" style="font-weight:bold;color:purple;"> to Purchasing</i>
                        </button>
                        {{-- SET DONE --}}
                        <button type="button"
                            class="btn btn-outline text-success btn-done"
                            data-url="{{ route('purchasing.payment_completion.done', Hashids::encode($payment_completion->id)) }}"
                            data-doc="{{ $payment_completion->doc_no }}"
                            title="Done" data-toggle="tooltip">
                            <i class="ti-thumb-up icon-sm" style="font-weight:bold;color:green;"> Set Done</i>
                        </button>
                    @endif

                    @if ($payment_completion->status == 2)
                        {{-- PRINT --}}
                        <a class="btn btn-outline mr-3 btnPrint" href="#"
                            onclick="openPrintWindow('{{ route('purchasing.payment_completion.print', Hashids::encode($payment_completion->id)) }}')"
                            title="Print">
                            Print <i class="ti-printer icon-sm"></i>
                        </a>
                    @endif
                @endif
            </div>
        </div>

        {{-- ── NAV TABS ── --}}
        <div class="d-block">
            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" data-toggle="tab" href="#tab1" role="tab">Payment Completion</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#tab-bukti" role="tab">
                        Bukti Pembayaran
                        @if(isset($paymentDetails) && $paymentDetails->flatten()->count() > 0)
                            <span class="badge badge-primary">{{ $paymentDetails->flatten()->count() }}</span>
                        @endif
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#tab2" role="tab">History</a>
                </li>
            </ul>
        </div>

        <div class="tab-content mT-30">

            {{-- ── TAB 1: PAYMENT COMPLETION ── --}}
            <div class="tab-pane active" id="tab1" role="tabpanel">
                <h6 style="font-weight:bold; text-decoration:underline; text-align:center;">
                    {{ $payment_completion->doc_no ?? '-' }}
                </h6>

                <div class="row mt-3" style="margin-left:20px;">
                    <div class="col-sm-6">
                        <div class="row">
                            <label class="col-sm-3">Company</label>
                            <div class="col-sm-9">: {{ strtoupper($payment_completion->nama_company ?? '-') }}</div>
                        </div>
                        <div class="row">
                            <label class="col-sm-3">Type PC</label>
                            <div class="col-sm-9">: {{ getTypePC($payment_completion->type_payment, 'raw') }}</div>
                        </div>
                        <div class="row">
                            <label class="col-sm-3">Dibuat Oleh</label>
                            <div class="col-sm-9">: {{ $payment_completion->nama_pembuat ?? '-' }}
                                [ {{ date('d M Y', strtotime($payment_completion->tgl_pc)) }} ]</div>
                        </div>
                        <div class="row">
                            <label class="col-sm-3">Status PC</label>
                            <div class="col-sm-9">: {!! getStatusPC($payment_completion->status) !!}</div>
                        </div>
                        <div class="row">
                            <label class="col-sm-3">Notes PC</label>
                            <div class="col-sm-9">: {{ $payment_completion->notes ?? '-' }}</div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="row">
                            <label class="col-sm-3">Nomor PR</label>
                            <div class="col-sm-9">: {{ $payment_completion->no_pr ?? '-' }}
                                [ {{ date('d M Y', strtotime($payment_completion->tgl_pr)) }} ]</div>
                        </div>
                        <div class="row">
                            <label class="col-sm-3">Nomor PO</label>
                            <div class="col-sm-9">:
                                <a href="{{ route('purchasing.po.show', Hashids::encode($payment_completion->po_id)) }}"
                                    target="_blank" title="Show PO">
                                    {{ $payment_completion->no_po ?? '-' }}
                                </a>
                                [ {{ date('d M Y', strtotime($payment_completion->tgl_po)) }} ]
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-sm-3">Supplier</label>
                            <div class="col-sm-9">: {{ $payment_completion->nama_supplier ?? '-' }}</div>
                        </div>
                        <div class="row">
                            <label class="col-sm-3"><strong>Total Harga PO</strong></label>
                            <div class="col-sm-9">:
                                <strong>{{ $po->currency . ' ' . $fmtNum($payment_amount,($po->currency == 'IDR'?0:2)) }}</strong>
                            </div>
                        </div>
                    </div>
                </div>

                @if ($payment_completion->status == 3)
                    <hr>
                    <div class="alert alert-danger">
                        <strong>REJECTED</strong><br>{{ $payment_completion->reject_reason }}
                    </div>
                @endif

                {{-- ── DATA CBD/COD/DP ── --}}
                @php
                    $currency     = $payment_completion->po_mata_uang ?? '-';
                    $totalInvoice = 0;
                @endphp

                @if (!empty($pairs))
                    @foreach ($pairs as $i => $row)
                        @php
                            $totalInvoice += (float) ($row['nilai_invoice'] ?? 0);

                            // Invoice
                            $invStatus = ($row['invoice_verify_status'] ?? 0) == 1 ? 'Verified' : 'Unverified';
                            $invUser   = $row['invoice_verify_user'] ?? '-';
                            $invDate   = !empty($row['invoice_verify_date']) ? $fmtDate($row['invoice_verify_date']) : '';
                            $invNote   = $row['invoice_verify_note'] ?? '';

                            // Faktur Pajak
                            $fakStatus = ($row['faktur_verify_status'] ?? 0) == 1 ? 'Verified' : 'Unverified';
                            $fakUser   = $row['faktur_verify_user'] ?? '-';
                            $fakDate   = !empty($row['faktur_verify_date']) ? $fmtDate($row['faktur_verify_date']) : '';
                            $fakNote   = $row['faktur_verify_note'] ?? '';

                            // Proforma
                            $promoStatus = ($row['proforma_verify_status'] ?? 0) == 1 ? 'Verified' : 'Unverified';
                            $promoUser   = $row['proforma_verify_user'] ?? '-';
                            $promoDate   = !empty($row['proforma_verify_date']) ? $fmtDate($row['proforma_verify_date']) : '';

                            // Tgl Akhir Bayar
                            $tglStatus = ($row['tgl_jatuh_tempo_verify_status'] ?? 0) == 1 ? 'Verified' : 'Unverified';
                            $tglUser   = $row['tgl_jatuh_tempo_verify_user'] ?? '-';
                            $tglDate   = !empty($row['tgl_jatuh_tempo_verify_date']) ? $fmtDate($row['tgl_jatuh_tempo_verify_date']) : '';

                            $rowCount = 3 // NO + INVOICE + TGL AKHIR BAYAR + CATATAN
                                + ($payment_completion->is_form_faktur == 1 ? 1 : 0)
                                + ($payment_completion->is_form_proforma == 1 ? 1 : 0);
                        @endphp

                        <table class="table table-bordered mt-3">
                            <thead>
                                <tr style="background-color:#f0f0f0;">
                                    <th style="width:50px;" class="text-center">NO</th>
                                    <th style="width:200px;">KOMPONEN</th>
                                    <th>DATA</th>
                                    <th style="width:250px;">STATUS VERIFIKASI</th>
                                </tr>
                            </thead>
                            <tbody>

                                {{-- INVOICE --}}
                                <tr>
                                    <td rowspan="{{ $rowCount }}" class="text-center align-middle" style="font-size:1.1rem;">
                                        <strong>{{ $i + 1 }}</strong>
                                    </td>
                                    <td class="align-middle"><strong>INVOICE</strong></td>
                                    <td style="vertical-align:top;">
                                        <strong>{{ $row['invoice'] ?: '-' }}</strong>
                                        <ul style="margin:4px 0 0 0; padding-left:20px;">
                                            <li>Nilai Invoice: {{ $currency }} {{ $fmtNum($row['nilai_invoice']) }}</li>
                                            <li>File Invoice:
                                                @if ($row['file_invoice'])
                                                    <a href="{{ asset('storage/' . $row['file_invoice']) }}" target="_blank">
                                                        <i class="ti-file text-danger icon-lg"></i>
                                                    </a>
                                                @else - @endif
                                            </li>
                                        </ul>
                                    </td>
                                    <td style="vertical-align:top;">
                                        <small>
                                            <span class="{{ $badgeClass($invStatus) }}">{{ $invStatus }}</span>
                                            @if($invUser != '-')
                                                <span class="{{ $invStatus == 'Verified' ? 'text-success' : 'text-danger' }}">
                                                    {{ $invUser }} {{ $invDate ? '['.$invDate.']' : '' }}
                                                </span>
                                            @endif
                                            @if($invNote) <br><span class="text-muted">Notes: {{ $invNote }}</span> @endif
                                        </small>
                                    </td>
                                </tr>

                                {{-- FAKTUR PAJAK --}}
                                @if ($payment_completion->is_form_faktur == 1)
                                    <tr>
                                        <td class="align-middle"><strong>FAKTUR PAJAK</strong></td>
                                        <td style="vertical-align:top;">
                                            <strong>{{ $row['faktur_pajak'] ?: '-' }}</strong>
                                            <ul style="margin:4px 0 0 0; padding-left:20px;">
                                                <li>File Faktur Pajak:
                                                    @if ($row['file_faktur_pajak'])
                                                        <a href="{{ asset('storage/' . $row['file_faktur_pajak']) }}" target="_blank">
                                                            <i class="ti-file text-danger icon-lg"></i>
                                                        </a>
                                                    @else - @endif
                                                </li>
                                            </ul>
                                        </td>
                                        <td style="vertical-align:top;">
                                            <small>
                                                <span class="{{ $badgeClass($fakStatus) }}">{{ $fakStatus }}</span>
                                                @if($fakUser != '-')
                                                    <span class="{{ $fakStatus == 'Verified' ? 'text-success' : 'text-danger' }}">
                                                        {{ $fakUser }} {{ $fakDate ? '['.$fakDate.']' : '' }}
                                                    </span>
                                                @endif
                                                @if($fakNote) <br><span class="text-muted">Notes: {{ $fakNote }}</span> @endif
                                            </small>
                                        </td>
                                    </tr>
                                @endif

                                {{-- PROFORMA INVOICE --}}
                                @if ($payment_completion->is_form_proforma == 1)
                                    <tr>
                                        <td class="align-middle"><strong>PROFORMA INVOICE</strong></td>
                                        <td style="vertical-align:top;">
                                            <strong>{{ $row['proforma_invoice'] ?? '-' }}</strong>
                                            <ul style="margin:4px 0 0 0; padding-left:20px;">
                                                <li>Nilai Proforma: {{ $currency }} {{ $fmtNum($row['nilai_proforma_invoice'] ?? null) }}</li>
                                                <li>File Proforma:
                                                    @if ($row['file_proforma_invoice'] ?? null)
                                                        <a href="{{ asset('storage/' . $row['file_proforma_invoice']) }}" target="_blank">
                                                            <i class="ti-file text-danger icon-lg"></i>
                                                        </a>
                                                    @else - @endif
                                                </li>
                                            </ul>
                                        </td>
                                        <td style="vertical-align:top;">
                                            <small>
                                                <span class="{{ $badgeClass($promoStatus) }}">{{ $promoStatus }}</span>
                                                @if($promoUser != '-')
                                                    <span class="{{ $promoStatus == 'Verified' ? 'text-success' : 'text-danger' }}">
                                                        {{ $promoUser }} {{ $promoDate ? '['.$promoDate.']' : '' }}
                                                    </span>
                                                @endif
                                            </small>
                                        </td>
                                    </tr>
                                @endif

                                {{-- TGL AKHIR BAYAR --}}
                                <tr>
                                    <td class="align-middle"><strong>TGL AKHIR BAYAR</strong></td>
                                    <td style="vertical-align:middle;">
                                        <strong>
                                            {{ !empty($row['tgl_jatuh_tempo'])
                                                ? \Carbon\Carbon::parse($row['tgl_jatuh_tempo'])->translatedFormat('d F Y')
                                                : '-' }}
                                        </strong>
                                    </td>
                                    <td style="vertical-align:top;">
                                        <small>
                                            <span class="{{ $badgeClass($tglStatus) }}">{{ $tglStatus }}</span>
                                            @if($tglUser != '-')
                                                <span class="{{ $tglStatus == 'Verified' ? 'text-success' : 'text-danger' }}">
                                                    {{ $tglUser }} {{ $tglDate ? '['.$tglDate.']' : '' }}
                                                </span>
                                            @endif
                                        </small>
                                    </td>
                                </tr>

                                {{-- CATATAN --}}
                                <tr>
                                    <td colspan="3" style="background-color:#fffbe6;">
                                        <strong>Catatan :</strong> {{ $row['detail_notes'] ?? '-' }}
                                    </td>
                                </tr>

                            </tbody>
                        </table>
                    @endforeach

                    {{-- JUMLAH & SISA --}}
                    <table class="table table-bordered mt-2">
                        <tbody>
                            <tr style="height:40px;">
                                <td style="width:160px;" class="text-center"><strong>JUMLAH</strong></td>
                                <td style="font-weight:bold;">{{ $currency }} {{ $fmtNum($totalInvoice) }}</td>
                            </tr>
                            <tr style="height:40px;">
                                <td class="text-center">
                                    @if (($payment_amount - $totalInvoice) < 0)
                                        <strong class="text-danger">LEBIH BAYAR</strong>
                                    @else
                                        <strong>SISA</strong>
                                    @endif
                                </td>
                                <td style="font-weight:bold; {{ ($payment_amount - $totalInvoice) < 0 ? 'color:#dc3545;' : '' }}">
                                    {{ $currency }} {{ $fmtNum(abs($payment_amount - $totalInvoice)) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>

                @else
                    <table class="table table-bordered mt-3">
                        <tr>
                            <td class="text-center text-muted">
                                Not Available Data Payment Completion - CBD / COD / DP
                            </td>
                        </tr>
                    </table>
                @endif
            </div>

            {{-- ── TAB BUKTI PEMBAYARAN ── --}}
            <div class="tab-pane" id="tab-bukti" role="tabpanel">
                @php
                    $allIndexes = collect($pairs)->pluck('index')->unique()->sort()->values();
                @endphp

                @forelse ($allIndexes as $loopIdx => $idx)
                    @php
                        $label     = 'Kelengkapan Ke-' . ($loopIdx + 1);
                        $buktiList = isset($paymentDetails) ? $paymentDetails->get($idx, collect()) : collect();
                    @endphp

                    <div class="card mb-3">
                        <div class="card-header d-flex justify-content-between align-items-center"
                            style="background-color:#f8f9fa;">
                            <strong>{{ $label }}</strong>
                            @if (Gate::allows('payment_completion_admin') && in_array($payment_completion->status, [0,1,2,4]))
                                <button type="button" class="btn btn-sm btn-success btn-tambah-bukti"
                                    data-index="{{ $idx }}"
                                    data-label="{{ $label }}">
                                    <i class="ti-plus"></i> Tambah
                                </button>
                            @endif
                        </div>
                        <div class="card-body p-0">
                            @if ($buktiList->isEmpty())
                                <p class="text-muted p-3 mb-0">Belum ada bukti pembayaran</p>
                            @else
                                <table class="table table-bordered mb-0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Title</th>
                                            <th>Notes</th>
                                            <th style="width:80px;" class="text-center">File</th>
                                            <th>Diupload Oleh</th>
                                            <th>Tanggal</th>
                                            @if (Gate::allows('payment_completion_admin') && in_array($payment_completion->status, [0,1,2,4]))
                                                <th style="width:60px;" class="text-center">Aksi</th>
                                            @endif
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($buktiList as $bukti)
                                            <tr>
                                                <td>{{ $bukti->title }}</td>
                                                <td>{!! $bukti->notes ?? '-' !!}</td>
                                                <td class="text-center">
                                                    @if ($bukti->file)
                                                        <a href="{{ asset('storage/' . $bukti->file) }}" target="_blank">
                                                            <i class="ti-file text-danger icon-lg"></i>
                                                        </a>
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                                <td>{{ $bukti->uploader_name ?? '-' }}</td>
                                                <td>{{ date('d M Y H:i', strtotime($bukti->created_at)) }}</td>
                                                @if (Gate::allows('payment_completion_admin') && in_array($payment_completion->status, [0,1,2,4]))
                                                    <td class="text-center">
                                                        <form method="POST"
                                                            action="{{ route('purchasing.payment_completion.payment_detail.destroy', Hashids::encode($bukti->id)) }}">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="button" class="btn btn-sm btn-danger btn-hapus-bukti">
                                                                <i class="ti-trash"></i>
                                                            </button>
                                                        </form>
                                                    </td>
                                                @endif
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-muted">Tidak ada data kelengkapan.</p>
                @endforelse
            </div>

            {{-- ── TAB HISTORY ── --}}
            <div class="tab-pane" id="tab2" role="tabpanel">
                <div class="timeline">
                    <div class="timeline__box">
                        <div class="timeline__date" style="width:auto !important; height:auto !important; border-radius:0; left:30px;">
                            <span class="timeline__month">Status PC</span>
                            <h5>{{ getStatusPC($payment_completion->status, 'raw') }}</h5>
                        </div>
                    </div>
                    <div class="timeline__group">
                        @foreach ($historyPc as $val)
                            <div class="timeline__box">
                                <div class="timeline__date"></div>
                                <div class="timeline__post">
                                    <div class="timeline__content">
                                        <span><strong>{{ date('d/m/Y H:i:s', strtotime($val->created_at)) }}</strong></span><br>
                                        <span>{{ $val->message }}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

{{-- MODAL REJECT --}}
<div class="modal fade" id="modalSetReject" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><strong>REJECT PAYMENT COMPLETION <span id="doc_spb"></span></strong></h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <form id="formSetSelesai" method="POST" action="{{ route('purchasing.payment_completion.reject') }}">
                    @csrf
                    <input type="hidden" name="id" value="">
                    <div class="form-group">
                        <label><b>Reject Reason</b></label>
                        <textarea name="receipt_note" class="form-control" placeholder="Reason" required></textarea>
                    </div>
                    <div class="text-right">
                        <button type="submit" class="btn btn-danger">REJECT</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- MODAL CHANGE PO --}}
<div class="modal fade" id="modalChangePo" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><strong>CHANGE RELATION PO</strong></h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <form id="formChangePo" method="POST"
                    action="{{ route('purchasing.payment_completion.change_relation_po', Hashids::encode($payment_completion->id)) }}">
                    @csrf
                    <div class="form-group">
                        <span>OLD PO <strong>{{ $payment_completion->no_po }}</strong></span>
                    </div>
                    <div class="form-group">
                        <label><b>New Document PO <span class="text-danger">*</span></b></label>
                        <select name="new_po_id" class="form-control select2" required>
                            @php
                                $dataReq = getDataByID('po', $payment_completion->po_id);
                                $docPo   = $dataReq->doc_no;
                                $docPo   = preg_replace('/-REV-\d+$/', '', $docPo);

                                $new_po = DB::table('po')
                                    ->where('doc_no', '!=', $dataReq->doc_no)
                                    ->where('doc_no', 'like', '%' . $docPo . '%')
                                    ->whereIn('status', [2, 4, 5])
                                    ->orderBy('id', 'desc')
                                    ->pluck('doc_no', 'id')
                                    ->prepend('Silahkan pilih...', '');
                            @endphp
                            @foreach ($new_po as $id => $doc_no)
                                <option value="{{ $id }}">{{ $doc_no }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="text-right">
                        <button type="submit" class="btn btn-danger">CHANGE</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- MODAL TAMBAH BUKTI PEMBAYARAN --}}
<div class="modal fade" id="modalTambahBukti" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Bukti Pembayaran — <span id="labelBukti"></span></h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form method="POST"
                action="{{ route('purchasing.payment_completion.payment_detail.store', Hashids::encode($payment_completion->id)) }}"
                enctype="multipart/form-data"
                id="formTambahBukti">
                @csrf
                <input type="hidden" name="index" id="inputIndexBukti">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" required
                            placeholder="Contoh: Bukti Transfer BCA">
                    </div>
                    <div class="form-group">
                        <label>File</label>
                        <input type="file" name="file" class="form-control"
                            accept=".pdf,.jpg,.jpeg,.png">
                        <small class="text-muted">Format: PDF, JPG, PNG. Maks 10MB</small>
                    </div>
                    <div class="form-group">
                        <label>Notes</label>
                        <input type="hidden" name="notes" id="notesInput">
                        <trix-editor input="notesInput" style="min-height:150px;"></trix-editor>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">
                        <i class="ti-upload"></i> Upload
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@stop

@section('js')
<script>
    function openPrintWindow(url) {
        var printWindow = window.open(url, "_blank", "width=1000,height=800");
        printWindow.focus();
        printWindow.onload = function() {
            setTimeout(function() { printWindow.print(); }, 500);
        };
    }

    $(document).ready(function () {

        $(document).on('click', '.btnPublish', function (e) {
            e.preventDefault();
            var _this = $(this);
            Swal.fire({
                title: 'Konfirmasi',
                html: 'Apakah anda yakin untuk publish?',
                type: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, lanjut',
                cancelButtonText: 'Batal',
            }).then(function (result) {
                if (result.value) _this.closest('form')[0].submit();
            });
        });

        $(document).on('click', '.btn-reject', function () {
            let id  = $(this).data('id');
            let doc = $(this).data('doc');
            $('#doc_spb').text(doc);
            $('input[name="id"]').val(id);
            $('#modalSetReject').modal('show');
        });

        $(document).on('click', '.btn-change-po', function () {
            $('#modalChangePo').modal('show');
        });

        $(document).on('click', '.btn-done', function () {
            var url = $(this).data('url');
            var doc = $(this).data('doc') || '';
            Swal.fire({
                title: 'Confirmation',
                text: doc ? ('Change Done Payment Completion ' + doc) : 'Done ?',
                type: 'question',
                showCancelButton: true,
                confirmButtonClass: 'btn btn-danger',
                cancelButtonClass: 'btn btn-primary',
                confirmButtonText: 'Done',
                cancelButtonText: 'Cancel'
            }).then(function (res) {
                if (res.value) postAndFollow(url);
            });
        });

        $(document).on('click', '.btn-tambahkelengkapan', function () {
            var url = $(this).data('url');
            Swal.fire({
                title: 'Confirmation',
                text: 'Kembalikan ke pending kelengkapan pembayaran?',
                type: 'question',
                showCancelButton: true,
                confirmButtonClass: 'btn btn-danger',
                cancelButtonClass: 'btn btn-primary',
                confirmButtonText: 'Ya',
                cancelButtonText: 'Cancel'
            }).then(function (res) {
                if (res.value) postAndFollow(url);
            });
        });

        // Buka modal tambah bukti
        $(document).on('click', '.btn-tambah-bukti', function () {
            var idx   = $(this).data('index');
            var label = $(this).data('label');
            $('#inputIndexBukti').val(idx);
            $('#labelBukti').text(label);
            $('input[name="title"]').val('');
            $('input[name="file"]').val('');
            if (document.querySelector('trix-editor')) {
                document.querySelector('trix-editor').editor.loadHTML('');
            }
            $('#modalTambahBukti').modal('show');
        });

        // Konfirmasi hapus bukti
        $(document).on('click', '.btn-hapus-bukti', function () {
            var form = $(this).closest('form');
            Swal.fire({
                title: 'Hapus Bukti Pembayaran?',
                text: 'File akan dihapus permanen',
                type: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal',
            }).then(function (result) {
                if (result.value) form.submit();
            });
        });

        // Reset modal saat ditutup
        $('#modalTambahBukti').on('hidden.bs.modal', function () {
            if (document.querySelector('trix-editor')) {
                document.querySelector('trix-editor').editor.loadHTML('');
            }
            $('input[name="title"]').val('');
            $('input[name="file"]').val('');
            $('#inputIndexBukti').val('');
            $('#labelBukti').text('');
        });

        function postAndFollow(url) {
            var f = document.createElement('form');
            f.method = 'POST';
            f.action = url;
            var t = document.createElement('input');
            t.type  = 'hidden';
            t.name  = '_token';
            t.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            f.appendChild(t);
            document.body.appendChild(f);
            f.submit();
        }
    });
</script>
@stop