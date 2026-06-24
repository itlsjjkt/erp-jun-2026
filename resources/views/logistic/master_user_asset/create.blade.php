@extends('layouts.app')

@section('page-header')
    Master User Asset
@endsection

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('logistic.master_user_asset.index') }}">Master User Asset</a></li>
        <li class="breadcrumb-item active" aria-current="page">Create</li>
    </ol>
@endsection

@section('content')

	<div class="row mB-40">
        <div class="col-sm-12">
            <div class="bgc-white p-20 bd">
                <h6><a class="float-left" href="{{ route('logistic.master_user_asset.index') }}"><i class="ti-arrow-left mR-10"></i></a></h6>
                <br>
                <hr>
                <div class="alert alert-info mb-3">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <strong>INFORMASI</strong> Mohon perhatikan pada saat Pembuatan Master User Asset, Pastikan User tidak double<br>
                </div>
                {!! Form::open(['method' => 'POST', 'route' => ['logistic.master_user_asset.store']]) !!}
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
                        <label class="col-sm-3 col-form-label text-right">NIK Karyawan <span class="text-danger">*</span></label>
                        <div class="col-sm-6">
                            {!! Form::text('nik', old('nik'), ['class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}
                            <p class="help-block"></p>
                            @if($errors->has('nik'))
                                <p class="help-block">
                                    {{ $errors->first('nik') }}
                                </p>
                            @endif
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label text-right">Status <span class="text-danger">*</span></label>
                        <div class="col-sm-6">
                            <input type="hidden" name="status" value="0">
                            <input type="checkbox" name="status" class="switch switch-info" id="status" value="0">
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
	                        <a href="{{ route('logistic.master_user_asset.index') }}" class="btn btn-light text-uppercase fsz-sm fw-600">{{ trans('Cancel') }}</a>
                            {!! Form::submit(trans('Submit'), ['class' => 'btn btn-danger text-uppercase fsz-sm fw-600']) !!}
                        </div>
                    </div>
                {!! Form::close() !!}
            </div>
        </div>
    </div>

@section('js')

    <script>
        if($('#status').val()=='1'){
            $('#status').attr('checked','checked').iCheck('update');
        }
        $('#status').on('ifChecked', function(){
            $("#status" ).attr('value', '1');
        });
        $('#status').on('ifUnchecked', function(){
            $("#status" ).attr('value', '0');
        });
    </script>
@stop
@endsection
