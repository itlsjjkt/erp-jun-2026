<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta name="csrf-token" content="{{ csrf_token() }}">
	<title>{{ config('app.name', 'Laravel') }}</title>

	<link href="{{ mix('/css/app.css') }}" rel="stylesheet">
	<link href="{{ asset ('/css/custom.css') }}" rel="stylesheet">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

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
	</style>

</head>

<body>
	<?php setlocale(LC_TIME, 'id_ID.utf8') ?>

	<?php
	if ($po->po_term_id == 1) {
		$t_kepada = 'Kepada Yth,';
		$t_total = 'TOTAL';
		$t_nopo  = 'No. PO';
		$t_wesend = 'Berikut ini kami kirimkan :';
		$t_nproccess = 'Mohon Untuk Tidak Di Proses.';
		$t_newsup ='MOHON DIKIRIMKAN NPWP, DAN DIBUATKAN SURAT PERNYATAAN NOMER REKENING, BERIKUT KAMI BERIKAN CONTOH NYA.';
		if($po->price_term_location == "JAKARTA"){
			$t_perf_inv = 'MOHON UNTUK DIKIRIMKAN INVOICE ASLI';
		}else{
			$t_perf_inv = 'MOHON UNTUK DIKIRIMKAN PERFORMA INVOICE';
		}
		$t_perfori = 'MOHON UNTUK DIKIRIMKAN INVOICE ASLI & BERITA ACARA ASLI SETELAH PEKERJAAN SELESAI.';
		$t_confirm = '<p style="margin:0;">Mohon konfirmasi melalui email, jika PO sudah diterima dan koordinasikan dengan logistik kami perihal kesiapan barang dan pengirimannya.</p>
					<p style="margin:0;">Kami tunggu konfirmasinya 2x24 jam, apabila melebihi waktu tersebut tidak ada konfirmasi melalui email, maka PO tidak akan diproses.</p><br>
					<p style="margin:0;"><strong>Note :Untuk Pengiriman Dokumen Asli mohon untuk dikirimkan ke alamat kantor kami di Belleza.</strong></p>';
		$t_alamat = '<p style="margin:0;"><strong>Berikut alamatnya : <br>
					PT Karunia Perkasa Megah (Harita Group) <br>
					Gapura Prima Office Tower The Bellezza Lt 20 <br>
					(Sebrang Grand ITC Permata Hijau)<br>
					Jl Letjen Soepeno No 34 Arteri Permata Hijau,  <br>
					Jakarta Selatan, 12210 <br>
					021-29229103-05 <br>
					Note : Untuk pengiriman barang harap hubungi Nomor yang sudah tercantum di PO.
					</strong></p><br>';
		$t_regard = '<p style="margin:0;">
					Thanks & Regards, <br>
					Admin Purchasing
					</p>';
	} else {
		$t_kepada = 'Dear,'; 
		$t_total = 'TOTAL';
		$t_nopo  = 'PO Number';
		$t_wesend = 'Hereby, we send :';
		$t_nproccess = 'Please do not process..';
		$t_newsup ='PLEASE SEND THE NPWP AND PROVIDE A STATEMENT LETTER FOR THE ACCOUNT NUMBER. BELOW IS AN EXAMPLE FOR YOUT REFERENCE.';
		$t_perf_inv = 'PLEASE SEND THE PERFORMANCE INVOICE';
		if($po->price_term_location == "JAKARTA"){
			$t_perf_inv = 'PLEASE SEND THE ORIGINAL INVOICE';
		}else{
			$t_perf_inv = 'PLEASE SEND THE PERFORMANCE INVOICE';
		}
		$t_perfori = 'PLEASE SEND THE ORIGINAL INVOICE AND ORIGINAL MINUTES OF MEETING AFTER THE WORK IS COMPLETED.';
		$t_confirm = '<p style="margin:0;">Please confirm via email once the PO has been received and coordinate with our logistics team regarding the readiness and shipment of the goods.</p>
					<p style="margin:0;">We expect your confirmation within 2x24 hours. If there is no confirmation via email after this period, the PO will not be processed.</p><br>
					<p style="margin:0;"><strong>Note: For the shipment of original documents, please send them to our office address at Belleza.</strong></p>';
		$t_alamat = '<p style="margin:0;"><strong>Here is the address: <br>
					PT Karunia Perkasa Megah (Harita Group) <br>
					Gapura Prima Office Tower The Bellezza, 20th Floor <br>
					(Across from Grand ITC Permata Hijau)<br>
					Jl Letjen Soepeno No 34, Arteri Permata Hijau, <br>
					South Jakarta, 12210 <br>
					021-29229103-05 <br>
					Note: For the shipment of goods, please contact the number listed on the PO.</strong></p><br>';
		$t_regard = '<p style="margin:0;">
					Thanks & Regards, <br>
					Admin Purchasing
					</p>';
	}
	?>

	<main class="" style="min-height:100vh; width:100%; padding:5vh; color:black;">
		<table class="border-0" style="margin-bottom:5vh;">
			<tr>
				<td class="border-0">
					<strong>{{ $t_kepada }} </strong>
				</td>
			</tr>
			<tr>
				<td class="border-0">{{ $po->picTitle }} {{ $po->picName }}</td>
			</tr>
		</table>
		<div>
			<?php $total = 0; ?>
			@foreach ($po_items as $item)
			<?php
			$total += $item->price * $item->qty - (($item->price * $item->qty) *  $item->discount / 100);
			?>
			@endforeach

			<?php
			if ($po->discount_item == false) {
				if($po->discount_type == 1){
					$po->discount_amount = $total * ((float)$po->discount_amount/100);
				}
				$netto = $total - (float)$po->discount_amount;
			}
			else{
				$netto = $total;
			}
			if ((float)$po->send_expense_ppn == 1 || (float)$po->send_expense_ppn == 11) {
				$send_expense_ppn = (11 / 100) * (float)$po->send_expense;
				$po->send_expense = (float)$send_expense_ppn + (float)$po->send_expense;
			}
			$ppn = $netto * (float)$po->ppn / 100;
			$pph = $netto * (float)$po->pph / 100;
			$payment_amount = $netto - (float)$pph + (float)$ppn + (float)$po->send_expense;
			?>
			@if($po->status == 8)
			<p class="border-0">{{$t_wesend}} </p>
			<ul>
				<li class="">CANCEL {{ $po->doc_no . '  ' }} <strong> {{ $po->currencysymbol. ' ' .format_number($payment_amount) }} </strong></li>	
			</ul>
			@else
			<p class="border-0" style="margin:0;">{{$t_wesend}} </p>
			<p class="fw-bold" style="font-weight: bold;margin:0;">{{ $t_nopo }} : {{ $po->doc_no }} </p>
			<p class="fw-bold" style="font-weight: bold;margin:0;">{{ $t_total }} : {{ $po->currencysymbol }} &nbsp; {{ format_number($payment_amount) }} </p>	
			@endif
		</div>
		<div style="margin:0; margin-top:2vh;">
			@if($po->status == 8)
			<h4 style="margin:0; text-transform: uppercase; text-decoration:underline;">{{$t_nproccess}}</h4>
			@else
				<p><strong>
					{{-- CBD 1 || COD 2 --}}
					@if($po->typeBodyEmail == 1 || $po->typeBodyEmail == 2)
						@if($history_supplier < 1)
							{{$t_newsup}}
							<br>
						@endif				
						{{$t_perf_inv}}.
					{{-- DP --}}
					@elseif ($po->typeBodyEmail == 3)
							@if($history_supplier < 1)
								{{$t_newsup}}
								<br>
							@endif
							{{$t_perf_inv}} (DP {{$po->dp_percentage}} %).
					{{-- SETELAH PEKERJAAN SELESAI --}}
					@elseif ($po->typeBodyEmail == 4)
						@if($history_supplier < 1)
							{{$t_newsup}}
						<br>
						@endif
							{{$t_perfori}}
					{{-- SELAIN ITU --}}
					@else
						@if($history_supplier < 1)
							{{$t_newsup}}
							<br>
						@endif	
						{{$t_perf_inv}}.
					@endif
				</strong></p>
				{!!$t_confirm!!}
				
				{!!$t_alamat!!}

				{!!$t_regard!!}
			@endif
		</div>
	</main>
</body>

</html>
