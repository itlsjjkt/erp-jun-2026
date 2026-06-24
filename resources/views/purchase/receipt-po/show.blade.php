@extends('layouts.app')

@section('page-header')
    Receipt Purchase Order
@endsection

@section('breadcrumbs')
<ol class="breadcrumb">
	<li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('purchasing.receipt-po.index') }}">Receipt Purchase Order</a></li>
	<li class="breadcrumb-item active" aria-current="page">Detail</li>
</ol>
@endsection

@section('content')
<style>
	.table td,
	.table th {
		padding: 0.3em 0.6em !important;
		vertical-align: middle !important;
	}
</style>
<div class="mB-40">
	<div class="bgc-white p-20 bd">
		<div class="row mb-3 justify-content-end">
			<div class="col-sm-6 ">
                <a href="{{ route('purchasing.receipt-po.index') }}" class="nav-link">
                    <i class="ti-arrow-left"></i> Kembali
                </a>
            </div>
			<div class="col-sm-6">
			@php
				use Illuminate\Support\Facades\Gate;
			@endphp
			@if(!Gate::allows('po_monitoring'))
				@if($po->status == 2 || $po->status == 4 || $po->status == 5)
				<a class="btn btn-outline float-right" href="#" title="Print Data" onclick='printExternal("/purchasing/po_print/{{ Hashids::encode($po->id) }}/print")'><i class="ti-printer icon-lg"></i></a>
				@elseif(isPoPriceAccess())
				<a class="btn btn-outline float-right" href="#" title="Print Data" onclick='printExternal("/purchasing/po_print/{{ Hashids::encode($po->id) }}/print")'><i class="ti-printer icon-lg"></i></a>
				@endif
			@endif
			</div>
		</div>
		<div class="d-block">
			<ul class="nav nav-tabs" role="tablist">
				<li class="nav-item">
					<a class="nav-link active" data-toggle="tab" href="#tab1" role="tab">Purchase Order (PO)</a>
				</li>
				<li class="nav-item">
					<a class="nav-link" data-toggle="tab" href="#tab2" role="tab">Histori PO</a>
				</li>
				@if (count($po_type_histories) > 0)
					<li class="nav-item">
						<a class="nav-link" data-toggle="tab" href="#tab4" role="tab">Histori Type PO</a>
					</li>
				@endif
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#tab-receipt" role="tab">Receipt Data</a>
                </li>
			</ul>
		</div>

		<div class="tab-content mt-5">
			<div class="tab-pane active" id="tab1" role="tabpanel" aria-labelledby="tab1">
				<h6 class="text-center font-weight-bold mB-30" style="text-decoration:underline">{{ $po->doc_no }}</h6>
				<div class="row">
					<div class="col-sm-6">
						<div class="row">
							<label class="col-sm-4">No. PR </label>
							<div class="col-sm-8">: {{ $po->pr_no }}</div>
						</div>
						<div class="row">
							<label class="col-sm-4">No. DPM </label>
							<div class="col-sm-8">: {{ $po->dpm_no }}</div>
						</div>
                        <div class="row">
							<label class="col-sm-4">Lokasi/Kapal </label>
							<div class="col-sm-7">: {{ $po->location ?? '-' }}</div>
						</div>
						<div class="row">
							<label class="col-sm-4">Departement </label>
							<div class="col-sm-7">: {{ $po->department }}</div>
						</div>
						<div class="row">
							<label class="col-sm-4">Dibuat Oleh</label>
							<div class="col-sm-7">: {{ $po->created }} [ {{ idDate($po->created_at) }}]
							</div>
						</div>
						<div class="row">
							<label class="col-sm-4">Status</label>
							<div class="col-sm-7">: {!! getStatusPO($po->status) !!}</div>
						</div>
                        <div class="row">
                            <label class="col-sm-4">Type</label>
                            <div class="col-sm-7">:
                                @if($po->type == 'lpb')
                                    <span class="badge badge-primary">LPB</span>
                                @else
                                    <span class="badge badge-info">BPB</span>
                                @endif
                            </div>
                        </div>
					</div>
					<div class="col-sm-6">
						<div class="row">
							<label class="col-sm-3">Supplier</label>
							<div class="col-sm-8">:
								{{ $po->supplier }}
							</div>
						</div>
						<div class="row">
							<label class="col-sm-3">Supplier Kontak</label>
							<div class="col-sm-8">:
								{{ $po->picTitle }} {{ $po->picName }} <br> <small class="ml-2"> Telepon : {!! str_replace('||', '<br>&nbsp;&nbsp;&nbsp;Mobile Phone : ', $po->picTelp) !!}
									<br>&nbsp;&nbsp;&nbsp;Email : {!! $po->picEmail !!} </small>
							</div>
						</div>
					</div>
				</div>
				@if ($po->status == 6)
					<hr>
					<div class="alert alert-danger"><strong> DITUTUP</strong>
						<br>{!! $po->reason !!}
					</div>
				@endif
				<table class="report-container table mt-5">
					<tbody class="report-content">
						<tr>
							<td class="report-content-cell">
							<div class="main">
								<table class="table table-bordered" style="width:100%">
									<thead>
										<tr>
											<th class="text-center text-uppercase">No</th>
											<th class="text-center text-uppercase">Nama Barang</th>
											<th class="text-center text-uppercase" style="width: 340px;">Catatan</th>
											<th colspan="2" class="text-center text-uppercase">Jumlah</th>
											<th class="text-center text-uppercase">Harga Satuan</th>
											<th class="text-center text-uppercase" style="width: 80px;">Disc @if($po->discount_type == 1) (%) @endif</th>
											<th class="text-center text-uppercase">Total</th>
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
											<td>
                                                {{ $item->product }} <br> <small>
                                                {!! $item->productPartNumber != NULL ? 'PN/Spec: '.$item->productPartNumber : 'PN/Spec: -' !!} <br>
												{{ $item->productBrand != NULL ? 'Brand: '.$item->productBrand : 'Brand: -' }} </small>
                                            </td>
											<td style="width: 250px;">
												{!! $item->specification !!} <br>
											</td>
											<td class="text-right" style="border-right:0 !important;">{{ $item->qty }}</td>
											<td class="text-left" style="border-left:0 !important;">{{ $item->measure }}</td>
											<td>
												<div class="currency" data-content="{{ $po->currencysymbol }}">
													<?php
														if($po->status == 2 || $po->status == 4 || $po->status == 5){
															echo format_number($item->price);
														}else if(isPoPriceAccess()){
															echo format_number($item->price);
														}
														else{
															$formatPrice = number_format($item->price, 2, ".", ",");
															$formatPriceWithX = preg_replace('/\d/', 'x', $formatPrice);
															echo $formatPriceWithX;
														}
													?>
												</div>
											</td>
											<td class="text-center">
												@if($po->discount_amount == 0 && $po->discount_type == 0)
													<div class="currency" data-content="{{ $po->currencysymbol }}"> </div>
												@endif
												<?php
													if($po->status == 2 || $po->status == 4 || $po->status == 5){
														echo $item->discount ? format_number($item->discount) : '0,00';
													}else if(isPoPriceAccess()){
														echo $item->discount ? format_number($item->discount) : '0,00';
													}
													else{
														$formatPricediscountItem = number_format(($item->discount?$item->discount:0), 2, ".", ",");
														$formatPricediscountItemWithX = preg_replace('/\d/', 'x', $formatPricediscountItem);
														echo $formatPricediscountItemWithX;
													}
												?>
											</td>
											<td class="text-right">
												<div class="currency" data-content="{{ $po->currencysymbol }}">
													<?php
														$total = $item->price * $item->qty - (($item->price * $item->qty) *  $item->discount / 100);
														if($po->status == 2 || $po->status == 4 || $po->status == 5){
															echo format_number($total);
														}else if(isPoPriceAccess()){
															echo format_number($total);
														}
														else{
															$formatPricetotal = number_format($total, 2, ".", ",");
															$formatPricetotalWithX = preg_replace('/\d/', 'x', $formatPricetotal);
															echo $formatPricetotalWithX;
														}
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
                                            <td colspan="5" rowspan="8" style="vertical-align:top !important;border-bottom:0 !important;border-top:0 !important">
                                                <table>
                                                    <tr>
														<td class="border-0 p-0" style="width: 200px;border:none !important">Metode Pembayaran</td>
														<td class="border-0 p-0" style="border:none !important">: {{ $po->payment_method }}</td>
													</tr>
                                                    <tr>
														<td class="border-0 p-0" style="width: 200px;border:none !important">Price Term</td>
														<td class="border-0 p-0" style="border:none !important">: {{ $po->price_term }} {{ $po->price_term_location }}</td>
													</tr>
                                                    <tr>
														<td class="border-0 p-0" style="width: 200px;border:none !important">Payment Term</td>
														<td class="border-0 p-0" style="border:none !important">: {{ $po->payment_term }}</td>
													</tr>
            										@if(!$po->dph_id)
                                                        <tr>
                                                            <td class="border-0 p-0" style="width: 200px;border:none !important">Tanggal Pengiriman</td>
                                                            <td class="border-0 p-0" style="border:none !important">:  {{ $po->delivery_date? date('d M Y',strtotime( $po->delivery_date)) : ' -' }}</td>
                                                        </tr>
                                                    @else
                                                        <tr>
                                                            <td class="border-0 p-0" style="width: 200px;border:none !important">Waktu Pengiriman</td>
                                                            <td class="border-0 p-0" style="border:none !important">:  {{ $po->estimated_delivery_day != 0 ? $po->estimated_delivery_day . ' Hari' : ' -' }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td class="border-0 p-0" style="width: 200px;border:none !important">Estimasi Tiba</td>
                                                            <td class="border-0 p-0" style="border:none !important">: {{ $po->estimated_receipt ? date('d M Y', strtotime($po->estimated_receipt)) : ' -' }}</td>
                                                        </tr>
                                                    @endif

                                                    <tr>
														<td class="border-0 p-0" style="width: 200px;border:none !important;vertical-align:top !important;">Kontak PIC</td>
														<td class="border-0 p-0" style="border:none !important"><div>:</div><div style="margin-top:-1.3rem !important;margin-left:0.5rem !important;" class="ms-2">{!! $po->notesDescription !!}</div></td>
													</tr>
                                                    <tr>
														<td class="border-0 p-0" style="width: 200px;border:none !important">Catatan</td>
														<td class="border-0 p-0" style="border:none !important"><div>:</div><div style="margin-top:-1.3rem !important;margin-left:0.5rem !important;width:300px !important;" class="ms-2"> {!! $po->notes?$po->notes:' -' !!}</div></td>
													</tr>
                                                </table>
                                            </td>
                                            <td colspan="2">Sub Total</td>
											<td class="text-right">
												<div class="currency" data-content="{{ $po->currencysymbol }}">
													<?php
														if($po->status == 2 || $po->status == 4 || $po->status == 5){
															echo format_number($subtotal);
														}else if(isPoPriceAccess()){
															echo format_number($subtotal);
														}
														else{
															$formatPriceSubtotal = number_format($subtotal, 2, ".", ",");
															$formatPriceSubtotalWithX = preg_replace('/\d/', 'x', $formatPriceSubtotal);
															echo $formatPriceSubtotalWithX;
														}
													?>
												</div>
											</td>
                                        </tr>
                                        <tr>
                                            <td colspan="2">Discount
												@if ($po->discount_item == false &&  $po->discount_type == 1 &&  $po->discount_amount != 0)
													<small>{{ $po->discount_amount  }}%</small>
												@endif
											</td>
											<td class="text-right " style="max-width: 300px;">
												<?php
													$total_discount = 0;
													if ($po->discount_item == false) {
														$total_discount = $po->discount_amount;
														if ($po->discount_type == 1) $total_discount = $subtotal * ($po->discount_amount / 100);
													}
												?>
												<div class="currency" data-content="{{ $po->currencysymbol }}">
													<?php
														if($po->status == 2 || $po->status == 4 || $po->status == 5){
															echo format_number($total_discount) ?? '0,00';
														}else if(isPoPriceAccess()){
															echo format_number($total_discount) ?? '0,00';
														}
														else{
															$formatPriceTotalDiscount = number_format($total_discount, 2, ".", ",");
															$formatPriceTotalDiscountWithX = preg_replace('/\d/', 'x', $formatPriceTotalDiscount);
															echo $formatPriceTotalDiscountWithX;
														}
													?>
												</div>
											</td>
                                        </tr>
                                        <tr>
                                            <td colspan="2">Netto</td>
											<td class="text-right">
												<div class="currency" data-content="{{ $po->currencysymbol }}">
													<?php
														$netto = 	$subtotal - $total_discount;

														if($po->status == 2 || $po->status == 4 || $po->status == 5){
															echo format_number($netto) ?? '0,00';
														}else if(isPoPriceAccess()){
															echo format_number($netto) ?? '0,00';
														}
														else{
															$formatPriceNetto = number_format($netto, 2, ".", ",");
															$formatPriceNettoWithX = preg_replace('/\d/', 'x', $formatPriceNetto);
															echo $formatPriceNettoWithX;
														}
													?>
												</div>
											</td>
                                        </tr>
                                        <tr>
                                            <td colspan="2">PPH <small>{{ $po->pph ? '('.$po->pph.'%)' : '' }}</small></td>
											<td class="text-right">
												<div class="currency" data-content="{{ $po->currencysymbol }}">
													(<?php
														$pph = 	($po->pph / 100) * $netto;
														if($po->status == 2 || $po->status == 4 || $po->status == 5){
															echo format_number($pph) ?? '0,00';
														}else if(isPoPriceAccess()){
															echo format_number($pph) ?? '0,00';
														}
														else{
															$formatPricePph = number_format($pph, 2, ".", ",");
															$formatPricePphWithX = preg_replace('/\d/', 'x', $formatPricePph);
															echo $formatPricePphWithX;
														}
													?>)
												</div>
											</td>
                                        </tr>
                                        <tr>
                                            <td colspan="2">PPN <small>{{ $po->ppn ? '('.$po->ppn.'%)' : '' }}</small></td>
											<td class="text-right">
												<div class="currency" data-content="{{ $po->currencysymbol }}">
													<?php
														$ppn_ = $po->ppn ?? 0;
														$ppn = 	($ppn_ / 100) * $netto;

														if($po->status == 2 || $po->status == 4 || $po->status == 5){
															echo format_number($ppn) ?? '0,00';
														}else if(isPoPriceAccess()){
															echo format_number($ppn) ?? '0,00';
														}
														else{
															$formatPricePpn = number_format($ppn, 2, ".", ",");
															$formatPricePpnWithX = preg_replace('/\d/', 'x', $formatPricePpn);
															echo $formatPricePpnWithX;
														}
													?>
												</div>
											</td>
                                        </tr>
                                        <?php
										$send_expense = $po->send_expense;
										$send_expense_ppn_caption = '';
										if ($po->send_expense_ppn == 1 || $po->send_expense_ppn == 11) {
											$send_expense_ppn_caption = "+PPN ".$po->send_expense_ppn."%";
											$send_expense_ppn = (11 / 100) * $send_expense;
											$send_expense = $send_expense_ppn + $send_expense;
										}
										?>
                                        <tr>
                                            <td colspan="2">Biaya Kirim<small> {{ $send_expense_ppn_caption }}</small></td>
											<td class="text-right">
												<div class="currency" data-content="{{ $po->currencysymbol }}">

													<?php
														if($po->status == 2 || $po->status == 4 || $po->status == 5){
															echo format_number($send_expense) ?? '0,00';
														}else if(isPoPriceAccess()){
															echo format_number($send_expense) ?? '0,00';
														}
														else{
															$formatPriceSendExpense = number_format($send_expense, 2, ".", ",");
															$formatPriceSendExpenseWithX = preg_replace('/\d/', 'x', $formatPriceSendExpense);
															echo $formatPriceSendExpenseWithX;
														}
													?>

												</div>
											</td>
                                        </tr>
                                        <tr>
                                            <td colspan="2">Total</td>
											<td class="text-right">
												<div class="currency" data-content="{{ $po->currencysymbol }}">
													<?php
														$total =  ($netto +  $ppn + $send_expense) - $pph;

														if($po->status == 2 || $po->status == 4 || $po->status == 5){
															echo format_number($total) ?? '0,00';
														}else if(isPoPriceAccess()){
															echo format_number($total) ?? '0,00';
														}
														else{
															$formatPriceTotalll = number_format($total, 2, ".", ",");
															$formatPriceTotalllWithX = preg_replace('/\d/', 'x', $formatPriceTotalll);
															echo $formatPriceTotalllWithX;
														}
													?>
												</div>
											</td>
                                        </tr>
                                        <tr>
                                            <td colspan="2">Uang Muka</td>
											<td class="text-right">
												<div class="currency" data-content="{{ $po->currencysymbol }}">
													<?php
														if($po->status == 2 || $po->status == 4 || $po->status == 5){
															echo format_number($total*$po->payment_term_dp_percentage/100) ?? '0,00';
														}else if(isPoPriceAccess()){
															echo format_number($total*$po->payment_term_dp_percentage/100) ?? '0,00';
														}
														else{
															$formatPriceDownPayment = number_format($total*$po->payment_term_dp_percentage/100, 2, ".", ",");
															$formatPriceDownPaymentWithX = preg_replace('/\d/', 'x', $formatPriceDownPayment);
															echo $formatPriceDownPaymentWithX;
														}
													?>
												</div>
											</td>
                                        </tr>
									</tbody>
								</table>
							</div>
							</td>
						</tr>
					</tbody>
				</table>
			</div>

			<div class="tab-pane" id="tab2" role="tabpanel" aria-labelledby="tab2">
				<div class="timeline">
					@if ($po->status==1)
					<div class="timeline__box">
						<div class="timeline__date" style="width:auto !important;height: auto !important;border-radius: 0;left: 30px;">
							<span class="timeline__month">Posisi Sekarang</span>
							<h5>{{ getUserByID($po->position) }} </h5>
						</div>
					</div>
					@elseif ($po->status!=1)
					<div class="timeline__box">
						<div class="timeline__date" style="width:auto !important;height: auto !important;border-radius: 0;left: 30px;">
							<span class="timeline__month">Status PO</span>
							<h5>{{ getStatusPO($po->status, 'raw') }} </h5>
						</div>
					</div>
					@endif
					<div class="timeline__group">
						<?php foreach ($po_history as $val) { ?>
							<div class="timeline__box">
								<div class="timeline__date"></div>
								<div class="timeline__post">
									<div class="timeline__content">
										<?php
										$employeeName = $val->employee;
										echo "<span>" . date('d/m/Y H:i A', strtotime($val->created_at)) . "</span><br>";
										echo "<strong>" . ucwords(strtolower($employeeName)) . "</strong> ";
										?>
										<?php
											if ($val->jenis == 'insert') {
												echo  "melakukan pembuatan PO ";
											} elseif ($val->jenis == 'draft') {
                                                if($po->dph_id){
                                                    echo  "menyetujui pembuatan PO dengan status Draft</p>";
                                                }else{
                                                    echo  "melakukan pembuatan PO dengan status Draft</p>";
                                                }
											}elseif ($val->jenis == 'approval_dph') {
												echo  "melakukan persetujuan penerbitan dokumen PO";
											} elseif ($val->jenis == 'approval') {
												echo  "melakukan Persetujuan PO";
												echo  "<p><strong>Catatan: </strong>" . $val->message . "</p>";
											} elseif ($val->jenis == 'revisi') {
												echo  "melakukan permintaan Perbaikan PO <br>";
												echo  "<p><strong>Catatan: </strong>" . $val->message . "</p>";
											}else {
												echo  "melakukan publish PO ";
											}
										?>
									</div>
								</div>
							</div>
						<?php } ?>
					</div>
				</div>
			</div>

			{{-- PO Type Histories --}}
			@if (count($po_type_histories) > 0)
				<div class="tab-pane" id="tab4" role="tabpanel" aria-labelledby="tab4">
					<div class="timeline">

						<div class="timeline__box">
							<div class="timeline__date" style="width:auto !important;height: auto !important;border-radius: 0;left: 30px;">
								<span class="timeline__month">Type PO Saat Ini</span>
								<h5>{{ strtoupper($po->type) }}</h5>
							</div>
						</div>

						<div class="timeline__group">
							@foreach ($po_type_histories as $history)
								<div class="timeline__box">
									<div class="timeline__date"></div>
									<div class="timeline__post">
										<div class="timeline__content">
											<span>{{ \Carbon\Carbon::parse($history->changed_at)->format('d/m/Y H:i') }}</span><br>
											<p>
												<strong>{{ getUserByID($history->changed_by) }}</strong>
												mengubah Type PO dari
												<strong>{{ strtoupper($history->old_type) }}</strong>
												menjadi
												<strong>{{ strtoupper($history->new_type) }}</strong>
											</p>
										</div>
									</div>
								</div>
							@endforeach
						</div>

					</div>
				</div>
			@endif

            <div class="tab-pane" id="tab-receipt" role="tabpanel">
                @if($po->type == 'lpb')
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>No. LPB</th>
                                <th>No. PO</th>
                                <th>No. PR</th>
                                <th>No. DPM</th>
                                <th>Tgl Dibuat</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- LPB --}}
                            @forelse ($receipt_data as $i => $lpb)
                                <tr class="row-receipt" style="cursor:pointer"
                                    data-id="{{ Hashids::encode($lpb->id) }}"
                                    data-type="lpb">
                                    <td>{{ $i + 1 }}</td>
                                    <td>{{ $lpb->doc_no }}</td>
                                    <td>{{ $lpb->po_no }}</td>
                                    <td>{{ $lpb->pr_no }}</td>
                                    <td>{{ $lpb->dpm_no ?? '-' }}</td>
                                    <td>{{ $lpb->created_at ? date('d/m/Y', strtotime($lpb->created_at)) : '-' }}</td>
                                    <td>{!! getStatusLPB($lpb->status, $lpb->spb_status) !!}</td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-center">Belum ada data LPB</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                @else
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>No. BPB</th>
                                <th>No. PO</th>
                                <th>Dibuat Oleh</th>
                                <th>Tgl Dibuat</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- BPB --}}
                            @forelse ($receipt_data as $i => $bpb)
                                <tr class="row-receipt" style="cursor:pointer"
                                    data-id="{{ Hashids::encode($bpb->id) }}"
                                    data-type="bpb">
                                    <td>{{ $i + 1 }}</td>
                                    <td>{{ $bpb->doc_no }}</td>
                                    <td>{{ $bpb->noPO }}</td>
                                    <td>{{ $bpb->created }}</td>
                                    <td>{{ $bpb->created_at ? date('d/m/Y', strtotime($bpb->created_at)) : '-' }}</td>
                                    <td>{!! getStatusData($bpb->status) !!}</td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center">Belum ada data BPB</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                @endif
                <div id="receipt-detail" class="mt-4" style="display:none;">
                    <div id="receipt-detail-content">
                        <div class="text-center"><i class="fa fa-spinner fa-spin"></i> Loading...</div>
                    </div>
                </div>
            </div>
		</div>
	</div>
