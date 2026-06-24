@extends('layouts.app')

@section('page-header')
    Aset
@stop

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('accounting.asset.index') }}">Aset</a></li>
        <li class="breadcrumb-item active" aria-current="page">Edit</li>
    </ol>
@endsection


@section('content')
<div class="row mB-40">

	<div class="col-sm-12">
        <div class="bgc-white p-30 bd">
            <h6><a class="float-left" href="{{ route('accounting.asset.index') }}"><i class="ti-arrow-left mR-10"></i></a> {{ trans('Back') }}</h6>
            <hr class="mB-30">

            {!! Form::model($item, [
                    'route' => ['accounting.asset.update', $item->id],
                    'method' => 'put', 
                    'files' => true
                ]);
            !!}
            
                @include('accounting.asset.form')
                
            {!! Form::close() !!}
     
		</div>  
	</div>
</div>
	
@stop