<div class="row mt-5">
    <div class="col-lg-6">
        <div class="form-group row">
            <label  class="col-sm-3">Nama<span class="text-danger">*</span></label>
            <div class="col-sm-8">
                @if(($item))
                    <select name="product_id" class="form-control productItem product narrow wrap" id="product" required>
                        <option value="{{$item->product_id}}" selected>{{$item->product->code}} - {{$item->product->name}} [{{$item->product->part_number}}]   </option>
                    </select>
                @else
                    <select name="product_id" class="form-control productItem product narrow wrap" id="product" required></select>
                @endif
            </div>
            @if($errors->has('name'))
                <p class="help-block">
                    {{ $errors->first('name') }}
                </p>
            @endif
        </div>

        <div class="form-group row">
            <label  class="col-sm-3">Kategori<span class="text-danger">*</span></label>
            <div class="col-sm-8">
                {!! Form::select('asset_category_id', $category, old('asset_category_id'), ['class' => 'form-control select2 asset_category', 'required' => '']) !!}
            </div>
            @if($errors->has('asset_category_id'))
                <p class="help-block">
                    {{ $errors->first('asset_category_id') }}
                </p>
            @endif
        </div>

        <div class="form-group row">
            <label class="col-sm-3">Tanggal Pembukuan</label>
            <div class="col-sm-8">
                {!! Form::date('date_input', old('date_input'), ['class' => 'form-control', 'placeholder' => '']) !!}
                @if($errors->has('date_input'))
                    <p class="help-block">
                        {{ $errors->first('date_input') }}
                    </p>
                @endif
            </div>
        </div>

        <div class="form-group row">
            <label class="col-sm-3">Referensi</label>
            <div class="col-sm-8">
                {!! Form::text('reference', old('reference'), ['class' => 'form-control', 'placeholder' => '']) !!}
                @if($errors->has('reference'))
                    <p class="help-block">
                        {{ $errors->first('reference') }}
                    </p>
                @endif
            </div>
        </div>

        <div class="form-group row">
            <label class="col-sm-3">Pengguna</label>
            <div class="col-sm-8">
                {!! Form::text('used', old('used'), ['class' => 'form-control', 'placeholder' => '']) !!}
                @if($errors->has('used'))
                    <p class="help-block">
                        {{ $errors->first('used') }}
                    </p>
                @endif
            </div>
        </div>

    </div>

    <div class="col-lg-6">

        <div class="form-group row">
            <label class="col-sm-3">Mata Uang<span class="text-danger">*</span></label>
            <div class="col-sm-8">
                {!! Form::select('currency', $currency, old('currency'), ['class' => 'form-control select2 asset_currency', 'required' => '']) !!}
                @if($errors->has('currency'))
                    <p class="help-block">
                        {{ $errors->first('currency') }}
                    </p>
                @endif
            </div>
        </div>

        <div class="form-group row">
            <label class="col-sm-3">Nilai Kotor<span class="text-danger">*</span></label>
            <div class="col-sm-8">
                {!! Form::text('gross_value', old('gross_value'), ['class' => 'form-control currency text-right', 'placeholder' => '0.00', 'required' => '']) !!}
                @if($errors->has('gross_value'))
                    <p class="help-block">
                        {{ $errors->first('gross_value') }}
                    </p>
                @endif
            </div>
        </div>

        <div class="form-group row">
            <label class="col-sm-3">Nilai Tetap</label>
            <div class="col-sm-8">
                {!! Form::text('salvage_value', old('salvage_value'), ['class' => 'form-control currency text-right', 'placeholder' => '0.00']) !!}
                @if($errors->has('salvage_value'))
                    <p class="help-block">
                        {{ $errors->first('salvage_value') }}
                    </p>
                @endif
            </div>
        </div>

        <div class="form-group row">
            <label class="col-sm-3">Catatan</label>
            <div class="col-sm-8">
                {!! Form::textarea('description', old('description'), ['class' => 'form-control', 'placeholder' => '', 'rows' => '2']) !!}
                @if($errors->has('description'))
                    <p class="help-block">
                        {{ $errors->first('description') }}
                    </p>
                @endif
            </div>
        </div>

    </div>


</div>


<h6 class="font-weight-bold mt-4">Informasi Depresiasi</h6>
<hr>

