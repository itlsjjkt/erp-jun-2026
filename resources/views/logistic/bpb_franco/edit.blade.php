@extends('layouts.app')

@section('page-header')
    Bukti Penerimaan Barang Lokal
@stop

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('logistic.bpb_franco.index') }}">Bukti Penerimaan Barang Lokalg</a></li>
        <li class="breadcrumb-item active" aria-current="page">Edit</li>
    </ol>
@endsection


@section('content')

<div class="mB-40">
		{!! Form::model($bpb, [
				'action' => ['Logistic\BpbFrancoController@update', $bpb->id],
				'method' => 'put',
				'class' => 'form-horizontal mt-3',
				'files' => true,
				'id' => 'bpb_update',
			])
		!!}
        <input type="hidden" name="po_id" value="{{ $bpb->po_id }}">

            <div class="bgc-white p-30 bd">
                <h6><a class="float-left" href="{{ route('logistic.bpb_franco.index') }}"><i class="ti-arrow-left mR-10"></i></a> {{ trans('Back') }}</h6>
                <hr class='mB-30'>

                <div class="row mt-5">
                    <div class="col-sm-6">
                        <div class="row">
                            <label class="col-sm-3">No. PO </label>
                            <div class="col-sm-8">: {{ $bpb->purchaseOrder->doc_no }} </div>
                        </div>
                        <div class="row">
                            <label class="col-sm-3">Kapal/Departemen </label>
                            <div class="col-sm-8">: {{ $bpb->purchaseOrder->purchaseRequisition->department->name }}</div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="row">
                            <label class="col-sm-3">Project </label>
                            <div class="col-sm-8">: {{ $bpb->purchaseOrder->purchaseRequisition->project->name }}</div>
                        </div>
                        <div class="row">
                            <label class="col-sm-3">Supplier</label>
                            <div class="col-sm-8">: {{ $bpb->purchaseOrder->supplier->name }}</div>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="row mt-5">
                    <div class="col-lg-6">
                        <div class="form-group row">
                            <label class="col-sm-3">Penerima <span class="text-danger">*</span></label>
                            <div class="col-sm-8">
                                {!! Form::text('received_by', old('received_by'), ['class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group row">
                            <label class="col-sm-3">Catatan </label>
                            <div class="col-sm-9">
                                {!! Form::textarea('notes', old('notes'), ['class' => 'form-control', 'placeholder' => '','rows' => 2]) !!}
                            </div>
                        </div>
                    </div>
                </div>
            <div class="alert alert-info mT-30">
                Dengan melakukan Publish BPB maka QTY Produk akan otomatis masuk ke dalam Inventory, jika terdapat Data Inventory akan mulakukan update Stock, jika belum terdapat pada data Inventory akan insert Data<br>
            </div>
            <table class="table table-bordered">
			<thead>
				<tr>
					<th style="width:50px">No</th>
					<th>Nama Barang</th>
					<th>Spesifikasi</th>
					<th class="text-center" >QTY</th>
					<th class="text-center">SATUAN</th>
					<th style="min-width:100px" class="text-center">QTY <br> Diterima</th>
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
								<input name="po_item_id[]" type="hidden" value="{{ $item->idPO }}">
								<input name="pr_item_id[]" type="hidden" value="{{ $item->pr_item_id }}">
								<input name="price[]" type="hidden" value="{{ $item->price }}">
								<input name="discount[]" type="hidden" value="{{ $item->price_discount }}">
                                <input name="conversion[]" type="hidden" value="{{ $item->productConversion }}">
                                <input name="measure_id[]" type="hidden" value="{{ $item->productMeasure }}">
                                <input name="product_id[]" type="hidden" value="{{ $item->product_id }}" >
                                <input name="location_id[]" type="hidden" value="{{ $item->location_id }}" >
								<input name="qty[]" type="hidden" value="{{ $item->qtyPO }}" >

								[{{ $item->productCode }}] {{ $item->product }} {!! $item->productPartNumber != NULL ? '<br><small> PN: '.$item->productPartNumber.'</small>' : '' !!}
                                {!! $item->productBrand != NULL ? '<small>Brand: '.$item->productBrand.'</small>' : '' !!}
							</td>
							<td>{!! $item->specification !!}</td>
							<td class="text-center">
								@if ($item->lpb_status == 2)
								    {{$item->qty_parsial}}
									<input name="qty_po[]" type="hidden" value="{{$item->qty_parsial}}"  id='qty_po_{{$item->idPO}}'>
								@else
									{{$item->qtyPO}}
									<input name="qty_po[]" type="hidden" value="{{$item->qtyPO}}"  id='qty_po_{{$item->idPO}}'>
								@endif
							</td>
							<td>{{ $item->measure }}</td>
							<td><input type='number' name="qty_bpb[]" value="{{ $item->qty }}" class="form-control" id='qty_bpb_{{$item->idPO}}' required min="1" oninput="this.value = Math.abs(this.value)" ></input></td>
                            <td><textarea name="description[]" class="form-control">{{ $item->description }}</textarea></td>
						</tr>
					@php
						$no++;
					@endphp
					@endforeach
			</tbody>
		</table>

		</div>


        <div class="mt-4">
            <a href="{{ route('logistic.bpb_franco.index') }}" id="btn-cancel" class="btn btn-light mr-1 text-uppercase fsz-sm fw-600">{{ trans('Cancel') }}</a>
            <button class="btn btn-info text-uppercase fsz-sm fw-600" type="submit" name="save" value="submit" id="btn-draft"><i class="fa fa-save mR-10"></i>Save as Draft</button>
            @if($bpb->status==0)
                <input type="hidden" value="0" name="status">
                <button class="btn btn-danger text-uppercase fsz-sm fw-600 float-right"  type="submit" name="publish" id="btn-submit" value="submit"><i class="fa fa-send mR-10"></i>Publish BPB</button>
            @endif
        </div>

	{!! Form::close() !!}
</div>

@stop


@section('js')

<script  type='text/javascript'>
	$(document).ready(function() {

		@foreach ($bpb_items as $item)
         $('#qty_bpb_{{$item->idPO}}').on('keyup', function(e) {
                var qty_po  = $('#qty_po_{{$item->idPO}}').val();
                var qty_bpb = $('#qty_bpb_{{$item->idPO}}').val();
                if(parseInt(qty_bpb) > parseInt(qty_po)){
                    e.preventDefault();
                    Swal.fire(
                    'Peringatan!',
                        'QTY BPB tidak boleh melebihi QTY SPB',
                        'warning'
                    );
                    $('#qty_bpb_{{$item->idPO}}').val('');
                }
            });

        @endforeach

		$(document).on("click", "#btn-submit", function(e) {
            $('input[name="status"]').val('1');
            $('#btn-cancel').attr('disabled', true);
            $('#btn-draft').attr('disabled', true);
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
            $("#bpb_update").submit();
        });

        $(document).on("click", "#btn-draft", function(e) {
            $('input[name="status"]').val('0');
            $('#btn-cancel').attr('disabled', true);
            $('#btn-draft').attr('disabled', true);
            $('#btn-submit').attr('disabled', true);
            $('#btn-draft').html('<span class="spinner"><i class="fa fa-spinner fa-spin"></i></span> Saving BPB as Draft');
            Swal.fire({
                title: 'Saving BPB as Draft',
                html: 'Don\'t refresh or close your browser until process is completed',
                showConfirmButton: false,
                allowOutsideClick: false,
                width: '700px',
                onBeforeOpen: () => {
                    Swal.showLoading();
                },
            });
            $("#bpb_update").submit();
        });

    });
    </script>
@stop

