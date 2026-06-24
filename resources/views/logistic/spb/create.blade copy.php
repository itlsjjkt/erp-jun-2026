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
<div class="bgc-white p-30 bd">
    
        <h6><a class="float-left" href="{{ route('logistic.spb.index') }}"><i class="ti-arrow-left mR-10"></i></a> {{ trans('Back') }}</h6>
        <hr class='mB-30'>
        <div class="grid gap-3 mB-20">
            <label for="">
                Banyak Dokumen yang dibutuhkan : 
            </label>
            <input type="number" min="1" id="spbNumber" style="width: 3em;" value="1">
            <button id="spbButton" class="btn btn-primary">Submit</button>
        </div>
        {!! Form::open(['method' => 'POST', 'route' => ['logistic.spb.store'], 'id' => 'form-spb']) !!}
        <!-- <input type="hidden" name="lpbID" value="{{ $lpb_id }}"> -->
        <div id="spbDocument">
        </div>
        <div class="alert alert-danger mT-3 d-none"  id="priceAlert">   
            <span style="color:red">* </span>Harga lebih dari 250 juta
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
                        <!-- <input name="lpb_id[]" type="hidden" value="{{ $item->id }}"> -->
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
                                                <td class="text-center"><input type="checkbox" name="selected_items[]" value="{{ $val->id }}" onchange="checkbox($(this), '{{ $val->id }}')" id="checkbox_{{$val->id}}"></td>
                                                <input name="lpb_id[{{ $val->id }}]" type="hidden" value="{{ $item->id }}">
                                                <input name="lpb_spb_id[{{ $val->id }}]" type="hidden" value="{{ $item->id }}">
                                                <input name="location_id[{{ $val->id }}]" type="hidden" value="{{ $val->location_id }}">
                                                <input name="lpb_item_id[{{ $val->id }}]" type="hidden" value="{{ $val->id }}">
                                                <input name="pr_item_id[{{ $val->id }}]" type="hidden" value="{{ $val->pr_item_id }}">
                                                <input name="price_lpb_item[{{ $val->id }}]" type="hidden" value="{{ $val->price }}" id="price_lpb_item{{ $val->id }}">
                                                {{-- <input name="price_discount_lpb_item[{{ $val->id }}]" type="hidden" value="{{ $val->price_discount }}"> --}}
                                                {{-- <input name="ppn_lpb_item[{{ $val->id }}]" type="hidden" value="{{ $val->ppnPO }}">
                                                <input name="discount_type_lpb_item[{{ $val->id }}]" type="hidden" value="{{ $val->discount_type }}">
                                                <input name="discount_lpb_item[{{ $val->id }}]" type="hidden" value="{{ $val->discountPO }}"> --}}
                                                <td>
                                                    {{ $val->productCode }} - {{ $val->product }} <br><small>PN/SPEC: {{ $val->productPartNumber }} 
                                                         |
                                                         Brand: {{ $val->productBrand }}</small>
                                                    <input name="product_id[{{ $val->id }}]" type="hidden" value="{{ $val->product_id }}">
                                                </td>
                                                <td>{!! $val->specification !!}</td>
                                                <td>
                                                    {{ $val->qty }} {{ $val->measure }}
                                                    <input  type="hidden" value="{{ $val->qty }}" class="form-control qty_asli" id="qty_lpb_{{ $val->id }}" >
                                                </td>
                                                <td>
                                                    <input type="number" class="form-control qty_input qty_spb_{{ $val->id }}" name="qty_spb[{{ $val->id }}]"  id="qty_spb_{{ $val->id }}" min="1" max="{{$val->qty}}" oninput="this.value = Math.abs(this.value)" onwheel="return false;"/>
                                                    <input type="hidden" class="form-control price_spb_{{ $val->id }}" name="price_spb[{{ $val->id }}]"  id="price_spb_{{ $val->id }}" value="{{$val->price}}"/>
                                                </td>
                                                <td><textarea name="annotation[{{ $val->id }}]" class="form-control"  placeholder="Cth. Nomor Koli, Berat Packing"></textarea></td>
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
        <div class="mt-4">
            <a href="{{ route('logistic.spb.index') }}" class="btn btn-light mr-1 text-uppercase fsz-sm fw-600">{{ trans('Cancel') }}</a>
            <input class="btn btn-info text-uppercase fsz-sm fw-600" type="submit"  name="save" value="Save as Draft" id="btnDraft">
            <input type="hidden" value="0" name="status">
            <input type="hidden" id="numberOfSpb" name="numberOfSpb">
            <input class="btn btn-danger text-uppercase fsz-sm fw-600 float-right" type="submit" name="publish" id="btnSubmit" value="Publish SPB">
        </div>
        {!! Form::close() !!}
    </div>

