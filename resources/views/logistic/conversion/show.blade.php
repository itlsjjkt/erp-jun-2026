@extends('layouts.app')

@section('page-header')
    Konversi Satuan
@stop

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('logistic.inventory.index') }}">Inventory</a></li>
        <li class="breadcrumb-item"><a href="{{ route('logistic.conversion.index') }}">Konversi Satuan</a></li>
        <li class="breadcrumb-item active" aria-current="page">Detail</li>
    </ol>
@endsection


@section('content')
<div class="row mB-40">

	<div class="col-sm-12">
        <div class="bgc-white p-30 bd">
            <div class="float-right">
                    <a class="btn btn-outline float-right" href="#" title="Print Data" onclick='printExternal("/logistic/conversion_print/{{ Hashids::encode($conversion->id) }}")'><i class="ti-printer icon-lg"></i></a>
            </div>
            <h6><a class="float-left" href="{{ route('logistic.conversion.index') }}"><i class="ti-arrow-left mR-10"></i></a> Kembali</h6>
            <hr class="mB-30">

            <h6 class="text-center font-weight-bold mB-40" style="text-decoration:underline">{{ $conversion->doc_no }}</h6>

            <div class="row">
                <div class="col-sm-6">
                    <div class="row">
                        <label class="col-sm-3">Operator</label>
                        <div class="col-sm-8">
                            : {{ $conversion->operator }}
                        </div>
                    </div>
                </div>
            </div>

            <h6 class=" mt-5">Daftar Item </h6>

            <table class="table table-bordered mt-2">
                <thead>
                    <th class="text-uppercase" style="width:80px">No. Rak</th>
                    <th class="text-uppercase" style="width:400px !important">Item</th>
                    <th class="text-uppercase text-center" style="width:150px" colspan="2">QTY</th>
                    <th style="width:50px"></th>
                    <th class="text-uppercase" style="width:80px">No. Rak</th>
                    <th class="text-uppercase" style="width:400px !important">Item</th>
                    <th class="text-uppercase text-center" style="width:150px" colspan="2">QTY</th>
                </thead>
                <tbody class="item_form" id="itemDPM">
                    @foreach($conversion_items as $item) 
                        <tr class="product_1">
                            <td>{{ $item->coderack1 }}</td>
                            <td>
                                {{ $item->productcode1 }} -  {{ $item->productname1 }} 
                                <br>
								{!! $item->productpartnumber1 != NULL ? '<small> PN: '.$item->productpartnumber1.'</small>' : '' !!}
                            <td class="text-right"style="border-right:0 !important">{{ $item->qty_stock }}</td>
                            <td class="text-left" style="border-left:0 !important">{{ $item->productunit1 }}</td>
                            <td class="text-center"> <i class="ti-arrow-right fa-2x"></i> </td>
                            <td>{{ $item->coderack2 }}</td>
                            <td>
                                {{ $item->productcode2 }} -  {{ $item->productname2 }} 
                                <br>
								{!! $item->productpartnumber2 != NULL ? '<small> PN: '.$item->productpartnumber2.'</small>' : '' !!}
                            <td class="text-right"style="border-right:0 !important">{{ $item->qty_conversion }}</td>
                            <td class="text-left" style="border-left:0 !important">{{ $item->productunit2 }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

		</div>  
	</div>
</div>
	
@stop

@section('js')
	<script  type='text/javascript'>
	    function printExternal(url) {
			var printWindow = window.open(url, 'Print', 'left=200, top=200, width=950, height=800, toolbar=0, resizable=0');
			printWindow.addEventListener('load', function() {
				printWindow.print();
			}, true);
		}
	</script>
@stop