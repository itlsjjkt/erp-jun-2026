@extends('layouts.app')

@section('page-header')
    Laporan Penerimaan Barang 
@stop

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('logistic.lpb.index') }}">Laporan Penerimaan Barang</a></li>
        <li class="breadcrumb-item active" aria-current="page">Revisi</li>
    </ol>
@endsection


@section('content')

<div class="mB-40">
		{!! Form::model($lpb, [
				'action' => ['Logistic\LpbController@revisi', $lpb->id],
				'method' => 'post', 
				'class' => 'form-horizontal mt-3',
				'files' => true
			])
		!!}
        <input name="po_id" type="hidden" value="{{ $lpb->po_id }}">
        <input type="hidden" name="location_id" value="{{$location->id}}">

		<div class="bgc-white p-30 bd">
            <h6>Revisi: {{ $lpb->doc_no }}</h6>
            <hr class='mB-30'>
  
            <div class="form-group row">
                <label class="col-sm-2 ">Penerima <span class="text-danger">*</span></label>
                <div class="col-sm-5">
                    <input type="text" class="form-control" name="received_by" value="{{ $lpb->received_by }}" required>
                </div>
            </div>
            <table class="table table-bordered mt-2">
                <thead>
                    <tr>
                        <th rowspan="2" style="width:50px">No</th>
                        <th rowspan="2" style="width:300px">Nama Barang</th>
                        <th rowspan="2" style="width:300px">Spesifikasi</th>
                        <th colspan="2" class="text-center">Jumlah </th>
                        <th rowspan="2" >Satuan</th>
                        <th rowspan="2" style="width:300px">Catatan</th>
                    </tr>
                    <tr>
                        <th style="width:150px" class="text-center">Dipesan</th>
                        <th style="width:150px" class="text-center">Diterima</th>
                    </tr>
                </thead>
                <tbody class="item_form">
                    @if (count($lpb_items) > 0)
                        @php
                            $no = 1
                        @endphp
                        @foreach ($lpb_items as $item)
                            <tr class="product_{{$item->id}}">
                                <input name="po_item_id[]" type="hidden" value="{{ $item->po_item_id }}">
                                <input name="pr_item_id[]" type="hidden" value="{{ $item->pr_item_id }}">
                                <td>{{ $no }}</td>
                                <td>
                                    {{ $item->product }} {!! $item->productPartNumber != NULL ? '<br> PN/Spec: '.$item->productPartNumber : '' !!} 
                                    <input name="product_id[]" type="hidden" value="{{ $item->product_id }}" >
                                </td>
                                <td>
                                    {!! $item->specification !!} <br>
                                    <small>{{ $item->productBrand != NULL ? 'Brand: '.$item->productBrand : '' }} </small>
                                </td>
                                <td class="text-center">
                                    {{ $item->qtyPO }}
                                    @if ($item->lpb_status == 2)
                                    <br>Belum diterima: {{$item->qty_parsial+$item->qty}}
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if ($item->lpb_status == 2)
                                        <input name="qty_po[]" type="hidden" value="{{ $item->qty_parsial + $item->qty }}"  id='qty_po_{{$item->id}}'>
                                    @else
                                        <input name="qty_po[]" type="hidden" value="{{ $item->qty }}"  id='qty_po_{{$item->id}}'>
                                    @endif
                                    <input name="qty[]" id='qty_req_po_{{$item->id}}'  class="form-control" value="{{ $item->qty }}">
                                </td>
								<td>{{ $item->measure }}</td>
                                <td>  <textarea name="notes[]" class="form-control">{{ $item->notes }}</textarea></td>
                            </tr>
                        @php
                            $no++
                        @endphp
                        @endforeach
                    @endif
                </tbody>
            </table>

		</div>
        

        <div class="mt-4">
            <a href="{{ route('logistic.lpb.index') }}" class="btn btn-light mr-1 text-uppercase fsz-sm fw-600">{{ trans('Cancel') }}</a>
            @if($lpb->status==1)
                <input class="btn btn-danger text-uppercase fsz-sm fw-600"  type="submit" name="publish" id="btn-submit" value="Publish LPB">
            @endif
        </div>

	{!! Form::close() !!}
</div>
	
@stop


@section('js')

<script  type='text/javascript'>
	$(document).ready(function() {

        @foreach ($lpb_items as $item)
         $('#qty_req_po_{{$item->id}}').on('keyup', function(e) {
                $('.product_{{$item->id}}').css("background-color", "#fff"); 
                var qty_po  = $('#qty_po_{{$item->id}}').val();
                var qty_req = $('#qty_req_po_{{$item->id}}').val();
                if(parseInt(qty_req) > parseInt(qty_po)){
                    e.preventDefault();
                    Swal.fire(
                    'Peringatan!',
                        'QTY LPB tidak boleh melebihi QTY PO',
                        'warning'
                    );
                    $('#qty_req_po_{{$item->id}}').val('');
                }
            });
        @endforeach
        

        $(document).on("click", "#btn-submit", function(e) {
            $('input[name="status"]').val('1');
        });
    });
    </script>
@stop