<div class="row mt-4">
    <div class="col-lg-6">

        <div class="form-group row">
            <label class="col-sm-3"> Metode Waktu</label>
            <div class="col-sm-9 checkbox-custom">
                <input class="checkbox-tools" type="radio" name="time_method" id="time_method-1" value="number" {{ ($item) ? ($item->time_method == 'number') ? "checked" : "" : "" }}>
                <label class="for-checkbox-tools" for="time_method-1" style="width:auto">Jumlah Entri</label>
                <input class="checkbox-tools" type="radio" name="time_method" id="time_method-2" value="date" {{ ($item) ? ($item->time_method == 'date') ? "checked" : "" : "" }}>
                <label class="for-checkbox-tools" for="time_method-2" style="width:auto">Tanggal Berakhir</label>
            </div>
        </div>

        <div class="form-group row" id='number_entry_field'>
            <label class="col-sm-3">Jumlah Entri<span class="text-danger">*</span></label>
            <div class="col-sm-5">
                {!! Form::number('number_entry', old('number_entry'), ['class' => 'form-control', 'placeholder' => '', 'required' => '','id' => 'number_entry']) !!}
                <p class="help-block"></p>
                @if($errors->has('number_entry'))
                    <p class="help-block">
                        {{ $errors->first('number_entry') }}
                    </p>
                @endif
            </div>
        </div>

        <div class="form-group row" id='ending_date_field' style="display: none">
            <label class="col-sm-3">Tanggal berakhir<span class="text-danger">*</span></label>
            <div class="col-sm-5">
                {!! Form::date('ending_date', old('ending_date'), ['class' => 'form-control', 'placeholder' => '', 'required' => '','id' => 'ending_date']) !!}
                <p class="help-block"></p>
                @if($errors->has('ending_date'))
                    <p class="help-block">
                        {{ $errors->first('ending_date') }}
                    </p>
                @endif
            </div>
        </div>

        <div class="form-group row">
            <label class="col-sm-3">Jumlah Bulan dalam Suatu Periode<span class="text-danger">*</span></label>
            <div class="col-sm-5">
                <div class="input-group ">
                    {!! Form::number('number_sequence', old('number_sequence'), ['class' => 'form-control', 'placeholder' => '', 'required' => '','id' => 'number_sequence']) !!}
                    <div class="input-group-append">
                        <span class="input-group-text">Bulan</span>
                    </div>
                </div>
                @if($errors->has('number_sequence'))
                    <p class="help-block">
                        {{ $errors->first('number_sequence') }}
                    </p>
                @endif
            </div>
        </div>
    
    </div>

    <div class="col-6">

        <div class="form-group row">
            <label class="col-sm-3"> Metode</label>
            <div class="col-sm-9 checkbox-custom">
                <input class="checkbox-tools" type="radio" name="compute_method" id="compute_method-1" value="linier" {{ ($item) ? ($item->compute_method == 'linier') ? "checked" : "" : "" }}>
                <label class="for-checkbox-tools" for="compute_method-1" style="width:auto">Linier</label>
                <input class="checkbox-tools" type="radio" name="compute_method" id="compute_method-2" value="degressive" {{ ($item) ? ($item->compute_method == 'degressive') ? "checked" : "" : "" }}>
                <label class="for-checkbox-tools" for="compute_method-2" style="width:auto">Degressive</label>
            </div>
        </div>

        <div class="form-group row" id='degressive_field' style="display: none">
            <label class="col-sm-3">Faktor Degressive<span class="text-danger">*</span></label>
            <div class="col-sm-5">
                {!! Form::number('degressive_factor', old('degressive_factor'), ['class' => 'form-control', 'placeholder' => '', 'required' => '','id'=>'degressive_factor']) !!}
                <p class="help-block"></p>
                @if($errors->has('degressive_factor'))
                    <p class="help-block">
                        {{ $errors->first('degressive_factor') }}
                    </p>
                @endif
            </div>
        </div>

    </div>
</div>
    
<hr> 
<div class="form-group mt-3">
    <a href="{{ route('accounting.asset_category.index') }}" class="btn btn-light">{{ trans('Cancel') }}</a>
    {!! Form::submit(trans('Submit'), ['class' => 'btn btn-success', 'id' => 'btn-submit']) !!}
</div>


@section('js')
    <script>
        $(document).ready(function() {

            $('.asset_currency').val('IDR').change();
            $('.currency').inputmask({ 'alias': 'decimal', 'groupSeparator': ',', 'autoGroup': true, 'digits': 2, 'digitsOptional': false, 'placeholder': '0.00',allowMinus: false});

            $('.asset_category').select2().on('change', function() {
                $.ajax({
                    url:"{{ route('accounting.asset_category.get') }}/" + $('.asset_category').val(), 
                    type:'GET',
                    success:function(data) {
                        if(data.time_method == 'number') $('#time_method-1').prop("checked", true);
                        else $('#time_method-2').prop("checked", true);

                        if(data.compute_method == 'linier') {
                            $('#compute_method-1').prop("checked", true);
                        }
                        else {
                            $('#compute_method-2').prop("checked", true);
                            $('#degressive_field').show();
                            $('#degressive_factor').val(data.degressive_factor);
                        }

                        if(data.time_method == 'date') {
                            $('#number_entry_field').hide();
                            $('#ending_date_field').show();
                            $('#ending_date_field').val(data.ending_date)
                        }else{
                            $('#number_entry').val(data.number_entry)
                        }
                        

                        $('#number_sequence').val(data.number_sequence)

                    }
                });
            });


            if( $('#time_method-1').prop('checked')) {
                $('#number_entry_field').show();
                $('#ending_date_field').hide();
            }
            if( $('#time_method-2').prop('checked')) {
                $('#number_entry_field').hide();
                $('#ending_date_field').show();
            }

            $('#time_method-1').click(function() {
                $('#number_entry_field').show();
                $('#ending_date_field').hide();
                $('#ending_date').val('');
            });

            $('#time_method-2').click(function() {
                $('#number_entry_field').hide();
                $('#ending_date_field').show();
                $('#number_entry').val('');
            });

            
            $('#compute_method-2').click(function() {
                $('#degressive_field').show();
            });

            $('#compute_method-1').click(function() {
                $('#degressive_field').hide();
                $('#degressive_factor').val('');
            });

            if( $('#compute_method-2').prop('checked')) {
                $('#degressive_field').show();
            }



            $('#product').select2({
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
                                    var product = item.code + "-" + item.name;
                                }else{
                                    var product = item.code + "-" + item.name + " ["+ item.part_number + "]";
                                }

                                return {
                                    id: item.id,
                                    text: product,
                                    measure: item.measure,
                                    code: item.code,
                                    item: item.item_id
                                }
                            })
                        };
                    },
                    cache: false
                }
            });


        });
    </script>
@stop