    <div class="form-group row">
        <label class="col-sm-3 col-form-label text-right">Title <span class="text-danger">*</span></label>
        <div class="col-sm-6">
            {!! Form::text('name', old('name'), ['class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}
            <p class="help-block"></p>
            @if($errors->has('name'))
                <p class="help-block">
                    {{ $errors->first('name') }}
                </p>
            @endif
        </div>
    </div>

    <div class="form-group row">
        <label class="col-sm-3 col-form-label text-right">Simbol <span class="text-danger">*</span></label>
        <div class="col-sm-3">
            {!! Form::text('symbol', old('name'), ['class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}
            <p class="help-block"></p>
            @if($errors->has('symbol'))
                <p class="help-block">
                    {{ $errors->first('symbol') }}
                </p>
            @endif
        </div>
    </div>

    <div class="form-group row">
        <label class="col-sm-3 col-form-label text-right">Status <span class="text-danger">*</span></label>
        <div class="col-sm-6">
            <input type="checkbox" name="status" class="switch switch-info" id="status" value="{{ isset($data) ? $data->status : '0'}}">
            <p class="help-block"></p>
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
            <a href="{{ route('purchasing.currency.index') }}" class="btn btn-light">{{ trans('Cancel') }}</a>
            {!! Form::submit(trans('Submit'), ['class' => 'btn btn-danger']) !!}
        </div>
    </div>
                

@section('js')
    <script>
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
        });
    </script>

@stop