<html>
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

		<style type="text/css">
			@page { margin:20px 20px 0px 20px; }
			body{
				font-size: 9pt;
			}
			table.table-bordered {
				font-size: 9pt;
				border-left: 0.01em solid #333;
				border-right: 0;
				border-top: 0.01em solid #333;
				border-bottom: 0;
				border-collapse: collapse;
			}
			table.table-bordered td,
			table.table-bordered th {
				border-left: 0;
				border-right: 0.01em solid #333;
				border-top: 0;
				border-bottom: 0.01em solid #333;
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
			.text-right{
				text-align:right;
			}
			.text-uppercase{
				text-transform:uppercase;
			}
		</style>

	</head>
	<body>
		<table style="width:100%">
			<tr>
				<td class="border-0" style="width: 80px">
					<img src="{{public_path('images/logo-shipping.jpeg')}}" alt="Logo" style="width:80px;left:10px;" >
				</td>
				<td class="border-0">
					<span class="text-uppercase" style="font-weight:bold;font-size:18px">{{ config('app.company_name') }}</span><br>
					{!! config('app.company_address') !!} <br>
					Telp: {{ config('app.company_telp') }} Website: {{ config('app.company_web') }}
				</td>
				<td class="border-0 text-right">
					<span style="font-weight:bold; font-size: 17px;text-decoration:underline"> SURAT PENGANTAR BARANG </span><br>
					<span style="font-weight:bold; font-size: 14px;">{{ $spb->doc_no }} </span><br>
					<span style="font-weight:bold; font-size: 14px;">{{ ($spb->company) ? $spb->company->name : ''}}</span>
				</td>
			</tr>
		</table>
		<hr>

		<table style="width:100%;">
			<tr>
				<td class="border-0" style="width:33.3%">
					Kepada Yth, <br>
					@if($spb->type =="SPB Cargo"  || $spb->type =="SPB Hand Carry" )
						<strong>{{ ($spb->expedition) ? $spb->expedition->name : '' }}</strong><br>
						{{ ($spb->expedition) ? $spb->expedition->address : '' }}
						<table class="border-0">
							<tr>
								<td class="border-0 p-0">Up</td>
								<td class="border-0 p-0">: {{ $spb->delivered_pic }}  </td>
							</tr>
							<tr>
								<td class="border-0 p-0">Telp</td>
								<td class="border-0 p-0">: {{ $spb->delivered_pic_telp }} </td>
							</tr>
						</table>
					@else
						<strong>{{  $supplier->name }}</strong>
						<table class="border-0">
							<tr>
								<td class="border-0 p-0">Up</td>
								<td class="border-0 p-0">: {{ $supplier->supplierPIC }}  </td>
							</tr>
							<tr>
								<td class="border-0 p-0">Telp</td>
								<td class="border-0 p-0">: {{ $supplier->supplierTelp }} </td>
							</tr>
							<tr>
								<td class="border-0 p-0">Email</td>
								<td class="border-0 p-0">: {{ $supplier->supplierEmail }} </td>
							</tr>
						</table>
					@endif
				</td>
				<td class="border-0" style="width:33.3%"></td>
				<td class="border-0" style="width:33.3%">
					<table style="width:100%">
						<tr>
							<td style="font-weight:bold;">Jenis SPB </td>
							<td>: {{ $spb->type }} </td>
						</tr>
						<tr>
							<td style="font-weight:bold;">Tgl SPB </td>
							<td>: {{ date('d/m/Y', strtotime($spb->date_transaction)) }} </td>
						</tr>
						<tr>
							<td style="font-weight:bold;">Catatan</td>
							<td>: {{ $spb->notes }} </td>
						</tr>
					</table>
				</td>
			</tr>
		</table>

		@if($spb->type =="SPB Vendor-Cargo")
			<table  style="width:100%;">
				<tr>
					<td class="border-0" style="width:33.3%">
						Mohon dikirimkan barang kami kepada: <br>
						<strong>{{ $spb->expedition }}</strong><br>
						{{ $spb->expeditionAddress }}
						<table class="border-0">
							<tr>
								<td class="border-0 p-0">Up</td>
								<td class="border-0 p-0">: {{ $spb->delivered_pic }}  </td>
							</tr>
							<tr>
								<td class="border-0 p-0">Telp</td>
								<td class="border-0 p-0">: {{ $spb->delivered_pic_telp }} </td>
							</tr>
						</table>
					</td>
					<td class="border-0" style="width:33.3%"></td>
					<td class="border-0" style="width:33.3%">
						Dan diteruskan kepada :
						<br>{{ $spb->address }}
						<table class="border-0">
							<tr>
								<td class="border-0 p-0">Up</td>
								<td class="border-0 p-0">: {{ $spb->received_pic }}  </td>
							</tr>
							<tr>
								<td class="border-0 p-0">Telp</td>
								<td class="border-0 p-0">: {{ $spb->received_pic_telp }} </td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
		@else
			<table class="border-0 mt-3">
				<tr>
					<td class="border-0" style="width:33.3%">
						Mohon dikirimkan barang kami kepada : 
						<br> {{ $spb->address }}
						<table class="border-0">
							<tr>
								<td class="border-0 p-0">Up</td>
								<td class="border-0 p-0">: {{ $spb->received_pic }}  </td>
							</tr>
							<tr>
								<td class="border-0 p-0">Telp</td>
								<td class="border-0 p-0">: {{ $spb->received_pic_telp }} </td>
							</tr>
						</table>
					</td>
					<td class="border-0" style="width:33.3%"></td>
					<td class="border-0" style="width:33.3%">
					</td>
				</tr>
			</table>
		@endif

		Adapun perincian barang yang kami kirim sebagai berikut:
		<table class="table table-bordered" style="width:100%;">
			<tr>
				<th style="width:30px">No</th>
				<th style="width:300px;">Nama Barang</th>
				<th class="text-center">QTY</th>
				<th class="text-center">DPM</th>
				<th class="text-center">PO</th>
				<th class="text-center">LPB </th>
				<th class="text-center">Kapal/Departemen</th>
				<th class="text-center">Supplier</th>
				<th class="text-center">Notes</th>
				<th>QR</th>
			</tr>
			<tbody>
					@php
						$no = 1;
					@endphp
					@foreach ($spb_items as $data)
						<?php
							$qrcode = base64_encode(QrCode::format('svg')->size(80)->errorCorrection('H')->generate($data->uuid));
						?>
						<tr>
							<td>{{ $no }}</td>
							<td>
								[{{ $data->productCode }}] {{ $data->product }} {!! $data->productPartNumber != NULL ? '<br> PN/Spec: '.$data->productPartNumber : '' !!}
								 {{ $data->productBrand != NULL ? 'Brand: '.$data->productBrand : '' }}
								<br>{!! $data->specification !!}
							</td>
							<td class="text-center">{{ $data->qtyKoli }} {{ $data->measure }}</td>
							<td class="text-center">{{ $data->noDPM  }}</td>
							<td>{{ $data->noPO }}</td>
							<td>{{ $data->noLPB }}</td>
							<td>{{ $data->department }}</td>
							<td>{{ $data->supplier }}</td>
							<td>{!! $data->annotation !!}</td>
							<td class="text-center"><img src="data:image/png;base64, {!! $qrcode !!}"></td>
						</tr>
						@php
							$no++;
						@endphp

					@endforeach
			</tbody>
			<tfoot>
				<tr>
					<td colspan="10" style="padding:0px !important">
						<table class="table table-bordered" style="width:100%;margin-top:15px">
							<tr>
								<td  class="p-0 text-center" style="width:14.25%;">Prepared by</td>
								<td  class="p-0 text-center" style="width:14.25%;">Checked by</td>
								<td  class="p-0 text-center" style="width:14.25%;">Checked by</td>
								<td  class="p-0 text-center" style="width:14.25%;">Delivered by</td>
								<td  class="p-0 text-center" style="width:14.25%;">Received by</td>
								<td  class="p-0 text-center" style="width:14.25%;">Teknisi/Operational</td>
							</tr>
							<tr>
								<td></td>
								<td style="height:80px"></td>
								<td></td>
								<td><br><br></td>
								<td><br><br></td>
								<td><br><br></td>
							</tr>
							<tr>
								<td class="p-0 text-center">{!! $spb->operator != NULL ? $spb->operator : "" !!}</td>
								<td class="p-0 text-center">{!! $spb->checker != NULL ? $spb->checker : "" !!}</td>
								<td class="p-0 text-center">DODDIE PRADHONO</td>
								<td  class="p-0 text-center"></td>
								<td class="p-0 text-center"> {{ $spb->received_pic }}</td>
								<td  class="p-0 text-center"></td>
							</tr>
							<tr>
								<td class="p-0 text-center text-uppercase">Logistic</td>
								<td class="p-0 text-center text-uppercase">Superintendent Logistic</td>
								<td class="p-0 text-center text-uppercase">Log. Manager HO</td>
								<td class="p-0 text-center text-uppercase" >
									@if($spb->type == 'SPB Vendor') 
										{{ $supplier->name }}
									@elseif($spb->type == 'SPB Hand Carry') 
										{{ $supplier->name }}
									@else 
										{{ ($spb->expedition) ? $spb->expedition->name : '' }}
									@endif
								</td>
								<td class="p-0 text-center text-uppercase">Logistic Site</td>
								<td  class="p-0 text-center"></td>
							</tr>
						</table>
					</td>
				</tr>
			</tfoot>
		</table>

	</body>

</html>
