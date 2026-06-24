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

<div class="mB-40 pB-50 bgc-white">
	<div class="bgc-white p-30">
		<div class="tab-content mT-30">
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
							<div class="col-sm-8">: {{ $pr->project->name ?? $pr->PurchaseRequest->project->name ?? ' -'  }}</div>
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
				{!! Form::open(['method' => 'POST', 'route' => ['purchasing.pr.closepr', $pr->id], 'id' => 'closeForm']) !!}
				<table class="table table-bordered">
					<thead>
						<th style="width:50px !important;">
                            <input class="magic-checkbox" name="checkedAll" id="checkedAll" type="checkbox"> <label for="checkedAll"></label>
                        </th>
						<th style="width:300px; text-align:center">Nama Barang</th>
						<th style="width:150px; text-align:center">QTY</th>
						<th style="width:150px; text-align:center">Purchaser</th>
						<th style="width:150px; text-align:center">Status</th>
						<th style="text-align:center">Alasan Tutup PR</th>
					</thead>
					<tbody>
						@if (count($pr_items) > 0)
							@php
								$no = 1
							@endphp
							@foreach ($pr_items as $item)
								<tr data-entry-id="{{ $item->id }}">
									<td style="text-align: center;">
										<input class=" magic-checkbox checkSingle checkbox-item" name="prItemId[]" id="checkbox_{{$item->id}}" type="checkbox" value="{{ $item->id }}" data-id="{{ $item->id }}">
                                        <label for="checkbox_{{ $item->id }}"></label>
									</td>
									<td>
										[{{ $item->productCode }}]
										{{ $item->product }} {!! $item->productPartNumber != NULL ? '<br> PN/Spec: '.$item->productPartNumber : '' !!}
										<br>{{ $item->productBrand != NULL ? 'Brand: '.$item->productBrand : '' }}
									</td>
									@if ($item->po_status == 2)
										<td style="text-align:center;">{{ $item->qty_parsial }} {{ $item->measure }}</td>
									@else
										<td style="text-align:center;">{{ $item->qty}} {{ $item->measure }}</td>
									@endif
									<td style="text-align:center;">{!! getUserById($item->assigned_id) ?? ' -' !!} </td>
									<td style="text-align:center;">{!! getStatusItemPR($item->pr_status, $item->po_status, $item->qty_parsial,$pr->type) !!} </td>
									<td style="width:300px">
										<textarea class="textarea-item" name="prItemTextarea[{{ $item->id }}]" data-id="{{ $item->id }}" style="width:100%" rows="3"></textarea>
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
				<button class="btn btn-danger text-uppercase fsz-sm fw-600 float-right mT-10" id="btn-close-pr" type="submit">Closed PR Items</button>
				{!! Form::close() !!}
			</div>

		</div>

	</div>
</div>
@stop


@section('js')
<script type='text/javascript'>
    $("#checkedAll").change(function(){
        $(".checkSingle").prop("checked", this.checked);
    });

    $(".checkSingle").change(function () {
        var allChecked = $(".checkSingle").length === $(".checkSingle:checked").length;
        $("#checkedAll").prop("checked", allChecked);
    });

    $(document).on('click', "#btn-close-pr", function(e) {
        e.preventDefault();
        var form = $("#closeForm");

        form.validate({
            rules: {
                count_form: "required",
            },
            onfocusout: function(element) {
                if (!this.checkable(element) && (element.name in this.submitted || !this.optional(element))) {
                    this.element(element);
                }
            },
            invalidHandler: function(form, validator) {
                var errors = validator.numberOfInvalids();
                if (errors) {
                    validator.errorList[0].element.focus();
                }
            },
            submitHandler: function(form) {
                var valid = true;

                $(".checkSingle:checked").each(function() {
                    var itemId = $(this).val();
                    var textarea = $("textarea[name='prItemTextarea[" + itemId + "]']");

                    if (textarea.val().trim() === "") {
                        valid = false;
                        textarea.css("border-color", "red");
                    } else {
                        textarea.css("border-color", "");
                    }
                });

                if (!valid) {
                    Swal.fire(
                        'Informasi',
                        'Pastikan semua Item PR yang dipilih memiliki alasan tutup PR.',
                        'warning'
                    );
                    return false;
                }

                $(".checkSingle:not(:checked)").each(function() {
                    var itemId = $(this).val();
                    $("textarea[name='prItemTextarea[" + itemId + "]']").remove();
                });

                form.submit();
            }
        });

        if (!form.find('input[name="prItemId[]"]:checked').length) {
            Swal.fire(
                'Informasi',
                'Minimal Checklist 1 Item untuk close Item PR',
                'warning'
            );
            return false;
        }

        Swal.fire({
            title: 'Konfirmasi',
            text: 'Apakah anda yakin melanjutkan ini?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#aaa',
            confirmButtonText: 'Ya, lanjut',
            cancelButtonText: 'Batal',
        }).then(result => {
            if (result.value) {
                form.submit();
            }
        });
    });
</script>

@stop