@stop


@section('js')

<script  type='text/javascript'>

	$(document).ready(function() {
        window.onload = function() {
            $('#spbButton').click();
        }
        
        let value = $('#spbNumber').val();
        $(document).on('click', "#spbButton", function(e) {
            console.log('clicked');
            
            let content = '';

            for(let i=0; i<value; i++){
                content += `
                    <h5>Dokumen SPB ke-${i+1}</h5>
                    <div class="" style="border:1px double black; width:100%;" > </div>  
                    <div class="row mt-4" >
                        <div class="col-lg-6">
                            <div class="form-group row">
                                <label class="col-sm-3 mt-2">Tanggal SPB <span class="text-danger">*</span></label>
                                <div class="col-sm-8">
                                    {!! Form::date('date_transaction[${i}]', old('date_transaction_${i}'), ['class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}
                                    <p class="help-block"></p>
                                    @if($errors->has('date_transaction_${i}'))
                                        <p class="help-block">
                                            {{ $errors->first('date_transaction_${i}') }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 mt-2">Estimasi Tiba <span class="text-danger">*</span></label>
                                <div class="col-sm-8">
                                    {!! Form::date('estimate_receives[${i}]', old('estimate_receives_${i}'), ['class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}
                                    <p class="help-block"></p>
                                    @if($errors->has('estimate_receives_${i}'))
                                        <p class="help-block">
                                            {{ $errors->first('estimate_receives_${i}') }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3">Jenis SPB <span class="text-danger">*</span></label>
                                <div class="col-sm-4">
                                    {!! Form::select('type[${i}]', $type, old('type_${i}'), ['class' => 'form-control select2','id'=>'type_${i}', 'required' => '']) !!}
                                </div>
                                <div class="col-sm-4" id="cargo_${i}">
                                    {!! Form::select('delivered_by[${i}]', $ekspedisi, old('delivered_by_${i}'), ['class' => 'form-control select2','id' => 'expedition_${i}']) !!}
                                </div>
                                <div class="col-sm-4" id="cargo_is_handcarry_${i}">
                                    {!! Form::select('delivered_by2[${i}]', $handcarry, old('delivered_by2_${i}'), ['class' => 'form-control select2','id' => 'is_handcarry_${i}','required' => '']) !!}
                                </div>
                            </div>
                            <input type="hidden" value="" id="delivered_pic_${i}" name="delivered_pic[${i}]">
                            <input type="hidden" value="" id="delivered_pic_telp_${i}" name="delivered_pic_telp[${i}]">
                        
                            <div class="form-group row mb-4">
                                <label class="col-sm-3 mt-2"> Jalur Pengiriman</label>
                                <div class="col-sm-8">
                                    {!! Form::select('jalur_pengiriman[${i}]', $jalur, old('jalur_pengiriman_${i}'), ['class' => 'form-control select2','id'=>'jalur_pengiriman_${i}']) !!}
                                </div>
                            </div>
                            <div class="form-group row mb-4">
                                <label class="col-sm-3 mt-2"> Operator<span class="text-danger"> *</span></label>
                                <div class="col-sm-8">
                                    {!! Form::select('operator[${i}]', $operator, old('operator_${i}'), ['class' => 'form-control select2', 'required' => '','id' =>'operator_${i}']) !!}
                                </div>
                            </div>
                            <div class="form-group row mb-4">
                                <label class="col-sm-3 mt-2">Checker<span class="text-danger"> *</span></label>
                                <div class="col-sm-8">
                                    {!! Form::select('checker[${i}]', $operator, old('checker_${i}'), ['class' => 'form-control select2', 'required' => '','id' =>'checker_${i}']) !!}
                                </div>
                            </div>
                        
                            <div class="form-group row mb-4">
                                <label class="col-sm-3 mt-2"> Cost SPB<span class="text-danger"> *</span></label>
                                <div class="col-sm-8">
                                    {!! Form::select('company_id[${i}]', $company, old('company_id_${i}'), ['class' => 'form-control select2', 'required' => '') !!}
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="form-group row" id="pickup_${i}">
                                <label class="col-sm-3 mt-2">Pengiriman</label>
                                <div class="col-sm-8">
                                    {!! Form::select('is_pickup[${i}]', $pickup, old('is_pickup_${i}'), ['class' => 'form-control select2','id' => 'pickuppp_${i}','required' => '']) !!}
                                </div>
                            </div>
                            <div class="form-group row mt-2" id="pickup_2_${i}">
                                <label class="col-sm-3 mt-1"> Lokasi Pick Up<span class="text-danger"> *</span></label>
                                <div class="col-sm-8">
                                    {!! Form::text('pickup_from[${i}]', old('pickup_from_${i}'), ['class' => 'form-control', 'placeholder' => 'Pick Up di..[contoh: PT...]','required' => '']) !!}
                                </div>
                                <label class="col-sm-3 mt-3"> Alamat Pick Up</label>
                                <div class="col-sm-8 mt-3">
                                    {!! Form::textarea('pickup_address[${i}]', old('pickup_address_${i}'), ['class' => 'form-control', 'placeholder' => '', 'style'=>'height:50px;','required' => '']) !!}
                                </div>
                                <label class="col-sm-3 mt-3"> PIC Lokasi Pick Up</label>
                                <div class="col-sm-8 mt-3">
                                    <div class="row">
                                        <div class="col-6">
                                            {!! Form::text('pickup_pic_name[${i}]', old('pickup_pic_name_${i}'), ['class' => 'form-control', 'placeholder' => 'Nama','required' => '']) !!}
                                        </div>
                                        <div class="col-6">
                                            {!! Form::number('pickup_pic_telp[${i}]', old('pickup_pic_telp_${i}'), ['class' => 'form-control', 'placeholder' => 'No Telpon','required' => '']) !!}
                                        </div>                
                                    </div>
                                </div>
                                <br><br><br><br>
                            </div>
                        
                            <div class="form-group row">
                                <label class="col-sm-3 mt-2">Nama Penerima <span class="text-danger">*</span></label>
                                <div class="col-sm-8">
                                    {!! Form::text('received_pic[${i}]', old('received_pic_${i}'), ['class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}
                                </div>
                            </div>
                        
                            <div class="form-group row">
                                <label class="col-sm-3 mt-2">Telp Penerima <span class="text-danger">*</span></label>
                                <div class="col-sm-8">
                                    {!! Form::text('received_pic_telp[${i}]', old('received_pic_telp_${i}'), ['class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}
                                </div>
                            </div>
                            <div class="form-group row mb-4">
                                <label class="col-sm-3 mt-2">Alamat Pengiriman</label>
                                <input id="alamatTujuannn_${i}" type="hidden" name="address[${i}]" class="form-control" value="" rows="2">
                                <div class="col-sm-8">
                                    <trix-editor input="alamatTujuannn"></trix-editor>
                                </div>
                            </div>
                        
                            <div class="form-group row mb-4">
                                <label class="col-sm-3 mt-2">Catatan </label>
                                <input id="note____${i}" type="hidden" name="notes[${i}]" class="form-control" value="" rows="2">
                                <div class="col-sm-8">
                                    <trix-editor input="note____${i}"></trix-editor>
                                </div>
                            </div>
                        </div>
                    </div>
                `
            }
            $('#spbDocument').html(content);
            
            for (let i = 0; i < value; i++) {
                $(`#checker_${i}`).select2({
                    placeholder: "Pilih Checker..."
                }).on('change', function () {
                    const checkerId = $(this).val();
                    $.ajax({
                        url: `{{ route('master.get_operator') }}/${checkerId}`,
                        method: "GET",
                        dataType: 'json',
                        success: function (data) {
                            // Handle the data for the checker
                        }
                    });
                });

                $(`#operator_${i}`).select2({
                    placeholder: "Pilih Operator..."
                }).on('change', function () {
                    const operatorId = $(this).val();
                    $.ajax({
                        url: `{{ route('master.get_operator') }}/${operatorId}`,
                        method: "GET",
                        dataType: 'json',
                        success: function (data) {
                            // Handle the data for the operator
                        }
                    });
                });

                $(`#expedition_${i}`).select2({
                    placeholder: "Pilih Ekspedisi..."
                }).on('change', function () {
                    const expeditionId = $(this).val();
                    $.ajax({
                        url: `{{ route('master.get_expedition') }}/${expeditionId}`,
                        method: "GET",
                        dataType: 'json',
                        success: function (data) {
                            // Handle the data for the expedition
                            console.log(data.telp);
                            
                            $(`#delivered_pic_${i}`).val(data.pic);
                            $(`#delivered_pic_telp_${i}`).val(data.telp);
                        }
                    });
                });

                $(`#handcarry_${i}`).select2({
                    placeholder: "Pilih Hand Carry..."
                }).on('change', function () {
                    const handcarryId = $(this).val();
                    $.ajax({
                        url: `{{ route('master.get_handcarry') }}/${handcarryId}`,
                        method: "GET",
                        dataType: 'json',
                        success: function (data) {
                            // Handle the data for the hand carry
                            $(`#delivered_pic_${i}`).val(data.pic);
                            $(`#delivered_pic_telp_${i}`).val(data.telp);
                        }
                    });
                });

                $(`#pickup_2_${i}`).hide();
                $(`#cargo_is_handcarry_${i}`).hide();
                $(`#type_${i}`).select2({
                    placeholder: "Silahkan Pilih Tipe SPB...",
                    allowClear: true
                }).on('change', function() {
                    if($(`#type_${i}`).val() =='SPB Vendor' || $(`#type_${i}`).val() === 'SPB Vendor-Cargo'){
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
                            $(`#type_${i}`).select2("val", "SPB Cargo");
                        }
                    }

                    if($(`#type_${i}`).val() =='SPB Cargo' || $(`#type_${i}`).val() === 'SPB Vendor-Cargo'){
                        $(`#cargo_${i}`).show();
                        $(`#cargo_is_handcarry_${i}`).hide();
                    }else if($(`#type_${i}`).val() === 'SPB Hand Carry'){
                        $(`#cargo_${i}`).hide();
                        $(`#cargo_is_handcarry_${i}`).show();
                    }
                    else if($(`#type_${i}`).val() =='SPB Pick Up'){
                        $(`#cargo_${i}`).hide();
                        $(`#cargo_is_handcarry_${i}`).hide();
                    }
                    else{
                        $(`#cargo_${i}`).hide();
                        $(`#cargo_is_handcarry_${i}`).hide();
                    }
                });

                $(`#pickuppp_${i}`).select2({
                    placeholder: "Silahkan Pilih Tipe PickUp...",
                    allowClear: true
                }).on('change', function() {
                    if($(`#pickuppp_${i}`).val() == 'Pick Up'){
                        $(`#pickup_2_${i}`).show();
                    }
                    else{
                        $(`#pickup_2_${i}`).hide();
                    }
                });
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
            $('#numberOfSpb').val(value);
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

        var checkedItems = [];
        let totalPayment = 0;
        let priceAlert = document.getElementById("priceAlert")

        window.checkbox = function (element, lpbId) {
            let price = $(`#price_lpb_item${lpbId}`).val();
            let quantity = $(`#qty_spb_${lpbId}`).val();
            let quantityElement = document.getElementById(`qty_spb_${lpbId}`);
            let total = price * quantity;

            if (element.prop('checked')) {
                console.log(quantityElement);
                
                quantityElement.setAttribute('required', true);
                totalPayment += total;
                checkedItems.push(lpbId); // Menambahkan ID item yang di-check ke dalamay
            } else {
                quantityElement.removeAttribute('required');
                totalPayment -= total;
                // Menghapus ID item yang di-uncheck dari array
                checkedItems = checkedItems.filter(item => item !== lpbId);
            }
            console.log(totalPayment);  

            if(totalPayment >= 250000000){
                if(priceAlert.classList.contains('d-none')){
                    priceAlert.classList.remove('d-none')
                }
            } else {
                if(!priceAlert.classList.contains('d-none')){
                    priceAlert.classList.add('d-none')
                }
            }
            
        }

        $('[id^="qty_spb_"]').change(function() {
            let id = $(this).attr('id').replace('qty_spb_', '');
            let price = parseFloat($(`#price_lpb_item${id}`).val());
            let quantity = parseFloat($(this).val());
            let total = price * quantity;

            // Periksa apakah item sudah di-check
            totalPayment = checkedItems.reduce((acc, id) => {
                let itemPrice = parseFloat($(`#price_lpb_item${id}`).val()) || 0;
                let itemQuantity = parseFloat($(`#qty_spb_${id}`).val()) || 1;
                return acc + (itemPrice * itemQuantity);
            }, 0);
            console.log(totalPayment);   

            if(totalPayment >= 250000000){
                if(priceAlert.classList.contains('d-none')){
                    priceAlert.classList.remove('d-none')
                }
            } else {
                if(!priceAlert.classList.contains('d-none')){
                    priceAlert.classList.add('d-none')
                }
            }
        });        
    });
    </script>
@stop
