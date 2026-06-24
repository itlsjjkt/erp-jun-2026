@extends('layouts.app')

@section('page-header')
    Kapal 
@endsection

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('company.index') }}">Perusahaan</a></li>
        <li class="breadcrumb-item"><a href="{{ route('department.index', ['id' => Hashids::encode($company->id)]) }}">Kapal</a></li>
        <li class="breadcrumb-item active" aria-current="page">Create</li>
    </ol>
@endsection

@section('content')

	<div class="row mB-40">

        @include('master.menu')

        <div class="col-sm-9">
            <div class="bgc-white p-20 bd">
                
                <h6><a class="float-left" href="{{ route('department.index', ['id' => Hashids::encode($company->id)]) }}"><i class="ti-arrow-left mR-10"></i></a> Kapal</h6>
                <hr class="mB-30">

                {!! Form::open(['method' => 'POST', 'route' => ['department.store']]) !!}
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
                        <label class="col-sm-3 col-form-label text-right">Kode</label>
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
                        <label class="col-sm-3 col-form-label text-right">Status </label>
                        <div class="col-sm-3">
                            <input type="checkbox" name="status" class="switch switch-info" id="status"  value="0">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label text-right">Apakah digunakan <br>sebagai DPM </label>
                        <div class="col-sm-3">
                            <input type="hidden" name="isdpm" value="0">
                            <input type="checkbox" name="isdpm" style="transform: scale(2)" id="isdpm"  value="1">
                        </div>
                    </div>
                    
                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label text-right"></label>
                        <div class="col-sm-8">
	                        <a href="{{ route('department.index', ['id' => Hashids::encode($company->id)]) }}" class="btn btn-light">{{ trans('Cancel') }}</a>
                            {!! Form::submit(trans('Submit'), ['class' => 'btn btn-danger']) !!}
                        </div>
                    </div>

                {!! Form::close() !!}       
            </div>  
        </div>
    </div>
        
@endsection


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

