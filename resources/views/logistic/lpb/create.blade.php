@extends('layouts.app')

@section('page-header')
    Laporan Penerimaan Barang
@stop

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('logistic.lpb.index') }}">Laporan Penerimaan Barang</a></li>
        <li class="breadcrumb-item active" aria-current="page">Tambah</li>
    </ol>
@endsection


@section('content')
    {!! Form::open(['method' => 'POST', 'route' => ['logistic.lpb.store'], 'id' => 'formLPB']) !!}
    <input name="po_id" type="hidden" value="{{ $po->id }}">
    <input name="token" type="hidden" value="{{ $token }}">
    <div class="bgc-white p-30 bd">
        <h6>Pembuatan LPB : {{ $po->doc_no }}</h6>
        <hr class='mB-30'>
        <div class="row">
            <div class="col-sm-6">
                <div class="row">
                    <label class="col-sm-4">No. PO </label>
                    <div class="col-sm-7">: {{ $po->doc_no }}</div>
                </div>
                <div class="row">
                    <label class="col-sm-4">Dibuat Oleh</label>
                    <div class="col-sm-7">: {{ $po->created }} [ {{ idDate($po->created_at) }}]
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="row">
                    <label class="col-sm-3">No. PR </label>
                    <div class="col-sm-8">: {{ $po->pr_no }}</div>
                </div>
                <div class="row">
                    <label class="col-sm-3">Supplier</label>
                    <div class="col-sm-8">:
                        {{ $po->supplier }}
                    </div>
                </div>
                <div class="row">
                    <label class="col-sm-3">Supplier Kontak</label>
                    <div class="col-sm-8">:
                        {{ $po->picName }} / Telp. {{ $po->picTelp }}
                    </div>
                </div>

            </div>
        </div>

        <h6 class='mT-30'>Daftar Item Barang</h6>
        <hr>
        <input type="hidden" name="companyCode" value="{{ $location->companyCode }}">
        <input type="hidden" name="locationCode" value="{{ $location->alias }}">
        <input type="hidden" name="location_id" value="{{ $location->id }}">

        <div class="form-group row">
            <label class="col-sm-2 ">Penerima <span class="text-danger">*</span></label>
            <div class="col-sm-5">
                <input type="text" class="form-control" name="received_by" value="" required>
            </div>
        </div>

        <div class="alert alert-info">
            - Checklist Daftar Barang dan Masukan Jumlah item yang diterima dari Purchase Order (PO) untuk membuat Laporan
            Penerimaan Barang. <br>
            - Jika jumlah barang yang diterima tidak sama dengan dipesan maka status PO akan menjadi Parsial
        </div>
        <table class="table table-bordered mt-2">
            <thead>
                <tr>
                    <th rowspan="2" style="width:50px"><input class="magic-checkbox" name="checkedAll" id="checkedAll"
                            type="checkbox"><label for="checkedAll"><label> </th>
                    <th rowspan="2" style="width:300px">Nama Barang</th>
                    <th rowspan="2" style="width:300px">Spesifikasi</th>
                    <th colspan="2" class="text-center">Jumlah </th>
                    <th rowspan="2" class="text-center">Satuan</th>
                    {{-- <th rowspan="2" class="text-center" >Satuan Inventory</th>
                    <th rowspan="2" class="text-center" >Konversi</th> --}}
                    <th rowspan="2" style="width:300px">Catatan</th>
                </tr>
                <tr>
                    <th style="width:150px" class="text-center">Dipesan</th>
                    <th style="width:150px" class="text-center">Diterima</th>
                </tr>
            </thead>
            <tbody class="item_form">
                @if (count($po_items) > 0)
                    @php
                        $no = 1;
                    @endphp
                    @foreach ($po_items as $item)
                        <tr class="product_{{ $item->id }}">
                            <input name="po_item_id[]" type="hidden" value="{{ $item->id }}">
                            <input name="" type="hidden" value="{{ $item->productConversion }}"
                                id="conversion_lpb_item{{ $item->id }}">
                            <input name="pr_item_id[]" type="hidden" value="{{ $item->pr_item_id }}">
                            <td><input type="checkbox" name="iscreateLPB[]" class="checkSingle magic-checkbox"
                                    value="{{ $item->id }}" id="{{ $item->id }}"><label
                                    for="{{ $item->id }}"></label></td>
                            <td>
                                {{ $item->productCode }} - {{ $item->product }} {!! $item->productPartNumber != null ? '<br> PN/Spec: ' . $item->productPartNumber : '' !!}
                                <input name="product_id[]" type="hidden" value="{{ $item->product_id }}">
                            </td>
                            <td>
                                {!! $item->specification !!} <br>
                                {{ $item->productBrand != null ? 'Brand: ' . $item->productBrand : '' }} </small>
                            </td>
                            <td class="text-center">{{ $item->qty }}
                                @if ($item->lpb_status == 2)
                                    <br>Belum diterima: {{ $item->qty_parsial }}
                                    <input name="qty_po[]" type="hidden" value="{{ $item->qty_parsial }}"
                                        id='qty_po_{{ $item->id }}'>
                                @else
                                    <input name="qty_po[]" type="hidden" value="{{ $item->qty }}"
                                        id='qty_po_{{ $item->id }}'>
                                @endif
                            </td>

                            <td class="text-center"><input type="number" name="qty[]" class="form-control"
                                    id='qty_lpb_{{ $item->id }}' value="0" min="0"
                                    oninput="this.value = Math.abs(this.value)"></td>
                            <td>{{ $item->measure }}</td>
                            {{-- <td>{{ $item->measure_inventory }}</td>
                                <td id="product_conversion_{{$item->id}}"></td> --}}
                            <td>
                                <textarea name="notes[]" class="form-control"></textarea>
                            </td>
                        </tr>
                        @php
                            $no++;
                        @endphp
                    @endforeach
                @endif
            </tbody>
        </table>

    </div>
    <div class="mt-4">
        <a href="{{ route('logistic.lpb.index') }}"
            class="btn btn-light mr-1 text-uppercase fsz-sm fw-600">{{ trans('Cancel') }}</a>
        <!-- <input class="btn btn-info text-uppercase fsz-sm fw-600" type="submit" name="save" value="Save as Draft" id="btn-draft"> -->
        <input type="hidden" value="0" name="status">
        <input class="btn btn-danger text-uppercase fsz-sm fw-600" type="submit" name="publish" id="btn-submit"
            value="Publish LPB">
    </div>
    {!! Form::close() !!}
