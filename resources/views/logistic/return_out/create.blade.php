@extends('layouts.app')

@section('page-header')
    Return Out
@stop

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('logistic.inventory.index') }}">Inventory</a></li>
        <li class="breadcrumb-item"><a href="{{ route('logistic.return_out.index') }}">Return Out</a></li>
        <li class="breadcrumb-item active" aria-current="page">Input</li>
    </ol>
@endsection


@section('content')
    {!! Form::open(['method' => 'POST', 'route' => ['logistic.return_out.store'], 'id'=>'form-dpm', 'files' => true]) !!}
        <div class="bgc-white p-30 bd">
            <h6>Input Return Out</h6>
            <hr class='mB-30'>
            <div class="form-group row">
                <label  class="col-sm-2">Operator <span class="text-danger">*</span></label>
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
                <label class="col-sm-2">Attachment File<br> <kbd class="btn-danger mr-2"><i class="fa fa-file-pdf-o"></i> PDF</kbd></label>
                <div class="col-sm-4">
                    {!! Form::myFile('file', '',['class' => 'form-control']) !!}
                </div>
            </div>
            
            <div class="form-group row mb-5">
                <label class="col-sm-2">Lokasi Warehouse</label>
                <div class="col-sm-2">
                    <input type="text" readonly class="form-control" value="{{ $locationName }}">
                    <input type="hidden" name="location_id" id="location_id" value="{{ $locationID }}">
                    <p class="help-block"></p>
                    @if($errors->has('location_id'))
                        <p class="help-block">
                            {{ $errors->first('location_id') }}
                        </p>
                    @endif
                </div>
            </div>


            <h6 >Daftar Item </h6>

            <table class="table table-bordered mt-2">
                <thead>
                    <th style="width:80px">NO. RAK</th>
                    <th style="width:400px !important">ITEM</th>
                    <th class="text-center">STOCK</th>
                    <th class="text-center">SATUAN</th>
                    <th class="text-center" style="width:150px">QTY RETUR<span class="text-danger">*</span></th>
                    <th style="width:250px !important">CATATAN</th>
                </thead>
                <tbody class="item_form" id="itemDPM">
                    @foreach($inventory as $item) 
                        <tr class="product_1">
                            <input type="hidden" value="{{ $item->id }}" name="inv_id[]">
                            <input type="hidden" value="{{ $item->stock_min }}" name="stock_min[]">
                            <input type="hidden" value="{{ $item->stock_max }}" name="stock_max[]">
                            <td>{{ $item->code_rack }}</td>
                            <td>
                                {{ $item->productCode }} - {{ $item->productName }}<br>
                                {!! $item->productPartNumber != NULL ? '<small> PN: '.$item->productPartNumber.'</small>' : '' !!} 
                                {!! $item->productBrand != NULL ? '<small>Brand: '.$item->productBrand.'</small>' : '' !!}
                            </td>
                            <td class="text-center">{{ $item->stock_onhand }}</td>
                            <td class="text-center">{{ $item->unit }}</td>
                            <td>
                                <input name="out[]" type="hidden" value="{{$item->out}}">
                                <input name="qty_stock[]" type="hidden" value="{{$item->stock_onhand}}"  id='qty_stock_{{$item->id}}'>
                                <input type="number" name="qty[]" class="form-control text-right" id='qty_return_out_{{$item->id}}' value="0" min="1" oninput="this.value = Math.abs(this.value)" onwheel="return false;">
                            </td>
                            <td>
                                {!! Form::textarea('notes[]', old('notes'), ['class' => 'form-control', 'rows' => 2, 'placeholder' => '']) !!}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

        </div>
        <div class="mt-4">
            <a href="{{ route('logistic.return_out.index') }}" class="btn btn-light mr-1 text-uppercase fsz-sm fw-600">{{ trans('Cancel') }}</a>
            <input class="btn btn-info text-uppercase fsz-sm fw-600" type="submit" name="save" id="btn-draft-dpm" value="Save as Draft">
            <input type="hidden" value="0" name="status">
            <input class="btn btn-danger text-uppercase fsz-sm fw-600 float-right" type="submit" name="publish" id="btn-submit-dpm" value="Publish">
        </div>
    {!! Form::close() !!}
@stop

@section('js')

<script  type='text/javascript'>

	$(document).ready(function() {
       
        @foreach ($inventory as $item)
            $('#qty_return_out_{{$item->id}}').on('keyup', function(e) {
                var qty_return_out  = $('#qty_return_out_{{$item->id}}').val();
                var qty_stock = $('#qty_stock_{{$item->id}}').val();
                if(parseInt(qty_return_out) > parseInt(qty_stock)){
                    e.preventDefault();
                    Swal.fire(
                    'Peringatan!',
                        'QTY RETUR tidak boleh melebihi QTY STOCK',
                        'warning'
                    );
                    $('#qty_return_out_{{$item->id}}').val('');
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
