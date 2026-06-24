<html>
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

		<style type="text/css">
			table{
				width:100%;
			}
			body{
				font-size: 11pt;
			}

			table.table-bordered {
				font-size: 9pt;
				border-left: 0.01em solid #ccc;
				border-right: 0;
				border-top: 0.01em solid #ccc;
				border-bottom: 0;
				border-collapse: collapse;
			}
			table.table-bordered td,
			table.table-bordered th {
				border-left: 0;
				border-right: 0.01em solid #ccc;
				border-top: 0;
				border-bottom: 0.01em solid #ccc;
				padding:5px 8px;
			}

			.table-bordered thead th,
			.table-bordered thead td {
				border-bottom-width: 1px;
				padding:5px;
			}
			.text-center{
				text-align:center;
			}
			.text-uppercase{
				text-transform:uppercase;
			}
		</style>

	</head>
	<body>

	
	<table class="table border-0">
		<tr>
			<td class="border-0" style="width:33%"> 
				<strong>{{ $pr->company }}</strong><br>
				{!! $pr->companyAddress !!}<br>
				Telp. {{ $pr->companyTelp }} Fax. {{ $pr->companyFax }}
			</td>
			<td class="border-0 text-center" style="width:33%"> 
				<span style="font-weight:bold; font-size: 17px;"> DPM </span> <br>
				<span style="font-weight:bold; font-size: 15px;text-decoration:underline">{{ $pr->doc_no }}</span>
			</td>
			<td class="border-0" style="width:33%">
				<table>
					<tr>
						<td class="border-0 p-0">Nama Kapal </td>
						<td class="border-0 p-0">: {{ $pr->department }} </td>
					</tr>
					<tr>
						<td class="border-0 p-0">Dibuat Oleh </td>
						<td class="border-0 p-0">: {{ $pr->created }}</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>

	<table class="table table-bordered" style="margin-top:30px;witdh:100%">
		<thead>
			<tr>
				<th>No</th>
				<th>Nama Barang</th>
				<th>QTY</th>
				<th>Catatan</th>
				<th>Flag</th>
				<th>Tgl Dibutuhkan</th>
				<th>Last Approved</th>
				<th>Next Approval</th>
				<th>Status</th>
			</tr>
		</thead>
		<tbody>
			@if (count($pr_items) > 0)
				@php
					$no = 1;
				@endphp

				@foreach ($pr_items as $item)
					<tr>
						<td>{{ $no }}</td>
						<td>[{{ $item->productCode }}] - {{ $item->product }} 
							<br>
							{!! $item->productPartNumber != NULL ? '<small> PN: '.$item->productPartNumber.'</small>' : '' !!} {!! $item->productBrand != NULL ? '<small>Brand: '.$item->productBrand.'</small>' : '' !!}
						</td>
						<td>{{ $item->qty }} {{ $item->measure }}</td>
						<td>{{ $item->notes }}</td>
						<td>{{ $item->flag == "normal" ? "Normal" : "Urgent" }} </td>
						<td>{{ date('d/m/Y',strtotime( $item->needed_on_date)) }}</td>
						<td>{{ $item->approved }} <br><small>
							@if ($item->last_approved_at != NULL)
								{{ date('d/m/Y',strtotime( $item->last_approved_at)) }}
							@endif
						</small>
						</td>
						<td>
							@php
								$val = array(0,1); 
							@endphp
							@if (in_array($item->status, $val))
								{{ getNextApprovalDPM($pr->location_id,$item->step) }}
							@endif
						</td>
						<td>{!! getStatusDPM($item->status,'raw') !!}</td>
					</tr>
				@php
					$no++
				@endphp
				@endforeach
			@else
				<tr>
					<td colspan="8">@lang('global.app_no_entries_in_table')</td>
				</tr>
			@endif
		</tbody>
	</table>
		
	
	</body>

</html>