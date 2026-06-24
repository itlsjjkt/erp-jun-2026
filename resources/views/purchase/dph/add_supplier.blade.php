@extends('layouts.app')

@section('page-header')
	Tambah Data Supplier
@stop

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('purchasing.dph.index') }}">Daftar Perbandingan Harga</a></li>
        <li class="breadcrumb-item active" aria-current="page">Tambah Supplier</li>
    </ol>
@endsection

@section('content')

<div class="mB-40">
    <div class="bgc-white p-30 bd">
        {!! Form::open(['method' => 'POST', 'route' => ['purchasing.dph.add_store'], 'id' => 'form-pr','files' => true]) !!}
            <input type="hidden" value="{{$dph->id}}" name="dph_id" id="dphId">
            <div class="tab-content" id="myTabContent">
                <div class="tab-pane active" id="supplier_" role="tabpanel" aria-labelledby="head_sup_tab_">
                    <div class="row mt-3">
                        <div class="col-sm-6">
                            <div class="form-group row">
                                <label class="col-sm-11">Tambah Data Supplier {{$dph->doc_no}}</label>
                            </div>

                            <!-- Supplier Selection -->
                            <div class="form-group row">
                                <label class="col-sm-3">Nama Supplier <span class="text-danger">*</span></label>
                                <div class="col-sm-8">
                                    <select name="supplier_id" class="select2 form-control supplier" required></select>
                                </div>
                            </div>

                            <!-- PIC Selection -->
                            <div class="form-group row">
                                <label class="col-sm-3">PIC Supplier <span class="text-danger">*</span></label>
                                <div class="col-sm-8">
                                    <select name="supplier_contact_id" class="select2 form-control supplier_pic" required></select>
                                </div>
                            </div>

                            <!-- Payment Information -->
                            <div>
                                <div id="supplierData" style="display:none">
                                    <div class="form-group row">
                                        <label class="col-sm-3">Payment Term<span class="text-danger">*</span> & PPN</label>
                                        <div class="col-sm-4">
                                            <select name="payment_term_id" class="select2 form-control payment_term" required></select>
                                        </div>
                                        <div class="col-sm-4">
                                            {!! Form::select('ppn', $ppn, old('ppn'), ['class' => 'form-control select2','id'=>'ppn']) !!}
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-sm-3 col-form-label text-left">Metode Pembayaran<span class="text-danger">*</span> & Mata Uang<span class="text-danger">*</span></label>
                                        <div class="col-sm-4">
                                            {!! Form::select('payment_method', $payment_method, null , ['class' => 'form-control select2 payment_method', 'required' => '','id' => 'payment_method']) !!}
                                        </div>
                                        <div class="col-sm-4">
                                            {!! Form::select('currency', $currency, null , ['class' => 'form-control select2 '.'po_currency', 'required' => '','id' => 'currency']) !!}
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-3 col-form-label text-left">Attachment <i class="fa fa-file-pdf-o text-danger icon-lg"></i></label>
                                    <div class="col-sm-4">
                                        {!! Form::file('mr_file', ['class' => '', 'accept' => '.pdf']) !!}
                                    </div>
                                    <div class="col-sm-4 form-group row">
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
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label text-right"></label>
                                <div class="col-sm-4">
                                </div>
                                <div class="col-sm-4">
                                </div>
                            </div>

                            <!-- Price Terms -->
                            <div class="form-group row">
                                <label class="col-sm-3">Price Term <span class="text-danger">*</span></label>
                                <div class="col-sm-4">
                                    {!! Form::select('price_term', $price_term, old('price_term'), ['class' => 'form-control select2 price_term', 'required']) !!}
                                </div>
                                <div class="col-sm-4">
                                    {!! Form::select('price_term_location', $price_term_location, old('price_term_location'), ['class' => 'form-control select2 price_term_location', 'required']) !!}
                                </div>
                            </div>

                            <!-- Delivery Information -->
                            <div class="form-group row">
                                <label class="col-sm-3">Waktu Pengirimanan<span class="text-danger">*</span> </label>
                                <div class="col-sm-8 input-group">
                                    {!! Form::number('estimated_delivery_day', null, ['class' => 'form-control estimated_delivery_day text-right', 'placeholder' => 'Hari', 'required' => '']) !!}
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">HARI</span>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-sm-3">Biaya Pengiriman </label>
                                <div class="col-sm-4">
                                    {!! Form::text('send_expense', 0, ['class' => 'form-control '.'currency text-right', 'placeholder' => 'Biaya']) !!}
                                </div>
                                <div class="col-sm-4">
                                    {!! Form::select('send_expense_ppn', $send_expense_ppn, old('send_expense_ppn'), ['class' => 'form-control select2','id'=>'send_expense_ppn']) !!}
                                </div>
                            </div>
                            <hr>
                            <div class="form-group row">
                                <label class="col-sm-3">Discount </label>
                                <div class="col-sm-4">
                                    {!! Form::select('discount_type', $discount_type, old('discount_type'), ['class' => 'form-control select2', 'id' => 'discount_type']) !!}
                                </div>
                                <div class="col-sm-4">
                                    {!! Form::text('discount_amount', 0, ['class' => 'form-control '.'currency text-right', 'placeholder' => '', 'id' => 'discountPO']) !!}
                                </div>
                            </div>
                            <div class="form-group row" id="row_discount_item">
                                <label class="col-sm-3">Edit Diskon Per Item </label>
                                <div class="col-sm-4">
                                        {!! Form::select('discount_item', $discount_item, old('discount_item'), ['class' => 'form-control select2', 'id' => 'discount_item']) !!}
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="row mt-2">
                        <table class="table table-bordered">
                            <thead>
                                <tr class="bg-grey-custome">
                                    <th style="width:50px"></th>
                                    <th colspan="2">ITEM</th>
                                    <th style="width:250px">CATATAN</th>
                                    <th style="width:100px">QTY</th>
                                    <th style="width:70px">SATUAN</th>
                                    <th style="width:150px">HARGA SATUAN</th>
                                    <th style="width:80px">DISC (%)</th>
                                    <th style="width:200px">TOTAL</th>
                                </tr>
                            </thead>
                            <tbody class="item_form">
                                @if (count($pr_items) > 0)
                                    @php $no = 1 @endphp
                                    @foreach ($pr_items as $item)
                                        @php
                                            $check_items = getCheckDphItemAdd($dph->id, $item->id);
                                        @endphp
                                        <tr>
                                            <input name="pr_item_id[]" type="hidden" value="{{ $item->id }}">
                                            <td>
                                                <input type="hidden" name="is_recomendation[{{$item->id}}]" value="0">
                                                <input type="checkbox" name="is_recomendation[{{$item->id}}]"  class="checkSingle magic-checkbox"  value="{{ 1 }}" id="checkbox_{{$item->id}}" {{ $check_items ? 'disabled' : '' }} onchange="handleCheckboxChange(this, {{$item->id}});"><label for="checkbox_{{$item->id}}"></label>
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
                                                <small>
                                                    <br>
                                                    {!! $item->productPartNumber != NULL ? 'PN: '.$item->productPartNumber : 'PN: -' !!}
                                                    <br>
                                                    {{ $item->productBrand != NULL ? 'Brand: '.$item->productBrand : 'Brand: -' }}
                                                    <input name="product_id[]" type="hidden" value="{{ $item->product_id }}">
                                                </small>
                                                @if($check_items)
                                                <br>
                                                <small style="font-family: 'Courier New', Courier, monospace;" class="text-danger">Item sudah dipilih untuk supplier lain</small>
                                                @endif
                                            </td>
                                            <td>
                                                <input id="specificationzz{{$item->id}}" type="hidden" name="specification[]" value="{{$item->notes}}">
                                                <div style="width: 250px;">
                                                    <trix-editor input="specificationzz{{$item->id}}"></trix-editor>
                                                </div>
                                            </td>
                                            <td>
                                                @if ($item->po_status == 2)
                                                    <input type="number" name="qty[]" class="form-control qty text-right" value="{{$item->qty_parsial}}" min="0" oninput="this.value = Math.abs(this.value)" id="qty_{{ $item->id }}" onwheel="return false;" onkeypress="return event.charCode >= 48">
                                                    <small>Qty Partial: {{$item->qty_parsial}}</small>
                                                    <input name="qty_pr[]" type="hidden" value="{{$item->qty_parsial}}" id='qty_po_{{$item->id}}'>
                                                @else
                                                    <input type="number" name="qty[]" class="form-control qty text-right" value="{{$item->qty}}" min="0" oninput="this.value = Math.abs(this.value)" id="qty_{{ $item->id }}" onwheel="return false;" onkeypress="return event.charCode >= 48">
                                                    <small>Qty Partial: {{$item->qty}}</small>
                                                    <input name="qty_pr[]" type="hidden" value="{{$item->qty}}" id='qty_po_{{$item->id}}'>
                                                @endif
                                                <input name="measure_id[]" type="hidden" value="{{ $item->measure }}">
                                            </td>
                                            <td>{{ $item->measure }}</td>
                                            <td>
                                                <input type="text" name="price[]" class="form-control text-right currency price next_price" value="0" id="price_{{ $item->id }}">
                                            </td>
                                            <td>
                                                <input type="number" name="diskon_item[]" class="form-control text-right diskon" value="0" id="discount_{{ $item->id }}" data-id="{{ $item->id }}" min="0" step="0.01" max="100" onwheel="return false;">
                                            </td>
                                            <td>
                                                <input type="text" name="total_price[]" readonly class="form-control text-right totalPrice" id="totalPrice_{{ $item->id }}" value="0">
                                            </td>
                                        </tr>
                                        @php $no++ @endphp
                                    @endforeach
                                @endif
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="7" class="font-bold text-right"><strong>NETTO <br><small>Setelah dikurangi Diskon</small></strong></td>
                                    <td colspan="1" class="font-weight-bold text-right"><span id="total" class="font-bold text-right"></span></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="mt-4">
                <a href="{{ route('purchasing.dph.index') }}" class="btn btn-light text-uppercase fsz-sm fw-600">{{ trans('Cancel') }}</a>
                <input type="hidden" value="1" name="status">
                <input class="btn btn-danger text-uppercase fsz-sm fw-600" type="submit" name="publish" id="btn-submit-dph" value="Tambah Supplier">
            </div>

        {!! Form::close() !!}
    </div>
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
            },
            function (isConfirm) {
                if (!isConfirm) return;
                window.location.href = getLink
        });
    }

	$(document).ready(function() {

        function toggleRowBackground(checkbox) {
            var $row = $(checkbox).closest('tr');
            if (checkbox.checked) {
                $row.css('background-color', '#dbefe0');
            }
            else {
                $row.css('background-color', '');
            }
        }
        $('.checkSingle').each(function() {
            toggleRowBackground(this);
        });
        $('.checkSingle').change(function() {
            toggleRowBackground(this);
        });

        $("#is_item_ppn").change(function() {
            var isItemPpnValue = $(this).val();
            var ppnValue = $("#ppn").val();

            if (isItemPpnValue === '1') {
                if (ppnValue === '1') {
                    Swal.fire(
                        'Informasi',
                        'PPN pada supplier aktif, silahkan gunakan salah satu PPN pada supplier atau PPN pada item harga satuan',
                        'info'
                    );
                    $(this).val('0').trigger('change');
                }
            }
        });

        $("#ppn").change(function() {
            var ppnValue = $(this).val();
            var isItemPpnValue = $("#is_item_ppn").val();

            if (ppnValue === '1') {
                if (isItemPpnValue === '1') {
                    Swal.fire(
                        'Informasi',
                        'PPN pada item harga satuan aktif, silahkan gunakan salah satu PPN pada supplier atau PPN pada item harga satuan',
                        'info'
                    );
                    $(this).val('0').trigger('change');
                }
            }
        });

        <?php foreach ($pr_items as $item) { ?>
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

        if($('#discount_item').val() ==='0'){
            $('#discount_item').val('0').trigger('change');
        }
        else{
            $('#discount_item').val('1').trigger('change');
        }

        if($('#discount_type').val() ==='1'){
            $("#row_discount_item").show();
            $("#discount_item" ).attr('max', '100');
        }else{
            $("#row_discount_item").hide();
        }

        if($('#discount_item').val() === '0'){
            $(".diskon").prop('readonly', false);
        }else{
            $(".diskon").prop('readonly', true);
        }

        $("#discount_type").select2().on('change', function() {
            var discountPO = $("#discountPO").val().replace(/\,/g,'');
            if($(this).val()=='0'){
                $("#discount_item_wrapper").hide();
                $("#discount_item").val('0').trigger('change');
                $(".diskon").each(function(){
                    var id = $(this).attr('data-id');
                    $(".diskon").val(0);
                    $(".diskon" ).prop('readonly', false);
                    $("#row_discount_item").hide();
                    calculatePrice(id)
                })
            }else {
                $("#row_discount_item").show();
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

        $('#discount_item').on('change', function() {
            var selectedValue = $(this).val();
            var discountPO = $("#discountPO").val().replace(/\,/g, '');
            $(".diskon").each(function() {
                var id = $(this).attr('data-id');
                if (selectedValue === '1') {
                    $(this).val(discountPO);
                    $(this).prop('readonly', true);
                    calculatePrice(id);
                } else {
                    $(this).val('0');
                    $(this).prop('readonly', false);
                    calculatePrice(id);
                }
            });
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

        var currentValue = $('#ppn').val();
        if (currentValue === '0') {
            $('#ppn').val('0').trigger('change');
        } else {
            $('#ppn').val('1').trigger('change');
        }

        var sendExpensePpnValue = $('#send_expense_ppn').val();
        if (sendExpensePpnValue === '0') {
            $('#send_expense_ppn').val('0').trigger('change');
        } else {
            $('#send_expense_ppn').val('1').trigger('change');
        }

        $('#myonoffswitch').change(function() {
            if(this.checked) {
                $("#po_term_id" ).attr('value', '1');
            }else{
                $("#po_term_id" ).attr('value', '2');
            }
        });

        $('.payment_method').val('BANK TRANSFER').change();
        $('.price_term_location').val('JAKARTA').change();
        $('.po_currency').val('IDR').change();
        $('.price_term').val('FRANCO').change();
        $('.po_note').val('9').change();

        var supplier_pic = $(".supplier_pic");
        var payment_term = $(".payment_term");
        var payment_method = $(".payment_method");
        var supplier = $(".supplier");
        supplier.select2({
            placeholder: 'Cari Supplier dengan mengetik 3 huruf...',
            ajax: {
                url: "{{ route('purchasing.get_supplier') }}",
                dataType: 'json',
                delay: 250,
                processResults: function (data) {
                    return {
                        results: $.map(data, function (item) {
                            return {
                                text: item.name,
                                id: item.id,
                                ppn: item.is_ppn,
                                term: item.is_payment,
                                currency: item.is_currency,
                                payment_method: item.is_payment_method,
                            };
                        })
                    };
                },
                cache: true
            }
        }).on('change', function() {
            $("#supplierData").show();
            var is_ppn = supplier.select2('data')[0].ppn;
            var is_currency = supplier.select2('data')[0].currency;
            var is_payment = supplier.select2('data')[0].term;
            var is_payment_method = supplier.select2('data')[0].payment_method;
            debugger;
            $('#ppn').val(is_ppn).trigger('change');
            $.ajax({
                url:"{{ route('purchasing.get_supplier_pic') }}/" + supplier.val(),
                type:'GET',
                success:function(data) {
                    supplier_pic.empty();
					supplier_pic.append($("<option></option>").attr("value", "").text("Silahkan pilih..."));
                    $.each(data, function(value, key) {
                        supplier_pic.append($("<option></option>").attr("value", value).text(key));
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
                        payment_term.append($("<option></option>").attr("value", value).text(key));
                    });
                    payment_term.select2().val(is_payment).trigger('change');
                }
            });

            $.ajax({
                url:"{{ route('purchasing.get_payment_method') }}",
                type:'GET',
                success:function(data) {
                    payment_method.empty();
                    $.each(data, function(value, key) {
                        payment_method.append($("<option></option>").attr("value", value).text(key));
                    });
                    payment_method.select2().val(is_payment_method).trigger('change');
                }
            });

            if(is_ppn != 0){
                $('#ppn').val(is_ppn).trigger('change');
                $("#ppn" ).attr('value', is_ppn);
            }else{
                $('#ppn').val(0).trigger('change');
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

        $(document).on('click', "#btn-submit-dph", function(e) {
            var _this = $(this);
            var form = _this.parents('form');

            var suppliers = $(".supplier").val();
            var supplier_pic = $(".supplier_pic").val();
            var payment_term = $(".payment_term").val();
            var payment_method = $(".payment_method").val();

            if (!suppliers || !supplier_pic || !payment_term || !payment_method) {
                Swal.fire(
                    'Informasi',
                    'Semua data supplier harus terisi',
                    'warning'
                );
                return;
            }
            e.preventDefault();
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
        });


    });
    document.addEventListener('trix-file-accept', function(e){
        e.preventDefault();
    });
</script>
@stop

