@extends('layouts.app')

@section('page-header')
    Payment Completion
@stop

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('purchasing.payment_completion') }}">Payment Completion</a></li>
        <li class="breadcrumb-item active" aria-current="page">Payment Completion Edit (Tempo)</li>
    </ol>
@endsection

@section('content')
    @php
        function isLocked($component, $key) {
            return $component[$key]['islock'] ?? false;
        }
    @endphp

    <style>
        .bg-locked { background-color: #e6ffed !important; }
        .input-locked { background-color: #e6ffed !important; border-color: #28a745; cursor: not-allowed; }
        .bg-invoice, .bg-faktur { vertical-align: top; }
        .placeholder-danger::placeholder { color: #dc3545 !important; opacity: 1; font-style: italic; }
    </style>

    <form method="POST"
        action="{{ route('purchasing.payment_completion.update', Hashids::encode($pc->id)) }}"
        enctype="multipart/form-data"
        id="formUpdate">
        @csrf
        @method('PUT')

        {{-- Flag tambah row baru, default 0. Diset 1 saat klik "+ Tambah Form" --}}
        <input type="hidden" name="add_tempo_row" id="add_tempo_row" value="0">

        {{-- Flag hapus row, default kosong. Diset ke index saat klik hapus --}}
        <input type="hidden" name="delete_tempo_index" id="delete_tempo_index" value="">

        <div class="card mb-3">
            <h6 style="font-weight:bold; text-decoration:underline; text-align:center; margin-top:20px;">
                {{ $pc->doc_no ?? '-' }}
            </h6>

            <div class="row mt-3" style="margin-left:20px;">
                <div class="col-sm-6">
                    <div class="row">
                        <label class="col-sm-3">Company</label>
                        <div class="col-sm-9">: {{ strtoupper($pc->nama_company) }}</div>
                    </div>
                    <div class="row">
                        <label class="col-sm-3">Type PC</label>
                        <div class="col-sm-9">: {{ getTypePC($pc->type_payment, 'raw') }}</div>
                    </div>
                    <div class="row">
                        <label class="col-sm-3">Dibuat Oleh</label>
                        <div class="col-sm-9">: {{ $pc->nama_pembuat }} [ {{ date('d M Y', strtotime($pc->created_at)) }} ]</div>
                    </div>
                    <div class="row">
                        <label class="col-sm-3">Status PC</label>
                        <div class="col-sm-9">: {!! getStatusPC($pc->status) !!}</div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="row">
                        <label class="col-sm-3">Nomor PR</label>
                        <div class="col-sm-9">: {{ $pc->no_pr ?? '-' }} [ {{ date('d/m/Y', strtotime($pc->tgl_pr)) }} ]</div>
                    </div>
                    <div class="row">
                        <label class="col-sm-3">Nomor PO</label>
                        <div class="col-sm-9">: {{ $pc->no_po }} [ {{ date('d/m/Y', strtotime($pc->tgl_po)) }} ]</div>
                    </div>
                    <div class="row">
                        <label class="col-sm-3">Supplier</label>
                        <div class="col-sm-9">: {{ $pc->nama_supplier }}</div>
                    </div>
                    <div class="row">
                        <label class="col-sm-3"><strong>Total Harga PO</strong></label>
                        <div class="col-sm-9">:
                            <strong>
                                @php
                                    $total = 0;
                                    $po = getDataByID('po', $pc->po_id);
                                    $po_items = \App\Models\PurchaseOrder::getProductItem($po->id);
                                @endphp
                                @foreach ($po_items as $item)
                                    @php
                                        $total += $item->price * $item->qty - (($item->price * $item->qty) * $item->discount / 100);
                                    @endphp
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
                                        $po->send_expense += (11 / 100) * (float)$po->send_expense;
                                    }
                                    $po->ppn = $netto * (float)$po->ppn / 100;
                                    $po->pph = $netto * (float)$po->pph / 100;
                                    $payment_amount = $netto - (float)$po->pph + (float)$po->ppn + (float)$po->send_expense;
                                @endphp
                                {{ $po->currency }} {{ $po->currency == 'IDR' ? number_format($payment_amount,0) : number_format($payment_amount,2) }}
                            </strong>
                        </div>
                    </div>
                    <div class="row">
                        <label class="col-sm-3">Notes</label>
                        <div class="col-sm-8">
                            <textarea name="notes" class="form-control" style="height:100px; resize:none;"
                                placeholder="NOTES...">{{ $pc->notes }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div style="padding:20px;">

                {{-- Tombol Tambah Form --}}
                <div class="d-flex justify-content-start mb-2">
                    <button type="button" class="btn btn-success btn-sm" id="btnAddRow">
                        <i class="ti-plus"></i> Tambah Form
                    </button>
                </div>

                {{-- Tab Headers --}}
                <div class="d-block mb-3">
                    <ul class="nav nav-tabs" role="tablist">
                        @foreach ($components as $index => $component)
                            <li class="nav-item" id="tab-{{ $index }}">
                                <a class="nav-link {{ $loop->first ? 'active' : '' }} d-flex justify-content-between align-items-center"
                                    data-toggle="tab"
                                    href="#component-{{ $index }}"
                                    role="tab"
                                    style="padding-right:5px;">
                                    <span>Form {{ $component['no_si']['value'] ?? 'Tempo Ke-' . $loop->iteration }}</span>
                                    {{-- Tidak ada tombol di tab header --}}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>

                {{-- Tab Content --}}
                <div class="tab-content">
                    @foreach ($components as $index => $componen)
                        @php
                            $isInvLocked    = isLocked($componen, 'invoice');
                            $isFpLocked     = isLocked($componen, 'faktur_pajak');
                            $isSjLocked     = isLocked($componen, 'tgl_surat_jalan');
                            $isProformaLocked = isLocked($componen, 'proforma_invoice');
                            $isRowLocked    = $isInvLocked && $isSjLocked
                                && ($pc->is_form_faktur == 1 ? $isFpLocked : true)
                                && ($pc->is_form_proforma == 1 ? $isProformaLocked : true);
                            $anyVerified    = \App\Models\PaymentCompletionDetail::where('pc_id', $pc->id)
                                ->where('index', $index)->where('verify_status', 1)->exists();
                            $canDelete      = !$isRowLocked && !$anyVerified && !$loop->first;
                        @endphp

                        <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}"
                            id="component-{{ $index }}"
                            role="tabpanel">

                            <div style="padding:20px;">

                                {{-- Tombol Hapus Form --}}
                                @if (!$loop->first)
                                    <div class="d-flex justify-content-end mb-2">
                                        @if ($canDelete)
                                            <button type="button"
                                                class="btn btn-sm btn-danger remove-tab"
                                                data-index="{{ $index }}">
                                                <i class="ti-trash"></i> Hapus Form
                                            </button>
                                        @else
                                            <span class="badge badge-success" style="padding:6px 10px;">
                                                <i class="ti-lock"></i> Sudah Diverifikasi
                                            </span>
                                        @endif
                                    </div>
                                @endif

                                {{-- TABLE INVOICE --}}
                                <table style="width:100%" class="table table-bordered mt-3">
                                    <thead>
                                        <tr>
                                            <th colspan="2" class="text-center card-header text-primary">INVOICE</th>
                                        </tr>
                                        <tr>
                                            <th style="width:200px;" class="text-center">FIELD</th>
                                            <th class="text-center">DATA</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {{-- No Sirkular (readonly) --}}
                                        <tr>
                                            <td class="align-middle font-weight-bold">No Sirkular</td>
                                            <td class="{{ $isInvLocked ? 'bg-locked' : '' }}">
                                                <input type="text" name="no_si[{{ $index }}]"
                                                    class="form-control placeholder-danger"
                                                    value="{{ old('no_si.' . $index, $componen['no_si']['value'] ?? '') }}"
                                                    readonly placeholder="akan terisi otomatis">
                                            </td>
                                        </tr>
                                        {{-- No Invoice --}}
                                        <tr>
                                            <td class="align-middle font-weight-bold">No Invoice <span class="text-danger">*</span></td>
                                            <td class="{{ $isInvLocked ? 'bg-locked' : '' }}">
                                                <input type="text" name="invoice[{{ $index }}]"
                                                    class="form-control"
                                                    value="{{ old('invoice.' . $index, $componen['invoice']['value'] ?? '') }}"
                                                    {{ $isInvLocked ? 'readonly' : 'required' }}
                                                    placeholder="NOMOR INVOICE">
                                            </td>
                                        </tr>
                                        {{-- Nilai Invoice --}}
                                        <tr>
                                            <td class="align-middle font-weight-bold">Nilai Invoice <span class="text-danger">*</span></td>
                                            <td class="{{ $isInvLocked ? 'bg-locked' : '' }}">
                                                <div class="input-group">
                                                    <div class="input-group-append">
                                                        <span class="input-group-text">{{ $po->currency }}</span>
                                                    </div>
                                                    <input type="text" name="nilai_invoice[{{ $index }}]"
                                                        class="form-control currencyyy"
                                                        value="{{ old('nilai_invoice.' . $index, $componen['nilai_invoice']['value'] ?? '') }}"
                                                        {{ $isInvLocked ? 'readonly' : 'required' }}
                                                        placeholder="0.00">
                                                </div>
                                            </td>
                                        </tr>
                                        {{-- Tgl Invoice --}}
                                        <tr>
                                            <td class="align-middle font-weight-bold">Tgl Invoice <span class="text-danger">*</span></td>
                                            <td class="{{ $isInvLocked ? 'bg-locked' : '' }}">
                                                <input type="date" name="tgl_invoice[{{ $index }}]"
                                                    class="form-control"
                                                    value="{{ old('tgl_invoice.' . $index, isset($componen['tgl_invoice']['value']) && $componen['tgl_invoice']['value'] ? \Carbon\Carbon::parse($componen['tgl_invoice']['value'])->format('Y-m-d') : '') }}"
                                                    {{ $isInvLocked ? 'readonly' : 'required' }}>
                                            </td>
                                        </tr>
                                        {{-- Tgl Terima Invoice --}}
                                        <tr>
                                            <td class="align-middle font-weight-bold">Tgl Terima Invoice <span class="text-danger">*</span></td>
                                            <td class="{{ $isInvLocked ? 'bg-locked' : '' }}">
                                                <input type="date" name="tgl_terima_invoice[{{ $index }}]"
                                                    class="form-control"
                                                    value="{{ old('tgl_terima_invoice.' . $index, isset($componen['tgl_terima_invoice']['value']) && $componen['tgl_terima_invoice']['value'] ? \Carbon\Carbon::parse($componen['tgl_terima_invoice']['value'])->format('Y-m-d') : '') }}"
                                                    {{ $isInvLocked ? 'readonly' : 'required' }}>
                                            </td>
                                        </tr>
                                        {{-- File Invoice --}}
                                        <tr>
                                            <td class="align-middle font-weight-bold">File Invoice</td>
                                            <td class="{{ $isInvLocked ? 'bg-locked' : '' }}">
                                                <input type="file" name="file_invoice[{{ $index }}]"
                                                    class="form-control" accept=".pdf,application/pdf"
                                                    {{ $isInvLocked ? 'disabled' : '' }}>
                                                @if (!empty($componen['file_invoice']['value']))
                                                    <div class="mt-1">
                                                        <a href="{{ asset('storage/' . $componen['file_invoice']['value']) }}" target="_blank">
                                                            <i class="ti-eye icon-lg text-danger"></i> View
                                                        </a>
                                                    </div>
                                                @endif
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>

                                {{-- TABLE FAKTUR PAJAK --}}
                                @if ($pc->is_form_faktur == 1)
                                    <table style="width:100%" class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th colspan="2" class="text-center card-header text-primary">FAKTUR PAJAK</th>
                                            </tr>
                                            <tr>
                                                <th style="width:200px;" class="text-center">FIELD</th>
                                                <th class="text-center">DATA</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td class="align-middle font-weight-bold">No Faktur Pajak</td>
                                                <td class="{{ $isFpLocked ? 'bg-locked' : '' }}">
                                                    <input type="text" name="faktur_pajak[{{ $index }}]"
                                                        class="form-control"
                                                        value="{{ old('faktur_pajak.' . $index, $componen['faktur_pajak']['value'] ?? '') }}"
                                                        {{ $isFpLocked ? 'readonly' : '' }}
                                                        placeholder="NOMOR FAKTUR PAJAK">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="align-middle font-weight-bold">File Faktur Pajak</td>
                                                <td class="{{ $isFpLocked ? 'bg-locked' : '' }}">
                                                    <input type="file" name="file_faktur_pajak[{{ $index }}]"
                                                        class="form-control" accept=".pdf,application/pdf"
                                                        {{ $isFpLocked ? 'disabled' : '' }}>
                                                    @if (!empty($componen['file_faktur_pajak']['value']))
                                                        <div class="mt-1">
                                                            <a href="{{ asset('storage/' . $componen['file_faktur_pajak']['value']) }}" target="_blank">
                                                                <i class="ti-eye icon-lg text-danger"></i> View
                                                            </a>
                                                        </div>
                                                    @endif
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                @endif

                                {{-- TABLE PROFORMA INVOICE --}}
                                @if ($pc->is_form_proforma == 1)
                                    <table style="width:100%" class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th colspan="2" class="text-center card-header text-primary">PROFORMA INVOICE</th>
                                            </tr>
                                            <tr>
                                                <th style="width:200px;" class="text-center">FIELD</th>
                                                <th class="text-center">DATA</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td class="align-middle font-weight-bold">No Proforma Invoice</td>
                                                <td class="{{ $isProformaLocked ? 'bg-locked' : '' }}">
                                                    <input type="text" name="proforma_invoice[{{ $index }}]"
                                                        class="form-control"
                                                        value="{{ old('proforma_invoice.' . $index, $componen['proforma_invoice']['value'] ?? '') }}"
                                                        {{ $isProformaLocked ? 'readonly' : '' }}
                                                        placeholder="NOMOR PROFORMA INVOICE">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="align-middle font-weight-bold">Nilai Proforma Invoice</td>
                                                <td class="{{ $isProformaLocked ? 'bg-locked' : '' }}">
                                                    <div class="input-group">
                                                        <div class="input-group-append">
                                                            <span class="input-group-text">{{ $po->currency }}</span>
                                                        </div>
                                                        <input type="text" name="nilai_proforma_invoice[{{ $index }}]"
                                                            class="form-control currencyyy"
                                                            value="{{ old('nilai_proforma_invoice.' . $index, $componen['nilai_proforma_invoice']['value'] ?? '') }}"
                                                            {{ $isProformaLocked ? 'readonly' : '' }}
                                                            placeholder="0.00">
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="align-middle font-weight-bold">File Proforma Invoice</td>
                                                <td class="{{ $isProformaLocked ? 'bg-locked' : '' }}">
                                                    <input type="file" name="file_proforma_invoice[{{ $index }}]"
                                                        class="form-control" accept=".pdf,application/pdf"
                                                        {{ $isProformaLocked ? 'disabled' : '' }}>
                                                    @if (!empty($componen['file_proforma_invoice']['value']))
                                                        <div class="mt-1">
                                                            <a href="{{ asset('storage/' . $componen['file_proforma_invoice']['value']) }}" target="_blank">
                                                                <i class="ti-eye icon-lg text-danger"></i> View
                                                            </a>
                                                        </div>
                                                    @endif
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                @endif

                                {{-- TABLE SURAT JALAN --}}
                                <table style="width:100%" class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th colspan="2" class="text-center card-header text-primary">SURAT JALAN</th>
                                        </tr>
                                        <tr>
                                            <th style="width:200px;" class="text-center">FIELD</th>
                                            <th class="text-center">DATA</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="align-middle font-weight-bold">Tgl Surat Jalan <span class="text-danger">*</span></td>
                                            <td class="{{ $isSjLocked ? 'bg-locked' : '' }}">
                                                <input type="date" name="tgl_surat_jalan[{{ $index }}]"
                                                    class="form-control"
                                                    value="{{ old('tgl_surat_jalan.' . $index, isset($componen['tgl_surat_jalan']['value']) && $componen['tgl_surat_jalan']['value'] ? \Carbon\Carbon::parse($componen['tgl_surat_jalan']['value'])->format('Y-m-d') : '') }}"
                                                    {{ $isSjLocked ? 'readonly' : 'required' }}>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="align-middle font-weight-bold">Periode Tempo <span class="text-danger">*</span></td>
                                            <td class="{{ isLocked($componen, 'periode_tempo') ? 'bg-locked' : '' }}">
                                                <div class="input-group" style="max-width:250px;">
                                                    <input type="number" name="periode_tempo[{{ $index }}]"
                                                        class="form-control"
                                                        value="{{ old('periode_tempo.' . $index, $componen['periode_tempo']['value'] ?? '') }}"
                                                        {{ isLocked($componen, 'periode_tempo') ? 'readonly' : 'required' }}
                                                        min="1">
                                                    <div class="input-group-append">
                                                        <span class="input-group-text">Hari</span>
                                                    </div>
                                                </div>
                                                <small class="text-danger">* Akan mempengaruhi Tgl Jatuh Tempo</small>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>

                                {{-- DETAIL NOTES --}}
                                <div class="form-group">
                                    <input type="text"
                                        name="detail_notes[{{ $index }}]"
                                        class="form-control {{ isLocked($componen, 'detail_notes') ? 'input-locked' : '' }}"
                                        value="{{ old('detail_notes.' . $index, $componen['detail_notes']['value'] ?? '') }}"
                                        {{ isLocked($componen, 'detail_notes') ? 'readonly' : '' }}
                                        placeholder="Detail Notes...">
                                </div>

                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="text-right mt-3">
                    <button type="submit" class="btn btn-primary" id="btnSave">SAVE</button>
                </div>
            </div>
        </div>
    </form>

@endsection

@section('js')
<script>
$(document).ready(function () {

    // Currency mask
    function applyMask() {
        $('.currencyyy').inputmask({
            alias: 'decimal',
            groupSeparator: ',',
            autoGroup: true,
            digits: 2,
            digitsOptional: false,
            placeholder: '0.00',
            allowMinus: false,
            radixPoint: ".",
            autoUnmask: true
        });
    }
    applyMask();

    // Auto aktifkan tab baru setelah redirect dari update() dengan add_tempo_row = 1
    @if (session('active_tab_index') !== null)
        var activeIdx = {{ session('active_tab_index') }};
        var $tabLink = $('.nav-tabs a[href="#component-' + activeIdx + '"]');
        if ($tabLink.length) {
            $tabLink.tab('show');
            $('html, body').animate({ scrollTop: $tabLink.offset().top - 100 }, 300);
        }
    @endif

    // ── Klik "+ Tambah Form" ──
    // 1. Popup konfirmasi
    // 2. Set flag add_tempo_row = 1
    // 3. Submit form update → controller save existing + insert row baru
    // 4. Redirect kembali ke edit, tab baru aktif
    $('#btnAddRow').on('click', function () {
        Swal.fire({
            title: 'Tambah Form Tempo',
            html: 'Data yang sudah diisi akan tersimpan terlebih dahulu, kemudian form tempo baru akan ditambahkan.<br><small class="text-muted">No SI akan digenerate otomatis.</small>',
            type: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Tambah',
            cancelButtonText: 'Batal',
        }).then(function (result) {
            if (result.value) {
                $('#add_tempo_row').val(1);
                $('#formUpdate').submit();
            }
        });
    });

    // ── Hapus tab → POST delete via form update ──
    $(document).on('click', '.remove-tab', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var index = $(this).data('index');
        Swal.fire({
            title: 'Hapus Form Tempo',
            html: 'Data yang sudah diisi pada form ini akan tersimpan terlebih dahulu, lalu form ke-<b>' + (parseInt(index) + 1) + '</b> akan dihapus.',
            type: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal',
        }).then(function (result) {
            if (result.value) {
                $('#add_tempo_row').val(0);
                $('#delete_tempo_index').val(index);
                $('#formUpdate').submit();
            }
        });
    });

    // Validasi field wajib semua tab
    function validateRequiredFields() {
        var errors = [];

        $('#formUpdate .tab-pane').each(function () {
            var tabId  = $(this).attr('id');
            var idx    = tabId.replace('component-', '');
            var label  = 'Form Tempo Ke-' + (parseInt(idx) + 1);

            var invoice = $(this).find('input[name="invoice[' + idx + ']"]');
            if (invoice.length && !invoice.prop('readonly') && !invoice.val().trim())
                errors.push(label + ': No Invoice wajib diisi');

            var nilaiRaw = $(this).find('input[name="nilai_invoice[' + idx + ']"]');
            if (nilaiRaw.length && !nilaiRaw.prop('readonly')) {
                var nVal = parseFloat(nilaiRaw.val().replace(/,/g, '')) || 0;
                if (nVal <= 0) errors.push(label + ': Nilai Invoice wajib diisi');
            }

            var tglInv = $(this).find('input[name="tgl_invoice[' + idx + ']"]');
            if (tglInv.length && !tglInv.prop('readonly') && !tglInv.val())
                errors.push(label + ': Tgl Invoice wajib diisi');

            var tglTerima = $(this).find('input[name="tgl_terima_invoice[' + idx + ']"]');
            if (tglTerima.length && !tglTerima.prop('readonly') && !tglTerima.val())
                errors.push(label + ': Tgl Terima Invoice wajib diisi');

            var tglSj = $(this).find('input[name="tgl_surat_jalan[' + idx + ']"]');
            if (tglSj.length && !tglSj.prop('readonly') && !tglSj.val())
                errors.push(label + ': Tgl Surat Jalan wajib diisi');

            var periode = $(this).find('input[name="periode_tempo[' + idx + ']"]');
            if (periode.length && !periode.prop('readonly')) {
                var pVal = parseInt(periode.val()) || 0;
                if (pVal < 1) errors.push(label + ': Periode Tempo wajib diisi (min. 1 hari)');
            }
        });

        return errors;
    }

    // ── SAVE biasa ──
    $(document).on('click', '#btnSave', function (e) {
        e.preventDefault();

        var errors = validateRequiredFields();
        if (errors.length > 0) {
            Swal.fire({
                title: 'Data Belum Lengkap',
                html: '<ul style="text-align:left; margin:0; padding-left:20px;">'
                    + errors.map(function(err){ return '<li>' + err + '</li>'; }).join('')
                    + '</ul>',
                type: 'warning',
                confirmButtonText: 'OK',
            });
            return;
        }

        Swal.fire({
            title: 'Konfirmasi',
            html: 'Apakah anda yakin menyimpan perubahan ini?',
            type: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, simpan',
            cancelButtonText: 'Batal',
        }).then(function (result) {
            if (result.value) {
                $('#add_tempo_row').val(0);
                $('#delete_tempo_index').val('');
                $('#formUpdate').submit();
            }
        });
    });

});
</script>
@endsection
