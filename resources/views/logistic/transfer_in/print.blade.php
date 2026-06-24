


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
				<td class="border-0">
					<strong>{{ $transfer->company }}</strong><br>
					Site {{ $transfer->location }} <br>
					{!! $transfer->locationAddress !!}<br>
					Telp. {{ $transfer->locationTelp }}
				</td>
				<td class="border-0 text-right" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                    <span class="text-bold" style="font-weight:bold; font-size: 17px; text-decoration: underline;">
                        WAREHOUSE TRANSFER IN
                    </span><br>
                    {{ $transfer->doc_no }}
                </td>
			</tr>
        </table>
		<hr>
        <div class="row">
            <div class="col-sm-6">
                <table class="mt-2">
                    <tr>
                        <td>Nomor Transfer Out</td>
                        <td>: {{ $transfer->out_doc_no }}</td>
                    </tr>
                    <tr>
                        <td>Type WTO</td>
                        <td>: {{ getTypeWto($transfer->type_wto) }}</td>
                    </tr>
                    <tr>
                        <td>Operator</td>
                        <td>: {{ $transfer->operator_wto }}</td>
                    </tr>
                </table>
            </div>
            <div class="col-sm-6">
                <table class="mt-2">
                    <tr>
                        <td>Penerima</td>
                        <td>: {{ $transfer->received }}</td>
                    </tr>
                    <tr>
                        <td>Lokasi Tujuan</td>
                        <td>: {{ $transfer->location.' - '.$transfer->companyCode }}</td>
                    </tr>
                    <tr>
                        <td>Tanggal diterima</td>
                        <td>: {{ date('d F Y',strtotime( $transfer->received_date )) }}</td>
                    </tr>
                </table>
            </div>
        </div>
        <p class="mt-3">
			Telah diterima Barang dengan spesifikasi dibawah ini:
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
					<strong>Yang Menerima, </strong><br><br><br><br>
                    {{ $transfer->received }}
				</td>
			</tr>
        </table>

	</div>
</div>

</body>

</html>
