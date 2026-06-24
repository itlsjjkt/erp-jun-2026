<div class="form-group row">
    <label class="col-sm-3 col-form-label text-right">Nama Produk<span class="text-danger">*</span></label>
    <div class="col-sm-8">
        {!! Form::text('name', old('name'), ['class' => 'form-control', 'placeholder' => '', 'required' => '' ,'id'=>'productName']) !!}
        @if($errors->has('name'))
            <p class="help-block">
                {{ $errors->first('name') }}
            </p>
        @endif
        <div class="productList" style="display:none">
            <div id="productList"> </div>
        </div>
    </div>
</div>
<div class="form-group row">
    <label class="col-sm-3 col-form-label text-right">PN/SPEC<span class="text-danger">*</span></label>
    <div class="col-sm-8">
        {!! Form::text('part_number', old('part_number'), ['class' => 'form-control', 'placeholder' => '']) !!}
        @if($errors->has('part_number'))
            <p class="help-block">
                {{ $errors->first('part_number') }}
            </p>
        @endif
    </div>
</div>

<div class="form-group row">
        <label class="col-sm-3 col-form-label text-right"> </label>
        <div class="col-sm-4">
            <label>Kategori<span class="text-danger">*</span></label>
            {!! Form::select('item_id', $item, old('item_id'), ['class' => 'form-control select2 item', 'required' => '']) !!}
            @if($errors->has('item_id'))
                <p class="help-block">
                    {{ $errors->first('item_id') }}
                </p>
            @endif
        </div>
        <div class="col-sm-4">
            <label> Brand </label>
            {!! Form::select('brand_id', $brand, old('brand_id'), ['class' => 'form-control select2','id' =>'merk']) !!}
            @if($errors->has('brand_id'))
            <p class="help-block">
                {{ $errors->first('brand_id') }}
            </p>
            @endif
        </div>
    </div>

    <div class="form-group row">
        <label class="col-sm-3 col-form-label text-right"> Satuan</label>
        <div class="col-sm-3">
            <label> Pembelian </label>
            {!! Form::select('measure_id', $measure, old('measure_id'), ['class' => 'form-control measure', 'required' => '']) !!}
            @if($errors->has('measure_id'))
            <p class="help-block">
                {{ $errors->first('measure_id') }}
            </p>
            @endif
        </div>
        <div class="col-sm-3">
            <label> Inventory <span class="text-danger">*</span> </label>
            {!! Form::select('measure_inventory', $measure, old('measure_inventory'), ['class' => 'form-control measure', 'required' => '']) !!}
            @if($errors->has('measure_inventory'))
            <p class="help-block">
                {{ $errors->first('measure_inventory') }}
            </p>
            @endif
        </div>
        <div class="col-sm-2">
            <label>Konversi <span class="text-danger">*</span> </label>
            {!! Form::number('conversion', old('conversion'), ['class' => 'form-control', 'placeholder' => '', 'required' => '', 'min' => 1]) !!}
            @if($errors->has('conversion'))
                <p class="help-block">
                    {{ $errors->first('conversion') }}
                </p>
            @endif
        </div>
    </div>

    <div class="form-group row">
        <label class="col-sm-3 col-form-label text-right">Deskripsi</label>
        <div class="col-sm-8">
            {!! Form::textarea('description', old('description'), ['class' => 'form-control', 'rows' => 3, 'placeholder' => '']) !!}
            @if($errors->has('description'))
                <p class="help-block">
                    {{ $errors->first('description') }}
                </p>
            @endif
        </div>
    </div>

    <div class="form-group row">
    <label class="col-sm-3 col-form-label text-right">Status <span class="text-danger">*</span></label>
    <div class="col-sm-6">
        <input type="checkbox" name="status" class="switch switch-info" id="status" value="{{ isset($product) ? $product->status : '0'}}">
        @if($errors->has('status'))
            <p class="help-block">
                {{ $errors->first('status') }}
            </p>
        @endif
    </div>
</div>

<div class="form-group row">
    <label class="col-sm-3 col-form-label text-right"></label>
    <div class="col-sm-8">
        <a href="{{ route('master.item_products.index') }}" class="btn btn-light text-uppercase fsz-sm fw-600">{{ trans('Cancel') }}</a>
        {!! Form::submit(trans('Submit'), ['class' => 'btn btn-danger text-uppercase fsz-sm fw-600']) !!}
    </div>
</div>

@section('js')
    <script>
        $(document).ready(function() {

            $('#merk').select2({
                allowClear: true,
                placeholder: "Silahkan pilih"
            });

            $('.measure').select2({
                allowClear: true,
                placeholder: "Silahkan pilih"
            });

            if($('#status').val()=='1'){
                $('#status').attr('checked','checked').iCheck('update');
            }
            $('#status').on('ifChecked', function(){
                $("#status" ).attr('value', '1');
            });
            $('#status').on('ifUnchecked', function(){
                $("#status" ).attr('value', '0');
            });
            
            $('#productName').on('keyup', function(){
                var productName  = $('#productName').val();

                $.ajax({
                    url: "{{ route('master.get_product') }}_name?q=" + productName, 
                    type: 'GET',
                    cache: false,
                    success: function(data){ 
                        if(data != 'null'){
                            $('.productList').show();
                            $('#productList').html(data);
                        }else{
                            $('.productList').hide();
                            $('#productList').html('');
                        }
                    }
                });
            });

        });
    </script>
@stop
