@extends('layouts.app')

@section('page-header')
    Purchase Requisition
@endsection

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('purchasing.pr') }}">Purchase Requisition</a></li>
        <li class="breadcrumb-item active" aria-current="page">Assign PR</li>
    </ol>
@endsection

@section('content')

<div class="mB-40">
	<div class="bgc-white p-30 bd">
		<div class="row mb-1 justify-content-end">
			<div class="col-sm-12">
				<a class="btn btn-outline float-right" href="#" title="Print Data" onclick='printExternal("/purchasing/pr_print/{{ Hashids::encode($pr->id) }}/print")'><i class="ti-printer icon-lg"></i></a>
			</div>
		</div>

		<div class="d-block">
			<ul class="nav nav-tabs" role="tablist">
				<li class="nav-item">
					<a class="nav-link active" data-toggle="tab" href="#tab1" role="tab">Purchase Requisition (PR)</a>
				</li>
				<li class="nav-item">
					<?php
					if ($pr->PurchaseRequest->mr_file)
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
							<div class="col-sm-8">: {{ $pr->department->name ?? '-' }}</div>
						</div>
						<div class="row">
							<label class="col-sm-3">Project </label>
							<div class="col-sm-8">: {{ $pr->project->name ?? ' -' }}</div>
						</div>
					</div>
					<div class="col-sm-6">
						<div class="row">
							<label class="col-sm-3">Deskripsi DPM</label>
							<div class="col-sm-8">: {{ $pr->PurchaseRequest->description }}</div>
						</div>
						<div class="row">
							<label class="col-sm-3">Dibuat Oleh</label>
							<div class="col-sm-8">: {{ $pr->PurchaseRequest->creator->name }}</div>
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
                <hr>
                {!! Form::open(['method' => 'POST', 'route' => ['purchasing.pr.reassign'], 'id' => 'form-pr']) !!}
                    <input name="id" type="hidden" value="{{ $pr->id }}">

                    <div class="row">
                        <div class="col-lg-6">
                            <div class="row mt-4">
                                <label class="col-lg-3">Purchaser</label>
                                <div class="col-lg-6">
                                    {!! Form::select('assigned_id', $purchaser, '', ['class' => 'form-control select2','required' => 'required']) !!}
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="row mt-4">
                                <label class="col-lg-3">Tipe PR</label>
                                <div class="col-lg-6">
                                    {!! Form::select('type', $type, $pr->type, ['class' => 'form-control select2','required' => 'required']) !!}
                                </div>
                            </div>
                        </div>
                    </div>

					<div class="alert alert-info my-4">
						<p class="m-0">Item PR dapat dilakukan Re-assing Purchaser selama belum dibuatkan PO. Silahkan Checklist Item PR yang akan di Re-assign</p>
					 </div>
                    <table class="table table-bordered">
                        <thead>
							<th style="width:50px">
								<input class="magic-checkbox" name="checkedAll" id="checkedAll" type="checkbox"> <label for="checkedAll">ALL</label>
							</th>
                            <th style="width:300px">Nama Barang</th>
                            <th>Catatan</th>
                            <th style="min-width:100px">QTY</th>
                            <th>Flag </th>
                            <th>Purchaser</th>
                            <th>Tgl Dibutuhkan</th>
                        </thead>
                        <tbody>
                            @php
                                $sortedItems = $pr->reassignItem->sortBy(fn($item) => $item->product->code.'-'.$item->product->name);
                            @endphp

                            @if (count($sortedItems) > 0)
                                @php
                                    $no = 1
                                @endphp
                                @foreach ($sortedItems as $item)
                                    <tr data-entry-id="{{ $item->id }}">
                                        <input name="pr_item_id[]" type="hidden" value="{{ $item->id }}">
                                        <td>
                                            <input type="checkbox" name="isPR[]" class="checkSingle magic-checkbox" value="{{ $item->id }}" id="checkbox_{{ $item->id }}"><label for="checkbox_{{ $item->id }}"></label>
                                        </td>
                                        <td>
                                            [{{ $item->product->code }}]
                                            {{ $item->product->name }} {!! ($item->product->part_number) ? '<br> PN/Spec: '.$item->product->part_number : '' !!}
                                            <br>{{ ($item->product->brand) ? 'Brand: '.$item->product->brand->name : '' }}
                                        </td>
                                        <td>
                                            {!! $item->notes !!}
                                        </td>
                                        <td>
                                            @if ($item->po_status == 2)
                                                {{ $item->qty_parsial }}
                                            @else
                                                {{ $item->qty }}
                                            @endif
                                            {{ $item->measure }}
                                        </td>
                                        <td>{{ $item->flag == "normal" ? "Normal" : "Urgent" }}</td>
                                        <td>{{ ($item->purchaser->name) ? $item->purchaser->name : '-' }}</td>
                                        <td>{{ date('d/m/Y', strtotime($item->needed_on_date)) }}</td>
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

                    <div class="mt-4">
                        <a href="{{ route('purchasing.pr') }}" class="btn btn-light text-uppercase fsz-sm fw-600">{{ trans('Cancel') }}</a>
                        <input class="btn btn-danger text-uppercase fsz-sm fw-600" type="submit" name="publish" id="btn-submit" value="Publish">
                    </div>
                {!! Form::close() !!}

			</div>
			<div class="tab-pane" id="tab2" role="tabpanel" aria-labelledby="tab2">
				<div class="row">
					<div class="col-12">
						@if ($pr->PurchaseRequest->mr_file)
							<embed class="col align-self-center" src="{{ asset('storage'.$pr->PurchaseRequest->mr_file) }}" width="600" height="500" alt="pdf" />
						@else
							<p class="text-center">File MR tidak dilampirkan dalam DPM</p>
						@endif
					</div>
				</div>
			</div>
		</div>

	</div>
</div>


@stop


@section('js')


<script type='text/javascript'>
    $(document).ready(function() {

		$('.select2').select2().on('change', function() {
            $(this).valid();
        });

        $("#checkedAll").change(function(){
            if(this.checked){
                $(".checkSingle").each(function(){
                    this.checked=true;
                })
            }else{
                $(".checkSingle").each(function(){
                    this.checked=false;
                })
            }
        });

        $(".checkSingle").click(function () {
            if ($(this).is(":checked")){
            var isAllChecked = 0;
            $(".checkSingle").each(function(){
                if(!this.checked)
                isAllChecked = 1;
            })
            if(isAllChecked == 0){
                 $("#checkedAll").prop("checked", true);
            }
            }else {
                $("#checkedAll").prop("checked", false);
            }
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


		$(document).on("click", "#btn-submit", function(e) {
            var checkbox= document.querySelector('input[name="isPR[]"]:checked');
            if(!checkbox) {
                Swal.fire(
                    'Informasi',
                    'Minimal Checklist 1 Item',
                    'warning'
                );
                return false;
            }
        });


	});


	function valthisform()
    {
        var checkboxs=document.getElementsByName("pr_item");
        var okay=false;
        for(var i=0,l=checkboxs.length;i<l;i++)
        {
            if(checkboxs[i].checked)
            {
                okay=true;
                break;
            }
        }
        if(okay)return true;
        else alert("Please check a checkbox");
    }

    function printExternal(url) {
        var printWindow = window.open(url, 'Print', 'left=200, top=200, width=950, height=800, toolbar=0, resizable=0');
        printWindow.addEventListener('load', function() {
            printWindow.print();
        }, true);
    }
</script>
@stop
