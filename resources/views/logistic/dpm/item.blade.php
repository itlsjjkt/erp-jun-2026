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
    <input type="hidden" name="location_id" value="{{ $location_id }}" id="location_id">
    <input type="hidden" name="category_id" value="{{ $category }}" id="category_id">
    <input type="hidden" name="department_id" value="{{ $department_id }}" id="department_id">
    <input type="hidden" name="project_id" value="{{ $project_id }}" id="project_id">
    <input type="hidden" name="type" value="{{ $type }}" id="type">

        <div class="bgc-white p-30 bd">
            <h6>Pengajuan DPM</h6>
            <hr class='mB-30'>
            <p>Step 2 dari 2</p>

            <div class="row">
                <div class="col-lg-6">
                    <div class="row">
                        <label  class="col-sm-4">Tipe</label>
                        <div class="col-sm-8">
                            : {{ strtoupper($type) }}
                        </div>
                    </div>
                    <div class="row">
                        <label  class="col-sm-4">Lokasi Gudang</label>
                        <div class="col-sm-8">
                            : {{ getDataByID('locations',$location_id )->name }}
                        </div>
                    </div>
                    <div class="row">
                        <label  class="col-sm-4">Kapal/Department</label>
                        <div class="col-sm-8">
                            : {{ getDataByID('departments',$department_id )->name }}
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="row">
                        <label  class="col-sm-4">Project</label>
                        <div class="col-sm-8">
                            : {{ getDataByID('projects',$project_id )->name }}
                        </div>
                    </div>
                    <div class="row">
                        <label  class="col-sm-4">Kategori Produk</label>
                        <div class="col-sm-8">
                            : {{ implode(', ', $category_name ) }}
                        </div>
                    </div>
                </div>
            </div>


            <div class="form-group row mt-5">
                <label  class="col-sm-3">Lampirkan Dokumen MR jika ada dengan format <kbd class="btn-danger mr-2"><i class="fa fa-file-pdf-o"></i> PDF</kbd></label>
                <div class="col-sm-4">
                    {!! Form::myFile('mr_file', '',['class' => 'form-control', 'id' => 'mr_file']) !!}
                </div>
            </div>
            <div class="form-group row">
                <label  class="col-sm-3">Deskripsi</label>
                <div class="col-sm-4">
                    <textarea type="text" class="form-control" name="description" ></textarea>
                </div>
            </div>

            <div class="form-group row">
                <label  class="col-sm-3">Flag & Tanggal Dibutuhkan<span class="text-danger">*</span></label>
                <div class="col-sm-2">
                        <select name="flag" id="flag_" class="form-control select2" required>
                            <option value="normal">Normal</option>
                            <option value="urgent">Urgent</option>
                        </select>
                </div>
                <div class="col-sm-2">
                    {!! Form::text('needed_on_date',date("m/d/Y", strtotime("+7 days")), ['class' => 'form-control datepicker needed_on_date', 'placeholder' => '', 'required' => '', 'id' => 'needed_on_date_']) !!}
                </div>
            </div>


            <h6 >Daftar Item </h6>
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

            <div class='border col-1 p-10 pull-left text-center '>
                Jumlah Item
                <h4 id="countDPM" class="font-weight-bold mb-0 text-danger"> 0 </h4>
            </div>

            <a class="btn btn-success c-white add_item float-right mb-2 fw-600"><i class="ti-plus"></i> TAMBAH ITEM</a>
            <table class="table table-bordered mt-2" id="tableDPM">
                <thead>
                    <th class="text-center">No</th>
                    <th style="max-width: 350px">Nama Barang <span class="text-danger">*</span></th>
                    <th style="width: 200px">QTY / Satuan<span class="text-danger">*</span></th>
                    <th style="width: 250px;">Catatan</th>
                    <th>Aksi</th>
                </thead>
                <tbody class="item_form" id="itemDPM"></tbody>
            </table>

        </div>
        <div class="mt-4">
            <a href="#" onclick="history.back()" class="btn btn-light mr-1 text-uppercase fsz-sm fw-600">Kembali</a>
            <!-- <input class="btn btn-info text-uppercase fsz-sm fw-600" type="submit" id="btn-draft-dpm" value="Save as Draft"> -->
            <input type="hidden" value="0" name="status" id="status-dpm">
            <input class="btn btn-danger text-uppercase fsz-sm fw-600 float-right" type="submit" id="btn-submit-dpm" value="Create DPM">
        </div>
    {!! Form::close() !!}
