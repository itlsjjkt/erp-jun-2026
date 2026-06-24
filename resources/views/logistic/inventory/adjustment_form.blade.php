@extends('layouts.app')

@section('page-header')
    Adjustment Stock 
@stop

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('logistic.inventory.index') }}">Inventory</a></li>
        <li class="breadcrumb-item active" aria-current="page">Adjustment Stock</li>
    </ol>
@endsection


@section('content')
<div class="row mB-40">

	<div class="col-sm-12">
        <div class="bgc-white p-30 bd">
            <h6><a class="float-left" href="{{ route('logistic.inventory.adjustment',['id' => Hashids::encode($inventory->id)]) }}"><i class="ti-arrow-left mR-10"></i></a> Kembali</h6>
            <hr class="mB-30">
            {!! Form::model($inventory, [
                    'route' => ['logistic.inventory.adjustment.store'],
					'method' => 'post', 
					'files' => true
				])
            !!}
                {{ csrf_field() }}
                <input type="hidden" value="{{ $inventory->id }}"  name="inventory_id" class="form-control">
                <input type="hidden" value="{{ $inventory->locationID }}"  name="location_id" class="form-control">
                <div class="row">
                    <label class="col-sm-2">Inventory ID </label>
                    <div class="col-sm-4">: {{ $inventory->id }}</div>
                </div>
                <div class="row">
                    <label class="col-sm-2">No. Rak </label>
                    <div class="col-sm-6">
                        : {{ $inventory->code_rack }}
                    </div>
                </div>
                <div class="row">
                    <label class="col-sm-2">Nama Produk </label>
                    <div class="col-sm-6">
                       : {{ $inventory->productCode }} - {{ $inventory->productName }}
                    </div>
                </div>
                <div class="row">
                    <label class="col-sm-2">PN/SPEC  </label>
                    <div class="col-sm-6">
                        : {{ $inventory->productPartNumber }}
                    </div>
                </div>
                <div class="row">
                    <label class="col-sm-2">Lokasi Warehouse </label>
                    <div class="col-sm-6">
                        : {{ $inventory->location }}
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-2">Stok </label>
                    <div class="col-sm-6">
                        : <span class="font-weight-bold bd p-10 fsz-lg">{{ $inventory->stock_onhand }}</span> {{ $inventory->unit }}
                       <input type="hidden" name="qty_awal" value="{{ $inventory->stock_onhand }}" id="qty_awal">
                    </div>
                </div>
                <hr>
                <div class="row mt-5">
                    <div class="col-6">
                        <div class="row">
                            <label class="col-sm-4">Nama Pemeriksa</label>
                            <div class="col-sm-6">
                                {!! Form::text('operator', old('operator'), ['class' => 'form-control', 'placeholder' => '', 'required' => '','id' => 'qty_fisik']) !!}
                                <p class="help-block"></p>
                                @if($errors->has('operator'))
                                    <p class="help-block">
                                        {{ $errors->first('operator') }}
                                    </p>
                                @endif
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-sm-4">Qty Fisik </label>
                            <div class="col-sm-6">
                                {!! Form::number('qty_fisik', old('qty_fisik'), ['class' => 'form-control', 'placeholder' => '', 'required' => '','id' => 'qty_fisik']) !!}
                                <p class="help-block"></p>
                                @if($errors->has('qty_fisik'))
                                    <p class="help-block">
                                        {{ $errors->first('qty_fisik') }}
                                    </p>
                                @endif
                            </div>
                        </div>

                        <div class="row">
                            <label class="col-sm-4"> <kbd class="btn-danger"><i class="fa fa-file-pdf-o"></i> PDF</kbd> <br> Attachment Berita Acara</label>
                            <div class="col-sm-4">
                                {!! Form::myFile('file', '',['class' => '']) !!}
                            </div>
                        </div>
                       
                    </div>
                    <div class="col-6">

                        <div class="form-group row">
                            <label class="col-sm-3">Alasan <span class="text-danger">*</span></label>
                            <div class="col-sm-8">
                                {!! Form::textarea('reason', old('reason'), ['class' => 'form-control', 'rows' => 3, 'placeholder' => '', 'required' => '']) !!}
                                <p class="help-block"></p>
                                @if($errors->has('reason'))
                                <p class="help-block">
                                    {{ $errors->first('reason') }}
                                </p>
                                @endif
                            </div>
                        </div>

                    </div>
                </div>
                <hr>
                <div class="form-group row">
                    <div class="col-sm-12 text-right">
                        <a href="{{ route('logistic.inventory.index') }}" class="btn btn-light text-uppercase fsz-sm fw-600">{{ trans('Cancel') }}</a>
                        <input class="btn btn-success text-uppercase fsz-sm fw-600" type="submit" name="publish" id="btn-submit" value="Submit">
                    </div>
                </div>
				
			{!! Form::close() !!}
		</div>  
	</div>
</div>
	
@stop

@section('js')
    <script>
        $(document).ready(function() {
      

        });
    </script>
@stop