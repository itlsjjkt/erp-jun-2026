@extends('layouts.app')

@section('page-header')
    Purchase Requisition
@endsection

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('purchasing.pr') }}">Purchase Requisition</a></li>
        <li class="breadcrumb-item active" aria-current="page">Detail</li>
    </ol>
@endsection

@section('content')

<div class="mB-40">
	<div class="bgc-white p-30 bd">
		@php
            use Illuminate\Support\Facades\Gate;
        @endphp
        @if(!Gate::allows('pr_monitoring'))
			<div class="row mb-1 justify-content-end">
				<div class="col-sm-12">
					<a class="btn btn-outline float-right" href="#" title="Print Data" onclick='printExternal("/purchasing/pr_print/{{ Hashids::encode($pr->id) }}/print")'><i class="ti-printer icon-lg"></i></a>
				</div>
			</div>
		@endif

		<div class="d-block">
			<ul class="nav nav-tabs" role="tablist">
				<li class="nav-item">
					<a class="nav-link active" data-toggle="tab" href="#tab1" role="tab">Purchase Requisition (PR)</a>
				</li>
				<li class="nav-item">
					@if($pr->PurchaseRequest)
						<?php
							if ($pr->PurchaseRequest->mr_file) $badge = "<sup class='badge '><span class='bg-success rounded-circle text-success' style='padding:0 5px'></span></sup>";
							else $badge = "";
						?>
						<a class="nav-link" data-toggle="tab" href="#tab2" role="tab">Dokumen MR {!! $badge !!}</a>
					@endif
				</li>
			</ul>
		</div>

		<div class="tab-content mT-30" >
  			<div class="tab-pane active" id="tab1" role="tabpanel" aria-labelledby="tab1">
			  	<h6 class="text-center font-weight-bold mB-30" style="text-decoration:underline">{{ $pr->doc_no }}</h6>
				<div class="row mt-5">
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
							<label class="col-sm-3">Lokasi/Kapal </label>
							<div class="col-sm-8">: {{ $pr->location->name ?? '-' }}</div>
						</div>
						<div class="row">
							<label class="col-sm-3">Departement </label>
							<div class="col-sm-8">: {{ ($pr->department) ? $pr->department->name : '' }}</div>
						</div>
						<div class="row">
							<label class="col-sm-3">Project </label>
							<div class="col-sm-8">: {{ $pr->project->name ?? $pr->PurchaseRequest->project->name ?? '-'  }}</div>
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

				@if ($pr->status == 5)
					<hr>
					<div class="alert alert-danger"><strong> DITUTUP</strong>
						<br>{{ $pr->notes }}
					</div>
				@endif

				<h6 class="mT-30">Daftar Item</h6>
				<table class="table table-bordered">
					<thead>
						<th>No</th>
						<th style="width:300px">Nama Barang</th>
						<th>Catatan</th>
						<th style="min-width:100px">QTY</th>
						<th>Flag </th>
						<th>Status</th>
						<th>Tgl Dibutuhkan</th>
						<th>Purchaser</th>
						<th>Aksi</th>
					</thead>
					<tbody>
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
					</tbody>
				</table>
				<hr>

					<div class="btn-group">
						<?php
							if($pr->status != 5 && $pr->status != 4 && $pr->status != 6 ){
								if(auth()->user()->can('purchase_admin') ){
									if($pr->type =='po'){
										echo "<a href='".route('purchasing.pr.cancel', ['id' => Hashids::encode($pr->id)])."' title='Tutup PR' class='btn btn-danger'><span class='ti-power-off icon-lg'></span> TUTUP PR</a>";
									}
									else {
										echo "<form method='POST' action='".route('purchasing.pr.done')."'>".
											csrf_field()
											."<input type='hidden' name='pr_id' value='".$pr->id."'>
											<button type='submit' class='btn btn-success' id='btn-done'><i class='ti-check'></i> SET SELESAI</button>

										</form>";

        								echo "<a href='#' data-toggle='modal' data-target='#modalClosePR' title='Close PR' data-toggle='tooltip' class='btn ml-2 btn-danger modalCloseDocument'><span class='ti-power-off icon-lg'></span> Close PR</a>";

									}
								}
								if (count($pr->assignItem) > 0 && auth()->user()->can('purchaser_assign')){
									echo "<a href='".route('purchasing.pr.assign', ['id' => Hashids::encode($pr->id)])."' title='Assigned to Purchaser' data-toggle='tooltip' class='btn ml-2 btn-info'><span class='ti-user icon-lg'></span>  ASSIGN PR</a>";
								}

								if(count($pr->reassignItem) > 0 && auth()->user()->can('purchaser_assign')){
									echo "<a href='".route('purchasing.pr.reassign', ['id' => Hashids::encode($pr->id)])."' title='Re-assign Purchaser' data-toggle='tooltip' class='btn ml-2 btn-primary'><span class='ti-user icon-lg'></span>  RE-ASSIGN PR</a>";
								}
							}
							// if(in_array($pr->status, array(null,'0','1','2')) && auth()->user()->can('purchase_admin') ){
							// 	echo "<a href='#' data-toggle='modal' data-target='#modalPR' title='Reject PR' data-toggle='tooltip' class='btn ml-2 btn-warning modalRevision'><span class='ti-pencil icon-lg'></span> Reject PR</a>";
							// }
						?>
					</div>
			</div>
			<div class="tab-pane" id="tab2" role="tabpanel" aria-labelledby="tab2">
				<div class="row">
					<div class="col-12">
						@if ($pr->PurchaseRequest)
							@if ($pr->PurchaseRequest->mr_file)
								<embed class="col align-self-center" src="{{ asset('storage'.$pr->PurchaseRequest->mr_file) }}" width="600" height="800" alt="pdf" />
							@endif
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


