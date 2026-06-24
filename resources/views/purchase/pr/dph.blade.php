@extends('layouts.app')

@section('page-header')
	View <small>{{ $pr->doc_no }}</small>
@stop

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
		<div class="row mb-1 justify-content-end">
			<div class="col-sm-6">
				<a href="{{ route('purchasing.pr') }}" class="btn btn-outline btn-lg">
                    <i class="ti-arrow-left"></i> Kembali
                </a>
			</div>
			<div class="col-sm-6">
				<a class="btn btn-outline float-right" href="#" title="Print Data" onclick='printExternal("/purchasing/pr_print/{{ Hashids::encode($pr->id) }}/print")'><i class="ti-printer icon-lg"></i></a>
			</div>
		</div>
        <hr>


		<div class="d-block mT-30">
			<ul class="nav nav-tabs" role="tablist">
				<li class="nav-item">
					<a class="nav-link active" data-toggle="tab" href="#tab1" role="tab">Form DPH</a>
				</li>
				<li class="nav-item">
					<a class="nav-link" data-toggle="tab" href="#tab2" role="tab">Histori DPH</a>
				</li>
			</ul>
		</div>

		<div class="tab-content mT-30" >
			<div class="tab-pane active" id="tab1" role="tabpanel" aria-labelledby="tab1">
				<div class="row mt-5">
					<div class="col-sm-6">
						<div class="row">
							<label class="col-sm-3">No. PR</label>
							<div class="col-sm-8">: {{ $pr->doc_no }}</div>
						</div>
						<div class="row">
							<label class="col-sm-3">No. DPM </label>
							<div class="col-sm-8">: {{ $pr->dpm_no }}</div>
						</div>
						<div class="row">
							<label class="col-sm-3">Kapal/Departemen </label>
							<div class="col-sm-8">: {{ $pr->department }}</div>
						</div>
					</div>
					<div class="col-sm-6">
						<div class="row">
							<label class="col-sm-3">Dibuat Oleh</label>
							<div class="col-sm-8">: {{ $pr->created }}</div>
						</div>
						<div class="row">
							<label class="col-sm-3">Dibuat Tanggal</label>
							<div class="col-sm-8">: {{ idDate($pr->created_at) }}</div>
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
						<th>Tgl Dibutuhkan</th>
						<th></th>
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
											{{ $item->product }} {!! $item->productPartNumber != NULL ? '<br> PN/Spec: '.$item->productPartNumber : '' !!}
											<br>{{ $item->productBrand != NULL ? 'Brand: '.$item->productBrand : '' }}
										</td>
										<td>
											{{ $item->notes }} 
										</td>
										@if ($item->po_status == 2)
											<td>{{ $item->qty_parsial }} {{ $item->measure }}</td>
										@else
											<td>{{ $item->qty}} {{ $item->measure }}</td>
										@endif

										<td></td>
										<td>{{ $item->flag == "normal" ? "Normal" : "Urgent" }} </td>
										<td>{{ date('d/m/Y',strtotime( $item->needed_on_date)) }}</td>
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
				
				{!! Form::open(['method' => 'POST', 'route' => ['purchasing.dph.store'], 'id' => 'form-pr']) !!}
					<input name="pr_id" type="hidden" value="{{ $pr->id }}">

					<div class="form-group row mt-5">
						<label class="col-sm-3">Nama Supplier <span class="text-danger">*</span></label>
						<div class="col-sm-4">
							<select name="supplier_id" class="select2 form-control supplier" required></select>
						</div>
					</div>
					<div class="form-group row">
						<label class="col-sm-3">Penawaran yang didapat<span class="text-danger">*</span></label>
						<div class="col-sm-3">
							{!! Form::number('total', old('total'), ['class' => 'form-control', 'placeholder' => '', 'value' => 0,'min' => '0']) !!}
						</div>
					</div>
					<div class="mt-4">
						<a href="{{ route('purchasing.pr') }}" class="btn btn-light text-uppercase fsz-sm fw-600">{{ trans('Cancel') }}</a>
						<button class="btn btn-danger text-uppercase fsz-sm fw-600" type="submit" id="btn-submit-po" >SUBMIT</button>
					</div>

				{!! Form::close() !!}
			</div>

			<div class="tab-pane" id="tab2" role="tabpanel" aria-labelledby="tab2">
				<div class="row">
					<div class="col-12">
						<table class="table table-bordered">
							<thead>
								<th>No</th>
								<th>Supplier</th>
								<th>Total Penawaran</th>
								<th>Dibuat Tanggal</th>
							</thead>
							<tbody>
								@php
									$no = 1;
								@endphp
								@forelse ($dph as $item)
									<tr>
										<td>{{ $no }}</td>
										<td>{{ $item->supplier->name }}</td>
										<td>{{ format_number($item->total) }}</td>
										<td>{{ idDate($item->created_at) }}</td>
									</tr>
								@empty
									<tr>
										<td colspan="4">Belum ada DPH</td>
									</tr>
								@endforelse
							</tbody>
						</table>
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


<div class="modal fade" tabindex="-1" role="dialog" id="modalBlock">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<form id="blockForm" action="" method="post">
				@csrf
				<div class="modal-header">
					<h5 class="modal-title">Alasan Penutupan PR</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<textarea name="reason" class="form-control" required></textarea>
				</div>
				<div class="modal-footer">
					<input type="hidden" name="pr_id" id="pr_id">
					<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
					<button type="submit" class="btn btn-danger">Submit</button>
				</div>
			</form>
		</div>
	</div>
</div>

@stop


@section('js')


<script  type='text/javascript'>
    $(document).ready(function() {

		var $supplier = $(".supplier");

        $supplier.select2({
            placeholder: 'Cari Supplier dengan mengetik 3 huruf...',
            ajax: {
                url:"{{ route('purchasing.get_supplier') }}",
                dataType: 'json',
                delay: 250,
                processResults: function (data) {
                    return {
                        results:  $.map(data, function (item) {
                            return {
                                text: item.name,
                                id: item.id,
                            }
                        })
                    };
                },
                cache: true
            }
        });

	$('.modalMd').off('click').on('click', function () {
			$('#modalMdContent').load($(this).attr('value'));
		});
	});

	$('.modalMdPO').off('click').on('click', function () {
		$('#modalMdContentPO').load($(this).attr('value'));
	});

	$('#modalBlock').on('show.bs.modal', function (event) {
		var button = $(event.relatedTarget);
		var id = button.data('id');
		$('#blockForm').attr("action", "{{ route('purchasing.pr.close') }}");
		$('#pr_id').attr("value",id);
	});

	$(document).on('click', "form.tutup button", function(e) {
		var _this = $(this);
		e.preventDefault();
		Swal.fire({
			title: 'Konfirmasi', // Opération Dangereuse
			text: 'Apakah anda yakin untuk menutup PR ini?', // Êtes-vous sûr de continuer ?
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
