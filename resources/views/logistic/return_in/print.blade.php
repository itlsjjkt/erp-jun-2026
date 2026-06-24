


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
					<img src="{{ asset('storage'.$return_in->companyLogo) }}" alt="Logo"  style="width:70px" >
				</td>
				<td class="border-0" style="width:50%">
					<strong>{{ $return_in->company }}</strong><br>
					Site {{ $return_in->location }} <br>
					{!! $return_in->locationAddress !!}<br>
					Telp. {{ $return_in->locationTelp }}
				</td>
				<td class="border-0 text-right">
                    <span class="text-bold" style="font-weight:bold; font-size: 17px;text-decoration:underline"> RETURN IN </span> <br>
                    {{ $return_in->doc_no }}
				</td>
			</tr>
        </table>

		<div class="row  mb-5">
                <div class="col-sm-6">
                    <div class="row">
                        <label class="col-sm-3">Operator</label>
                        <div class="col-sm-8">
                            : {{ $return_in->operator }}
                        </div>
                    </div>
                    <div class="row">
                        <label class="col-sm-3">Nomor ROT</label>
                        <div class="col-sm-8">
                            : {{ $return_in->doc_rot }}
                        </div>
                    </div>
                </div>
                <div class="col-sm-6">

                </div>
            </div>
       	Daftar item Barang yang dilakukan Return In:
		<table class="table table-bordered mt-2">
			<thead>
				<th class="text-uppercase" style="width:80px">No. Rak</th>
				<th class="text-uppercase" style="width:400px !important">Item</th>
				<th class="text-center text-uppercase" style="width:150px" colspan="2">QTY Retur</th>
				<th class="text-uppercase" >Penerima</th>
                <th class="text-uppercase" >Tgl Diterima</th>
				<th class="text-uppercase" style="width:250px !important">Catatan</th>
			</thead>
			<tbody class="item_form" id="itemDPM">
				@foreach($return_in_items as $item)
					<tr class="product_1">
						<td>{{ $item->code_rack }}</td>
						<td>
							{{ $item->productCode }} -  {{ $item->productName }} <br>
							<small>PN/SPEC: {{ $item->productPartNumber }}  </small></td>
							<td class="text-right"style="border-right:0 !important">{{ $item->qty }}</td>
                            <td class="text-left" style="border-left:0 !important">{{ $item->unit }}</td>
							<td>{!! $item->received != NULL ? $item->received : 'Belum Diterima' !!} </td>
                            <td>{!! $item->received_date != NULL ? date('d/m/Y',strtotime($item->received_date)) : 'Belum Diterima' !!} </td>
							<td>{{ $item->notes }}</td>
					</tr>
				@endforeach
			</tbody>
		</table>


        <table class="table border-0 mt-2">
			<tr>
				<td class="border-0" style="width:70% !important">
					{{ $return_in->location }},  {{ date('d F Y',strtotime( $return_in->created_at)) }} <br>
					<strong>Yang Menerima, </strong><br><br><br><br>
                    {{ $return_in->operator }}
				</td>
                <td class="border-0" style="width:30% !important">
                    Jakarta, <br>
                    <strong>Yang Menyerahkan, </strong><br><br><br><br>
                    Logistik Jakarta
                </td>
			</tr>
        </table>

	</div>
</div>

</body>

</html>
