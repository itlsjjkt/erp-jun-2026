@extends('layouts.app')

@section('page-header')
	Purchase Order <small>{{ trans('app.update_item') }}</small>
@stop

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('purchasing.po.index') }}">Purchase Order</a></li>
        <li class="breadcrumb-item active" aria-current="page">Edit</li>
    </ol>
@endsection


@section('content')

<div class="mB-40">
		{!! Form::model($po, [
				'action' => ['Purchasing\PoController@update', $po->id],
				'method' => 'put',
				'class' => 'form-horizontal mt-3',
				'files' => true,
                'id'    => 'formPR'
			])
		!!}

        <div class="bgc-white p-30 bd">
            <h6>Edit {{$po->doc_no}}</h6>
            <hr class='mB-30'>

            <div class="row">

                <div class="col-sm-6">
                    <div class="form-group row">
                        <label class="col-sm-3">Nama Supplier <span class="text-danger">*</span></label>
                        <div class="col-sm-8">
                            <select name="supplier_id"  class="form-control select2 supplier">
                                <option value="{{ $po->supplier_id }}" selected>{{ $po->supplier->name }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3">PIC Supplier <span class="text-danger">*</span></label>
                        <div class="col-sm-8">
                            <select name="supplier_contact_id" class="select2 form-control supplier_pic" required >
                                <option value="{{ $po->supplier_contact_id }}" selected>{{ $po->supplierContact->name }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-3">Payment Term <span class="text-danger">*</span></label>
                        <div class="col-sm-6">
                        {!! Form::select('payment_term_id', $payment_term, old('payment_term_id'), ['class' => 'form-control select2', 'required' => '']) !!}
                        </div>
                        <div class="col-sm-2">
                            <input type="checkbox" name="ppn" id="ppn" class="switch switch-info" value="{{ $po->ppn }}">
                            PPN
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-3">Jatuh Tempo Pembayaran <span class="text-danger">*</span></label>
                        <div class="col-sm-4">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="ti-calendar"></i></span>
                                </div>
                                {!! Form::text('due_date_payment', date('m/d/Y',strtotime( $po->due_date_payment)), ['class' => 'form-control datepicker due_date_payment', 'placeholder' => '', 'required' => '']) !!}
                            </div>
                        </div>
                        <div class="col-sm-4">
                            {!! Form::select('payment_method', $payment_method, old('payment_method'), ['class' => 'form-control select2', 'required' => '']) !!}
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-3">Price Term <span class="text-danger">*</span></label>
                        <div class="col-sm-4">
                            {!! Form::select('price_term', $price_term, old('price_term'), ['class' => 'form-control select2', 'required' => '']) !!}
                        </div>
                        <div class="col-sm-4">
                            {!! Form::select('price_term_location', $price_term_location, old('price_term_location'), ['class' => 'form-control select2', 'required' => '']) !!}
                        </div>
                    </div>


                    <div class="form-group row">
                        <label class="col-sm-3">Tgl & Biaya Pengiriman </label>
                        <div class="col-sm-4">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="ti-calendar"></i></span>
                                </div>
                                @if ($po->delivery_date == NULL)
                                    {!! Form::text('delivery_date','' , ['class' => 'form-control datepicker delivery_date', 'placeholder' => '']) !!}
                                @else
                                    {!! Form::text('delivery_date', date('m/d/Y',strtotime( $po->delivery_date)) , ['class' => 'form-control datepicker delivery_date', 'placeholder' => '']) !!}
                                @endif
                            </div>
                        </div>
                        <div class="col-sm-4">
                            {!! Form::text('send_expense', old('send_expense'), ['class' => 'form-control text-right currency mb-2', 'placeholder' => '']) !!}
                            <input type="checkbox" name="send_expense_ppn" id="send_expense_ppn" class="switch switch-info" value="{{ $po->send_expense_ppn }}"> PPN
                        </div>
                    </div>

                </div>

                <div class="col-sm-6">

                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label text-right">Mata Uang <span class="text-danger">*</span></label>
                        <div class="col-sm-4">
                            {!! Form::select('currency', $currency, old('currency') , ['class' => 'form-control select2', 'required' => '']) !!}
                        </div>
                        <div class="col-sm-4">
                            <div class="input-group">
                                {!! Form::number('pph', old('pph'), ['class' => 'form-control', 'placeholder' => '','min' => '0','step' => '0.01']) !!}
                                <div class="input-group-prepend">
                                    <span class="input-group-text">PPH (%)</span>
                                </div>
                            </div>
                        </div>
                    </div>

					<div class="form-group row">
                        <label class="col-sm-3 col-form-label text-right">Kontak PIC </label>
                        <div class="col-sm-8">
                            {!! Form::select('po_note', $po_note, old('po_note'), ['class' => 'form-control select2 po_note', 'required' => '']) !!}
                            <textarea class="form-control notes" rows="5" readonly>{!! $po2->notesDescription !!}</textarea>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label text-right">Catatan </label>
                        <div class="col-sm-8">
                            <input id="notes" type="hidden" name="notes" value="{{$po2->notes}}" class="form-control">
                            <trix-editor style="max-width: 500px; width: 100%;" input="notes"></trix-editor>
                        </div>
					</div>

                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label text-right">Bahasa</label>
                        <div class="col-sm-4">
                            <div class="onoffswitch">
                                <input type="checkbox" name="po_term_id" class="onoffswitch-checkbox" id="myonoffswitch" checked value="{{ $po->po_term_id }}">
                                <label class="onoffswitch-label" for="myonoffswitch">
                                    <span class="onoffswitch-inner"></span>
                                    <span class="onoffswitch-switch"></span>
                                </label>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
            <hr>


            <div class="form-group row mt-4">
                <label class="col-sm-2">Discount </label>
                <div class="col-sm-2">
                    {!! Form::select('discount_type', $discount_type, old('discount_type'), ['class' => 'form-control select2','id' => 'discount_type']) !!}
                </div>
                <div class="col-sm-3">
                    <input type='text' name="discount_amount" class="form-control currency diskonTotal" value="{{ round($po->discount_amount,2) }}" id="discountPO">
                </div>
                
                <div class="col-sm-3" id="discount_item_wrapper" style="{{ ($po->discount_type == '0') ? 'display:none' : ''}}">
                    <input type="checkbox" name="discount_item" id="discount_item" class="switch switch-info" value="{{ $po->discount_item }}"> Diskon Untuk Semua Item
                </div>
            </div>
            <hr>
            <div class="row ">
                <div class="col-lg-12">
                    <div class="float-right">
                        <input type="checkbox" name="is_item_ppn" id="is_item_ppn" class="magic-checkbox" value="0">
                        <label for="is_item_ppn">Checklist jika harga satuan include PPN</label>
                    </div>
                    <h6 class='mT-10'>DAFTAR ITEM</h6>
                </div>
            </div>
			<table class="table table-bordered mt-2" style="width: 100%">
                <thead>
                    <tr class="bg-grey-custome">
                        <th style="width:50px;text-align:center">NO</th>
                        <th >ITEM | SPEC</th>
                        <th style="width:250px">CATATAN</th>
                        <th style="width:100px">QTY</th>
                        <th style="width:70px">SATUAN</th>
                        <th style="width:150px">HARGA SATUAN</th>
                        <th style="width:80px">DISC (%)</th>
                        <th style="width:150px">TOTAL</th>
                        <th style="width: 50px"></th>
                        </tr>
				</thead>
                <tbody class="item_form">
                    @if (count($po_items) > 0)
                        @php
                            $no = 1
                        @endphp
                        @foreach ($po_items as $item)
                            @php
                                $totalPrice = $item->price  * $item->qty;
                                if($po->discount_type == 1) $totalPrice = ($item->price - ($item->price * $item->discount/100)) * $item->qty ;
                            @endphp
                            <tr>
                                <input name="po_item_id[]" type="hidden" value="{{ $item->id }}">
                                <input name="pr_item_id[]" type="hidden" value="{{ $item->pr_item_id }}">
                                <td style="text-align:center">{{ $no }}</td>
                                <td>{{ $item->product }} {!! $item->productPartNumber != NULL ? '<br>PN: '.$item->productPartNumber : '' !!}
									<br>
									{{ $item->productBrand != NULL ? 'Brand: '.$item->productBrand : '' }}
                                    <input name="product_id[]" type="hidden" value="{{ $item->product_id }}">
                                </td>
                                <td>
								{{--<textarea name="specification[]" class="form-control mb-2">{{$item->specification}}</textarea>--}}
									<input id="specification{{$item->id}}" type="hidden" name="specification[]" value="{{$item->specification}}">
									<div style="width: 250px;">
										<trix-editor input="specification{{$item->id}}"></trix-editor>      
									</div>
                                </td>
                                <td>
                                    @if ($item->po_status == '2')
                                        <input name="qty[]" type="hidden" value="{{$item->qty_parsial + $item->qty}}"  id='qty_pr_{{$item->id}}'>
                                    @else
                                        <input name="qty[]" type="hidden" class="form-control qty" value="{{$item->qty}}">
                                    @endif
                                    <input name="qty_po[]" class="form-control text-right" value="{{$item->qty }}" id='qty_po_{{$item->id}}' oninput="this.value = Math.abs(this.value)" onkeypress="return event.charCode >= 48" min="1">
                                    <input name="measure_id[]" type="hidden" value="{{ $item->measure }}">
                                </td>
                                <td>{{ $item->measure }}</td>
                                <td><input type='text' name="price[]" class="form-control currency text-right next_price" value="{{ $item->price }}" id="price_{{ $item->id }}"> </td>
                                <td><input type="number" name="diskon_item[]" class="form-control diskon" data-id="{{ $item->id }}" id="discount_{{ $item->id }}" value="{{ $item->discount }}" min="0" step="0.01" max="100" onwheel="return false;"> </td>
                                <td><input type="text" readonly class="form-control text-right totalPrice"  id="totalPrice_{{ $item->id }}" value="{{ $totalPrice }}"> </td>
                                <td style="max-width: 50px;">
                                    <a href="{{ route('purchasing.po.remove_item',['id'=>$item->id ]) }}" onclick="deleteItem({{ $item->id }})" title="Hapus Item" data-toggle='tooltip' class='btn btn-outline' id="btn-remove"><span class='ti-trash text-danger icon-lg'></span> </button>
                                </td>
                            </tr>
                        @php
                            $no++
                        @endphp
                        @endforeach
                    @endif
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="7" class="font-bold text-right"><strong>NETTO <br><small>Setelah dikurangi Diskon</small></strong></td>
                        <td colspan="2" class="font-weight-bold text-right"><span id="total" class="font-bold text-right"></span></td>
                    </tr>
                 </tfoot>
            </table>
		</div>

        <div class="mt-4">
            <a href="{{ route('purchasing.po.index') }}" class="btn btn-light  text-uppercase fsz-sm fw-600">{{ trans('Cancel') }}</a>
            @if($po->status==0)
                <input class="btn btn-info  text-uppercase fsz-sm fw-600" type="submit" name="save" value="Save as Draft" id="btn-draft-po" >
                <input type="hidden" value="0" name="status">
                <input class="btn btn-danger  text-uppercase fsz-sm fw-600"  type="submit" name="publish" id="btn-submit-po" value="Publish">
            @endif
            @if($po->status==3)
                <input type="hidden" value="3" name="status">
                <input class="btn btn-danger  text-uppercase fsz-sm fw-600" type="submit" name="save" value="Perbaiki" >
            @endif
        </div>

	{!! Form::close() !!}
</div>

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
        var price  = $("#price_"+id).val().replace(/\,/g,'');
        var diskon = $("#discount_"+id).val();
        var qty    = $("#qty_po_"+id).val();
        if(diskon == 0) var total = parseFloat(price) * qty ;
        else var total = (parseFloat(price) - (parseFloat(price) * diskon/100)) * qty;
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



    function deleteItem(id) {
        var getLink = $(this).attr('href');

        Swal.fire({
            title: "Are you sure?",
            text: "Dengan menghapus Item PO akan dikembalikan ke PR, apakah anda yakin?",
            type: "warning",
            showCancelButton: true,
            confirmButtonText: "Ya, Hapus!",
            confirmButtonColor: "#ec6c62",
            closeOnConfirm: false
            }, function (isConfirm) {
                if (!isConfirm) return;
                window.location.href = getLink
        });
    }

	$(document).ready(function() {


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

        <?php foreach ($po_items as $item) { ?>
            $("#price_{{ $item->id }}").keyup(function(){
                var id     = "{{ $item->id }}";
                calculatePrice(id)
                calculateSum();
            });
            $("#qty_po_{{ $item->id }}").keyup(function(){
                var id     = "{{ $item->id }}";
                calculatePrice(id)
                calculateSum();
            });
            $("#discount_{{ $item->id }}").keyup(function(){
                var id   = "{{ $item->id }}";
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

        calculateSum();

        $('.delete-link').on('click',function(){
            var getLink = $(this).attr('href');
            swal({
                    title: 'Alert',
                    text: 'Hapus Data?',
                    html: true,
                    confirmButtonColor: '#d9534f',
                    showCancelButton: true,
                    },function(){
                    window.location.href = getLink
                });
            return false;
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

        if($('#discount_item').val()=='1'){
            $('#discount_item').prop('checked', true).iCheck('update');
        }
        
        if($('#discount_type').val()=='1'){
            $("#discount_item" ).attr('max', '100');
        }


        $("#discount_type").select2().on('change', function() {
            var discountPO = $("#discountPO").val().replace(/\,/g,'');
            if($(this).val()=='0'){
                $("#discount_item_wrapper").hide();
                $('#discount_item').prop('checked', false).iCheck('update');
                $("#discount_item").val('0');
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

        $(".due_date_payment").datepicker().on('change', function() {
            checkDate($(this).datepicker('getDate'));
            $(this).valid();
        });

        $(".delivery_date").datepicker().on('change', function() {
            checkDate($(this).datepicker('getDate'));
        });


        $('.modalMd').off('click').on('click', function () {
            $('#modalMdContent').load($(this).attr('value'));
        });

        $('.modalMdPO').off('click').on('click', function () {
            $('#modalMdContentPO').load($(this).attr('value'));
        });

        $('.currency').inputmask({ 'alias': 'decimal', 'groupSeparator': ',', 'autoGroup': true, 'digits': 2, 'digitsOptional': false, 'placeholder': '0.00',allowMinus: false});

        if($('#ppn').val()=='11'){
            $('#ppn').attr('checked','checked').iCheck('update');
        }
        $('#ppn').on('ifChecked', function(){
            $("#ppn" ).attr('value', '11');
        });
        $('#ppn').on('ifUnchecked', function(){
            $("#ppn" ).attr('value', '0');
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

        $('#myonoffswitch').change(function() {
            if(this.checked) {
                $("#po_term_id" ).attr('value', '1');
            }else{
                $("#po_term_id" ).attr('value', '2');
            }
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
                url:"{{ route('purchasing.get_payment_term') }}",
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
                $('#ppn').attr('checked','checked').iCheck('update');
                $("#ppn" ).attr('value', '1');
            }else{
                $('#ppn'). removeAttr('checked').iCheck('update');
                $("#ppn" ).attr('value', '0');
            }

        });


        supplier_pic.select2().on('change', function() {
            $.ajax({
                url:"{{ route('purchasing.get_supplier_pic_detail') }}/" + supplier_pic.val(), // if you say $(this) here it will refer to the ajax call not $('.item')
                type:'GET',
                success:function(data) {
                    $("#picTelp").text("Telp: " + data.telp);
                    $("#picEmail").text("Email: " +data.email);
                }
            });
        }).trigger('change');


        $(document).on('click', "#btn-submit-po", function(e) {
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


        $("#formPR").validate({
            rules: {
                payment_method: "required",
                currency: "required",
                price_term: "required",
                price_term_location: "required",
                due_date_payment: "required",
                supplier_id: "required",
                supplier_contact_id: "required",
                payment_term_id: "required",
            },
            onfocusout: false,
            invalidHandler: function(form, validator) {
                var errors = validator.numberOfInvalids();
                if (errors) {
                    validator.errorList[0].element.focus();
                }
            }
        });
    });
	$('.po_note').select2().on('change', function() {
        $.ajax({
            url:"{{ route('purchasing.get_notes') }}/" + $(this).val(),
            type:'GET',
            success:function(data) {
                $('.notes').empty();
                $('.notes').show();
                $('.notes').val(data.description); 
            }
        });
        $(this).valid();

	});

	document.addEventListener('trix-file-accept', function(e){
        e.preventDefault();
	});
    </script>
@stop

