@extends('layouts.app')

@section('page-header')
    DPM
@stop

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('purchase_request.index') }}">DPM</a></li>
        <li class="breadcrumb-item active" aria-current="page">Edit</li>
    </ol>
@endsection


@section('content')

<div class="mB-40">
    {!! Form::model($pr, [
            'action' => ['Logistic\PurchaseRequestController@update', $pr->id],
            'method' => 'put',
            'class' => 'form-horizontal mt-3',
            'id'    => 'formPR',
            'files' => true
        ])
    !!}
        <input type="hidden" name="location_id" id="location_id" value="{{$pr->location_id}}">
        <input type="hidden" name="category_id" id="category_id" value="{{$category}}">
        <input type="hidden" name="project_id" id="project_id" value="{{$pr->project_id}}">
        <input type="hidden" name="location_alias" value="{{$pr->locationAlias}}">
        <input type="hidden" name="company_alias" value="{{$pr->companyAlias}}">

		<div class="bgc-white p-30 bd">
            <h6>Edit DPM</h6>
            <hr class='mB-30'>

            <div class="row">
                <div class="col-sm-6">
                    <div class="mt-3 form-group row">
                        <label class="col-sm-3">Nomor DPM </label>
                        <div class="col-sm-8">
                            <input class="form-control" value="{{$pr->doc_no}}" readonly>
                        </div>
                    </div>
                    <div class="mt-3 form-group row">
                        <label class="col-sm-3">Project</label>
                        <div class="col-sm-8">
                            <input class="form-control" value="{{$pr->project}}" readonly>
                        </div>
                    </div>

                    <div class="mt-3 form-group row">
                        <label class="col-sm-3">Tipe DPM</label>
                        <div class="col-sm-8">
                            {!! Form::select('type', $type, old('type'), ['class' => 'form-control select2', 'required' => '']) !!}
                        </div>
                    </div>
                </div>

                <div class="col-sm-6">

                    <div class="mt-3 form-group row">
                        <label class="col-sm-3">Nama Kapal/Department</label>
                        <div class="col-sm-8">
                            {!! Form::select('department_id', $department, old('department_id'), ['class' => 'form-control select2', 'required' => '']) !!}
                            <p class="help-block"></p>
                            @if($errors->has('department_id'))
                                <p class="help-block">
                                    {{ $errors->first('department_id') }}
                                </p>
                            @endif
                        </div>
                    </div>

                    <div class="mt-3 form-group row">
                        <label class="col-sm-3">Deskripsi</label>
                        <div class="col-sm-8">
                            {!! Form::textarea('description', old('description'), ['class' => 'form-control','rows'=>'2']) !!}
                            <p class="help-block"></p>
                            @if($errors->has('description'))
                                <p class="help-block">
                                    {{ $errors->first('description') }}
                                </p>
                            @endif
                        </div>
                    </div>

                </div>
            </div>
            <hr>
            <div class="form-group row">
                <label class="col-sm-3">Scan Document MR  <kbd class="btn-danger mr-2"><i class="fa fa-file-pdf-o"></i> PDF</kbd></label>
                <div class="col-sm-8">
                    {!! Form::myFile('mr_file', '') !!}
                    @if ($pr->mr_file)
                        <code> {{ asset('storage'.$pr->mr_file) }}</code>
                    @endif
                </div>
            </div>

            <hr>

            <h6>Daftar Item</h6>
            <hr>
            <div class="alert alert-info mb-4">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h6 class="alert-heading">Informasi</h6>
                - Jika tidak ada Item Produk silahkan hubungi bagian Logistik untuk menambahkan Master Item Produk <br>
                - Jika terdapat stock silahkan hubungi bagian Logistik untuk melakukan Delivery Order (DO) <br>
                - Hanya bisa mengajukan Maksimal 50 Item DPM
            </div>
            @if($pr->status==3)
                <div class="alert alert-warning mb-4">
                    <h6 class="alert-heading mb-0 font-weight-bold">Alasan Hold</h6>
                    {{ $pr_history->message }}
                </div>
            @endif
            <div class='border col-1 p-10 pull-left text-center '>
                Jumlah Item
                <h4 id="countDPM" class="font-weight-bold mb-0 text-danger"> {{ count($pr_items) }} </h4>
            </div>
            <a class="btn btn-success btn-sm c-white add_item float-right mb-2 fw-600"><i class="ti-plus"></i> TAMBAH ITEM</a>
            <div class="table-responsive">
                <table class="table table-bordered mt-2" id="tableDPM">
                    <thead>
                        <tr>
                            <th class="text-center">No</th>
                            <th>Nama Barang <span class="text-danger">*</span></th>
                            <th style="width:150px">QTY <span class="text-danger">*</span></th>
                            <th style="width:250px">Catatan</th>
                            <th style="width:120px">Flag <span class="text-danger">*</span></th>
                            <th style="width:120px">Tgl Dibutuhkan <span class="text-danger">*</span></th>
                            <th style="width:60px">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="item_form">
                        @if (count($pr_items) > 0)
                            @php
                                $no = 1
                            @endphp
                            @foreach ($pr_items as $item)
                                    <?php
                                        if ($item->productPartNumber !=null) {
                                            $partNumber = "[".$item->productPartNumber."]";
                                        } else{
                                            $partNumber = '';
                                        }
                                    ?>
                                    <tr class="itemDPM product_{{$no}} level_{{$no}} dpm" id="itemDPM">
                                            <td>
                                                <p class="text-center">{{ $no }}</p>
                                            </td>
                                            <td>
                                                <input type="hidden" name="index[]" value="{{$no}}">
                                                <input name="dpm_item_id[]" type="hidden" value="{{ $item->id }}">
                                                <small>Nama Produk [PN/SPEC] [Kode] [Merk]</small>
                                                <select name="product_id[]" class="form-control select2 mB-5 productItem product" id="product_{{$no}}" required>
                                                    <option value="{{$item->product_id}}" selected data-item="{{$category}}" data-code="{{$item->productCode}}">{{$item->product}} {{$partNumber}} [{{$item->productCode}}]  [{{$item->productBrand}}]</option>
                                                </select>
                                            </td>
                                            <td>
                                                <div class="input-group">
                                                    <input type="number" name="qty[]" class="form-control qtyItem" value="{{$item->qty}}" id="inputs_qty_{{$no}}" min="0.1" required onwheel="return false;">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text" id="measure_text_{{$no}}"> {{$item->measure}} </span>
                                                    </div>
                                                </div>
                                                <input type="hidden" readonly name="measure[]" class="form-control" value="{{$item->measure}}" id="measure_{{$no}}" readonly>
                                                <div class="text-danger" id="previews_qty_{{$no}}" style="display:none"></div>
                                            </td>
                                            <td>
                                                <input id="notesss{{$item->product_id}}" type="hidden" name="notes[]" class="form-control" value="{{$item->notes}}">
                                                <div style="width: 250px;">
                                                    <trix-editor input="notesss{{$item->product_id}}"></trix-editor>
                                                </div>
                                            </td>
                                            <td>
                                                {!! Form::select('flag[]', $flag, $item->flag , ['class' => 'form-control select2', 'required' => '']) !!}
                                            </td>
                                            <td>
                                                <input type="text" name="needed_on_date[]" class="form-control datepicker dateItem" id="datepicker_{{$no}}" value="{{ date('m/d/Y',strtotime( $item->needed_on_date)) }}" >
                                            </td>
                                            <td>
                                                <a href="{{ route('purchase_request.remove_item',['dpm_id'=>$item->id ]) }}"  title="Hapus Item" data-toggle='tooltip' class='btn btn-danger' id="btn-remove"><span class='ti-trash icon-lg'></span> </button>
                                            </td>
                                    </tr>
                                    @php
                                        $no++
                                    @endphp
                            @endforeach
                        @endif
                    </tbody>
                </table>
		    </div>
		</div>

        <div class="mt-4">
            <a href="{{ route('purchase_request.index') }}" class="btn btn-light  text-uppercase fsz-sm fw-600">{{ trans('Cancel') }}</a>
            @if($pr->status==0)
		        <!-- <input type="submit" class="btn btn-primary text-uppercase fsz-sm fw-600" id="btn-draft-dpm" value="Save As Draft"> -->
                <input type="hidden" value="0" name="status" id="status-dpm">
                <input type="hidden" value="0" name="revision">
                <input type="submit" class="btn btn-danger text-uppercase fsz-sm fw-600 float-right" name="publish" id="btn-submit-dpm" value="Publish DPM">
            @endif
            @if($pr->status==3)
                <input type="hidden" value="1" name="status" id="status-dpm">
                <input type="hidden" value="1" name="revision">
                <input type="submit" class="btn btn-danger text-uppercase fsz-sm fw-600" name="publish" id="btn-submit-dpm" value="Revisi DPM">
            @endif
        </div>

	{!! Form::close() !!}
