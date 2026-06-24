@extends('layouts.app')

@section('page-header')
    Konversi Satuan
@stop

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('logistic.inventory.index') }}">Inventory</a></li>
        <li class="breadcrumb-item"><a href="{{ route('logistic.conversion.index') }}">Konversi Satuan</a></li>
        <li class="breadcrumb-item active" aria-current="page">Input</li>
    </ol>
@endsection


@section('content')
    {!! Form::open(['method' => 'POST', 'route' => ['logistic.conversion.store'], 'id'=>'form-dpm', 'files' => true]) !!}
        <input type="hidden" name="location_id" id="location_id" value="{{ $locationID }}">
        <div class="bgc-white p-30 bd">
            <h6>Konversi Satuan</h6>
            <hr class='mB-30'>
            <div class="form-group row">
                <label  class="col-sm-2 text-right">Perusahaan<span class="text-danger">*</span></label>
                <div class="col-sm-3">
                    {{ $companyName }}
                </div>
            </div>

            <div class="form-group row">
                <label  class="col-sm-2 text-right">Warehouse</label>
                <div class="col-sm-3">
                    {{ $locationName }}
                </div>
            </div>

            <div class="form-group row mt-4">
                <label  class="col-sm-2 text-right">Operator <span class="text-danger">*</span></label>
                <div class="col-sm-4">
                    {!! Form::text('operator', old('operator'), ['class' => 'form-control', 'required' => '']) !!}
                    <p class="help-block"></p>
                    @if($errors->has('operator'))
                        <p class="help-block">
                            {{ $errors->first('operator') }}
                        </p>
                    @endif
                </div>
            </div>

            <h6 >Daftar Item </h6>
            <hr>
            <div class="alert alert-info mb-4">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h6 class="alert-heading">Informasi</h6>
                - Item Produk yang akan menjadi tujuan konversi harus sudah ada dalam inventory <br>
                - Jika tidak ada silahkan hubungi bagian Logistik untuk menambahkan pada Master Item Produk terlebih dahulu dengan satuan yang sesuai, kemudian tambahankan kedalam inventory <br>
            </div>
            <table class="table table-bordered mt-2">
                <thead>
                    <th class="text-uppercase" style="width:500px !important">Item</th>
                    <th class="text-center text-uppercase">Stock</th>
                    <th style="width:50px !important"></th>
                    <th class="text-uppercase" style="width:500px !important">Item</th>
                    <th class="text-center text-uppercase">QTY Konversi</th>
                </thead>
                <tbody class="item_form" id="itemDPM">
                    @php
                        $no=1;
                    @endphp
                    @foreach($inventory as $item) 
                        <tr class="product_1">
                            <input type="hidden" value="{{ $item->id }}" name="inv_id[]">
                            <td>
                                {{ $item->productCode }} - {{ $item->productName }}<br>
                                {!! $item->productPartNumber != NULL ? '<small> PN: '.$item->productPartNumber.'</small>' : '' !!} 
                            </td>
                                <td class="text-center">{{ $item->stock_onhand }} {{ $item->unit }}
                                <input name="stock_out[]" type="hidden" class="form-control" value="{{ $item->out }}">
                                <input name="stock_qty[]" type="hidden" class="form-control" value="{{ $item->stock_onhand }}">
                                <input name="stock_min[]" type="hidden" class="form-control" value="{{ $item->stock_min }}">
                                <input name="stock_max[]" type="hidden" class="form-control" value="{{ $item->stock_max }}">
                            </td>
                            <td class="text-center"> <i class="ti-arrow-right fa-2x"></i> </td>
                            <td>
                                <select class="form-control select2 item mB-5" id="{{ $no }}" required style="width:350px !important"></select>
                            </td>
                            <td class="text-center">
                                <div class="input-group">
                                    <input name="conversion_qty[]" type="number"  class="form-control" value="" onwheel="return false;">
                                    <div class="input-group-append">
                                        <span class="input-group-text" id="conversion_unit_{{ $no }}"></span>
                                    </div>
                                </div>
                            </td>
                            <input name="conversion_inv_id[]" type="hidden" id="conversion_id_{{ $no }}"  class="form-control" value="">
                            <input name="conversion_stock[]" type="hidden" id="conversion_stock_{{ $no }}"  class="form-control" value="">
                            <input name="conversion_stock_min[]" type="hidden" id="conversion_stock_min_{{ $no }}"  class="form-control" value="">
                            <input name="conversion_stock_max[]" type="hidden" id="conversion_stock_max_{{ $no }}"  class="form-control" value="">
                            <input name="conversion_stock_in[]" type="hidden" id="conversion_stock_in_{{ $no }}"  class="form-control" value="">
                        </tr>
                        @php
                            $no++;
                        @endphp
                    @endforeach
                </tbody>
            </table>

        </div>
        <div class="mt-4">
            <a href="{{ route('logistic.conversion.index') }}" class="btn btn-light mr-1 text-uppercase fsz-sm fw-600">{{ trans('Cancel') }}</a>
            <input class="btn btn-danger text-uppercase fsz-sm fw-600" type="submit" name="publish" id="btn-submit-dpm" value="Publish ">
        </div>
    {!! Form::close() !!}