</div>
<style>
    #tab-receipt .table thead th {
    background-color: #f8f9fa;
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    padding: 0.75rem 1rem !important;
    border-bottom: 2px solid #dee2e6;
    white-space: nowrap;
}

#tab-receipt .table tbody td {
    padding: 0.75rem 1rem !important;
    vertical-align: middle !important;
    font-size: 0.875rem;
}

#tab-receipt .row-receipt {
    transition: background-color 0.2s ease;
}

#tab-receipt .row-receipt:hover {
    background-color: #eef6ff !important;
    cursor: pointer;
}

#tab-receipt .row-receipt.table-active {
    background-color: #ddeeff !important;
    border-left: 3px solid #0088c1;
}

#receipt-detail {
    border: 1px solid #dee2e6;
    border-radius: 6px;
    padding: 20px;
    background-color: #fafafa;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
}
</style>

@stop


@section('js')

<script type='text/javascript'>
	window.addEventListener("pageshow", function (event) {
		if (event.persisted) {
		window.location.reload();
		}
	});
	function printExternal(url) {
		var printWindow = window.open(url, 'Print', 'left=200, top=200, width=950, height=800, toolbar=0, resizable=0');
		printWindow.addEventListener('load', function() {
			printWindow.print();
		}, true);
	}
    $(document).on('click', '.row-receipt', function () {
        var id      = $(this).data('id');
        var type    = $(this).data('type');
        var $detail = $('#receipt-detail');

        // Jika klik row yang sama → toggle hide
        if ($(this).hasClass('table-active')) {
            $(this).removeClass('table-active');
            $detail.slideUp(300);
            return;
        }

        // Highlight row
        $('.row-receipt').removeClass('table-active');
        $(this).addClass('table-active');

        // Jika sudah terbuka → fade out dulu, ganti konten, fade in
        if ($detail.is(':visible')) {
            $detail.fadeOut(200, function () {
                loadDetail(id, type);
                $detail.fadeIn(300);
            });
        } else {
            $('#receipt-detail-content').html(
                '<div class="text-center p-4"><i class="fa fa-spinner fa-spin fa-2x text-primary"></i><br><small class="text-muted mt-2 d-block">Memuat data...</small></div>'
            );
            $detail.slideDown(350);
            loadDetail(id, type);
        }
    });

    function loadDetail(id, type) {
        var url = type === 'lpb'
            ? '{{ route("logistic.lpb.show", ":id") }}'.replace(':id', id)
            : '{{ route("logistic.bpb_franco.show", ":id") }}'.replace(':id', id);

        var printUrl = type === 'lpb'
            ? '/logistic/lpb_print/' + id + '/print'
            : '/logistic/bpb_franco_print/' + id + '/print';

        $.ajax({
            url: url,
            method: 'GET',
            success: function (response) {
                var content = $(response).find('#tab1').html();
                var label   = type === 'lpb'
                    ? '<span class="badge badge-primary mr-2">LPB</span>'
                    : '<span class="badge badge-info mr-2">BPB</span>';

                var html = '<div class="d-flex justify-content-between align-items-center mb-3">' +
                                '<h6 class="font-weight-bold mb-0">' + label + '</h6>' +
                            '</div><hr>' + content;

                $('#receipt-detail-content')
                    .hide()
                    .html(html)
                    .fadeIn(300);
            },
            error: function () {
                $('#receipt-detail-content')
                    .hide()
                    .html('<div class="alert alert-danger"><i class="ti-close mr-2"></i>Gagal memuat detail.</div>')
                    .fadeIn(300);
            }
        });
    }
</script>
@stop
