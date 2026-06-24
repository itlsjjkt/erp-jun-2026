@extends('layouts.app')

@section('page-header')
    Tanda Terima Barang (TTB)
@stop

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('logistic.inventory.index') }}">Inventory</a></li>
        <li class="breadcrumb-item"><a href="{{ route('logistic.ttb.index') }}">Tanda Terima Barang (TTB)</a></li>
        <li class="breadcrumb-item active" aria-current="page">Edit TTB</li>
    </ol>
@endsection


@section('content')
        {!! Form::model($ttb, [
                'action' => ['Logistic\TtbController@update', $ttb->id],
                'method' => 'put', 
                'class' => 'form-horizontal mt-3',
                'id'    => 'form-ttb', 
                'files' => true
            ])
        !!}
        <div class="bgc-white p-30 bd">
            <h6><a class="float-left" href="{{ route('logistic.ttb.index') }}"><i class="ti-arrow-left mR-10"></i></a> {{ trans('Back') }}</h6>
            <hr class='mB-30'>

            <div class="row mb-5 mt-4">
                <div class="col-lg-6">
                    <div class="form-group row">
                        <label class="col-sm-3">Operator <span class="text-danger">*</span></label>
                        <div class="col-sm-7">
                            {!! Form::text('operator', old('operator'), ['class' => 'form-control', 'required' => '']) !!}
                            @if($errors->has('operator'))
                                <p class="help-block">
                                    {{ $errors->first('operator') }}
                                </p>
                            @endif
                        </div>
                    </div>
                    
                    <div class="form-group row">
                        <label class="col-sm-3">Penerima <span class="text-danger">*</span></label>
                        <div class="col-sm-7">
                            {!! Form::text('received', old('received'), ['class' => 'form-control', 'required' => '']) !!}
                            @if($errors->has('received'))
                                <p class="help-block">
                                    {{ $errors->first('received') }}
                                </p>
                            @endif
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3">Tanggal TTB <span class="text-danger">*</span></label>
                        <div class="col-sm-7">
                            {!! Form::text('date_transaction', date('m/d/Y',strtotime($ttb->date_transaction)), ['class' => 'form-control datepicker', 'placeholder' => '', 'required' => '']) !!}
                            @if($errors->has('date_transaction'))
                                <p class="help-block">
                                    {{ $errors->first('date_transaction') }}
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="form-group row">
                        <label class="col-sm-3">Project</label>
                        <div class="col-sm-8">
                            {!! Form::select('project_id', $project, old('project_id'), ['class' => 'form-control select2', 'required' => '']) !!}
                            @if($errors->has('project_id'))
                                <p class="help-block">
                                    {{ $errors->first('project_id') }}
                                </p>
                            @endif
                        </div>
                    </div>
                    <div class="form-group row">
                        <label  class="col-sm-3">Kapal/Department<span class="text-danger">*</span></label>
                        <div class="col-sm-8">
                            {!! Form::select('department_id', $department, old('department_id'), ['class' => 'form-control select2', 'required' => '']) !!}
                        </div>
                    </div>
               
                    <div class="form-group row">
                        <label class="col-sm-3">Location</label>
                        <div class="col-sm-8">
                            <input type="text" readonly class="form-control" value="{{ getDataByID('locations',$ttb->location_id )->name }}">
                            <input type="hidden" name="location_id" id="location_id" value="{{ $ttb->location_id  }}">
                        </div>
                    </div>

                </div>
            </div>
            <hr>
            <div class="form-group row">
                <label class="col-sm-2">Attachment File<br> <kbd class="btn-danger mr-2"><i class="fa fa-file-pdf-o"></i> PDF</kbd></label>
                <div class="col-sm-8">
                    {!! Form::myFile('file', '',['class' => '']) !!}
                    @if ($ttb->file)
                        <code> {{ asset('storage'.$ttb->file) }}</code> 
                    @endif
                </div>
            </div>
            <hr>
            <div class="alert alert-warning">
                <h6 class="alert-heading mb-0 font-weight-bold">INFORMASI</h6>
                - Jika menambah item silahkan hapus terlebih dahalu ITEM sebelumnya <br>
            </div>
            <h6 class="mt-4">DAFTAR ITEM</h6>
            <div class='border col-1 p-10 pull-left text-center '>
                Jumlah Item
                <h4 id="countTTB" class="font-weight-bold mb-0 text-danger"> {{ count($ttb_items) }} </h4>
            </div>
            <a class="btn btn-success c-white add_item float-right mb-2 fw-600"><i class="ti-plus"></i> TAMBAH ITEM</a>
            <table class="table table-bordered"  id="tableTTB">
                <thead>
                    <th class="text-uppercase" style="width:80px">No. Rak</th>
                    <th class="text-uppercase" style="width:400px !important">Item</th>
                    <th class="text-center text-uppercase">Stock</th>
                    <th class="text-center text-uppercase" style="width:150px">QTY<span class="text-danger">*</span></th>
                    <th class="text-uppercase" >SATUAN</th>
                    <th class="text-uppercase" style="width:250px !important">Catatan</th>
                    <th  style="width:80px">Aksi</th>
                </thead>
                <tbody class="item_form">
                    @foreach($ttb_items as $item) 
                        <tr class="product_1">
                            <input type="hidden" value="{{ $item->id }}" name="ttb_itemID[]">
                            <input type="hidden" value="{{ $item->inventory_id }}" name="inv_id[]" class="product">

                            <td>{{ $item->code_rack }}</td>
                            <td>
                                {{ $item->productCode }} -
                                {{ $item->productName }} <br>
                                <small>PN/SPEC: {{ $item->productPartNumber }} </small></td>
                            <td class="text-center">{{$item->stock_onhand}}</td>
                            <td>
                                <input name="qty_stock[]" type="hidden" value="{{$item->stock_onhand}}"  id='qty_stock_{{$item->id}}'>
                                <input type="number" name="qty[]" class="form-control text-right" id='qty_ttb_{{$item->id}}' value="{{$item->qty}}" min="1"  onwheel="return false;">
                            </td>
                            <td class="text-center">{{ $item->unit }}</td>
                            <td>
                                {!! Form::textarea('notes[]', $item->description, ['class' => 'form-control', 'rows' => 2, 'placeholder' => '']) !!}
                            </td>
                            <td class="text-center">
                                @if($param =='revision') 
                                    <a href="{{ route('logistic.ttb.remove_item',['id'=>$item->id,'param' => $param]) }}"  title="Hapus Item" data-toggle='tooltip' class='btn btn-danger' id="btn-remove"><span class='ti-trash icon-lg'></span> </button>
                                @else
                                    <a href="{{ route('logistic.ttb.remove_item',['id'=>$item->id]) }}"  title="Hapus Item" data-toggle='tooltip' class='btn btn-danger' id="btn-remove"><span class='ti-trash icon-lg'></span> </button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

        </div>
        <div class="mt-4">
            <a href="{{ route('logistic.ttb.index') }}" class="btn btn-light mr-1 text-uppercase fsz-sm fw-600">{{ trans('Cancel') }}</a>
            <input class="btn btn-info text-uppercase fsz-sm fw-600" type="submit" name="save" id="btn-draft-dpm" value="Save as Draft">
            <input type="hidden" value="1" name="status">
            <input class="btn btn-danger text-uppercase fsz-sm fw-600 float-right" type="submit" name="publish" id="btn-submit-dpm" value="Publish TTB">
        </div>
    {!! Form::close() !!}
