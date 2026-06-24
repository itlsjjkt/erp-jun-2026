@extends('layouts.app')

@section('page-header')
    Bukti Penerimaan Barang
@stop

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('logistic.bpb.index') }}">Bukti Penerimaan Barang</a></li>
        <li class="breadcrumb-item active" aria-current="page">Detail</li>
    </ol>
@endsection

@section('content')

<div class="mB-40">
	<div class="bgc-white p-30 bd">
        @php
            use Illuminate\Support\Facades\Gate;
        @endphp
        @if(!Gate::allows('bpb_monitoring') || Gate::allows('bpb_print'))
    		<a class="btn btn-outline float-right" href="#" title="Print Data" onclick='printExternal("/logistic/bpb_print/{{ Hashids::encode($bpb->id) }}/print")'><i class="ti-printer icon-lg"></i></a>
        @endif
		<h6><a class="float-left" href="{{ route('logistic.bpb.index') }}"><i class="ti-arrow-left mR-10"></i></a> {{ trans('Back') }}</h6>

        <div class="d-block">
			<ul class="nav nav-tabs" role="tablist">
				<li class="nav-item">
					<a class="nav-link active" data-toggle="tab" href="#tab1" role="tab">Bukti Penerimaan Barang (BPB)</a>
				</li>
				<li class="nav-item">
					@php
						if ($bpb->attachment_file) $badge = "<sup class='badge '><span class='bg-success rounded-circle text-success' style='padding:0 5px'></span></sup>";
						else $badge = "";
					@endphp
					<a class="nav-link" data-toggle="tab" href="#tab4" role="tab">Attachment {!!$badge!!}</a>
				</li>
			</ul>
		</div>
        <div class="tab-content mT-30" >
            <div class="tab-pane active" id="tab1" role="tabpanel" aria-labelledby="tab1">
                <div class="row">
                    <div class="col-sm-6">
                        <div class="row">
                            <label class="col-sm-3">Nomor BPB</label>
                            <div class="col-sm-7">: {{ $bpb->doc_no }}</div>
                        </div>
                        <div class="row">
                            <label class="col-sm-3">Penerima</label>
                            <div class="col-sm-7">: {{ $bpb->received_by }}</div>
                        </div>
                        <div class="row">
                            <label class="col-sm-3">Dibuat Oleh</label>
                            <div class="col-sm-7">: {{ ($bpb->creator) ? $bpb->creator->name : '' }} [ {{ idDate($bpb->created_at) }}]
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="row">
                            <label class="col-sm-3">Nomor SPB</label>
                            <div class="col-sm-7">: {{ ($bpb->spb) ? $bpb->spb->doc_no : '' }}</div>
                        </div>
                        <div class="row">
                            <label class="col-sm-3">Catatan</label>
                            <div class="col-sm-7">: {{ $bpb->notes }}</div>
                        </div>
                        <div class="row">
                            <label class="col-sm-3">Status</label>
                            <div class="col-sm-8">: {!! getStatusData($bpb->status) !!}</div>
                        </div>
                    </div>
                </div>


                {{-- VERIFY BANNER --}}
                @if($bpb->verified_at)
                    <div class="alert alert-success mb-4">
                        <span><i class="ti-check mr-2"></i>Diverifikasi oleh <strong>{{ getUserByID($bpb->verified_by) }}</strong> pada {{ \Carbon\Carbon::parse($bpb->verified_at)->format('d/m/Y H:i') }}</span>
                        @if($bpb->verified_notes)
                            <br><small class="mt-1 d-block"><strong>Catatan:</strong> {{ $bpb->verified_notes }}</small>
                        @endif
                    </div>
                @else
                    @if($bpb->verify_request_at)
                        <div class="alert alert-danger mb-3">
                            <span><i class="ti-comment-alt mr-2"></i>Pesan dari <strong>{{ getUserByID($bpb->verify_request_by) }}</strong> pada {{ \Carbon\Carbon::parse($bpb->verify_request_at)->format('d/m/Y H:i') }}:</span>
                            <br><span class="mt-1 d-block">{{ $bpb->verify_request_notes }}</span>
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
                            <th style="width:50px">No</th>
                            <th style="width:300px">Nama Barang</th>
                            <th >QTY</th>
                            <th style="width:150px">Nomor DPM</th>
                            <th style="width:150px">Nomor PO</th>
                            <th style="width:150px">Nomor LPB</th>
                            <th class="text-center">Catatan </th>
                        </tr>

                    </thead>
                    <tbody>
                            @php
                                $no = 1;
                            @endphp
                            @foreach ($bpb_items as $item)
                                <tr>
                                    <td>{{ $no }}</td>
                                    <td>
                                        [{{ $item->productCode }}] {{ $item->product }} <br>
                                        {!! $item->productPartNumber != NULL ? '<small> PN: '.$item->productPartNumber.'</small>' : '' !!}
                                        {!! $item->productBrand != NULL ? '<small>Brand: '.$item->productBrand.'</small>' : '' !!}
                                    </td>
                                    <td>{{ $item->qty }} {{ $item->measure }}</td>
                                    <td>{{ $item->noDPM }}</td>
                                    <td>{{ $item->noPO }}</td>
                                    <td>{{ $item->noLPB }}</td>
                                    <td>{!! $item->description !!}</td>
                                </tr>
                            @php
                                $no++;
                            @endphp
                            @endforeach
                    </tbody>
                </table>

                @if($bpb->status==0)
                    <div class="mt-4 btn-group">

                        <a href="{{ route('logistic.bpb.edit',Hashids::encode($bpb->id)) }}" class="btn btn-light text-uppercase fsz-sm fw-600 mr-1" id="btn-edit"><i class="fa fa-edit mR-10"></i>Edit Draft</a>

                        <form class="delete" action="{{ route('logistic.bpb.delete', ['id' => $bpb->id]) }}" id="bpb_delete" method='POST'>
                            {{ csrf_field() }}
                            <button class='btn btn-danger mr-1 text-uppercase fsz-sm fw-600' id="btn-delete"><i class="fa fa-trash mR-10"></i>Hapus Draft</button>
                        </form>

                        {!! Form::model($bpb, [
                                'action' => ['Logistic\BpbController@publish', $bpb->id],
                                'method' => 'POST',
                                'id' => 'bpb_publish',
                            ])
                        !!}
                            @foreach ($bpb_items as $item)
                                <input name="product_id[]" type="hidden" value="{{ $item->product_id }}" >
                                <input name="qty[]" type="hidden" value="{{  $item->qty }}" >
                                <input name="no_po[]" type="hidden" value="{{ $item->noPO }}">
                                <input name="price[]" type="hidden" value="{{ $item->price }}">
                                <input name="discount[]" type="hidden" value="{{ $item->price_discount }}">
                                <input name="spb_item_id[]" type="hidden" value="{{ $item->idKoli }}">
                                <input name="location_id[]" type="hidden" value="{{ $item->location_id }}">
                                <input name="conversion[]" type="hidden" value="{{ $item->productConversion }}">
                                <input name="measure_id[]" type="hidden" value="{{ $item->productMeasure }}">
                            @endforeach

                        <button class="btn btn-success text-uppercase fsz-sm fw-600" type="submit" name="publish" id="btn-submit" value="submit"><i class="fa fa-send mR-10"></i>Publish</button>
                        {!! Form::close() !!}
                    </div>
                @endif
            </div>

            <div class="tab-pane" id="tab4" role="tabpanel" aria-labelledby="tab4">
				<div class="row">
					<div class="col-12">
						@if ($bpb->attachment_file)
							@if($bpb->created_by == Auth::user()->id || Auth::user()->id == 1 )
								<p class="text-center">
									<button title="Upload Attachment Dokumen BPB" class="btn btn-sm btn_upload_dokumen_bpb fw-bold text-primary" style="background-color: transparent;" data-toggle="modal" data-target="#modalUploadDokumenBpb" data-id="{{Hashids::encode($bpb->id)}}"><i class="ti-upload icon-lg"> Update Attachment Dokumen BPB</i></button> <br>
								</p>
							@endif
							<p class="text-center">
								<embed class="col align-self-center" src="{{ asset('storage'.$bpb->attachment_file) }}" width="600" height="800" alt="pdf" />
							</p>
							@else
							<p class="text-center">Belum melampirkan attachment dokumen BPB</p> <br>
							@if($bpb->created_by == Auth::user()->id || Auth::user()->id == 1)
								<p class="text-center">
									<button title="Upload Attachment Dokumen BPB" class="btn btn-sm btn_upload_dokumen_bpb fw-bold text-primary" style="background-color: transparent;" data-toggle="modal" data-target="#modalUploadDokumenBpb" data-id="{{Hashids::encode($bpb->id)}}"><i class="ti-upload icon-lg"> Upload Attachment</i></button>
								</p>
							@endif
						@endif
					</div>
				</div>
			</div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalUploadDokumenBpb" tabindex="-1" role="dialog" aria-labelledby="modalUploadDokumenBpb" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalUploadDokumenBpb">Upload Attachment Dokumen Bpb</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="detailmodalUploadDokumenBpb"></div>
            </div>
        </div>
    </div>
