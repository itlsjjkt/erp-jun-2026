@extends('layouts.app')

@section('page-header')
    Work Area <small>{{ trans('app.update_item') }}</small>
@stop

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('company.index') }}">Company</a></li>
        <li class="breadcrumb-item"><a href="{{ route('workarea.index', ['id' => Hashids::encode($company->id)]) }}">Work Area</a></li>
        <li class="breadcrumb-item active" aria-current="page">Edit</li>
    </ol>
@endsection


@section('content')
<div class="row mB-40">

    @include('master.menu')

	<div class="col-sm-9">
        <div class="bgc-white p-30 bd">
            <h6><a class="float-left" href="{{ route('workarea.index', ['id' => Hashids::encode($company->id)]) }}"><i class="ti-arrow-left mR-10"></i></a> Work Area</h6>
            <hr class="mB-30">
            
            {!! Form::model($workarea, [
                    'route' => ['workarea.update', $workarea->id],
					'method' => 'put', 
					'files' => true
				])
			!!}
                
                {{ Form::hidden('company_id', $company->id ) }}
                
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
                    <label class="col-sm-3 col-form-label text-right">Alias<span class="text-danger">*</span></label>
                    <div class="col-sm-3">
                        {!! Form::text('alias', old('alias'), ['class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}
                        <p class="help-block"></p>
                        @if($errors->has('alias'))
                            <p class="help-block">
                                {{ $errors->first('alias') }}
                            </p>
                        @endif
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-3 col-form-label text-right">Area<span class="text-danger">*</span></label>
                    <div class="col-sm-4">
                        {!! Form::select('area_id', $area, old('area_id'), ['class' => 'form-control select2','id' => 'area_id']) !!}
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-3 col-form-label text-right">Telepon<span class="text-danger">*</span></label>
                    <div class="col-sm-4">
                        {!! Form::text('telp', old('telp'), ['class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}
                        <p class="help-block"></p>
                        @if($errors->has('telp'))
                            <p class="help-block">
                                {{ $errors->first('telp') }}
                            </p>
                        @endif
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-3 col-form-label text-right">Email<span class="text-danger">*</span></label>
                    <div class="col-sm-4">
                        {!! Form::text('email', old('email'), ['class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}
                        <p class="help-block"></p>
                        @if($errors->has('email'))
                            <p class="help-block">
                                {{ $errors->first('email') }}
                            </p>
                        @endif
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-3 col-form-label text-right">Address</label>
                    <div class="col-sm-8">
                        {!! Form::textarea('address', old('address'), ['class' => 'form-control', 'rows' => 4, 'placeholder' => '', 'required' => '']) !!}
                        <p class="help-block"></p>
                        @if($errors->has('address'))
                            <p class="help-block">
                                {{ $errors->first('address') }}
                            </p>
                        @endif
                    </div>
                </div>

                {{-- STATUS --}}
                <div class="form-group row">
                    <label class="col-sm-3 col-form-label text-right">Status</label>
                    <div class="col-sm-3">
                        <input type="hidden" name="status" value="0">
                        <input type="checkbox" name="status" id="statusss" style="transform: scale(2)" value="{{1}}" {{$workarea->status==1 ?'checked':''}}>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-3 col-form-label text-right">Apakah digunakan <br> sebagai DPM</label>
                    <div class="col-sm-3">
                        <input type="hidden" name="isDPM" value="0">
                        <input type="checkbox" name="isDPM" id="isDPM" style="transform: scale(2)" value="{{1}}" {{$workarea->isDPM==true ?'checked':''}}>
                    </div>
                </div>
                <hr>
                <div class="form-group row">
                    <label class="col-sm-3 col-form-label text-right"></label>
                    <div class="col-sm-8">
                        <a href="{{ route('workarea.index', ['id' => Hashids::encode($company->id)]) }}" class="btn btn-light">{{ trans('Cancel') }}</a>
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
        });
    </script>
@stop
