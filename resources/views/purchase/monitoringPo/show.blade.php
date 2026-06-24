@extends('layouts.app')

@section('page-header')
    Monitoring Item PO
@endsection

@section('breadcrumbs')
<ol class="breadcrumb">
	<li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
	<li class="breadcrumb-item"><a href="{{ route('purchasing.monitoring_po') }}">Monitoring Item PO</a></li>
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
                <a href="{{ route('purchasing.monitoring_po') }}" class="nav-link"> <i class="ti-arrow-left"></i> Kembali  </a>
            </div>
			<div class="col-sm-6">
				<a class="btn btn-outline float-right" href="#" title="Print Data" onclick='printExternal("/purchasing/po_print/{{ Hashids::encode($po->id) }}/print")'><i class="ti-printer icon-lg"></i></a>
			</div>
		</div>
		<div class="d-block">
			<ul class="nav nav-tabs" role="tablist">
				<li class="nav-item">
					<a class="nav-link active" data-toggle="tab" href="#tab1" role="tab">Purchase Order (PO)</a>
				</li>
				<li class="nav-item">
					<a class="nav-link" data-toggle="tab" href="#tab2" role="tab">Histori</a>
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
							<div class="col-sm-8">: <a href="{{ route('purchasing.pr.show', Hashids::encode($po->purchase_id)) }}" target="_blank"> {{ $po->pr_no }} </a></div>
						</div>
						<div class="row">
							<label class="col-sm-4">No. DPM </label>
							<div class="col-sm-8">: <a href="{{ route('logistic.monitoring.dpm.detail', Hashids::encode($po->dpm_id)) }}" target="_blank"> {{ $po->dpm_no }} </a></div>
						</div>
						<div class="row">
							<label class="col-sm-4">Departemen/Kapal </label>
							<div class="col-sm-7">: {{ $po->department }}</div>
						</div>
						<div class="row">
							<label class="col-sm-4">Dibuat Oleh</label>
							<div class="col-sm-7">: {{ $po->created }} [ {{ idDate($po->created_at) }}]
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
									<br>&nbsp;&nbsp;&nbsp;Email : {{ $po->picEmail }} </small>
							</div>
						</div>
						@if ($po->delivery_date != NULL)
							<div class="row  mt-2">
								<label class="col-sm-3">Tgl Pengiriman</label>
								<div class="col-sm-8">: {{ date('d M Y',strtotime( $po->delivery_date)) }}</div>
							</div>
						@endif
					
						<div class="row">
							<label class="col-sm-3">Status</label>
							<div class="col-sm-8">: {!! getStatusPO($po->status) !!}</div>
						</div>

					</div>
				</div>
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
											<th class="text-center text-uppercase" style="width: 340px;">Spesifikasi</th>
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
											<td>{{ $item->product }} {!! $item->productPartNumber != NULL ? '<br> PN/Spec: '.$item->productPartNumber : '' !!} </td>
											<td style="width: 250px;">
												{!! $item->specification !!} <br>
												{{ $item->productBrand != NULL ? 'Brand: '.$item->productBrand : 'Brand: -' }}
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
														<td class="border-0 p-0" style="width: 200px;border:none !important">Metode Pembayaran</td>
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
														<td class="border-0 p-0" style="width: 200px;border:none !important">Price Term</td>
														<td class="border-0 p-0" style="border:none !important">: {{ $po->price_term }} {{ $po->price_term_location }}</td>
													</tr>
												</table>
											</td>
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
													{{ format_number($total_discount) }}
												</div>
											</td>
										</tr>
										<tr>
											<td colspan="5" style="border-bottom:0 !important;border-top:0 !important">
												<table>
													<tr>
														<td class="border-0 p-0" style="width: 200px;border:none !important">Payment Term</td>
														<td class="border-0 p-0" style="border:none !important">: {{ $po->payment_term }}</td>
													</tr>
												</table>
											</td>
											<td colspan="2">Netto</td>
											<td class="text-right">
												<div class="currency" data-content="{{ $po->currencysymbol }}">
													<?php
													$netto = 	$subtotal - $total_discount;
													echo format_number($netto);
													?>
												</div>
											</td>
										</tr>
										<tr>
											<td colspan="5" rowspan="5" style="vertical-align:top !important;border-bottom:0 !important;border-top:0 !important">
												<table>
													<tr>
														<span class="row">
															<td class="border-0 p-0" style="width: 200px;border:none !important; vertical-align:top !important;">Kontak PIC</td>
															<td class="border-0 p-0" style="border:none !important; width:350px"><div>:</div><div style="margin-top:-1.3rem !important;margin-left:0.5rem !important;" class="ms-2">{!! $po->notesDescription !!}</div></td>
														</span>
													</tr>
													<tr>
														<span class="row">
															<td class="border-0 p-0" style="width: 200px;border:none !important; vertical-align:top !important;margin-top: 3rem !important;">Catatan</td>
															<td class="border-0 p-0" style="border:none !important;"><div>:</div><div style="margin-top:-1.3rem !important;margin-left:0.5rem !important;width:300px !important;" class="ms-2"> {!! $po->notes?$po->notes:' -' !!}</div></td>
														</span>
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
											<td colspan="2">PPN <small>(11%)</small></td>
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
											<td colspan="2">Biaya Kirim<small> {{ $send_expense_ppn_caption }}</small></td>
											<td class="text-right">
												<div class="currency" data-content="{{ $po->currencysymbol }}">
													{{ ($po->currency=='IDR') ? format_number(numberPrecision($send_expense)) : format_number($send_expense) }}
												</div>
											</td>
										</tr>
										<tr>
											<td colspan="2">Total</td>
											<td class="text-right">
												<div class="currency" data-content="{{ $po->currencysymbol }}">
													<?php
													$total =  ($netto +  $ppn + $send_expense) - $pph;
													echo ($po->currency=='IDR') ? format_number(numberPrecision($total)) : format_number($total);
													?>
												</div>
											</td>
										</tr>
										<tr>
											<td colspan="2">Uang Muka</td>
											<td class="text-right">
												<div class="currency" data-content="{{ $po->currencysymbol }}"> {{ ($po->currency=='IDR') ? format_number(numberPrecision($po->down_payment)) : format_number($po->down_payment) }} </div>
											</td>
										</tr>
									</tbody>
								</table>
							</div>
							</td>
						</tr>
					</tbody>
				</table>
				@if($po->status==0)

					<hr>
					<div class="mt-4 btn-group">
						<a href="{{ route('purchasing.po.edit',Hashids::encode($po->id)) }}" class="btn btn-light mr-1 text-uppercase fsz-sm fw-600">Edit Draft</a>

						<form class='delete' action="{{ route('purchasing.po.delete', ['id' => $po->id]) }}" method='POST'>
							{{ csrf_field() }}
							<button class='btn btn-danger  mr-1  text-uppercase fsz-sm fw-600' title='Hapus'>Hapus Draft</button>
						</form>

						{!! Form::model($po, [
							'action' => ['Purchasing\PoController@publish', $po->id],
							'method' => 'POST',
						])
							!!}
							<input class="btn btn-success  text-uppercase fsz-sm fw-600" type="submit" name="publish" id="btn-submit" value="Publish">
						{!! Form::close() !!}
					</div>

				@endif
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
							<span class="timeline__month">Status</span>
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
												echo  "melakukan pembuatan PO dengan status Draft</p>";
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

		</div>
	</div>
</div>

@stop


@section('js')

<script type='text/javascript'>
	function printExternal(url) {
		var printWindow = window.open(url, 'Print', 'left=200, top=200, width=950, height=800, toolbar=0, resizable=0');
		printWindow.addEventListener('load', function() {
			printWindow.print();
		}, true);
	}
</script>
@stop