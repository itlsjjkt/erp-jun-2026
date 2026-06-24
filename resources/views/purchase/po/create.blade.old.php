@extends('layouts.app')

@section('page-header')
    Purchase Requisition
@endsection

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('purchasing.pr') }}">Purchase Requisition</a></li>
        <li class="breadcrumb-item active" aria-current="page">Pembuatan PO</li>
    </ol>
@endsection


@section('content')
    {!! Form::open(['method' => 'POST', 'route' => ['purchasing.po.store'], 'id' => 'form-pr']) !!}
    <input name="purchase_id" type="hidden" value="{{ $pr->id }}">
    <input name="location_id" type="hidden" value="{{ $pr->location_id }}">
    <input name="company_id" type="hidden" value="{{ $pr->company_id }}">
    <input name="company_code" type="hidden" value="{{ $pr->company_code }}">
	<div class="bgc-white p-30 bd">
        <h6>{{ $pr->doc_no }}</h6>
        <hr class='mB-30'>

        <div class="row">

            <div class="col-sm-6">
                <div class="form-group row">
                    <label class="col-sm-3">Nama Supplier <span class="text-danger">*</span></label>
                    <div class="col-sm-8">
                        <select name="supplier_id" class="select2 form-control supplier" required></select>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-3">PIC Supplier <span class="text-danger">*</span></label>
                    <div class="col-sm-8">
                        <select name="supplier_contact_id" class="select2 form-control supplier_pic" required ></select>
                    </div>
                </div>

                <div class="form-group row" id="supplierData" style="display:none">
                    <label class="col-sm-3">Payment Term<span class="text-danger">*</span></label>
                    <div class="col-sm-6">
                        <select name="payment_term_id" class="select2 form-control payment_term" required ></select>
                    </div>
                    <div class="col-sm-2">
                        <input type="checkbox" name="ppn" id="ppn" class="magic-checkbox" value="0"><label for="ppn"> PPN </label>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-3">Jatuh Tempo Pembayaran <span class="text-danger">*</span></label>
                    <div class="col-sm-4">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="ti-calendar"></i></span>
                            </div>
                            {!! Form::text('due_date_payment', old('due_date_payment'), ['class' => 'form-control datepicker due_date_payment', 'placeholder' => '', 'required' => '']) !!}
                        </div>
                    </div>
                    <div class="col-sm-4">
                        {!! Form::select('payment_method', $payment_method, old('payment_method'), ['class' => 'form-control select2 payment_method', 'required' => '']) !!}
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-3">Price Term <span class="text-danger">*</span></label>
                    <div class="col-sm-4">
                            {!! Form::select('price_term', $price_term, old('price_term'), ['class' => 'form-control select2 price_term', 'required' => '']) !!}
                    </div>
                    <div class="col-sm-4">
                        {!! Form::select('price_term_location', $price_term_location, old('price_term_location'), ['class' => 'form-control select2 price_term_location', 'required' => '']) !!}
                    </div>
                </div>
                
                <div class="form-group row">
                    <label class="col-sm-3">Tgl & Biaya Pengiriman </label>
                    <div class="col-sm-4">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="ti-calendar"></i></span>
                            </div>
                            {!! Form::text('delivery_date', old('delivery_date'), ['class' => 'form-control datepicker delivery_date', 'placeholder' => '']) !!}
                        </div>
                    </div>
                    <div class="col-sm-4">
                        {!! Form::text('send_expense', 0, ['class' => 'form-control currency text-right mb-2', 'placeholder' => 'Biaya']) !!}
                        <input type="checkbox" name="send_expense_ppn" id="send_expense_ppn" class="switch switch-info mt-2" value="0"> <label for="send_expense_ppn">PPN</label>
                    </div>
                </div>

            </div>

            <div class="col-sm-6">

                <div class="form-group row">
                    <label class="col-sm-3 col-form-label text-right">Mata Uang<span class="text-danger">*</span></label>
                    <div class="col-sm-4">
                        {!! Form::select('currency', $currency, null , ['class' => 'form-control select2 po_currency', 'required' => '','id' => 'currency']) !!}
                    </div>
                    <div class="col-sm-4">
                        <div class="input-group">
                            {!! Form::number('pph', old('pph'), ['class' => 'form-control', 'placeholder' => 'PPH', 'value' => 0,'min' => '0','step' => '0.01']) !!}
                            <div class="input-group-prepend">
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                    </div>
                </div>

				<div class="form-group row">
						<label class="col-sm-3 col-form-label text-right">Kontak PIC</label>
						<div class="col-sm-8">
							{!! Form::select('po_note', $po_note, old('po_note'), ['class' => 'form-control select2 po_note', 'required' => '']) !!}
							<textarea class="form-control notes" rows="5" readonly>{!! old('notes') !!}</textarea>
						</div>
					</div>                
					<div class="form-group row">
						<label class="col-sm-3 col-form-label text-right">Catatan </label>
						<div class="col-sm-8">
							<input id="notes" type="hidden" name="notes" value="" class="form-control">
							<trix-editor style="max-width: 300px;" input="notes"></trix-editor>
						</div>
				</div>

                <div class="form-group row">
                    <label class="col-sm-3 col-form-label text-right">Bahasa</label>
                    <div class="col-sm-4">
                        <div class="onoffswitch">
                            <input type="checkbox" class="onoffswitch-checkbox" id="myonoffswitch" checked>
                            <label class="onoffswitch-label" for="myonoffswitch">
                                <span class="onoffswitch-inner"></span>
                                <span class="onoffswitch-switch"></span>
                            </label>
                        </div>
                        <input type="hidden" name="po_term_id" id="po_term_id" value="1">
                    </div>
                </div>

             

            </div>
        </div>

        <hr class='mB-30'>

            <div class="form-group row">
                <label class="col-sm-2">Discount </label>
                <div class="col-sm-2">
                    {!! Form::select('discount_type', $discount_type, old('discount_type'), ['class' => 'form-control select2','id' => 'discount_type']) !!}
                </div>
                <div class="col-sm-2">
                    {!! Form::text('discount_amount', 0, ['class' => 'form-control currency text-right', 'placeholder' => '','id' => 'discountPO']) !!}
                </div>
               
                <div class="col-sm-3" id="discount_item_wrapper">
                    <input type="checkbox" name="discount_item" id="discount_item" class="switch switch-info" value="0"> <label for="discount_item">Diskon Untuk Semua Item</label>
                </div>
            </div>

        
        <hr>
        <div class="alert alert-info">
           - Checklist Daftar Barang dan Masukan harga pada Item product yang akan diterbitkan Purchase Order (PO) <br>
           - Jika Satuan Produk tidak sesuai silahkan konfirmasi ke bagain admin Logistik karena berhubungan dengan Inventory Control. <br>
           - Jika ingin membuat PO Parsial (Supplier berbeda) maka masukan jumlah QTY/Checklist Item sesuai dengan ketersediaan barang pada supplier.  <br>
        </div>

        <div class="row mt-4">
            <div class="col-lg-12">
                <div class="float-right">
                    <input type="checkbox" name="is_item_ppn" id="is_item_ppn" class="magic-checkbox" value="0"> 
                    <label for="is_item_ppn">Checklist jika harga satuan include PPN</label>
                </div>
                <h6 class='mT-10'>DAFTAR ITEM</h6>
            </div>
        </div>

        <div class="row mt-4">
            <table class="table table-bordered">
                <thead>
					<tr class="bg-grey-custome">
                        <th style="width:50px">
                            <input class="magic-checkbox" name="checkedAll" id="checkedAll" type="checkbox"> <label for="checkedAll"></label>
                        </th>
                        <th colspan="2">ITEM | SPEC</th>
                        <th style="width:250px">CATATAN</th>
                        <th style="width:100px">QTY</th>
                        <th style="width: 70px">SATUAN</th>
                        <th style="width:150px">HARGA SATUAN</th>
                        <th style="width:80px">DISC (%)</th>
                        <th style="width:200px">TOTAL</th>
					</tr>
                </thead>
                <tbody class="item_form">
                    @if (count($pr_items) > 0)
                        @php
                            $no = 1
                        @endphp
                        @foreach ($pr_items as $item)
                                <tr>
                                    <input name="pr_item_id[]" type="hidden" value="{{ $item->id }}">
                                    <td>
                                        <input type="checkbox" name="iscreatePO[]"  class="checkSingle magic-checkbox"  value="{{ $item->id }}" id="checkbox_{{ $item->id }}"><label for="checkbox_{{ $item->id }}"></label>
                                    </td>
                                    <td style="width:70px"> 
                                        @if(getDPMLog($item->purchase_id) > 0)
                                            <a href="#" value="{{ action('Logistic\PurchaseRequestController@getPrItemNotes',['id'=>$item->id]) }}" class="icon-lg modalMd" title="Show Data" data-toggle="modal" data-target="#modalMd"><span class="ti-eye text-danger"></span></a>
                                        @else
                                            <a href="#" value="{{ action('Logistic\PurchaseRequestController@getPrItemNotes',['id'=>$item->id]) }}" class="icon-lg modalMd" title="Show Data" data-toggle="modal" data-target="#modalMd"><span class="ti-eye text-muted"></span></a>
                                        @endif
                                        <a href="#" value="{{ action('Purchasing\PoController@getItems',['id'=>$item->product_id]) }}" class="icon-lg modalMdPO ml-1" data-toggle="modal" data-target="#modalHistoryPO"><span class="ti-shopping-cart text-muted"></span></a>
                                    </td>
                                    <td>
                                         {{ $item->product }} 
                                         <br>
                                         {!! $item->productPartNumber != NULL ? 'PN: '.$item->productPartNumber : '' !!}  
                                         {{ $item->productBrand != NULL ? 'Brand: '.$item->productBrand : '' }}
                                        <input name="product_id[]" type="hidden" value="{{ $item->product_id }}"> 
                                    </td>
                                    <td>
									    <input id="specificationzz{{$item->id}}" type="hidden" name="specification[]" value="{{$item->notes}}">
										<div style="width: 250px;">
											<trix-editor input="specificationzz{{$item->id}}"></trix-editor>
										</div>
										{{--<textarea name="specification[]" class="form-control mb-2">{{$item->notes}}</textarea> --}}
                                    </td>
                                    <td>
                                        @if ($item->po_status == 2)
                                            <input type="number" name="qty[]" class="form-control qty text-right" value="{{$item->qty_parsial}}"  min="1" oninput="this.value = Math.abs(this.value)" id="qty_{{ $item->id }}" onwheel="return false;" onkeypress="return event.charCode >= 48">
                                            <input name="qty_pr[]" type="hidden" value="{{$item->qty_parsial}}"  id='qty_po_{{$item->id}}'>
                                        @else
                                            <input type="number" name="qty[]" class="form-control qty text-right" value="{{$item->qty}}"  min="1" oninput="this.value = Math.abs(this.value)" id="qty_{{ $item->id }}" onwheel="return false;" onkeypress="return event.charCode >= 48">
                                            <input name="qty_pr[]" type="hidden" value="{{$item->qty}}"  id='qty_po_{{$item->id}}'>
                                        @endif
                                        <input name="measure_id[]" type="hidden" value="{{ $item->measure }}">
                                    </td>
                                    <td>{{ $item->measure }}</td>
                                    <td><input type="text" name="price[]" class="form-control text-right currency price next_price" value="0"  id="price_{{ $item->id }}" > </td>
                                    <td><input type="number" name="diskon_item[]" class="form-control text-right diskon" value="0" id="discount_{{ $item->id }}" data-id="{{ $item->id }}" min="0" step="0.01" max="100" onwheel="return false;"> </td>
                                    <td><input type="text" readonly class="form-control text-right totalPrice"  id="totalPrice_{{ $item->id }}" value="0"> </td>
                                </tr>
                                @php
                                    $no++
                                @endphp
                        @endforeach
                    @endif
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="8" class="font-bold text-right"><strong>NETTO <br><small>Setelah dikurangi Diskon</small></strong></td>
                        <td colspan="1" class="font-weight-bold text-right"><span id="total" class="font-bold text-right"></span></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        
    </div>
    <div class="mt-4">
        <a href="{{ route('purchasing.po.index') }}" class="btn btn-light text-uppercase fsz-sm fw-600">{{ trans('Cancel') }}</a>
        <input class="btn btn-info text-uppercase fsz-sm fw-600" type="submit" name="save" value="Save as Draft" id="btn-draft-po" >
        <input type="hidden" value="0" name="status">
        <input class="btn btn-danger text-uppercase fsz-sm fw-600" type="submit" name="publish" id="btn-submit-po" value="Publish">
    </div>
    {!! Form::close() !!}


    <div class="modal fade" id="modalMd" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalMdTitle">History Data</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="modalError"></div>
                    <div id="modalMdContent"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalHistoryPO" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalMdTitle">History Purchase Order</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="modalError"></div>
                    <div id="modalMdContentPO"></div>
                </div>
            </div>
        </div>
    </div>

