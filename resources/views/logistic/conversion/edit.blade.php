@extends('layouts.app')

@section('page-header')
    Konversi Satuan
@stop

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('logistic.inventory.index') }}">Inventory</a></li>
        <li class="breadcrumb-item"><a href="{{ route('logistic.conversion.index') }}">Konversi Satuan</a></li>
        <li class="breadcrumb-item active" aria-current="page">Edit Konversi</li>
    </ol>
@endsection


@section('content')
    {!! Form::model($conversion, [
            'action' => ['Logistic\conversionController@update', $conversion->id],
            'method' => 'put', 
            'class' => 'form-horizontal mt-3',
            'id'    => 'form-conversion', 
            'files' => true
        ])
    !!}
    {!! Form::open(['method' => 'POST', 'route' => ['logistic.conversion.store'], 'id'=>'form-dpm', 'files' => true]) !!}
        <div class="bgc-white p-30 bd">
            <h6>Edit conversion {{$conversion->doc_no}}</h6>
            <hr class='mB-30'>
            <div class="form-group row">
                <div class="col-sm-4 offset-sm-2">
                    <label>Operator <span class="text-danger">*</span></label>
                    {!! Form::text('operator', old('operator'), ['class' => 'form-control', 'required' => '']) !!}
                    <p class="help-block"></p>
                    @if($errors->has('operator'))
                        <p class="help-block">
                            {{ $errors->first('operator') }}
                        </p>
                    @endif
                </div>
                <div class="col-sm-4">
                    <label>Penerima <span class="text-danger">*</span></label>
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
                <div class="col-sm-4 offset-sm-2">
                    <label>Department <span class="text-danger">*</span></label>
                    {!! Form::select('department_id', $department, old('department_id'), ['class' => 'form-control select2', 'required' => '','id' =>'department']) !!}
                    <p class="help-block"></p>
                    @if($errors->has('department_id'))
                        <p class="help-block">
                            {{ $errors->first('department_id') }}
                        </p>
                    @endif
                </div>
                <div class="col-sm-4">
                    <label>COA </label>
                    {!! Form::text('coa', old('coa'), ['class' => 'form-control']) !!}
                    <p class="help-block"></p>
                    @if($errors->has('coa'))
                        <p class="help-block">
                            {{ $errors->first('coa') }}
                        </p>
                    @endif
                </div>
            </div>

            <div class="form-group row">
                <div class="col-sm-4  offset-sm-2">
                <label>Attachment File <kbd class="btn-danger mr-2"><i class="fa fa-file-pdf-o"></i> PDF</kbd></label>
                {!! Form::myFile('file', '',['class' => 'form-control']) !!}
                @if ($conversion->file)
                    <code> {{ asset('storage'.$conversion->file) }}</code> 
                @endif
                </div>
                <div class="col-sm-4">
                    <label>Location  <span class="text-danger">*</span></label>
                    <input type="text" readonly class="form-control" value="{{ getDataByID('locations',$conversion->location_id )->name }}">
                    <input type="hidden" name="location_id" id="location_id" value="{{ $conversion->location_id }}">
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
                    <th class="text-uppercase" style="width:250px !important">Keperluan</th>
                    <th class="text-uppercase" style="width:250px !important">Catatan</th>
                    <th class="text-uppercase" style="width:250px !important">Tgl Pengeluaran</th>
                </thead>
                <tbody class="item_form" id="itemDPM">
                    @foreach($conversion_items as $item) 
                        <tr class="product_1">
                            <input type="hidden" value="{{ $item->id }}" name="conversion_itemID[]">
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
                                <input type="hidden" name="qty_conversion[]" class="form-control text-right" value="{{$item->qty}}" >
                                <input type="number" name="qty[]" class="form-control text-right" id='qty_conversion_{{$item->id}}' value="{{$item->qty}}" min="1" oninput="this.value = Math.abs(this.value)" onwheel="return false;">
                            </td>
                            <td class="text-left">{{ $item->unit }}</td>
                            <td>
                                {!! Form::select('usage[]', $usage, $item->usage , ['class' => 'form-control select2', 'required' => '']) !!}
                            </td>
                            <td>
                                {!! Form::textarea('notes[]', $item->description, ['class' => 'form-control', 'rows' => 2, 'placeholder' => '']) !!}
                            </td>
                            <td>
                                <input type="text" name="date_of_issue[]" class="form-control datepicker" value="{{ date('m/d/Y',strtotime( $item->date_of_issue)) }}" >
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

        </div>
        <div class="mt-4">
            <a href="{{ route('logistic.conversion.index') }}" class="btn btn-light mr-1 text-uppercase fsz-sm fw-600">{{ trans('Cancel') }}</a>
            <input type="hidden" value="0" name="status">
            <input class="btn btn-danger text-uppercase fsz-sm fw-600" type="submit" name="publish" id="btn-submit-dpm" value="Publish conversion">
        </div>
    {!! Form::close() !!}
@stop

@section('js')

<script  type='text/javascript'>

	$(document).ready(function() {

        @foreach ($conversion_items as $item)
         $('#qty_conversion_{{$item->id}}').on('keyup', function(e) {
                var qty_conversion  = $('#qty_conversion_{{$item->id}}').val();
                var qty_stock = $('#qty_stock_{{$item->id}}').val();
                if(parseInt(qty_conversion) > parseInt(qty_stock)){
                    e.preventDefault();
                    Swal.fire(
                    'Peringatan!',
                        'QTY conversion tidak boleh melebihi QTY STOCK',
                        'warning'
                    );
                    $('#qty_conversion_{{$item->id}}').val('');
                }
            });
        @endforeach

        $(document).on("click", ".add_item", function(e) {
            
        });

        <?php if(isAdministrator() || isAdministratorCompany() ){ ?>
            $('#location_id').select2().on('change', function() {
                $(this).valid();
            });
        <?php } ?>

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
