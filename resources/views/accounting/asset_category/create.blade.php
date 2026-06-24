@extends('layouts.app')

@section('page-header')
    Kategori Asset 
@endsection

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('accounting.asset_category.index') }}">Kategori Asset</a></li>
        <li class="breadcrumb-item active" aria-current="page">Create</li>
    </ol>
@endsection

@section('content')

	<div class="row mB-40">
   
        <div class="col-sm-12">
            <div class="bgc-white p-20 bd">
                
                <h6><a class="float-left" href="{{ route('accounting.asset_category.index') }}"><i class="ti-arrow-left mR-10"></i></a> {{ trans('Back') }}</h6>
                <hr class="mB-30">

                {!! Form::open(['method' => 'POST', 'route' => ['accounting.asset_category.store']]) !!}

                    @include('accounting.asset_category.form')

                {!! Form::close() !!}       
            </div>  
        </div>
    </div>
        
@endsection