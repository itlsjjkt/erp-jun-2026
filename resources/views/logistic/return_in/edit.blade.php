@extends('layouts.app')

@section('page-header')
    Return In
@stop

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('logistic.inventory.index') }}">Inventory</a></li>
        <li class="breadcrumb-item"><a href="{{ route('logistic.return_in.index') }}"> Return In</a></li>
        <li class="breadcrumb-item active" aria-current="page">Edit </li>
    </ol>
@endsection


@section('content')
    {!! Form::model($return_in, [
            'action' => ['Logistic\ReturnInController@update', $return_in->id],
            'method' => 'put', 
            'class' => 'form-horizontal mt-3',
            'id'    => 'form-return_in', 
            'files' => true
        ])
    !!}
        <div class="bgc-white p-30 bd">
            <h6>Edit ROT {{$return_in->doc_no}}</h6>
            <hr class='mB-30'>
            <div class="form-group row">
                <label class="col-sm-3 text-right">Operator <span class="text-danger">*</span></label>
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


            <div class="form-group row">
                <label class="col-sm-3 text-right">Location  <span class="text-danger">*</span></label>
                <div class="col-sm-2">
                    <input type="text" readonly class="form-control" value="{{ getDataByID('locations',$return_in->location_id )->name }}">
                    <input type="hidden" name="location_id" id="location_id" value="{{ $return_in->location_id }}">
                    <p class="help-block"></p>
                    @if($errors->has('location_id'))
                        <p class="help-block">
                            {{ $errors->first('location_id') }}
                        </p>
                    @endif
                </div>
            </div>

            <h6 >Daftar Item </h6>
            <hr>

            <table class="table table-bordered mt-2">
                <thead>
                    <th class="text-uppercase" style="width:80px">No. Rak</th>
                    <th class="text-uppercase" style="width:400px !important">Item</th>
                    <th class="text-center text-uppercase">Stock</th>
                    <th class="text-center text-uppercase" style="width:150px">QTY<span class="text-danger">*</span></th>
                    <th class="text-uppercase" >SATUAN</th>
                    <th class="text-uppercase" style="width:250px !important">Keterangan</th>
                </thead>
                <tbody class="item_form" id="itemDPM">
                    @foreach($return_in_items as $item) 
                        <tr class="product_1">
                            <input type="hidden" value="{{ $item->id }}" name="return_in_itemID[]">
                            <input type="hidden" value="{{ $item->inventory_id }}" name="inv_id[]">
                            <input type="hidden" value="{{ $item->stock_min }}" name="stock_min[]">
                            <input type="hidden" value="{{ $item->stock_max }}" name="stock_max[]">
                            <td>{{ $item->code_rack }}</td>
                            <td>
                                {{ $item->productCode }} <br>
                                {{ $item->productName }} <br>
                                <small>PN/SPEC: {{ $item->productPartNumber }} </small></td>
                            <td class="text-center">{{$item->stock_onhand + $item->qty}}</td>
                            <td>
                                <input name="qty_stock[]" type="hidden" value="{{$item->stock_onhand + $item->qty}}"  id='qty_stock_{{$item->id}}'>
                                <input name="qty_real[]" type="hidden"  value="{{$item->stock_onhand}}" >
                                <input type="hidden" name="qty_return_in[]" class="form-control text-right" value="{{$item->qty}}" >
                                <input type="number" name="qty[]" class="form-control text-right" id='qty_return_in_{{$item->id}}' value="{{$item->qty}}" min="1" oninput="this.value = Math.abs(this.value)" onwheel="return false;">
                            </td>
                            <td class="text-left">{{ $item->unit }}</td>
                            <td>
                                {!! Form::textarea('notes[]', $item->notes, ['class' => 'form-control', 'rows' => 2, 'placeholder' => '']) !!}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

        </div>
        <div class="mt-4">
            <a href="{{ route('logistic.return_in.index') }}" class="btn btn-light mr-1 text-uppercase fsz-sm fw-600">{{ trans('Cancel') }}</a>
            <input class="btn btn-info text-uppercase fsz-sm fw-600" type="submit" name="save" id="btn-draft-dpm" value="Save as Draft">
            <input type="hidden" value="0" name="status">
            <input class="btn btn-danger text-uppercase fsz-sm fw-600 float-right" type="submit" name="publish" id="btn-submit-dpm" value="Publish">
        </div>
    {!! Form::close() !!}
@stop

@section('js')

<script  type='text/javascript'>

	$(document).ready(function() {

        @foreach ($return_in_items as $item)
         $('#qty_return_in_{{$item->id}}').on('keyup', function(e) {
                var qty_return_in  = $('#qty_return_in_{{$item->id}}').val();
                var qty_stock = $('#qty_stock_{{$item->id}}').val();
                if(parseInt(qty_return_in) > parseInt(qty_stock)){
                    e.preventDefault();
                    Swal.fire(
                    'Peringatan!',
                        'QTY ROT tidak boleh melebihi QTY STOCK',
                        'warning'
                    );
                    $('#qty_return_in_{{$item->id}}').val('');
                }
            });
        @endforeach

     
        $('#form-dpm').validate({
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


        $(document).on('click', "#btn-draft-dpm", function(e) {
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
