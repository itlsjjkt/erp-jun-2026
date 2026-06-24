


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
					<img src="{{ asset('storage'.$inventory->companyLogo) }}" alt="Logo"  style="width:70px" >
				</td>
				<td class="border-0" style="width:60%"> 
					<strong>{{ $inventory->company }}</strong><br>
					SITE {{ $inventory->location }} <br>
					{!! $inventory->locationAddress !!}<br>
					Telp. {{ $inventory->locationTelp }}
				</td>
				<td class="border-0 text-right">
                    <span class="text-bold" style="font-weight:bold; font-size: 17px;text-decoration:underline">WRITE OFF STOCK</span> <br>
                    {{ $inventory->doc_no }}
				</td>
			</tr>
        </table>
        
       Terdapat Write Off Stock Inventory dengan spesifikasi dibawah ini:
       
		<table class="table table-bordered mt-4">
			<thead>
				<tr>
					<th>Rak</th>
					<th style="width:300px">Nama Barang</th>
					<th class="text-center">PN/SPEC</th>
					<th class="text-center">Alasan</th>
				</tr>
			</thead>
			<tbody>
                <tr>
                    <td>{{ $inventory->code_rack }}</td>
                    <td style="width:300px"> {{ $inventory->productCode }} - {{ $inventory->productName }}</td>
                    <td>{{ $inventory->productPartNumber }}</td>
                    <td>{{ $inventory->reason }}</td>
                </tr>
			</tbody>
        </table>
        
        <span class="font-weight-bold">Keterangan:</span><br>
        {{ $inventory->reason }}

        <table class="table border-0 mt-5">
			<tr>
				<td class="border-0"> 
					{{ $inventory->location }},  {{ date('d F Y',strtotime( $inventory->created_at)) }} <br>
					<strong>Nama Pemeriksa</strong><br><br><br><br>
                    {{ $inventory->operator }}
				</td>
			</tr>
        </table>

	</div>
</div>
	
</body>

</html>