@extends('layouts.app')

@section('page-header')
    Surat Pengantar Barang (SPB)
@stop

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('logistic.spb.index') }}">Surat Pengantar Barang</a></li>
        <li class="breadcrumb-item active" aria-current="page">Edit</li>
    </ol>
@endsection


@section('content')

<div class="mB-40">
		{!! Form::model($spb, [
				'action' => ['Logistic\SpbController@update', $spb->id],
				'method' => 'put', 
				'class' => 'form-horizontal mt-3',
				'id' => 'form-spb'
			])
		!!}

            <div class="bgc-white p-30 bd">

            <h6><a class="float-left" href="{{ route('logistic.spb.index') }}"><i class="ti-arrow-left mR-10"></i></a> {{ trans('Back') }}</h6>
            <hr class='mB-30'>
            
            @include('logistic.spb.form')

            <div class="alert alert-danger mT-3 d-none" id="priceAlert">   
                <span style="color:red">* </span>Harga lebih dari 250 juta
            </div>
            <div class="alert alert-warning mT-3">
                - Daftar Laporan Penerimaan Barang (LPB) yang akan dibuatkan Surat Pengantar Barang. <br> 
                - Silahkan hapus LPB terlebih dahalu sebelum melakukan update data
            </div>

            <table class="table table-bordered mt-2">
                <tbody class="item_form">
                    @if (count($lpb) > 0)
                        @php $no = 1  @endphp
                        @foreach ($lpb as $item)
                                <input name="lpb_spb_id[]" type="hidden" value="{{ $item->id }}">
                                <input type="hidden" name="supplierID" value="{{ $item->supplierID }}" class="supplierID">

                                <tr>
                                    <td class="border-0 bg-light"> Nomor LPB <br><span class="font-weight-bold">{{ $item->doc_no }} </span></td>
                                    <td class="border-0 bg-light"> Nomor PO <br><span class="font-weight-bold">{{ $item->po_no }} </span></td>
                                    <td class="border-0 bg-light"> Supplier <br><span class="font-weight-bold">{{ $item->supplier }}  </span></td>
                                    <td class="border-0 bg-light">
                                        <a href="{{ route('logistic.spb.remove_lpb', ['spb_id' => $spb->id, 'lpb_id' => $item->id] ) }}" class="btn btn-danger pull-right"><i class="ti-trash icon-lg"></i></a>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="4">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th style="width:50px">No</th>
                                                    <th>Nama Barang</th>
                                                    <th>Spesifikasi</th>
                                                    <th class="text-center" style="width:150px">QTY</th>
                                                    <th class="text-center" style="width:150px">QTY SPB</th>
                                                    <th class="text-center" style="width:200px">Notes</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                    @php
                                                        $no = 1;
                                                        $spb_items = getItemSPB($item->id);
                                                    @endphp
                                                    @foreach ($spb_items as $val)
                                                        <tr>
                                                            <input name="lpb_item_id[]" type="hidden" value="{{ $val->spb_item_id }}">
                                                            <input name="pr_item_id[]" type="hidden" value="{{ $val->pr_item_id }}">
                                                            <input type="hidden" value="{{ $val->price }}" id="price_{{$val->spb_item_id}}">
                                                            <input type="hidden" value="{{ $val->conversion_idr }}" id="conversion_{{$val->spb_item_id}}">
                                                            <td style="vertical-align:top !important">{{ $no }}</td>
                                                            <td style="vertical-align:top !important">
                                                                {{ $val->productCode }} - {{ $val->product }} <br><small>PN/SPEC: {{ $val->productPartNumber }} | Brand: {{ $val->productBrand }}</small>
                                                                <input name="product_id[]" type="hidden" value="{{ $val->id }}">
                                                            </td>
                                                            <td style="vertical-align:top !important">{!! $val->specification !!}</td>
                                                            <td style="vertical-align:top !important"  class="text-center" >
                                                                {{ $val->qtyLpb }} {{ $val->measure }}
                                                            </td>
                                                            <td>
                                                                <input type="hidden" value="{{ $val->qtyLpb }}" class="form-control" id="qty_lpb_{{ $val->spb_item_id }}" >
                                                                <input type="number" name="qty_spb[]" class="form-control" value="{{ $val->qty }}" id="qty_spb_{{ $val->spb_item_id }}" required  min="1" oninput="this.value = Math.abs(this.value)" onwheel="return false;"> 
                                                            </td>
                                                            <td style="vertical-align:top !important"><textarea name="annotation[]" class="form-control">{{ $val->annotation }}</textarea></td>
                                                        </tr>
                                                      
                                                    @php
                                                        $no++
                                                    @endphp
                                                    @endforeach
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                            @php $no++ @endphp
                        @endforeach
                    @endif
                </tbody>
            </table>
            <input id="valPic" type="hidden" value="{{$spb->is_pickup}}">
            <input id="valDeliveredBy" type="hidden" value="{{$spb->delivered_by}}">
		</div>
        

        <div class="mt-4">
            <a href="{{ route('logistic.spb.index') }}" class="btn btn-light mr-1 text-uppercase fsz-sm fw-600">{{ trans('Cancel') }}</a>
            <!-- <input class="btn btn-info text-uppercase fsz-sm fw-600" type="submit" name="save" value="Save as Draft" id="btn-draft"> -->
            @if($spb->status==0)
                <input type="hidden" value="0" name="status">
                <input class="btn btn-danger text-uppercase fsz-sm fw-600 float-right"  type="submit" name="publish" id="btn-submit" value="Publish SPB">
            @endif
        </div>

	{!! Form::close() !!}
</div>
	
@stop


@section('js')

<script  type='text/javascript'>
	$(document).ready(function() {
        
        $(document).on("click", "#btn-submit", function(e) {
            $('input[name="status"]').val('1');
        });

        $(document).on("click", "#btn-draft", function(e) {
            $('input[name="status"]').val('1');
        });

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


        $('#expedition').select2({
            placeholder: "Silahkan Ekspedisi..."
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

        $('#pickup_2').hide();
        $('#cargo_is_handcarry').hide();
        if($('#valPic').val() == true){
            $('#pickuppp').val('Pick Up').trigger('change');
            $('#pickup_2').show();
        };
        if($('#type').val() == 'SPB Hand Carry'){
            var delBy = $('#valDeliveredBy').val();
            $('#is_handcarry').val(delBy).trigger('change');
            $('#cargo_is_handcarry').show();
        };
        
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

        }).trigger('change');

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

        $('[id^="qty_spb_"]').change(function() {
            // Recalculate totalPayment for all items
            let totalPayment = 0;

            $('[id^="qty_spb_"]').each(function() {
                let id = $(this).attr('id').replace('qty_spb_', '');
                let price = parseFloat($(`#price_${id}`).val()) || 0;
                let conversion = parseFloat($(`#conversion_${id}`).val());
                let quantity = parseFloat($(this).val()) || 1;

                totalPayment += price * conversion * quantity;
            });

            // Show or hide the price alert based on totalPayment
            if (totalPayment >= 250000000) {
                if (priceAlert.classList.contains('d-none')) {
                    priceAlert.classList.remove('d-none');
                }
            } else {
                if (!priceAlert.classList.contains('d-none')) {
                    priceAlert.classList.add('d-none');
                }
            }
        });

        $('[id^="qty_spb_"]').trigger('change');

    });
    </script>
@stop

