


<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Styles -->
	<link href="{{ mix('/css/app.css') }}" rel="stylesheet"> 
	<link href="{{ asset ('/css/custom.css') }}" rel="stylesheet"> 
	
	@yield('css')

</head>

	<div class="mB-40">
		<div class="p-30">
			<table class="table border-0">
				<tr>
					<td class="border-0" style="width:33%"> 
						<strong>{{ $pr->company }}</strong><br>
						{!! $pr->companyAddress !!}<br>
						Telp. {{ $pr->companyTelp }} Fax. {{ $pr->companyFax }}
					</td>
					<td class="border-0 text-center" style="width:33%"> 
						<span class="text-bold" style="font-weight:bold; font-size: 17px;"> DPM </span> <br>
						<h6 class="text-center font-weight-bold mB-30" style="text-decoration:underline">{{ $pr->doc_no }}</h6>
					
					</td>
					<td class="border-0 float-right" style="width:33%">
						{!! QrCode::size(130)->generate("{{ route('api.purchase_request.detail',$pr->uuid) }}"); !!}
					</td>
				</tr>
			</table>

			<table>
				<tr>
					<td class="border-0 p-0">Nama Depertmen/Kapal </td>
					<td class="border-0 p-0">: {{ $pr->department }} </td>
				</tr>
				<tr>
					<td class="border-0 p-0">Project</td>
					<td class="border-0 p-0">: {{ $pr->project }} </td>
				</tr>
				<tr>
					<td class="border-0 p-0">Deskripsi</td>
					<td class="border-0 p-0">: {{ $pr->description }} </td>
				</tr>
				<tr>
					<td class="border-0 p-0">Dibuat Oleh </td>
					<td class="border-0 p-0">: {{ $pr->created }} / {{ idDate($pr->updated_at) }}</td>
				</tr>
			</table>
			<table class="table table-bordered mT-30">
			<thead>
				<th>No</th>
				<th>Nama Barang</th>
				<th>QTY</th>
				<th>Catatan</th>
				<th>Flag</th>
				<th>Tgl Dibutuhkan</th>
				<th>Last Approved</th>
				<th>Next Approval</th>
				<th>Status</th>
			</thead>
			<tbody>
				@if (count($pr_items) > 0)
					@php
						$no = 1;
					@endphp

					@foreach ($pr_items as $item)
						<tr data-entry-id="{{ $item->id }}">
							<td>{{ $no }}</td>
							<td>[{{ $item->productCode }}] - {{ $item->product }} 
								<br>
								{!! $item->productPartNumber != NULL ? '<small> PN: '.$item->productPartNumber.'</small>' : '' !!} {!! $item->productBrand != NULL ? '<small>Brand: '.$item->productBrand.'</small>' : '' !!}
							</td>
							<td>{{ $item->qty }} {{ $item->measure }}</td>
							<td>{!! $item->notes !!}</td>
							<td>{{ $item->flag == "normal" ? "Normal" : "Urgent" }}</td>
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
		</div>
	</div>
	
</body>

</html>