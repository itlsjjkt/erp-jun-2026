@extends('layouts.app')

@section('page-header')
    Master Kategori Produk
@stop

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('master.items.index') }}">Master Kategori Produk</a></li>
        <li class="breadcrumb-item active" aria-current="page">Edit</li>
    </ol>
@endsection


@section('content')
<div class="row mB-40">
	<div class="col-sm-12">
        <div class="bgc-white p-30 bd">
            @include('master.menulogistik')
            <h6><a class="float-left" href="{{ route('master.items.index') }}"><i class="ti-arrow-left mR-10"></i></a> Items</h6>
            <hr class="mB-30">
            
            {!! Form::model($items, [
                    'route' => ['master.items.update', $items->id],
					'method' => 'put', 
					'files' => true
				])
			!!}
                
                <div class="form-group row">
                    <label class="col-sm-3 col-form-label text-right">Kode <span class="text-danger">*</span></label>
                    <div class="col-sm-2">
                        {!! Form::text('code', old('code'), ['class' => 'form-control', 'placeholder' => '', 'required' => '','maxlength'=>'5']) !!}
                        <p class="help-block"></p>
                        @if($errors->has('code'))
                            <p class="help-block">
                                {{ $errors->first('code') }}
                            </p>
                        @endif
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-3 col-form-label text-right">Nama Item <span class="text-danger">*</span></label>
                    <div class="col-sm-4">
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
                        <input type="checkbox" name="status" class="switch switch-info" id="status" value="{{ $items->status or '0'}}">
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
                        <a href="{{ route('master.items.index') }}" class="btn btn-light text-uppercase fsz-sm fw-600">{{ trans('Cancel') }}</a>
                        {!! Form::submit(trans('Submit'), ['class' => 'btn btn-danger text-uppercase fsz-sm fw-600']) !!}
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