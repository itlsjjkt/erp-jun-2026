@extends('layouts.app')

@section('page-header')
    Warehouse Transfer Out
@stop

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('logistic.inventory.index') }}">Inventory</a></li>
        <li class="breadcrumb-item"><a href="{{ route('logistic.transfer_out.index') }}">Warehouse Transfer Out</a></li>
        <li class="breadcrumb-item active" aria-current="page">Input</li>
    </ol>
@endsection


@section('content')
    {!! Form::open(['method' => 'POST', 'route' => ['logistic.transfer_out.store'], 'id'=>'form-dpm', 'files' => true]) !!}
        <input type="hidden" value="{{ $locationID }}" name="location_id">
        <input type="hidden" value="{{ $locationCode }}" name="locationCode">
        <input type="hidden" value="{{ $companyID }}" name="companyID" id="companyID">
        <input type="hidden" value="{{ $companyCode }}" name="companyCode">
        <div class="bgc-white p-30 bd">
            <h6>Pengajuan Transfer Stock {{$locationName.' - '.$companyCode}}</h6>
            <hr class='mB-30'>
            <div class="alert alert-info">
                <strong>INFO ! </strong><br>
                <span>
                    - MOHON UNTUK MENGISI FORM SECARA BERURUTAN.
                </span>
            </div>
            <div class="row">
                <div class="col-lg-6">
                    <div class="form-group row">
                        <label class="col-sm-3">Type <span class="text-danger">*</span></label>
                        <div class="col-sm-6">
                            {!! Form::select('type', $type, old('type'),['class' => 'form-control select2', 'id'=>'typeee']) !!}
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3">Lokasi Tujuan <span class="text-danger">*</span></label>
                        <div class="col-sm-6">
                            {!! Form::select('location_destination', $location, old(), ['class' => 'form-control select2', 'id'=>'location_destination','required' => '']) !!}
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3">Attachment File <br><kbd class="btn-danger mr-2"><i class="fa fa-file-pdf-o"></i> PDF</kbd></label>
                        <div class="col-sm-6">
                            {!! Form::file('file', ['class' => 'text-danger', 'accept' => '.pdf']) !!}
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="form-group row">
                        <label class="col-sm-3">Operator <span class="text-danger">*</span></label>
                        <div class="col-sm-6">
                            {!! Form::text('operator', old('operator'), ['class' => 'form-control', 'required' => '']) !!}
                        </div>
                    </div>
                    <div class="form-group row">
                        <label id="received_label" class="col-sm-3">Penerima <span class="text-danger">*</span></label>
                        <div class="col-sm-6">
                            {!! Form::text('received', old('received'), ['class' => 'form-control', 'required' => '', 'id'=>'received']) !!}
                        </div>
                    </div>
                    <hr>
                    <div class="form-group row" id="created_ttb_label">
                        <label class="col-sm-3">
                            Otomatis Buatkan TTB
                        </label>
                        <div class="col-sm-6">
                            {!! Form::select('created_ttb', [0 => 'Tidak', 1 => 'Ya'], 0, ['class' => 'form-control select2', 'required' => '', 'id' => 'created_ttb']) !!}
                        </div>
                    </div>
                    <div class="form-group row project_id_label">
                        <label class="col-sm-3">Project <span class="text-danger">*</span></label>
                        <div class="col-sm-6">
                            {!! Form::select('project_id', $project, '', ['class' => 'form-control select2 project_id', 'id'=>'project_id','required' => '']) !!}
                        </div>
                    </div>
                    <div class="form-group row department_id_label">
                        <label class="col-sm-3">Department <span class="text-danger">*</span></label>
                        <div class="col-sm-6">
                            {!! Form::select('department_id', $department, '', ['class' => 'form-control select2 department_id', 'id'=>'department_id','required' => '']) !!}
                        </div>
                    </div>
                    <div class="form-group row date_transaction_label">
                        <label class="col-sm-3">
                            Tanggal TTB <span class="text-danger">*</span>
                        </label>
                        <div class="col-sm-6">
                            {!! Form::date('date_transaction', old('date_transaction', now()->format('Y-m-d')), [
                                'class' => 'form-control date_transaction',
                                'id' => 'date_transaction',
                                'required' => ''
                            ]) !!}
                        </div>
                    </div>
                </div>
            </div>
            <h6 >Daftar Item </h6>
            <hr>
            <div class="table-responsive">
                <table class="table table-bordered mt-2">
                    <thead>
                        <th class="text-uppercase" style="width:80px">No. Rak</th>
                        <th class="text-uppercase" style="width:400px !important">Item</th>
                        <th class="text-center text-uppercase">Stock</th>
                        <th class="text-center text-uppercase" style="width:300px">QTY<span class="text-danger">*</span></th>
                        {{-- <th class="text-uppercase" >SATUAN</th> --}}
                        <th class="text-uppercase" style="width:300px !important">CATATAN</th>
                    </thead>
                    <tbody class="item_form" id="itemDPM">
                        @foreach($inventory as $item)
                            <tr class="product_1">
                                <input type="hidden" value="{{ $item->id }}" name="inv_id[]">

                                <td>{{ $item->code_rack }}</td>
                                <td>
                                    {{ $item->productCode }} - {{ $item->productName }}<br>
                                    {!! $item->productPartNumber != NULL ? '<small> PN: '.$item->productPartNumber.'</small>' : '<small> PN: -</small>' !!} <br>
                                    <small class="text-danger keterangan_peminjaman1">
                                        Satuan Pembelian : {{$item->unitpembelian}} <br>
                                        Satuan Inventory : {{$item->unit}} <br>
                                        Nilai Konversi : {{$item->produkKonversi}} <br>
                                        <input type="hidden" name="nilai_konversi[]" id="nilai_konversi_{{$item->id}}" value="{{ $item->produkKonversi ?? 1 }}">
                                    </small>
                                </td>
                                <td class="text-center">{{ $item->stock_onhand.' '.$item->unit }}</td>
                                <td>
                                    <input type="hidden" value="{{ $item->stock_onhand }}" id='qty_stock_{{$item->id}}'>
                                    <div class="input-group">
                                        <input type="number" name="qty[]" class="form-control text-center" id='qty_{{$item->id}}' value="" placeholder="0" min="0.01" step="0.01" oninput="if (this.valueAsNumber < 0) this.value = Math.abs(this.valueAsNumber)" onwheel="return false;">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">{{$item->unit}}</span>
                                        </div>
                                    </div>
                                    <span class="keterangan_peminjaman2">
                                        <small>
                                            <strong class="text-danger">
                                                QTY peminjaman hanya diperbolehkan kelipatan nilai konversi
                                            </strong>
                                        </small>
                                    </span>
                                    <input type="hidden" name="unit[]" value="{{$item->unit}}">
                                    <input type="hidden" name="measure_id[]" value="{{$item->measure_id}}">
                                    <input type="hidden" name="product_id[]" value="{{$item->product_id}}">
                                    <input type="hidden" name="price[]" value="{{$item->price}}">
                                    <input type="hidden" name="price_after_discount[]" value="{{$item->price_after_discount}}">
                                </td>
                                {{-- <td class="text-center">
                                </td> --}}
                                <td>
                                    {!! Form::textarea('notes['.$loop->index.']', old('notes.'.$loop->index), ['class' => 'form-control', 'rows' => 2, 'placeholder' => '']) !!}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-4">
            <a href="{{ route('logistic.transfer_out.index') }}" class="btn btn-light mr-1 text-uppercase fsz-sm fw-600">{{ trans('Cancel') }}</a>
            <input type="hidden" value="0" name="status">
            <input class="btn btn-danger text-uppercase fsz-sm fw-600 float-right" type="submit" name="publish" id="btn-submit" value="Publish">
        </div>
    {!! Form::close() !!}
