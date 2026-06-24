
@section('content')

<div class="row" style="margin-top:10px">
	<div class="col-lg-12">
		<div class="text-center" style="font-weight:bold; font-size: 17px;text-decoration:underline">{{ $spb->doc_no }} </div><br>
		<table class="table table-striped" >
			<tr>
				<th>LPB</th>
				<th>PO</th>
				<th>PR</th>
				<th>DPM</th>
			</tr>
			@foreach ($lpb as $lpbitem)
			<tr>
				<td>{{ $lpbitem->doc_no }}</td>
				<td>{{ $lpbitem->po_no }}</td>
				<td>{{ $lpbitem->pr_no }}</td>
				<td>{{ $lpbitem->dpm_no }}</td>
			</tr>
			@endforeach
		</table>
	</div>
</div>

@endsection