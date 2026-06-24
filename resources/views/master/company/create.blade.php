@extends('layouts.app')

@section('page-header')
	Company  
@stop

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('company.index') }}">Company</a></li>
        <li class="breadcrumb-item active" aria-current="page">Create</li>
    </ol>
@stop

@section('content')
    {!! Form::open(['method' => 'POST', 'files' => true, 'route' => ['company.store']]) !!}

	<div class="row mB-40">
        <div class="col-sm-12">
            <div class="bgc-white p-20 bd">
                <h6>Form Company</h6>
                <hr class="mB-30">
                <div class="form-group row">
			        <label class="col-sm-3 col-form-label text-right">Kode <span class="text-danger">*</span></label>
                    <div class="col-sm-2">
                        {!! Form::text('code', old('code'), ['class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}
                        <p class="help-block"></p>
                        @if($errors->has('code'))
                            <p class="help-block">
                                {{ $errors->first('code') }}
                            </p>
                        @endif
                    </div>
                </div>
                <div class="form-group row">
			        <label class="col-sm-3 col-form-label text-right">Company Name <span class="text-danger">*</span></label>
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
			        <label class="col-sm-3 col-form-label text-right">Alias <span class="text-danger">*</span></label>
                    <div class="col-sm-4">
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
			        <label class="col-sm-3 col-form-label text-right">Contact <span class="text-danger">*</span></label>
                    <div class="col-sm-4">
			            <label> Email</label>
                        {!! Form::email('email', old('email'), ['class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}
                        <p class="help-block"></p>
                        @if($errors->has('email'))
                            <p class="help-block">
                                {{ $errors->first('email') }}
                            </p>
                        @endif
                    </div>
                    <div class="col-sm-4">
			            <label> Website</label>
                        {!! Form::text('website', old('website'), ['class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}
                        <p class="help-block"></p>
                        @if($errors->has('website'))
                            <p class="help-block">
                                {{ $errors->first('website') }}
                            </p>
                        @endif
                    </div>
                </div>

                <div class="form-group row">
			        <label class="col-sm-3 col-form-label text-right">Telp. / Fax <span class="text-danger">*</span></label>
                    <div class="col-sm-4">
			            <label> Telepon</label>
                        {!! Form::text('telp', old('telp'), ['class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}
                        <p class="help-block"></p>
                        @if($errors->has('telp'))
                            <p class="help-block">
                                {{ $errors->first('telp') }}
                            </p>
                        @endif
                    </div>
                    <div class="col-sm-4">
			            <label> Fax</label>
                        {!! Form::text('fax', old('fax'), ['class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}
                        <p class="help-block"></p>
                        @if($errors->has('fax'))
                            <p class="help-block">
                                {{ $errors->first('fax') }}
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
                
                
                <div class="form-group row">
                    <label class="col-sm-3 col-form-label text-right">Company Logo </label>
                    <div class="col-sm-8">
                        {!! Form::myFile('logo', '') !!}
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-3 col-form-label text-right">Stempel </label>
                    <div class="col-sm-8">
                        {!! Form::myFile('stempel', '') !!}
                    </div>
                </div>
            </div>  
        </div>
    </div>
	<a href="{{ route('company.index') }}" class="btn btn-light">{{ trans('Cancel') }}</a>
    {!! Form::submit(trans('Submit'), ['class' => 'btn btn-danger']) !!}
    {!! Form::close() !!}
@stop

