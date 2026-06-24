@extends('layouts.app')

@section('page-header')
    Payment Completion
@stop

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('purchasing.payment_completion') }}">Payment Completion</a></li>
        <li class="breadcrumb-item active" aria-current="page">Payment Completion Edit (COD / CBD / DP)</li>
    </ol>
@endsection

@section('content')

    <style>
        .bg-locked  { background-color: #e6ffed !important; }
        .input-locked { background-color: #e6ffed !important; border-color: #28a745; cursor: not-allowed; }
    </style>

    <form method="POST"
        action="{{ route('purchasing.payment_completion.update', Hashids::encode($pc->id)) }}"
        enctype="multipart/form-data"
        id="formUpdate">
        @csrf
        @method('PUT')

        {{-- Flag tambah row baru --}}
        <input type="hidden" name="add_cbd_row" id="add_cbd_row" value="0">
        {{-- Flag hapus row --}}
        <input type="hidden" name="delete_cbd_index" id="delete_cbd_index" value="">

        <div class="card mb-3">
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
                            @php
                                $anyLocked = collect($component)->contains(fn($c) => ($c['islock'] ?? false) == true);
                                $anyVerified = \App\Models\PaymentCompletionDetail::where('pc_id', $pc->id)
                                    ->where('index', $index)
                                    ->where('verify_status', 1)
                                    ->exists();
                                $canDelete = !$anyLocked && !$anyVerified && !$loop->first;
                            @endphp
                            <li class="nav-item" id="tab-{{ $index }}">
                                <a class="nav-link {{ $loop->first ? 'active' : '' }} d-flex align-items-center"
                                    data-toggle="tab"
                                    href="#component-{{ $index }}"
                                    role="tab"
                                    style="padding-right:5px;">
                                    <span>Kelengkapan Ke-{{ $loop->iteration }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>

                {{-- Tab Content --}}
                <div class="tab-content">
                    @foreach ($components as $index => $componen)
                        @php
                            $isInvLocked  = $componen['invoice']['islock'] ?? false;
                            $isFpLocked   = $componen['faktur_pajak']['islock'] ?? false;
                            $isTglJatuhTempo = $componen['tgl_jatuh_tempo']['islock'] ?? false;
                            $isRowLocked  = $isInvLocked && $isFpLocked;
                            $anyVerified2 = \App\Models\PaymentCompletionDetail::where('pc_id', $pc->id)
                                ->where('index', $index)
                                ->where('verify_status', 1)
                                ->exists();
                            $canDelete2   = !$isRowLocked && !$anyVerified2 && !$loop->first;
                        @endphp
                        <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}"
                            id="component-{{ $index }}"
                            role="tabpanel">

                            <div style="padding:20px;">

                                {{-- Tombol Hapus Form — pojok kanan atas --}}
                                @if (!$loop->first)
                                    <div class="d-flex justify-content-end mb-2">
                                        @if ($canDelete2)
                                            <button type="button"
                                                class="btn btn-sm btn-danger remove-tab"
                                                data-index="{{ $index }}"
                                                title="Hapus Form Ini">
                                                <i class="ti-trash"></i> Hapus Form
                                            </button>
                                        @else
                                            <span class="badge badge-success" style="padding:6px 10px;">
                                                <i class="ti-lock"></i> Sudah Diverifikasi
                                            </span>
                                        @endif
                                    </div>
                                @endif

                                {{-- Hidden IDs --}}
                                <input type="hidden" name="rows[{{ $index }}][id_invoice]"
                                    value="{{ $componen['invoice']['id'] ?? '' }}">
                                <input type="hidden" name="rows[{{ $index }}][id_nilai_invoice]"
                                    value="{{ $componen['nilai_invoice']['id'] ?? '' }}">
                                <input type="hidden" name="rows[{{ $index }}][id_tgl_jatuh_tempo]"
                                    value="{{ $componen['tgl_jatuh_tempo']['id'] ?? '' }}">
                                <input type="hidden" name="rows[{{ $index }}][id_file_invoice]"
                                    value="{{ $componen['file_invoice']['id'] ?? '' }}">
                                <input type="hidden" name="rows[{{ $index }}][id_detail_notes]"
                                    value="{{ $componen['detail_notes']['id'] ?? '' }}">
                                @if ($pc->is_form_faktur == 1)
                                    <input type="hidden" name="rows[{{ $index }}][id_faktur_pajak]"
                                        value="{{ $componen['faktur_pajak']['id'] ?? '' }}">
                                    <input type="hidden" name="rows[{{ $index }}][id_file_faktur_pajak]"
                                        value="{{ $componen['file_faktur_pajak']['id'] ?? '' }}">
                                @endif

                                {{-- TABLE INVOICE --}}
                                <table style="width:100%" class="table table-bordered mt-3">
                                    <thead>
                                        <tr>
                                            <th colspan="3" class="text-center card-header text-primary">INVOICE</th>
                                        </tr>
                                        <tr>
                                            <th class="text-center">NO INVOICE</th>
                                            <th class="text-center">NILAI INVOICE</th>
                                            <th class="text-center">FILE INVOICE</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            {{-- No Invoice — wajib hanya jika tidak ada proforma --}}
                                            <td class="{{ $isInvLocked ? 'bg-locked' : '' }}">
                                                <input type="text"
                                                    name="rows[{{ $index }}][invoice]"
                                                    class="form-control"
                                                    value="{{ old('rows.' . $index . '.invoice', $componen['invoice']['value'] ?? '') }}"
                                                    {{ $isInvLocked ? 'readonly' : ($pc->is_form_proforma == 0 ? 'required' : '') }}
                                                    placeholder="NOMOR INVOICE">
                                            </td>
                                            {{-- Nilai Invoice — wajib hanya jika tidak ada proforma --}}
                                            <td class="{{ $isInvLocked ? 'bg-locked' : '' }}">
                                                <div class="input-group">
                                                    <div class="input-group-append">
                                                        <span class="input-group-text">{{ $po->currency }}</span>
                                                    </div>
                                                    <input type="text"
                                                        name="rows[{{ $index }}][nilai_invoice]"
                                                        class="form-control currencyyy"
                                                        value="{{ old('rows.' . $index . '.nilai_invoice', $componen['nilai_invoice']['value'] ?? '') }}"
                                                        {{ $isInvLocked ? 'readonly' : '' }}
                                                        placeholder="0.00">
                                                </div>
                                            </td>
                                            {{-- File Invoice --}}
                                            <td class="{{ $isInvLocked ? 'bg-locked' : '' }}">
                                                <input type="file"
                                                    name="file_invoice[{{ $index }}]"
                                                    class="form-control"
                                                    accept=".pdf,application/pdf"
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

                                @if ($pc->is_form_faktur == 1)
                                    {{-- TABLE FAKTUR PAJAK --}}
                                    <table style="width:100%" class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th colspan="2" class="text-center card-header text-primary">FAKTUR PAJAK</th>
                                            </tr>
                                            <tr>
                                                <th class="text-center">NO FAKTUR PAJAK</th>
                                                <th class="text-center">FILE FAKTUR PAJAK</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td class="{{ $isFpLocked ? 'bg-locked' : '' }}">
                                                    <input type="text"
                                                        name="rows[{{ $index }}][faktur_pajak]"
                                                        class="form-control"
                                                        value="{{ old('rows.' . $index . '.faktur_pajak', $componen['faktur_pajak']['value'] ?? '') }}"
                                                        {{ $isFpLocked ? 'readonly' : '' }}
                                                        placeholder="NOMOR FAKTUR PAJAK">
                                                </td>
                                                <td class="{{ $isFpLocked ? 'bg-locked' : '' }}">
                                                    <input type="file"
                                                        name="file_faktur_pajak[{{ $index }}]"
                                                        class="form-control"
                                                        accept=".pdf,application/pdf"
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
                                    @php $isProformaLocked = $componen['proforma_invoice']['islock'] ?? false; @endphp
                                    <table style="width:100%" class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th colspan="3" class="text-center card-header text-primary">PROFORMA INVOICE</th>
                                            </tr>
                                            <tr>
                                                <th class="text-center">NO PROFORMA INVOICE</th>
                                                <th class="text-center">NILAI PROFORMA INVOICE</th>
                                                <th class="text-center">FILE PROFORMA INVOICE</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td class="{{ $isProformaLocked ? 'bg-locked' : '' }}">
                                                    <input type="text"
                                                        name="rows[{{ $index }}][proforma_invoice]"
                                                        class="form-control"
                                                        value="{{ old('rows.' . $index . '.proforma_invoice', $componen['proforma_invoice']['value'] ?? '') }}"
                                                        {{ $isProformaLocked ? 'readonly' : '' }}
                                                        placeholder="NOMOR PROFORMA INVOICE">
                                                </td>
                                                <td class="{{ $isProformaLocked ? 'bg-locked' : '' }}">
                                                    <div class="input-group">
                                                        <div class="input-group-append">
                                                            <span class="input-group-text">{{ $po->currency }}</span>
                                                        </div>
                                                        <input type="text"
                                                            name="rows[{{ $index }}][nilai_proforma_invoice]"
                                                            class="form-control currencyyy"
                                                            value="{{ old('rows.' . $index . '.nilai_proforma_invoice', $componen['nilai_proforma_invoice']['value'] ?? '') }}"
                                                            {{ $isProformaLocked ? 'readonly' : '' }}
                                                            placeholder="0.00">
                                                    </div>
                                                </td>
                                                <td class="{{ $isProformaLocked ? 'bg-locked' : '' }}">
                                                    <input type="file"
                                                        name="file_proforma_invoice[{{ $index }}]"
                                                        class="form-control"
                                                        accept=".pdf,application/pdf"
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

                                    {{-- Hidden IDs proforma --}}
                                    <input type="hidden" name="rows[{{ $index }}][id_proforma_invoice]"
                                        value="{{ $componen['proforma_invoice']['id'] ?? '' }}">
                                    <input type="hidden" name="rows[{{ $index }}][id_nilai_proforma_invoice]"
                                        value="{{ $componen['nilai_proforma_invoice']['id'] ?? '' }}">
                                    <input type="hidden" name="rows[{{ $index }}][id_file_proforma_invoice]"
                                        value="{{ $componen['file_proforma_invoice']['id'] ?? '' }}">
                                @endif

                                {{-- TABLE TANGGAL AKHIR BAYAR --}}
                                <table style="width:100%" class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th class="text-center card-header text-primary">TANGGAL AKHIR BAYAR</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="{{ $isTglJatuhTempo ? 'bg-locked' : '' }}">
                                                <input type="date"
                                                    name="rows[{{ $index }}][tgl_jatuh_tempo]"
                                                    class="form-control"
                                                    value="{{ old('rows.' . $index . '.tgl_jatuh_tempo', isset($componen['tgl_jatuh_tempo']['value']) && $componen['tgl_jatuh_tempo']['value'] ? \Carbon\Carbon::parse($componen['tgl_jatuh_tempo']['value'])->format('Y-m-d') : '') }}"
                                                    {{ $isTglJatuhTempo ? 'readonly' : '' }}>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>

                                {{-- DETAIL NOTES --}}
                                <div class="form-group">
                                    <input type="text"
                                        name="rows[{{ $index }}][detail_notes]"
                                        class="form-control {{ $isRowLocked ? 'input-locked' : '' }}"
                                        value="{{ old('rows.' . $index . '.detail_notes', $componen['detail_notes']['value'] ?? '') }}"
                                        {{ $isRowLocked ? 'readonly' : '' }}
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

    // Auto aktifkan tab setelah redirect dengan active_tab_index
    @if (session('active_tab_index') !== null)
        var activeIdx = {{ session('active_tab_index') }};
        var $tabLink = $('.nav-tabs a[href="#component-' + activeIdx + '"]');
        if ($tabLink.length) {
            $tabLink.tab('show');
            $('html, body').animate({ scrollTop: $tabLink.offset().top - 100 }, 300);
        }
    @endif

    // ── Tambah Form ──
    $('#btnAddRow').on('click', function () {
        Swal.fire({
            title: 'Tambah Form',
            html: 'Data yang sudah diisi akan tersimpan, kemudian form baru akan ditambahkan.',
            type: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Tambah',
            cancelButtonText: 'Batal',
        }).then(function (result) {
            if (result.value) {
                $('#add_cbd_row').val(1);
                $('#delete_cbd_index').val('');
                $('#formUpdate').submit();
            }
        });
    });

    // ── Hapus Form ──
    $(document).on('click', '.remove-tab', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var index = $(this).data('index');
        Swal.fire({
            title: 'Hapus Form',
            html: 'Data yang sudah diisi akan tersimpan, lalu form <b>Kelengkapan Ke-' + (parseInt(index) + 1) + '</b> akan dihapus.',
            type: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal',
        }).then(function (result) {
            if (result.value) {
                $('#add_cbd_row').val(0);
                $('#delete_cbd_index').val(index);
                $('#formUpdate').submit();
            }
        });
    });

    var hasProforma = {{ $pc->is_form_proforma == 1 ? 'true' : 'false' }};

    // Validasi field wajib
    function validateRequiredFields() {
        var errors = [];

        $('#formUpdate .tab-pane').each(function () {
            var tabId  = $(this).attr('id');
            var idx    = tabId.replace('component-', '');
            var tabNum = parseInt(idx) + 1;
            var label  = 'Kelengkapan Ke-' + tabNum;

            // No Invoice — wajib hanya jika tidak ada proforma
            if (!hasProforma) {
                var invoice = $(this).find('input[name="rows[' + idx + '][invoice]"]');
                if (invoice.length && !invoice.prop('readonly') && !invoice.val().trim())
                    errors.push(label + ': No Invoice wajib diisi');

                var nilai = $(this).find('input[name="rows[' + idx + '][nilai_invoice]"]');
                if (nilai.length && !nilai.prop('readonly')) {
                    var nVal = parseFloat(nilai.val().replace(/,/g, '')) || 0;
                    if (nVal <= 0) errors.push(label + ': Nilai Invoice wajib diisi');
                }
            }
        });

        return errors;
    }

    // ── SAVE ──
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
                $('#add_cbd_row').val(0);
                $('#delete_cbd_index').val('');
                $('#formUpdate').submit();
            }
        });
    });

});
</script>
@endsection
