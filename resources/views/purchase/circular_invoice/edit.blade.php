@extends('layouts.app')

@section('page-header')
    Sirkular Invoice
@endsection

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('purchasing.circular_invoice') }}">Sirkular Invoice</a></li>
        <li class="breadcrumb-item active" aria-current="page">Edit Sirkular Invoice</li>
    </ol>
@endsection

@section('content')
    <div class="row mB-40">
        <div class="col-sm-12">
            <div class="bgc-white p-20 bd">
                <h4 class="c-grey-900 mB-20">Edit Sirkular Invoice</h4>
                <hr>

                {!! Form::model($invoice, [
                    'method' => 'PUT',
                    'route' => ['purchasing.circular_invoice.update', Hashids::encode($invoice->id)],
                    'id' => 'formCircularInvoice',
                ]) !!}

                {{-- PO --}}
                <div class="form-group row">
                    <label class="col-sm-3 col-form-label text-right">No PO <span class="text-danger">*</span></label>
                    <div class="col-sm-6">
                        {{-- Select Disabled --}}
                        <span class="form-control" readonly>{{ $list_po[$invoice->po_id] ?? '-' }}</span>

                        {{-- Hidden Input --}}
                        {!! Form::hidden('po_id', $invoice->po_id) !!}
                    </div>
                </div>

                {{-- PR --}}
                <div class="form-group row">
                    <label class="col-sm-3 col-form-label text-right">No PR<span class="text-danger">*</span></label>
                    <div class="col-sm-6">
                        <span class="form-control" readonly>{{ $po_detail->pr_no ?? '' }}</span>
                    </div>
                </div>

                {{-- Perusahaan --}}
                <div class="form-group row">
                    <label class="col-sm-3 col-form-label text-right">Perusahaan<span class="text-danger">*</span></label>
                    <div class="col-sm-6">
                        <span class="form-control" readonly>{{ strtoupper($po_detail->company_nama ?? '') }}<span>
                    </div>
                </div>

                {{-- Supplier --}}
                <div class="form-group row">
                    <label class="col-sm-3 col-form-label text-right">Supplier<span class="text-danger">*</span></label>
                    <div class="col-sm-6">
                        <span class="form-control" readonly>{{ strtoupper($po_detail->supplier_nama ?? '') }}</span>
                    </div>
                </div>

                {{-- Type Pembayaran --}}
                <div class="form-group row">
                    <label class="col-sm-3 col-form-label text-right">Type Pembayaran</label>
                    <div class="col-sm-6">
                        {!! Form::select(
                            'type_payment',
                            [
                                1 => 'CBD',
                                2 => 'COD',
                                3 => 'DP',
                                4 => 'Setelah Pekerjaan Selesai',
                            ],
                            old('type_payment', $invoice->type_payment ?? null),
                            [
                                'class' => 'form-control select2',
                                'placeholder' => 'Pilih Type Pembayaran',
                            ],
                        ) !!}

                        @error('type_payment')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                </div>

                {{-- No Faktur Pajak --}}
                <div class="form-group row">
                    <label class="col-sm-3 col-form-label text-right">No Faktur Pajak</label>
                    <div class="col-sm-6">
                        {!! Form::text('tax_invoice', null, ['class' => 'form-control']) !!}
                    </div>
                </div>

                {{-- No Invoice Eksternal --}}
                <div class="form-group row">
                    <label class="col-sm-3 col-form-label text-right">No Invoice Ext</label>
                    <div class="col-sm-6">
                        {!! Form::text('invoice_number_ext', null, ['class' => 'form-control']) !!}
                    </div>
                </div>

                {{-- Tanggal Terima Invoice --}}
                <div class="form-group row">
                    <label class="col-sm-3 col-form-label text-right">Tgl Terima Invoice Ext</label>
                    <div class="col-sm-6">
                        {{-- {!! Form::date('date_received_invoice', $invoice->date_received_invoice ? \Carbon\Carbon::parse($invoice->date_received_invoice)->format('Y-m-d') : null, ['class' => 'form-control']) !!} --}}
                        {!! Form::text('date_received_invoice', null, ['class' => 'form-control datepicker', 'autocomplete' => 'off']) !!}
                    </div>
                </div>

                {{-- Tanggal Invoice --}}
                <div class="form-group row">
                    <label class="col-sm-3 col-form-label text-right">Tgl Invoice Ext</label>
                    <div class="col-sm-6">
                        {{-- {!! Form::date('date_invoice_ext', $invoice->date_invoice_ext ? \Carbon\Carbon::parse($invoice->date_invoice_ext)->format('Y-m-d') : null, ['class' => 'form-control']) !!} --}}
                        {!! Form::text('date_invoice_ext', null, ['class' => 'form-control datepicker', 'autocomplete' => 'off']) !!}
                    </div>
                </div>

                {{-- Tanggal Jatuh Tempo --}}
                <div class="form-group row">
                    <label class="col-sm-3 col-form-label text-right">Tgl Jatuh Tempo Pembayaran</label>
                    <div class="col-sm-6">
                        {{-- {!! Form::date('due_date_payment', $invoice->due_date_payment ? \Carbon\Carbon::parse($invoice->due_date_payment)->format('Y-m-d') : null, ['class' => 'form-control']) !!} --}}
                        {!! Form::text('due_date_payment', null, ['class' => 'form-control datepicker', 'autocomplete' => 'off']) !!}
                    </div>
                </div>

                {{-- Nominal Pembayaran --}}
                <div class="form-group row">
                    <label class="col-sm-3 col-form-label text-right">Nominal Pembayaran</label>
                    <div class="col-sm-6">

                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"> {{ $currency }} </span>
                            </div>
                            {{-- Input Terlihat --}}
                            {!! Form::text(
                                'payment_amount_view',
                                number_format(old('payment_amount', $invoice->payment_amount ?? 0), 2, '.', ','),
                                [
                                    'class' => 'form-control number-format',
                                    'autocomplete' => 'off',
                                ],
                            ) !!}

                            {{-- Hidden Input Yang Di Kirim Server --}}
                            {!! Form::hidden('payment_amount', old('payment_amount', $invoice->payment_amount ?? 0), [
                                'id' => 'payment_amount',
                            ]) !!}
                        </div>

                    </div>
                </div>

                {{-- Tanggal Surat Jalan --}}
                <div class="form-group row">
                    <label class="col-sm-3 col-form-label text-right">Tgl Surat Jalan</label>
                    <div class="col-sm-6">
                        {{-- {!! Form::date('date_delivery_note', $invoice->date_delivery_note ? \Carbon\Carbon::parse($invoice->date_delivery_note)->format('Y-m-d') : null, ['class' => 'form-control']) !!} --}}
                        {!! Form::text('date_delivery_note', null, ['class' => 'form-control datepicker', 'autocomplete' => 'off']) !!}
                    </div>
                </div>

                {{-- Catatan --}}
                <div class="form-group row">
                    <label class="col-sm-3 col-form-label text-right">Catatan</label>
                    <div class="col-sm-6">
                        <input id="note" type="hidden" name="note" value="{{ old('note', $invoice->note ?? '') }}"
                            class="form-control">
                        <trix-editor style="max-width: 700px;" input="note"></trix-editor>
                    </div>
                </div>

                {{-- UPDATE --}}
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
@endsection

@section('js')
    {{-- Input Nominal --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const inputFormatted = document.querySelector('.number-format');
            const inputRaw = document.getElementById('payment_amount');

            function formatNumber(value, preserveDecimal = '') {
                value = value.replace(/^0+(?!\.)/, ''); // hapus 0 depan
                const parts = value.replace(/[^\d.]/g, '').split('.');
                let integerPart = parts[0] || '0';
                let decimalPart = parts[1] || '';

                // Pakai desimal user (jika sedang ketik), bukan hasil dari auto-format
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

                // Jika user sedang ketik angka desimal
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

    {{-- Konfirmasi Simpan --}}
    <script>
        $(document).on('click', '#btnSave', function(e) {
            e.preventDefault();

            var $form = $('#formCircularInvoice');
            var $btn = $(this);

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
            }).then(function(res) {
                if (!res.value) return;
                $form.trigger('submit');
            });
        });
    </script>
@endsection
