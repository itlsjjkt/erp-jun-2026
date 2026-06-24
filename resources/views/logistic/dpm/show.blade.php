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
			<div class="col-sm-6 text-right">
                <!-- Button Group with Border -->
                <div class="btn-group" role="group">
                    @if($pr->status == 11)
                        <!-- Edit Button -->
                        <a href="{{ route('purchase_request.edit', Hashids::encode($pr->id)) }}" title="Edit" data-toggle="tooltip" class="btn btn-outline border-right">
                            <span class="ti-pencil-alt icon-lg"></span> Edit
                        </a>
                    @endif

                    <!-- Print Button -->
                    <a class="btn btn-outline" href="#" title="Print Data" onclick="printExternal('/purchase_print/{{ Hashids::encode($pr->id) }}/print')">
                        <i class="ti-printer icon-lg"></i> Print
                    </a>

                    @if($pr->status == 11)

                        <!-- Publish DPM -->
                        <form class='publish-form' action='{{ route('purchase_request.publish_approval', ['id' => Hashids::encode($pr->id)]) }}' method='POST'>
                            @csrf
                            <button type="submit" class='btn btn-outline text-primary' title='Publish DPM' data-toggle='tooltip'>
                                <i class='ti-new-window icon-lg'></i> Publish
                            </button>
                        </form>

                        <!-- Reject DPM -->
                        <button title="Reject DPM" class="btn btn-sm btn-reject-dpm text-danger" style="background-color: transparent;" data-toggle="modal" data-target="#modalAlasanReject" data-id="{{Hashids::encode($pr->id)}}"><i class='ti-power-off icon-lg'></i> Reject</button>
                    @endif
                </div>
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
				<h6 class="text-center font-weight-bold mB-30" style="text-decoration:underline">{!! $pr->doc_no !!}</h6>
				<div class="row">
					<div class="col-sm-6">
						<div class="row">
							<label class="col-sm-3">Perusahaan </label>
							<div class="col-sm-8">: {{ $pr->company }}</div>
						</div>
						<div class="row">
							<label class="col-sm-3">Lokasi/Kapal </label>
							<div class="col-sm-8">: {{ $pr->location ?? '-' }}</div>
						</div>
						<div class="row">
							<label class="col-sm-3">Departemen </label>
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
							<div class="col-sm-8">: {{ strtoupper($pr->type)}} @if ($pr->request_type == 1) - Pengganti @endif</div>
						</div>
						<div class="row">
							<label class="col-sm-3">Deskripsi/PIC</label>
							<div class="col-sm-8">: {{ ($pr->description) ? $pr->description : '-' }}</div>
						</div>
						<div class="row">
							<label class="col-sm-3">Dibuat Oleh</label>
							<div class="col-sm-8">: {{ $pr->created . ' [' . idDate($pr->created_at) . ']'}}</div>
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

                @if ($pr->request_type == 1)
                    <div class="row mt-3">
                        <div class="col-lg-6">
                            <div class="alert alert-info mT-3">
                                <div class="mb-3"><strong>Data Transfer Out</strong></div>
                                <div class="row">
                                    <label class="col-sm-3">No Dokumen</label>
                                    <div class="col-sm-7">
                                        : {{$dataWti->doc_no_wto}}
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label class="col-sm-3">Lokasi WTO</label>
                                    <div class="col-sm-7">
                                        : {{getCompanyByLocationId($dataWti->location_id_wto)->code.' - '.getLocationByID($dataWti->location_id_wto)->name}}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="alert alert-info mT-3">
                                <div class="mb-3"><strong>Data Transfer In</strong></div>
                                <div class="row">
                                    <label class="col-sm-3">No Dokumen</label>
                                    <div class="col-sm-7">
                                        : {{ $dataWti->doc_no }}
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label class="col-sm-3">Lokasi WTI</label>
                                    <div class="col-sm-7">
                                        : {{getCompanyByLocationId($dataWti->location_id)->code.' - '.getLocationByID($dataWti->location_id)->name}}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

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
                                        <small>
                                            {!! $item->productPartNumber != NULL ? 'PN : '.$item->productPartNumber : 'PN : -' !!} <br>
                                            {!! $item->productBrand != NULL ? 'Brand: '.$item->productBrand : 'Brand: -' !!}
                                            @if ($item->request_type_item == 1)
                                                <br> <span class="text-danger">PENGGANTIAN ITEM MILIK {{getCompanyByLocationId($item->return_location)->alias.' - '.getLocationByID($item->return_location)->name}}</span>
                                            @endif
                                        </small>
									</td>
									<td style="width: 250px !important;">{!! $item->notes !!}</td>
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
										<td>{!! getStatusItemDPM($item->status,$item->pr_status,$item->po_status,$item->qty_parsial,$pr->status,$pr->type) !!} </td>
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

<div class="modal fade" id="modalAlasanReject" tabindex="-1" role="dialog" aria-labelledby="modalAlasanReject" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalAlasanReject">Reject DPM</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="detailmMdalAlasanReject"></div>
            </div>
        </div>
    </div>
</div>

@stop


@section('js')

<script  type='text/javascript'>

    $(document).on('click', '.btn-reject-dpm', function() {
        var dpm_id = $(this).data('id');
        var url = "{{ route('purchase_request.getDpmRejectById', ['id' => ':id']) }}".replace(':id', dpm_id);
        $.ajax({
            url: url,
            method: 'GET',
            success: function(response) {
                $('#detailmMdalAlasanReject').html(`
                    <div style="display: flex; justify-content: flex-start; margin-bottom: 10px;">
                        <span style="font-weight: bold; margin-right: 10px; min-width: 120px;">NO DPM</span>
                        <span style="flex-grow: 1; text-align: left;">: ${response.doc_no}</span>
                    </div>
                    <form action="{{ route('purchase_request.reject', ['id' => 'ID_PLACEHOLDER']) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input name="dpm_id" type="hidden" value="${response.id}" />
                        <div style="display: flex; justify-content: flex-start; margin-bottom: 10px;">
                            <span style="font-weight: bold; margin-right: 10px; min-width: 120px;">Alasan Reject</span>
                            <span style="flex-grow: 1; text-align: left;">
                                <input class="form-control" name="alasan_reject" type="text" value="" />
                            </span>
                        </div>
                        <div style="display: flex; justify-content: flex-end; margin-top: 10px;">
                            <button class="btn btn-danger" type="submit">Reject</button>
                        </div>
                    </form>
                `);

                $('#rejectForm').on('keydown', 'input', function(event) {
                    if (event.key === 'Enter') {
                        event.preventDefault();
                    }
                });

                $('#detailmMdalAlasanReject form').attr('action', function(i, val) {
                    return val.replace('ID_PLACEHOLDER', response.id);
                });
            },
            error: function() {
                $('#detailmMdalAlasanReject').html('<p>Error loading DPM details.</p>');
            }
        });
    });

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

    $(document).on('click', ".rejectBtn_", function(e) {
        var _this = $(this);
        var form = _this.parents('form');
        e.preventDefault();
        if (form.valid() ) {
            Swal.fire({
                title: 'REJECT DPM',
                text: 'Apakah anda yakin melanjutkan ini?',
                type: 'question',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonClass: 'btn btn-danger',
                cancelButtonClass: 'btn btn-primary',
                confirmButtonText: 'Ya, Reject',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then(res => {
                if (res.value) {
                    _this.closest("form").submit();
                }
            });
        }
    });



</script>
@stop
