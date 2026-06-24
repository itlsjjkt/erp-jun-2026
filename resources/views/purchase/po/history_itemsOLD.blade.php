@section('content')

	<div class="row" style="margin-top:10px">
	<div class="col-lg-12">
	<table class="table table-bordered table-hover">
	<thead>
		<tr> 
			<th>Nomor PO</th>
            <th>Tanggal PO</th>
			<th>QTY</th>
			<th>Harga Satuan</th>
			<th>Supplier</th>
		</tr>
	</thead>
	<tbody>
			@foreach($items as $row)
				<tr>
					{{-- <td>{{ $row->doc_no }}</td> --}}
					<td>
						<a target="_blank" href="{{route('purchasing.po.show', Hashids::encode($row->poId))}}">{{ $row->doc_no }}</a>
					</td>
					<td>{{ idDate($row->created_at,'d-m-Y') }}</td>
					<td>{{ $row->qty }}</td>
					<td>Rp. {{ number_format($row->price,2,".",',') }}</td>
					<td>{{ $row->supplier }}</td>
				</tr>
			@endforeach
	</tbody>
	</table>
	</div>
</div>
@endsection
