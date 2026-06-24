@extends('layouts.app')

@section('page-header')
    Asuransi Cargo
@stop

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('logistic.insurance_cargo.index') }}">Asuransi Cargo</a></li>
        <li class="breadcrumb-item active" aria-current="page">Tambah</li>
    </ol>
@endsection


@section('content')
    {!! Form::open(['method' => 'POST', 'route' => ['logistic.insurance_cargo.store'], 'id' => 'formInsurance']) !!}
    <input type="hidden" name="spbID" value="{{ $spb_id }}">
	<div class="bgc-white p-30 bd">

        <h6 class='mT-10'>Form Asuransi Cargo</h6>
        <hr>
   
        <div class="form-group row">
            <label class="col-sm-2 ">Shipper <span class="text-danger">*</span></label>
            <div class="col-sm-4">
                {!! Form::select('shipper_by', $ekspedisi, old('shipper_by'), ['class' => 'form-control select2', 'required' => '']) !!}
            </div>
        </div>

        <div class="form-group row">
            <label class="col-sm-2 ">Periode <span class="text-danger">*</span></label>
            <div class="col-sm-4">
                {!! Form::text('period', old('period'), ['class' => 'form-control datepicker ', 'placeholder' => '', 'required' => '']) !!}
            </div>
        </div>
        <div class="form-group row">
            <label class="col-sm-2 ">Risk Location<span class="text-danger">*</span></label>
            <div class="col-sm-4">
                {!! Form::text('risk_location', old('risk_location'), ['class' => 'form-control', 'placeholder' => '','required' => '']) !!}
            </div>
        </div>
        <div class="form-group row">
            <label class="col-sm-2 ">Catatan </label>
            <div class="col-sm-8">
                {!! Form::textarea('notes', old('notes'), ['class' => 'form-control', 'placeholder' => '','rows' => 2]) !!}
            </div>
        </div>

        <div class="form-group row">
            <div class="col-sm-12">
                <label>Approval </label>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th rowspan="2" class="text-center">Prepared By</th>
                            <th rowspan="2" class="text-center">Checked By</th>
                            <th rowspan="2" class="text-center">Approved By</th>
                            <th colspan="2" class="text-center">Checked By</th>
                            <th rowspan="2" class="text-center">Received By</th>
                            <th rowspan="2" class="text-center">Insurance Officer </th>
                        </tr>
                        <tr>
                            <th class="text-center">Purchasing 1</th>
                            <th class="text-center">Purchasing 2</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td> {!! Form::text('prepared_by', old('prepared_by'), ['class' => 'form-control', 'placeholder' => '','required' =>'']) !!} </td>
                            <td> {!! Form::text('checked_by', old('checked_by'), ['class' => 'form-control', 'placeholder' => '','required' =>'']) !!} </td>
                            <td> {!! Form::text('approved_by', old('approved_by'), ['class' => 'form-control', 'placeholder' => '','required' =>'']) !!} </td>
                            <td> {!! Form::text('checked_purchasing_1', old('checked_purchasing_1'), ['class' => 'form-control', 'placeholder' => '','required' =>'']) !!} </td>
                            <td> {!! Form::text('checked_purchasing_2', old('checked_purchasing_2'), ['class' => 'form-control', 'placeholder' => '','required' =>'']) !!} </td>
                            <td> {!! Form::text('received_by_1', old('received_by_1'), ['class' => 'form-control', 'placeholder' => '','required' =>'']) !!} </td>
                            <td> {!! Form::text('received_by_2', old('received_by_2'), ['class' => 'form-control', 'placeholder' => '','required' =>'']) !!} </td>
                        </tr>
                    </tbody>
                </table>
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
                                    <input name="spb_id[]" type="hidden" value="{{ $item->id }}">
                                    <th class="bg-light">{{ $item->doc_no }}</th>
                                </tr>
                                <tr>
                                    <td>
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th style="width:50px">All <br><input class="magic-checkbox" name="checkedAll" id="checkedAll_{{ $checkbox_item }}" type="checkbox"><label for="checkedAll_{{ $checkbox_item }}"></label> </th>
                                                    <th style="width:200px">Nama Barang</th>
                                                    <th>Spesifikasi</th>
                                                    <th class="text-center">QTY</th>
                                                    <th class="text-center">No. DPM</th>
                                                    <th class="text-center">No. PO</th>
                                                    <th class="text-center">Supplier</th>
                                                    <th class="text-center">Harga</th>
                                                    <th class="text-center" style="width:150px">Diskon(%)</th>
                                                    <th class="text-center" style="width:100px !important">PPN</th>
                                                </tr>
                                            </thead>
                                            <?php
                                            $spbitem = getSPBInsurance($item->id,'cargo'); ?>
                                            <tbody>
                                                    @php
                                                        $no = 1
                                                    @endphp
                                                    @foreach ($spbitem as $val)
                                                        <tr>
                                                            <input name="spb_item_id[]" type="hidden" value="{{ $val->idKoli }}">
                                                            <td><input type="checkbox" name="iscreateInsurance[]" class="checkSingle_{{ $checkbox_item }} magic-checkbox"  value="{{ $val->idKoli }}" id="id_{{ $val->idKoli }}"> <label for="id_{{ $val->idKoli }}"></label></td>
                                                            <td>
                                                                {{ $val->productCode }} - {{ $val->product }} <br><small>PN/SPEC: {{ $val->productPartNumber }} | Brand: {{ $val->productBrand }}</small>
                                                            </td>
                                                            <td>{{ $val->specification }}</td>
                                                            <td>{{ $val->qtyKoli }} {{ $val->measure }}</td>
                                                            <td>{{ $val->noDPM }}</td>
                                                            <td>{{ $val->noPO }}</td>
                                                            <td>{{ $val->supplier }}</td>
                                                            <td>
                                                                {{ number_format($val->price ,2,".",',') }}
                                                                <input type="hidden" name="price[]"  id="price_{{ $no }}" value="{{ $val->price }}"> 
                                                            </td>
                                                            <td><input type="number"  name="discount[]"  id="diskon_{{ $no }}" value="0.0" step="0.1" min="0" max="100"   class="form-control text-right diskon" onwheel="return false;"> </td>
                                                            <td>
                                                                <select name="ppn[]" class="form-control">
                                                                    <option value='0'>Tidak</option>
                                                                    <option value='1'>Ya</option>
                                                                </select>
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
        <a href="{{ route('logistic.insurance_cargo.index') }}" class="btn btn-light text-uppercase fsz-sm fw-600">{{ trans('Cancel') }}</a>
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