@stop

@section('js')

<script  type='text/javascript'>

	$(document).ready(function() {

        @foreach ($ttb_items as $item)
            $('#qty_ttb_{{$item->id}}').on('keyup', function(e) {
                var qty_ttb  = $('#qty_ttb_{{$item->id}}').val();
                var qty_stock = $('#qty_stock_{{$item->id}}').val();
                if(parseInt(qty_ttb) > parseInt(qty_stock)){
                    e.preventDefault();
                    Swal.fire(
                    'Peringatan!',
                        'QTY TTB tidak boleh melebihi QTY STOCK',
                        'warning'
                    );
                    $('#qty_ttb_{{$item->id}}').val('');
                }
            });

        @endforeach


        var wrapper = $(".item_form");
        var add_sk = $(".add_item");
        var i = 0;

        $(document).on("click", ".add_item", function(e) {
            i += 1;
            e.preventDefault();
            $(wrapper).append(
                '<tr class="product[] dpm" level_'+i+'">' +
                    '<td>' +
                        '<span id="rack_'+i+'"></span>' +
                    '</td>' +
                    '<td style="max-width: 350px">' +
                        '<input type="hidden" name="index[]" value="'+i+'">' +
                        '<select name="inv_id[]" class="form-control productItem product narrow wrap" id="product_'+i+'" required></select>' +
                    '</td>' +
                    '<td class="text-center">' +
                        '<span id="preview_qty_stock_'+i+'"></span>' +
                        '<input name="qty_stock[]" type="hidden" id="qty_stock_'+i+'">' +
                    '</td>' +
                    '<td>' +
                        '<input type="number" name="qty[]" class="form-control qtyItem  text-right"  min="0.1" required id="qty_ttb_'+i+'" onwheel="return false;">' +
                    '</td>' +
                    '<td class="text-center">' +
                        '<span id="measure_'+i+'"></span>' +
                    '</td>' +
                    '<td>' +
                        '<textarea name="notes[]" class="form-control" id="description_'+i+'"></textarea>' +
                    '</td>' +
                    '<td class="text-center">' +
                        '<a class="remove_item text-white btn btn-danger btn-md"><i class="ti-trash"></i></a>' +
                        '<div id="js_'+i+'" ></div>'+
                    '</td>' +
                '</tr>'
            );

            var countTTB = $('#tableTTB tbody tr').length;
            $("#countTTB").html(countTTB);

            $.ajax({
                url: "{{ route('ttb.js') }}/"+i,
                type: "GET",
                success: function(data){
                    $('#js_'+i).html(data);
                }
            });

        });

        $(document).on("click", ".remove_item", function() {
            $(this).parents("tr").remove();
            var countTTB = $('#tableTTB tbody tr').length;
            $("#countTTB").html(countTTB);
        });
        
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

            var arr = [];
            var arr_id = [];
            var validproduct = '';

            $('.product').each(function(){
                var value = $(this).val();
                var id = $(this).attr('id');
                if (arr.indexOf(value) == -1){
                    arr.push(value);
                    validproduct = true;
                }else{
                    arr_id.push(id);
                    var current_arr = arr_id.pop();
                    $('.'+current_arr).css("background-color", "#ffd5d5");
                    $('.'+current_arr).find('select').focus();
                    validproduct = false;
                    Swal.fire(
                        'Informasi',
                        'Terdapat Duplikasi Item DPM, silahkan hapus atau ganti salah satu item tersebut.',
                        'warning'
                    );
                    return false;
                }
            });

            e.preventDefault();
            if (form.valid() && validproduct === true ) {
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

            var arr = [];
            var arr_id = [];
            var validproduct = '';

            $('.product').each(function(){
                var value = $(this).val();
                var id = $(this).attr('id');
                if (arr.indexOf(value) == -1){
                    arr.push(value);
                    validproduct = true;
                }else{
                    arr_id.push(id);
                    var current_arr = arr_id.pop();
                    $('.'+current_arr).css("background-color", "#ffd5d5");
                    $('.'+current_arr).find('select').focus();
                    validproduct = false;
                    Swal.fire(
                        'Informasi',
                        'Terdapat Duplikasi Item DPM, silahkan hapus atau ganti salah satu item tersebut.',
                        'warning'
                    );
                    return false;
                }
            });

            e.preventDefault();
            if (form.valid() && validproduct === true) {
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
