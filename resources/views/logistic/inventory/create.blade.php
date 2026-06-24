@extends('layouts.app')

@section('page-header')
   Inventory
@stop

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('logistic.inventory.index') }}">Inventory</a></li>
        <li class="breadcrumb-item active" aria-current="page">Create</li>
    </ol>
@endsection


@section('content')
<div class="row mB-40">


	<div class="col-sm-12">
        <div class="bgc-white p-30 bd">
            <h6><a class="float-left" href="{{ route('logistic.inventory.index') }}"><i class="ti-arrow-left mR-10"></i></a> Inventory</h6>
            <hr class="mB-30">
                {!! Form::open(['method' => 'POST', 'route' => ['logistic.inventory.store'], 'files' => true]) !!}

                <input type="hidden" id="measure" name="measure_id">
                <div class="form-group row">
                    <label class="col-sm-3 col-form-label text-right">Nama Produk<span class="text-danger">*</span></label>
                    <div class="col-sm-6">
                        <small>Nama Produk [PN/SPEC] [Kode] [Tipe]</small>
                        <select name="product_id"  class="form-control select2 product mB-5" id="product"></select>
                        <span class="text-info" id="filter">Filter Kategori</span>
                        <div id="filter-form" style="display:none;margin-top:-10px">
                            <span class="text-default float-right mb-2" id="filter-hide"><i class="ti-close"></i></span>
                            {!! Form::select('category_id', $item, null , ['class' => 'form-control select2 category mB-5', 'id'=>'category_id']) !!}
                        </div>
                    </div>
                </div>


                <div class="form-group row">
                    <label class="col-sm-3 col-form-label text-right">Lokasi Gudang<span class="text-danger">*</span></label>
                    <div class="col-sm-4">

                        @if(isAdministrator() || isAdministratorCompany() || isLocationAdministrator() ||  isEmployeeAdministrator() )
                            {!! Form::select('location_id', $location, old('location_id'), ['class' => 'form-control select2', 'required' => '','id'=>'location_id']) !!}
                        @elseif(isAdministratorLocation())
                            <input type="text" readonly class="form-control" value="{{ getDataByID('locations',Auth::user()->location_id )->name }}">
                            <input type="hidden" name="location_id" id="location_id" value="{{ Auth::user()->location_id }}">
                        @else
                            <input type="text" readonly class="form-control" value="{{ getDataByID('locations',Auth::user()->location_id)->name }}">
                            <input type="hidden" name="location_id" id="location_id" value="{{Auth::user()->location_id}}">
                        @endif
                        <p class="help-block"></p>
                        @if($errors->has('location_id'))
                            <p class="help-block">
                                {{ $errors->first('location_id') }}
                            </p>
                        @endif
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-3 col-form-label text-right">Rak</label>
                    <div class="col-sm-4">
                        <input type="text" value="{{ old('code_rack') }}" name="code_rack"  class="form-control">
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-3 col-form-label text-right">Setting Stock</label>
                    <div class="col-sm-2">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text font-weight-bold">Onhand</span>
                            </div>
                         <input type="number" value="{{ old('stock_onhand') }}" name="stock_onhand"  class="form-control" required onwheel="return false;">
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text font-weight-bold">Min</span>
                            </div>
                            <input type="number" value="{{ old('stock_min') }}" name="stock_min"  class="form-control" onwheel="return false;">
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text font-weight-bold">Max</span>
                            </div>
                            <input type="number" value="{{ old('stock_max') }}" name="stock_max"  class="form-control" onwheel="return false;">
                        </div>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-3 col-form-label text-right">Tipe Inventory</label>
                    <div class="col-sm-4">
                        {!! Form::select('kind', $kind, old('kind'), ['class' => 'form-control select2', 'required' => '','id'=>'kind']) !!}
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-3 col-form-label text-right">Harga Satuan <br>
                    <small>Berdasarkan PO </small></label>
                    <div class="col-sm-4">
                        <input type="text" value="0" name="price"  class="form-control  currency">
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-3 col-form-label text-right">Catatan <br>
                    <small>Catatan akan dimasukan ke dalam history Mutasi</small></label>
                    <div class="col-sm-4">
                        <textarea name="description" class="form-control"></textarea>
                    </div>
                </div>


                <hr class="mt-5">
                
                <div class="form-group row">
                    <label class="col-sm-3 col-form-label text-right"></label>
                    <div class="col-sm-8">
                        <a href="{{ route('logistic.inventory.index') }}" class="btn btn-light text-uppercase fsz-sm fw-600">{{ trans('Cancel') }}</a>
                        {!! Form::submit(trans('Submit'), ['class' => 'btn btn-danger text-uppercase fsz-sm fw-600']) !!}
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
            
            var $category  = $('.category');

            $('#filter').click(function() {
                $('#filter-form').show();
            });

            $('#filter-hide').click(function() {
                $('#filter-form').hide();
            });

            $('.currency').inputmask({ 'alias': 'decimal', 'groupSeparator': ',', 'autoGroup': true, 'digits': 2, 'digitsOptional': false, 'placeholder': '0.00',allowMinus: false});

            $category.select2({
                placeholder: "Silahkan Pilih Kategori...",
                allowClear: true
            }).on('change', function() {
               $("#product").empty();
               $("#product").select2({
                    width: 'resolve',
                    placeholder: 'Cari produk dengan mengetik Nama Produk...',
                    minimumInputLength: 2,
                    ajax: {
                        url: "{{ route('master.get_product') }}/" + $category.val(),
                        dataType: 'json',
                        delay: 250,
                        processResults: function(data) {
                            return {
                                results: $.map(data, function(item) {
                                    if(item.part_number === '' || item.part_number == null){
                                        var part_number = " [" + item.code + " - " + item.brand + "]";
                                    }else{
                                        var part_number = " [" + item.part_number + "] ["+ item.code + "] [" + item.brand + "]";
                                    }
                                    return {
                                        id: item.id,
                                        text: item.name + part_number,
                                        measure: item.measure_iventory,
                                        code: item.code,
                                        item: item.item_id
                                    }
                                })
                            };
                        },
                        cache: false
                    }
                }).on('change', function() {
                    var measure = $('#product').select2('data')[0].measure;

                    $('#measure').val(measure);
                });
            });

           $("#product").select2({
                width: 'resolve',
                placeholder: 'Cari produk dengan mengetik Nama Produk...',
                minimumInputLength: 2,
                ajax: {
                    url: "{{ route('master.get_product') }}",
                    dataType: 'json',
                    delay: 250,
                    processResults: function(data) {
                        return {
                            results: $.map(data, function(item) {
                                if(item.part_number === '' || item.part_number == null){
                                    var part_number = " [" + item.code + " - " + item.brand + "]";
                                }else{
                                    var part_number = " [" + item.part_number + "] ["+ item.code + "] [" + item.brand + "]";
                                }
                                return {
                                    id: item.id,
                                    text: item.name + part_number,
                                    measure: item.measure_inventory,
                                    code: item.code,
                                    item: item.item_id
                                }
                            })
                        };
                    },
                    cache: false
                }
            }).on('change', function() {
                console.log($('#product').select2('data')[0]);
                 var measure = $('#product').select2('data')[0].measure;

                $('#measure').val(measure);
            });
        });
    </script>
@stop