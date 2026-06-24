


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
	
<body onload="window.print()">
	
	@foreach($pr_data as $pr)
	<table class="report-container">
		<thead class="report-header">
			<tr>
				<td class="report-header-cell">
					<div class="header-info">
						<table class="table border-0">
							<tr>
								<td class="border-0" style="width:50%"> 
									<strong>{{ ($pr->location) ? $pr->location->company->name : '' }}</strong><br>
									{!!  ($pr->location) ? $pr->location->company->address : '' !!}<br>
									Telp. {{  ($pr->location) ? $pr->location->company->telp : ''}} Fax. {{  ($pr->location) ? $pr->location->company->fax : '' }}
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
								<td class="border-0" style="width: 50%">
									<table class="table">
										<tr>
											<td class="border-0 p-0">Nomor DPM</td>
											<td class="border-0 p-0">: {{  $pr->dpm_no }} </td>
										</tr>
										<tr>
											<td class="border-0 p-0">Departemen </td>
											<td class="border-0 p-0">: {{ ($pr->department) ? $pr->department->name : '' }} </td>
										</tr>
										<tr>
											<td class="border-0 p-0">Project </td>
											<td class="border-0 p-0">: {{ ($pr->project) ?  $pr->project->name : ''}} </td>
										</tr>
									</table>
								</td>
								<td class="border-0" style="width: 50%">
									<table class="table">
										<tr>
											<td class="border-0 p-0">Tipe DPM</td>
											<td class="border-0 p-0">: {{ ($pr->PurchaseRequest) ? strtoupper($pr->PurchaseRequest->type) : '' }} </td>
										</tr>
										<tr>
											<td class="border-0 p-0">Deskripsi DPM</td>
											<td class="border-0 p-0">: {{ ($pr->PurchaseRequest) ? $pr->PurchaseRequest->description : '' }} </td>
										</tr>
										<tr>
											<td class="border-0 p-0">DPM Dibuat Oleh </td>
											<td class="border-0 p-0">: {{ ($pr->PurchaseRequest) ? ($pr->PurchaseRequest->creator) ? $pr->PurchaseRequest->creator->name : ''  : ''}}</td>
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
								<th style="width:200px">Catatan</th>
								<th>QTY</th>
								<th>Flag</th>
								<th>Tgl Dibutuhkan</th>
								<th>Status</th>
							</thead>
							<tbody>
									@php
										$no = 1
									@endphp
									@forelse($pr->PurchaseRequestItem as $item)
										<tr data-entry-id="{{ $item->id }}">
											<td>{{ $no }}</td>
											<td>
												{{ ($item->product) ? $item->product->name : '' }}
												<br>
												{{ ($item->product) ? 'PN/Spec: '. $item->product->part_number  : '' }}  
												Brand: {{ ($item->product) ? ($item->product->brand) ? $item->product->brand->name : '-' : '-' }}
											</td>
											<td>
												{!! $item->notes !!}
											</td>
											@if ($item->po_status == 2)
												<td>{{ $item->qty_parsial }} {{ $item->measure }}</td>
											@else
												<td>{{ $item->qty}} {{ $item->measure }}</td>
											@endif
											<td>{{ $item->flag == "normal" ? "Normal" : "Urgent" }}</td>
											<td>{{ date('d/m/Y',strtotime( $item->needed_on_date)) }}</td>
											<td>{!! getStatusItemPR($item->pr_status, $item->po_status, $item->qty_parsial,$pr->PurchaseRequest->type,'raw') !!} </td>
										</tr>
										@php
											$no++
										@endphp
									@empty
										<tr>
											<td colspan="8">@lang('global.app_no_entries_in_table')</td>
										</tr>
									@endforelse
							</tbody>
						</table>
					</div>
				</td>
				</tr>
			</tbody>
		</table>
	@endforeach
	
</body>

</html>