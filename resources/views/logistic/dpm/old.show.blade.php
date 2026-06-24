@extends('layouts.app')

@section('page-header')
	View <small>{{ $pr->doc_no }}</small>
@stop

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('purchase_request.index') }}">DPM</a></li>
        <li class="breadcrumb-item active" aria-current="page">Detail</li>
    </ol>
@endsection

@section('content')

<div class="mB-40">
	<div class="bgc-white p-30 bd">
		<div class="row mb-1 justify-content-end">
			<div class="col-sm-6">
				<a class="btn btn-outline float-right" href="#" title="Print Data" onclick='printExternal("/purchase_print/{{ Hashids::encode($pr->id) }}/print")'><i class="ti-printer icon-lg"></i></a>
			</div>
		</div>
		<div class="d-block">
			<ul class="nav nav-tabs" role="tablist">
				<li class="nav-item">
					<a class="nav-link active" data-toggle="tab" href="#tab1" role="tab">DPM</a>
				</li>
				<li class="nav-item">
					<?php 
					if ($pr->mr_file)
						$badge = "<sup class='badge '><span class='bg-success rounded-circle text-success' style='padding:0 5px'></span></sup>";
					else
						$badge = "";
					?>
					<a class="nav-link" data-toggle="tab" href="#tab2" role="tab">Dokumen MR {!! $badge !!}</a>
				</li>
			</ul>
		</div>
		
		<div class="tab-content mT-30" >
  			<div class="tab-pane active" id="tab1" role="tabpanel" aria-labelledby="tab1">
				<h6 class="text-center font-weight-bold mB-30" style="text-decoration:underline">{{ $pr->doc_no }}</h6>
				<div class="row">
					<div class="col-sm-6"> 
						<div class="row">
							<label class="col-sm-3">Perusahaan </label>
							<div class="col-sm-8">: {{ $pr->company }}</div>
						</div>
						<div class="row">
							<label class="col-sm-3">Kapal/Departemen </label>
							<div class="col-sm-8">: {{ $pr->department }}</div>
						</div>
						<div class="row">
							<label class="col-sm-3">Project</label>
							<div class="col-sm-8">: {{ $pr->project }}</div>
						</div>
					</div>
					<div class="col-sm-6">
						<div class="row">
							<label class="col-sm-3">Tipe DPM</label>
							<div class="col-sm-8">: {{ strtoupper($pr->type) }}</div>
						</div>
						<div class="row">
							<label class="col-sm-3">Deskripsi/PIC</label>
							<div class="col-sm-8">: {{ ($pr->description) ? $pr->description : '-' }}</div>
						</div>
						<div class="row">
							<label class="col-sm-3">Dibuat Oleh</label>
							<div class="col-sm-8">: {{ $pr->created }}</div>
						</div>
					
						@if ( $pr->status != '0'  )
							<div class="row">
								<label class="col-sm-3">Publish Tanggal</label>
								<div class="col-sm-8">: {{ idDate($pr->updated_at) }}</div>
							</div>
						@endif

						@if ( $pr->status == '3' )
							<div class="row">
								<label class="col-sm-3">Status</label>
								<div class="col-sm-8">: <span class="badge badge-warning">HOLD </span> 
								</div>
							</div>
							<div class="row">
								<label class="col-sm-3"></label>
								<div class="col-sm-8"><div class="alert alert-warning p-10"> Alasan: {{ $pr_history->message }} </div>
								</div>
							</div>
						@endif
					</div>
				</div>

				<table class="table table-bordered mT-30">
					<thead>
						<th>No</th>
						<th>Nama Barang</th>
						<th>Catatan</th>
						<th>QTY</th>
						<th>Flag</th>
						<th>Tgl Dibutuhkan</th>
						<th>Last Approved</th>
						<th>Next Approval</th>
						@if ( $pr->status != '3' )
							<th>Status</th>
						@endif
						<th>Aksi</th>
					</thead>
					<tbody>
						@if (count($pr_items) > 0)
							@php
								$no = 1;
							@endphp

							@foreach ($pr_items as $item)
								<tr data-entry-id="{{ $item->id }}">
									<td>{{ $no }}</td>
									<td>[{{ $item->productCode }}] - {{ $item->product }} 
										<br>
										{!! $item->productPartNumber != NULL ? '<small> PN: '.$item->productPartNumber.'</small>' : '' !!} {!! $item->productBrand != NULL ? '<small>Brand: '.$item->productBrand.'</small>' : '' !!}
									</td>
									<td>{!! $item->notes !!}</td>
									<td>{{ $item->qty }} {{ $item->measure }}</td>
									<td>{{ $item->flag == "normal" ? "Normal" : "Urgent" }} </td>
									<td>{{ date('d/m/Y',strtotime( $item->needed_on_date)) }}</td>
									<td>{{ $item->approved }} <br><small>
											@if ($item->last_approved_at != NULL)
											{{ date('d/m/Y',strtotime( $item->last_approved_at)) }}
											@endif
									</small>
									</td>
									<td>
										@php
											$val = array(0,1); 
										@endphp
										@if (in_array($item->status, $val))
											{{ getNextApprovalDPM($pr->location_id,$item->step) }}
										@endif
									</td>
									@if ( $pr->status != '3' )
										<td>{!! getStatusDPM($item->status ) !!} </td>
									@endif
									<td>
										@if(getDPMLog($item->id) > 0)
											<a href="#" value="{{ action('Logistic\PurchaseRequestController@getPrItemNotes',['id'=>$item->id]) }}" class="icon-lg modalMd" title="Show Data" data-toggle="modal" data-target="#modalMd"><span class="ti-eye text-danger"></span></a>
										@else
											<a href="#" value="{{ action('Logistic\PurchaseRequestController@getPrItemNotes',['id'=>$item->id]) }}" class="icon-lg modalMd" title="Show Data" data-toggle="modal" data-target="#modalMd"><span class="ti-eye text-muted"></span></a>
										@endif
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
				<hr>
				@if ($pr->status == '0')

					<div class="mt-4 btn-group">

						<a href="{{ route('purchase_request.edit', Hashids::encode($pr->id)) }}" class="btn btn-info mr-1 text-uppercase fsz-sm fw-600">Edit Draft</a>

						<form class='delete' action="{{ route('purchase_request.delete', ['id' => $pr->id]) }}" method='POST'>
							{{ csrf_field() }}
							<button class='btn btn-danger mr-1 text-uppercase fsz-sm fw-600' title='Hapus'>Hapus Draft</button>
						</form>
					</div>

				@endif

				@if ($pr->status == '3')

					<div class="mt-4 btn-group">

						<a href="{{ route('purchase_request.edit', Hashids::encode($pr->id)) }}" class="btn btn-info mr-1 text-uppercase fsz-sm fw-600">Revisi DPM</a>

						<form class='delete' action="{{ route('purchase_request.delete', ['id' => $pr->id]) }}" method='POST'>
							{{ csrf_field() }}
							<button class='btn btn-danger mr-1 text-uppercase fsz-sm fw-600' title='Hapus'>Hapus DPM</button>
						</form>
					</div>

				@endif

			</div>

			<div class="tab-pane" id="tab2" role="tabpanel" aria-labelledby="tab2">
				<div class="row">
					<div class="col-12">
						@if ($pr->mr_file)
							<embed class="col align-self-center" src="{{ asset('storage'.$pr->mr_file) }}" width="600" height="500" alt="pdf" />
						@else
							<p class="text-center">File MR tidak dilampirkan dalam DPM</p>
						@endif
					</div>
				</div>
  			</div>

		</div>
	</div>
</div>

<div class="modal fade" id="modalMd" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="modalMdTitle">History Approval Data</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			</div>
			<div class="modal-body">
				<div class="modalError"></div>
				<div id="modalMdContent"></div>
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
	});

   function printExternal(url) {
        var printWindow = window.open(url, 'Print', 'left=200, top=200, width=950, height=800, toolbar=0, resizable=0');
        printWindow.addEventListener('load', function() {
            printWindow.print();
        }, true);
    }
</script>
@stop