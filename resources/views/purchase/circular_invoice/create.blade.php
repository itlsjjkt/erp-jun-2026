@extends('layouts.app')

@section('page-header')
    Sirkular Invoice
@endsection

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('purchasing.circular_invoice') }}">Sirkular Invoice</a></li>
        <li class="breadcrumb-item active" aria-current="page">Tambah Sirkular Invoice</li>
    </ol>
@endsection

@section('content')
    <div class="row mB-40">
        <div class="col-sm-12">
            <div class="bgc-white p-20 bd">
                <h4 class="c-grey-900 mB-20">Tambah Sirkular Invoice</h4>
                <hr>

                {!! Form::open([
                    'method' => 'POST',
                    'route' => ['purchasing.circular_invoice.store'],
                    'id' => 'formCircularInvoice',
                ]) !!}

                {{-- PO --}}
                <div class="form-group row">
                    <label class="col-sm-3 col-form-label text-right">No PO <span class="text-danger">*</span></label>
                    <div class="col-sm-6">
                        <span class="form-control" readonly>{{ $list_po->doc_no }}</span>
                        {{-- Hidden Input - Simpan Nilai Tetap Untuk Di Kirim --}}
                        {!! Form::hidden('po_id', $po_id) !!}
                    </div>
                </div>

                {{-- PR --}}
                <div class="form-group row">
                    <label class="col-sm-3 col-form-label text-right">No PR</label>
                    <div class="col-sm-6">
                        <span class="form-control" readonly>{{ $list_po->pr_no }}</span>
                    </div>
                </div>

                {{-- Perusahaan --}}
                <div class="form-group row">
                    <label class="col-sm-3 col-form-label text-right">Perusahaan</label>
                    <div class="col-sm-6">
                        <span class="form-control" readonly>{{ strtoupper($list_po->company_nama) }}</span>
                    </div>
                </div>

                {{-- Supplier --}}
                <div class="form-group row">
                    <label class="col-sm-3 col-form-label text-right">Supplier</label>
                    <div class="col-sm-6">
                        <span class="form-control" readonly>{{ $list_po->supplier_nama }}</span>
                    </div>
                </div>

                {{-- Type Payment --}}
                <div class="form-group row">
                    <label class="col-sm-3 col-form-label text-righr">Type Pembayaran</label>
                    <div class="col-sm-6">
                        {!! Form::select(
                            'type_payment',
                            [
                                1 => 'CBD',
                                2 => 'COD',
                                3 => 'DP',
                                4 => 'Setelah Pekerjaan Selesai',
                            ],
                            old('type_payment'),
                            ['class' => 'form-control, select2', 'placeholder' => ''],
                        ) !!}
                    </div>
                </div>

                {{-- Faktur Pajak Eksternal --}}
                <div class="form-group row">
                    <label class="col-sm-3 col-form-label text-right">No Faktur Pajak</label>
                    <div class="col-sm-6">
                        {!! Form::text('tax_invoice', old('tax_invoice'), ['class' => 'form-control']) !!}
                    </div>
                </div>

                {{-- Nomor Invoice Eksternal --}}
                <div class="form-group row">
                    <label class="col-sm-3 col-form-label text-right">No Invoice Ext</label>
                    <div class="col-sm-6">
                        {!! Form::text('invoice_number_ext', old('invoice_number_ext'), ['class' => 'form-control']) !!}
                    </div>
                </div>

                {{-- Tanggal Terima Invoice --}}
                <div class="form-group row">
                    <label class="col-sm-3 col-form-label text-right">Tgl Terima Invoice Ext</label>
                    <div class="col-sm-6">
                        {{-- {!! Form::date('date_received_invoice', old('date_received_invoice'), ['class' => 'form-control']) !!} --}}
                        {!! Form::text('date_received_invoice', null, ['class' => 'form-control datepicker', 'autocomplete' => 'off']) !!}
                    </div>
                </div>

                {{-- Tanggal Invoice Eksternal --}}
                <div class="form-group row">
                    <label class="col-sm-3 col-form-label text-right">Tgl Invoice Ext</label>
                    <div class="col-sm-6">
                        {{-- {!! Form::date('date_invoice_ext', old('date_invoice_ext'), ['class' => 'form-control']) !!} --}}
                        {!! Form::text('date_invoice_ext', null, ['class' => 'form-control datepicker', 'autocomplete' => 'off']) !!}
                    </div>
                </div>

                {{-- Tanggal Jatuh Tempo --}}
                <div class="form-group row">
                    <label class="col-sm-3 col-form-label text-right">Tgl Jatuh Tempo & Tgl Surat Jalan</label>
                    <div class="col-sm-6">
                        {{-- {!! Form::date('due_date_payment', old('due_date_payment'), ['class' => 'form-control']) !!} --}}
                        {!! Form::text('due_date_payment', null, ['class' => 'form-control datepicker', 'autocomplete' => 'off']) !!}
                    </div>
                </div>

                @php
                    $sisa = $list_po->sisa_bayar ?? ($list_po->harga_po ?? 0);
                @endphp

                {{-- Nominal Pembayaran --}}
                <div class="form-group row">
                    <label class="col-sm-3 col-form-label text-right">Nominal Pembayaran</label>
                    <div class="col-sm-6">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"> {{ $currency }} </span>
                            </div>
                            {!! Form::text('payment_amount_view', number_format($sisa, 2, '.', ','), [
                                'class' => 'form-control number-format',
                                'autocomplete' => 'off',
                                'id' => 'payment_amount_view',
                            ]) !!}

                            {!! Form::hidden('payment_amount', $sisa, ['id' => 'payment_amount']) !!}
                        </div>
                        <small id="paymentWarning" class="text-danger mt-2" style="display:none;">
                            Nominal Pembayaran Sirkular Invoice Melebihi Nilai Pembayaran PO
                        </small>
                    </div>
                </div>

                {{-- Catatan --}}
                <div class="form-group row">
                    <label class="col-sm-3 col-form-label text-right">Catatan</label>
                    <div class="col-sm-6">
                        <input id="note" type="hidden" name="note" value="" class="form-control">
                        <trix-editor style="max-width: 780px;" input="note"></trix-editor>
                    </div>
                </div>

                {{-- SAVE --}}
                <div class="form-group row">
                    <label class="col-sm-3 col-form-label text-right"></label>
                    <div class="col-sm-6">
                        <button type="button" id="btnSave" class="btn btn-primary text-uppercase fsz-sm fw-600">
                            SIMPAN
                        </button>
                    </div>
                </div>

                {!! Form::close() !!}

            </div>
        </div>
    </div>

    {{-- LIST INVOICE --}}
    <div class="row mB-40">
        <div class="col-sm-12">
            <div class="bgc-white p-20 bd">
                <h4 class="c-grey-900 mB-20">Sirkular Invoice</h4>
                <hr>

                @if ($invoices->count() > 0)
                    <table class="table table-bordered" id="dataTables">
                        <thead>
                            <tr>
                                <th>No SI</th>
                                <th>No PO</th>
                                <th>No PR</th>
                                <th>Tgl SI</th>
                                <th>No Invoice Ext</th>
                                <th>Tgl Invoice Tgl</th>
                                <th>Tgl Jatuh Tempo</th>
                                <th>Type Pembayaran</th>
                                <th>Nominal</th>
                                <th>Dibuat Oleh</th>
                                <th>Note</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($invoices as $invoice)
                                <tr>
                                    {{-- <td class="text-center">{{ $loop->iteration }}</td>  --}}
                                    <td>{{ $invoice->doc_no }}</td>
                                    <td>{{ $invoice->po_no }}</td>
                                    <td>{{ $invoice->pr_no }}</td>
                                    <td>{{ date('d/m/Y', strtotime($invoice->date_invoice_ext)) }}</td>
                                    <td>{{ $invoice->invoice_number_ext }}</td>
                                    <td>{{ $invoice->date_invoice_ext }}</td>
                                    <td>{{ $invoice->due_date_payment }}</td>
                                    <td>
                                        @if ($invoice->type_payment == 1)
                                            {!! getTypeBodyEmal(1) !!}
                                        @elseif($invoice->type_payment == 2)
                                            {!! getTypeBodyEmal(2) !!}
                                        @elseif($invoice->type_payment == 3)
                                            {!! getTypeBodyEmal(3) !!}
                                        @elseif($invoice->type_payment == 4)
                                            {!! getTypeBodyEmal(4) !!}
                                        @else
                                            {!! getTypeBodyEmal(null) !!}
                                        @endif
                                    </td>
                                    <td>{{ $invoice->mata_uang }}
                                        {{ number_format($invoice->payment_amount, 2, ',', '.') }}</td>
                                    <td>{{ $invoice->nama_pembuat }}</td>
                                    <td>{!! $invoice->note !!}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p class="text-muted">Tidak Ada Sirkular Invoice Dalam PO Ini</p>
                @endif

            </div>
        </div>
    </div>

