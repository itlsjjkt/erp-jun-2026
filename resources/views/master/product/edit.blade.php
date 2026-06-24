@extends('layouts.app')

@section('page-header')
    Produk
@stop

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('master.item_products.index') }}">Produk</a></li>
        <li class="breadcrumb-item active" aria-current="page">Edit</li>
    </ol>
@endsection


@section('content')
<div class="row mB-40">
	<div class="col-sm-12">
        <div class="bgc-white p-30 bd">
            @include('master.menulogistik')
            <h6><a class="float-left" href="{{ route('master.item_products.index') }}"><i class="ti-arrow-left mR-10"></i></a> Produk</h6>
            <hr class="mB-30">
            {!! Form::model($product, [
                    'route' => ['master.item_products.update', $product->id],
					'method' => 'put', 
					'files' => true
				])
			!!}
                <div class="form-group row">
                    <label class="col-sm-3 col-form-label text-right">Kode Produk</label>
                    <div class="col-sm-3">
                        <input value="{{ $product->code }}" readonly class="form-control">
                    </div>
                </div>

                @include ('master.product.form')

			{!! Form::close() !!}
		</div>  
	</div>
</div>
	
@stop