@stop


@section('js')

    <script type='text/javascript'>
        $(document).ready(function() {
            let formSubmitting = false;

            @foreach ($po_items as $item)
                $('#qty_lpb_{{ $item->id }}').on('keyup', function(e) {
                    $('.product_{{ $item->id }}').css("background-color", "#fff");
                    var qty_po = $('#qty_po_{{ $item->id }}').val();
                    var qty_lpb = $('#qty_lpb_{{ $item->id }}').val();
                    if (parseInt(qty_lpb) > parseInt(qty_po)) {
                        e.preventDefault();
                        Swal.fire(
                            'Peringatan!',
                            'QTY LPB tidak boleh melebihi QTY PO',
                            'warning'
                        );
                        $('#qty_lpb_{{ $item->id }}').val('');
                    }
                });
            @endforeach



            $("#formLPB").validate({
                rules: {
                    "iscreateLPB[]": {
                        required: true,
                        minlength: 1
                    }
                },
                messages: {
                    "iscreateLPB[]": "Minimal Checklist 1 Item"
                }
            });

            $(document).on("click", "#btn-submit", function(e) {
                if (formSubmitting) return;

                $('input[name="status"]').val('1');
                var _this = $(this);
                var form = _this.parents('form');

                form.validate({
                    onfocusout: false,
                    invalidHandler: function(form, validator) {
                        var errors = validator.numberOfInvalids();
                        if (errors) {
                            validator.errorList[0].element.focus();
                        }
                    }
                });

                var checkbox = document.querySelector('input[name="iscreateLPB[]"]:checked');
                if (!checkbox) {
                    Swal.fire(
                        'Informasi',
                        'Minimal Checklist 1 Item untuk pembuatan LPB',
                        'warning'
                    );
                    return false;
                }

                var arr_id = [];
                var validform = ''; // ini aneh

                $('input[name="iscreateLPB[]"]:checked').each(function() {
                    var checkbox = $(this).val();
                    var qty = $("#qty_lpb_" + checkbox).val();

                    if (qty == 0) {
                        arr_id.push(checkbox);
                        var current_arr = arr_id.pop();
                        $('.product_' + current_arr).css("background-color", "#ffd5d5");
                        $('.product_' + current_arr).find('input').focus();
                        validform = false;
                        Swal.fire(
                            'Informasi',
                            'Minimal QTY LPB harus diisi 1 Item',
                            'warning'
                        );
                        return false;
                    } else {
                        validform = true;
                    }
                });

                e.preventDefault();
                if (form.valid() && validform === true) {

                    Swal.fire({
                        title: 'Konfirmasi',
                        text: 'Apakah anda yakin melanjutkan ini?',
                        type: 'question',
                        showCancelButton: true,
                        confirmButtonColor: 'null',
                        cancelButtonColor: 'null',
                        confirmButtonClass: 'btn btn-danger',
                        cancelButtonClass: 'btn btn-primary',
                        confirmButtonText: 'Ya, lanjut',
                        cancelButtonText: 'Batal',
                    }).then(res => {
                        if (res.value) {
                            formSubmitting = true;

                            form.find('input, textarea, select').prop('readonly', true);
                            $('#btn-submit').prop('disabled', true);
                            $('#btn-draft').prop('disabled', true);
                            $('body').append(
                                '<div class="loading-lock h-screen w-screen d-flex justify-content-center align-items-center"><div class="spinner-border text-dark m-5" role="status"><span class="sr-only">Loading...</span></div></div>'
                            );

                            _this.closest("form").submit();
                        }
                    });
                }
            });

            $(document).on("click", "#btn-draft", function(e) {
                if (formSubmitting) return;

                $('input[name="status"]').val('1');

                var _this = $(this);
                var form = _this.parents('form');

                form.validate({
                    onfocusout: false,
                    invalidHandler: function(form, validator) {
                        var errors = validator.numberOfInvalids();
                        if (errors) {
                            validator.errorList[0].element.focus();
                        }
                    }
                });

                var checkbox = document.querySelector('input[name="iscreateLPB[]"]:checked');
                if (!checkbox) {
                    Swal.fire(
                        'Informasi',
                        'Minimal Checklist 1 Item untuk pembuatan LPB',
                        'warning'
                    );
                    return false;
                }

                e.preventDefault();
                if (form.valid()) {
                    Swal.fire({
                        title: 'Konfirmasi',
                        text: 'Apakah anda yakin melanjutkan ini?',
                        type: 'question',
                        showCancelButton: true,
                        confirmButtonColor: 'null',
                        cancelButtonColor: 'null',
                        confirmButtonClass: 'btn btn-danger',
                        cancelButtonClass: 'btn btn-primary',
                        confirmButtonText: 'Ya, lanjut',
                        cancelButtonText: 'Batal',
                    }).then(res => {
                        if (res.value) {
                            formSubmitting = true;

                            form.find('input, textarea, select').prop('readonly', true);
                            $('#btn-submit').prop('disabled', true);
                            $('#btn-draft').prop('disabled', true);
                            $('body').append(
                                '<div class="loading-lock h-screen w-screen d-flex justify-content-center align-items-center"><div class="spinner-border text-dark m-5" role="status"><span class="sr-only">Loading...</span></div></div>'
                            );

                            _this.closest("form").submit();
                        }
                    });
                }

            });

            $("#checkedAll").change(function() {
                if (this.checked) {
                    $(".checkSingle").each(function() {
                        this.checked = true;
                    })
                } else {
                    $(".checkSingle").each(function() {
                        this.checked = false;
                    })
                }
            });

            $(".checkSingle").click(function() {
                if ($(this).is(":checked")) {
                    var isAllChecked = 0;
                    $(".checkSingle").each(function() {
                        if (!this.checked)
                            isAllChecked = 1;
                    })
                    if (isAllChecked == 0) {
                        $("#checkedAll").prop("checked", true);
                    }
                } else {
                    $("#checkedAll").prop("checked", false);
                }
            });

            $('[id^="qty_lpb_"]').change(function() {
                let id = $(this).attr('id').replace('qty_lpb_', '');
                let conversion = parseFloat($(`#conversion_lpb_item${id}`).val());
                let quantity = parseFloat($(this).val());
                let total = quantity * conversion;

                $(`#product_conversion_${id}`).html(total);
            });

        });
    </script>
@stop
