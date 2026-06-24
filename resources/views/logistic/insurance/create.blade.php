@extends('layouts.app')

@section('page-header')
    Asuransi
@stop

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('logistic.insurance.index') }}">Asuransi</a></li>
        <li class="breadcrumb-item active" aria-current="page">Tambah</li>
    </ol>
@endsection


@section('content')
    {!! Form::open(['method' => 'POST', 'route' => ['logistic.insurance.store'], 'id' => 'formInsurance']) !!}
    <input type="hidden" name="spbID" value="{{ $spb_id }}">
	<div class="bgc-white p-30 bd">
        <h6 class='mT-10'>Form Asuransi</h6>
        <hr>
        <div class="col-12 row">
            <div class="col-6">
                <div class="form-group row">
                    <label class="col-sm-4 ">Company<span class="text-danger">*</span></label>
                    <div class="col-sm-8">
                        {!! Form::text('company', old('company'), ['class' => 'form-control ', 'placeholder' => '', 'required' => '']) !!}
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-4 ">Project<span class="text-danger">*</span></label>
                    <div class="col-sm-8">
                        {!! Form::text('project', old('project'), ['class' => 'form-control ', 'placeholder' => '', 'required' => '']) !!}
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-4 ">Manifest Number</label>
                    <div class="col-sm-8">
                        @foreach ($spb as $item)
                            <li>{{$item->doc_no}}</li>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="col-6">
                <div class="form-group row">
                    <label class="col-sm-4 ">Ekspedisi / Forwarder <span class="text-danger">*</span></label>
                    <div class="col-sm-8">
                        {!! Form::text('expedition_forwarder', old('expedition_forwarder'), ['class' => 'form-control', 'required' => '']) !!}
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-4 ">Risk Location<span class="text-danger">*</span></label>
                    <div class="col-sm-8">
                        {!! Form::text('risk_location', old('risk_location'), ['class' => 'form-control', 'placeholder' => '','required' => '']) !!}
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-4 ">ETD / ETA <span class="text-danger">*</span></label>
                    <div class="col-sm-8">
                        {!! Form::date('etd_eta', old('etd_eta'), ['class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-4 ">Ready To Shipped By <span class="text-danger">*</span></label>
                    <div class="col-sm-8">
                        {!! Form::text('shipped_by', old('shipped_by'), ['class' => 'form-control ', 'placeholder' => '', 'required' => '']) !!}
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12">
            <div class="form-group row">
                <div class="col-sm-12">
                    <br>
                    <label>APPROVAL </label>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th class="text-center">Prepared By</th>
                                <th colspan="2" class="text-center">Checked By</th>
                                <th colspan="3" class="text-center">Mengetahui</th>
                                <th class="text-center">Received By</th>
                                <th class="text-center">Menyetujui</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><input type="text" name="prepared_by" value="BUDIMAN" class="form-control text-center"></td>
                                <td><input type="text" name="checked_by_1" value="FANDI RACHMAD" class="form-control text-center"></td>
                                <td><input type="text" name="checked_by_2" value="DODDIE PRADHONO" class="form-control text-center"></td>
                                <td><input type="text" name="known_by_1" value="THAM ARVIN SETYANTO" class="form-control text-center"></td>
                                <td><input readonly type="text" name="known_by_2" value="PURCHASING CHECKER" class="form-control text-center"></td>
                                <td><input readonly type="text" name="known_by_3" value="PURCHASING CHECKER" class="form-control text-center"></td>
                                <td><input type="text" name="received_by" value="MUKTI SE" class="form-control text-center"></td>
                                <td><input readonly type="text" name="approved_by" value="HEAD OF PURCHASING" class="form-control text-center"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="alert alert-info mT-3">
           Daftar Surat Pengantar Barang (SPB) yang akan dibuatkan Asuransi. 
        </div>
        <div class="table-responsive">
            <table class="table table-bordered mt-2">
                <tbody class="item_form">
                    @if (count($spb) > 0)
                        @php
                            $checkbox_item = 1
                        @endphp
                        @foreach ($spb as $item)
                            <tr>
                                <th class="bg-light">{{ $item->doc_no }}</th>
                            </tr>
                            <tr>
                                <td>
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th class="text-center" colspan="5">DIKIRIM</th>
                                                <th class="text-center" colspan="7">ASURANSI</th>
                                            </tr>
                                            <tr>
                                                <th rowspan="2" style="width:50px; height:60px;">All <br><input class="magic-checkbox" name="checkedAll" id="checkedAll_{{ $checkbox_item }}" type="checkbox"><label for="checkedAll_{{ $checkbox_item }}"></label> </th>
                                                <th rowspan="2" style="width:200px">Nama Barang</th>
                                                <th rowspan="2" style="width:300px">Part Number / SPEC</th>
                                                <th colspan="2" class="text-center">Item</th>
                                                <th rowspan="2" class="text-center" style="width: 150px !important;">Harga / Item</th>
                                                <th rowspan="2" class="text-center" style="width:60px">Disc(%)</th>
                                                <th rowspan="2" class="text-center" style="width:60px">PPN(%)</th>
                                                <th rowspan="2" class="text-center" style="width: 200px !important;">Total</th>
                                            </tr>
                                            <tr>
                                                <th class="text-center" style="width: 100px !important;">QTY</th>
                                                <th class="text-center" style="width: 80px !important;">UOM</th>
                                            </tr>
                                        </thead>
                                        <?php
                                        $spbitem = getProductItemSPBInsurance($item->id);?>
                                        <tbody>
                                            @php
                                                $no = 1   
                                            @endphp
                                            {{-- <?php $totalAll = 0 ?> --}}
                                            @foreach ($spbitem as $val)
                                                <tr>
                                                    <input name="spb_item_id[]" type="hidden" value="{{ $val->idKoli }}">
                                                    <td><input type="checkbox" name="iscreateInsurance[]" class="checkSingle_{{ $checkbox_item }} magic-checkbox"  value="{{ $val->idKoli }}" id="id_{{ $val->idKoli }}"> <label for="id_{{ $val->idKoli }}"></label></td>
                                                    <td>
                                                        {{ '['.$val->productCode.']' }} <br> {{ $val->product }} <br><small>Brand: {{ $val->productBrand }}</small>
                                                    </td>
                                                    <td>{{ $val->productPartNumber }}</td>
                                                    <td class="text-center">{{ $val->qtyKoli }}</td>
                                                    <td class="text-center">{{ $val->measure }}</td>
                                                    <td>
                                                        {{ $val->symbol.'. '.(number_format($val->price ,2,",",'.')) }}
                                                        <input type="hidden" name="price[]"  id="price_{{ $val->idKoli }}" value="{{ $val->price }}"> 
                                                    </td>
                                                    <td style="width: 50px !important;">
                                                        <?php 
                                                        $diskon = 0;
                                                        $harga = 0;
                                                        if ($val->price == 0) {
                                                            $harga = 1;
                                                        }
                                                        else{
                                                            $harga = $val->price;
                                                        }
                                                        if($val->po_discount_type == 0){
                                                            $diskon = ((1-($val->price_discount/$harga))*100);
                                                        }
                                                        else{
                                                            $diskon = $val->discount;
                                                        }
                                                        ?>
                                                        <input class="form-control text-center discount_" type="number"  name="discount[]"  id="discount_{{ $val->idKoli }}" value="{{$diskon}}" min="0" max="100"> 
                                                    </td>                                                            
                                                    <td style="width: 50px !important;">
                                                        <?php if($val->ppn == null){
                                                            $val->ppn = 0;}?>
                                                        <input class="form-control text-center ppn_" type="number"  name="ppn[]"  id="ppn_{{ $val->idKoli }}" value="{{$val->ppn}}" min="0" max="100"> 
                                                    </td>                                                          
                                                    <td id="total_{{$val->idKoli}}">
                                                        <?php 
                                                            $hargaAsli_ = 0;
                                                            $ppn_ = 0;
                                                            $total_ = 0;
                                                            $total_ = $val->price * (1-($diskon/100));
                                                            $total_ += ($total_*$val->ppn/100);
                                                            $total_ = $total_ * $val->qtyKoli;
                                                        ?>
                                                        <input class="form-control text-right total_" style="text-align: left;" id="total_{{$val->idKoli}}" readonly type="text" value="{{$val->symbol.'. '.(number_format($total_ ,2,",",'.'))}}">
                                                    </td>
                                                </tr>
                                                @php
                                                $no++
                                                @endphp
                                            @endforeach
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                            @php
                                $checkbox_item++
                            @endphp
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>   
    </div>
    <div class="mt-4">
        <a href="{{ route('logistic.insurance.index') }}" class="btn btn-light text-uppercase fsz-sm fw-600">{{ trans('Cancel') }}</a>
        <input class="btn btn-info text-uppercase fsz-sm fw-600" type="submit" name="save" value="Save as Draft"  id="btn-draft">
        <input type="hidden" value="0" name="status">
        <input class="btn btn-danger text-uppercase fsz-sm fw-600 float-right" type="submit" name="publish" id="btn-submit" value="Publish">
    </div>
    {!! Form::close() !!}

   
@stop


@section('js')

<script  type='text/javascript'>

	$(document).ready(function() {
        
        $(document).on("click", "#btn-submit", function(e) {
            $('input[name="status"]').val('1');
        });

        $(document).on("click", "#btn-draft", function(e) {
            $('input[name="status"]').val('0');
        });

        $("#formInsurance").validate({ 
            rules: { 
                    "iscreateInsurance[]": { 
                        required: true, 
                        minlength: 1 
                    }
            }, 
            messages: { 
                    "iscreateInsurance[]": "Minimal Checklist 1 Item"
            } 
        }); 
        @if (count($spb) > 0)
            @php
                $no = 1;
            @endphp
            @foreach ($spb as $val)
                $("#checkedAll_{{ $no }}").change(function(){
                    if(this.checked){
                        $(".checkSingle_{{ $no }}").each(function(){
                            this.checked=true;
                        })              
                    }else{
                        $(".checkSingle_{{ $no }}").each(function(){
                            this.checked=false;
                        })              
                    }
                });
                $(".checkSingle_{{ $no }}").click(function () {
                    if ($(this).is(":checked")){
                        var isAllChecked = 0;
                        $(".checkSingle_{{ $no }}").each(function(){
                            if(!this.checked)
                            isAllChecked = 1;
                        })              
                        if(isAllChecked == 0){ 
                            $("#checkedAll_{{ $no }}").prop("checked", true); 
                        }     
                    }else {
                        $("#checkedAll_{{ $no }}").prop("checked", false);
                    }
                });
                @php
                    $no++;
                @endphp
            @endforeach
        @endif
        $('.currency').mask('000.000.000.000', {reverse: true});    
    });
    </script>
@stop