@stop

@section('js')

<script  type='text/javascript'>

	$(document).ready(function() {
       
        var $item       = $('.item');
        var $location   = $('#location_id');

        $item.select2({
            placeholder: "Silahkan Pilih Item Inventory...",
            allowClear: true,
            minimumInputLength: 3,
            ajax: {
                url:"{{ route('logistic.inventory.getdata') }}/" + $location.val(),
                dataType: 'json',
                delay: 250,
                processResults: function (data) {
                    return {
                        results:  $.map(data, function (item) {
                            if(item.part_number === '' || item.part_number == null){
                                var part_number = "";
                            }else{
                                var part_number = " [" + item.part_number + "]";
                            }
                            return {
                                text: item.name + part_number + " [" + item.code + "]" + " [" + item.unit + "]",
                                id: item.id,
                                code: item.code,
                                measure: item.unit,
                                stock: item.stock,
                                stock_min: item.stock_min,
                                stock_max: item.stock_max,
                                stock_in: item.in,
                            }
                        })
                    };
                },
                cache: false
            }
        }).on('change', function() {
            var id =  this.id;
            $('#conversion_id_'+id).val($('#'+id).select2('data')[0].id);
            $('#conversion_unit_'+id).text($('#'+id).select2('data')[0].measure);
            $('#conversion_stock_'+id).val($('#'+id).select2('data')[0].stock);
            $('#conversion_stock_in_'+id).val($('#'+id).select2('data')[0].stock_in);
            $('#conversion_stock_min_'+id).val($('#'+id).select2('data')[0].stock_min);
            $('#conversion_stock_max_'+id).val($('#'+id).select2('data')[0].stock_max);
        });


        $('#form-dpm').validate({
            rules: {
                location_id: "required",
            },
            onfocusout: false,
            invalidHandler: function(form, validator) {
                var errors = validator.numberOfInvalids();
                if (errors) {                    
                    validator.errorList[0].element.focus();
                }
            }
        });

        $(document).on('click', "#btn-submit-dpm", function(e) {
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

            e.preventDefault();
            if (form.valid() ) {
                Swal.fire({
                    title: 'Konfirmasi', // Opération Dangereuse
                    text: 'Apakah anda yakin melanjutkan ini?', // Êtes-vous sûr de continuer ?
                    type: 'question',
                    showCancelButton: true,
                    confirmButtonColor: 'null',
                    cancelButtonColor: 'null',
                    confirmButtonClass: 'btn btn-danger',
                    cancelButtonClass: 'btn btn-primary',
                    confirmButtonText: 'Ya, lanjut', // Oui, sûr
                    cancelButtonText: 'Batal', // Annuler
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
