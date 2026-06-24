<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta name="csrf-token" content="{{ csrf_token() }}">
	<title>{{ config('app.name', 'Laravel') }}</title>

	<link href="{{ mix('/css/app.css') }}" rel="stylesheet">
	<link href="{{ asset ('/css/custom.css') }}" rel="stylesheet">

	<style>
		#ttd_ {
			position: relative;
			padding: 5px;
			overflow: hidden;
		}
		.fit-image {
			width: 80%;
			height: 80%;
			object-fit: cover;
		}
		.fit-image2 {
			width: 70%;
			height: 70%;
			object-fit: cover;
		}
		.table td,
		.table th {
			padding: 0.2em 0.6em !important;
			vertical-align: middle !important;
		}

		body {
			font-size: 11pt;
			margin: 0;
			font-family: "Tahoma";
		}

		.table-bordered th,
		.table-bordered td,
		.border {
			border: 1px solid #555 !important;
		}

		.border-0 {
			border: 0;
		}
		table {
			width: 100%;
		}
		table.report-container {
			page-break-after:always;
		}
		thead.report-header {
			display:table-header-group;
		}
		tfoot.report-footer {
			display:table-footer-group;
		} 

		table.report-container div.article {
			page-break-inside: avoid;
		}

		@media print {
			@page {
				margin: 0.4cm;
				size: letter;
			}
		}
	</style>

</head>

<body>
	<?php setlocale(LC_TIME, 'id_ID.utf8'); ?>

	<?php
		if ($po->po_term_id == 1) {
			$t_kepada = 'Kepada Yth,';
			$t_bpk    = 'Bapak/Ibu';
			$t_item   = 'NAMA BARANG';
			$t_jumlah = 'Jumlah';
			$t_spesifikasi = 'CATATAN';
			$t_harga = 'HARGA SATUAN';
			$t_total = 'TOTAL';
			$t_payment_method = 'Metode Pembayaran';
			$t_payment_term   = 'Termin Pembayaran';
			$t_mata_uang  = 'Mata Uang';
			$t_price_term = 'Ketentuan Harga';
			$t_diskon   = 'Diskon';
			$t_delivery_date  = 'Tgl Pengiriman';
			$t_po_term = 'Syarat dan Ketentuan';
			$t_delivery_price = 'Biaya Kirim';
			$t_prepared = 'Disiapkan Oleh';
			$t_approved = 'Disetujui Oleh';
			$t_downpayment = 'Uang Muka';
			$t_notes    = 'Catatan';
			$t_pic		= 'Kontak PIC';
			$t_tanggal  = 'Tanggal';
			$t_bank   = 'Nama Bank';
			$t_voucher   = 'No. Giro/Cek';
			$t_cek    = 'No. Voucher';
			$t_paraf  = 'Paraf';
			$t_phone  = 'HP';
			$t_pph  = 'PPH';
			$t_nodpm  = 'Nomor DPM';
			$last_print = "Last printed at";
			$t_finance  = 'Diisi Oleh Kasir/Finance';
			$t_confirm  = 'Diterima dan dikonfirmasi oleh';
			$t_confirm_name  = 'Nama';
			$t_confirm_date  = 'Date';
			$t_confirm_caption  = 'Setelah ditandatangani, <br> mohon di Fax kembali ke kami.';
			$t_nopo  = 'No. PO';
			$t_nopr  = 'No. PR';
			$t_created  = 'Tgl Buat';
			$t_tax      = 'PPN';
			$t_date      = 'Tanggal';
		} else {
			$t_kepada = 'Dear,';
			$t_bpk    = 'Mr/Mrs';
			$t_jumlah = 'Amount';
			$t_item   = 'ITEM';
			$t_spesifikasi = 'DESCRIPTION';
			$t_harga = 'PRICE';
			$t_total = 'TOTAL';
			$t_po_term = 'Term & Condition';
			$t_payment_method = 'Payment Method';
			$t_payment_term = 'Payment Term';
			$t_mata_uang = 'Currency';
			$t_price_term = 'Price Term';
			$t_diskon = 'Discount';
			$t_downpayment = 'Down Payment';
			$t_delivery_date  = 'Delivery Date';
			$t_delivery_price = 'Freight Cost';
			$t_prepared = 'Prepared By';
			$t_approved = 'Approved By';
			$t_notes    = 'Notes';
			$t_pic		= 'PIC Contacts';
			$t_pph  = 'Tax';
			$t_tanggal  = 'Date';
			$t_bank   = 'Bank Name';
			$t_voucher   = 'Giro Number/Cheque';
			$t_cek    = 'Voucher Number';
			$t_paraf  = 'Sign';
			$t_nodpm  = 'DPM Number';
			$last_print = "Last printed at";
			$t_finance  = 'Filled By Cashier / Finance';
			$t_confirm  = 'Received & Confirmed By:';
			$t_confirm_name = 'Name';
			$t_confirm_date  = 'Date';
			$t_confirm_caption  = 'After signed, <br> please send it back via Fax';
			$t_nopo  = 'PO Number';
			$t_nopr  = 'PR Number';
			$t_created  = 'Created At';
			$t_tax      = 'Tax';
			$t_date      = 'Date';
			$t_phone  = 'HP';
		}
	?>

