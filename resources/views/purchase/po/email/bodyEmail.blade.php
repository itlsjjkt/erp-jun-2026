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
		$po->ppn = $netto * (float)$po->ppn / 100;
		$po->pph = $netto * (float)$po->pph / 100;
		$payment_amount = $netto - (float)$po->pph + (float)$po->ppn + (float)$po->send_expense;
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
			<tr>
				<td class="border-0">Berikut terlampir penawaran harga dari kami</td>
			</tr>
		</table>
		<!-- <div style="line-height:0;">
			<p style="margin:0"><strong>{{ $t_kepada }}</strong></p>
			<p style="margin:0">{{ $po->picTitle }} {{ $po->picName }}</p>
			<p style="margin:0">Berikut terlampir penawaran harga dari kami</p>
		</div> -->
		<div>
			<p class="fw-bold" style="font-weight: bold;">{{ $t_nopo }} : {{ $po->doc_no }} </p>
			<p class="fw-bold" style="font-weight: bold;">{{ $t_total }} : {{ $po->currencysymbol. ' ' .format_number($payment_amount) }} </p>
		</div>

		<div style="margin:0; margin-top:2vh;">
			{{-- CBD 1 || COD 2 --}}
			@if($po->typeBodyEmail == 1 || $po->typeBodyEmail == 2)
				@if($new_supplier)
					<p style="margin:0;"><strong>MOHON DIKIRIMKAN NPWP, DAN DIBUATKAN SURAT PERNYATAAN NOMER REKENING, BERIKUT KAMI BERIKAN CONTOH NYA.</strong></p><br>
				@else
					<p style="margin:0;"><strong>MOHON UNTUK DIKIRIMKAN PERF INVOICE.</strong></p><br>
				@endif				
			{{-- DP --}}
			@elseif ($po->typeBodyEmail == 3)
				@if($new_supplier)
					<p style="margin:0;"><strong>MOHON DIKIRIMKAN NPWP, DAN DIBUATKAN SURAT PERNYATAAN NOMER REKENING, BERIKUT KAMI BERIKAN CONTOH NYA.</strong></p><br>
				@else
					<p style="margin:0;"><strong>MOHON UNTUK DIKIRIMKAN PERF INVOICE (DP {{$po->dp_percentage}} %).</strong></p><br>
				@endif
			{{-- SETELAH PEKERJAAN SELESAI --}}
			@elseif ($po->typeBodyEmail == 4)
				@if($new_supplier)
					<p style="margin:0;"><strong>MOHON DIKIRIMKAN NPWP, DAN DIBUATKAN SURAT PERNYATAAN NOMER REKENING, BERIKUT KAMI BERIKAN CONTOH NYA.</strong></p><br>
				@else
					<p style="margin:0;"><strong>MOHON UNTUK DIKIRIMKAN INVOICE ASLI & BERITA ACARA ASLI SETELAH PEKERJAAN SELESAI.</strong></p><br>
				@endif
			@else
				@if($new_supplier)
					<p style="margin:0;"><strong>MOHON DIKIRIMKAN NPWP, DAN DIBUATKAN SURAT PERNYATAAN NOMER REKENING, BERIKUT KAMI BERIKAN CONTOH NYA.</strong></p><br>
				@else
					<p style="margin:0;"><strong>MOHON UNTUK DIKIRIMKAN PERF INVOICE.</strong></p><br>
				@endif	
			@endif
				
			<p style="margin:0;"><strong>Mohon konfirmasi melalui email, jika PO sudah diterima dan koordinasikan dengan logistik kami perihal kesiapan barang dan pengirimannya.</strong></p><br>
			<p style="margin:0;"><strong>Kami tunggu konfirmasinya 2x24 jam, apabila melebihi waktu tersebut tidak ada konfirmasi melalui email, maka PO tidak akan diproses.</strong></p><br>
			<p style="margin:0;"><strong>Note :Untuk Pengiriman Dokumen Asli mohon untuk dikirimkan ke alamat kantor kami di Belleza.</strong></p><br>
			<p style="margin:0;"><strong>Berikut alamatnya :</strong></p><br>
			<p style="margin:0;"><strong>
				PT Karunia Perkasa Megah (Harita Group) <br>
				Gapura Prima Office Tower The Bellezza Lt 20 <br>
				Jl Letjen Soepeno No 34 Arteri Permata Hijau,  <br>
				Jakarta Selatan, 12210 <br>
				021-29229103-05 <br>
				Note : Untuk pengiriman barang harap hubungi Nomor yang sudah tercantum di PO.
			</strong></p><br>

			<p style="margin:0;">
				Thanks & Regards, <br>
				Admin Purchasing <br>
				PT Karunia Perkasa Megah (Harita Group) <br>
				Gapura Prima Office Tower The Bellezza Lt 20 <br>
				Jl Letjen Soepeno No 34 <br>
				Arteri Permata Hijau, <br>
				Jakarta Selatan, 12210 <br>
				021-29229103-05
			</p><br>
		</div>
	</main>






</body>

</html>
