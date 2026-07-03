@extends('layouts.app')

@section('page-header')
	Monitoring DPM
@stop

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('logistic.monitoring.dpm') }}"> Monitoring DPM</a></li>
        <li class="breadcrumb-item active" aria-current="page">Detail</li>
    </ol>
@endsection

@section('content')
<style>
	.monitoring .active{
		background-color: #d4edda !important;
   		border-color: #c3e6cb !important;
		font-weight:bold;
	}
</style>
<div class="mB-40">
	<div class="bgc-white p-30 bd">
	<h6>Monitoring DPM</h6>

	<hr>
	<div class="row">
		<div class="col-sm-12">
			<h6 class="text-center font-weight-bold mB-30" style="text-decoration:underline">{{ $dpm->doc_no }}</h6>
		</div>
		<div class="col-sm-6">
			<div class="row">
				<label class="col-sm-3">Perusahaan </label>
				<div class="col-sm-8">: {{ $dpm->company }}</div>
			</div>
			<div class="row">
				<label class="col-sm-3">Lokasi/Kapal </label>
				<div class="col-sm-8">: {{ $dpm->location }}</div>
			</div>
			<div class="row">
				<label class="col-sm-3">Departemen </label>
				<div class="col-sm-8">: {{ $dpm->department }}</div>
			</div>
			<div class="row">
				<label class="col-sm-3">Project </label>
				<div class="col-sm-8">: {{ $dpm->project }}</div>
			</div>
			<?php
			if ($dpm->mr_file) { ?>
				<div class="row">
					<label class="col-sm-3">MR File </label>
					<div class="col-sm-8">:  <code> {{ $dpm->mr_file }}</code>
						<a href="#" class="icon-lg modalMR" title="Show Data" data-toggle="modal" data-target="#modalMR"><span class="ti-eye"></span></a>
					</div>
				</div>
			<?php } ?>
		</div>
		<div class="col-sm-6">
			<div class="row">
				<label class="col-sm-3">Tipe DPM</label>
				<div class="col-sm-8">: {{ strtoupper($dpm->type) }}</div>
			</div>
			<div class="row">
				<label class="col-sm-3">Dibuat Oleh</label>
				<div class="col-sm-8">: {{ $dpm->created }}</div>
			</div>
			<div class="row">
				<label class="col-sm-3">Dibuat Tanggal</label>
				<div class="col-sm-8">: {{ idDate($dpm->created_at) }}</div>
			</div>
			<div class="row">
				<label class="col-sm-3">Deskripsi/PIC</label>
				<div class="col-sm-8">: {{ $dpm->description }}</div>
			</div>

			@if ($dpm->status == '3' )
				<div class="row">
					<div class="col-sm-11"><div class="alert alert-warning p-10"> Alasan Hold: {{ $pr_history->message }} </div>
					</div>
				</div>
			@endif
		</div>
	</div>

	<ul class="nav nav-tabs mt-5 monitoring" id="myTab" role="tablist">
		<li class="nav-item">
			<a style="border: 2px solid #ddd;border-radius: 0;padding: 10px 12px; font-size:14px" class="nav-link active" id="home-tab" data-toggle="tab" href="#home" role="tab" aria-controls="home" aria-selected="true">DPM <br> <small>Daftar Permintaan Material </small></a>
		</li>
		@if(count($pr) > 0)
			<li class="arrow" style="padding: 20px 0px;font-size: 20px;"><i class="ti-arrow-right"></i></li>
			<li class="nav-item">
				<a style="border: 2px solid #ddd;border-radius: 0;padding: 10px 12px; font-size:14px" class="nav-link" id="pr-tab" data-toggle="tab" href="#pr" role="tab" aria-controls="pr" aria-selected="false">{{ count($pr) }} PR <br><small>Purchase Requisition </small></a>
			</li>
		@endif
		@if(count($po) > 0)
			<li class="arrow" style="padding: 20px 0px;font-size: 20px;"><i class="ti-arrow-right"></i></li>
			<li class="nav-item">
				<a style="border: 2px solid #ddd;border-radius: 0;padding: 10px 12px; font-size:14px" class="nav-link" id="po-tab" data-toggle="tab" href="#po" role="tab" aria-controls="po" aria-selected="false">{{ count($po) }} PO <br><small>Purchase Order </small></a>
			</li>
		@endif

		@if(count($lpb) > 0)
			<li class="arrow" style="padding: 20px 0px;font-size: 20px;"><i class="ti-arrow-right"></i></li>
			<li class="nav-item">
				<a style="border: 2px solid #ddd;border-radius: 0;padding: 10px 12px; font-size:14px" class="nav-link" id="lpb-tab" data-toggle="tab" href="#lpb" role="tab" aria-controls="lpb" aria-selected="false">{{ count($lpb) }}  LPB <br><small>Laporan Penerimaan Barang</small></a>
			</li>
		@endif

		@if(count($spb_data) > 0)
			<li class="arrow" style="padding: 20px 0px;font-size: 20px;"><i class="ti-arrow-right"></i></li>
			<li class="nav-item">
				<a style="border: 2px solid #ddd;border-radius: 0;padding: 10px 12px; font-size:14px" class="nav-link" id="spb-tab" data-toggle="tab" href="#spb" role="tab" aria-controls="spb" aria-selected="false">{{ count($spb_data) }}  SPB <br><small>Surat Pengantar Barang</small></a>
			</li>
		@endif

		@if(count($bpb_data) > 0)
			<li class="arrow" style="padding: 20px 0px;font-size: 20px;"><i class="ti-arrow-right"></i></li>
			<li class="nav-item">
				<a style="border: 2px solid #ddd;border-radius: 0;padding: 10px 12px; font-size:14px" class="nav-link" id="bpb-tab" data-toggle="tab" href="#bpb" role="tab" aria-controls="bpb" aria-selected="false">{{ count($bpb_data) }}  BPB<br><small>Berita Penerimaan Barang Jakarta</small></a>
			</li>
		@endif

		@if(count($bpb_franco) > 0)
			<li class="arrow" style="padding: 20px 0px;font-size: 20px;"><i class="ti-arrow-right"></i></li>
			<li class="nav-item">
				<a style="border: 2px solid #ddd;border-radius: 0;padding: 10px 12px; font-size:14px" class="nav-link" id="bpb-tab" data-toggle="tab" href="#bpb-franco" role="tab" aria-controls="bpb-franco" aria-selected="false">{{ count($bpb_franco) }}  BPB Lokal<br><small>Berita Penerimaan Barang Lokal</small></a>
			</li>
		@endif


	</ul>


		<div class="tab-content" id="myTabContent">

			<div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">
				<h6 class="mT-30">Daftar Barang</h6>
				<table class="table table-bordered">
					<thead>
						<th>No</th>
						<th>Nama Barang</th>
						<th>Catatan</th>
						<th>QTY</th>
						<th>Flag/<br>Tujuan Penggunaan</th>
						<th>Tgl Dibutuhkan</th>
						<th>Last Approved</th>
						<th>Next Approval</th>
						<th>Status</th>
						<th></th>
					</thead>
					<tbody>
						@if (count($dpm_items) > 0)
							@php
								$no = 1;
							@endphp
							@foreach ($dpm_items as $item)
								<tr data-entry-id="{{ $item->id }}">
									<td>{{ $no }}</td>
									<td>
                                        [{{ $item->productCode }}] - {{ $item->product }} <br>
                                        <small>
                                            {{ $item->productPartNumber ? 'PN/SPEC :'.$item->productPartNumber : 'PN/SPEC : -' }} <br>
                                            {{ $item->productBrand ? 'Brand : '. $item->productBrand : 'Brand : -'}} <br>
                                        </small>
										@php
                                            $dataHistoryRequest = getHistoryRequest($item->productId,$item->departmentId);
                                        @endphp
                                        @if(!empty($dataHistoryRequest))
                                        <small>
                                            <strong class="text-danger">
                                                Request Terakhir : {{$dataHistoryRequest->nopo}} <br>
                                                Diterima : {{ \Carbon\Carbon::parse($dataHistoryRequest->tglpenerimaan)->translatedFormat('F Y') }}
                                            </strong> <br>
                                        </small>
                                        @endif
										@if ($item->request_type_item == 1)
                                            <br><span class="text-danger">PENGGANTIAN ITEM MILIK {{getCompanyByLocationId($item->return_location)->alias.' - '.getLocationByID($item->return_location)->name}}</span>
                                        @endif
                                    </td>
									<td>{!! $item->notes !!}</td>
									<td>{{ $item->qty }} {{ $item->measure }}
										{{-- <br> @if(Auth::user()->id == 1) {{getQtyAllBpbItemByPurchaseItem($item->id)}} @endif --}}
									</td>
									<td>{{ $item->flag == "normal" ? "Normal" : "Urgent" }} </td>
									<td>{{ date('d/m/Y',strtotime( $item->needed_on_date)) }}</td>
									<td>{{ $item->approved }} <br><small>
											@if ($item->approved != NULL)
											{{ date('d/m/Y',strtotime( $item->last_approved_at)) }}
											@endif
									</small>
									</td>
									<td>
										@if ($dpm->status != '0')
											@if ($item->status != 4 && $item->status != 2)
												{{ getNextApprovalDPM($dpm->location_id,$item->step) }}
											@endif
										@endif
									</td>

									<td>
										{!! getStatusItemByQty($item->typeDpm, $item->status, $item->statusDpm, $item->pr_status, $item->po_status, $item->statusPr, getTypePoByPurchaseItem($item->id)->type ?? null , getQtyAllPoItemByPurchaseItem($item->id), getQtyAllLpbItemByPurchaseItem($item->id), getQtyAllSpbItemByPurchaseItem($item->id), getQtyAllBpbItemByPurchaseItem($item->id), $item->qty, (($item->qty - getQtyItemPoByPrItemId($item->id) == $item->qty ? 0 : ($item->qty - getQtyItemPoByPrItemId($item->id)))) ?? 0 )!!}
									</td>

									<td>
										@if(getDPMLog($item->id) > 0)
											<a href="#" value="{{ action('Logistic\PurchaseRequestController@getPrItemNotes',['id'=>$item->id]) }}" class="icon-lg modalMd" title="Show Data" data-toggle="modal" data-target="#modalMd"><span class="ti-eye text-danger"></span></a>
										@else
											<a href="#" value="{{ action('Logistic\PurchaseRequestController@getPrItemNotes',['id'=>$item->id]) }}" class="icon-lg modalMd" title="Show Data" data-toggle="modal" data-target="#modalMd"><span class="ti-eye text-muted"></span></a>
										@endif
										<a value="{{route('logistic.monitoring.item.log',$item->id)}}" class="icon-lg modalMdTimeLine" style="padding-top: 5px; padding-left: 5px;" title="Show Timeline {{$item->product}}" data-toggle="modal" data-target="#modalMdTimeLine">
                                            <span class="ti-signal text-primary"></span>
                                        </a>
									</td>
								</tr>
							@php
								$no++
							@endphp
							@endforeach
						@else
							<tr>
								<td colspan="8">@lang('global.app_no_entries_in_table')</td>
							</tr>
						@endif
					</tbody>
				</table>
			</div>

			@if(count($pr) > 0)
				<div class="tab-pane fade" id="pr" role="tabpanel" aria-labelledby="pr-tab">

					@foreach ($pr as $pr_item)
					<div class="bd p-20 mt-5" style="background: #f8f9fa;">

						<div class="row">
							<div class="col-sm-12">
								<a class="btn btn-outline float-right" href="#" title="Print Data" onclick='printExternal("/purchasing/pr_print/{{ Hashids::encode($pr_item->id) }}/print")'><i class="ti-printer icon-lg"></i></a>
								<h6 class="font-weight-bold mB-10">{{ $pr_item->doc_no }}</h6>
								<hr>
							</div>
						</div>

						<div class="row">
							<div class="col-sm-6">
								<div class="row">
									<label class="col-sm-4">Tanggal Terbit PR </label>
									<div class="col-sm-7">: {{ date('d/m/Y',strtotime( $pr_item->created_at)) }}</div>
								</div>
							</div>
						</div>

						<table class="table table-bordered mt-2">
								<thead>
									<th>No</th>
									<th style="width:300px">Nama Barang</th>
									<th>Catatan</th>
									<th style="min-width:100px">QTY</th>
									<th>Flag / Tujuan<br>Penggunaan</th>
									<th>Tgl Dibutuhkan</th>
									<th>Keterangan</th>
									<th>Purchaser</th>
									<th></th>
								</thead>
							<tbody>
							@php
								$no = 1;
								$pritem = getPRItem($pr_item->id);
							@endphp
								@foreach ($pritem as $item)
									<?php
										$background = "";
										if($item->po_status == 3 && $item->qty_parsial == 0){
											$background = "#f8d7da";
										}
										if($item->po_status == 3 && $item->qty_parsial != 0){
											$background = "#d1ecf1";
										}
									?>
									<tr data-entry-id="{{ $item->id }}" style="background-color:{{ $background }}">
										<td>{{ $no }}</td>
										<td>
											[{{ $item->productCode }}] {{ $item->product }} <br>
											<small>
												{!! $item->productPartNumber ? 'PN/Spec: '.$item->productPartNumber : 'PN/Spec: -' !!} <br>
												{{ $item->productBrand ? 'Brand: '.$item->productBrand : 'Brand: -' }}
											</small>
										</td>
										<td>
											{!! $item->notes !!}
										</td>
										<td>{{ $item->qty }} {{ $item->measure }}</td>
										<td>{{ $item->flag == "normal" ? "Normal" : "Urgent" }} </td>
										<td>{{ date('d/m/Y',strtotime( $item->needed_on_date)) }}</td>
										<td>
											@if($item->po_status == 3 && ((($item->qty - getQtyItemPoByPrItemId($item->id) == $item->qty ? 0 : ($item->qty - getQtyItemPoByPrItemId($item->id)))) ?? 0) == 0)
												<span class="badge badge-danger">PR Closed</span><br>
												{{ $pr_item->notes }}
											@endif
											@if($item->po_status == 3 && ((($item->qty - getQtyItemPoByPrItemId($item->id) == $item->qty ? 0 : ($item->qty - getQtyItemPoByPrItemId($item->id)))) ?? 0) != 0)
												<span class="badge badge-danger">PR Parsial Closed</span> {{ $item->qty - getQtyItemPoByPrItemId($item->id) }} <br>
												{{ $pr_item->notes }}
											@endif
										</td>
										<td>{{ ($item->purchaser) ? $item->purchaser : '-'}}</td>
										<td>
											<a href="#" value="{{ action('Logistic\PurchaseRequestController@getPrItemNotes',['id'=>$item->id]) }}" class="icon-lg modalMd" title="Show Data" data-toggle="modal" data-target="#modalMd"><span class="ti-eye"></span></a>
										</td>
									</tr>
								@php
									$no++
								@endphp
								@endforeach

						</tbody>
					</table>

					</div>
					@endforeach
				</div>
			@endif

			@if(count($po) > 0)
				<div class="tab-pane fade" id="po" role="tabpanel" aria-labelledby="po-tab">

					@foreach ($po as $item)
						<div class="bd p-20 mt-5" style="background: #f8f9fa;">
							<div class="row">
								<div class="col-sm-12">
									@if($item->status == 2 || $item->status == 4 || $item->status == 5)
										<a class="btn btn-outline float-right" href="#" title="Print Data" onclick='printExternal("/purchasing/po_print/{{ Hashids::encode($item->id) }}/print")'><i class="ti-printer icon-lg"></i></a>
									@elseif(isPoPriceAccess())
										<a class="btn btn-outline float-right" href="#" title="Print Data" onclick='printExternal("/purchasing/po_print/{{ Hashids::encode($item->id) }}/print")'><i class="ti-printer icon-lg"></i></a>
									@endif
									<h6 class="font-weight-bold mB-10">{{ $item->doc_no }}</h6>
									<hr>
								</div>
							</div>

							<div class="row">
								<div class="col-sm-6">
									<div class="row">
										<label class="col-sm-4">No. PR </label>
										<div class="col-sm-7">: {{ $item->pr_no }}</div>
									</div>
									<div class="row">
										<label class="col-sm-4">Dibuat Oleh</label>
										<div class="col-sm-7">: {{ $item->created }} [ {{ idDate($item->created_at) }}]
										</div>
									</div>
								</div>
								<div class="col-sm-6">

									<div class="row">
										<label class="col-sm-3">Supplier</label>
										<div class="col-sm-8">:
											{{ $item->supplier }} [ Nama: {{ $item->picName }} / Telp. {{ $item->picTelp }} ]
										</div>
									</div>
									<div class="row">
										<label class="col-sm-3">Status</label>
										<div class="col-sm-8">: {!! getStatusPO($item->status) !!}</div>
									</div>
								</div>
							</div>

							<table class="table table-bordered mT-10">
								<thead>
									<th style="width:30px">No</th>
									<th>Nama Barang</th>
									<th>Catatan</th>
									<th style="width:100px">Jumlah</th>
									<th>Harga Satuan</th>
									<th>Total</th>
								</thead>
								<tbody>
										@php
											$no = 1;
											$subtotal = 0;
											$po_items = getPOItem($item->id)
										@endphp
										@foreach ($po_items as $poitem)
											<tr>
												<td>{{ $no }}</td>
												<td style="width:400px">[{{ $poitem->productCode }}] {{ $poitem->product }} <br>
													<small>
														{!! $poitem->productPartNumber ? 'PN/Spec: '.$poitem->productPartNumber : 'PN/Spec: -' !!} <br>
														{{ $poitem->productBrand ? 'Brand: '.$poitem->productBrand : 'Brand: -' }}
													</small>
												</td>
												<td style="width:350px">{!! $poitem->specification !!}</td>
												<td>{{ $poitem->qty }} {{ $poitem->measure }}</td>
												<td>
													<div class="currency" data-content="{{ getCurrencySymbol($item->currency) }}">
														<?php
															if($item->status == 2 || $item->status == 4 || $item->status == 5){
																echo number_format($poitem->price,2,".",',');
															}
															else if (isPoPriceAccess()) {
																echo number_format($poitem->price,2,".",',');
															}
															 else {
																$price_ = number_format($poitem->price, 2, ".", ",");
																$price_x = preg_replace('/\d/', 'x', $price_);
																echo $price_x;
															}
														?>
													</div>
												</td>
												<td class="text-right">
													<div class="currency" data-content="{{ getCurrencySymbol($item->currency) }}">
														<?php

															$total= $poitem->price * $poitem->qty;
															if($item->status == 2 || $item->status == 4 || $item->status == 5){
																echo number_format($total, 2, ".", ",");
															}
															else if (isPoPriceAccess()) {
																echo number_format($total, 2, ".", ",");
															} else {
																$formattedTotal = number_format($total, 2, ".", ",");
																$formattedTotalWithX = preg_replace('/\d/', 'x', $formattedTotal);
																echo $formattedTotalWithX;
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
								</tbody>
							</table>
						</div>
					@endforeach
				</div>
			@endif

			@if(count($lpb) > 0)
				<div class="tab-pane fade" id="lpb" role="tabpanel" aria-labelledby="lpb-tab">

					@foreach ($lpb as $item)
						<div class="bd p-20 mt-5" style="background: #f8f9fa;">
							<div class="row">
								<div class="col-sm-12">
									<h6 class="font-weight-bold mB-10">{{ $item->doc_no }}</h6>
									<hr>
								</div>
							</div>

							<div class="row">
								<div class="col-sm-6">
									<div class="row">
										<label class="col-sm-4">No. PO </label>
										<div class="col-sm-7">: {{ $item->po_no }}</div>
									</div>
									<div class="row">
										<label class="col-sm-4">Dibuat Oleh</label>
										<div class="col-sm-7">: {{ $item->created }} [ {{ idDate($item->created_at) }}]
										</div>
									</div>
									<div class="row">
										<label class="col-sm-4">Status</label>
										<div class="col-sm-7">: {!! getStatusLPB($item->status,$item->spb_status) !!}
										</div>
									</div>
								</div>
								<div class="col-sm-6">
									<div class="row">
										<label class="col-sm-3">Penerima</label>
										<div class="col-sm-8">:
											{{ $item->received_by }}
										</div>
									</div>
								</div>
							</div>

							<table class="table table-bordered mT-10">
								<thead>
									<tr>
										<th rowspan="2" style="width:50px">No</th>
										<th rowspan="2" style="width:300px">Nama Barang</th>
										<th rowspan="2" style="width:300px">Catatan</th>
										<th colspan="2" class="text-center">Jumlah </th>
										<th rowspan="2" class="text-center">Satuan</th>
										<th rowspan="2" class="text-center">Harga</th>
										<th rowspan="2" class="text-center">Catatan </th>
									</tr>
									<tr>
										<th style="width:100px" class="text-center">Dipesan</th>
										<th style="width:100px" class="text-center">Diterima</th>
									</tr>
								</thead>
								<tbody>
										@php
											$no = 1;
											$subtotal = 0;
											$lpb_items = getLPBItemMonitor($item->id);

										@endphp
										@foreach ($lpb_items as $item)
											<tr>
												<td>{{ $no }}</td>
												<td style="width:300px">
													[{{ $item->productCode }}] {{ $item->product }} <br>
													<small>
														PN/SPEC: {{ $item->productPartNumber ?? '-' }} <br>Brand: {{ $item->productBrand ?? '-' }}
													</small>
												</td>
												<td>{!! $item->specification !!}</td>
												<td>{{ $item->qtyPO }}</td>
												<td>{{ $item->qty }}</td>
												<td>{{ $item->measure }}</td>
												<td>{{ number_format($item->price ,2,".",',') }}</td>
												<td>{!! $item->notes !!}</td>
											</tr>
										@php
											$subtotal += $total;
											$no++;
										@endphp
										@endforeach
								</tbody>
							</table>
						</div>
					@endforeach
				</div>
			@endif

			@if(count($spb_data) > 0)

				<div class="tab-pane fade" id="spb" role="tabpanel" aria-labelledby="spb-tab">

					@foreach ($spb_data as $item)

						<div class="bd p-20 mt-5" style="background: #f8f9fa;">
							<div class="row">
								<div class="col-sm-12">
									<a class="btn btn-outline float-right" target="_blank" href="{{ route('logistic.spb.show',Hashids::encode($item['spb_id'])) }}" title="Detail SPB"><i class="ti ti-eye text-success icon-lg"></i></a>
									<h6 class="font-weight-bold mB-10">{{ $item['doc_no'] }}</h6>
									<hr>
								</div>
							</div>

							<div class="row">
								<div class="col-sm-6">
									<div class="row">
										<label class="col-sm-4">Dibuat Oleh</label>
										<div class="col-sm-7">: {{ $item['created']}} [ {{ idDate($item['created_at']) }}]
										</div>
									</div>
								</div>
								<div class="col-sm-6">
									<div class="row">
										<label class="col-sm-3">Tipe</label>
										<div class="col-sm-8">:
											{{ $item['type'] }}
											@if($item['is_pickup'] == true)
												[Pick Up]
											@endif
										</div>
									</div>
								</div>
								<div class="col-sm-6">
									<div class="row">
										<label class="col-sm-4">Status</label>
										<div class="col-sm-7">: {!! getStatusSPB($item['status_spb'])!!}
										</div>
									</div>
								</div>
							</div>

							<table class="table table-bordered">
					<thead>
						<tr>
							<th style="width:50px">No</th>
							<th>Nama Barang</th>
							<th>Catatan</th>
							<th class="text-center">QTY</th>
							<th class="text-center">PR</th>
							<th class="text-center">PO</th>
							<th class="text-center">LPB </th>
							<th class="text-center">Kapal/Departemen</th>
							<th class="text-center">Supplier</th>
							<th class="text-center">Annotation</th>
						</tr>
					</thead>
					<tbody>
							<?php
								// $spb_items=getSPBKoliByLPB($item['spb_id'],$item['lpb_id']);
								$spb_items=getSPBKoliByDPM($item['spb_id'],$item['dpm_id']);
							?>

							@php
								$no = 1;
							@endphp
							@foreach ($spb_items as $data)
								<tr>
									<td>{{ $no }}</td>
									<td>[{{ $data->productCode }}] {{ $data->product }} <br>
										<small>
											PN/SPEC: {{ $data->productPartNumber ?? '-' }}  <br> Brand: {{ $data->productBrand ?? '-' }}
										</small>
									</td>
									<td>{!! $data->specification !!}</td>
									<td class="text-center">{{ $data->qtyKoli }} {{ $data->measure }}</td>
									<td class="text-center">{{ $data->noPR }}</td>
									<td>{{ $data->noPO }}</td>
									<td>{{ $data->noLPB }}</td>
									<td>{{ $data->department }}</td>
									<td>{{ $data->supplier }}</td>
									<td>{!! $data->annotation !!}</td>
								</tr>
								@php
									$no++;
								@endphp

							@endforeach

					</tbody>

				</table>
						</div>
					@endforeach
				</div>
			@endif

			@if(count($bpb_data) > 0)

				<div class="tab-pane fade" id="bpb" role="tabpanel" aria-labelledby="bpb-tab">
					@foreach ($bpb_data as $item)
						<div class="bd p-20 mt-5" style="background: #f8f9fa;">
							<div class="row">
								<div class="col-sm-12">
									<a class="btn btn-outline float-right" href="{{ route('logistic.bpb.show',Hashids::encode($item['bpb_id'])) }}" title="Detail BPB"><i class="ti ti-eye text-success icon-lg"></i></a>
									<h6 class="font-weight-bold mB-10">{{ $item['doc_no'] }}</h6>
									<hr>
								</div>
							</div>

							<div class="row">
								<div class="col-sm-6">
									<div class="row">
										<label class="col-sm-4">No. SPB </label>
										<div class="col-sm-7">: {{ $item['spb_no'] }}</div>
									</div>
									<div class="row">
										<label class="col-sm-4">Dibuat Oleh</label>
										<div class="col-sm-7">: {{ $item['created'] }} [ {{ idDate($item['created_at']) }}]
										</div>
									</div>
								</div>
								<div class="col-sm-6">
									<div class="row">
										<label class="col-sm-3">Penerima</label>
										<div class="col-sm-8">:
											{{ $item['received_by'] }}
										</div>
									</div>
								</div>
							</div>

							<?php
								$bpbNo =  explode('-',$item['doc_no']);
								//$bpb_items=getBPBItemByID($item['spb_item_id'], $item['bpb_id']);
							?>

							<h6 class="mT-30">Daftar Barang</h6>
							<table class="table table-bordered">
								<thead>
									<tr>
										<th style="width:50px">No</th>
										<th style="width:300px">Nama Barang</th>
										<th >QTY</th>
										<th style="width:150px">Nomor PR</th>
										<th style="width:150px">Nomor PO</th>
										<th style="width:150px">Nomor LPB</th>
										<th class="text-center">Catatan </th>
									</tr>

								</thead>
								<tbody>
								    <?php
								        $no = 1;
								        foreach($item['spb_item_id'] as $itemBpb) {
                                        $bpb_items_list=getBPBItemByID($itemBpb, $item['bpb_id']);
								    ?>
								    @foreach ($bpb_items_list as $itemlist)
											<tr>
												<td>{{ $no }}</td>
												<td>[{{ $itemlist->productCode }}] - {{ $itemlist->product }} <br><small>PN/SPEC: {{ $itemlist->productPartNumber ?? '-' }} <br> Brand: {{ $itemlist->productBrand ?? '-' }}</small> <br> <small>Spesifikasi: {!! $itemlist->specification ?? '-' !!}</small></td>
												<td>{{ $itemlist->qty }} {{ $itemlist->measure }}</td>
												<td>{{ $itemlist->noPR }}</td>
												<td>{{ $itemlist->noPO }}</td>
												<td>{{ $itemlist->noLPB }}</td>
												<td>{{ $itemlist->description }}</td>
											</tr>
											<?php $no++; ?>
								    @endforeach
                                    <?php } ?>
								</tbody>
							</table>

						</div>
					@endforeach
				</div>
			@endif


			@if(count($bpb_franco) > 0)

			<div class="tab-pane fade" id="bpb-franco" role="tabpanel" aria-labelledby="bpb-franco-tab">
				@foreach ($bpb_franco as $item)
					<div class="bd p-20 mt-5" style="background: #f8f9fa;">
						<div class="row">
							<div class="col-sm-12">
								<a class="btn btn-outline float-right" href="{{ route('logistic.bpb_franco.show',Hashids::encode($item->id)) }}" title="Detail BPB"><i class="ti ti-eye text-success icon-lg"></i></a>
								<h6 class="font-weight-bold mB-10">{{ $item->doc_no }}</h6>
								<hr>
							</div>
						</div>

						<div class="row">
							<div class="col-sm-6">
								<div class="row">
									<label class="col-sm-4">No. PB </label>
									<div class="col-sm-7">: {{ $item->noPO }}</div>
								</div>
								<div class="row">
									<label class="col-sm-4">Dibuat Oleh</label>
									<div class="col-sm-7">: {{ $item->created }} [ {{ idDate($item->created_at) }}]
									</div>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="row">
									<label class="col-sm-3">Penerima</label>
									<div class="col-sm-8">:
										{{ $item->received_by }}
									</div>
								</div>
							</div>
						</div>


						<h6 class="mT-30">Daftar Barang</h6>
						<table class="table table-bordered">
							<thead>
								<tr>
									<th style="width:50px">No</th>
									<th style="width:400px">Nama Barang</th>
									<th class="text-center">QTY</th>
									<th class="text-center">STN</th>
									<th>Nomor PR</th>
									<th>Nomor DPM</th>
									<th class="text-center">Catatan </th>
								</tr>

							</thead>
							<tbody>
								<?php
									$no = 1;
									$bpb_items_list = getProductFrancoItem($item->id);
								?>
								@foreach ($bpb_items_list as $itemlist)
										<tr>
											<td>{{ $no }}</td>
											<td>[{{ $itemlist->productCode }}] - {{ $itemlist->product }} <br><small>PN/SPEC: {{ $itemlist->productPartNumber }} | Brand: {{ $itemlist->productBrand }}</small></td>
											<td>{{ $itemlist->qty }}</td>
											<td>{{ $itemlist->measure }}</td>
											<td>{{ $itemlist->noPR }}</td>
											<td>{{ $itemlist->noDPM }}</td>
											<td>{{ $itemlist->description }}</td>
										</tr>
										<?php $no++; ?>
								@endforeach
							</tbody>
						</table>

					</div>
				@endforeach
			</div>

			@endif



		</div>

	</div>
</div>

<div class="modal fade" id="modalMR" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	<div class="modal-dialog modal-xl" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="modalMdTitle">MR File</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			</div>
			<div class="modal-body">
				<embed class="col align-self-center" src="{{ asset('storage'.$dpm->mr_file) }}" width="600" height="500" alt="pdf" />
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="modalMd" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="modalMdTitle">History Data</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			</div>
			<div class="modal-body">
				<div class="modalError"></div>
				<div id="modalMdContent"></div>
			</div>
		</div>
	</div>
</div>


<div class="modal fade" id="modalHistoryPO" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="modalMdTitle">History Purchase Order</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			</div>
			<div class="modal-body">
				<div class="modalError"></div>
				<div id="modalMdContentPO"></div>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="modalMdTimeLine" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document" style="max-width: 80%;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalMdTimeLineTitle">TIMELINE ITEM DPM</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="modalError"></div>
                <div id="modalMdTimeLineContent"></div>
            </div>
        </div>
    </div>
</div>


@stop


@section('js')

<script  type='text/javascript'>
    $(document).ready(function() {
		$('.modalMd').off('click').on('click', function () {
			$('#modalMdContent').load($(this).attr('value'));
		});

		$('.modalMdPO').off('click').on('click', function () {
			$('#modalMdContentPO').load($(this).attr('value'));
		});

		$(document).on('click', '.modalMdTimeLine', function (e) {
            e.preventDefault();

            var url = $(this).attr('value');

            $('#modalMdTimeLineContent').html('');
            $('.modalError').html('');

            $.ajax({
                url: url,
                type: 'GET',
                dataType: 'html',
                success: function (response) {
                    $('#modalMdTimeLineContent').html(response);
                    $('#modalMdTimeLine').modal('show');
                },
                error: function (xhr, status, error) {
                    $('.modalError').html('<div class="alert alert-danger">Failed to load history item. Please try again later.</div>');
                }
            });
        });
	});



    function printExternal(url) {
        var printWindow = window.open(url, 'Print', 'left=200, top=200, width=950, height=800, toolbar=0, resizable=0');
        printWindow.addEventListener('load', function() {
            printWindow.print();
        }, true);
    }
</script>
@stop
