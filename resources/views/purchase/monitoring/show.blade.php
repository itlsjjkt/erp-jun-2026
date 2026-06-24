@extends('layouts.app')

@section('page-header')
	Monitoring Item PR
@stop

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('logistic.monitoring.item') }}"> Monitoring Item PR</a></li>
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
		<a href="{{ route('purchasing.monitoring_pr') }}" class="" >
			<i class="ti-arrow-left"></i> Kembali
		</a>
		<hr>
		<h6 class=" font-weight-bold mB-30" style="text-decoration:underline">{{ $pr->doc_no }}</h6>
		<div class="row mt-3">
			<div class="col-sm-6">
				<div class="row">
					<label class="col-sm-3">Tipe DPM </label>
					<div class="col-sm-8">: {{ strtoupper($pr->type) }}</div>
				</div>
				<div class="row">
					<label class="col-sm-3">No. DPM </label>
					<div class="col-sm-8">: <a href="{{ route('logistic.monitoring.dpm.detail', ['id' => Hashids::encode($pr->purchase_id)]) }}" target="_blank"> {{ $pr->dpm_no }} </a></div>
				</div>
				<div class="row">
					<label class="col-sm-3">Kapal/Departemen </label>
					<div class="col-sm-8">: {{ $pr->department->name }}</div>
				</div>
				<div class="row">
					<label class="col-sm-3">Project </label>
					<div class="col-sm-8">: {{ $pr->project->name  }}</div>
				</div>
			</div>
			<div class="col-sm-6">
				<div class="row">
					<label class="col-sm-3">Deskripsi DPM</label>
					<div class="col-sm-8">: {{ ($pr->PurchaseRequest) ? $pr->PurchaseRequest->description : '' }}</div>
				</div>
				<div class="row">
					<label class="col-sm-3">Dibuat Oleh</label>
					<div class="col-sm-8">: {{ ($pr->PurchaseRequest) ? $pr->PurchaseRequest->creator->name : '' }}</div>
				</div>
				<div class="row">
					<label class="col-sm-3">Dibuat Tanggal</label>
					<div class="col-sm-8">: {{ idDate($pr->created_at) }}</div>
				</div>
				<div class="row">
					<label class="col-sm-3">Status</label>
					<div class="col-sm-8">: {!! getStatusPR($pr->status) !!}</div>
				</div>
			</div>
		</div>

		<h6 class="mT-30">Daftar Barang</h6>

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
				@endphp
					@foreach ($pr_item as $item)
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
								[{{ $item->productCode }}] {{ $item->product }} {!! $item->productPartNumber != NULL ? '<br> PN/Spec: '.$item->productPartNumber : '' !!}
								<br>{{ $item->productBrand != NULL ? 'Brand: '.$item->productBrand : '' }}
							</td>
							<td>
								{!! $item->notes !!} 
							</td>
							<td>{{ $item->qty }} {{ $item->measure }}</td>
							<td>{{ $item->flag == "normal" ? "Normal" : "Urgent" }} </td>
							<td>{{ date('d/m/Y',strtotime( $item->needed_on_date)) }}</td>
							<td>
								@if($item->po_status == 3 && $item->qty_parsial == 0)
									<span class="badge badge-danger">PR Closed</span><br>
									{{ $pr_item->notes }}
								@endif
								@if($item->po_status == 3 && $item->qty_parsial != 0)
									<span class="badge badge-danger">PR Parsial Closed</span><br>
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
</div>

<div class="modal fade" id="modalMR" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	<div class="modal-dialog modal-xl" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="modalMdTitle">MR File</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			</div>
			<div class="modal-body">
				<embed class="col align-self-center" src="{{ asset('storage'.$pr->PurchaseRequest->mr_file) }}" width="600" height="500" alt="pdf" />
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
	});

    function printExternal(url) {
        var printWindow = window.open(url, 'Print', 'left=200, top=200, width=950, height=800, toolbar=0, resizable=0');
        printWindow.addEventListener('load', function() {
            printWindow.print();
        }, true);
    }
</script>
@stop
