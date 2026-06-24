


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
					<img src="{{ asset('storage'.$conversion->companyLogo) }}" alt="Logo"  style="width:70px" >
				</td>
				<td class="border-0"> 
					<strong>{{ $conversion->company }}</strong><br>
					Site {{ $conversion->location }} <br>
					{!! $conversion->locationAddress !!}<br>
					Telp. {{ $conversion->locationTelp }}
				</td>
				<td class="border-0 text-right">
                    <span class="text-bold" style="font-weight:bold; font-size: 17px;text-decoration:underline"> KONVERSI </span> <br>
                    {{ $conversion->doc_no }}
				</td>
			</tr>
        </table>
        
       Daftar Barang yang dilakukan konversi dibawah ini:
       
		
	   <table class="table table-bordered mt-2">
			<thead>
				<th class="text-uppercase" style="width:80px">No. Rak</th>
				<th class="text-uppercase" style="width:400px !important">Item</th>
				<th class="text-uppercase text-center" style="width:150px" colspan="2">QTY</th>
				<th style="width:50px"></th>
				<th class="text-uppercase" style="width:80px">No. Rak</th>
				<th class="text-uppercase" style="width:400px !important">Item</th>
				<th class="text-uppercase text-center" style="width:150px" colspan="2">QTY</th>
			</thead>
			<tbody class="item_form" id="itemDPM">
				@foreach($conversion_items as $item) 
					<tr class="product_1">
						<td>{{ $item->coderack1 }}</td>
						<td>
							{{ $item->productcode1 }} -  {{ $item->productname1 }} 
							<br>
							{!! $item->productpartnumber1 != NULL ? '<small> PN: '.$item->productpartnumber1.'</small>' : '' !!}
						<td class="text-right"style="border-right:0 !important">{{ $item->qty_stock }}</td>
						<td class="text-left" style="border-left:0 !important">{{ $item->productunit1 }}</td>
						<td class="text-center"> <i class="ti-arrow-right fa-2x"></i> </td>
						<td>{{ $item->coderack2 }}</td>
						<td>
							{{ $item->productcode2 }} -  {{ $item->productname2 }} 
							<br>
							{!! $item->productpartnumber2 != NULL ? '<small> PN: '.$item->productpartnumber2.'</small>' : '' !!}
						<td class="text-right"style="border-right:0 !important">{{ $item->qty_conversion }}</td>
						<td class="text-left" style="border-left:0 !important">{{ $item->productunit2 }}</td>
					</tr>
				@endforeach
			</tbody>
		</table>

        <table class="table border-0 mt-5">
			<tr>
				<td class="border-0"> 
					{{ $conversion->location }},  {{ date('d F Y',strtotime( $conversion->created_at)) }} <br>
					<strong>Operator, </strong><br><br><br><br>
                    {{ $conversion->operator }}
				</td>
			</tr>
        </table>

	</div>
</div>
	
</body>

</html>