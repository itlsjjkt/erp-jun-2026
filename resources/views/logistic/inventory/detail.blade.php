


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
		<table class="table table-bordered">
			<tr>
				<td>Kode Barang</td>
				<td>: {{ $inv->productCode }}</td>
			</tr>
			<tr>
				<td>Nama Barang</td>
				<td>: {{ $inv->productName }}</td>
			</tr>
			<tr>
				<td>PN/SPEC</td>
				<td>: {{ $inv->productPartNumber  }}</td>
			</tr>
			<tr>
				<td>Nomor Rak</td>
				<td>: {{ $inv->code_rack  }}</td>
			</tr>
			<tr>
				<td>Stock Awal</td>
				<td>: {{ $inv->initial  }}</td>
			</tr>
			<tr>
				<td>In</td>
				<td>: {{ $inv->in  }}</td>
			</tr>
			<tr>
				<td>Out</td>
				<td>: {{ $inv->out }}</td>
			</tr>
			<tr>
				<td>Stock On Hand</td>
				<td>: {{ $inv->stock_onhand  }}</td>
			</tr>
        </table>
        
	</div>
</div>
	
</body>

</html>