</div>

@stop


@section('js')

	<script  type='text/javascript'>

        $(document).on('click', '.btn_upload_dokumen_bpb', function() {
            var bpb_id = $(this).data('id');
            var url = "{{ route('logistic.bpb.get_dokumen_bpb', ['id' => ':id']) }}".replace(':id', bpb_id);
            debugger;
            $.ajax({
                url: url,
                method: 'GET',
                success: function(response) {
                    $('#detailmodalUploadDokumenBpb').html(`
                        <div style="display: flex; justify-content: flex-start; margin-bottom: 10px;">
                            <span style="font-weight: bold; margin-right: 10px; min-width: 120px;">NO BPB</span>
                            <span style="flex-grow: 1; text-align: left;">: ${response.doc_no}</span>
                        </div>
                        <form action="{{ route('logistic.bpb.upload_dokumen', ['id' => 'ID_PLACEHOLDER']) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input name="bpb_id" type="hidden" value="${response.id}" />
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

                    $('#detailmodalUploadDokumenBpb form').attr('action', function(i, val) {
                        return val.replace('ID_PLACEHOLDER', response.id);
                    });
                },
                error: function() {
                    $('#detailmodalUploadDokumenBpb').html('<p>Error loading BPB details.</p>');
                }
            });
        });


	    function printExternal(url) {
			var printWindow = window.open(url, 'Print', 'left=200, top=200, width=950, height=800, toolbar=0, resizable=0');
			printWindow.addEventListener('load', function() {
				printWindow.print();
			}, true);
        }

        $(document).on("click", "#btn-delete", function(e) {
            Swal.fire({
                title: 'Konfirmasi',
                text: 'Apakah anda yakin untuk menghapus Data?',
                type: 'error',
                showCancelButton: true,
                confirmButtonColor: 'null',
                cancelButtonColor: 'null',
                confirmButtonClass: 'btn btn-danger',
                cancelButtonClass: 'btn btn-primary',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal',
            }).then(res => {
                if (res.value) {
                    $('#btn-edit').attr('disabled', true);
                    $('#btn-delete').attr('disabled', true);
                    $('#btn-submit').attr('disabled', true);
                    $('#btn-delete').html('<span class="spinner"><i class="fa fa-spinner fa-spin"></i></span> Deleting Draft BPB');
                    Swal.fire({
                        title: 'Deleting Draft BPB',
                        html: 'Don\'t refresh or close your browser until process is completed',
                        showConfirmButton: false,
                        allowOutsideClick: false,
                        width: '700px',
                        onBeforeOpen: () => {
                            Swal.showLoading();
                        },
                    });
                    $("#bpb_delete").submit();
                }
            });
        });


        $(document).on("click", "#btn-submit", function(e) {
            Swal.fire({
                title: 'Konfirmasi',
                text: 'Apakah anda yakin melanjutkan ini?',
                type: 'question',
                showCancelButton: true,
                confirmButtonColor: 'null',
                cancelButtonColor: 'null',
                confirmButtonClass: 'btn btn-danger',
                cancelButtonClass: 'btn btn-primary',
                confirmButtonText: 'Ya, lanjut',
                cancelButtonText: 'Batal',
            }).then(res => {
                if (res.value) {
                    $('#btn-edit').attr('disabled', true);
                    $('#btn-delete').attr('disabled', true);
                    $('#btn-submit').attr('disabled', true);
                    $('#btn-submit').html('<span class="spinner"><i class="fa fa-spinner fa-spin"></i></span> Publishing BPB');
                    Swal.fire({
                        title: 'Publishing BPB',
                        html: 'Don\'t refresh or close your browser until process is completed',
                        showConfirmButton: false,
                        allowOutsideClick: false,
                        width: '700px',
                        onBeforeOpen: () => {
                            Swal.showLoading();
                        },
                    });
                    $("#bpb_publish").submit();
                }
            });
        });

	</script>
@stop
