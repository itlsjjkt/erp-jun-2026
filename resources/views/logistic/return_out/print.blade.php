


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
					<img src="{{ asset('storage'.$return_out->companyLogo) }}" alt="Logo"  style="width:70px" >
				</td>
				<td class="border-0" style="width:50%">
					<strong>{{ $return_out->company }}</strong><br>
					Site {{ $return_out->location }} <br>
					{!! $return_out->locationAddress !!}<br>
					Telp. {{ $return_out->locationTelp }}
				</td>
				<td class="border-0 text-right">
                    <span class="text-bold" style="font-weight:bold; font-size: 17px;text-decoration:underline"> RETURN OUT </span> <br>
                    {{ $return_out->doc_no }}
				</td>
			</tr>
        </table>

       Daftar item Barang yang dilakukan Return Out:

		<table class="table table-bordered mt-2">
			<thead>
				<th class="text-uppercase" style="width:80px">No. Rak</th>
				<th class="text-uppercase" style="width:400px !important">Item</th>
				<th class="text-center text-uppercase" style="width:150px" colspan="2">QTY Retur</th>
				<th class="text-uppercase" style="width:250px !important">Catatan</th>
			</thead>
			<tbody class="item_form" id="itemDPM">
				@foreach($return_out_items as $item)
					<tr class="product_1">
						<td>{{ $item->code_rack }}</td>
						<td>
							{{ $item->productCode }} -  {{ $item->productName }} <br>
							<small>PN/SPEC: {{ $item->productPartNumber }} </small></td>
							<td class="text-right"style="border-right:0 !important">{{ $item->qty }}</td>
                            <td class="text-left" style="border-left:0 !important">{{ $item->unit }}</td>
						<td>{{ $item->reason }}</td>
					</tr>
				@endforeach
			</tbody>
		</table>


        <table class="table border-0 mt-5">
			<tr>
				<td class="border-0" style="width:70% !important">
					{{ $return_out->location }},  {{ date('d F Y',strtotime( $return_out->created_at)) }} <br>
					<strong>Yang Menyerahkan, </strong><br><br><br><br>
                    {{ $return_out->operator }}
				</td>
                <td class="border-0" style="width:30% !important">
                    Jakarta, <br>
                    <strong>Yang Menerima, </strong><br><br><br><br>
                    Logistik Jakarta
                </td>
			</tr>
        </table>

	</div>
</div>

</body>

</html>
