@extends('layouts.app')

@section('page-header')
    Return Out
@stop

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('logistic.inventory.index') }}">Inventory</a></li>
        <li class="breadcrumb-item"><a href="{{ route('logistic.return_out.index') }}">Return Out</a></li>
        <li class="breadcrumb-item active" aria-current="page">Detail</li>
    </ol>
@endsection


@section('content')
<div class="row mB-40">

	<div class="col-sm-12">
        <div class="bgc-white p-30 bd">
            <div class="float-right">
                    <a class="btn btn-outline float-right" href="#" title="Print Data" onclick='printExternal("/logistic/return_out_print/{{ Hashids::encode($return_out->id) }}")'><i class="ti-printer icon-lg"></i></a>
            </div>
            <h6><a class="float-left" href="{{ route('logistic.return_out.index') }}"><i class="ti-arrow-left mR-10"></i></a> Kembali</h6>
            <hr class="mB-30">

            <h6 class="text-center font-weight-bold mB-40" style="text-decoration:underline">{{ $return_out->doc_no }}</h6>

            <div class="row">
                <div class="col-sm-6">
                    <div class="row">
                        <label class="col-sm-3">Operator</label>
                        <div class="col-sm-8">
                            : {{ $return_out->operator }}
                        </div>
                    </div>
                </div>
            </div>

            <h6 class=" mt-5">Daftar Item </h6>
            <hr>

            <table class="table table-bordered mt-2">
                <thead>
                    <th class="text-uppercase" style="width:80px">No. Rak</th>
                    <th class="text-uppercase" style="width:400px !important">Item</th>
                    <th class="text-uppercase text-center" style="width:150px" colspan="2">QTY</th>
                    <th class="text-uppercase" style="width:250px !important">Catatan</th>
                </thead>
                <tbody class="item_form" id="itemDPM">
                    @foreach($return_out_items as $item) 
                        <tr class="product_1">
                            <td>{{ $item->code_rack }}</td>
                            <td>
                                {{ $item->productCode }} -  {{ $item->productName }} <br>
                                <small>PN/SPEC: {{ $item->productPartNumber }}  </small></td>
                            <td class="text-right"style="border-right:0 !important">{{ $item->qty }}</td>
                            <td class="text-left" style="border-left:0 !important">{{ $item->unit }}</td>
                            <td>{{ $item->reason }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            @if ($return_out->status == '0'  )

                <div class="mt-4 btn-group">

                    <a href="{{ route('logistic.return_out.edit',Hashids::encode($return_out->id)) }}" class="btn btn-info mr-1 text-uppercase fsz-sm fw-600">Edit Draft</a>

                    <form class='delete' action="{{ route('logistic.return_out.delete', ['id' => $return_out->id]) }}" method='POST'>
                        {{ csrf_field() }}
                        <button class='btn btn-danger mr-1 text-uppercase fsz-sm fw-600' title='Hapus'>Hapus Draft</button>
                    </form>
                </div>

            @endif
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