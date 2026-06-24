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
				border-bottom: 0.01em solid #333;
				border-top: 0.01em solid #333;
				padding:5px 8px;
			}
			.p-left{
				padding-left: 50px;
			}
			.p-left2{
				padding-left: 50px;
				padding-top: 0;
			}
			.p-left-2{
				padding-left: 100px;
			}
			.min-p-left{
				padding-left: -50px;
			}
			.plus-p{
				padding-left: 110px;
				vertical-align: 9pt;
			}
			.plus-p2{
				list-style-type: none;
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
			.text-left{
				text-align:left;
			}
			.text-uppercase{
				text-transform:uppercase;
			}
			.jalur {
				border: 1px solid;
				width: 200px;
				margin: 0 auto;
			}

		</style>

	</head>
	<body>
		<h3 class="text-center font-weight-bold mB-80">== FORM PERMINTAAN COVER ASURANSI ==</h3>
		<table style="width:100%;">
			<tr>
				<th style="width: 50%">
					<div class="text-left p-left">COMPANY</div>
				</th>
				<th style="width: 50%">
					<div class="text-left min-p-left">: {{ $insurance->company }}</div>
				</th>
				<th style="width: 50%">
					<div class="text-left p-left-2">EKSPEDISI / FORWARDER</div>
				</th>
				<th style="width: 50%">
					<div class="text-left min-p-left">: {{ $insurance->expedition_forwarder }}</div>
				</th>
			</tr>
		</table>
		<table style="width:100%;">
			<tr>
				<th style="width: 50%">
					<div class="text-left p-left">PROJECT</div>
				</th>
				<th style="width: 50%">
					<div class="text-left min-p-left">: {{ $insurance->project }}</div>
				</th>
				<th style="width: 50%">
					<div class="text-left p-left-2">RISK LOCATION</div>
				</th>
				<th style="width: 50%">
					<div class="text-left min-p-left">: {{ $insurance->risk_location }}</div>
				</th>
			</tr>
		</table>
		<table style="width:100%;">
			<tr>
				<th style="width: 50% ">
					<div class="text-left p-left">INSURANCE NUMBER</div>
				</th>
				<th style="width: 50%">
					<div class="text-left min-p-left">: {{ $insurance->doc_no }}</div>
				</th>
				<th style="width: 50%">
					<div class="text-left p-left-2">ETD / ETA</div>
				</th>
				<th style="width: 50%">
					<div class="text-left min-p-left">: {{ idDate2($insurance->etd_eta) }}</div>
				</th>
			</tr>
		</table>
		<table style="width:100%;">
			<tr>
				<th style="width: 50%; vertical-align: top;">
					<div class="text-left p-left2">MANIFEST NUMBER</div>
				</th>
				<th style="width: 50%">
					<div class="text-left min-p-left">
						@php
							$previousNoSPB = null;
						@endphp
						@foreach($insurance_items as $val)
							@if ($val->noSPB != $previousNoSPB)
								<li class="plus-p2">• {{ $val->noSPB }}</li>
							@endif
							@php
								$previousNoSPB = $val->noSPB;
							@endphp
						@endforeach
					</div>
				</th>
				<th style="width: 50%; vertical-align: top;">
					<div class="text-left p-left-2">READY TO SHIPPED BY</div>
				</th>
				<th style="width: 50%; vertical-align: top;" >
					<div class="text-left min-p-left">: {{$insurance->shipped_by }}</div>
				</th>
			</tr>
		</table>
		<table style="width:100%;">
			<tr>
				
				<th style="width: 50%">
					<div class="text-left p-left-2"></div>
				</th>
				<th style="width: 50%">
					<div class="text-left min-p-left"></div>
				</th>
			</tr>
		</table>


		<br><br>

		<table class="table table-bordered" style="width:100%;">
			<tbody>
				<tr>
					<th class="text-center" style="width: 60%;" colspan="5">DIKIRIM</th>
					<th class="text-center" colspan="4">ASURANSI</th>
				</tr>
				<tr>
					<th rowspan="2" style="width: 10%">No</th>
					<th rowspan="2" style="width: 30%">Nama Barang</th>
					<th rowspan="2" style="width: 35%">Notes</th>
					<th colspan="2" class="text-center" style="width: 30%">Item</th>
					<th rowspan="2" class="text-center" style="width: 25%;">Harga / Item</th>
					<th rowspan="2" class="text-center" style="width: 10%;">Disc(%)</th>
					<th rowspan="2" class="text-center" style="width: 10%;">PPN(%)</th>
					<th rowspan="2" class="text-center" style="width: 50%;">Total</th>
				</tr>
				<tr>
					<th class="text-center">QTY</th>
					<th class="text-center">UOM</th>
				</tr>
			</tbody>
			<tbody>
				@php
					$no = 1;
					$totalharga = 0;
				@endphp
				<?php $akhir = 0; ?>
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
                        <td class="text-center">
                            {{$no}}
                        </td>
						<td>
							{{'['.$item->productCode.'] '}}{{$item->product}}<br> 
							<small>
								{{$item->productPartNumber?'PN/Spec : '.$item->productPartNumber : 'PN/Spec : -'}} <br>
								{{$item->productBrand?'Brand : '.$item->productBrand : 'Brand : -'}}
							</small>
						</td>
						<td>{!!$item->notes?$item->notes:'-'!!}</td>
						<td class="text-center">{{$item->qtyKoli}}</td>
						<td class="text-center">{{$item->measure}}</td>
						<td><div>{{($item->symbol?$item->symbol.'.':'').number_format($item->price,2,",",'.')}}</div></td>
						<td class="text-center">{{$item->discount}}</td>
						<td class="text-center">{{$item->ppn}}</td>
						<?php
							$harga_diskon = $item->price * (1-($item->discount/100));
							$harga_ppn = $harga_diskon * ($item->ppn/100);
							$total = ($harga_diskon + $harga_ppn ) * $item->qtyKoli;
							$akhir += $total;
							$mataUang = $item->symbol;
						?>
						<td style="text-align: right">
							<div>{{($item->symbol?$item->symbol.'.':'').number_format($total,2,",",'.')}}</div>
						</td>
						</style>
					</tr>
				@php
					$no++;
				@endphp
				@endforeach
			</tbody>
			<tfoot>
				<tr>
					<td colspan="7"></td>
					<td colspan="1" class="text-center font-weight-bold">TOTAL</td>
					{{-- <td colspan="1" class="text-right font-weight-bold"><div class="currency" data-content="{{$mataUang.'.'}}">{{number_format($akhir ,2,",",'.')}}</div></td> --}}
					<td colspan="1" class="text-right font-weight-bold">{{($mataUang?$mataUang.'. ':'').number_format($akhir ,2,",",'.')}}</td>
				</tr>
			</tfoot>
		</table>
		<br>
		<table class="table table-bordered" style="width: 100%">
			<thead>
				<tr>
					<th class="text-center" style="width: 12.5%;">Prepared By</th>
					<th colspan="2" class="text-center" style="width: 25%;">Checked By</th>
					<th colspan="3" class="text-center" style="width: 37.5%;">Mengetahui</th>
					<th class="text-center" style="width: 12.5%;">Received By</th>
					<th class="text-center" style="width: 12.5%;">Menyetujui</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td style="height: 75px"></td>
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
					<td class="text-center"> {{ $insurance->checked_by_1 }} </td>
					<td class="text-center"> {{ $insurance->checked_by_2 }} </td>
					<td class="text-center"> {{ $insurance->known_by_1 }}  </td>
					<td class="text-center"> {{ $insurance->known_by_2 }} </td>
					<td class="text-center"> {{ $insurance->known_by_3 }}  </td>
					<td class="text-center"> {{ $insurance->received_by }}  </td>
					<td class="text-center"> {{ $insurance->approved_by }}  </td>
				</tr>
			</tbody>
		</table>
	</body>
</html>