@stop

@section('js')

<script  type='text/javascript'>

	$(document).ready(function() {

        $('#flag_').change(function() {
            var flagValue = $(this).val();
            var currentDate = new Date();
            var newDate;

            if (flagValue === 'normal') {
                // Add 7 days for 'normal'
                newDate = new Date(currentDate);
                newDate.setDate(currentDate.getDate() + 7);
            } else if (flagValue === 'urgent') {
                // Add 3 days for 'urgent'
                newDate = new Date(currentDate);
                newDate.setDate(currentDate.getDate() + 3);
            }

            // Format the date as mm/dd/yyyy
            var formattedDate = (newDate.getMonth() + 1) + '/' + newDate.getDate() + '/' + newDate.getFullYear();
            $('#needed_on_date_').val(formattedDate);
        });

        $('input[type=file]').change(function () {
            var val = $(this).val().toLowerCase(),
                regex = new RegExp("(.*?)\.(pdf)$");

            if (!(regex.test(val))) {
                $(this).val('');
                Swal.fire(
                    'Informasi',
                    'Mohon upload MR File dengan format PDF',
                    'warning'
                );
            }
        });

        // ADD ITEM
        var wrapper = $(".item_form");
        var add_sk = $(".add_item");

        var count = 0;
        var max = 50;
        var i = 0;

        $(document).on("click", ".add_item", function(e) {

            count += 1;

            if (count <= max) {
                i += 1;

                e.preventDefault();
                $(wrapper).append(
                    '<tr class="product[] dpm" level_'+i+'" id="item">' +
                        '<td style="max-width: 350px">' +
                            '<p class="text-center row-index">' + count +"</p>" +
                        '</td>' +
                        '<td style="max-width: 350px">' +
                            '<input type="hidden" name="index[]" value="'+i+'">' +
                            '<small>Nama Produk [PN/SPEC] [Kode] [Merk]</small>'+
                            '<select name="product_id[]" class="form-control productItem product narrow wrap" id="product_'+i+'" required></select>' +
                        '</td>' +
                        '<td>' +
                            '<div class="input-group">'+
                                '<input type="number" name="qty[]" class="form-control qtyItem"  min="0.1" required id="input_qty_'+i+'" onwheel="return false;">' +
                                '<div class="input-group-prepend">'+
                                    '<span class="input-group-text" id="measure_text_'+i+'"></span>'+
                                    '</div>'+
                                '</div>'+
                            '<input type="hidden" name="measure[]" id="measure_'+i+'">' +
                            '<div class="info_stock_'+i+'" id="info_stock"></div>'+
                            '<div class="validasi_stock_'+i+'" id="validasi_stock">'+
                                '<input type="hidden" id="stock_min_'+i+'" value="" />'+
                                '<input type="hidden" id="stock_max_'+i+'" value="" />'+
                                '<input type="hidden" id="stock_onhand_'+i+'" value="" />'+
                            '</div>' +
                        '</td>' +
                        '<td>' +
                            '<input type="hidden" name="notes[]" class="form-control" id="description_'+i+'">' +
                            '<div style="width: 250px;">' +
                                '<trix-editor input="description_'+i+'"></trix-editor>' +
                            '</div>' +
                        '</td>' +
                        '<td>' +
                            '<a class="remove_item text-white btn btn-danger btn-md pull-right"><i class="ti-trash"></i></a>' +
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
                    'Hanya bisa mengajukan Maksimal 25 Item DPM',
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

        var $item   = $('.item');
        var $product = $("#product_1");

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
