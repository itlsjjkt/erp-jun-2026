@extends('layouts.app')

@section('page-header')
    Warehouse Transfer Out
@stop

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('logistic.inventory.index') }}">Inventory</a></li>
        <li class="breadcrumb-item"><a href="{{ route('logistic.transfer_out.index') }}">Warehouse Transfer Out</a></li>
        <li class="breadcrumb-item active" aria-current="page">Edit</li>
    </ol>
@endsection


@section('content')
    {!! Form::model($transfer, [
            'action' => ['Logistic\InventoryTransferOutController@update', $transfer->id],
            'method' => 'put', 
            'class' => 'form-horizontal mt-3',
            'id'    => 'form-transfer', 
            'files' => true
        ])
    !!}
        <div class="bgc-white p-30 bd">
            <h6>Edit  {{$transfer->doc_no}}</h6>
            <hr class='mB-30'>
            
            <div class="row">
                <div class="col-lg-6">
                    <div class="form-group row">
                        <label class="col-sm-3">Operator <span class="text-danger">*</span></label>
                        <div class="col-sm-6">
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
                        <label class="col-sm-3">Lokasi Tujuan <span class="text-danger">*</span></label>
                        <div class="col-sm-6">
                            {!! Form::select('location_destination', $location, $transfer->location_destination, ['class' => 'form-control select2', 'id'=>'location_id','required' => '']) !!}
                            <p class="help-block"></p>
                            @if($errors->has('location_destination'))
                                <p class="help-block">
                                    {{ $errors->first('location_destination') }}
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="form-group row">
                        <label class="col-sm-3">Attachment File <br><kbd class="btn-danger mr-2"><i class="fa fa-file-pdf-o"></i> PDF</kbd></label>
                        <div class="col-sm-6">
                            {!! Form::myFile('file', '',['class' => 'form-control']) !!}
                            @if ($transfer->file)
                                <code> {{ asset('storage'.$transfer->file) }}</code> 
                            @endif
                        </div>
                    </div>
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
                    <th class="text-uppercase" style="width:250px !important">Catatan</th>
                </thead>
                <tbody class="item_form" id="itemDPM">
                    @foreach($transfer_items as $item) 
                        <tr class="product_1">
                            <input type="hidden" value="{{ $item->id }}" name="transfer_itemID[]">
                            <input type="hidden" value="{{ $item->inventory_id }}" name="inv_id[]">

                            <td>{{ $item->code_rack }}</td>
                            <td>
                                <strong>{{ $item->productcode }} </strong> | {{ $item->productname }} <br>
                                <small>PN/SPEC: {{ $item->productpartnumber }} </small></td>
                            <td class="text-center">{{ $item->stock_onhand }}</td>
                            <td>
                                <input type="number" name="qty[]" class="form-control text-right" id='qty_transfer_{{$item->id}}' value="{{$item->qty}}" min="1" oninput="this.value = Math.abs(this.value)" onwheel="return false;">
                                <input type="hidden" id='qty_stock_{{$item->id}}' value="{{$item->stock_onhand}}">
                            </td>
                            <td class="text-left">{{ $item->productunit }}</td>
                            <td>
                                {!! Form::textarea('notes[]', $item->notes, ['class' => 'form-control', 'rows' => 2, 'placeholder' => '']) !!}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

        </div>
        <div class="mt-4">
            <a href="{{ route('logistic.transfer_out.index') }}" class="btn btn-light mr-1 text-uppercase fsz-sm fw-600">{{ trans('Cancel') }}</a>
            <input class="btn btn-info text-uppercase fsz-sm fw-600" type="submit" name="save" id="btn-draft" value="Save as Draft">
            <input type="hidden" value="0" name="status">
            <input class="btn btn-danger text-uppercase fsz-sm fw-600 float-right" type="submit" name="publish" id="btn-submit" value="Publish">
        </div>
    {!! Form::close() !!}
@stop

@section('js')

<script  type='text/javascript'>

	$(document).ready(function() {

        @foreach ($transfer_items as $item)
            $('#qty_transfer_{{$item->id}}').on('keyup', function(e) {
                var qty_transfer  = $('#qty_transfer_{{$item->id}}').val();
                var qty_stock = $('#qty_stock_{{$item->id}}').val();
                if(parseInt(qty_transfer) > parseInt(qty_stock)){
                    e.preventDefault();
                    Swal.fire(
                    'Peringatan!',
                        'QTY Transfer tidak boleh melebihi QTY Stock',
                        'warning'
                    );
                    $('#qty_transfer_{{$item->id}}').val('');
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
