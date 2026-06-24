@extends('layouts.app')

@section('page-header')
    Inventory <small>{{ trans('app.update_item') }}</small>
@stop

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('logistic.inventory.index') }}">Inventory</a></li>
        <li class="breadcrumb-item active" aria-current="page">Edit</li>
    </ol>
@endsection


@section('content')
<div class="row mB-40">


	<div class="col-sm-12">
        <div class="bgc-white p-30 bd">
            <h6><a class="float-left" href="{{ route('logistic.inventory.index') }}"><i class="ti-arrow-left mR-10"></i></a> Inventory</h6>
            <hr class="mB-30">
            {!! Form::model($inventory, [
                    'route' => ['logistic.inventory.update', $inventory->id],
					'method' => 'put',
                    'files' => true
				])
			!!}
                <div class="row">
                    <div class="col-lg-6">
                        <div class="row">
                            <label class="col-sm-3">Produk Name </label>
                            <div class="col-sm-6">: {{ $inventory->productCode }} - {{ $inventory->productName }} </div>
                        </div>
                        <div class="row">
                            <label class="col-sm-3">PN/SPEC </label>
                            <div class="col-sm-4">: {{ $inventory->productPartNumber }}</div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="row">
                            <label class="col-sm-3">Lokasi Warehouse </label>
                            <div class="col-sm-4">: {{ $inventory->location }}  </div>
                        </div>
                        <div class="row">
                            <label class="col-sm-3">Stock Onhand</label>
                            <div class="col-sm-2">: {{ $inventory->stock_onhand }} {{ $inventory->unit }}</div>
                        </div>
                    </div>
                </div>
                <hr>
               
                <div class="alert alert-warning">
                    <h6 class="alert-heading mb-0 font-weight-bold">INFORMASI</h6>
                    Perubahan Stock Onhand dilakukan melalui fitur Adjustment Stok
                </div>

                <div class="form-group row mt-5">
                    <label class="col-sm-2">Rak</label>
                    <div class="col-sm-4">
                        <input type="text" value="{{ $inventory->code_rack }}" name="code_rack"  class="form-control">
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-2">Setting Stock</label>
                    <div class="col-sm-2">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text font-weight-bold">Min</span>
                            </div>
                            <input type="number" value="{{ $inventory->stock_min }}" name="stock_min"  class="form-control" onwheel="return false;">
                        </div>
                       
                    </div>
                    <div class="col-sm-2">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text font-weight-bold">Max</span>
                            </div>
                            <input type="number" value="{{ $inventory->stock_max }}" name="stock_max"  class="form-control" onwheel="return false;">
                        </div>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-2">Tipe Inventory</label>
                    <div class="col-sm-4">
                        {!! Form::select('kind', $kind, old('kind'), ['class' => 'form-control select2', 'required' => '','id'=>'kind']) !!}
                        @if($inventory->kind == 'Dead Stock')
                            <textarea name="notes" class="form-control" id="notes" placeholder="Deskripsi Dead stock">{{ $inventory->notes }}</textarea>
                        @else
                            <textarea name="notes" class="form-control" id="notes" style="display:none" placeholder="Deskripsi Dead stock"></textarea>
                        @endif
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-2">Harga Satuan <br>
                    <small>Berdasarkan PO </small></label>
                    <div class="col-sm-4">
                        <input type="text" value="{{ $inventory->price }}" name="price"  class="form-control currency">
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-2">Status <span class="text-danger">*</span></label>
                    <div class="col-sm-6">
                        <input type="checkbox" name="status" class="switch switch-info" id="status" value="{{ $inventory->status or '0'}}">
                        <p class="help-block"></p>
                        @if($errors->has('status'))
                            <p class="help-block">
                                {{ $errors->first('status') }}
                            </p>
                        @endif
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-2">Gambar Inventory</label>
                    <div class="col-sm-8">
                        {!! Form::myFile('image', '',['id' => 'inventory-image']) !!}
                        <img src="" id="inventory-image-tag" width="200px" />
                    </div>
                </div>
                @if ($inventory->image)
                    <div class="form-group row">
                        <label class="col-sm-2"> </label>
                        <div class="col-sm-2">
                            <img src="{{ asset('storage'.$inventory->image) }}" class="img-fluid img-thumbnail w-75" id="inventory-image-exist">
                        </div>
                    </div>
                @endif

                <hr class="mt-5">
                <div class="form-group">
                        <a href="{{ route('logistic.inventory.index') }}" class="btn btn-light text-uppercase fsz-sm fw-600">{{ trans('Cancel') }}</a>
                        {!! Form::submit(trans('Submit'), ['class' => 'btn btn-danger text-uppercase fsz-sm fw-600']) !!}
                </div>
				
			{!! Form::close() !!}
		</div>  
	</div>
</div>
	
@stop

@section('js')
    <script>
        function readURL(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                
                reader.onload = function (e) {
                    $('#inventory-image-tag').attr('src', e.target.result);
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        $(document).ready(function() {
            if($('#status').val()=='1'){
                $('#status').attr('checked','checked').iCheck('update');
            }
            $('#status').on('ifChecked', function(){
                $("#status" ).attr('value', '1');
            });
            $('#status').on('ifUnchecked', function(){
                $("#status" ).attr('value', '0');
            });

            $('#kind').select2().on('change', function() {
                if($('#kind').val() == 'Dead Stock'){
                    $("#notes").show();
                }else{
                    $("#notes").hide();
                }
            });

            $('.currency').inputmask({ 'alias': 'decimal', 'groupSeparator': ',', 'autoGroup': true, 'digits': 2, 'digitsOptional': false, 'placeholder': '0.00',allowMinus: false});

            $("#inventory-image").change(function(){
				readURL(this);
				$("#inventory-image-exist").hide();
			});

        });
    </script>
@stop