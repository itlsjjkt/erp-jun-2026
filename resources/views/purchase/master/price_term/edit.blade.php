@extends('layouts.app')

@section('page-header')
    Price Term 
@stop

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('purchasing.price_terms.index') }}">Price Term</a></li>
        <li class="breadcrumb-item active" aria-current="page">Edit</li>
    </ol>
@endsection


@section('content')
<div class="row mB-40">

	@include('purchase.master.menu')

    <div class="col-sm-9">
        <div class="bgc-white p-30 bd">
            <h6><a class="float-left" href="{{ route('purchasing.price_terms.index') }}"><i class="ti-arrow-left mR-10"></i></a> Price Term</h6>
            <hr class="mB-30">

            {!! Form::model($supplier, [
                    'route' => ['purchasing.price_terms.update', $supplier->id],
                    'method' => 'put', 
                    'files' => true
                ]);
            !!}
            
                <div class="form-group row">
                    <label class="col-sm-3 col-form-label text-right">Title <span class="text-danger">*</span></label>
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
                    <label class="col-sm-3 col-form-label text-right">Status <span class="text-danger">*</span></label>
                    <div class="col-sm-6">
                        <input type="checkbox" name="status" class="switch switch-info" id="status" value="{{ $supplier->status or '0'}}">
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
                        <a href="{{ route('purchasing.price_terms.index') }}" class="btn btn-light">{{ trans('Cancel') }}</a>
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