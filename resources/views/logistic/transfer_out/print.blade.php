


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
		.table td{
			padding: .5rem !important;
		}
	</style>

</head>

<div class="mB-40">
	<div class="p-30">
		<table class="table border-0">
			<tr>
				<td class="border-0 p-0" style="width:80px" >
					<img src="{{ asset('storage'.$transfer->companyLogo) }}" alt="Logo"  style="width:70px" >
				</td>
				<td class="border-0" style="width:60%">
					<strong>{{ strtoupper($transfer->company) }}</strong><br>
					SITE {{ $transfer->location }} <br>
					{!! $transfer->locationAddress !!}<br>
					Telp. {{ $transfer->locationTelp }}
				</td>
				<td class="border-0 text-right">
                    <span class="text-bold" style="font-weight:bold; font-size: 17px;text-decoration:underline"> WAREHOUSE TRANSFER OUT </span> <br>
                    {{ $transfer->doc_no }}
				</td>
			</tr>
        </table>
		<hr>
		<table class="mt-2">
            <tr>
				<td>Type WTO</td>
				<td>: {{ getTypeWto($transfer->type) }}</td>
			</tr>
			<tr>
				<td>Lokasi Asal</td>
				<td>: {{ $transfer->location.' - '.$transfer->companyCode }}</td>
			</tr>
            <tr>
				<td>Lokasi Tujuan</td>
				<td>: {{ $transfer->location_destination_name.' - '.$transfer->comdestinasiCode }}</td>
			</tr>
        </table>

        <p class="mt-3">
			Daftar Barang dengan spesifikasi dibawah ini:
		</p>

		<table class="table table-bordered mt-2">
			<thead>
				<th class="text-uppercase" style="width:50px">No</th>
				<th class="text-uppercase" style="width:50% !important">Item</th>
				<th class="text-center text-uppercase">QTY</th>
				<th class="text-uppercase">Catatan</th>
			</thead>
			<tbody class="item_form" id="itemDPM">
				@php
					$no = 1;
				@endphp
				@foreach($transfer_items as $item)
					<tr class="product_1">
						<td>{{ $no }}</td>
						<td>
						{{ $item->productcode }} -  {{ $item->productname }} <br>
						<small>PN/SPEC: {{ $item->productpartnumber }} </small></td>
						<td class="text-right"style="border-right:0 !important">{{ $item->qty }} {{ $item->productunit }}</td>
						<td>{{ $item->notes }}</td>
					</tr>
					@php
						$no++;
					@endphp
				@endforeach
			</tbody>
		</table>


        <table class="table border-0 mt-5">
			<tr>
				<td class="border-0" style="width:50%">
					{{ $transfer->location }}, {{ date('d F Y',strtotime( $transfer->created_at)) }} <br>
					<strong>Yang Menyerahkan, </strong><br><br><br><br>
                    {{ $transfer->operator }}
				</td>
			</tr>
        </table>

	</div>
</div>

</body>

</html>
