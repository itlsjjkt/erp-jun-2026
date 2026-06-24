
@section('content')


<div class="row" style="margin-top:10px">


	<div class="col-lg-12">
		<div class="text-center" style="font-weight:bold; font-size: 17px;text-decoration:underline">{{ $return_out->doc_no }}</div><br>
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
	</div>
</div>

@endsection