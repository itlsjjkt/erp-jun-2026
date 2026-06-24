@extends('layouts.app')

@section('page-header')
    Cost Centre 
@endsection

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('company.index') }}">Company</a></li>
        <li class="breadcrumb-item"><a href="{{ route('cost_centre.index', ['id' => Hashids::encode($company->id)]) }}">Cost Centre</a></li>
        <li class="breadcrumb-item active" aria-current="page">Upload</li>
    </ol>
@endsection

@section('content')

	<div class="row mB-40">

        @include('master.menu')

        <div class="col-sm-9">
            <div class="bgc-white p-20 bd">
                
                <h6><a class="float-left" href="{{ route('cost_centre.index', ['id' => Hashids::encode($company->id)]) }}"><i class="ti-arrow-left mR-10"></i></a> Cost Centre</h6>
                <hr class="mB-30">

                {!! Form::open(['method' => 'POST', 'route' => ['cost_centre.import'],'enctype' =>'multipart/form-data']) !!}
                    <input type="hidden" name="company_id" value="{{ $company->id }}">
                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label text-right">File <span class="text-danger">*</span></label>
                        <div class="col-sm-8">
                            {!! Form::file('file', old('file'), ['class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}
                            <p class="help-block"></p>
                            @if($errors->has('file'))
                                <p class="help-block">
                                    {{ $errors->first('file') }}
                                </p>
                            @endif
                            <p>Gunakan Template ini untuk mengisi Data dari Upload 
                                <a download href="{{ asset('docs/template-costcentre.xlsx') }}" class="text-success"> <i class="fa fa-file-excel-o"></i> Download</a>
                            </p>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label text-right"></label>
                        <div class="col-sm-8">
                            <a href="{{ route('cost_centre.index') }}" class="btn btn-light text-uppercase fsz-sm fw-600">{{ trans('Cancel') }}</a>
                            {!! Form::submit(trans('Submit'), ['class' => 'btn btn-danger text-uppercase fsz-sm fw-600']) !!}
                        </div>
                    </div>

                    {!! Form::close() !!}      
            </div>  
        </div>
    </div>
        
@endsection


