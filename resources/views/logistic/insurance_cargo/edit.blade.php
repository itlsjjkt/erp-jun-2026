@extends('layouts.app')

@section('page-header')
    Asuransi Cargo
@stop

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('logistic.insurance_cargo.index') }}"> Asuransi Cargo</a></li>
        <li class="breadcrumb-item active" aria-current="page">Edit</li>
    </ol>
@endsection


@section('content')

<div class="mB-40">
    {!! Form::model($insurance, [
            'action' => ['Logistic\InsuranceCargoController@update', $insurance->id],
            'method' => 'put', 
            'class' => 'form-horizontal mt-3',
            'files' => true
        ])
    !!}
        <div class="bgc-white p-30 bd">
            <h6>Edit Asuransi: {{ $insurance->doc_no }}</h6>
            <hr class='mB-30'>

        <div class="form-group row">
            <label class="col-sm-2 ">Ekspedisi Oleh <span class="text-danger">*</span></label>
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
                {!! Form::text('risk_location', old('risk_location'), ['class' => 'form-control', 'placeholder' => '']) !!}
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

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th style="width:50px">No</th>
                    <th>Nama Barang</th>
                    <th>Catatan/Spesifikasi</th>
                    <th class="text-center">QTY</th>
                    <th class="text-center">Harga</th>
                    <th class="text-center">Supplier</th>
                    <th class="text-center" style="width:150px">Diskon (%)</th>
                    <th class="text-center" style="width:100px">PPN</th>
                </tr>
            </thead>
            <tbody>
                    @php
                        $no = 1
                    @endphp
                    @foreach ($insurance_items as $val)

                        <tr>
                            <input name="ins_item_id[]" type="hidden" value="{{ $val->id }}">
                            <td>{{ $no }}</td>
                            <td>
                                {{ $val->productCode }} - {{ $val->product }} <br><small>PN/SPEC: {{ $val->productPartNumber }} |  Brand: {{ $val->productBrand }}</small>
                            </td>
                            <td>{{ $val->notes }}</td>
                            <td>{{ $val->qtyKoli }} {{ $val->measure }}</td>
                            <td>
                                {{ number_format($val->price ,2,".",',') }}
                            </td>
                            <td>{{ $val->supplier }}</td>
                            <td><input type="text"  name="discount[]"  id="diskon_{{ $no }}" value="{{ $val->discount }}"  class="form-control text-right diskon" max="100" step="0.1" min="0" > </td>
                            <td>
                                <select name="ppn[]" class="form-control">
                                    <option value='0' {{ $val->ppn == 1 ? 'selected' : '' }}>Tidak</option>
                                    <option value='1' {{ $val->ppn == 1 ? 'selected' : '' }}>Ya</option>
                                </select>
                            </td>
                        </tr>
                    @php
                        $no++
                    @endphp
                    @endforeach
            </tbody>
        </table>
        <hr>

        <div class="mt-4">
            <a href="{{ route('logistic.insurance_cargo.index') }}" class="btn btn-light text-uppercase fsz-sm fw-600">{{ trans('Cancel') }}</a>
            <input class="btn btn-info text-uppercase fsz-sm fw-600" type="submit" name="save" value="Save as Draft"  id="btn-draft">
            @if($insurance->status==0)
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

