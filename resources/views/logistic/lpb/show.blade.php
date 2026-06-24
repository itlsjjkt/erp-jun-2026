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
				@php
					use Illuminate\Support\Facades\Gate;
				@endphp
				@if (!Gate::allows('lpb_monitoring') || Gate::allows('lpb_print'))
					<!-- <a class="btn btn-outline float-right" href="#" title="Print Data" onclick='printExternal("/logistic/lpb_print/{{ Hashids::encode($lpb->id) }}/print")'><i class="ti-printer icon-lg"></i></a> -->
				@endif
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
				<li class="nav-item">
					@php
						if ($lpb->attachment_file) $badge = "<sup class='badge '><span class='bg-success rounded-circle text-success' style='padding:0 5px'></span></sup>";
						else $badge = "";
					@endphp
					<a class="nav-link" data-toggle="tab" href="#tab4" role="tab">Attachment {!!$badge!!}</a>
				</li>
			</ul>
		</div>

		<div class="tab-content mT-30" >
  			<div class="tab-pane active" id="tab1" role="tabpanel" aria-labelledby="tab1">
				<div class="d-flex justify-content-between align-items-center mb-3">
				<h6 class="mb-0">Detail LPB</h6>
					@if(!Gate::allows('lpb_monitoring') || Gate::allows('lpb_print'))
						@if($lpb->verified_at)
							<a class="btn btn-outline-primary btn-sm"
							href="#"
							title="Print Data"
							onclick='printExternal("/logistic/lpb_print/{{ Hashids::encode($lpb->id) }}/print")'>
								<i class="ti-printer mr-1"></i> Print
							</a>
						@endif
					@endif
                </div>
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
						<div class="row">
							<label class="col-sm-3">Kapal / Departement</label>
							<div class="col-sm-7">: {{$lpb->department}}</div>
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

				@if ($lpb->status == 3)
					<hr>
					<div class="alert alert-danger"><strong> DITUTUP</strong>
						<br>
                        <small>
                            {!! $lpb->reason !!}
                        </small>
					</div>
				@endif

                {{-- VERIFY BANNER --}}
                @if($lpb->verified_at)
                    <div class="alert alert-success mb-4">
                        <span><i class="ti-check mr-2"></i>Dirverifikasi oleh <strong>{{ getUserByID($lpb->verified_by) }}</strong> pada {{ \Carbon\Carbon::parse($lpb->verified_at)->format('d/m/Y H:i') }}</span>
                        @if($lpb->verified_notes)
                            <br><small class="mt-1 d-block"><strong>Catatan:</strong> {{ $lpb->verified_notes }}</small>
                        @endif
                    </div>
                @else
                    @if($lpb->verify_request_at)
                        <div class="alert alert-danger mb-3">
                            <span><i class="ti-comment-alt mr-2"></i>Pesan dari <strong>{{ getUserByID($lpb->verify_request_by) }}</strong> pada {{ \Carbon\Carbon::parse($lpb->verify_request_at)->format('d/m/Y H:i') }}:</span>
                            <br><span class="mt-1 d-block">{{ $lpb->verify_request_notes }}</span>
                        </div>
                    @endif
                    <div class="alert alert-warning mb-4">
                        <span><i class="ti-info-alt mr-2"></i>Dokumen ini belum diverifikasi.</span>
                    </div>
                @endif

				<h6 class="mT-30">Daftar Barang</h6>
				<table class="table table-bordered">
					<thead>
						<tr>
							<th rowspan="2" style="width:50px">No</th>
							<th rowspan="2" style="width:350px">Nama Barang</th>
							<th rowspan="2" style="width:300px">Spesifikasi</th>
							<th colspan="2" class="text-center">Jumlah </th>
							<th rowspan="2" class="text-center">Satuan</th>
							{{-- <th rowspan="2" class="text-center">Satuan Konversi</th>
							<th rowspan="2" class="text-center">Konversi</th> --}}
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
									{{-- <td>{{ $item->measure_inventory }}</td>
									<td>{{ $item->productConversion * $item->qty}}</td> --}}
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
											}elseif($val->jenis=='revisi'){
												echo  "melakukan revisi LPB</p>";
											}elseif($val->jenis=='update_dokumen'){
												echo  "melakukan update attachment dokumen LPB</p>";
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

			<div class="tab-pane" id="tab4" role="tabpanel" aria-labelledby="tab4">
				<div class="row">
					<div class="col-12">
						@if ($lpb->attachment_file)
							@if($lpb->created_by == Auth::user()->id || Auth::user()->id == 1 )
								<p class="text-center">
									<button title="Upload Attachment Dokumen LPB" class="btn btn-sm btn_upload_dokumen_lpb fw-bold text-primary" style="background-color: transparent;" data-toggle="modal" data-target="#modalUploadDokumenLpb" data-id="{{Hashids::encode($lpb->id)}}"><i class="ti-upload icon-lg"> Update Attachment Dokumen LPB</i></button> <br>
								</p>
							@endif
							<p class="text-center">
								<embed class="col align-self-center" src="{{ asset('storage'.$lpb->attachment_file) }}" width="600" height="800" alt="pdf" />
							</p>
							@else
							<p class="text-center">Belum melampirkan attachment dokumen LPB</p> <br>
							@if($lpb->created_by == Auth::user()->id || Auth::user()->id == 1)
								<p class="text-center">
									<button title="Upload Attachment Dokumen LPB" class="btn btn-sm btn_upload_dokumen_lpb fw-bold text-primary" style="background-color: transparent;" data-toggle="modal" data-target="#modalUploadDokumenLpb" data-id="{{Hashids::encode($lpb->id)}}"><i class="ti-upload icon-lg"> Upload Attachment</i></button>
								</p>
							@endif
						@endif
					</div>
				</div>
			</div>

		</div>
	</div>
</div>
<div class="modal fade" id="modalUploadDokumenLpb" tabindex="-1" role="dialog" aria-labelledby="modalUploadDokumenLpb" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalUploadDokumenLpb">Upload Attachment Dokumen Lpb</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="detailmodalUploadDokumenLpb"></div>
            </div>
        </div>
    </div>
</div>
@stop


@section('js')

	<script  type='text/javascript'>

	$(document).on('click', '.btn_upload_dokumen_lpb', function() {
		var lpb_id = $(this).data('id');
		var url = "{{ route('logistic.lpb.get_dokumen_lpb', ['id' => ':id']) }}".replace(':id', lpb_id);
		debugger;
		$.ajax({
			url: url,
			method: 'GET',
			success: function(response) {
				$('#detailmodalUploadDokumenLpb').html(`
					<div style="display: flex; justify-content: flex-start; margin-bottom: 10px;">
						<span style="font-weight: bold; margin-right: 10px; min-width: 120px;">NO LPB</span>
						<span style="flex-grow: 1; text-align: left;">: ${response.doc_no}</span>
					</div>
					<form action="{{ route('logistic.lpb.upload_dokumen', ['id' => 'ID_PLACEHOLDER']) }}" method="POST" enctype="multipart/form-data">
						@csrf
						<input name="lpb_id" type="hidden" value="${response.id}" />
						<div style="display: flex; justify-content: flex-start; margin-bottom: 10px;">
							<span style="font-weight: bold; margin-right: 10px; min-width: 120px;">Update Dokumen</span>
							<span style="flex-grow: 1; text-align: left;">
								: <input class="mb-3" name="attachment_file" type="file" accept="application/pdf" value="" />
							</span>
						</div>
						<div style="display: flex; justify-content: flex-end; margin-top: 10px;">
							<button class="btn btn-primary" type="submit">Upload</button>
						</div>
					</form>
				`);
				$('#rejectForm').on('keydown', 'input', function(event) {
					if (event.key === 'Enter') {
						event.preventDefault();
					}
				});

				$('#detailmodalUploadDokumenLpb form').attr('action', function(i, val) {
					return val.replace('ID_PLACEHOLDER', response.id);
				});
			},
			error: function() {
				$('#detailmodalUploadDokumenLpb').html('<p>Error loading LPB details.</p>');
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
