@extends('layouts.app')

@section('page-header')
    Payment Completion
@stop

@php
    use Carbon\Carbon;
    $fmtDate     = fn($v) => !empty($v) ? Carbon::parse($v)->format('d F Y') : '-';
    $fmtDateTime = fn($v) => !empty($v) ? Carbon::parse($v)->format('d F Y H:i:s') : '-';
    $fmtNum      = fn($v, $dec = 2) => is_numeric($v) ? number_format($v, $dec) : '-';
    $poCurrency  = $pc->po_mata_uang ?? '';
@endphp

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('approval.verify_pc.index') }}">Payment Completion</a></li>
        <li class="breadcrumb-item active" aria-current="page">Payment Completion Verify</li>
    </ol>
@endsection

@section('content')
<style>
    .bg-locked    { background-color: #e6ffed !important; }
    .input-locked { background-color: #e6ffed !important; border-color: #28a745; cursor: not-allowed; }
    .bg-invoice, .bg-faktur, .bg-surat-jalan { vertical-align: top; }
    .check-label {
        display: flex;
        align-items: center;
        justify-content: start;
        margin-left: 10px;
        gap: 10px;
        font-size: 0.85rem;
        font-weight: bold;
    }
</style>

@php
    $poTotal = 0;
    $po = getDataByID('po', $pc->po_id);
    $po_items = \App\Models\PurchaseOrder::getProductItem($po->id);
    foreach ($po_items as $item) {
        $poTotal += $item->price * $item->qty - (($item->price * $item->qty) * $item->discount / 100);
    }
    if ($po->discount_item == false) {
        if ($po->discount_type == 1) {
            $po->discount_amount = $poTotal * ((float)$po->discount_amount / 100);
        }
        $netto = $poTotal - (float)$po->discount_amount;
    } else {
        $netto = $poTotal;
    }
    if ((float)$po->send_expense_ppn == 1 || (float)$po->send_expense_ppn == 11) {
        $po->send_expense += (11 / 100) * (float)$po->send_expense;
    }
    $po->ppn     = $netto * (float)$po->ppn / 100;
    $po->pph     = $netto * (float)$po->pph / 100;
    $payment_amount = $netto - (float)$po->pph + (float)$po->ppn + (float)$po->send_expense;
@endphp

<div class="card bgc-white p-30 bd">

    {{-- ── HEADER ACTIONS ── --}}
    <div class="row justify-content-end" style="margin-top:-20px; padding-bottom:10px;">
        <div class="col-sm-6">
            <a title="Kembali" href="{{ route('approval.verify_pc.index') }}" class="nav-link">
                <i class="ti-arrow-left"></i> Kembali
            </a>
        </div>
        <div class="col-sm-6 d-flex justify-content-end" style="margin-top:10px;">
            @if ($pc->status == 1 && Gate::allows('approval_pc'))

                <button type="button" class="btn btn-outline text-primary btn-tambahkelengkapan"
                    data-url="{{ route('purchasing.payment_completion.tambahkelengkapan', Hashids::encode($pc->id)) }}"
                    data-id="{{ Hashids::encode($pc->id) }}"
                    title="Returned to Pending Purchasing" data-toggle="tooltip">
                    <i class="ti-back-left icon-sm" style="font-weight:bold; color:purple;"> to Purchasing</i>
                </button>

                @php
                    $allFullyLocked = true;
                    foreach ($details as $row) {
                        if ($pc->type_payment == 1) {
                            $rowLocked = $row['islock_invoice'] && $row['islock_tgl_surat_jalan'];
                            if ($pc->is_form_faktur == 1)   $rowLocked = $rowLocked && $row['islock_faktur_pajak'];
                            if ($pc->is_form_proforma == 1) $rowLocked = $rowLocked && ($row['islock_proforma_invoice'] ?? false);
                        } else {
                            $rowLocked = $row['islock_invoice'] && ($row['islock_tgl_jatuh_tempo'] ?? false);
                            if ($pc->is_form_faktur == 1)   $rowLocked = $rowLocked && $row['islock_faktur_pajak'];
                            if ($pc->is_form_proforma == 1) $rowLocked = $rowLocked && ($row['islock_proforma_invoice'] ?? false);
                        }
                        if (!$rowLocked) { $allFullyLocked = false; break; }
                    }

                    $totalNilaiInvoice = collect($details)->sum(fn($r) => (float) ($r['nilai_invoice'] ?? 0));
                    $invoiceSufficient = $totalNilaiInvoice >= $payment_amount;
                    $canSetDone = $allFullyLocked && $invoiceSufficient && count($details) > 0;
                @endphp

                @if ($canSetDone)
                    {{-- <button type="button" class="btn btn-outline text-success btn-done"
                        data-url="{{ route('approval.verify_pc.done', Hashids::encode($pc->id)) }}"
                        data-doc="{{ $pc->doc_no }}"
                        title="Done" data-toggle="tooltip">
                        <i class="ti-thumb-up icon-sm" style="font-weight:bold; color:green;"> Set Done</i>
                    </button> --}}
                @elseif ($allFullyLocked && count($details) > 0 && !$invoiceSufficient)
                    {{-- <span class="badge badge-warning" style="padding:7px 10px; font-size:0.8rem;"
                        title="Total Invoice ({{ $poCurrency }} {{ number_format($totalNilaiInvoice, 2) }}) belum mencapai Total Harga PO ({{ $poCurrency }} {{ number_format($payment_amount, 2) }})"
                        data-toggle="tooltip">
                        <i class="ti-alert"></i> Invoice Belum Cukup
                    </span> --}}
                @endif
            @endif
        </div>
    </div>

    <hr>

    {{-- ── INFO PC ── --}}
    <h6 style="font-weight:bold; text-decoration:underline; text-align:center; margin-top:20px;">
        {{ $pc->doc_no ?? '-' }}
    </h6>

    <div class="row mt-3" style="margin-left:20px;">
        <div class="col-sm-6">
            <div class="row">
                <label class="col-sm-3">Company</label>
                <div class="col-sm-9">: {{ strtoupper($pc->nama_company ?? '-') }}</div>
            </div>
            <div class="row">
                <label class="col-sm-3">Type PC</label>
                <div class="col-sm-9">: {{ getTypePC($pc->type_payment, 'raw') }}</div>
            </div>
            <div class="row">
                <label class="col-sm-3">Dibuat Oleh</label>
                <div class="col-sm-9">: {{ strtoupper($pc->nama_pembuat ?? '-') }}
                    [ {{ date('d M Y', strtotime($pc->created_at)) }} ]</div>
            </div>
            <div class="row">
                <label class="col-sm-3">Status PC</label>
                <div class="col-sm-9">: {!! getStatusPC($pc->status) !!}</div>
            </div>
            <div class="row">
                <label class="col-sm-3">Notes PC</label>
                <div class="col-sm-9">: {!! $pc->notes ?? '-' !!}</div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="row">
                <label class="col-sm-3">Nomor PR</label>
                <div class="col-sm-9">: {{ $pc->no_pr ?? '-' }}
                    [ {{ date('d M Y', strtotime($pc->tgl_pr)) }} ]</div>
            </div>
            <div class="row">
                <label class="col-sm-3">Nomor PO</label>
                <div class="col-sm-9">: {{ $pc->no_po ?? '-' }}
                    [ {{ date('d M Y', strtotime($pc->tgl_po)) }} ]</div>
            </div>
            <div class="row">
                <label class="col-sm-3">Supplier</label>
                <div class="col-sm-9">: {{ $pc->nama_supplier ?? '-' }}</div>
            </div>
            <div class="row">
                <label class="col-sm-3">Payment Term</label>
                <div class="col-sm-9">: {{ $pc->payment_term ?? '-' }}</div>
            </div>
            <div class="row">
                <label class="col-sm-3">Total Harga PO</label>
                <div class="col-sm-9">: {{ $po->currency }} {{ $fmtNum($payment_amount) }}</div>
            </div>
        </div>
    </div>

    {{-- ── FORM VERIFY ── --}}
    <div class="card-body p-16">
        <div class="table-responsive">
            <form method="POST"
                action="{{ route('approval.verify_pc.page_lock', Hashids::encode($pc->id)) }}">
                @csrf

                @php
                    $allLocked    = true;
                    $totalInvoice = 0;
                @endphp

                {{-- ============================================================
                     TEMPO — per row, tiap komponen punya checkbox sendiri
                     ============================================================ --}}
                @if ($pc->type_payment == 1)

                    @foreach ($details as $i => $row)
                        @php
                            $rowAllLocked = $row['islock_invoice'] && $row['islock_tgl_surat_jalan'];
                            if ($pc->is_form_faktur == 1)   $rowAllLocked = $rowAllLocked && $row['islock_faktur_pajak'];
                            if ($pc->is_form_proforma == 1) $rowAllLocked = $rowAllLocked && ($row['islock_proforma_invoice'] ?? false);
                            if (!$rowAllLocked) $allLocked = false;

                            $totalInvoice += (float) ($row['nilai_invoice'] ?? 0);

                            $noteAutoLocked = $row['islock_invoice'] && $row['islock_tgl_surat_jalan'];
                            if ($pc->is_form_faktur == 1)   $noteAutoLocked = $noteAutoLocked && $row['islock_faktur_pajak'];
                            if ($pc->is_form_proforma == 1) $noteAutoLocked = $noteAutoLocked && ($row['islock_proforma_invoice'] ?? false);
                        @endphp

                        <input type="hidden" name="index[{{ $row['index'] }}]" value="{{ $row['index'] }}">

                        <table class="table table-bordered mb-3">
                            <thead>
                                <tr style="background-color:#f0f0f0;">
                                    <th style="width:50px;" class="text-center">NO</th>
                                    <th style="width:220px;" class="text-center">CHECK</th>
                                    <th>DATA</th>
                                    <th style="width:250px;">CATATAN VERIFIKASI</th>
                                </tr>
                            </thead>
                            <tbody>

                                {{-- ── INVOICE ── --}}
                                <tr class="bg-invoice {{ $row['islock_invoice'] ? 'bg-locked' : '' }}">
                                    <td rowspan="{{ 3 + ($pc->is_form_faktur == 1 ? 1 : 0) + ($pc->is_form_proforma == 1 ? 1 : 0) + 1 }}"
                                        class="text-center align-middle" style="font-size:1.2rem;">
                                        <strong>{{ $i + 1 }}</strong>
                                    </td>
                                    <td class="align-middle text-center">
                                        <div class="check-label">
                                            <input type="hidden" name="check_invoice[{{ $row['index'] }}]" value="0">
                                            <input type="checkbox"
                                                name="check_invoice[{{ $row['index'] }}]"
                                                value="1"
                                                style="transform:scale(2); cursor:pointer;"
                                                {{ $row['islock_invoice'] ? 'checked disabled' : '' }}>
                                            <span>INVOICE</span>
                                        </div>
                                    </td>
                                    <td style="vertical-align:top;">
                                        <strong>{{ $row['no_si'] ?? '-' }} / {{ $row['invoice'] ?: '-' }}</strong>
                                        <ul style="margin:4px 0 0 0; padding-left:20px;">
                                            <li>Nilai Invoice: {{ $poCurrency }} {{ $fmtNum($row['nilai_invoice']) }}</li>
                                            <li>Tgl Invoice: {{ $fmtDate($row['tgl_invoice']) }}</li>
                                            <li>Tgl Terima Invoice: {{ $fmtDate($row['tgl_terima_invoice']) }}</li>
                                            <li>Periode Tempo: {{ $row['periode_tempo'] ?? 0 }} Hari</li>
                                            <li>Tgl Jatuh Tempo: {{ $fmtDate($row['tgl_jatuh_tempo']) }}</li>
                                            <li>File Invoice:
                                                @if ($row['file_invoice'])
                                                    <a href="{{ asset('storage/' . $row['file_invoice']) }}" target="_blank">
                                                        <i class="ti-file text-danger icon-lg"></i>
                                                    </a>
                                                @else - @endif
                                            </li>
                                        </ul>
                                    </td>
                                    <td>
                                        <input type="text"
                                            class="form-control {{ $row['islock_invoice'] ? 'input-locked' : '' }}"
                                            name="note_invoice[{{ $row['index'] }}]"
                                            value="{{ $row['verify_notes_invoice'] ?? '' }}"
                                            {{ $row['islock_invoice'] ? 'readonly' : '' }}
                                            placeholder="Catatan Invoice">
                                    </td>
                                </tr>

                                {{-- ── FAKTUR PAJAK ── --}}
                                @if ($pc->is_form_faktur == 1)
                                    <tr class="bg-faktur {{ $row['islock_faktur_pajak'] ? 'bg-locked' : '' }}">
                                        <td class="align-middle text-center">
                                            <div class="check-label">
                                                <input type="hidden" name="check_faktur_pajak[{{ $row['index'] }}]" value="0">
                                                <input type="checkbox"
                                                    name="check_faktur_pajak[{{ $row['index'] }}]"
                                                    value="1"
                                                    style="transform:scale(2); cursor:pointer;"
                                                    {{ $row['islock_faktur_pajak'] ? 'checked disabled' : '' }}>
                                                <span>FAKTUR PAJAK</span>
                                            </div>
                                        </td>
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
                                        <td>
                                            <input type="text"
                                                class="form-control {{ $row['islock_faktur_pajak'] ? 'input-locked' : '' }}"
                                                name="note_faktur_pajak[{{ $row['index'] }}]"
                                                value="{{ $row['verify_notes_faktur_pajak'] ?? '' }}"
                                                {{ $row['islock_faktur_pajak'] ? 'readonly' : '' }}
                                                placeholder="Catatan Faktur Pajak">
                                        </td>
                                    </tr>
                                @endif

                                {{-- ── PROFORMA INVOICE ── --}}
                                @if ($pc->is_form_proforma == 1)
                                    @php $isProformaLocked = $row['islock_proforma_invoice'] ?? false; @endphp
                                    <tr class="{{ $isProformaLocked ? 'bg-locked' : '' }}">
                                        <td class="align-middle text-center">
                                            <div class="check-label">
                                                <input type="hidden" name="check_proforma_invoice[{{ $row['index'] }}]" value="0">
                                                <input type="checkbox"
                                                    name="check_proforma_invoice[{{ $row['index'] }}]"
                                                    value="1"
                                                    style="transform:scale(2); cursor:pointer;"
                                                    {{ $isProformaLocked ? 'checked disabled' : '' }}>
                                                <span>PROFORMA INVOICE</span>
                                            </div>
                                        </td>
                                        <td style="vertical-align:top;">
                                            <strong>{{ $row['proforma_invoice'] ?? '-' }}</strong>
                                            <ul style="margin:4px 0 0 0; padding-left:20px;">
                                                <li>Nilai Proforma: {{ $poCurrency }} {{ $fmtNum($row['nilai_proforma_invoice'] ?? 0) }}</li>
                                                <li>File Proforma:
                                                    @if ($row['file_proforma_invoice'] ?? null)
                                                        <a href="{{ asset('storage/' . $row['file_proforma_invoice']) }}" target="_blank">
                                                            <i class="ti-file text-danger icon-lg"></i>
                                                        </a>
                                                    @else - @endif
                                                </li>
                                            </ul>
                                        </td>
                                        <td>
                                            <input type="text"
                                                class="form-control {{ $isProformaLocked ? 'input-locked' : '' }}"
                                                name="note_proforma_invoice[{{ $row['index'] }}]"
                                                value="{{ $row['verify_notes_proforma_invoice'] ?? '' }}"
                                                {{ $isProformaLocked ? 'readonly' : '' }}
                                                placeholder="Catatan Proforma Invoice">
                                        </td>
                                    </tr>
                                @endif

                                {{-- ── SURAT JALAN ── --}}
                                <tr class="bg-surat-jalan {{ $row['islock_tgl_surat_jalan'] ? 'bg-locked' : '' }}">
                                    <td class="align-middle text-center">
                                        <div class="check-label">
                                            <input type="hidden" name="check_tgl_surat_jalan[{{ $row['index'] }}]" value="0">
                                            <input type="checkbox"
                                                name="check_tgl_surat_jalan[{{ $row['index'] }}]"
                                                value="1"
                                                style="transform:scale(2); cursor:pointer;"
                                                {{ $row['islock_tgl_surat_jalan'] ? 'checked disabled' : '' }}>
                                            <span>SURAT JALAN</span>
                                        </div>
                                    </td>
                                    <td style="vertical-align:middle;">
                                        <strong>{{ $fmtDate($row['tgl_surat_jalan']) }}</strong>
                                    </td>
                                    <td>
                                        <input type="text"
                                            class="form-control {{ $row['islock_tgl_surat_jalan'] ? 'input-locked' : '' }}"
                                            name="note_surat_jalan[{{ $row['index'] }}]"
                                            value="{{ $row['verify_notes_tgl_surat_jalan'] ?? '' }}"
                                            {{ $row['islock_tgl_surat_jalan'] ? 'readonly' : '' }}
                                            placeholder="Catatan Surat Jalan">
                                    </td>
                                </tr>

                                {{-- ── CATATAN (auto-lock) ── --}}
                                <tr class="{{ $noteAutoLocked ? 'bg-locked' : '' }}">
                                    <td colspan="3" style="vertical-align:middle; {{ $noteAutoLocked ? '' : 'background-color:#fffbe6;' }}">
                                        <strong>Catatan :</strong> {{ $row['detail_notes'] ?? '-' }}
                                        @if (!$noteAutoLocked)
                                            <small class="text-muted ml-2">(akan terkunci otomatis setelah semua komponen terkunci)</small>
                                        @endif
                                    </td>
                                </tr>

                            </tbody>
                        </table>

                    @endforeach

                    {{-- Footer JUMLAH / SISA --}}
                    <table class="table table-bordered mb-3">
                        <tbody>
                            <tr style="height:45px;">
                                <td style="width:160px;" class="text-center"><strong>JUMLAH</strong></td>
                                <td style="font-weight:bold;">{{ $poCurrency }} {{ $fmtNum($totalInvoice) }}</td>
                            </tr>
                            <tr style="height:45px;">
                                <td class="text-center">
                                    @if (($payment_amount - $totalInvoice) < 0)
                                        <strong class="text-danger">LEBIH BAYAR</strong>
                                    @else
                                        <strong>SISA</strong>
                                    @endif
                                </td>
                                <td style="font-weight:bold; {{ ($payment_amount - $totalInvoice) < 0 ? 'color:#dc3545;' : '' }}">
                                    {{ $poCurrency }} {{ $fmtNum(abs($payment_amount - $totalInvoice)) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>

                {{-- ============================================================
                     CBD/COD/DP — per row, tiap komponen punya checkbox sendiri
                     ============================================================ --}}
                @else
                    @php
                        $rows         = $details ?? [];
                        $totalInvoice = 0;
                    @endphp

                    @foreach ($rows as $i => $row)
                        @php
                            $rowAllLocked = $row['islock_invoice'];
                            if ($pc->is_form_faktur == 1)   $rowAllLocked = $rowAllLocked && $row['islock_faktur_pajak'];
                            if ($pc->is_form_proforma == 1) $rowAllLocked = $rowAllLocked && ($row['islock_proforma_invoice'] ?? false);
                            $rowAllLocked = $rowAllLocked && ($row['islock_tgl_jatuh_tempo'] ?? false);
                            if (!$rowAllLocked) $allLocked = false;

                            $totalInvoice += (float) ($row['nilai_invoice'] ?? 0);

                            $noteAutoLocked = $row['islock_invoice'];
                            if ($pc->is_form_faktur == 1)   $noteAutoLocked = $noteAutoLocked && $row['islock_faktur_pajak'];
                            if ($pc->is_form_proforma == 1) $noteAutoLocked = $noteAutoLocked && ($row['islock_proforma_invoice'] ?? false);
                            $noteAutoLocked = $noteAutoLocked && ($row['islock_tgl_jatuh_tempo'] ?? false);
                        @endphp

                        <input type="hidden" name="index[{{ $row['index'] }}]" value="{{ $row['index'] }}">

                        <table class="table table-bordered mb-3">
                            <thead>
                                <tr style="background-color:#f0f0f0;">
                                    <th style="width:50px;" class="text-center">NO</th>
                                    <th style="width:220px;" class="text-center">CHECK</th>
                                    <th>DATA</th>
                                    <th style="width:250px;">CATATAN VERIFIKASI</th>
                                </tr>
                            </thead>
                            <tbody>

                                {{-- ── INVOICE ── --}}
                                <tr class="bg-invoice {{ $row['islock_invoice'] ? 'bg-locked' : '' }}">
                                    <td rowspan="{{ 3 + ($pc->is_form_faktur == 1 ? 1 : 0) + ($pc->is_form_proforma == 1 ? 1 : 0) + 1 + 1 }}"
                                        class="text-center align-middle" style="font-size:1.2rem;">
                                        <strong>{{ $i + 1 }}</strong>
                                    </td>
                                    <td class="align-middle text-center">
                                        <div class="check-label">
                                            <input type="hidden" name="check_invoice[{{ $row['index'] }}]" value="0">
                                            <input type="checkbox"
                                                name="check_invoice[{{ $row['index'] }}]"
                                                value="1"
                                                style="transform:scale(2); cursor:pointer;"
                                                {{ $row['islock_invoice'] ? 'checked disabled' : '' }}>
                                            <span>INVOICE</span>
                                        </div>
                                    </td>
                                    <td style="vertical-align:top;">
                                        <strong>{{ $row['invoice'] ?: '-' }}</strong>
                                        <ul style="margin:4px 0 0 0; padding-left:20px;">
                                            <li>Nilai Invoice: {{ $poCurrency }} {{ $fmtNum($row['nilai_invoice']) }}</li>
                                            <li>File Invoice:
                                                @if ($row['file_invoice'])
                                                    <a href="{{ asset('storage/' . $row['file_invoice']) }}" target="_blank">
                                                        <i class="ti-file text-danger icon-lg"></i>
                                                    </a>
                                                @else - @endif
                                            </li>
                                        </ul>
                                    </td>
                                    <td>
                                        <input type="text"
                                            class="form-control {{ $row['islock_invoice'] ? 'input-locked' : '' }}"
                                            name="invoice_notes[{{ $row['index'] }}]"
                                            value="{{ $row['verify_notes_invoice'] ?? '' }}"
                                            {{ $row['islock_invoice'] ? 'readonly' : '' }}
                                            placeholder="Catatan Invoice">
                                    </td>
                                </tr>

                                {{-- ── FAKTUR PAJAK ── --}}
                                @if ($pc->is_form_faktur == 1)
                                    <tr class="bg-faktur {{ $row['islock_faktur_pajak'] ? 'bg-locked' : '' }}">
                                        <td class="align-middle text-center">
                                            <div class="check-label">
                                                <input type="hidden" name="check_faktur_pajak[{{ $row['index'] }}]" value="0">
                                                <input type="checkbox"
                                                    name="check_faktur_pajak[{{ $row['index'] }}]"
                                                    value="1"
                                                    style="transform:scale(2); cursor:pointer;"
                                                    {{ $row['islock_faktur_pajak'] ? 'checked disabled' : '' }}>
                                                <span>FAKTUR PAJAK</span>
                                            </div>
                                        </td>
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
                                        <td>
                                            <input type="text"
                                                class="form-control {{ $row['islock_faktur_pajak'] ? 'input-locked' : '' }}"
                                                name="faktur_pajak_notes[{{ $row['index'] }}]"
                                                value="{{ $row['verify_notes_faktur_pajak'] ?? '' }}"
                                                {{ $row['islock_faktur_pajak'] ? 'readonly' : '' }}
                                                placeholder="Catatan Faktur Pajak">
                                        </td>
                                    </tr>
                                @endif

                                {{-- ── PROFORMA INVOICE ── --}}
                                @if ($pc->is_form_proforma == 1)
                                    @php $isProformaLocked = $row['islock_proforma_invoice'] ?? false; @endphp
                                    <tr class="{{ $isProformaLocked ? 'bg-locked' : '' }}">
                                        <td class="align-middle text-center">
                                            <div class="check-label">
                                                <input type="hidden" name="check_proforma_invoice[{{ $row['index'] }}]" value="0">
                                                <input type="checkbox"
                                                    name="check_proforma_invoice[{{ $row['index'] }}]"
                                                    value="1"
                                                    style="transform:scale(2); cursor:pointer;"
                                                    {{ $isProformaLocked ? 'checked disabled' : '' }}>
                                                <span>PROFORMA INVOICE</span>
                                            </div>
                                        </td>
                                        <td style="vertical-align:top;">
                                            <strong>{{ $row['proforma_invoice'] ?: '-' }}</strong>
                                            <ul style="margin:4px 0 0 0; padding-left:20px;">
                                                <li>Nilai Proforma: {{ $poCurrency }} {{ $fmtNum($row['nilai_proforma_invoice'] ?? null) }}</li>
                                                <li>File Proforma:
                                                    @if ($row['file_proforma_invoice'] ?? null)
                                                        <a href="{{ asset('storage/' . $row['file_proforma_invoice']) }}" target="_blank">
                                                            <i class="ti-file text-danger icon-lg"></i>
                                                        </a>
                                                    @else - @endif
                                                </li>
                                            </ul>
                                        </td>
                                        <td>
                                            <input type="text"
                                                class="form-control {{ $isProformaLocked ? 'input-locked' : '' }}"
                                                name="proforma_notes[{{ $row['index'] }}]"
                                                value="{{ $row['verify_notes_proforma_invoice'] ?? '' }}"
                                                {{ $isProformaLocked ? 'readonly' : '' }}
                                                placeholder="Catatan Proforma Invoice">
                                        </td>
                                    </tr>
                                @endif

                                {{-- ── TGL AKHIR BAYAR ── --}}
                                @php $isTglLocked = $row['islock_tgl_jatuh_tempo'] ?? false; @endphp
                                <tr class="{{ $isTglLocked ? 'bg-locked' : '' }}">
                                    <td class="align-middle text-center">
                                        <div class="check-label">
                                            <input type="hidden" name="check_tgl_jatuh_tempo[{{ $row['index'] }}]" value="0">
                                            <input type="checkbox"
                                                name="check_tgl_jatuh_tempo[{{ $row['index'] }}]"
                                                value="1"
                                                style="transform:scale(2); cursor:pointer;"
                                                {{ $isTglLocked ? 'checked disabled' : '' }}>
                                            <span>TGL AKHIR BAYAR</span>
                                        </div>
                                    </td>
                                    <td style="vertical-align:middle;">
                                        <strong>{{ $fmtDate($row['tgl_jatuh_tempo']) }}</strong>
                                    </td>
                                    <td>
                                        <input type="text"
                                            class="form-control {{ $isTglLocked ? 'input-locked' : '' }}"
                                            name="tgl_jatuh_tempo_notes[{{ $row['index'] }}]"
                                            value="{{ $row['verify_notes_tgl_jatuh_tempo'] ?? '' }}"
                                            {{ $isTglLocked ? 'readonly' : '' }}
                                            placeholder="Catatan Tgl Akhir Bayar">
                                    </td>
                                </tr>

                                {{-- ── CATATAN (auto-lock) ── --}}
                                <tr class="{{ $noteAutoLocked ? 'bg-locked' : '' }}">
                                    <td colspan="3" style="vertical-align:middle; {{ $noteAutoLocked ? '' : 'background-color:#fffbe6;' }}">
                                        <strong>Catatan :</strong> {{ $row['detail_notes'] ?? '-' }}
                                        @if (!$noteAutoLocked)
                                            <small class="text-muted ml-2">(akan terkunci otomatis setelah semua komponen terkunci)</small>
                                        @endif
                                    </td>
                                </tr>

                            </tbody>
                        </table>

                    @endforeach

                    {{-- Footer JUMLAH / SISA / LEBIH BAYAR --}}
                    <table class="table table-bordered mb-3">
                        <tbody>
                            <tr style="height:45px;">
                                <td style="width:160px;" class="text-center"><strong>JUMLAH</strong></td>
                                <td style="font-weight:bold;">{{ $poCurrency }} {{ $fmtNum($totalInvoice) }}</td>
                            </tr>
                            <tr style="height:45px;">
                                <td class="text-center">
                                    @if (($payment_amount - $totalInvoice) < 0)
                                        <strong class="text-danger">LEBIH BAYAR</strong>
                                    @else
                                        <strong>SISA</strong>
                                    @endif
                                </td>
                                <td style="font-weight:bold; {{ ($payment_amount - $totalInvoice) < 0 ? 'color:#dc3545;' : '' }}">
                                    {{ $poCurrency }} {{ $fmtNum(abs($payment_amount - $totalInvoice)) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                @endif

                {{-- ── TOMBOL LOCK & BACK ── --}}
                <div class="text-right">
                    @if ($pc->status == 1)
                        <button type="submit" class="btn btn-primary btn-lock"
                            {{ $allLocked ? 'disabled' : '' }}>
                            <i class="ti-lock"> LOCK</i>
                        </button>
                    @endif
                    <a href="{{ route('approval.verify_pc.index') }}" class="btn btn-secondary">BACK</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
$(document).ready(function () {

    $(document).on('click', '.btn-lock', function (e) {
        e.preventDefault();
        var _this = $(this);
        Swal.fire({
            title: 'Konfirmasi',
            html: 'Apakah anda yakin untuk mengunci data yang telah dicentang?',
            type: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, lanjut',
            cancelButtonText: 'Batal',
        }).then(function (result) {
            if (result.value) {
                _this.closest('form')[0].submit();
            }
        });
    });

    $(document).on('click', '.btn-done', function () {
        var url = $(this).data('url');
        var doc = $(this).data('doc') || '';
        Swal.fire({
            title: 'Confirmation',
            text: doc ? ('Set Done Payment Completion ' + doc) : 'Done?',
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

    function postAndFollow(url) {
        var f = document.createElement('form');
        f.method = 'POST';
        f.action = url;
        var t = document.createElement('input');
        t.type = 'hidden';
        t.name = '_token';
        t.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        f.appendChild(t);
        document.body.appendChild(f);
        f.submit();
    }
});
</script>
@endsection