<table class="report-container">
	<thead class="report-header">
	  <tr>
		 <td class="report-header-cell">
			<div class="header-info">
				<table class="table border-0">
					<td class="border-0" style="width:30%;padding: 0 !important;">
						<strong class="text-uppercase">{{ $po->company }}</strong><br>
						{!! $po->companyAddress !!}<br>
						Telp. {{ $po->companyTelp }} <br> Fax. {{ $po->companyFax }}
					</td>
					<td class="border-0 text-center" style="width:40%">
						<span class="text-bold" style="font-weight:bold; font-size: 15px;text-decoration:underline"> PURCHASE ORDER </span> <br>
						<table class="border-0 text-left" style="width:100%">
							<tr>
								<td class="border-0">{{ $t_nopo }}</td>
								<td class="border-0">: {{ $po->doc_no }}</td>
							</tr>
							<tr>
								<td class="border-0">{{ $t_created }}</td>
								<td class="border-0">: {{ idDate($po->created_at) }}</td>
							</tr>
							<tr>
								<td class="border-0">{{ $t_nopr }}</td>
								<td class="border-0">: {{ $po->pr_no }}</td>
							</tr>
							<tr>
								<td class="border-0">Departemen/Kapal</td>
								<td class="border-0">: {{ $po->department }}</td>
							</tr>
							@if ($po->delivery_date != NULL)
							<tr>
								<td class="border-0">{{ $t_delivery_date }}</td>
								<td class="border-0">:
									{{ date('d M Y',strtotime( $po->delivery_date)) }}
								</td>
							</tr>
							@endif
						</table>
					</td>
					<td class="border-0" style="width:30%;padding: 0 !important;">
						<div class="border p-10">
							{{ $t_kepada }}<br>
							<strong>{{ $po->supplier }}</strong><br>
							{{ $po->picTitle }} {{ $po->picName }}<br>
							Telepon : {!! str_contains($po->picTelp, '||') ? str_replace('||', '<br>Mobile Phone : ', $po->picTelp) : $po->picTelp !!} <br>
							Email: {{ $po->picEmail  }}
						</div>
					</td>
				</table>
			</div>
		  </td>
	   </tr>
	 </thead>

	 <tbody class="report-content">
	   <tr>
		  	<td class="report-content-cell">
				<div class="main">
					<table class="table table-bordered" style="width:100%">
						<thead>
							<tr>
								<th class="text-center text-uppercase" style="width:30px">No</th>
								<th class="text-center text-uppercase" style="width:250px">{{ $t_item }}</th>
								<th class="text-center text-uppercase" style="width:350px">{{ $t_spesifikasi }}</th>
								<th colspan="2" class="text-center text-uppercase" >QTY</th>
								<th class="text-center text-uppercase" style="min-width:150px">{{ $t_harga }} </th>
								<th class="text-center text-uppercase" style="min-width:30px">DISC @if($po->discount_type == 1) (%) @endif</th>
								<th class="text-center text-uppercase" style="min-width:200px">{{ $t_total }}</th>
							</tr>
						</thead>
						<tbody>
							@php
							$no = 1;
							$subtotal = 0;
							@endphp
							@foreach ($po_items as $item)
							<tr>
								<td style="text-align:center">{{ $no }}</td>
								<td>{{ $item->product }} {!! $item->productPartNumber != NULL ? '<br> PN/Spec: '.$item->productPartNumber : '' !!} </td>
								<td style="max-width: 340px;">
									{!! $item->specification !!} <br>
									{{ $item->productBrand != NULL ? 'Brand: '.$item->productBrand : '' }}
								</td>
								<td class="text-right" style="border-right:0 !important;">{{ $item->qty }}</td>
								<td class="text-left" style="border-left:0 !important;">{{ $item->measure }}</td>
								<td>
									<div class="currency" data-content="{{ $po->currencysymbol }}"> {{  format_number($item->price) }} </div>
								</td>
								<td class="text-center">
									@if($po->discount_amount == 0 && $po->discount_type == 0) 
										<div class="currency" data-content="{{ $po->currencysymbol }}"> </div>
									@endif
									{{ format_number($item->discount) }}
								</td>
								<td class="text-right">
									<div class="currency" data-content="{{ $po->currencysymbol }}">
										<?php
											$total = $item->price * $item->qty - (($item->price * $item->qty) *  $item->discount / 100);
											echo format_number($total) 
										?>
									</div>
								</td>
							</tr>
							@php
							$subtotal += $total;
							$no++;
							@endphp
							@endforeach
							<tr>
								<td colspan="5" style="border-bottom:0 !important;border-top:0 !important">
									<table class="border-0">
										<tr>
											<td class="border-0 p-0" style="width: 200px;border:none !important">{{ $t_payment_method }}</td>
											<td class="border-0 p-0" style="border:none !important">: {{ $po->payment_method }}</td>
										</tr>
									</table>
								</td>
								<td colspan="2">Sub Total</td>
								<td class="text-right">
									<div class="currency" data-content="{{ $po->currencysymbol }}"> {{ format_number($subtotal)}} </div>
								</td>
							</tr>
							<tr>
								<td colspan="5" style="border-bottom:0 !important;border-top:0 !important">
									<table>
										<tr>
											<td class="border-0 p-0" style="width: 200px;border:none !important">{{ $t_price_term }}</td>
											<td class="border-0 p-0" style="border:none !important">: {{ $po->price_term }} {{ $po->price_term_location }}</td>
										</tr>
									</table>
								</td>
								<td colspan="2">{{ $t_diskon }}
									@if ($po->discount_item == false &&  $po->discount_type == 1 &&  $po->discount_amount != 0) 
										<small>{{ $po->discount_amount  }}%</small>
									@endif
								</td>
								<td class="text-right">
									<?php
										$total_discount = 0;	
										if ($po->discount_item == false) {
											$total_discount = $po->discount_amount;
											if ($po->discount_type == 1) $total_discount = $subtotal * ($po->discount_amount / 100);
										}
									?>
									<div class="currency" data-content="{{ $po->currencysymbol }}"> {{ format_number($total_discount) }} </div>
								</td>
							</tr>
							<tr>
								<td colspan="5" style="border-bottom:0 !important;border-top:0 !important">
									<table>
										<tr>
											<td class="border-0 p-0" style="width: 200px;border:none !important">{{ $t_payment_term }}</td>
											<td class="border-0 p-0" style="border:none !important">: {{ $po->payment_term }}</td>
										</tr>
									</table>
								</td>
								<td colspan="2">Netto</td>
								<td class="text-right">
									<div class="currency" data-content="{{ $po->currencysymbol }}">
										<?php
										$netto = $subtotal - $total_discount;
										echo format_number($netto);
										?>
									</div>
								</td>
							</tr>
							<tr>
								<td colspan="5" rowspan="5" style="vertical-align:top !important;border-bottom:0 !important;border-top:0 !important">
									<table>
										<tr>
											<td class="border-0 p-0" style="width: 200px;border:none !important; vertical-align:top !important;">{{ $t_pic }} </td>
											<td class="border-0 p-0" style="border:none !important"><div>:</div><div style="margin-top:-1.3rem !important;margin-left:0.5rem !important;" class="ms-2">{!! $po->notesDescription !!}</div></td>
										</tr>
										<tr>
											<td class="border-0 p-0" style="width: 200px;border:none !important; vertical-align:top !important;margin-top: 3rem !important;">{{ $t_notes }} </td>
											<td class="border-0 p-0" style="border:none !important"><div>:</div><div style="margin-top:-1.3rem !important;margin-left:0.5rem !important; width:300px !important;" class="ms-2"> {!! $po->notes?$po->notes:' -' !!}</div></td>
										</tr>
									</table>
								</td>
								<td colspan="2">PPH <small>({{ $po->pph }} %)</small></td>
								<td class="text-right">
									<div class="currency" data-content="{{ $po->currencysymbol }}">
										(<?php
											$pph = 	($po->pph / 100) * $netto;
											echo ($po->currency=='IDR') ? format_number(numberPrecision($pph)) : format_number($pph);
											?>)
									</div>
								</td>
							</tr>
							<tr>
				
								<td colspan="2">{{ $t_tax }} <small>(11%)</small></td>
								<td class="text-right">
									<div class="currency" data-content="{{ $po->currencysymbol }}">
										<?php
										$ppn = 0;
										if ($po->ppn == "11") {
											$ppn = 	(11 / 100) * $netto;
											echo ($po->currency=='IDR') ? format_number(numberPrecision($ppn)) : format_number($ppn);
										}
										?>
									</div>
								</td>
							</tr>
				
							<?php
							$send_expense = $po->send_expense;
							$send_expense_ppn_caption = '';
							if ($po->send_expense_ppn == 1) {
								$send_expense_ppn_caption = "+ PPN 11%";
								$send_expense_ppn = (11 / 100) * $send_expense;
								$send_expense = $send_expense_ppn + $send_expense;
							}
							?>
							<tr>
								<td colspan="2">{{ $t_delivery_price }} <small>{{ $send_expense_ppn_caption }}</small></td>
								<td class="text-right">
									<div class="currency" data-content="{{ $po->currencysymbol }}">
										{{ ($po->currency=='IDR') ? format_number(numberPrecision($send_expense)) :  format_number($send_expense) }}
									</div>
								</td>
							</tr>
							<tr>
								<td colspan="2">Total</td>
								<td class="text-right">
									<div class="currency" data-content="{{ $po->currencysymbol }}">
										<?php
										$total = ($netto + $ppn + $send_expense) - $pph;
										echo ($po->currency=='IDR') ? format_number(numberPrecision($total)) : format_number($total);
										?>
									</div>
								</td>
							</tr>
							<tr>
								<td colspan="2">{{ $t_downpayment }}</td>
								<td class="text-right">
									<div class="currency" data-content="{{ $po->currencysymbol }}"> {{ ($po->currency=='IDR') ? format_number(numberPrecision($po->down_payment)) : format_number($po->down_payment) }} </div>
								</td>
							</tr>
							<tr>
								<td colspan="5" rowspan="3" style="padding: 0px !important;">
									<table>
										<tr>
											<td style="border-bottom:0 !important;border-top:0 !important;border-left:0 !important">
												{{ $t_confirm }} <br>
												{{ $t_confirm_name }}: <br>
												{{ $t_confirm_date }}: <br>
												{!! $t_confirm_caption !!}
											</td>
											<td style="border-bottom:0 !important;border-top:0 !important;border-right:0 !important">
												<span class="font-weight-bold">{{ $t_po_term }}: </span> <br>
												{!! $po->po_termDescription !!}
											</td>
										</tr>
									</table>
								</td>
								<td colspan="2" class="text-center">
									<span class="text-center font-weight-bold">{{ $t_prepared }}:</span>
								</td>
								<td class="text-center" style="vertical-align:bottom;">
									<span class="text-center font-weight-bold">{{ $t_approved }}:</span>
				
								</td>
							</tr>
							<tr>
								{{-- <td colspan="2" class="text-center">
									@if($po->approved_by != 0 && in_array($po->status, [2, 4, 5]) || $po->approved_by != null && in_array($po->status, [2, 4, 5]))
										@php
											$ttdAppFirst =getTTDUserByID(getApprovalFirstPurchasing($po->company_id)->user_id)
										@endphp
										@if($po->created_by == getApprovalFirstPurchasing($po->company_id)->user_id)
										<div class="row" style="align-items:center;justify-content:center;" id="ttd_">
											<span style="width: 50%;">
												@if(getTTDUserByID($po->created_by)->ttd)
												<img src="{{ asset('storage/'.(getTTDUserByID($po->created_by))->ttd) }}" alt="" class="fit-image">
												@endif
											</span>
										</div>
										@else
										<div class="row" id="ttd_">
											<span style="width: 50%">
												@if(getTTDUserByID($po->created_by)->ttd)
												<img src="{{ asset('storage/'.(getTTDUserByID($po->created_by))->ttd) }}" alt="" class="fit-image">
												@endif
											</span>
											<span style="width: 50%">
												@if($ttdAppFirst->ttd)
												<img src="{{ asset('storage/'.$ttdAppFirst->ttd) }}" alt="" class="fit-image">
												@endif
											</span>
										</div>
										@endif
									@else
									<br>
									<br>
									<br>
									<br>
									<br>
									<br>
									<br>
									<br>
									@endif
								</td>
								<td class="text-center">
									@if($po->ttd != null && in_array($po->status, [2, 4, 5]))
									<div id="ttd_" style="vertical-align:bottom;">
										@if($po->ttd)
										<img src="{{ asset('storage/'.$po->ttd) }}" alt="" class="fit-image2">
										@endif
									</div>
									@else
									<br>
									<br>
									<br>
									<br>
									<br>
									<br>
									<br>
									<br>
									@endif
								</td> --}}
								<td colspan="2" class="text-center">
									<br>
									<br>
									<br>
									<br>
									<br>
								</td>
								<td class="text-center" style="vertical-align:bottom;">
									<br>
									<br>
									<br>
									<br>
									<br>
								</td>
							</tr>
							<td colspan="2" class="text-center">
								<span style="text-transform:capitalize">{{ strtolower($po->created) }}</span>
								@if ($po->status != 3 && $po->approved_by != NULL)
								/ <span style="text-transform:capitalize">Purchasing Checker</span>
								@endif
							</td>
							<td class="text-center" style="vertical-align:bottom;">
								Head of Puchasing
							</td>
							</tr>
							<tr>
								<td colspan="8">{{ $t_finance }}</td>
							</tr>
							<tr>
								<td colspan="8" class="border-0" style="padding:0 !important">
									<table class="border-0 table m-0">
										<tr>
											<td class="text-center font-weight-bold" style="width:16.6%;border-bottom:0 !important;border-top:0 !important;border-left:0 !important;">{{ $t_date }}</td>
											<td class="text-center font-weight-bold" style="width:16.6%;border-bottom:0 !important;border-top:0 !important;">{{ $t_bank }}</td>
											<td class="text-center font-weight-bold" style="width:16.6%;border-bottom:0 !important;border-top:0 !important;">{{ $t_voucher }}</td>
											<td class="text-center font-weight-bold" style="width:16.6%;border-bottom:0 !important;border-top:0 !important;">{{ $t_cek }} </td>
											<td class="text-center font-weight-bold" style="width:16.6%;border-bottom:0 !important;border-top:0 !important;">{{ $t_jumlah }}</td>
											<td class="text-center font-weight-bold" style="width:16.6%;border-bottom:0 !important;border-top:0 !important;border-right:0 !important;">{{ $t_paraf }}</td>
										</tr>
										<tr>
											<td class="text-center" style="border-bottom:0 !important;border-left:0 !important;"><br> <br> <br></td>
											<td class="text-center" style="border-bottom:0 !important;"><br> <br> <br></td>
											<td class="text-center" style="border-bottom:0 !important;"><br> <br> <br></td>
											<td class="text-center" style="border-bottom:0 !important;"><br> <br> <br></td>
											<td class="text-center" style="border-bottom:0 !important;"><br> <br> <br></td>
											<td class="text-center" style="border-bottom:0 !important;border-right:0 !important;"><br> <br> <br></td>
										</tr>
									</table>
								</td>
							</tr>
				
						</tbody>
					</table>
					<small>
						{{ $t_nodpm }} : {{ $po->dpm_no }} / 
						{{ $last_print }} : {!! Carbon\Carbon::parse($po->last_print)->formatLocalized('%A, %d %B %Y Pukul %H:%M:%S') !!}
					</small>
				</div>
		   	</td>
		</tr>
	  </tbody>
 </table>




</body>

</html>