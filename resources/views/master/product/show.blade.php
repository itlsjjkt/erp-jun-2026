@extends('layouts.app')

@section('page-header')
    Produk 
@stop

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('master.item_products.index') }}">Master Produk</a></li>
        <li class="breadcrumb-item active" aria-current="page">Detail</li>
    </ol>
@endsection


@section('content')
<div class="row mB-40">


	<div class="col-sm-12">
        <div class="bgc-white p-30 bd">
          @include('master.menulogistik')
            <h6><a class="float-left" href="{{ route('master.item_products.index') }}"><i class="ti-arrow-left mR-10"></i></a> Produk</h6>
            <hr class="mB-30">

                <div class="row">
                    <label class="col-sm-2">Kategori</label>
                    <div class="col-sm-4">
                        : {{ ($product->item) ? $product->item->name  : ''}}
                    </div>
                </div>

                <div class="row">
                    <label class="col-sm-2">Produk Name</label>
                    <div class="col-sm-8">
                        : {{ $product->name }}
                    </div>
                </div>

                <div class="row">
                    <label class="col-sm-2">PN/SPEC</label>
                    <div class="col-sm-8">
                        : {{ $product->part_number }}
                    </div>
                </div>

                <div class="row">
                    <label class="col-sm-2">Brand</label>
                    <div class="col-sm-8">
                        : {{ ($product->brand) ? $product->brand->name : '' }}
                    </div>
                </div>

                <div class="row">
                    <label class="col-sm-2">Satuan</label>
                    <div class="col-sm-8">
                        : {{ ($product->measure) ? $product->measure->name : '' }}
                    </div>
                </div>

                <div class="row">
                    <label class="col-sm-2">Satuan Inventory</label>
                    <div class="col-sm-8">
                        : {{ ($product->measureInventory) ? $product->measureInventory->name  : ''}}
                    </div>
                </div>

                <div class="row">
                    <label class="col-sm-2">Konversi</label>
                    <div class="col-sm-8">
                        : {{ $product->conversion }}
                    </div>
                </div>
                
                <div class="row">
                    <label class="col-sm-2">Deskripsi</label>
                    <div class="col-sm-8">
                        : {!! $product->description !!}
                    </div>
                </div>

		</div>  
	</div>
</div>
	
@stop

