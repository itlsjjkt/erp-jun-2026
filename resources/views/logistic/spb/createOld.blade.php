@extends('layouts.app')

@section('page-header')
    Surat Pengantar Barang
@stop

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('logistic.spb.index') }}">Surat Pengantar Barang</a></li>
        <li class="breadcrumb-item active" aria-current="page">Tambah</li>
    </ol>
@endsection


@section('content')
    {!! Form::open(['method' => 'POST', 'route' => ['logistic.spb.store'], 'id' => 'form-spb']) !!}
	<div class="bgc-white p-30 bd">
        <input type="hidden" name="lpbID" value="{{ $lpb_id }}">

        <h6><a class="float-left" href="{{ route('logistic.spb.index') }}"><i class="ti-arrow-left mR-10"></i></a> {{ trans('Back') }}</h6>
        <hr class='mB-30'>

        <div class="row mt-4">
            <div class="col-lg-6">
                <div class="form-group row">
                    <label class="col-sm-3 mt-2">Tanggal SPB <span class="text-danger">*</span></label>
                    <div class="col-sm-8">
                        {!! Form::date('date_transaction', old('date_transaction'), ['class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}
                        <p class="help-block"></p>
                        @if($errors->has('date_transaction'))
                            <p class="help-block">
                                {{ $errors->first('date_transaction') }}
                            </p>
                        @endif
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-3">Jenis SPB <span class="text-danger">*</span></label>
                    <div class="col-sm-4">
                        {!! Form::select('type', $type, old('type'), ['class' => 'form-control select2','id'=>'type', 'required' => '']) !!}
                    </div>
                    <div class="col-sm-4" id="cargo">
                        {!! Form::select('delivered_by', $ekspedisi, old('delivered_by'), ['class' => 'form-control select2','id' => 'expedition']) !!}
                    </div>
                    <div class="col-sm-4" id="cargo_is_handcarry">
                        {!! Form::select('delivered_by2', $handcarry, old('delivered_by2'), ['class' => 'form-control select2','id' => 'is_handcarry','required' => '']) !!}
                    </div>
                </div>
                <input type="hidden" value="" id="delivered_pic" name="delivered_pic">
                <input type="hidden" value="" id="delivered_pic_telp" name="delivered_pic_telp">
            
                <div class="form-group row mb-4">
                    <label class="col-sm-3 mt-2"> Jalur Pengiriman</label>
                    <div class="col-sm-8">
                        {!! Form::select('jalur_pengiriman', $jalur, old('jalur_pengiriman'), ['class' => 'form-control select2','id'=>'jalur_pengiriman']) !!}
                    </div>
                </div>
                <div class="form-group row mb-4">
                    <label class="col-sm-3 mt-2"> Operator<span class="text-danger"> *</span></label>
                    <div class="col-sm-8">
                        {!! Form::select('operator', $operator, old('operator'), ['class' => 'form-control select2', 'required' => '','id' =>'operator']) !!}
                    </div>
                </div>
                <div class="form-group row mb-4">
                    <label class="col-sm-3 mt-2">Checker<span class="text-danger"> *</span></label>
                    <div class="col-sm-8">
                        {!! Form::select('checker', $operator, old('checker'), ['class' => 'form-control select2', 'required' => '','id' =>'checker']) !!}
                    </div>
                </div>
            
                <div class="form-group row mb-4">
                    <label class="col-sm-3 mt-2"> Cost SPB<span class="text-danger"> *</span></label>
                    <div class="col-sm-8">
                        {!! Form::select('company_id', $company, old('company_id'), ['class' => 'form-control select2', 'required' => '']) !!}
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="form-group row" id="pickup_">
                    <label class="col-sm-3 mt-2">Pengiriman</label>
                    <div class="col-sm-8">
                        {!! Form::select('is_pickup', $pickup, old('is_pickup'), ['class' => 'form-control select2','id' => 'pickuppp','required' => '']) !!}
                    </div>
                </div>
                <div class="form-group row mt-2" id="pickup_2">
                    <label class="col-sm-3 mt-1"> Lokasi Pick Up<span class="text-danger"> *</span></label>
                    <div class="col-sm-8">
                        {!! Form::text('pickup_from', old('pickup_from'), ['class' => 'form-control', 'placeholder' => 'Pick Up di..[contoh: PT...]','required' => '']) !!}
                    </div>
                    <label class="col-sm-3 mt-3"> Alamat Pick Up</label>
                    <div class="col-sm-8 mt-3">
                        {!! Form::textarea('pickup_address', old('pickup_address'), ['class' => 'form-control', 'placeholder' => '', 'style'=>'height:50px;','required' => '']) !!}
                    </div>
                    <label class="col-sm-3 mt-3"> PIC Lokasi Pick Up</label>
                    <div class="col-sm-8 mt-3">
                        <div class="row">
                            <div class="col-6">
                                {!! Form::text('pickup_pic_name', old('pickup_pic_name'), ['class' => 'form-control', 'placeholder' => 'Nama','required' => '']) !!}
                            </div>
                            <div class="col-6">
                                {!! Form::number('pickup_pic_telp', old('pickup_pic_telp'), ['class' => 'form-control', 'placeholder' => 'No Telpon','required' => '']) !!}
                            </div>                
                        </div>
                    </div>
                    <br><br><br><br>
                </div>
            
                <div class="form-group row">
                    <label class="col-sm-3 mt-2">Nama Penerima <span class="text-danger">*</span></label>
                    <div class="col-sm-8">
                        {!! Form::text('received_pic', old('received_pic'), ['class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}
                    </div>
                </div>
            
                <div class="form-group row">
                    <label class="col-sm-3 mt-2">Telp Penerima <span class="text-danger">*</span></label>
                    <div class="col-sm-8">
                        {!! Form::text('received_pic_telp', old('received_pic_telp'), ['class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}
                    </div>
                </div>
                <div class="form-group row mb-4">
                    <label class="col-sm-3 mt-2">Alamat Pengiriman</label>
                    <input id="alamatTujuannn" type="hidden" name="address" class="form-control" value="" rows="2">
                    <div class="col-sm-8">
                        <trix-editor input="alamatTujuannn"></trix-editor>
                    </div>
                </div>
            
                <div class="form-group row mb-4">
                    <label class="col-sm-3 mt-2">Catatan </label>
                    <input id="note___" type="hidden" name="notes" class="form-control" value="" rows="2">
                    <div class="col-sm-8">
                        <trix-editor input="note___"></trix-editor>
                    </div>
                </div>
            </div>
        </div>

        <div class="alert alert-info mT-3">
            LPB.
        </div>

        <div class="row mt-4">
            <div class="col-lg-12">
                <div class="float-right">
                    <input type="checkbox" id="is_qty" class="magic-checkbox mt-2" value="0">
                    <label for="is_qty">Set QTY sama dengan LPB</label>
                </div>
            </div>
        </div>
        @if (count($lpb) > 0)
            @php
                $no = 1
            @endphp
            @foreach ($lpb as $item)
            <table class="table table-bordered mt-2">
                <tbody class="item_form">
                    <input type="hidden" name="supplierID" value="{{ $item->supplierID }}" class="supplierID">
                    <tr>
                        <input name="lpb_id[]" type="hidden" value="{{ $item->id }}">
                        <td class="bg-light">
                            <table class="border-0 table m-0">
                                <tr>
                                    <td class="border-0"> Nomor LPB <br><span class="font-weight-bold">{{ $item->doc_no }} </span></td>
                                    <td class="border-0"> Nomor PO <br><span class="font-weight-bold">{{ $item->po_no }} </span></td>
                                    <td class="border-0"> Supplier <br><span class="font-weight-bold">{{ $item->supplier }}  </span></td>
                                    <td class="border-0">  
                                        <a href="{{ route('logistic.spb.item') }}?lpb_id={{ $lpb_id }}&id={{ $item->id  }}" class="btn btn-danger pull-right"><i class="ti-trash icon-lg"></i></a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th style="width:50px">No</th>
                                        <th style="width:30%">Nama Barang</th>
                                        <th style="width:20%">Spesifikasi</th>
                                        <th style="width:10%" class="text-center">QTY</th>
                                        <th class="text-center" style="width:150px">QTY SPB</th>
                                        <th style="width:200px">Notes</th>
                                    </tr>
                                </thead>
                                <?php
                                $lpbitem = getLPBItem($item->id); ?>
                                <tbody>
                                        @php
                                            $no = $no;
                                        @endphp
                                        @foreach ($lpbitem as $val)
                                            <tr class="product_{{ $val->id }}">
                                                <input name="lpb_spb_id[]" type="hidden" value="{{ $item->id }}">
                                                <input name="location_id[]" type="hidden" value="{{ $val->location_id }}">
                                                <input name="lpb_item_id[]" type="hidden" value="{{ $val->id }}">
                                                <input name="pr_item_id[]" type="hidden" value="{{ $val->pr_item_id }}">
                                                <input name="price_lpb_item[]" type="hidden" value="{{ $val->price }}">
                                                {{-- <input name="price_discount_lpb_item[]" type="hidden" value="{{ $val->price_discount }}"> --}}
                                                {{-- <input name="ppn_lpb_item[]" type="hidden" value="{{ $val->ppnPO }}">
                                                <input name="discount_type_lpb_item[]" type="hidden" value="{{ $val->discount_type }}">
                                                <input name="discount_lpb_item[]" type="hidden" value="{{ $val->discountPO }}"> --}}
                                                <td class="text-center">{{ $no }}</td>
                                                <td>
                                                    {{ $val->productCode }} - {{ $val->product }} <br><small>PN/SPEC: {{ $val->productPartNumber }} 
                                                         |
                                                         Brand: {{ $val->productBrand }}</small>
                                                    <input name="product_id[]" type="hidden" value="{{ $val->product_id }}">
                                                </td>
                                                <td>{!! $val->specification !!}</td>
                                                <td>
                                                    {{ $val->qty }} {{ $val->measure }}
                                                    <input  type="hidden" value="{{ $val->qty }}" class="form-control qty_asli" id="qty_lpb_{{ $val->id }}" >
                                                </td>
                                                <td>
                                                    <input type="number" class="form-control qty_input qty_spb_{{ $val->id }}" name="qty_spb[]"  id="qty_spb_{{ $val->id }}" required min="1" max="{{$val->qty}}" oninput="this.value = Math.abs(this.value)" onwheel="return false;"/>
                                                    <input type="hidden" class="form-control price_spb_{{ $val->id }}" name="price_spb[]"  id="price_spb_{{ $val->id }}" value="{{$val->price}}"/>
                                                </td>
                                                <td><textarea name="annotation[]" class="form-control"  placeholder="Cth. Nomor Koli, Berat Packing"></textarea></td>
                                            </tr>
                                            @php
                                                $no++
                                            @endphp
                                        @endforeach
                                </tbody>
                            </table>
                        </td>
                    </tr>
                    </tbody>
                </table>
            @endforeach
        @endif

    </div>
    <div class="mt-4">
        <a href="{{ route('logistic.spb.index') }}" class="btn btn-light mr-1 text-uppercase fsz-sm fw-600">{{ trans('Cancel') }}</a>
        <input class="btn btn-info text-uppercase fsz-sm fw-600" type="submit"  name="save" value="Save as Draft" id="btnDraft">
        <input type="hidden" value="0" name="status">
        <input class="btn btn-danger text-uppercase fsz-sm fw-600 float-right" type="submit" name="publish" id="btnSubmit" value="Publish SPB">
    </div>
    {!! Form::close() !!}

@stop


@section('js')

<script  type='text/javascript'>


   
	$(document).ready(function() {


        $('#checker').select2({
            placeholder: "Silahkan pilih...",
        }).on('change', function() {
            $.ajax({
                url: "{{ route('master.get_operator') }}/"+$('#checker').val(),
                method: "GET",
                dataType: 'json',
                success:function(data){
                    $('#checker_name').val(data.name);
                    $('#checker_sign').val(data.sign);
                }
            });
            $(this).valid();
        });


        $('#operator').select2({
            placeholder: "Silahkan pilih..."
        }).on('change', function() {
            $.ajax({
                url: "{{ route('master.get_operator') }}/"+$('#operator').val(),
                method: "GET",
                dataType: 'json',
                success:function(data){
                    $('#operator_name').val(data.name);
                    $('#operator_sign').val(data.sign);
                }
            });
            $(this).valid();
        });


        $('[id^="qty_spb_"]').change(function() {
            var id = $(this).attr('id').replace('qty_spb_', '');
            var qty_input = $(this).val();
            var qty_asli = $("#qty_lpb_" + id).val();
            if (parseInt(qty_input) > parseInt(qty_asli)) {
                Swal.fire(
                    'Informasi',
                    'QTY Item SPB Tidak Boleh Melebihi Qty LPB',
                    'warning'
                );
                $(this).val(0);
            }
        });


        $('#expedition').select2({
            placeholder: "Pilih Ekspedisi.."
        }).on('change', function() {
            $.ajax({
                url: "{{ route('master.get_expedition') }}/"+$('#expedition').val(),
                method: "GET",
                dataType: 'json',
                success:function(data){
                    $('#delivered_pic').val(data.pic);
                    $('#delivered_pic_telp').val(data.telp);
                }
            });
        });

        $('#is_handcarry').select2({
            placeholder: "Pilih Hand Carry.."
        }).on('change', function() {
            $.ajax({
                url: "{{ route('master.get_handcarry') }}/"+$('#is_handcarry').val(),
                method: "GET",
                dataType: 'json',
                success:function(data){
                    $('#delivered_pic').val(data.pic);
                    $('#delivered_pic_telp').val(data.telp);
                }
            });
        });

        $('#form-spb').validate({
            onfocusout: false,
            invalidHandler: function(form, validator) {
                var errors = validator.numberOfInvalids();
                if (errors) {
                    validator.errorList[0].element.focus();
                }
            }
        });

        $("#is_qty").change(function(){
            if (this.checked) {
                $('#is_qty').prop('checked', true);
                <?php foreach ($lpb as $item) {
                    $lpbitem = getLPBItem($item->id);
                    foreach ($lpbitem as $val){ ?>
                        var qty  = $("#qty_lpb_{{$val->id}}").val();
                        $("#qty_spb_{{$val->id}}").val(qty);
                    <?php }
                } ?>
            } else {
                $("#is_qty").val(0);
                <?php foreach ($lpb as $item) {
                    $lpbitem = getLPBItem($item->id);
                    foreach ($lpbitem as $val){ ?>
                         $("#qty_spb_{{$val->id}}").val('');
                    <?php }
                } ?>
            }
        });

        $('[id^="qty_spb_"]').change(function() {
            var id = $(this).attr('id').replace('qty_spb_', '');
            var qty_input = $(this).val();
            var qty_asli = $("#qty_lpb_" + id).val();
            if (parseInt(qty_input) > parseInt(qty_asli)) {
                Swal.fire(
                    'Informasi',
                    'QTY Item SPB Tidak Boleh Melebihi Qty LPB',
                    'warning'
                );
                $(this).val(0);
            }
        });

        $(document).on('click', "#btnSubmit", function(e) {
            $('input[name="status"]').val('1');
            var _this = $(this);
            var form = _this.parents('form');

            var validform = true;
        

            e.preventDefault();
            if (form.valid() && validform === true) {
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


        $(document).on('click', "#btnDraft", function(e) {
            $('input[name="status"]').val('0');
            var _this = $(this);
            var form = _this.parents('form');

            var validform = true;


            e.preventDefault();
            if (form.valid() && validform === true) {
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

        $('#pickup_2').hide();
        $('#cargo_is_handcarry').hide();
        $('#type').select2({
            placeholder: "Silahkan Pilih Tipe SPB...",
            allowClear: true
        }).on('change', function() {
            if($('#type').val() =='SPB Vendor' || $('#type').val() === 'SPB Vendor-Cargo'){
                var arr = [];
                $('.supplierID').each(function(){
                    var value = $(this).val();
                    arr.push(value);
                });
                const allEqual = arr => arr.every( v => v === arr[0] )
                if (allEqual(arr)){
                    console.log('TRUE');
                }else{
                    Swal.fire(
                        'Informasi',
                        'Tidak dapat mengajukan SPB dengan Tipe SPB Vendor dan SPB Vendor-Cargo dikarenakan Terdapat Supplier yang berbeda, silahkan pilih tipe SPB Cargo/Hand Carry atau kembali ke Menu list LPB.',
                        'warning'
                    );
                    $('#type').select2("val", "SPB Cargo");
                }
            }

            if($('#type').val() =='SPB Cargo' || $('#type').val() === 'SPB Vendor-Cargo'){
                $('#cargo').show();
                $('#cargo_is_handcarry').hide();
            }else if($('#type').val() === 'SPB Hand Carry'){
                $('#cargo').hide();
                $('#cargo_is_handcarry').show();
            }
            else if($('#type').val() =='SPB Pick Up'){
                $('#cargo').hide();
                $('#cargo_is_handcarry').hide();
            }
            else{
                $('#cargo').hide();
                $('#cargo_is_handcarry').hide();
            }
        });

        $('#pickuppp').select2({
            placeholder: "Silahkan Pilih Tipe PickUp...",
            allowClear: true
        }).on('change', function() {
            if($('#pickuppp').val() == 'Pick Up'){
                $('#pickup_2').show();
            }
            else{
                $('#pickup_2').hide();
            }
        });

    });
    </script>
@stop
