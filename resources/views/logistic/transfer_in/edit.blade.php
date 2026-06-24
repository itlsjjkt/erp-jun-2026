@extends('layouts.app')

@section('page-header')
    Warehouse Transfer In
@stop

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('logistic.inventory.index') }}">Inventory</a></li>
        <li class="breadcrumb-item"><a href="{{ route('logistic.transfer_in.index') }}">Warehouse Transfer In</a></li>
        <li class="breadcrumb-item active" aria-current="page">Edit</li>
    </ol>
@endsection


@section('content')
    {!! Form::model($transfer, [
            'action' => ['Logistic\InventoryTransferInController@update', $transfer->id],
            'method' => 'put', 
            'class' => 'form-horizontal mt-3',
            'id'    => 'form-transfer', 
            'files' => true
        ])
    !!}
         <input type="hidden" name="transfer_out_doc_no" value="{{ $transfer_out->doc_no }}">
         <input type="hidden" name="location_id" value="{{ $transfer->location_id }}">

        <div class="bgc-white p-30 bd">
            <h6>Edit  {{$transfer->doc_no}}</h6>
            <hr class='mB-30'>
            
            <div class="row">
                <div class="col-lg-6">
                    <div class="form-group row">
                        <label class="col-sm-3">Operator <span class="text-danger">*</span></label>
                        <div class="col-sm-6">
                            {!! Form::text('received', old('received'), ['class' => 'form-control', 'required' => '']) !!}
                            <p class="help-block"></p>
                            @if($errors->has('received'))
                                <p class="help-block">
                                    {{ $errors->first('received') }}
                                </p>
                            @endif
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3">Tanggal diterima <span class="text-danger">*</span></label>
                        <div class="col-sm-6">
                            {!! Form::text('received_date', old('received_date'), ['class' => 'form-control datepicker', 'required' => '']) !!}
                            <p class="help-block"></p>
                            @if($errors->has('received_date'))
                                <p class="help-block">
                                    {{ $errors->first('received_date') }}
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    
                </div>
            </div>

            <h6 >Daftar Item </h6>
            <hr>

            <table class="table table-bordered mt-2">
                <thead>
                    <th class="text-uppercase" style="width:400px !important">Item</th>
                    <th class="text-center text-uppercase" style="width:150px">QTY</th>
                    <th class="text-uppercase" >SATUAN</th>
                    <th class="text-uppercase" style="width:250px !important">Catatan</th>
                </thead>
                <tbody class="item_form" id="itemDPM">
                    @foreach($transfer_items as $item) 
                        <tr class="product_1">
                            <input type="hidden" value="{{ $item->id }}" name="transfer_itemID[]">
                            <input type="hidden" value="{{ $item->inventory_id }}" name="inv_id[]">
                            <input type="hidden" value="{{ $item->product_id }}" name="product_id[]">
                            <input type="hidden" value="{{ $item->price }}" name="product_price[]">
                            <input type="hidden" name="qty[]" class="form-control text-right" value="{{$item->qty}}">
                            
                            <td>
                                <strong>{{ $item->productcode }} </strong> | {{ $item->productname }} <br>
                                <small>PN/SPEC: {{ $item->productpartnumber }}  </small></td>
                            <td class="text-center">
                                {{ $item->qty }}
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
            <a href="{{ route('logistic.transfer_in.index') }}" class="btn btn-light mr-1 text-uppercase fsz-sm fw-600">{{ trans('Cancel') }}</a>
            <input class="btn btn-info text-uppercase fsz-sm fw-600" type="submit" name="save" id="btn-draft" value="Save as Draft">
            <input type="hidden" value="0" name="status">
            <input class="btn btn-danger text-uppercase fsz-sm fw-600 float-right" type="submit" name="publish" id="btn-submit" value="Publish">
        </div>
    {!! Form::close() !!}
@stop

@section('js')

<script  type='text/javascript'>

	$(document).ready(function() {

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