</div>

@stop


@section('js')

    <script  type='text/javascript'>
        $(document).ready(function() {

            $('#department').select2().on('change', function() {
                $(this).valid();
            });
            $('#product_id').select2().on('change', function() {
                $(this).valid();
            });


            var wrapper = $(".item_form");
            var add_sk = $(".add_item");


            var max = 50;
            var count = $('[id^=itemDPM]').length;
            let i = count;

            $(document).on("click", ".add_item", function(e) {
                count += 1;
                e.preventDefault();

                if (count <= max) {
                    i+=1;

                    $(wrapper).append(
                        '<tr class="dpm product_'+i+' level_'+i+'">' +
                            '<td>' +
                                '<p class="text-center row-index">' + count +"</p>" +
                            '</td>' +
                            '<td>' +
                                '<input type="hidden" name="index[]" value="'+i+'">' +
                                '<small>Nama Produk [PN/SPEC] [Kode]</small>'+
                                '<select name="product_id_new[]" class="form-control productItem product" id="product_'+i+'" required></select>' +
                            '</td>' +
                            '<td>' +
                                '<div class="input-group">'+
                                    '<input type="number" name="qty_new[]" class="form-control qtyItem"  min="0.1" required id="input_qty_'+i+'" onwheel="return false;">' +
                                    '<div class="input-group-prepend">'+
                                        '<span class="input-group-text" id="measure_text_'+i+'"></span>'+
                                        '</div>'+
                                    '</div>'+
                                '<input type="hidden" name="measure_new[]"  class="form-control" id="measure_'+i+'">' +
                                '<div class="info_stock_'+i+'" id="info_stock"></div>'+
                                '<span class="text-danger" id="preview_qty_'+i+'" style="display:none"></span>' +
                            '</td>' +
                            '<td>' +
                                '<input type="hidden" name="notes_new[]" class="form-control" id="description_'+i+'">' +
                                '<div style="width: 250px;">' +
                                    '<trix-editor input="description_'+i+'"></trix-editor>' +
                                '</div>' +
                            '</td>' +
                            '<td>' +
                                '<select name="flag_new[]" class="form-control">' +
                                    '<option value="normal">Normal</option>'+
                                    '<option value="urgent">Urgent</option>'+
                                '</select>' +
                            '</td>' +
                            '<td>' +
                                '<input type="text" name="needed_on_date_new[]" class="form-control dateItem" required id="datepicker_'+i+'" value="{{ date("m/d/Y") }}">' +
                            '</td>' +
                            '<td>' +
                                '<a class="text-white remove_item btn btn-danger btn-md"><i class="ti-trash"></i></a>' +
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

            function reorderRows() {
                var countDPM = 0;

                $("#tableDPM tbody tr").each(function (index) {
                    countDPM++;
                    $(this).find(".row-index").text(index + 1); // Update displayed index
                    $(this).find('input[name="index[]"]').val(index + 1); // Update hidden input value
                });

                count--;
                // Update the displayed count of rows
                $("#countDPM").html(countDPM);
            }

            $(document).on("click", ".remove_item", function() {
                $(this).parents("tr").remove();
                var countDPM = $('#tableDPM tbody tr').length;
                $("#countDPM").html(countDPM);
                reorderRows();
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
                $('#status-dpm').val('1');
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

                var arr_id = [];

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
                        validproduct= false;
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

            $(document).on('click', "#btn-draft-dpm", function(e) {
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


        });
    </script>

    @if (count($pr_items) > 0)
        @php
            $no = 1
        @endphp
        @foreach ($pr_items as $item)
            <script type='text/javascript'>

                function isEmpty(obj) {
                    for(var key in obj) {
                        if(obj.hasOwnProperty(key))
                            return true;
                    }
                    return false;
                }

                $(document).ready(function() {
                    var $item        = $('#category_id');
                    var $product     = $("#product_{{$no}}");
                    var $input_qty   = $("#inputs_qty_{{$no}}");
                    var $preview_qty = $("#previews_qty_{{$no}}");
                    var $masteritem  = $("#masteritem_{{$no}}");
                    var $measure     = $("#measure_{{$no}}");


                    $product.select2({
                        placeholder: 'Cari produk dengan mengetik Nama Produk...',
                        minimumInputLength: 2,
                        ajax: {
                            url:"{{ route('master.get_products') }}/" + $item.val(),
                            dataType: 'json',
                            delay: 250,
                            processResults: function (data) {
                                return {
                                    results:  $.map(data, function (item) {
                                            if(item.part_number === '' || item.part_number == null){
                                                var part_number = " [" + item.code + " - " + item.brand + "]";
                                            }else{
                                                var part_number = " [" + item.part_number + "] ["+ item.code + "] [" + item.brand + "]";
                                            }
                                            return {
                                            id: item.id,
                                            text: item.name + part_number,
                                            measure: item.measure,
                                            code: item.code,
                                            item: item.item_id
                                        }
                                    })
                                };
                            },
                            cache: true
                        }
                    }).on('change', function() {
                        $('#inputs_qty_{{$no}}').val('');
                        $('#previews_qty_{{$no}}').text('');
                        $('#measure_{{$no}}').show();
                        $('.product_{{$no}}').css("background-color", "#fff");

                        var code = $product.select2('data')[0].code;
                        var measure = $product.select2('data')[0].measure;

                        $('#measure_{{$no}}').val(measure);



                    });

                    $input_qty.on('keyup', function(){
                        var product_id      = $product.val();
                        var location_id     = $('#location_id').val();

                        $.ajax({
                            url: "{{ route('logistic.get_stock') }}/" + product_id + "/" + location_id,
                            type: 'GET',
                            cache: false,
                            success: function(data){
                                if(data.type !== 3 && data.stock_onhand > 1){
                                    $preview_qty.text("Stock: " + parseFloat(data.stock_onhand) + " / Min: " + data.stock_min + " / Max: " + data.stock_max);
                                    $preview_qty.show();
                                }
                            }
                        });
                    });


                });
            </script>

            @php
                $no++
            @endphp
        @endforeach
    @endif
@stop
