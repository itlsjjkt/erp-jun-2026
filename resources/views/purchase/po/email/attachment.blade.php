<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta name="csrf-token" content="{{ csrf_token() }}">
	<title>{{ config('app.name', 'Laravel') }}</title>

	<link href="{{ mix('/css/app.css') }}" rel="stylesheet">
	<link href="{{ public_path('/css/custom.css') }}" rel="stylesheet">
	<style>
		#ttd_ {
			position: relative;
			padding: 2px;
			overflow: visible;
			background-color:transparent !important;
		}

		.fit-image {
			vertical-align: bottom;
			width: auto;
			height: 100px;
			object-fit: cover;
			background-color:transparent !important;
		}

		.fit-image2 {
			width: auto;
			height: 100px;
			object-fit: cover;
			background-color:transparent !important;
		}

		.table td,
		.table th {
			padding: 0.2em 0.6em !important;
			vertical-align: middle !important;
			background-color:transparent !important;
		}

		body {
			font-size: 7pt;
            line-height: 1.5 !important;
            font-weight: normal;
			margin: 0;
			font-family: "Helvetica" !important;
			background-color:transparent !important;
		}

		@page {
            margin-top: 150px;
            margin-bottom: 50px;
			background-color:transparent !important;
        }

        header {
            position: fixed;
            line-height: 1.4 !important;
            top: -130px;
            left: 0px;
            right: 0px;
			background-color:transparent !important;
        }

		.table-bordered th,
		.table-bordered td,
		.border {
			border: 0.5px solid black !important;
			border-bottom: 0.5px solid black !important;
			border-right: 0.5px solid black !important;
			background-color:transparent !important;

		}

		.border-0 {
			border: 0;
		}

		table {
			width: 100%;
			background-color:transparent !important;
		}

		table.report-container {
			page-break-after: always;
			background-color:transparent !important;
		}

		.report-header {
			display: table-header-group;
			background-color:transparent !important;
		}

		tfoot.report-footer {
			display: table-footer-group;
			background-color:transparent !important;
		}

		table.report-container div.article {
			page-break-inside: avoid;
			background-color:transparent !important;
		}
        small{
            font-size:5.5pt !important;
        }

		@media print {
            @page {
                margin: 0.3cm;
                size: letter;
            }
        }
		p {
			margin: 0;
			background-color:transparent !important;
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
		$t_confirm_date  = 'Tanggal';
		$t_confirm_caption  = 'Setelah ditandatangani, <br> mohon di Fax kembali ke kami.';
		$t_nopo  = 'No. PO';
		$t_nopr  = 'No. PR';
		$t_created  = 'Tgl Buat';
		$t_tax      = 'PPN';
		$t_date      = 'Tanggal';
		$t_estimated_receipt  = 'Estimasi Penerimaan';
        $t_delivery_day  = 'Waktu Pengiriman';
        $t_day = 'Hari';
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
		$t_estimated_receipt  = 'Estimated Receipt';
        $t_delivery_day  = 'Delivery Time';
        $t_day = 'Days';
	}
	?>
	<header>
        <table style="width:100% !important">
            <tbody>
                <tr style="font-weight:bold; font-size: 12px; text-decoration:underline;">
                    <td colspan="2" style="text-align: center;">
                        PURCHASE ORDER
                    </td>
                </tr>
            </tbody>
        </table>
		<table >
            <tr>
                <td class="border-0" style="width:30%;padding: 0 !important;vertical-align:top !important;">
                    <strong class="">{{ $po->company }}</strong><br>
                    {!! $po->companyAddress !!}<br>
                    Telp. {{ $po->companyTelp }} <br> Fax. {{ $po->companyFax }}
                </td>
                <td class="border-0 text-center" style="width:40%; margin-top: -30px; vertical-align: middle;">
                    <table style="margin-top: -16px;">
                        <tbody>
                            <tr>
                                <td class="border-0">
                                    {{$t_nopo}}
                                </td>
                                <td class="border-0">
                                    : {{ $po->doc_no }}
                                </td>
                            </tr>
                            <tr>
                                <td class="border-0">
                                    {{ $t_created }}
                                </td>
                                <td class="border-0">
                                    : {{ idDate($po->created_at) }}
                                </td>
                            </tr>
                            <tr>
                                <td class="border-0">
                                    {{ $t_nopr }}
                                </td>
                                <td class="border-0">
                                    : {{ $po->pr_no }}
                                </td>
                            </tr>
                            <tr>
                                <td class="border-0">
                                    Departemen/Kapal
                                </td>
                                <td class="border-0">
                                    : {{ $po->department }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </td>
                <td style="width:30%;vertical-align:tops !important;">
                    <table style="margin-top:-8px !important">
                        <tbody>
                            <tr>
                                <td style="padding: 3px !important;border: 0.5px solid black !important;">
                                    {{ $t_kepada }}<br>
                                    <strong>{{ $po->supplier }}</strong><br>
                                    {{ $po->picTitle }} {{ $po->picName }}<br>
                                    Telepon : {!! str_contains($po->picTelp, '||') ? str_replace('||', '<br>Mobile Phone : ', $po->picTelp) : $po->picTelp ?? '-' !!} <br>
                                    Email :
                                    <?php
                                        if (is_array($po->picEmail)) {
                                            echo implode('<br>', $po->picEmail);
                                        } else {
                                            $emailText = $po->picEmail ?? ' -';
                                            echo str_replace(';', '<br>', $emailText);
                                        }
                                    ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
		</table>
	</header>
	<table class="table table-bordered headerrr" style="width: 100% !important;"  cellspacing="0">
		<tbody>
			<tr style="border-right: 0.5px solid black !important;" class="w-100">
				<th class="text-center text-uppercase" style="width:5%" >NO</th>
				<th class="text-center text-uppercase" style="width:20%" >{{ $t_item }}</th>
				<th class="text-center text-uppercase" style="width:22% !important;" >{{ $t_spesifikasi }}</th>
				<th colspan="2" class="text-center text-uppercase" style="width:5%" >QTY</th>
				<th class="text-center text-uppercase" style="width:13%" >{{ $t_harga }} </th>
				<th class="text-center text-uppercase" style="width:4%" >DISC<br> @if($po->discount_type == 1)(%) @endif</th>
				<th colspan="2" class="text-center text-uppercase border-right" style="width:15%;border-right: 0.5px solid black !important;">{{ $t_total }}</th>
			</tr>
			@php
			$no = 1;
			$subtotal = 0;
			@endphp
			@foreach ($po_items as $item)
			<tr>
				<td style="text-align:center">{{ $no }}</td>
				<td style="line-height: 1.5 !important;">{{ $item->product }}
                    <small>
                        <br>{!! $item->productPartNumber != NULL ? 'PN/Spec: '.$item->productPartNumber : 'PN/Spec: -' !!}
                        <br>{{ $item->productBrand != NULL ? 'Brand: '.$item->productBrand : 'Brand: -' }}
                    </small>
				</td>
				<td style="max-width: 340px; vertical-align: top !important;">
					{!! $item->specification !!}
				</td>
				<td class="text-center" style="text-align:center;border-right:0 !important; width:10px;">{{ $item->qty }}</td>
				<td class="text-center" style="text-align:center;border-left:0 !important;width:50px;">{{ $item->measure }}</td>
				<td class="text-center">
					<div class="" style="text-align:center" data-content="">{{ $po->currencysymbol }} {{  format_number($item->price) }} </div>
				</td>
				<td class="text-right">
					@if($po->discount_amount == 0 && $po->discount_type == 0)
						<div class="currency" data-content="{{ $po->currencysymbol }}"> </div>
					@endif
					{{ format_number($item->discount) }}
				</td>
				<td colspan="2" class="text-right" style="border-right: 0.5px solid black !important;">
					<div class="currency" data-content="{{ $po->currencysymbol }}">
						<?php
						$total = $item->price * $item->qty - (($item->price * $item->qty) *  $item->discount / 100);
						?>
						{{ format_number($total) }}
					</div>
				</td>
			</tr>
        </tbody>
        @php
        $subtotal += $total;
        $no++;
        @endphp
        @endforeach
        <tbody>
            <tr>
                <td colspan="5" rowspan="8" style="vertical-align:top !important;border-bottom:0 !important;border-top:0 !important;border-top: 0.5px solid black !important;padding: 0 !important;">
                    <table>
                        <tr>
                            <td class="border-0 p-0" style="width: 120px;border:none !important">{{ $t_payment_method }}</td>
                            <td class="border-0 p-0" style="border:none !important">: {{ $po->payment_method }}</td>
                        </tr><tr>
                            <td class="border-0 p-0" style="width: 120px;border:none !important">{{ $t_price_term }}</td>
                            <td class="border-0 p-0" style="border:none !important">: {{ $po->price_term }} {{ $po->price_term_location }}</td>
                        </tr>
                        <tr>
                            <td class="border-0 p-0" style="width: 120px;border:none !important; vertical-align:top !important;">{{ $t_payment_term }}</td>
                            <td class="border-0 p-0" style="border:none !important">: {{ $po->payment_term }}</td>
                        </tr>
                        {{-- Tanggal Kirim --}}
                        @if(!$po->dph_id)
                            <tr>
                                <td class="border-0 p-0" style="width: 120px;border:none !important; vertical-align:top !important;">{{ $t_delivery_date }}</td>
                                <td class="border-0 p-0" style="border:none !important">: {{ $po->delivery_date? date('d M Y',strtotime( $po->delivery_date)) : ' -' }}</td>
                            </tr>
                        @else
                            {{-- WAKTU KIRIM --}}
                            <tr>
                                <td class="border-0 p-0" style="width: 120px;border:none !important; vertical-align:top !important;">{{ $t_delivery_day }}</td>
                                <td class="border-0 p-0" style="border:none !important">: {{ $po->estimated_delivery_day != 0 ? $po->estimated_delivery_day . ' ' . $t_day : ' -'}}</td>
                            </tr>
                            {{-- ESTIMASI TIBA --}}
                            <tr>
                                <td class="border-0 p-0" style="width: 120px;border:none !important; vertical-align:top !important;">{{ $t_estimated_receipt }}</td>
                                <td class="border-0 p-0" style="border:none !important">: {{ $po->estimated_receipt? date('d M Y',strtotime( $po->estimated_receipt)) : ' -' }}</td>
                            </tr>
                        @endif
                        <tr>
                            <td class="border-0 p-0" style="width: 120px;border:none !important; vertical-align:top !important;">{{ $t_pic }} <br></td>
                            <td class="border-0 p-0" style="border:none !important"><div>:</div><div style="margin-top:-1.3rem !important;margin-left:0.5rem !important;" class="ms-2">{!! $po->notesDescription !!}</div></td>
                        </tr>
                        <tr>
                            <td class="border-0 p-0" style="width: 120px; border:none !important; vertical-align:top !important; margin-top: 3rem !important;">{{ $t_notes }}</td>
                            <td class="border-0 p-0" style="border:none !important;vertical-align:top !important;">
                                <div>:</div>
                                <div style="margin-top:-1rem !important; margin-left:-0.2rem !important; width:100%; display: flex; align-items: stretch;" class="ms-2 row">
                                    <div style="flex: 1; padding: 10px; margin-top:-10px;">{!! $po->notes ? $po->notes : ' -' !!}</div>
                                    <div style="display: flex; align-items: center; margin-left: auto; margin-top: 8px;"></div>
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>
                <td colspan="2">Sub Total</td>
                <td colspan="2" style="border-right: 0.5px solid black !important;" class="text-right">
                    <div class="currency" data-content="{{ $po->currencysymbol }}"> {{ format_number($subtotal)}} </div>
                </td>
            </tr>
            <tr>
                <td colspan="2">{{ $t_diskon }}
                    @if ($po->discount_item == false &&  $po->discount_type == 1 &&  $po->discount_amount != 0)
                        <small>{{ $po->discount_amount  }}%</small>
                    @endif
                </td>
                <td class="text-right" colspan="2" style="border-right: 0.5px solid black !important;">
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
                <td colspan="2">Netto</td>
                <td class="text-right" colspan="2" style="border-right: 0.5px solid black !important;">
                    <div class="currency" data-content="{{ $po->currencysymbol }}">
                        <?php
                        $netto = $subtotal - $total_discount;
                        echo format_number($netto);
                        ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td colspan="2">PPH <small>{{ $po->pph ? '('.$po->pph.'%)' : '' }}</small></td>
                <td class="text-right" colspan="2" style="border-right: 0.5px solid black !important;">
                    <div class="currency" data-content="{{ $po->currencysymbol }}">
                        (<?php
                            $pph = 	($po->pph / 100) * $netto;
                            echo $pph ? format_number($pph) : '0,00';
                            ?>)
                    </div>
                </td>
            </tr>
            <tr>
                <td colspan="2">{{ $t_tax }} <small>({{$po->ppn}}%)</small></td>
                <td class="text-right" colspan="2" style="border-right: 0.5px solid black !important;">
                    <div class="currency" data-content="{{ $po->currencysymbol }}">
                        <?php
                            $ppn_ = $po->ppn ?? 0;
                            $ppn = 	($ppn_ / 100) * $netto;
                            echo $ppn ? format_number($ppn) : '0,00';
                        ?>
                    </div>
                </td>
            </tr>
            <?php
            $send_expense = $po->send_expense;
            $send_expense_ppn_caption = '';
            if ($po->send_expense_ppn == 1 || $po->send_expense_ppn == 11) {
                $send_expense_ppn_caption = "+ PPN 11%";
                $send_expense_ppn = (11 / 100) * $send_expense;
                $send_expense = $send_expense_ppn + $send_expense;
            }
            ?>
            <tr>
                <td colspan="2">{{ $t_delivery_price }} <small>{{ $send_expense_ppn_caption }}</small></td>
                <td class="text-right" colspan="2" style="border-right: 0.5px solid black !important;">
                    <div class="currency" data-content="{{ $po->currencysymbol }}">
                        {{ $send_expense ? format_number($send_expense) : '0,00' }}
                    </div>
                </td>
            </tr>
            <tr>
                <td colspan="2">Total</td>
                <td class="text-right" colspan="2" style="border-right: 0.5px solid black !important;">
                    <div class="currency" data-content="{{ $po->currencysymbol }}">
                        <?php
                        $total = ($netto + $ppn + $send_expense) - $pph;
                        echo $total ? format_number($total) : '0,00';
                        ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td colspan="2">{{ $t_downpayment }}</td>
                <td class="text-right" colspan="2" style="border-right: 0.5px solid black !important;">
                    <div class="currency" data-content="{{ $po->currencysymbol }}"> {{ $po->down_payment ? format_number($po->down_payment) : '0,00' }} </div>
                </td>
            </tr>
        </tbody>
        <tbody style="padding:0 !important;">
            <tr>
                <td colspan="2" rowspan="8" style="vertical-align:top !important;padding-bottom:0 !important;">
                    <div>
                        {{ $t_confirm }} <br>
                        {{ $t_confirm_name }}: <br>
                        {{ $t_confirm_date }}: <br>
                        {!! $t_confirm_caption !!}
                    </div>
                    <br>
                    <div style="text-align: center; position: relative; z-index: -2;" class="qrCode_">
                        @if (in_array($po->status, [2, 4, 5, 6, 8]))
                            <img src="{{ $qrCodeImage }}" alt="QR Code" type="png">
                        @endif
                    </div>
                </td>
                <td colspan="3" rowspan="8" style="vertical-align:top !important;padding-bottom:0px !important;">
                    <span style="font-weight:bold !important;">{{ $t_po_term }}: </span> <br>
                    <span>
                        {!! $po->po_termDescription !!}
                    </span>
                </td>
                <td colspan="2" style="text-align: center; vertical-align: middle; font-weight:bold !important;">
                    <span class="text-center font-weight-bold">{{ $t_prepared }} :</span>
                </td>
                <td colspan="2" style="text-align: center; vertical-align: middle; font-weight:bold !important;border-right: 0.5px solid black !important;">
                    <span class="text-center font-weight-bold">{{ $t_approved }} :</span>
                </td>
            </tr>
            <tr>
                <td colspan="2" rowspan="6">
                @if($po->approved_by != 0 && in_array($po->status, [2, 4, 5]) || $po->approved_by != null && in_array($po->status, [2, 4, 5]))
                    @php
                        $ttdAppFirst =getTTDUserByID(getApprovalFirstPurchasing($po->id)->user_id)
                    @endphp
                    @if($po->created_by == getApprovalFirstPurchasing($po->id)->user_id || $po->created_by == 310 || $po->created_by == 215)
                        <div id="ttd_" style="vertical-align:middle;">
                            <table>
                                <tbody>
                                    <tr>
                                        <td style="width: 100%;height:100px !important; border:none !important;">
                                            @if(getTTDUserByID($po->created_by)->ttd)
                                                <img src="{{ asset('storage/'.(getTTDUserByID($po->created_by))->ttd) }}" alt="" class="fit-image2" style="transform: scale(2) translateY(-20px) translateX(10px); transform-origin: center;">
                                            @endif
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="row" id="ttd_" style="vertical-align:middle;">
                            <table>
                                <tbody>
                                    <tr>
                                        <td style="width:50%; border:none !important; height:100px !important;">
                                            <span style="width: 50%; vertical-align: middle;">
                                                @if(getTTDUserByID($po->created_by)->ttd)
                                                    <img src="{{ asset('storage/'.(getTTDUserByID($po->created_by))->ttd) }}" alt="" class="fit-image" style="transform: scale(1.5) translateX(-10px) translateY(-15px); transform-origin: center;">
                                                @endif
                                            </span>
                                        </td>
                                        <td style="width:50%; border:none !important; height:100px !important;">
                                            <span style="width: 50%; vertical-align: middle;">
                                                @if($ttdAppFirst->ttd)
                                                    <img src="{{ asset('storage/'.$ttdAppFirst->ttd) }}" alt="" class="fit-image" style="transform: scale(1.5) translateX(-10px) translateY(-15px); transform-origin: center;">
                                                @endif
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    @endif
                @endif
                </td>
                <td rowspan="6" style="border-right:none !important;">
                    @if($po->ttd != null && in_array($po->status, [2, 4, 5]))
                    <div id="ttd_" style="vertical-align:bottom;">
                        <table>
                            <tbody>
                                <tr>
                                    <td style="width: 100%; height:100px !important; border:none !important">
                                        @if($po->ttd)
                                            <img src="{{ asset('storage/'.$po->ttd) }}" alt="" class="fit-image2">
                                        @endif
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    @endif
                </td>
                <td style="width:0px !important;border-bottom:none !important;border-left:none !important;border-right: 0.5px solid black !important;"></td>
            </tr>
            <tr>
                <td style="border-top:none !important;border-bottom:none !important;border-left:none !important;border-right: 0.5px solid black !important;"></td>
            </tr>
            <tr>
                <td style="border-top:none !important;border-bottom:none !important;border-left:none !important;border-right: 0.5px solid black !important;"></td>
            </tr>
            <tr>
                <td style="border-top:none !important;border-bottom:none !important;border-left:none !important;border-right: 0.5px solid black !important;"></td>
            </tr>
            <tr>
                <td style="border-top:none !important;border-bottom:none !important;border-left:none !important;border-right: 0.5px solid black !important;"></td>
            </tr>
            <tr>
                <td style="border-top:none !important;border-bottom:none !important;border-left:none !important;border-right: 0.5px solid black !important;"></td>
            </tr>
            <tr>
                <td colspan="2" style="text-align: center; vertical-align: middle;">
                    <span style="text-transform:capitalize">{{ strtolower($po->created) }}</span>
                    @if ($po->status != 3 && $po->approved_by != NULL)
                    / <span style="text-transform:capitalize">Purchasing Checker</span>
                    @endif
                </td>
                <td colspan="2" style="text-align: center; vertical-align: middle; border-right: 0.5px solid black !important;">
                    Head of Purchasing
                </td>
            </tr>
        </tbody>
        <tbody>
            <tr>
                <td colspan="9" style="border-right: 0.5px solid black !important;">{{ $t_finance }}</td>
            </tr>
            <tr>
                <td colspan="9" class="border-0" style="padding:0 !important; border:none!important;border-left: 0.5px solid black !important;">
                    <table class="border-0 table m-0" cellspacing="0" cellpadding="0" style="border-bottom: 0.5px solid black !important;">
                        <tr>
                            <td class="text-center font-weight-bold" style="width:16.6%;border-bottom:0 !important;border-top:0 !important;border-left:0 !important;">{{ $t_date }}</td>
                            <td class="text-center font-weight-bold" style="width:16.6%;border-bottom:0 !important;border-top:0 !important;">{{ $t_bank }}</td>
                            <td class="text-center font-weight-bold" style="width:16.6%;border-bottom:0 !important;border-top:0 !important;">{{ $t_voucher }}</td>
                            <td class="text-center font-weight-bold" style="width:16.6%;border-bottom:0 !important;border-top:0 !important;">{{ $t_cek }} </td>
                            <td class="text-center font-weight-bold" style="width:16.6%;border-bottom:0 !important;border-top:0 !important;">{{ $t_jumlah }}</td>
                            <td class="text-center font-weight-bold" style="width:16.6%;border-bottom:0 !important;border-top:0 !important;">{{ $t_paraf }}</td>
                        </tr>
                        <tr>
                            <td class="text-center" style="border-bottom:0 !important;border-left:0 !important;"><br> <br> <br></td>
                            <td class="text-center" style="border-bottom:0 !important;"><br> <br> <br></td>
                            <td class="text-center" style="border-bottom:0 !important;"><br> <br> <br></td>
                            <td class="text-center" style="border-bottom:0 !important;"><br> <br> <br></td>
                            <td class="text-center" style="border-bottom:0 !important;"><br> <br> <br></td>
                            <td class="text-center" style="border-bottom:0 !important; border-right: 0.5px solid black !important;"><br> <br> <br></td>
                        </tr>
                    </table>
                </td>
            </tr>
        </tbody>
	</table>
</body>
</html>
