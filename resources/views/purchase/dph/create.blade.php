@extends('layouts.app')

@section('page-header')
    Daftar Perbandingan Harga
@endsection

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('purchasing.pr') }}">Purchase Requisition</a></li>
        <li class="breadcrumb-item active" aria-current="page">Pembuatan DPH</li>
    </ol>
@endsection


@section('content')
    <style>
        .monitoring .active {
            background-color: #d4edda !important;
            border-color: #c3e6cb !important;
            font-weight: bold;
        }
    </style>
    <div class="mB-40">
        <div class="bgc-white p-30 bd">
            <h5 style="text-align: center;">{{ $pr->doc_no }}</h5>
            <ul class="nav nav-tabs mt-5 monitoring" id="myTab" role="tablist">
                @for($in = 1; $in <= $count_form; $in++)
                @if($in != 1)
                    <li class="arrow" style="padding: 20px 0px;font-size: 20px;"><i class="ti-arrow-right"></i></li>
                @endif
                    <li class="nav-item">
                        <a style="border: 2px solid #dddddd;border-radius: 0;padding: 10px 12px; font-size:14px" @if($in == 1) class="nav-link active" @else class="nav-link" @endif id="head_sup_tab_{{$in}}" data-toggle="tab" href="#supplier_{{$in}}" role="tab" aria-controls="supplier_{{$in}}" aria-selected="false">DATA SUPPLIER <br><small>KE - {{$in}}</small></a>
                    </li>
                @endfor
            </ul>
            {!! Form::open(['method' => 'POST', 'route' => ['purchasing.dph.store'], 'id' => 'form-pr','files' => true]) !!}
            <input name="count_loop" value="{{$count_form}}" type="hidden">
            <input name="purchase_id" type="hidden" value="{{ $pr->id }}">
            <input name="location_id" type="hidden" value="{{ $pr->location_id }}">
            <input name="company_id" type="hidden" value="{{ $pr->company_id }}">
            <input name="company_code" type="hidden" value="{{ $pr->company_code }}">
            <div class="tab-content" id="myTabContent">
                @for($i = 1; $i <= $count_form; $i++)
                    <div @if($i == 1) class="tab-pane active" @else class="tab-pane fade" @endif id="supplier_{{$i}}" role="tabpanel" aria-labelledby="head_sup_tab_{{$i}}">
                        <div class="row mt-3">
                            <div class="col-sm-6">
                                <div class="form-group row">
                                    <label class="col-sm-3">DATA SUPPLIER KE - {{$i}}</label>
                                    <div class="col-sm-8">
                                    </div>
                                </div>

                                <!-- Supplier Selection -->
                                <div class="form-group row">
                                    <label class="col-sm-3">Nama Supplier <span class="text-danger">*</span></label>
                                    <div class="col-sm-8">
                                        <select name="supplier_id[]" class="select2 form-control {{$i}}supplier" required></select>
                                    </div>
                                </div>

                                <!-- PIC Selection -->
                                <div class="form-group row">
                                    <label class="col-sm-3">PIC Supplier <span class="text-danger">*</span></label>
                                    <div class="col-sm-8">
                                        <select name="supplier_contact_id[]" class="select2 form-control {{$i}}supplier_pic" required></select>
                                    </div>
                                </div>

                                <!-- Payment Information -->
                                <div id="{{$i}}supplierData" style="display:none">
                                    <div class="form-group row">
                                        <label class="col-sm-3">Payment Term<span class="text-danger">*</span> & PPN</label>
                                        <div class="col-sm-4">
                                            <select name="payment_term_id[]" class="select2 form-control {{$i}}payment_term" required></select>
                                        </div>
                                        <div class="col-sm-4">
                                            {!! Form::select('ppn[]', $ppn, old('ppn[]'), ['class' => 'form-control select2','id'=>$i.'ppn']) !!}
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-sm-3 col-form-label text-left">Metode Pembayaran<span class="text-danger">*</span> & Mata Uang<span class="text-danger">*</span></label>
                                        <div class="col-sm-4">
                                            {!! Form::select('payment_method[]', $payment_method, null , ['class' => 'form-control select2 '.$i.'payment_method', 'required' => '','id' => $i.'payment_method']) !!}
                                        </div>
                                        <div class="col-sm-4">
                                            {!! Form::select('currency[]', $currency, null , ['class' => 'form-control select2 '.$i.'po_currency', 'required' => '','id' => $i.'currency']) !!}
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-3 col-form-label text-left">Attachment <i class="fa fa-file-pdf-o text-danger icon-lg"></i></label>
                                    <div class="col-sm-4">
                                        {!! Form::file('mr_file[]', ['class' => '', 'accept' => '.pdf']) !!}
                                    </div>
                                    <div class="col-sm-4 form-group row">
                                        <label class="col-sm-3 col-form-label text-right">Bahasa</label>
                                        <div class="col-sm-4">
                                            <div class="onoffswitch">
                                                <input type="checkbox" class="onoffswitch-checkbox" id="{{$i.'myonoffswitch'}}" checked>
                                                <label class="onoffswitch-label" for="{{$i.'myonoffswitch'}}">
                                                    <span class="onoffswitch-inner"></span>
                                                    <span class="onoffswitch-switch"></span>
                                                </label>
                                            </div>
                                            <input type="hidden" name="po_term_id[]" id="{{$i.'po_term_id'}}" value="1">
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
                                        {!! Form::select('price_term[]', $price_term, old('price_term[]'), ['class' => 'form-control select2 '.$i.'price_term', 'required']) !!}
                                    </div>
                                    <div class="col-sm-4">
                                        {!! Form::select('price_term_location[]', $price_term_location, old('price_term_location[]'), ['class' => 'form-control select2 '.$i.'price_term_location', 'required']) !!}
                                    </div>
                                </div>

                                <!-- Delivery Information -->
                                <div class="form-group row">
                                    <label class="col-sm-3">Waktu Pengirimanan<span class="text-danger">*</span> </label>
                                    <div class="col-sm-8 input-group">
                                        {!! Form::number('estimated_delivery_day[]', null, ['class' => 'form-control '.$i.'estimated_delivery_day text-right', 'placeholder' => 'Hari', 'required']) !!}
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">HARI</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-sm-3">Biaya Pengiriman </label>
                                    <div class="col-sm-4">
                                        {!! Form::text('send_expense[]', 0, ['class' => 'form-control '.$i.'currency text-right', 'placeholder' => 'Biaya']) !!}
                                    </div>
                                    <div class="col-sm-4">
                                        {!! Form::select('send_expense_ppn[]', $send_expense_ppn, old('send_expense_ppn[]'), ['class' => 'form-control select2','id'=>$i.'send_expense_ppn']) !!}
                                    </div>
                                </div>
                                <hr>
                                <div class="form-group row">
                                    <label class="col-sm-3">Discount </label>
                                    <div class="col-sm-4">
                                        {!! Form::select('discount_type[]', $discount_type, old('discount_type[]'), ['class' => 'form-control select2', 'id' => $i.'discount_type']) !!}
                                    </div>
                                    <div class="col-sm-4">
                                        {!! Form::text('discount_amount[]', 0, ['class' => 'form-control '.$i.'currency text-right', 'placeholder' => '', 'id' => $i.'discountPO']) !!}
                                    </div>
                                </div>
                                <div class="form-group row" id="{{$i}}row_discount_item">
                                    <label class="col-sm-3">Edit Diskon Per Item </label>
                                    <div class="col-sm-4">
                                            {!! Form::select('discount_item[]', $discount_item, old('discount_item[]'), ['class' => 'form-control select2', 'id' => $i.'discount_item']) !!}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info">
                            - Checklist Item Untuk di Ajukan Ke Approval DPH<br>
                            - Jika Satuan Produk tidak sesuai silahkan konfirmasi ke bagain admin Logistik karena berhubungan dengan Inventory Control<br>
                         </div>

                        <div class="row mt-2">
                            <table class="table table-bordered">
                                <thead>
                                    <tr class="bg-grey-custome">
                                        <th style="width:50px">
                                            <input class="magic-checkbox checkedAll_" name="checkedAll[{{$i}}]" id="checkedAll{{$i}}" type="checkbox" onchange="handleCheckboxAllChange(this, {{$i}});"><label for="checkedAll{{$i}}"></label>
                                        </th>
                                        <th colspan="2">ITEM</th>
                                        <th style="width:250px">CATATAN</th>
                                        <th style="width:100px">QTY</th>
                                        <th style="width:70px">SATUAN</th>
                                        <th style="width:150px">HARGA SATUAN</th>
                                        <th style="width:80px">DISC (%)</th>
                                        <th style="width:200px">TOTAL</th>
                                    </tr>
                                </thead>
                                <tbody class="item_form" id="t_body{{$i}}">
                                    @if (count($pr_items) > 0)
                                        @php $no = 1 @endphp
                                        @foreach ($pr_items as $item)
                                            <tr>
                                                <input name="pr_item_id[]" type="hidden" value="{{ $item->id }}">
                                                <td>
                                                    <input type="hidden" name="is_recomendation[{{$i}}{{$item->id}}]" value="0">
                                                    <input type="checkbox" name="is_recomendation[{{$i}}{{$item->id}}]" class="checkSingle{{$i}} checkSingle_ magic-checkbox" value="{{ 1 }}" id="{{$i}}checkbox_{{$item->id}}" onchange="handleCheckboxChange(this, {{$item->id}}, {{$i}});">
                                                    <label for="{{$i}}checkbox_{{$item->id}}"></label>
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
                                                    {!! $item->productPartNumber != NULL ? 'PN: '.$item->productPartNumber : 'PN: -' !!}
                                                    <br>
                                                    {{ $item->productBrand != NULL ? 'Brand: '.$item->productBrand : 'Brand: -' }}
                                                    <input name="product_id[]" type="hidden" value="{{ $item->product_id }}">
                                                </td>
                                                <td>
                                                    <input id="{{$i}}specificationzz{{$item->id}}" type="hidden" name="specification[]" value="{{$item->notes}}">
                                                    <div style="width: 250px;">
                                                        <trix-editor input="{{$i}}specificationzz{{$item->id}}"></trix-editor>
                                                    </div>
                                                </td>
                                                <td>
                                                    <input type="number" name="qty[]" class="form-control qty text-right" value="{{$item->qty - getQtyItemDphByPrItemId($item->id)}}" min="0" oninput="this.value = Math.abs(this.value)" id="{{$i}}qty_{{ $item->id }}" onwheel="return false;" onkeypress="return event.charCode >= 48">
                                                    <input name="qty_pr[]" type="hidden" value="{{$item->qty - getQtyItemDphByPrItemId($item->id)}}" id='qty_po_{{$item->id}}'>
                                                    <input name="measure_id[]" type="hidden" value="{{ $item->measure }}">
                                                </td>
                                                <td>{{ $item->measure }}</td>
                                                <td>
                                                    <input type="text" name="price[]" class="form-control text-right {{$i}}currency price next_price" value="0" id="{{$i}}price_{{ $item->id }}">
                                                </td>
                                                <td>
                                                    <input type="number" name="diskon_item[]" class="form-control text-right {{$i}}diskon" value="0" id="{{$i}}discount_{{ $item->id }}" data-id="{{ $item->id }}" min="0" step="0.01" max="100" onwheel="return false;">
                                                </td>
                                                <td>
                                                    <input type="text" name="total_price[]" readonly class="form-control text-right {{$i}}totalPrice" id="{{$i}}totalPrice_{{ $item->id }}" value="0">
                                                </td>
                                            </tr>
                                            @php $no++ @endphp
                                        @endforeach
                                    @endif
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="7" class="font-bold text-right"><strong>NETTO <br><small>Setelah dikurangi Diskon</small></strong></td>
                                        <td colspan="1" class="font-weight-bold text-right"><span id="{{$i}}total" class="font-bold text-right"></span></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                @endfor
            </div>

            <!-- Action Buttons -->

            <div class="mt-3">
                <label>Catatan DPH </label>
                <input id="notes_dph" type="hidden" name="notes_dph" value="">
                <div style="width: 100%;">
                    <trix-editor input="notes_dph"></trix-editor>
                </div>
            </div>

            <div class="mt-4">
                <a href="{{ route('purchasing.dph.index') }}" class="btn btn-light text-uppercase fsz-sm fw-600">{{ trans('Cancel') }}</a>
                <input type="hidden" value="0" name="status">
                <input class="btn btn-danger text-uppercase fsz-sm fw-600" type="submit" name="publish" id="btn-submit-dph" value="Create">
            </div>
            {!! Form::close() !!}
        </div>
    </div>

    <!-- Modals -->
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

    function calculatePrice(index, id){
        var price     = $("#"+index+"price_"+id).val().replace(/\,/g,'');
        var diskon = $("#"+index+"discount_"+id).val();
        var qty = $("#"+index+"qty_"+id).val();
        if(diskon == 0) var total = parseFloat(price) * qty ;
        else var total = (parseFloat(price) - (parseFloat(price) * diskon/100))  * qty;
        $("#"+index+"totalPrice_"+id).val(total.toFixed(2).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ","));
    }

    function calculateSum(index){
        var price = $("."+index+"totalPrice");
        var discountPO = $("#"+index+"discountPO").val().replace(/\,/g,'');
        var discount_type = $("#"+index+"discount_type").val();
        var discount_item = $("#"+index+"discount_item").val();
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
        $("#"+index+"total").html(totalAll.toFixed(2).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ","));
    }

    function handleCheckboxAllChange(checkbox, index) {
        // Set all checkboxes with class .checkSingle to false and enable them
        const allSingleCheckboxes = document.querySelectorAll(`input[type="checkbox"].checkSingle_`);
        allSingleCheckboxes.forEach(function(singleCheckbox) {
            singleCheckbox.checked = false;  // Uncheck all .checkSingle checkboxes
            singleCheckbox.disabled = false; // Enable all .checkSingle checkboxes
            singleCheckbox.dispatchEvent(new Event('change')); // Trigger change event to update state if necessary
        });

        // Enable all checkboxes with id starting with "checkedAll"
        const allCheckboxes = document.querySelectorAll(`input[type="checkbox"][id^="checkedAll"]`);
        allCheckboxes.forEach(function(checkbox) {
            checkbox.disabled = false; // Enable all "checkedAll" checkboxes
        });

        // Now proceed with your existing logic
        if (checkbox.checked) {
            // Disable all other checkboxes with the same class (except the current one)
            allCheckboxes.forEach(function(otherCheckbox) {
                const otherIndex = otherCheckbox.id.match(/checkedAll(\d+)/)[1];
                if (otherIndex !== index.toString()) {
                    otherCheckbox.disabled = true;
                }
            });

            const checkboxesToCheck = document.querySelectorAll(`input[type="checkbox"].checkSingle${index}`);
            checkboxesToCheck.forEach(function(singleCheckbox) {
                if (!singleCheckbox.checked) {
                    singleCheckbox.checked = true;
                    singleCheckbox.dispatchEvent(new Event('change'));
                }
            });
        } else {
            // Enable all checkboxes again
            allCheckboxes.forEach(function(otherCheckbox) {
                otherCheckbox.disabled = false;
            });

            // Uncheck all the checkboxes related to the current row/item
            const checkboxesToUncheck = document.querySelectorAll(`input[type="checkbox"].checkSingle${index}`);
            checkboxesToUncheck.forEach(function(singleCheckbox) {
                if (singleCheckbox.checked) {
                    singleCheckbox.checked = false;
                    singleCheckbox.dispatchEvent(new Event('change'));
                }
            });
        }
    }




    function handleCheckboxChange(checkbox, itemId, index) {

        const row = checkbox.closest('tr');
        if (checkbox.checked) {
            row.style.backgroundColor = '#dbefe0';
            const rows = document.querySelectorAll('tr');
            rows.forEach(tr => {
                const checkboxInRow = tr.querySelector(`input[type="checkbox"][id*="checkbox_${itemId}"]`);
                if (checkboxInRow && checkboxInRow.id !== checkbox.id) {
                    tr.style.backgroundColor = '#e9e9e9';
                    checkboxInRow.disabled = true;
                }
            });
        } else {
            row.style.backgroundColor = '';
            const rows = document.querySelectorAll('tr');
            let isAnyChecked = false;
            rows.forEach(tr => {
                const checkboxInRow = tr.querySelector(`input[type="checkbox"][id*="checkbox_${itemId}"]`);
                if (checkboxInRow && checkboxInRow.checked) {
                    isAnyChecked = true;
                }
            });
            if (!isAnyChecked) {
                rows.forEach(tr => {
                    const checkboxInRow = tr.querySelector(`input[type="checkbox"][id*="checkbox_${itemId}"]`);
                    if (checkboxInRow) {
                        tr.style.backgroundColor = '';
                        checkboxInRow.disabled = false;
                    }
                });
            }
        }
    }

    $(document).ready(function() {
        <?php for($i = 1; $i <= $count_form; $i++){
            foreach ($pr_items as $item) { ?>
                $("#{{$i}}price_{{ $item->id }}").keyup(function(){
                    var id      = "{{ $item->id }}";
                    var index   = "{{$i}}";
                    calculatePrice(index, id)
                    calculateSum(index);
                });
                $("#{{$i}}qty_{{ $item->id }}").keyup(function(){
                    var id     = "{{ $item->id }}";
                    var index   = "{{$i}}";
                    calculatePrice(index, id)
                    calculateSum(index);
                });
                $("#{{$i}}discount_{{ $item->id }}").keyup(function(){
                    var id     = "{{ $item->id }}";
                    var index   = "{{$i}}";
                    if($(this).val() > 100) {
                        Swal.fire(
                            'Informasi',
                            'Maksimal Discount adalah 100%',
                            'warning'
                        );
                        $(this).val('0')
                    }
                    calculatePrice(index, id)
                    calculateSum(index);
                });

            <?php } ?>

            if($('#{{$i}}discount_type').val()=='1'){
                $("#{{$i}}discount_item" ).attr('max', '100');
            }

            $("#{{$i}}discount_type").select2().on('change', function() {
                var discountPO = $("#{{$i}}discountPO").val().replace(/\,/g,'');
                var index   = "{{$i}}";
                if($(this).val() == '0'){
                    $("#{{$i}}discount_item_wrapper").hide();
                    $("#{{$i}}discount_item").val('0').trigger('change');
                    $(".{{$i}}diskon").each(function(){
                        var id = $(this).attr('data-id');
                        $(".{{$i}}diskon").val(0);
                        $(".{{$i}}diskon").prop('readonly', false);
                        $("#{{$i}}row_discount_item").hide();
                        calculatePrice(index, id);
                    });
                } else {
                    $("#{{$i}}row_discount_item").show();
                    $("#{{$i}}discount_item_wrapper").show();
                    if(discountPO > 100) {
                        Swal.fire(
                            'Informasi',
                            'Maksimal Discount adalah 100%',
                            'warning'
                        );
                        $("#{{$i}}discountPO").val(0);
                    }
                }
                calculateSum(index);
            });


            $("#{{$i}}discount_item").on('change', function() {
                var discountPO = $("#{{$i}}discountPO").val().replace(/\,/g,'');
                var index = "{{$i}}";
                if ($(this).val() == '1') {
                    var discountValue = discountPO;
                    $(".{{$i}}diskon").each(function() {
                        var id = $(this).attr('data-id');
                        $(this).val(discountValue);
                        $(this).prop('readonly', true);
                        calculatePrice(index, id);
                    });
                } else {
                    $(".{{$i}}diskon").each(function() {
                        var id = $(this).attr('data-id');
                        $(this).val('0');
                        $(this).prop('readonly', false);
                        calculatePrice(index, id);
                    });
                }

                calculateSum(index);
            });


            $("#{{$i}}discount_item").on('change', function() {
                var index = "{{$i}}";
                if ($(this).val() == '0') {
                    $(".{{$i}}diskon").each(function() {
                        var id = $(this).attr('data-id');
                        $(this).val('0');
                        $(this).prop('readonly', false);
                        calculatePrice(index, id);
                    });
                }
                calculateSum(index);
            });

            $("#{{$i}}discountPO").on('keyup change', function() {
                var discountPO = $("#{{$i}}discountPO").val().replace(/\,/g, '');
                var index = "{{$i}}";
                var discountItemValue = $("#{{$i}}discount_item").val();
                if (discountItemValue === '1') {
                    $(".{{$i}}diskon").each(function() {
                        $(this).val(discountPO);
                        var id = $(this).attr('data-id');
                        calculatePrice(index, id);
                    });
                }
                calculateSum(index);
            });


            $("#{{$i}}discountPO").change(function(){
                if($('#{{$i}}discount_type').val()=='1'){
                    var discountPO = $("#{{$i}}discountPO").val().replace(/\,/g,'');
                    if(discountPO > 100){
                            Swal.fire(
                                'Informasi',
                                'Maksimal Discount adalah 100%',
                                'warning'
                            );
                            $("#{{$i}}discountPO").val(0);
                            $(".{{$i}}diskon").each(function(){
                                $(".{{$i}}diskon").val(0);
                            })
                        }
                    }
                }
            );


            $("#{{$i}}is_item_ppn").change(function() {
                var isItemPpnValue = $(this).val();
                var ppnValue = $("#{{$i}}ppn").val();

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

            $("#{{$i}}ppn").change(function() {
                var ppnValue = $(this).val();
                var isItemPpnValue = $("#{{$i}}is_item_ppn").val();
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

            $(".{{$i}}due_date_payment").datepicker().on('change', function() {
                checkDate($(this).datepicker('getDate'));
                $(this).valid();
            });

            $(".{{$i}}delivery_date").datepicker().on('change', function() {
                checkDate($(this).datepicker('getDate'));
            });

            $('.{{$i}}price_term_location').select2().on('change', function() {
                $(this).valid();
            });

            $('.{{$i}}price_term').select2().on('change', function() {
                $(this).valid();
            });

            // tidak ada class
            $('.{{$i}}po_term').select2().on('change', function() {
                $(this).valid();
            });

            $('.{{$i}}payment_term').select2().on('change', function() {
                $(this).valid();
            });

            $('.{{$i}}payment_method').select2().on('change', function() {
                $(this).valid();
            });

            $('.{{$i}}po_currency').select2().on('change', function() {
                $(this).valid();
            });

            $('.{{$i}}payment_method').val('BANK TRANSFER').change();
            $('.{{$i}}price_term_location').val('JAKARTA').change();
            $('.{{$i}}po_currency').val('IDR').change();
            $('.{{$i}}price_term').val('FRANCO').change();
            $('.{{$i}}po_note').val('9').change();

            $('.{{$i}}supplier_pic').select2().on('change', function() {
                $(this).valid();
            });

            $('.{{$i}}supplier').select2().on('change', function() {
                $(this).valid();
            });

            // MODAL
            $('.modalMd').off('click').on('click', function () {
                $('#modalMdContent').load($(this).attr('value'));
            });

            $('.modalMdPO').off('click').on('click', function () {
                $('#modalMdContentPO').load($(this).attr('value'));
            });

            $('.{{$i}}currency').inputmask({ 'alias': 'decimal', 'groupSeparator': ',', 'autoGroup': true, 'digits': 2, 'digitsOptional': false, 'placeholder': '0.00',allowMinus: false});

            $('#{{$i}}myonoffswitch').change(function() {
                if(this.checked) {
                    $("#{{$i}}po_term_id" ).attr('value', '1');
                }else{
                    $("#{{$i}}po_term_id" ).attr('value', '2');
                }
            });

            var sendExpensePpnValue = $('#{{$i}}send_expense_ppn').val();
            if (sendExpensePpnValue === '1') {
                $('#{{$i}}send_expense_ppn').val('1').trigger('change');
            } else {
                $('#{{$i}}send_expense_ppn').val('0').trigger('change');
            }

            initSupplierSelect("{{$i}}");

            <?php } ?>

            $(document).on('click', "#btn-submit-dph", function(e) {
                $('input[name="status"]').val('1');
                e.preventDefault();
                var _this = $(this);
                var form = _this.parents('form');
                <?php for($i = 1; $i <= $count_form; $i++){ ?>
                    var suppliers = $(".{{$i}}supplier").val();
                    var supplier_pic = $(".{{$i}}supplier_pic").val();
                    var payment_term = $(".{{$i}}payment_term").val();
                    var payment_method = $(".{{$i}}payment_method").val();
                    if (!suppliers || !supplier_pic || !payment_term || !payment_method) {
                        Swal.fire(
                            'Informasi',
                            'Semua data supplier harus terisi',
                            'warning'
                        );
                        return;
                    }
                <?php } ?>
                const totalItems = {{ count($pr_items) }};
                const checkedItems = document.querySelectorAll('input[type="checkbox"]:checked').length;
                if (checkedItems < totalItems) {
                    Swal.fire(
                        'Informasi',
                        'Terdapat item yang belum di checklist <br> untuk diajukan ke Approval DPH',
                        'warning'
                    );
                    return false;
                }
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
            function initSupplierSelect(index) {
                var supplier = $("." + index + "supplier");
                var supplier_pic = $("." + index + "supplier_pic");
                var payment_term = $("." + index + "payment_term");
                var payment_method = $("." + index + "payment_method");
                var ppnSelect = $('#' + index + 'ppn');
                var currencyValue = $('.' + index + 'po_currency');
                supplier.select2({
                    placeholder: 'Cari Supplier dengan mengetik 3 huruf...',
                    ajax: {
                        url: "{{ route('purchasing.get_supplier') }}",
                        dataType: 'json',
                        delay: 250,
                        processResults: function(data) {
                            return {
                                results: $.map(data, function(item) {
                                    return {
                                        text: item.name,
                                        id: item.id,
                                        ppn: item.is_ppn,
                                        term: item.is_payment,
                                        payment_method: item.is_payment_method,
                                        currency: item.is_currency
                                    };
                                })
                            };
                        },
                        cache: true
                    }
                }).on('change', function() {
                    $("#" + index + "supplierData").show();
                    var selectedData = supplier.select2('data')[0];
                    if (selectedData && selectedData.ppn !== undefined) {
                        ppnSelect.val(selectedData.ppn).trigger('change');
                    }
                    currencyValue.val(selectedData.currency).trigger('change');
                    updateSupplierDetails(selectedData, supplier_pic, payment_term, payment_method, index);
                });
            }

            function updateSupplierDetails(selectedData, supplier_pic, payment_term, payment_method, index) {
                var is_ppn = selectedData.ppn;
                var is_payment = selectedData.term;
                var is_payment_method = selectedData.payment_method;

                $.ajax({
                    url: "{{ route('purchasing.get_supplier_pic') }}/" + selectedData.id,
                    type: 'GET',
                    success: function(data) {
                        updateSupplierPicOptions(data, supplier_pic);
                    }
                });

                $.ajax({
                    url: "{{ route('purchasing.get_payment_term') }}",
                    type: 'GET',
                    success: function(data) {
                        updatePaymentTermOptions(data, payment_term, is_payment);
                    }
                });

                $.ajax({
                    url: "{{ route('purchasing.get_payment_method') }}",
                    type: 'GET',
                    success: function(data) {
                        updatePaymentMethodOptions(data, payment_method, is_payment_method);
                    }
                });

                $('#' + index + 'ppn').val(is_ppn === 11 ? '11' : (is_ppn === 12 ? '12' : '0') ).trigger('change');
                checkPPNStatus(index);
            }

            function updateSupplierPicOptions(data, supplier_pic) {
                supplier_pic.empty().append($("<option></option>").attr("value", "").text("Silahkan pilih..."));
                $.each(data, function(value, key) {
                    supplier_pic.append($("<option></option>").attr("value", value).text(key));
                });
                supplier_pic.select2();
            }

            function updatePaymentTermOptions(data, payment_term, is_payment) {
                payment_term.empty();
                $.each(data, function(value, key) {
                    payment_term.append($("<option></option>").attr("value", value).text(key));
                });
                payment_term.select2().val(is_payment).trigger('change');
            }

            function updatePaymentMethodOptions(data, payment_method, is_payment_method) {
                payment_method.empty();
                $.each(data, function(value, key) {
                    payment_method.append($("<option></option>").attr("value", value).text(key));
                });
                payment_method.select2().val(is_payment_method).trigger('change');
            }

            function checkPPNStatus(index) {
                var ppnSelect = $('#' + index + 'ppn');
                var isItemPpnSelect = $('#' + index + 'is_item_ppn');
                var ppnValue = ppnSelect.val();
                var isItemPpnValue = isItemPpnSelect.val();
                if (ppnValue !== '0') {
                    if (isItemPpnValue === '1') {
                        Swal.fire(
                            'Informasi',
                            'PPN pada item harga satuan aktif, silahkan gunakan salah satu PPN pada supplier atau PPN pada item harga satuan',
                            'info'
                        );
                        ppnSelect.val('0');
                    }
                } else {
                    ppnSelect.val('0');
                }
            }


        }
    );
</script>
@stop