@endsection

@section('js')
    {{-- Input Nominal --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const inputFormatted = document.querySelector('.number-format');
            const inputRaw = document.getElementById('payment_amount');

            function formatNumber(value, preserveDecimal = '') {
                value = value.replace(/^0+(?!\.)/, '');
                const parts = value.replace(/[^\d.]/g, '').split('.');
                let integerPart = parts[0] || '0';
                let decimalPart = parts[1] || '';

                // pakai desimal user (jika sedang ketik), bukan hasil dari auto-format
                if (preserveDecimal !== '') {
                    decimalPart = preserveDecimal;
                } else if (decimalPart.length > 2) {
                    decimalPart = decimalPart.substring(0, 2);
                }

                integerPart = integerPart.replace(/\B(?=(\d{3})+(?!\d))/g, ',');

                return `${integerPart}.${decimalPart.padEnd(2, '0')}`;
            }

            function unformatNumber(formatted) {
                return formatted.replace(/,/g, '');
            }

            let decimalFocus = false;
            let typingDecimal = '';

            inputFormatted.addEventListener('keydown', function(e) {
                if (e.key === '.') {
                    e.preventDefault();
                    const dotIndex = this.value.indexOf('.');
                    if (dotIndex !== -1) {
                        this.setSelectionRange(dotIndex + 1, dotIndex + 1);
                        decimalFocus = true;
                        typingDecimal = ''; // reset decimal yang diketik
                    }
                }

                // jika user sedang ketik angka desimal
                const dotIndex = this.value.indexOf('.');
                if (dotIndex !== -1 && this.selectionStart > dotIndex) {
                    if (/^[0-9]$/.test(e.key)) {
                        if (typingDecimal.length < 2) {
                            typingDecimal += e.key;
                        }
                    }
                }
            });

            inputFormatted.addEventListener('input', function() {
                const cursorPos = this.selectionStart;
                const originalLength = this.value.length;

                const cleaned = unformatNumber(this.value);
                const formatted = formatNumber(cleaned, typingDecimal);
                const newLength = formatted.length;

                this.value = formatted;
                inputRaw.value = parseFloat(unformatNumber(formatted)) || 0;

                const dotIndex = formatted.indexOf('.');

                // fokus desimal
                if (decimalFocus) {
                    this.setSelectionRange(dotIndex + 1 + typingDecimal.length, dotIndex + 1 + typingDecimal
                        .length);
                    decimalFocus = false;
                    return;
                }

                // reset typingDecimal jika sudah 2 digit
                if (typingDecimal.length >= 2) typingDecimal = '';

                // posisi kursor
                if (cursorPos <= dotIndex) {
                    const diff = newLength - originalLength;
                    const adjustedPos = Math.min(dotIndex, cursorPos + diff);
                    this.setSelectionRange(adjustedPos, adjustedPos);
                } else {
                    const diff = newLength - originalLength;
                    const adjustedPos = cursorPos + diff;
                    this.setSelectionRange(adjustedPos, adjustedPos);
                }
            });

            inputFormatted.addEventListener('focus', function() {
                setTimeout(() => {
                    this.setSelectionRange(0, 0);
                }, 0);
            });

            // set default saat halaman dimuat
            const defaultVal = parseFloat(inputRaw.value || 0).toFixed(2);
            inputFormatted.value = formatNumber(defaultVal);
        });
    </script>

    {{-- Input Tanggal --}}
    <script>
        $('.datepicker').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true,
        });
    </script>

    {{-- Cek Nilai CI Tidak Melebihin Nilai PO --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let maxAmount = parseFloat("{{ $list_po->harga_po ?? 0 }}");
            let input = document.getElementById('payment_amount_view');
            let warning = document.getElementById('paymentWarning');

            input.addEventListener('input', function() {
                let val = parseFloat(this.value.replace(/,/g, '')) || 0;

                if (val > maxAmount) {
                    warning.style.display = 'block';
                } else {
                    warning.style.display = 'none';
                }
            });
        });
    </script>

    {{-- Konfirmasi Simpan --}}
    <script>
        $(document).on('click', '#btnSave', function(e) {
            e.preventDefault();

            const $form = $('#formCircularInvoice');

            if (typeof $form.valid === 'function' && !$form.valid()) return;

            Swal.fire({
                title: 'Konfirmasi',
                text: 'Pastikan Data Sudah Benar !',
                type: 'question',
                showCancelButton: true,
                confirmButtonClass: 'btn btn-danger',
                cancelButtonClass: 'btn btn-primary',
                confirmButtonText: 'Simpan',
                cancelButtonText: 'Batal'
            }).then(res => {
                if (!res.value) return;
                $form.attr('action', "{{ route('purchasing.circular_invoice.store') }}").submit();
            });
        });
    </script>
@endsection
