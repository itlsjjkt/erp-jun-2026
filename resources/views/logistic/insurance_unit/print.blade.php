


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
	

</head>

<div class="mB-40">
	<div class="p-30">
		<table class="table border-0">
			<tr>
				<td class="border-0" style="width:60%"> 
					<span class="text-uppercase icon-lg font-weight-bold">{!!$company->name !!}</span><br>
					{!! $company->address !!} <br>
					Telp. {{ $company->telp }} Fax. {{ $company->fax }}
				</td>
				<td class="border-0 text-right"> 
					<span style="font-weight:bold; font-size: 17px;text-decoration:underline"> ASURANSI UNIT </span> <br>
					{{ $insurance->doc_no }} 
				</td>
			</tr>
			
		</table>


		<table class="table border-0">
			<tr>
				<td class="border-0" style="width:33.3%"> 
					<table class="border-0 table">
						<tr>
							<td class="border-0 p-0">Kepada</td>
							<td class="border-0 p-0">: {{ $insurance->kepada }} </td>
						</tr>
						<tr>
							<td class="border-0 p-0">CC</td>
							<td class="border-0 p-0">: {{ $insurance->cc }} </td>
						</tr>
						<tr>
							<td class="border-0 p-0">Dari</td>
							<td class="border-0 p-0">: Logistik</td>
						</tr>
					</table>
				</td>
				<td class="border-0" style="width:33.3%"></td>
				<td class="border-0" style="width:33.3%"> 
					<table class="border-0 table">
						
						<tr>
							<td class="border-0 p-0">Perihal</td>
							<td class="border-0 p-0">: {{ $insurance->perihal }}</td>
						</tr>
						<tr>
							<td class="border-0 p-0">Dibuat Oleh</td>
							<td class="border-0 p-0">: {{ $insurance->created }} [ {{ idDate($insurance->created_at) }} ] </td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
		
		<h6 class="mT-30">Daftar Barang</h6>
		<table class="table table-bordered">
			<thead>
				<tr>
					<th style="width:50px">No</th>
					<th style="width:400px">Nama Barang</th>
					<th class="text-center">QTY </th>
					<th class="text-center">No. DPM</th>
					<th class="text-center">No. PO</th>
					<th class="text-center">No. SPB</th>
					<th class="text-center">Annotation</th>
					<th class="text-center">Supplier</th>
					<th class="text-center">Harga</th>
					<th class="text-center">Diskon</th>
					<th class="text-center">PPN (11%)</th>
					<th class="text-center">Total</th>
				</tr>
			</thead>
			<tbody>
					@php
						$no = 1;
						$totalharga = 0;
					@endphp
					@foreach ($insurance_items as $item)
						@php
							$total= $item->price * $item->qtyKoli - (($item->price * $item->qtyKoli) *  $item->discount /100);
							if($item->ppn == 1){
								$subtotal = $total + ( $total * 11/100);
							}else{
								$subtotal = $total;
               				}
							$totalharga += $subtotal;
						@endphp
						<tr>
							<td>{{ $no }}</td>
							<td>
								[{{ $item->productCode }}] - {{ $item->product }} 
								<br><small>PN/SPEC: {{ $item->productPartNumber }} | Brand: {{ $item->productBrand }}</small>
								<br><small>{{ $item->notes }}</small>
							</td>
							<td>{{ $item->qtyKoli }} {{ $item->measure }}</td>
							<td>{{ $item->noDPM }}</td>
							<td>{{ $item->noPO }}</td>
							<td>{{ $item->noSPB }}</td>
							<td>{{ $item->annotation }}</td>
							<td>{{ $item->supplier }}</td>
							<td class="text-right">{{ number_format($item->price ,2,".",',') }}</td>
							<td class="text-right">{{ number_format($item->discount ,2,".",',') }}</td>
							<td>{{ $item->ppn == 1 ? "Ya" : "Tidak" }}</td>
							<td class="text-right">{{ number_format($subtotal ,2,".",',') }}</td>
						</tr>
					@php
						$no++;
					@endphp
					@endforeach
			</tbody>
			<tfoot>
				<tr>
					<td class="text-right font-weight-bold" colspan="12">TOTAL</td>
					<td class="text-right font-weight-bold">  {{ number_format($totalharga ,2,".",',') }} </td>
				</tr>
			</tfoot>
		</table>

		<table class="table table-bordered">
			<thead>
				<tr>
					<th rowspan="2" class="text-center">Prepared By</th>
					<th rowspan="2" class="text-center">Checked By</th>
					<th rowspan="2" class="text-center">Approved By</th>
					<th colspan="2" class="text-center">Checked By</th>
					<th colspan="2" class="text-center">Received By</th>
				</tr>
			</thead>
			<tbody>
				<tr style="height: 100px">
					<td> </td>
					<td> </td>
					<td> </td>
					<td> </td>
					<td> </td>
					<td> </td>
					<td> </td>
				</tr>
				<tr>
					<td class="text-center"> {{ $insurance->prepared_by }}</td>
					<td class="text-center"> {{ $insurance->checked_by }} </td>
					<td class="text-center"> {{ $insurance->approved_by }} </td>
					<td class="text-center"> {{ $insurance->checked_purchasing_1 }}  </td>
					<td class="text-center"> {{ $insurance->checked_purchasing_2 }} </td>
					<td class="text-center"> {{ $insurance->received_by_1 }}  </td>
					<td class="text-center"> {{ $insurance->received_by_2 }}  </td>
				</tr>
			</tbody>
		</table>

	</div>
</div>
	
</body>

</html>