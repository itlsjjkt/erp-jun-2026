@extends('layouts.app')

@section('page-header')
    Cost Centre <small>{{ trans('app.update_item') }}</small>
@stop

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('company.index') }}">Company</a></li>
        <li class="breadcrumb-item"><a href="{{ route('cost_centre.index', ['id' => Hashids::encode($company->id)]) }}">Cost Centre</a></li>
        <li class="breadcrumb-item active" aria-current="page">Edit</li>
    </ol>
@endsection


@section('content')
<div class="row mB-40">
	@include('master.menu')

	<div class="col-sm-9">
        <div class="bgc-white p-30 bd">
            <h6><a class="float-left" href="{{ route('cost_centre.index', ['id' => Hashids::encode($company->id)]) }}"><i class="ti-arrow-left mR-10"></i></a> Cost Centre</h6>
            <hr class="mB-30">
            
            {!! Form::model($cost_centre, [
                    'route' => ['cost_centre.update', $cost_centre->id],
					'method' => 'put', 
					'files' => true
				])
			!!}

            
                
                {{ Form::hidden('company_id', $company->id ) }}

                <div class="form-group row">
                    <label class="col-sm-3 col-form-label text-right">Kode </label>
                    <div class="col-sm-3">
                        {!! Form::text('code', old('code'), ['class' => 'form-control', 'placeholder' => '']) !!}
                        <p class="help-block"></p>
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
                        <p class="help-block"></p>
                        @if($errors->has('name'))
                            <p class="help-block">
                                {{ $errors->first('name') }}
                            </p>
                        @endif
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-3 col-form-label text-right">Status </label>
                    <div class="col-sm-3">
                        <input type="checkbox" name="status" class="switch switch-info" id="status" value="{{ $cost_centre->status or '0'}}">
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
                        <a href="{{ route('cost_centre.index', ['id' => Hashids::encode($company->id)]) }}" class="btn btn-light">{{ trans('Cancel') }}</a>
                        {!! Form::submit(trans('Submit'), ['class' => 'btn btn-danger']) !!}
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