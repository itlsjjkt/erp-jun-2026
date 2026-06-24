<div class="form-group row">
    <label class="col-sm-3 col-form-label text-right">Kode</label>
    <div class="col-sm-3">
        {!! Form::text('code', old('code'), ['class' => 'form-control', 'placeholder' => '']) !!}
        @if($errors->has('code'))
            <p class="help-block">
                {{ $errors->first('code') }}
            </p>
        @endif
    </div>
</div>

<div class="form-group row">
    <label class="col-sm-3 col-form-label text-right">Name <span class="text-danger">*</span></label>
    <div class="col-sm-6">
        {!! Form::text('name', old('name'), ['class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}
        @if($errors->has('name'))
            <p class="help-block">
                {{ $errors->first('name') }}
            </p>
        @endif
    </div>
</div>



<div class="form-group row">
    <label class="col-sm-3 col-form-label text-right">Kategori Produk <br><small>Digunakan untuk pembuatan DPM</small> </label>
    <div class="col-sm-6">
        {!! Form::select('category[]', $category, old('category'), ['class' => 'form-control select2', 'multiple' => 'multiple']) !!}
    </div>
    @if($errors->has('category'))
        <p class="help-block">
            {{ $errors->first('category') }}
        </p>
    @endif
</div>

<div class="form-group row">
    <label class="col-sm-3 col-form-label text-right">Status </label>
    <div class="col-sm-3">
        <input type="checkbox" name="status" class="switch switch-info" id="status"  value="{{ isset($project) ? $project->status : '0' }}">
    </div>
</div>

<div class="form-group row">
    <label class="col-sm-3 col-form-label text-right"></label>
    <div class="col-sm-6">
        <a href="{{ route('master.project.index') }}" class="btn btn-light">{{ trans('Cancel') }}</a>
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