<div class="modal fade" tabindex="-1" role="dialog" id="modalPR">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<form id="prForm" action="" method="post">
				<input type="hidden" name="pr_id" value="{{ $pr->id }}">
				@csrf
				<div class="modal-header">
					<h5 class="modal-title">Alasan</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<textarea name="reason" class="form-control" required></textarea>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
					<button type="submit" class="btn btn-danger" id="btn-submit">Submit</button>
				</div>
			</form>
		</div>
	</div>
</div>


<div class="modal fade" tabindex="-1" role="dialog" id="modalClosePR">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<form id="prCloseForm" action="" method="post">
				<input type="hidden" name="pr_id" value="{{ $pr->id }}">
				@csrf
				<div class="modal-header">
					<h5 class="modal-title">Alasan</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<textarea name="reason" class="form-control" required></textarea>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
					<button type="submit" class="btn btn-danger" id="btn-submit">Submit</button>
				</div>
			</form>
		</div>
	</div>
</div>


@stop


@section('js')


<script  type='text/javascript'>
    $(document).ready(function() {

	$('.modalCloseDocument').on('click', function () {
		$('#prCloseForm').attr("action", "{{ route('purchasing.pr.close_document') }}");
	});

	$('.modalMd').off('click').on('click', function () {
			$('#modalMdContent').load($(this).attr('value'));
		});
	});


	$('#modalAssigned').on('show.bs.modal', function (e) {
		var id = $(e.relatedTarget).data('id');
		$('#modalAssignedContent').load("{{ route('purchasing.pr.assign') }}?id="+ id);
	});

	$('.modalMdPO').off('click').on('click', function () {
		$('#modalMdContentPO').load($(this).attr('value'));
	});

	// $('.modalRevision').on('click', function () {
	// 	$('#prForm').attr("action", "{{ route('purchasing.pr.revision') }}");
	// });

	$('.modalClose').on('click', function () {
		$('#prForm').attr("action", "{{ route('purchasing.pr.close') }}");
	});


	$(document).on('click', "#btn-done", function(e) {
		var _this = $(this);
		e.preventDefault();
		Swal.fire({
			title: 'Konfirmasi', // Opération Dangereuse
			text: 'Apakah anda yakin untuk menyelesaikan PR ini ?', // Êtes-vous sûr de continuer ?
			type: 'error',
			showCancelButton: true,
			confirmButtonColor: 'null',
			cancelButtonColor: 'null',
			confirmButtonClass: 'btn btn-danger',
			cancelButtonClass: 'btn btn-primary',
			confirmButtonText: 'Ya, tutup!', // Oui, sûr
			cancelButtonText: 'Batal', // Annuler
		}).then(res => {
			if (res.value) {
				_this.closest("form").submit();
			}
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
