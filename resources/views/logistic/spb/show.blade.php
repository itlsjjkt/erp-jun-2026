@extends('layouts.app')

@section('page-header')
    Surat Pengantar Barang
@stop

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('logistic.spb.index') }}">Surat Pengantar Barang</a></li>
        <li class="breadcrumb-item active" aria-current="page">Detail</li>
    </ol>
@endsection

@section('content')

	<div class="mB-40">
		<div class="bgc-white p-30 bd">
			@php
				use Illuminate\Support\Facades\Gate;
			@endphp
			@if(!Gate::allows('spb_monitoring'))
				<a class="btn btn-outline float-right" href="{{ route('logistic.spb.print',['id' => Hashids::encode($spb->id) ,'type' => 'print']) }}" title="Download Excel"><i class="fa fa-file-pdf-o text-danger icon-lg"></i></a>
			@endif
			<h6><a class="float-left" href="{{ route('logistic.spb.index') }}"><i class="ti-arrow-left mR-10"></i></a> {{ trans('Back') }}</h6>
        	<hr class='mB-30'>
			<div class="d-block">
				<ul class="nav nav-tabs" role="tablist">
					<li class="nav-item">
						<a class="nav-link active" data-toggle="tab" href="#tab1" role="tab">SPB</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" data-toggle="tab" href="#tab3" role="tab">Histori</a>
					</li>
                    <li class="nav-item">
                        @php
                            if ($spb->attachment_file) $badge = "<sup class='badge '><span class='bg-success rounded-circle text-success' style='padding:0 5px'></span></sup>";
                            else $badge = "";
                        @endphp
                        <a class="nav-link" data-toggle="tab" href="#tab4" role="tab">Attachment {!!$badge!!}</a>
                    </li>
				</ul>
			</div>

			<div class="tab-content mT-30" >
				<div class="tab-pane active" id="tab1" role="tabpanel" aria-labelledby="tab1">


					<table style="width:100%;margin-top:20px">
						<tr>
							<td class="border-0">
								<span style="font-weight:bold; font-size: 17px;text-decoration:underline"> SURAT PENGANTAR BARANG </span><br>
								<span style="font-weight:bold; font-size: 14px;">{{ $spb->doc_no }}</span> <br>
								<span style="font-weight:bold; font-size: 14px;">{{ ($spb->company) ? $spb->company->name : ''}}</span>
							</td>
						</tr>
					</table>
					<hr>

					<table style="width:100%;">
						<tr>
							<td class="border-0" style="width:33.3%">
								Kepada Yth, <br>
								@if($spb->type =="SPB Cargo")
									<strong>{{ ($spb->expedition) ? $spb->expedition->name : '' }}</strong><br>
									{{ ($spb->expedition) ? $spb->expedition->address : '' }}
									<table class="border-0">
										<tr>
											<td class="border-0 p-0">Up</td>
											<td class="border-0 p-0">: {{ $spb->delivered_pic }}  </td>
										</tr>
										<tr>
											<td class="border-0 p-0">Telp</td>
											<td class="border-0 p-0">: {{ $spb->delivered_pic_telp }} </td>
										</tr>
									</table>
								@elseif($spb->type =="SPB Hand Carry" )
								<strong>{{ ($spb->expedition) ? $spb->expedition->name : '' }}</strong><br>
									<table class="border-0">
										<tr>
											<td class="border-0 p-0">Telp</td>
											<td class="border-0 p-0">: {{ $spb->delivered_pic_telp }} </td>
										</tr>
									</table>
								@else
									<strong>{{  $supplier->name ?? '' }}</strong>
									<table class="border-0">
										<tr>
											<td class="border-0 p-0">Up</td>
											<td class="border-0 p-0">: {{ $supplier->supplierPIC ?? ''}}  </td>
										</tr>
										<tr>
											<td class="border-0 p-0">Telp</td>
											<td class="border-0 p-0">: {{ $supplier->supplierTelp ?? ''}} </td>
										</tr>
										<tr>
											<td class="border-0 p-0">Email</td>
											<td class="border-0 p-0">: {{ $supplier->supplierEmail ?? ''}} </td>
										</tr>
									</table>
								@endif
							</td>
							<td class="border-0" style="width:33.3%"></td>
							<td class="border-0" style="width:33.3%">
								<table style="width:100%">
									<tr>
										<td style="font-weight:bold; width:25%;">Jenis SPB </td>
										<td>: {{ $spb->type }} @if($spb->is_pickup == true)- Pick Up @endif</td>
									</tr>
									<tr>
										<td style="font-weight:bold;width:25%;">Tgl SPB </td>
										<td>: {{  date('d/m/Y', strtotime($spb->date_transaction)) }} </td>
									</tr>
									<tr>
										<td style="font-weight:bold;width:25%;">Estimasi Tiba </td>
										<td>: {{  $spb->estimate_receives ? date('d/m/Y', strtotime($spb->estimate_receives)) : ' -'}} </td>
									</tr>
									<tr>
										@if($spb->notes)
										<td style="font-weight:bold; vertical-align: top;">Catatan</td>
										<td style="max-width: 300px; word-wrap: break-word;"><div style="vertical-align: top;">:</div><div style="margin-top:-1.2rem !important;margin-left:0.5rem !important;width:250px !important;" class="ms-2"> {!! $spb->notes !!}</div></td>
										@else
										<td style="font-weight:bold;">Catatan </td>
										<td>: {!! $spb->notes !!} </td>
										@endif
									</tr>
								</table>
							</td>
						</tr>
					</table>
					@if($spb->is_pickup == true)
					<table class="border-0 mt-3">
						<tr>
							<td class="border-0" style="width:33.3%">
								Mohon pick up barang kami di:
								<br><strong>{{ $spb->pickup_from }}</strong>
								<table class="border-0">
									<tr>
										<td class="border-0 p-0">{{ $spb->pickup_address }}  </td>
									</tr>
									<tr>
										<td class="border-0 p-0">PIC :{{ ' '.$spb->pickup_pic_name. ' - ' .$spb->pickup_pic_telp }} </td>
									</tr>
								</table>
							</td>
							<td class="border-0" style="width:33.3%"></td>
							<td class="border-0" style="width:33.3%">
							</td>
						</tr>
					</table>
					@endif
					@if($spb->type =="SPB Vendor-Cargo")
						<table class="mt-3" style="width:100%;">
							<tr>
								<td class="border-0" style="width:33.3%">
									Mohon dikirimkan barang kami kepada : <br>
									<strong>{{ $spb->expedition->name?? '' }}</strong><br>
									{!! $spb->expeditionAddress !!}
									<table class="border-0">
										<tr>
											<td class="border-0 p-0">Up</td>
											<td class="border-0 p-0">: {{ $spb->delivered_pic }}  </td>
										</tr>
										<tr>
											<td class="border-0 p-0">Telp</td>
											<td class="border-0 p-0">: {{ $spb->delivered_pic_telp }} </td>
										</tr>
									</table>
								</td>
								<td class="border-0" style="width:33.3%"></td>
								<td class="border-0" style="width:33.3%">
									Dan diteruskan kepada :
									<br>
									<br>{!! $spb->address !!}
									<table class="border-0">
										<tr>
											<td class="border-0 p-0">Up</td>
											<td class="border-0 p-0">: {{ $spb->received_pic }}  </td>
										</tr>
										<tr>
											<td class="border-0 p-0">Telp</td>
											<td class="border-0 p-0">: {{ $spb->received_pic_telp }} </td>
										</tr>
									</table>
								</td>
							</tr>
						</table>
					@else
						<table class="border-0 mt-3">
							<tr>
								<td class="border-0" style="width:33.3%">
									Mohon dikirimkan barang kami kepada:
									<br>{!! $spb->address !!}
									<table class="border-0">
										<tr>
											<td class="border-0 p-0">Up</td>
											<td class="border-0 p-0">: {{ $spb->received_pic }}  </td>
										</tr>
										<tr>
											<td class="border-0 p-0">Telp</td>
											<td class="border-0 p-0">: {{ $spb->received_pic_telp }} </td>
										</tr>
									</table>
								</td>
								<td class="border-0" style="width:33.3%"></td>
								<td class="border-0" style="width:33.3%">
								</td>
							</tr>
						</table>
					@endif

					@if($spb->jalur_pengiriman)
						<div class="text-center fa-2x" style="border: 1px solid; width:200px; margin: 0 auto;">
							{{$spb->jalur_pengiriman}}
						</div>
					@endif

					@if($spb->receipt_type == 'non_bpb' && $spb->status == 3)
                    <div class="alert alert-success" style="margin-top:30px;" role="alert">
                        <p style="font-weight:bold;">DETAIL PENERIMAAN :</p>
                        <table>
                            <tbody>
                                <tr>
                                    <td>DITERIMA OLEH</td>
                                    <td> : </td>
                                    <td>{{ json_decode($spb->notes_receipt_non_bpb)->receipt_by ?? ' -' }}</td>
                                </tr>
                                <tr>
                                    <td>TANGGAL</td>
                                    <td> : </td>
                                    <td>{{ \Carbon\Carbon::parse(json_decode($spb->notes_receipt_non_bpb)->receipt_date)->format('d M Y') ?? ' -' }}</td>
                                </tr>
                                <tr>
                                    <td>DIBUAT OLEH</td>
                                    <td> : </td>
                                    <td>{{ getUserById(json_decode($spb->notes_receipt_non_bpb)->created_receipt_by) ?? ' -' }}</td>
                                </tr>
                                <tr>
                                    <td style="vertical-align:top;">CATATAN PENERIMAAN</td>
                                    <td style="vertical-align:top;"> : </td>
                                    <td style="vertical-align:top;">{!! json_decode($spb->notes_receipt_non_bpb)->receipt_notes !!}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    @endif
					
					<p class="mt-3">Adapun perincian barang yang kami kirim sebagai berikut:</p>

					<table class="table table-bordered">
						<thead>
							<tr>
								<th style="width:50px">No</th>
								<th>Nama Barang</th>
								<th class="text-center">QTY</th>
								<th class="text-center">DPM</th>
								<th class="text-center">PO </th>
								<th class="text-center">LPB </th>
								<th class="text-center">Kapal/Lokasi</th>
								<th class="text-center">Supplier</th>
								<th class="text-center" style="width:200px">Notes</th>
								<th class="text-center">QR</th>
							</tr>
						</thead>
						<tbody>
								@php
									$no = 1;
								@endphp
								@foreach ($spb_items as $data)
									<tr>
										<td>{{ $no }}</td>
										<td>
											[{{ $data->productCode }}] {{ $data->product }}
											<br>{!! $data->productPartNumber != NULL ? '<small> PN: '.$data->productPartNumber.'</small>' : 'PN: -' !!}
											{!! $data->productBrand != NULL ? '<small>Brand: '.$data->productBrand.'</small>' : 'Brand: -' !!}
											<br>{!! $data->specification !!}
											<br>@if($data->status_insurance != 0)<small style="color:red">* Item Sudah Di Asuransi</small>@endif
										</td>
										<td class="text-center">{{ $data->qtyKoli }} {{ $data->measure }}
                                            @if(Auth::user()->id == 1) <br>QTY BPB:{{getQtyAllBpbItemBySpbKolis($data->idKoli)}} @endif
										</td>
										<td class="text-center">{{ $data->noDPM  }}</td>
										<td>{{ $data->noPO }}</td>
										<td>{{ $data->noLPB }}</td>
										<td>{{ $data->locationnn }}</td>
										<td>{{ $data->supplier }}</td>
										<td>{!! $data->annotation !!}</td>
										<td class="text-center">{!! QrCode::size(80)->generate($data->uuid); !!}</td>
									</tr>
									@php
										$no++;
									@endphp
								@endforeach
						</tbody>
						<tfoot>
                            <tr>
                                <td colspan="10">
                                    @if($totalPrice >= 250000000)
                                        <p style="font-weight:bold; color:red; width:100%; display:flex; justify-content:flex-end;">* Jumlah Harga Item lebih dari Rp.250.000.000 [Segera Buatkan Asuransi] </p>
                                    @endif
                                </td>
                            </tr>
                        </tfoot>
					</table>
					<hr>

					@if($spb->status==0)
						<div class="mt-4 btn-group">

							<a href="{{ route('logistic.spb.edit',Hashids::encode($spb->id)) }}" class="btn btn-light mr-1 text-uppercase fsz-sm fw-600">Edit Draft</a>

							<form class="delete" action="{{ route('logistic.spb.delete', ['id' => $spb->id]) }}" method='POST'>
								{{ csrf_field() }}
								<button class='btn btn-danger mr-1 text-uppercase fsz-sm fw-600 '>Hapus Draft</button>
							</form>

							{!! Form::model($spb, [
									'action' => ['Logistic\SpbController@publish', $spb->id],
									'method' => 'POST',
									'files' => true
								])
							!!}
								<input type="hidden" name="tipe" value="{{ $spb->type }}">
								<input class="btn btn-success text-uppercase fsz-sm fw-600"  type="submit" name="publish" id="btn-submit" value="Publish">
							{!! Form::close() !!}
						</div>
					@endif

				</div>

				<div class="tab-pane" id="tab3" role="tabpanel" aria-labelledby="tab3">
					<div class="timeline">
						<div class="timeline__group">
							<?php foreach($spb_history as $val){?>
								<div class="timeline__box">
									<div class="timeline__date"></div>
									<div class="timeline__post">
										<div class="timeline__content">
											<?php
												$employeeName = $val->employee;
												echo "<span>". date('d/m/Y H:i A',strtotime($val->created_at)). "</span><br>";
												echo "<strong>".ucwords(strtolower($employeeName))."</strong> ";
												if($val->jenis=='insert'){
													echo  "melakukan pengajuan SPB ";
												}elseif($val->jenis=='draft'){
													echo  "melakukan pengajuan SPB dengan status Draft</p>";
												}elseif($val->jenis=='set_done'){
													echo  "melakukan set selesai SPB</p>";
												}elseif($val->jenis=='reversal'){
													echo  "melakukan reversal SPB</p>";
                                                }elseif($val->jenis=='update_dokumen'){
												echo  "melakukan update attachment dokumen SPB</p>";
											    }else{
													echo  "melakukan publish SPB ";
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
                            @if ($spb->attachment_file)
                                @if($spb->created_by == Auth::user()->id || Auth::user()->id == 1 )
                                    <p class="text-center">
                                        <button title="Upload Attachment Dokumen SPB" class="btn btn-sm btn_upload_dokumen_spb fw-bold text-primary" style="background-color: transparent;" data-toggle="modal" data-target="#modalUploadDokumenSpb" data-id="{{Hashids::encode($spb->id)}}"><i class="ti-upload icon-lg"> Update Attachment Dokumen SPB</i></button> <br>
                                    </p>
                                @endif
                                <p class="text-center">
                                    <embed class="col align-self-center" src="{{ asset('storage'.$spb->attachment_file) }}" width="600" height="800" alt="pdf" />
                                </p>
                                @else
                                <p class="text-center">Belum melampirkan attachment dokumen SPB</p> <br>
                                @if($spb->created_by == Auth::user()->id || Auth::user()->id == 1)
                                    <p class="text-center">
                                        <button title="Upload Attachment Dokumen SPB" class="btn btn-sm btn_upload_dokumen_spb fw-bold text-primary" style="background-color: transparent;" data-toggle="modal" data-target="#modalUploadDokumenSpb" data-id="{{Hashids::encode($spb->id)}}"><i class="ti-upload icon-lg"> Upload Attachment</i></button>
                                    </p>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>

			</div>
		</div>
	</div>

    <div class="modal fade" id="modalUploadDokumenSpb" tabindex="-1" role="dialog" aria-labelledby="modalUploadDokumenSpb" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalUploadDokumenSpb">Upload Attachment Dokumen SPB</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="detailmodalUploadDokumenSpb"></div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')

    <script  type='text/javascript'>

        $(document).on('click', '.btn_upload_dokumen_spb', function() {
            var spb_id = $(this).data('id');
            var url = "{{ route('logistic.spb.get_dokumen_spb', ['id' => ':id']) }}".replace(':id', spb_id);
            debugger;
            $.ajax({
                url: url,
                method: 'GET',
                success: function(response) {
                    $('#detailmodalUploadDokumenSpb').html(`
                        <div style="display: flex; justify-content: flex-start; margin-bottom: 10px;">
                            <span style="font-weight: bold; margin-right: 10px; min-width: 120px;">NO SPB</span>
                            <span style="flex-grow: 1; text-align: left;">: ${response.doc_no}</span>
                        </div>
                        <form action="{{ route('logistic.spb.upload_dokumen', ['id' => 'ID_PLACEHOLDER']) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input name="spb_id" type="hidden" value="${response.id}" />
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

                    $('#detailmodalUploadDokumenSpb form').attr('action', function(i, val) {
                        return val.replace('ID_PLACEHOLDER', response.id);
                    });
                },
                error: function() {
                    $('#detailmodalUploadDokumenSpb').html('<p>Error loading SPB details.</p>');
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
