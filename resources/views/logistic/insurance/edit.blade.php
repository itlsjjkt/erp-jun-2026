@extends('layouts.app')

@section('page-header')
    Asuransi
@stop

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('logistic.insurance.index') }}"> Asuransi</a></li>
        <li class="breadcrumb-item active" aria-current="page">Edit</li>
    </ol>
@endsection


@section('content')

<div class="mB-40">
    {!! Form::model($insurance, [
            'action' => ['Logistic\InsuranceController@update', $insurance->id],
            'method' => 'put', 
            'class' => 'form-horizontal mt-3',
            'files' => true
        ])
    !!}
        <div class="bgc-white p-30 bd">
        <h6>Edit Asuransi: {{ $insurance->doc_no }}</h6>
        <hr class='mB-30'>
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
                        @php
                        $previousNoSPB = null;
                        @endphp
                        @foreach($insurance_items as $val)
                            @if ($val->noSPB != $previousNoSPB)
                                <li>{{ $val->noSPB }}</li>
                            @endif
                            @php
                                $previousNoSPB = $val->noSPB;
                            @endphp
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
        <div class="form-group row">
            <div class="col-sm-12">
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
                            <td> {!! Form::text('prepared_by', old('prepared_by'), ['class' => 'form-control text-center', 'placeholder' => '','required' =>'']) !!} </td>
                            <td> {!! Form::text('checked_by_1', old('checked_by_1'), ['class' => 'form-control text-center', 'placeholder' => '','required' =>'']) !!} </td>
                            <td> {!! Form::text('checked_by_2', old('checked_by_2'), ['class' => 'form-control text-center', 'placeholder' => '','required' =>'']) !!} </td>
                            <td> {!! Form::text('known_by_1', old('known_by_1'), ['class' => 'form-control text-center', 'placeholder' => '','required' =>'']) !!} </td>
                            <td> {!! Form::text('known_by_2', old('known_by_2'), ['class' => 'form-control text-center', 'placeholder' => '','required' =>'','readonly']) !!} </td>
                            <td> {!! Form::text('known_by_3', old('known_by_3'), ['class' => 'form-control text-center', 'placeholder' => '','required' =>'','readonly']) !!} </td>
                            <td> {!! Form::text('received_by', old('received_by'), ['class' => 'form-control text-center', 'placeholder' => '','required' =>'']) !!} </td>
                            <td> {!! Form::text('approved_by', old('approved_by'), ['class' => 'form-control text-center', 'placeholder' => '','required' =>'','readonly']) !!} </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="alert alert-info mT-3">
            Daftar Surat Pengantar Barang (SPB) yang akan dibuatkan Asuransi. 
        </div>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th class="text-center" colspan="4">DIKIRIM</th>
                    <th class="text-center" colspan="7">ASURANSI</th>
                </tr>
                <tr>
                    <th rowspan="2" style="width:200px">Nama Barang</th>
                    <th rowspan="2" style="width:300px">Spesifikasi</th>
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
            <tbody>
                @php
                    $no = 1
                @endphp
                <?php $totalAll = 0; ?>
                @foreach($insurance_items as $val)
                <tr>
                    <input name="ins_item_id[]" type="hidden" value="{{ $val->id }}">
                    <td>{{ '['.$val->productCode.']' }}<br>{{ $val->product }} <br><small>Brand: {{ $val->productBrand }}</td>
                    <td>{{ $val->productPartNumber }}</td>
                    <td class="text-center">{{ $val->qtyKoli }}</td>
                    <td class="text-center">{{ $val->measure }}</td>
                    <td style="text-align: right">
                        <input type="hidden" name="price[]" value="{{$val->price}}">
                        <div class="currency" data-content="{{$val->symbol.'.'}}">{{number_format($val->price,2,",",'.')}}</div>
                    </td>
                    <td>
                        <input class="form-control text-center" type="number"  name="discount[]"  id="diskon_{{ $val->idKoli }}" value="{{$val->discount}}" min="0" max="100"> 
                    </td>
                    <td>
                        <input class="form-control text-center" type="number"  name="ppn[]"  id="ppn_{{ $val->idKoli }}" value="{{$val->ppn}}" min="0" max="100"> 
                    </td>
                    <td>
                        <?php 
                            $total_ = 0;
                            $total_ = $val->price - ($val->price * $val->discount/100);
                            $total_ += ($total_*$val->ppn/100);
                            $total_ = $total_ * $val->qtyKoli;
                        ?>
                        <input class="form-control text-right total_" style="text-align: left;" id="total_{{$val->idKoli}}" readonly type="text" value="{{$val->symbol.'. '.(number_format($total_ ,2,",",'.'))}}">
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <hr>

        <div class="mt-4">
            <a href="{{ route('logistic.insurance.index') }}" class="btn btn-light text-uppercase fsz-sm fw-600">{{ trans('Cancel') }}</a>
            <input class="btn btn-info text-uppercase fsz-sm fw-600" type="submit" name="save" value="Save as Draft"  id="btn-draft">
            @if($insurance->status==0 || $insurance->status==4)
                <input type="hidden" value="0" name="status">
                <input class="btn btn-danger text-uppercase fsz-sm fw-600 float-right"  type="submit" name="publish" id="btn-submit" value="Publish">
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
            $('input[name="status"]').val('0');
        });

    });
    </script>
@stop

