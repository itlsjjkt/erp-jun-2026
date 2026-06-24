


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

	<style>
		.table td,
		.table th {
			padding: 0.2em 0.6em !important;
			vertical-align: middle !important;
		}

		body {
			font-size: 11pt;
			margin: 0;
			font-family: "Tahoma";
		}

		.table-bordered th,
		.table-bordered td,
		.border {
			border: 1px solid #555 !important;
		}

		.border-0 {
			border: 0;
		}
		table {
			width: 100%;
		}
		table.report-container {
			page-break-after:always;
		}
		thead.report-header {
			display:table-header-group;
		}
		tfoot.report-footer {
			display:table-footer-group;
		}

		table.report-container div.article {
			page-break-inside: avoid;
		}

		@media print {
			@page {
				margin: 0.4cm;
				size: letter;
			}
		}

		.purchase_page {
			counter-reset: pagen;
			page-break-before: always;
			page-break-inside: avoid;
			position: relative;
		}

	</style>

</head>
<body>

	<table class="report-container">
		<thead class="report-header">
			<tr>
				<td class="report-header-cell">
					<div class="header-info">
						<table class="table border-0">
							<tr>
								<td class="border-0" style="width:50%">
									<strong>{{ $pr->company }}</strong><br>
									{!! $pr->companyAddress !!}<br>
									Telp. {{ $pr->companyTelp }} Fax. {{ $pr->companyFax }}
								</td>
								<td class="border-0 text-right" style="width:50%">
									<span class="text-bold" style="font-size: 17px;text-decoration:underline"> PURCHASE REQUISITION </span> <br>
									<span class="text-bold" style="font-weight:bold; font-size: 17px;"> {{ $pr->doc_no }} </span> <br>
									Tanggal Terbit : {{  date('d M Y', strtotime($pr->created_at))  }}
								</td>
							</tr>
						</table>
						<table class="table border-0">
							<tr>
								<td class="border-0" style="width:50%">
									<table class="table">
										<tr>
											<td class="border-0 p-0">Nomor DPM</td>
											<td class="border-0 p-0">: {{ $pr->no_dpm }} </td>
										</tr>
                                        <tr>
											<td class="border-0 p-0">Lokasi/Kapal </td>
											<td class="border-0 p-0">: {{ $pr->location }} </td>
										</tr>
										<tr>
											<td class="border-0 p-0">Departement </td>
											<td class="border-0 p-0">: {{ $pr->department }} </td>
										</tr>
										<tr>
											<td class="border-0 p-0">Project </td>
											<td class="border-0 p-0">: {{ $pr->project }} </td>
										</tr>
										<tr>
											<td class="border-0 p-0">Tipe DPM </td>
											<td class="border-0 p-0">: {{ strtoupper($pr->type) }} </td>
										</tr>
									</table>
								</td>
								<td class="border-0" style="width:50%">
									<table class="table">
										<tr>
											<td class="border-0 p-0" style="vertical-align: top !important; width:30%">Deskripsi</td>
											<td class="border-0 p-0" style="vertical-align: top !important;">: {{ $pr->description }} </td>
										</tr>
										<tr>
											<td class="border-0 p-0" style="vertical-align: top !important;">Dibuat Oleh </td>
											<td class="border-0 p-0" style="vertical-align: top !important;">: {{ $pr->created }}</td>
										</tr>
									</table>
								</td>
							</tr>
						</table>
					</div>
				</td>
			</tr>
		</thead>

		<tbody class="report-content">
			<tr>
				<td class="report-content-cell">
				<div class="main">
						<table class="table table-bordered">
							<thead>
								<th>No</th>
								<th>Nama Barang</th>
								<th  style="width:200px">Catatan</th>
								<th>QTY</th>
								<th>Flag</th>
								<th>Tgl Dibutuhkan</th>
								<th>Status</th>
							</thead>
							<tbody>
								@if (count($pr_items) > 0)
									@php
										$no = 1
									@endphp
									@foreach ($pr_items as $item)
										<tr data-entry-id="{{ $item->id }}">
											<td>{{ $no }}</td>
											<td>
												{{ $item->product }}
												<br>
												{{ $item->productPartNumber != NULL ? 'PN/Spec: '.$item->productPartNumber : '' }}
												{{ $item->productBrand != NULL ? 'Brand: '.$item->productBrand : '' }}
											</td>
											<td>
												{!! $item->notes !!}
											</td>
											<!-- @if ($item->po_status == 2)
												<td>{{ $item->qty_parsial }} {{ $item->measure }}</td>
											@else -->
												<td>{{ $item->qty}} {{ $item->measure }}</td>
											<!-- @endif -->
											<td>{{ $item->flag == "normal" ? "Normal" : "Urgent" }}</td>
											<td>{{ date('d/m/Y',strtotime( $item->needed_on_date)) }}</td>
											<td>{!! getStatusItemPR($item->pr_status, $item->po_status, $item->qty_parsial,$pr->type,'raw') !!} </td>
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


						@if ($pr->mr_file)
							@if($num_page == 1)
								<div class="purchase_page" style="display: flex; justify-content: center;">
									<img src="{{ asset('storage/mr_file/'.$pr->doc_no.'.jpg') }}" style="width:70%;height:70%">
								</div>
							@else
								@for ($i = 0; $i < $num_page; $i++)
									<div class="purchase_page" style="display: flex; justify-content: center;">
										<img src="{{ asset('storage/mr_file/'.$pr->doc_no.'-'.$i.'.jpg') }}" style="width:70%;height:70%">
									</div>
								@endfor
							@endif
						@endif

					</div>
				</td>
			</tr>
		</tbody>
	</table>

</body>

</html>
