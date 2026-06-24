@extends('layouts.app')

@section('page-header')
    DPM
@stop

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('purchase_request.index') }}">DPM</a></li>
        <li class="breadcrumb-item active" aria-current="page">Tambah</li>
    </ol>
@endsection


@section('content')
    {!! Form::open(['method' => 'POST', 'route' => ['purchase_request.store'], 'id'=>'form-dpm', 'files' => true]) !!}
        <div class="bgc-white p-30 bd">
            <h6>Pengajuan DPM</h6>
            <hr class='mB-30'>

            <div class="row mt-5">
                <div class="col-lg-6">
                    <div class="row">
                        <label class="col-sm-4">Location <span class="text-danger">*</span></label>
                        <div class="col-sm-7">
                            <input type="text" readonly class="form-control" value="{{ $locationName }}">
                            <input type="hidden" name="location_id" id="location_id" value="{{ $locationID }}">
                            @if($errors->has('location_id'))
                                <p class="help-block">
                                    {{ $errors->first('location_id') }}
                                </p>
                            @endif
                        </div>
                    </div>

                    <div class="row mt-2">
                        <label class="col-sm-4">Kategori Produk <span class="text-danger">*</span></label>
                        <div class="col-sm-7">
                            <input type="hidden" name="category_id" id="category_id" value="{{ $itemID }}">
                            <input type="text" readonly class="form-control" value="{{ $itemName }}">
                            <p class="help-block"></p>
                            @if($errors->has('category_id'))
                                <p class="help-block">
                                    {{ $errors->first('category_id') }}
                                </p>
                            @endif
                        </div>
                    </div>

                    <div class="row">
                        <label class="col-sm-4">Project  <span class="text-danger">*</span></label>
                        <div class="col-sm-7">
                            {!! Form::select('project_id', $project, old('project_id'), ['class' => 'form-control select2', 'required' => '','id' =>'project']) !!}
                            <p class="help-block"></p>
                            @if($errors->has('project_id'))
                                <p class="help-block">
                                    {{ $errors->first('project_id') }}
                                </p>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">

                    <div class="row">
                        <label  class="col-sm-3">Kapal/Departemen </label>
                        <div class="col-sm-8">
                            {!! Form::select('department_id', $department, old('department_id'), ['class' => 'form-control select2', 'id' =>'department']) !!}
                            <p class="help-block"></p>
                            @if($errors->has('department_id'))
                                <p class="help-block">
                                    {{ $errors->first('department_id') }}
                                </p>
                            @endif
                        </div>
                    </div>
                    <div class="row">
                        <label  class="col-sm-3">Deskripsi</label>
                        <div class="col-sm-8">
                            <textarea type="text" class="form-control" name="description" ></textarea>
                        </div>
                    </div>


                </div>
            </div>

            <hr>
            <div class="row mt-4">
                <label class="col-sm-3">Scan Document MR  <kbd class="btn-danger mr-2"><i class="fa fa-file-pdf-o"></i> PDF</kbd></label>
                <div class="col-sm-8">
                    {!! Form::myFile('mr_file', '') !!}
                </div>
            </div>
            <hr>


            <h6 class="mt-4" >Daftar Item </h6>
            <hr>
            <div class="alert alert-info mb-4">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h6 class="alert-heading">Informasi</h6>
                - Jika tidak ada Item Product silahkan hubungi bagian Logistik untuk menambahkan Master Item Product <br>
                - Jika terdapat stock silahkan hubungi bagian Logistik untuk melakukan Delivery Order (DO) <br>
                - Hanya bisa mengajukan Maksimal 25 Item DPM
            </div>

            <div class='border col-1 p-10 pull-left text-center '>
                Jumlah Item
                <h4 id="countDPM" class="font-weight-bold mb-0 text-danger"> {{ count($inventory) }} </h4>
            </div>
            <a class="btn btn-success c-white add_item float-right mb-2 fw-600"><i class="ti-plus"></i> TAMBAH ITEM</a>
            <table class="table table-bordered mt-2" id="tableDPM">
                <thead>
                    <th style="width:400px !important">Nama Barang <span class="text-danger">*</span></th>
                    <th>QTY / Satuan<span class="text-danger">*</span></th>
                    <th style="width:250px !important">Catatan</th>
                    <th>Flag <span class="text-danger">*</span></th>
                    <th>Tgl Dibutuhkan <span class="text-danger">*</span></th>
                    <th>Aksi</th>
                </thead>
                <tbody class="item_form" id="itemDPM">
                    @php
                        $no = 1;
                    @endphp
                    @foreach($inventory as $item)

                        <input type="hidden" value="{{ $item->productID }}" name="product_id[]" class="product">
                        <tr class="product_{{ $no }}">
                            <td>
                                [{{ $item->productCode }}] {{ $item->productName }}<br>
                                {!! $item->productPartNumber != NULL ? '<small> PN: '.$item->productPartNumber.'</small>' : '' !!}
                                {!! $item->product_type != NULL ? '<small> Tipe: '.$item->product_type.'</small>' : '' !!} {!! $item->productBrand != NULL ? '<small>Brand: '.$item->productBrand.'</small>' : '' !!}
                                <input type="hidden" name="index[]" value="{{ $no }}">

                                <br> {{ $item->itemName }}
                            </td>
                            <td>
                                {!! Form::number('qty[]', old('qty'), ['class' => 'form-control', 'placeholder' => '', 'required' => '','min' => '0.1']) !!}
                                <input name="measure[]"  class="form-control measure mB-5" id="measure" value="{{ $item->unit }}" readonly>
                                <small class="text-danger ml-2"> Stock: {{ $item->stock_onhand }} | Min:  {{ $item->stock_min }} | Max: {{ $item->stock_max }}</small>
                            </td>

                            <td>
                                {!! Form::textarea('notes[]', old('notes'), ['class' => 'form-control', 'rows' => 2, 'placeholder' => '']) !!}
                            </td>
                            <td>
                                {!! Form::select('flag[]', $flag, null , ['class' => 'form-control select2', 'required' => '']) !!}
                            </td>
                            <td>
                                {!! Form::text('needed_on_date[]', date("m/d/Y") , ['onchage' => 'checkDate(this.value)','class' => 'form-control datepicker needed_on_date', 'placeholder' => '', 'required' => '']) !!}
                            </td>
                            <td>
                                <a class="remove_item text-white btn btn-danger btn-md pull-right"><i class="ti-trash"></i></a>
                            </td>
                        </tr>
                        @php
                            $no ++;
                        @endphp
                    @endforeach
                </tbody>
            </table>

        </div>
        <div class="mt-4">
            <a href="{{ route('purchase_request.index') }}" class="btn btn-light mr-1 text-uppercase fsz-sm fw-600">{{ trans('Cancel') }}</a>
            <input class="btn btn-info text-uppercase fsz-sm fw-600" type="submit" name="save" id="btn-draft-dpm" value="Save as Draft">
            <input type="hidden" value="0" name="status">
            <input class="btn btn-danger text-uppercase fsz-sm fw-600" type="submit" name="publish" id="btn-submit-dpm" value="Publish DPM">
        </div>
    {!! Form::close() !!}