@stop

@section('js')

<script  type='text/javascript'>

	$(document).ready(function() {

        var typeee = $("#typeee");
        var companyID = $("#companyID");
        var location_destination = $("#location_destination");
        $('#created_ttb_label').hide();
        $(".keterangan_peminjaman1").hide();
        $(".keterangan_peminjaman2").hide();
        $("#received").hide();
        $("#received_label").hide();
        $('.project_id_label').hide();
        $('.project_id').val('');
        $('.department_id_label').hide();
        $('.department_id').val('');
        $('.date_transaction_label').hide();
        typeee.select2().on('change', function () {
            var selectedType = typeee.val();
            var selectedCompanyID = companyID.val();
            $.ajax({
                url: "{{ url('logistic/get_location_destination') }}/" + selectedType + "/" + selectedCompanyID,
                type: 'GET',
                success: function (data) {
                    location_destination.empty();
                    location_destination.append($("<option></option>").attr("value", "").text("Silahkan pilih..."));
                    $.each(data, function (value, key) {
                        location_destination.append($("<option></option>").attr("value", value).text(key));
                    });
                    location_destination.select2();
                },
                error: function (xhr, status, error) {
                    console.error("AJAX Error Detail:");
                    console.error("Status: " + status);
                    console.error("Error: " + error);
                    console.error("Response: ", xhr.responseText);
                    alert('Gagal mengambil data lokasi tujuan.\n' +
                        'Status: ' + status + '\n' +
                        'Error: ' + error + '\n' +
                        'Response: ' + xhr.responseText);
                }
            });

            if(typeee.val() == 0){
                //PEMINDAHAN
                $("#received").hide();
                $("#received_label").hide();
                $("#received").val('');
                $(".keterangan_peminjaman1").hide();
                $(".keterangan_peminjaman2").hide();
                $('#created_ttb_label').hide();
                $('#created_ttb').val(0);

                $('.project_id_label').hide();
                $('.project_id').val('').trigger('change');
                $('.department_id_label').hide();
                $('.department_id').val('').trigger('change');
                $('.date_transaction_label').hide();


            }else if(typeee.val() == 1){
                //PEMINJAMAN
                $("#received").show();
                $("#received_label").show();
                $("#received").val('');
                $(".keterangan_peminjaman1").show();
                $(".keterangan_peminjaman2").show();
                $('#created_ttb_label').show();
                $('#created_ttb').val(0);

                $('#created_ttb').select2().on('change', function () {
                    if($('#created_ttb').val() == 1){
                        $('.project_id_label').show();
                        $('.project_id').val('').trigger('change');
                        $('.department_id_label').show();
                        $('.department_id').val('').trigger('change');
                        $('.date_transaction_label').show();
                    }else{
                        $('.project_id_label').hide();
                        $('.project_id').val('').trigger('change');
                        $('.department_id_label').hide();
                        $('.department_id').val('').trigger('change');
                        $('.date_transaction_label').hide();
                    }
                });
            }else{
                //PENJUALAN
                $("#received").hide();
                $("#received_label").hide();
                $("#received").val('');
                $(".keterangan_peminjaman1").hide();
                $(".keterangan_peminjaman2").hide();
                $('#created_ttb_label').hide();
                $('#created_ttb').val(0);
                $('.project_id_label').hide();
                $('.project_id').val('').trigger('change');
                $('.department_id_label').hide();
                $('.department_id').val('').trigger('change');
                $('.date_transaction_label').hide();
            }
        });

        var location_destination = $("#location_destination");
        var department = $("#department_id");

        location_destination.select2().on('change', function () {
            var selectLocationDestination = location_destination.val();

            $.ajax({
                url: "{{ url('logistic/get_department_by_location') }}/" + selectLocationDestination,
                type: 'GET',
                success: function (data) {
                    department.empty();
                    department.append($("<option></option>").attr("value", "").text("Silahkan pilih..."));
                    $.each(data, function (value, key) {
                        department.append($("<option></option>").attr("value", value).text(key));
                    });
                    department.select2();
                },
                error: function (xhr, status, error) {
                    console.error("AJAX Error:", error);
                    alert('Gagal mengambil data departemen.');
                }
            });
        });


        @foreach ($inventory as $item)
            $('#qty_{{$item->id}}').on('keyup', function(e) {
                var qty = $('#qty_{{$item->id}}').val();
                var qty_stock = $('#qty_stock_{{$item->id}}').val();
                if(parseFloat(qty) > parseFloat(qty_stock)){
                    e.preventDefault();
                    Swal.fire(
                    'Peringatan!',
                        'QTY Transfer tidak boleh melebihi QTY Stock',
                        'warning'
                    );
                    $('#qty_{{$item->id}}').val('');
                }
            });
        @endforeach

        $('#form').validate({
            rules: {
                location_id: "required",
                department_id: "required",
            },
            onfocusout: false,
            invalidHandler: function(form, validator) {
                var errors = validator.numberOfInvalids();
                if (errors) {
                    validator.errorList[0].element.focus();
                }
            }
        });

        $(document).on('click', "#btn-submit", function(e) {
            e.preventDefault();
            $('input[name="status"]').val('1');

            var _this = $(this);
            var form = _this.closest('form');

            form.validate({
                onfocusout: false,
                invalidHandler: function(form, validator) {
                    var errors = validator.numberOfInvalids();
                    if (errors) {
                        validator.errorList[0].element.focus();
                    }
                }
            });

            // 🧩 Validasi custom jika type = 1
            var typeVal = $('#typeee').val();
            if (typeVal == 1) {
                var valid = true;
                var invalidItems = [];

                $('input[name="qty[]"]').each(function(index) {
                    var id = $(this).attr('id').split('_')[1];
                    var qty = parseFloat($(this).val());
                    var konversi = parseFloat($('#nilai_konversi_' + id).val()) || 1;

                    if (qty > 0) {

                        if (qty % konversi !== 0) {
                            valid = false;
                            invalidItems.push(id);
                            $(this).addClass('is-invalid');
                        } else {
                            $(this).removeClass('is-invalid');
                        }
                    }
                });

                if (!valid) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Validasi Gagal',
                        html: `
                            <div style="text-align:left; font-size:14px;">
                                <p><strong>Terdapat item yang tidak sesuai dengan ketentuan:</strong></p>
                                <ul style="list-style:disc; padding-left:20px; text-align:left;">
                                    <li>QTY harus <strong>kelipatan nilai konversi</strong> untuk <em>Type Peminjaman</em>.</li>
                                    <li>Contoh: jika nilai konversi = 5, maka QTY harus 5, 10, 15, dst.</li>
                                </ul>
                            </div>
                        `,
                        background: '#fff5f5',
                        color: '#a94442',
                        showClass: {
                            popup: 'animate__animated animate__shakeX'
                        },
                        confirmButtonText: 'Mengerti',
                        confirmButtonColor: '#d33',
                        width: 500,
                    });
                    return false;
                }
            }

            // 🧩 Lanjut validasi form biasa
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
                        _this.closest("form").submit();
                    }
                });
            }
        });



        $(document).on('click', "#btn-draft", function(e) {
            $('input[name="status"]').val('0');

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
                        _this.closest("form").submit();
                    }
                });
            }
        });

    });
    </script>
@stop
