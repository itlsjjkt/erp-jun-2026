@extends('layouts.app')

@section('page-header')
    Write Off Stock 
@stop

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('logistic.inventory.index') }}">Inventory</a></li>
        <li class="breadcrumb-item active" aria-current="page">Write Off Stock</li>
    </ol>
@endsection


@section('content')
<div class="row mB-40">

	<div class="col-sm-12">
        <div class="bgc-white p-30 bd">
          
            <h6><a class="float-left" href="{{ route('logistic.inventory.index') }}"><i class="ti-arrow-left mR-10"></i></a> Kembali</h6>
            <hr class="mB-30">
           
                <div class="row">
                    <label class="col-sm-3 text-right">No. Rak </label>
                    <div class="col-sm-2">
                        : {{ $inventory->code_rack }}
                    </div>
                </div>
                <div class="row">
                    <label class="col-sm-3 text-right">Nama Produk </label>
                    <div class="col-sm-6">
                       : {{ $inventory->productCode }} - {{ $inventory->productName }}
                    </div>
                </div>
                <div class="row">
                    <label class="col-sm-3 text-right">PN/SPEC </label>
                    <div class="col-sm-4">
                        : {{ $inventory->productPartNumber }}
                    </div>
                </div>
                <div class="row">
                    <label class="col-sm-3 text-right">Lokasi Warehouse </label>
                    <div class="col-sm-4">
                        : {{ $inventory->location }}
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-3 text-right">Stok </label>
                    <div class="col-sm-2">
                       : <span class="font-weight-bold bd p-10 fsz-lg">{{ $inventory->stock_onhand }}</span> {{ $inventory->unit }}
                       <input type="hidden" name="qty_awal" value="{{ $inventory->stock_onhand }}" id="qty_awal">
                    </div>
                </div>

                <hr>

                @if($wo)
                    <a  onclick="printExternal()" class="btn btn-outline border-dark float-right text-uppercase fsz-sm fw-600">
                        <i class="ti-printer"></i> Print
                    </a>
                    <table id="dataTables" class="table table-striped table-bordered" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th>Nomor</th>
                                <th>Nama Pemeriksa</th>
                                <th>Reason</th>
                                <th>Tanggal Input</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>{{ $wo->doc_no }}</td>
                                <td>{{ $wo->operator }}</td>
                                <td>{{ $wo->reason }}</td>
                                <td>{{ date('d F Y',strtotime( $wo->created_at)) }} </td>
                        </tbody>
                    </table>

                        <script>
                            function printExternal() {
                                var url ="{{ route('logistic.inventory.writeoff.print',Hashids::encode($wo->id)) }}";
                                var printWindow = window.open(url, 'Print', 'left=200, top=200, width=950, height=800, toolbar=0, resizable=0');
                                printWindow.addEventListener('load', function() {
                                    printWindow.print();
                                }, true);
                            }   
                        </script>
                @else

                    {!! Form::model($inventory, [
                        'route' => ['logistic.inventory.writeoff.store'],
                        'method' => 'post', 
                        'files' => true
                    ])!!}
                        {{ csrf_field() }}
                        <input type="hidden" value="{{ $inventory->id }}"  name="inventory_id" class="form-control">
                        <input type="hidden" value="{{ $inventory->locationID }}"  name="location_id" class="form-control">
                        <input type="hidden" value="{{ $inventory->stock_onhand }}"  name="stock" class="form-control">
                        

                        <div class="mt-5 form-group row">
                            <label class="col-sm-3 col-form-label text-right">Nama Pemeriksa</label>
                            <div class="col-sm-4">
                                {!! Form::text('operator', old('operator'), ['class' => 'form-control', 'placeholder' => '', 'required' => '','id' => 'qty_fisik']) !!}
                                <p class="help-block"></p>
                                @if($errors->has('operator'))
                                    <p class="help-block">
                                        {{ $errors->first('operator') }}
                                    </p>
                                @endif
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label text-right">Alasan <span class="text-danger">*</span></label>
                            <div class="col-sm-6">
                                {!! Form::textarea('reason', old('reason'), ['class' => 'form-control', 'rows' => 3, 'placeholder' => '', 'required' => '']) !!}
                                <p class="help-block"></p>
                                @if($errors->has('reason'))
                                <p class="help-block">
                                    {{ $errors->first('reason') }}
                                </p>
                                @endif
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label text-right"> <kbd class="btn-danger"><i class="fa fa-file-pdf-o"></i> PDF</kbd> <br> Attachment Berita Acara</label>
                            <div class="col-sm-6">
                                {!! Form::myFile('file', '',['class' => 'form-control']) !!}
                            </div>
                        </div>
                        <hr>

                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label text-right"></label>
                            <div class="col-sm-8">
                                <a href="{{ route('logistic.inventory.writeoff',['id' => Hashids::encode($inventory->id)]) }}" class="btn btn-light text-uppercase fsz-sm fw-600">{{ trans('Cancel') }}</a>
                                {!! Form::submit(trans('Submit'), ['class' => 'btn btn-danger text-uppercase fsz-sm fw-600']) !!}
                            </div>
                        </div>
                    {!! Form::close() !!}
                @endif
			
		</div>  
	</div>
</div>
	
@stop