@stop

@section('js')

<script  type='text/javascript'>

	$(document).ready(function() {

        // ADD ITEM
        var wrapper = $(".item_form");
        var add_sk = $(".add_item");

        var count = 0;
        var max = 25;
        var i = {{ $no }};

        $(document).on("click", ".add_item", function(e) {

            count += 1;

            if (count <= max) {
                i += 1;

                e.preventDefault();
                $(wrapper).append(
                    '<tr class="product_'+i+' level_'+i+'">' +
                        '<td>' +
                            '<input type="hidden" name="index[]" value="'+i+'">' +
                            '<small>Nama Produk [PN/SPEC] [Kode]</small>'+
                            '<select name="product_id[]" class="form-control productItem product" id="product_'+i+'" required>' +
                            '</select>' +
                            '<span id="masteritem_'+i+'"></span><span id="mastercategory_'+i+'"></span>' +
                            '<p class="text-info mb-2 filter_inc" id="filter_'+i+'" data-id="'+i+'">Filter</p>' +
                            '<div id="filter-form_'+i+'" style="display:none">' +
                                '<span class="text-default float-right mb-2" id="filter-hide_'+i+'"><i class="ti-close"></i></span>' +
                                '<select name="item[]" class="w-100 form-control" id="item_'+i+'"></select>' +
                                '<select name="category[]" class="form-control category" id="category_'+i+'"></select>' +
                            '</div>' +
                        '</td>' +
                        '<td>' +
                            '<input type="number" name="qty[]" class="form-control qtyItem" min="0.1" required id="input_qty_'+i+'" onwheel="return false;">' +
                            '<input name="measure[]"  class="form-control select2" id="measure_'+i+'" style="display:none" readonly>' +
                            '<span class="text-danger" id="preview_qty_'+i+'" style="display:none"></span>' +
                        '</td>' +
                        '<td>' +
                            '<textarea name="notes[]" class="form-control"></textarea>' +
                        '</td>' +
                        '<td>' +
                            '<select name="flag[]" class="form-control" required>' +
                                '<option value="normal">Normal</option>'+
                                '<option value="urgent">Urgent</option>'+
                            '</select>' +
                        '</td>' +
                        '<td>' +
                            '<input type="text" name="needed_on_date[]" class="form-control dateItem" required id="datepicker_'+i+'" onchage="checkDate(this.value)" value="{{ date("m/d/Y") }}" >' +
                        '</td>' +
                        '<td>' +
                            '<a class="remove_item text-white btn btn-danger btn-md"><i class="ti-trash"></i></a>' +
                            '<div id="js_'+i+'" ></div>'+
                        '</td>' +
                    '</tr>'
                );

                var countDPM = $('#tableDPM tbody tr').length;
                $("#countDPM").html(countDPM);

                $.ajax({
                    url: "{{ route('dpm.js') }}/"+i,
                    type: "GET",
                    success: function(data){
                        $('#js_'+i).html(data);
                    }
                });
            }else{
                Swal.fire(
                    'Peringatan!',
                    'Hanya bisa mengajukan Maksimal 10 Item DPM',
                    'warning'
                );
            }

        });


        // END DINAMIS

        function isEmpty(obj) {
            for(var key in obj) {
                if(obj.hasOwnProperty(key))
                    return true;
            }
            return false;
        }

        $(document).on("click", ".remove_item", function() {
            $(this).parents("tr").remove();
            var countDPM = $('#tableDPM tbody tr').length;
            $("#countDPM").html(countDPM);
        });

        var $item   = $('.item');
        var $category = $(".category");
        var $product = $("#product_1");
        var $type   = $(".type");
        $type.next(".select2-container").hide();
        $(".measure").next(".select2-container").hide();

        $(".needed_on_date").datepicker().on('change', function() {
            checkDate($(this).datepicker('getDate'));
            $(this).valid();
        });

        $('#department').select2().on('change', function() {
            $(this).valid();
        });


        function checkDate(val) {
            var selectedDate = val;
            var now = new Date(new Date().getFullYear(),new Date().getMonth() , new Date().getDate());
            var dt1 = Date.parse(now),
            dt2 = Date.parse(selectedDate);
            if (dt2 < dt1) {
                Swal.fire(
                    'Informasi',
                    'Tanggal dibutuhkan Item DPM adalah Back Date',
                    'info'
                );
            }
        }

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
