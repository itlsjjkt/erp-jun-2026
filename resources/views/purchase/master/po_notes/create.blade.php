@extends('layouts.app')

@section('page-header')
    Purchase Order Notes
@endsection

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('purchasing.po_notes.index') }}">Purchase Order Notes</a></li>
        <li class="breadcrumb-item active" aria-current="page">Create</li>
    </ol>
@endsection

@section('content')

	<div class="row mB-40">
   
        @include('purchase.master.menu')

        <div class="col-sm-9">
            <div class="bgc-white p-20 bd">
                
                <h6><a class="float-left" href="{{ route('purchasing.po_notes.index') }}"><i class="ti-arrow-left mR-10"></i></a> Purchase Order Term</h6>
                <hr class="mB-30">

                {!! Form::open(['method' => 'POST', 'route' => ['purchasing.po_notes.store']]) !!}

                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label text-right">Title<span class="text-danger">*</span></label>
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
                        <label class="col-sm-3 col-form-label text-right">Content</label>
                        <div class="col-sm-6">
                            {!! Form::textarea('description', old('description'), ['class' => 'form-control description', 'rows' => 10, 'placeholder' => '']) !!}
                            <p class="help-block"></p>
                            @if($errors->has('description'))
                                <p class="help-block">
                                    {{ $errors->first('description') }}
                                </p>
                            @endif
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label text-right">Status <span class="text-danger">*</span></label>
                        <div class="col-sm-6">
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
	                        <a href="{{ route('purchasing.po_notes.index') }}" class="btn btn-light">{{ trans('Cancel') }}</a>
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