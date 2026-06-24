@extends('layouts.app')

@section('page-header')
	Company  
@stop

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('company.index') }}">Company</a></li>
        <li class="breadcrumb-item active" aria-current="page">Pengaturan</li>
    </ol>
@stop


@section('content')
<div class="row mB-40">

    @include('master.menu')

	<div class="col-sm-9">
        <div class="bgc-white p-30 bd">
            <h6>Form Company</h6>
            <hr class="mB-30">

			{!! Form::model($company, [
                    'route' => ['company.update', $company->id],
					'method' => 'put', 
					'files' => true
				])
			!!}
       
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
                        {!! Form::text('email', old('email'), ['class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}
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
                        @if ($company->logo)
                            <img src="{{ asset('storage'.$company->logo) }}" id="profile-img-exits" class="img-fluid img-thumbnail w-25"><br>
                            <code>
                                {{ $company->logo }}
                            </code>
                        @endif
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-3 col-form-label text-right">Stempel</label>
                    <div class="col-sm-8">
                        {!! Form::myFile('stempel', '') !!}
                        @if ($company->stempel)
                            <img src="{{ asset('storage'.$company->stempel) }}" id="profile-img-exits" class="img-fluid img-thumbnail w-50"><br>
                            <code>
                                {{ $company->stempel }}
                            </code>
                        @endif
                    </div>
                </div>
                
                <div class="form-group row">
                    <label class="col-sm-3 col-form-label text-right"></label>
                    <div class="col-sm-6">
				        <button type="submit" class="btn btn-danger">{{ trans('app.save_button') }}</button>
                    </div>
                </div>

				
			{!! Form::close() !!}
		</div>  
	</div>
</div>
	
@stop
