@extends('layouts.app')

@section('page-header')
    Ekspedisi / Vendor
@endsection

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('master.expeditions.index') }}">Master Ekspedisi</a></li>
        <li class="breadcrumb-item active" aria-current="page">Create</li>
    </ol>
@endsection

@section('content')

	<div class="row mB-40">

        <div class="col-sm-12">
            <div class="bgc-white p-20 bd">
                @include('master.menulogistik')
                <h6><a class="float-left" href="{{ route('master.expeditions.index') }}"><i class="ti-arrow-left mR-10"></i></a> Ekspedisi</h6>
                <hr class="mB-30">

                {!! Form::open(['method' => 'POST', 'route' => ['master.expeditions.store']]) !!}
                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label text-right">Nama Ekpedisi / Vendor<span class="text-danger">*</span></label>
                        <div class="col-sm-8">
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
                        <label class="col-sm-3 col-form-label text-right"> </label>
                        <div class="col-sm-4">
                            <label> PIC </label>
                            {!! Form::text('pic', old('pic'), ['class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}
                            <p class="help-block"></p>
                            @if($errors->has('pic'))
                                <p class="help-block">
                                    {{ $errors->first('pic') }}
                                </p>
                            @endif
                        </div>
                        <div class="col-sm-4">
                            <label> Email </label>
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
                        <label class="col-sm-3 col-form-label text-right"> </label>
                        <div class="col-sm-4">
                            <label> Telepon </label>
                            {!! Form::text('telp', old('telp'), ['class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}
                            <p class="help-block"></p>
                            @if($errors->has('telp'))
                                <p class="help-block">
                                    {{ $errors->first('telp') }}
                                </p>
                            @endif
                        </div>
                        <div class="col-sm-4">
                            <label> Fax </label>
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
                        <label class="col-sm-3 col-form-label text-right">Is Hand Carry </label>
                        <div class="col-sm-8">
                            <input type="checkbox" name="is_handcarry" class="switch switch-info" id="is_handcarry" value="0">
                        </div>
                    </div>
                    
                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label text-right">Status <span class="text-danger">*</span></label>
                        <div class="col-sm-8">
                            <input type="checkbox" name="status" class="switch switch-info" id="status" value="0">
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
	                        <a href="{{ route('master.expeditions.index') }}" class="btn btn-light text-uppercase fsz-sm fw-600">{{ trans('Cancel') }}</a>
                            {!! Form::submit(trans('Submit'), ['class' => 'btn btn-danger text-uppercase fsz-sm fw-600']) !!}
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
            if($('#is_handcarry').val()=='1'){
                $('#is_handcarry').attr('checked','checked').iCheck('update');
            }
            $('#is_handcarry').on('ifChecked', function(){
                $("#is_handcarry" ).attr('value', '1');
            });
            $('#is_handcarry').on('ifUnchecked', function(){
                $("#is_handcarry" ).attr('value', '0');
            });
        });
    </script>
@stop


