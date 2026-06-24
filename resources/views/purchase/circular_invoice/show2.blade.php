@extends('layouts.app')

@section('page-header')
    Circular Invoice
@endsection

@section('breadcrumbs')
<ol class="breadcrumb">
	<li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
	<li class="breadcrumb-item"><a href="{{ route('purchasing.circular_invoice') }}">Circular Invoice</a></li>
	<li class="breadcrumb-item active" aria-current="page">Detail Circular Invoice</li>
</ol>
@endsection

@section('content')

<div class="mB-40">
	<div class="bgc-white p-30 bd">
        
		@php
            use Illuminate\Support\Facades\Gate;
        @endphp

            {{-- Button Print --}}
		<div class="row mb-1 justify-content-end">
			<div class="col-sm-12">
                <a class="btn btn-outline float-right" href="{{ route('purchasing.circular_invoice.print', Hashids::encode($invoice->id)) }}" target="_blank" title="Print Data"> <i class="ti-printer icon-lg"></i></a>
            </div>
		</div>

		<div class="d-block">
			<ul class="nav nav-tabs" role="tablist">
				<li class="nav-item">
					<a class="nav-link active" data-toggle="tab" href="#tab1" role="tab">Circular Invoice</a>
				</li>
				<li class="nav-item">
	
				</li>
			</ul>
		</div>

		<div class="tab-content mT-30" >

  			<div class="tab-pane active" id="tab1" role="tabpanel" aria-labelledby="tab1">
			  	<h6 class="text-center font-weight-bold mB-30" style="text-decoration:underline">{{ $invoice->doc_no }}</h6>
				<div class="row mt-5">
					<div class="col-sm-6">
						<div class="row">
							<label class="col-sm-3">No Circular Invoice </label>
							<div class="col-sm-8">: {{ ($invoice->doc_no) }}</div>
						</div>
						<div class="row">
                            <label class="col-sm-3">No PO </label>
							<div class="col-sm-8">: {{ ($invoice->po_number) }}</div>
						</div>
						<div class="row">
							<label class="col-sm-3">No Invoice Ext </label>
							<div class="col-sm-8">: {{ ($invoice->invoice_number_ext) }}</div>
						</div>
                        <div class="row">
							<label class="col-sm-3">Tgl Invoice Ext </label>
                            <div class="col-sm-8">: {{ $invoice->date_invoice_ext ? \Carbon\Carbon::parse($invoice->date_invoice_ext)->format('d/m/Y') : '-' }}</div>
						</div>
						<div class="row">
							<label class="col-sm-3">Tgl Jatuh Tempo Pembayaran </label>
							<div class="col-sm-8">: {{ $invoice->due_date_payment ? \Carbon\Carbon::parse($invoice->date_invoice_ext)->format('d/m/Y') : '-' }}</div>
						</div>
					</div>
					<div class="col-sm-6">
                        <div class="row">
							<label class="col-sm-3">Jumlah Pembayaran</label>
							<div class="col-sm-8">: Rp. {{ number_format($invoice->payment_amount, 0, ',', '.') }}</div>
						</div>
						<div class="row">
							<label class="col-sm-3">Dibuat Oleh</label>
							<div class="col-sm-8">: {{ ($invoice->nama_pembuat) }}</div>
						</div>
						<div class="row">
							<label class="col-sm-3">Dibuat Tanggal</label>
							<div class="col-sm-8">: {{ idDate($invoice->created_at) }}</div>
						</div>
						<div class="row">
							<label class="col-sm-3">Status</label>
							<div class="col-sm-8">:
                                @php
                                    $statusBadge = [
                                        0 => '<span class="badge badge-secondary">Draft</span>',
                                        1 => '<span class="badge badge-success">Publish</span>',
                                        2 => '<span class="badge badge-danger">Cancel</span>',
                                    ];
                                @endphp

                                {!! $statusBadge[$invoice->status] ?? '<span class="badge badge-dark">Unknown</span>' !!}
                            </div>
						</div>
                        <div class="row">
							<label class="col-sm-3">Note</label>
							<div class="col-sm-8">: {{ ($invoice->note) }}</div>
						</div>
					</div>
				</div>

				{{-- @if ($invoice->status == 5)
					<hr>
					<div class="alert alert-danger"><strong> DITUTUP</strong>
						<br>{{ $invoice->note }}
					</div>
				@endif --}}

				{{-- <h6 class="mT-30">Daftar Item</h6> --}}
				<table class="table table-bordered">
					{{-- <thead>
						<th>No</th>
						<th style="width:300px">Nama Barang</th>
						<th>Catatan</th>
						<th style="min-width:100px">QTY</th>
						<th>Flag </th>
						<th>Status</th>
						<th>Tgl Dibutuhkan</th>
						<th>Purchaser</th>
						<th>Aksi</th>
					</thead> --}}
					{{-- <tbody>
						@if (count($pr_items) > 0)
							@php
								$no = 1
							@endphp
							@foreach ($pr_items as $item)
									<tr data-entry-id="{{ $item->id }}">
										<td>{{ $no }}</td>
										<td>
											[{{ $item->productCode }}]
											{{ $item->product }} <br>
											<small>
												{!! $item->productPartNumber != NULL ? 'PN/Spec: '.$item->productPartNumber : 'PN/Spec: -' !!} <br>
												{{ $item->productBrand != NULL ? 'Brand: '.$item->productBrand : 'Brand: -' }}
											</small>
										</td>
										<td>
											{!! $item->notes !!}
										</td>
										<!-- @if ($item->po_status == 2)
											<td>{{ $item->qty_parsial }} {{ $item->measure }}</td>
										@else
											<td>{{ $item->qty}} {{ $item->measure }}</td>
										@endif -->
										<td>
											{{ $item->qty }} {{ $item->measure }}
											<small>
												<table style="height:auto; line-height: 1.2; padding: 0; border-collapse: collapse;">
													<tr>
														<td style="border:none; padding: 0;"><small><strong>Qty DPH<br>Qty PO</strong></small></td>
														<td style="border:none; padding: 0;"><small>: {{ getQtyItemDphByPrItemId($item->id) }} <br> : {{ getQtyItemPoByPrItemId($item->id) }}</small></td>
													</tr>
												</table>
											</small>
										</td>

										<td>{{ $item->flag == "normal" ? "Normal" : "Urgent" }} </td>
										<td>
                                            {!! getStatusItemPR($item->pr_status, $item->po_status, $item->qty_parsial,$pr->type) !!} <br>
                                            <small class="text-danger">
                                                @if($item->po_status === 3 || $item->po_status === 4)Alasan Close : {!!$item->reason ?? ($pr->status == 5 || $pr->status == 6 ? $pr->notes : '')!!} @endif
                                            </small>
                                        </td>
										<td>{{ date('d/m/Y',strtotime( $item->needed_on_date)) }}</td>
										<td>{{ ($item->purchaser) ? $item->purchaser : '-'}}</td>
										<td>
											@if(getDPMLog($item->id) > 0)
												<a href="#" value="{{ action('Logistic\PurchaseRequestController@getPrItemNotes',['id'=>$item->id]) }}" class="icon-lg modalMd" title="Show Data" data-toggle="modal" data-target="#modalMd"><span class="ti-eye text-danger"></span></a>
											@else
												<a href="#" value="{{ action('Logistic\PurchaseRequestController@getPrItemNotes',['id'=>$item->id]) }}" class="icon-lg modalMd" title="Show Data" data-toggle="modal" data-target="#modalMd"><span class="ti-eye text-muted"></span></a>
											@endif
											<a href="#" value="{{ action('Purchasing\PoController@getItems',['id'=>$item->product_id]) }}" class="icon-lg modalMdPO ml-1" data-toggle="modal" data-target="#modalHistoryPO"><span class="ti-shopping-cart text-muted"></span></a>
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
					</tbody> --}}
				</table>
				<hr>

					{{-- <div class="btn-group">
						
					</div> --}}
			</div>

		</div>

	</div>
</div>

@stop

