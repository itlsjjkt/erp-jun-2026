@extends('layouts.app')

@section('page-header')
	View LPB <small>{{ $lpb->doc_no }}</small>
@stop

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('logistic.lpb.index') }}">Laporan Penerimaan Barang</a></li>
        <li class="breadcrumb-item active" aria-current="page">Detail</li>
    </ol>
@endsection

@section('content')

<div class="mB-40">
	<div class="bgc-white p-30 bd">
		<div class="row mb-1 justify-content-end">
			<div class="col-sm-6">
				<a class="btn btn-outline float-right" href="#" title="Print Data" onclick='printExternal("/logistic/lpb_print/{{ Hashids::encode($lpb->id) }}/print")'><i class="ti-printer icon-lg"></i></a>
			</div>
		</div>
		<div class="d-block">
			<ul class="nav nav-tabs" role="tablist">
				<li class="nav-item">
					<a class="nav-link active" data-toggle="tab" href="#tab1" role="tab">Laporan Penerimaan Barang (LPB)</a>
				</li>
				<li class="nav-item">
					<a class="nav-link" data-toggle="tab" href="#tab3" role="tab">Histori</a>
				</li>
			</ul>
		</div>
		
		<div class="tab-content mT-30" >
  			<div class="tab-pane active" id="tab1" role="tabpanel" aria-labelledby="tab1">
			  	<h6 class="text-center font-weight-bold mB-40" style="text-decoration:underline">{{ $lpb->doc_no }}</h6>
				<div class="row">
					<div class="col-sm-6"> 
						<div class="row">
							<label class="col-sm-3">No. PO</label>
							<div class="col-sm-7">: {{ $lpb->po_no }}</div>
						</div>
						<div class="row">
							<label class="col-sm-3">No. PR</label>
							<div class="col-sm-7">: {{ $lpb->pr_no }}</div>
						</div>
						<div class="row">
							<label class="col-sm-3">No. DPM</label>
							<div class="col-sm-7">: {{ $lpb->dpm_no }}</div>
						</div>
					</div>
					<div class="col-sm-6">
						<div class="row">
							<label class="col-sm-3">Penerima</label>
							<div class="col-sm-7">: {{ $lpb->received_by }}</div>
						</div>
						<div class="row">
							<label class="col-sm-3">Dibuat Oleh</label>
							<div class="col-sm-7">: {{ $lpb->created }} [ {{ idDate($lpb->created_at) }}]
							</div>
						</div>
						<div class="row">
							<label class="col-sm-3">Supplier</label>
							<div class="col-sm-8">: 
								{{ $lpb->supplier }} <br> [ Nama: {{ $lpb->supplierPIC }} / Telp. {{ $lpb->supplierTelp }} ]
							</div>
						</div>
					</div>
				</div>

				<h6 class="mT-30">Daftar Barang</h6>
				<table class="table table-bordered">
					<thead>
						<tr>
							<th rowspan="2" style="width:50px">No</th>
							<th rowspan="2" style="width:350px">Nama Barang</th>
							<th rowspan="2" style="width:300px">Spesifikasi</th>
							<th colspan="2" class="text-center">Jumlah </th>
							<th rowspan="2" class="text-center">Satuan</th>
							<th rowspan="2" class="text-center">Catatan </th>
						</tr>
						<tr>
							<th style="width:150px" class="text-center">Dipesan</th>
							<th style="width:150px" class="text-center">Diterima</th>
						</tr>
					</thead>
					<tbody>
							@php
								$no = 1;
							@endphp
							@foreach ($lpb_items as $item)
								<tr>
									<td>{{ $no }}</td>
									<td>
									 {{ $item->productCode }} - {{ $item->product }}
									 {!! $item->productPartNumber != NULL ? '<br> PN/Spec: '.$item->productPartNumber : '' !!}  {{ $item->productBrand != NULL ? 'Brand: '.$item->productBrand : '' }}
									</td>
									<td>
										{!! $item->specification !!} <br>
									</td>
									<td class="text-center">{{ $item->qtyPO }}</td>
									<td class="text-center">{{ $item->qty }}</td>
									<td>{{ $item->measure }}</td>
									<td>{{ $item->notes }}</td>
								</tr>
							@php
								$no++;
							@endphp
							@endforeach
					</tbody>
					
				</table>
				<hr>
				@if($lpb->status==0)

					<div class="mt-2 btn-group">

					<a href="{{ route('logistic.lpb.edit',Hashids::encode($lpb->id)) }}" class="btn btn-info mr-2 text-uppercase fsz-sm fw-600">Edit Draft</a>

					<form class='delete' action="{{ route('logistic.lpb.delete', ['id' => $lpb->id]) }}" method='POST'>
						{{ csrf_field() }}
						<button class='btn btn-danger mr-2 text-uppercase fsz-sm fw-600' title='Hapus'>Hapus Draft</button>
					</form>

					</div>
				@endif


			</div>

			<div class="tab-pane" id="tab3" role="tabpanel" aria-labelledby="tab3">
				<div class="timeline">
					<div class="timeline__group">
						<?php foreach($lpb_history as $val){?>
							<div class="timeline__box">
								<div class="timeline__date"></div>
								<div class="timeline__post">
									<div class="timeline__content">
										<?php 
											$employeeName = $val->employee;
											echo "<span>". date('d/m/Y H:i A',strtotime($val->created_at)). "</span><br>";
											echo "<strong>".ucwords(strtolower($employeeName))."</strong> ";
											if($val->jenis=='insert'){
												echo  "melakukan pengajuan LPB ";
											}elseif($val->jenis=='draft'){
												echo  "melakukan pengajuan LPB dengan status Draft</p>";
											}else{
												echo  "melakukan publish LPB ";
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

	<script  type='text/javascript'>
	function printExternal(url) {
			var printWindow = window.open(url, 'Print', 'left=200, top=200, width=950, height=800, toolbar=0, resizable=0');
			printWindow.addEventListener('load', function() {
				printWindow.print();
			}, true);
		}
	</script>
@stop