@stop


@section('js')

<script  type='text/javascript'>

    function calculatePrice(id){
        var price     = $("#price_"+id).val().replace(/\,/g,'');
        var diskon = $("#discount_"+id).val();
        var qty    = $("#qty_"+id).val();
        if(diskon == 0) var total = parseFloat(price) * qty ;
        else var total = (parseFloat(price) - (parseFloat(price) * diskon/100))  * qty;
        $("#totalPrice_"+id).val(total.toFixed(2).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ","));
    }

    function calculateSum(){
        var price = $(".totalPrice");
        var discountPO = $("#discountPO").val().replace(/\,/g,'');
        var discount_type = $("#discount_type").val();
        var discount_item = $("#discount_item").val();
        var total = 0 ;

        for(var i=0;i<price.length;i++){
            var vl = price[i].value.replace(/\,/g,'');
            if(!isNaN(vl) && vl.length!=0){
                total +=parseFloat(vl);
            }
        }
        if(discount_type == 0){
            if(discount_item == 0) var totalAll = total-parseInt(discountPO);
            else var totalAll = total;
        }else{
            if(discount_item == 0) var totalAll = total-(total * parseInt(discountPO)/100);
            else var totalAll = total;
        }
        $("#total").html(totalAll.toFixed(2).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ","));
    }


	$(document).ready(function() {

        <?php foreach ($pr_items as $item) { ?>
            $("#price_{{ $item->id }}").keyup(function(){
                var id     = "{{ $item->id }}";
                calculatePrice(id)
                calculateSum();
            });
            $("#qty_{{ $item->id }}").keyup(function(){
                var id     = "{{ $item->id }}";
                calculatePrice(id)
                calculateSum();
            });
            $("#discount_{{ $item->id }}").keyup(function(){
                var id     = "{{ $item->id }}";
                if($(this).val() > 100) {
                    Swal.fire(
                        'Informasi',
                        'Maksimal Discount adalah 100%',
                        'warning'
                    );
                    $(this).val('0')
                }
                calculatePrice(id)
                calculateSum();
            });

        <?php } ?>

        if($('#discount_type').val()=='1'){
            $("#discount_item" ).attr('max', '100');
        }

        $("#discount_type").select2().on('change', function() {
            var discountPO = $("#discountPO").val().replace(/\,/g,'');
            if($(this).val() =='0'){
                $("#discount_item_wrapper").hide();
                $('#discount_item').prop('checked', false).iCheck('update');
                $("#discount_item").attr('value', '0');
                $(".diskon").each(function(){
                    var id = $(this).attr('data-id');
                    $(".diskon").val(0);
                    $(".diskon" ).prop('readonly', false);
                    calculatePrice(id)
                }) 
            }else {
                $("#discount_item_wrapper").show();
                if(discountPO > 100) {
                    Swal.fire(
                        'Informasi',
                        'Maksimal Discount adalah 100%',
                        'warning'
                    );
                    $("#discountPO").val(0);
                }
            }
            calculateSum();
        });

        $('#discount_item').on('ifChecked', function(){
            $("#discount_item" ).attr('value', '1');
            var discountPO = $("#discountPO").val().replace(/\,/g,'');
            $(".diskon").each(function(){
                var id = $(this).attr('data-id');
                $(".diskon").val(discountPO);
                $(".diskon" ).prop('readonly', true);
                calculatePrice(id)
            })  
            calculateSum();
        });

        $('#discount_item').on('ifUnchecked', function(){
            $("#discount_item" ).attr('value', '0');
            $(".diskon").each(function(){
                var id = $(this).attr('data-id');
                $(".diskon").val('0');
                $(".diskon" ).prop('readonly', false);
                calculatePrice(id)
            })
            calculateSum();
        });

        $("#discountPO").keyup(function(){
            var discountPO = $("#discountPO").val().replace(/\,/g,'');
            if($('#discount_item').val()=='1'){
                $(".diskon").each(function(){
                    $(".diskon").val(discountPO);
                    var id = $(this).attr('data-id');
                    calculatePrice(id)
                }) 
            }
            calculateSum();
        });

        $("#discountPO").change(function(){
            if($('#discount_type').val()=='1'){
                var discountPO = $("#discountPO").val().replace(/\,/g,'');
                if(discountPO > 100){
                        Swal.fire(
                            'Informasi',
                            'Maksimal Discount adalah 100%',
                            'warning'
                        );   
                    $("#discountPO").val(0);
                    $(".diskon").each(function(){
                        $(".diskon").val(0);
                    }) 
                }
            }
        });


        $("#is_item_ppn").change(function(){
                
            if (this.checked) {
                if($('#ppn').val()=='1'){
                    Swal.fire(
                        'Informasi',
                        'PPN pada supplier aktif, silahkan gunakan salah satu PPN pada supplier atau PPN pada item harga satuan',
                        'info'
                    );   
                    $('#is_item_ppn').prop('checked', false);
                    $("#is_item_ppn").val(0);
                }else{
                    $('#is_item_ppn').prop('checked', true);
                    $("#is_item_ppn").val(1);
                }
            } else {
                $("#is_item_ppn").val(0);
            }
        });


        $('#ppn').change(function(){
            if (this.checked) {
                if ($("#is_item_ppn").prop('checked')) {
                    Swal.fire(
                        'Informasi',
                        'PPN pada item harga satuan aktif, silahkan gunakan salah satu PPN pada supplier atau PPN pada item harga satuan',
                        'info'
                    );   
                    $('#ppn').prop('checked', false);
                    $("#ppn" ).attr('value', '0');
                }else {
                    $('#ppn').prop('checked', true);
                    $("#ppn" ).attr('value', '1');
                }
            }else{
                $('#ppn').prop('checked', false);
                $("#ppn" ).attr('value', '0');
            }
        });
      

        $(".due_date_payment").datepicker().on('change', function() {
            checkDate($(this).datepicker('getDate'));
            $(this).valid();
        });

        $(".delivery_date").datepicker().on('change', function() {
            checkDate($(this).datepicker('getDate'));
        });

        $('.price_term_location').select2().on('change', function() {
            $(this).valid();
        });

        $('.price_term').select2().on('change', function() {
            $(this).valid();
        });

        $('.po_term').select2().on('change', function() {
            $(this).valid();
        });

        $('.payment_term').select2().on('change', function() {
            $(this).valid();
        });

        $('.payment_method').select2().on('change', function() {
            $(this).valid();
        });

        $('.po_currency').select2().on('change', function() {
            $(this).valid();
        });

        $('.po_note').select2().on('change', function() {
            $.ajax({
                url:"{{ route('purchasing.get_notes') }}/" + $(this).val(), // if you say $(this) here it will refer to the ajax call not $('.item')
                type:'GET',
                success:function(data) {
                    $('.notes').empty();
                    $('.notes').show();
                    $('.notes').val(data.description); 
                }
            });
            $(this).valid();

        });

        $('.payment_method').val('BANK TRANSFER').change();
        $('.price_term_location').val('JAKARTA').change();
        $('.po_currency').val('IDR').change();
        $('.price_term').val('FRANCO').change();
        $('.po_note').val('9').change();

        $('.supplier_pic').select2().on('change', function() {
            $(this).valid();
        });

        $('.supplier').select2().on('change', function() {
            $(this).valid();
        });

        $('.modalMd').off('click').on('click', function () {
            $('#modalMdContent').load($(this).attr('value'));
        });

        $('.modalMdPO').off('click').on('click', function () {
            $('#modalMdContentPO').load($(this).attr('value'));
        });

        $('.currency').inputmask({ 'alias': 'decimal', 'groupSeparator': ',', 'autoGroup': true, 'digits': 2, 'digitsOptional': false, 'placeholder': '0.00',allowMinus: false});

        $('#myonoffswitch').change(function() {
            if(this.checked) {
                $("#po_term_id" ).attr('value', '1');
            }else{
                $("#po_term_id" ).attr('value', '2');
            }
        });



        if($('#send_expense_ppn').val()=='1'){
            $('#send_expense_ppn').attr('checked','checked').iCheck('update');
        }
        $('#send_expense_ppn').on('ifChecked', function(){
            $("#send_expense_ppn" ).attr('value', '1');
        });
        $('#send_expense_ppn').on('ifUnchecked', function(){
            $("#send_expense_ppn" ).attr('value', '0');
        });

        var supplier = $(".supplier");
        var supplier_pic = $(".supplier_pic");
        var payment_term = $(".payment_term");

        supplier.select2({
            placeholder: 'Cari Supplier dengan mengetik 3 huruf...',
            ajax: {
                url:"{{ route('purchasing.get_supplier') }}",
                dataType: 'json',
                delay: 250,
                processResults: function (data) {
                    return {
                        results:  $.map(data, function (item) {
                            return {
                                text: item.name,
                                id: item.id,
                                ppn: item.is_ppn,
                                term: item.is_payment
                            }
                        })
                    };
                },
                cache: true
            }
        }).on('change', function() {

            $("#supplierData").show();
            $('#ppn').iCheck('update');
            var is_ppn = supplier.select2('data')[0].ppn;
            var is_payment = supplier.select2('data')[0].term;

            $.ajax({
                url:"{{ route('purchasing.get_supplier_pic') }}/" + supplier.val(), // if you say $(this) here it will refer to the ajax call not $('.item')
                type:'GET',
                success:function(data) {
                    supplier_pic.empty();
					supplier_pic.append($("<option></option>").attr("value", "").text("Silahkan pilih...")); 
                    $.each(data, function(value, key) {
                        supplier_pic.append($("<option></option>").attr("value", value).text(key)); // name refers to the objects value when you do you ->lists('name', 'id') in laravel
                    });
                    supplier_pic.select2(); 
                }
            });

            $.ajax({
                url:"{{ route('purchasing.get_payment_term') }}", // if you say $(this) here it will refer to the ajax call not $('.item')
                type:'GET',
                success:function(data) {
                    payment_term.empty();
                    $.each(data, function(value, key) {
                        payment_term.append($("<option></option>").attr("value", value).text(key)); // name refers to the objects value when you do you ->lists('name', 'id') in laravel
                    });
                    payment_term.select2().val(is_payment).trigger('change'); 
                }
            });
           
            if(is_ppn === 1){
                $('#ppn').prop('checked', true);
                $("#ppn" ).attr('value', '1');
            }else{
                $('#ppn').prop('checked', false);
                $("#ppn" ).attr('value', '0');
            }

            if ($("#ppn").prop('checked')) {
                if ($("#is_item_ppn").prop('checked')) {
                    Swal.fire(
                        'Informasi',
                        'PPN pada item harga satuan aktif, silahkan gunakan salah satu PPN pada supplier atau PPN pada item harga satuan',
                        'info'
                    );   
                    $('#ppn').prop('checked', false);
                    $("#ppn" ).attr('value', '0');
                }else {
                    $('#ppn').prop('checked', true);
                    $("#ppn" ).attr('value', '1');
                }
            }else{
                $('#ppn').prop('checked', false);
                $("#ppn" ).attr('value', '0');
            }
           
        });


        $("#checkedAll").change(function(){
            if(this.checked){
                $(".checkSingle").each(function(){
                    this.checked=true;
                })              
            }else{
                $(".checkSingle").each(function(){
                    this.checked=false;
                })              
            }
        });

        $(".checkSingle").click(function () {
            if ($(this).is(":checked")){
            var isAllChecked = 0;
            $(".checkSingle").each(function(){
                if(!this.checked)
                isAllChecked = 1;
            })              
            if(isAllChecked == 0){
                 $("#checkedAll").prop("checked", true); 
            }     
            }else {
                $("#checkedAll").prop("checked", false);
            }
        });

        $("#form-pr").validate({ 
            rules: { 
                    payment_method: "required",
                    due_date_payment: "required",
                    supplier_id: "required",
                    supplier_contact_id: "required",
                    payment_term_id: "required",
            },
            onfocusout: function( element ) {
                if ( !this.checkable( element ) && ( element.name in this.submitted || !this.optional( element ) ) ) {
                    this.element( element );
                }
            },
            invalidHandler: function(form, validator) {
                var errors = validator.numberOfInvalids();
                if (errors) {                    
                    validator.errorList[0].element.focus();
                }
            }
        }); 



        $(document).on('click', "#btn-submit-po", function(e) {
            $('input[name="status"]').val('1');
            var _this = $(this);
            var form = _this.parents('form');

            $("#form-pr").validate({ 
                rules: { 
                        payment_method: "required",
                        due_date_payment: "required",
                        supplier_id: "required",
                        supplier_contact_id: "required",
                        payment_term_id: "required",
                },
                onfocusout: function( element ) {
                    if ( !this.checkable( element ) && ( element.name in this.submitted || !this.optional( element ) ) ) {
                        this.element( element );
                    }
                },
                invalidHandler: function(form, validator) {
                    var errors = validator.numberOfInvalids();
                    if (errors) {                    
                        validator.errorList[0].element.focus();
                    }
                }
            });

            var checkbox= document.querySelector('input[name="iscreatePO[]"]:checked');
            if(!checkbox) {
                Swal.fire(
                    'Informasi',
                    'Minimal Checklist 1 Item untuk pembuatan PO',
                    'warning'
                );
                return false;
            }

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


        $(document).on('click', "#btn-draft-po", function(e) {
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

            var checkbox= document.querySelector('input[name="iscreatePO[]"]:checked');
            if(!checkbox) {
                Swal.fire(
                    'Informasi',
                    'Minimal Checklist 1 Item untuk pembuatan PO',
                    'warning'
                );
                return false;
            }
            
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

        function checkDate(val) {
            var selectedDate = val;
            var now = new Date(new Date().getFullYear(),new Date().getMonth() , new Date().getDate());
            var dt1 = Date.parse(now),
            dt2 = Date.parse(selectedDate);
            if (dt2 < dt1) {
                Swal.fire(
                    'Informasi',
                    'Anda menginput tanggal Back Date',
                    'info'
                );
            }
        }

   
    });
        document.addEventListener('trix-file-accept', function(e){
        e.preventDefault();
    });
	</script>
@stop
