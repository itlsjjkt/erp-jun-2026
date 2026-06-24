@extends('layouts.app')

@section('page-header')
    Produk 
@endsection

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('master.item_products.index') }}">Produk</a></li>
        <li class="breadcrumb-item active" aria-current="page">Create</li>
    </ol>
@endsection

@section('content')

	<div class="row mB-40">

        <div class="col-sm-12">
            <div class="bgc-white p-20 bd">
                @include('master.menulogistik')
                <h6><a class="float-left" href="{{ route('master.item_products.index') }}"><i class="ti-arrow-left mR-10"></i></a> Produk</h6>
                <hr class="mB-30">
                <div class="alert alert-info mb-5">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <strong>INFORMASI</strong> Mohon perhatikan pada saat Pembuatan Master Produk, sistem akan memberikan informasi Master product dengan nama yang mirip. <br>
                </div>

                {!! Form::open(['method' => 'POST', 'route' => ['master.item_products.store']]) !!}

                    @include ('master.product.form')

                {!! Form::close() !!}       
            </div>  
        </div>
    </div>
        
@endsection