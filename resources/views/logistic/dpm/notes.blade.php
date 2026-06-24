
@section('content')

	<div class="row" style="margin-top:10px">
	<div class="col-lg-12">
	<table class="table table-bordered table-hover">
	<thead>
		<tr>
			<th>Tanggal</th>
			<th>Approval</th>
			<th>Catatan</th>
			<th>Log</th>
            <th>Attachment</th>
		</tr>
	</thead>
	<tbody>
			@foreach($notes as $row)
		<tr>
			<td>{{ idDate($row->created_at,'d-m-Y H:i:s') }}</td>
			<td>{{ $row->user }}</td>
			<td>{{ $row->notes }}</td>
			<td>{{ $row->message }}</td>
            <td>
                @if($row->approval_dpm_file)
                <a href="{{ asset('storage'.$row->approval_dpm_file) }}" target="_blank" title="Show">
                    Show
                </a>
                @else
                    -
                @endif
            </td>
		</tr>
			@endforeach
	</tbody>
	</table>
	</div>
</div>
@endsection
