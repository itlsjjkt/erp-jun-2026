@extends('layouts.app')

@section('page-header')
    Satuan 
@endsection

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('master.measures.index') }}">Master Satuan</a></li>
        <li class="breadcrumb-item active" aria-current="page">Create</li>
    </ol>
@endsection

@section('content')

	<div class="row mB-40">
        <div class="col-sm-12">
            <div class="bgc-white p-20 bd">
                @include('master.menulogistik')
                <h6><a class="float-left" href="{{ route('master.measures.index') }}"><i class="ti-arrow-left mR-10"></i></a> Satuan</h6>
                <hr class="mB-30">

                {!! Form::open(['method' => 'POST', 'route' => ['master.measures.store']]) !!}
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
                        <label class="col-sm-3 col-form-label text-right"></label>
                        <div class="col-sm-8">
	                        <a href="{{ route('master.measures.index') }}" class="btn btn-light text-uppercase fsz-sm fw-600">{{ trans('Cancel') }}</a>
                            {!! Form::submit(trans('Submit'), ['class' => 'btn btn-danger text-uppercase fsz-sm fw-600']) !!}
                        </div>
                    </div>

                {!! Form::close() !!}       
            </div>  
        </div>
    </div>
        
@endsection
