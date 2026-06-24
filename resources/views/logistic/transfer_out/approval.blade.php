@extends('layouts.app')

@section('page-header')
    Warehouse Transfer Out
@stop

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('logistic.inventory.index') }}">Inventory</a></li>
        <li class="breadcrumb-item"><a href="{{ route('logistic.transfer_out.index') }}">Warehouse Transfer Out</a></li>
        <li class="breadcrumb-item active" aria-current="page">Approval</li>
    </ol>
@endsection


@section('content')
<div class="row mB-40">

        <div class="col-sm-12">
            {!! Form::model($transfer, [
                    'action' => ['Logistic\InventoryTransferOutController@approval', ],
                    'method' => 'post', 
                    'class' => 'form-horizontal mt-3',
                    'id'    => 'form', 
                ])
            !!}  
                <input type="hidden" name="transfer_id" value="{{ $transfer->id }}">
                <input type="hidden" name="doc_no" value="{{ $transfer->doc_no }}">
                <div class="bgc-white p-30 bd">
                    <div class="float-right">
                            <a class="btn btn-outline float-right" href="#" title="Print Data" onclick='printExternal("/logistic/transfer_print/{{ Hashids::encode($transfer->id) }}")'><i class="ti-printer icon-lg"></i></a>
                    </div>
                    <h6><a class="float-left" href="{{ route('logistic.transfer_out.index') }}"><i class="ti-arrow-left mR-10"></i></a> Kembali</h6>
                    <hr class="mB-30">
                    <div class="alert alert-info">
                        <strong>INFO ! </strong> Jika dilakukan REJECT stock akan dikembalikan ke inventori asal
                    </div>

                    <h6 class="text-center font-weight-bold mB-40" style="text-decoration:underline">{{ $transfer->doc_no }}</h6>

                    <div class="row">
                        <div class="col-sm-6">
                            <div class="row">
                                <label class="col-sm-3">Operator</label>
                                <div class="col-sm-8">
                                    : {{ $transfer->operator }}
                                </div>
                            </div>
                            <div class="row">
                                <label class="col-sm-3">Lokasi Warehouse Tujuan</label>
                                <div class="col-sm-8">
                                    : {{ $transfer->location_destination_name }}
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="row">
                                <label class="col-sm-3">Attachment Dokumen</label>
                                <div class="col-sm-8">
                                    @if( $transfer->file )
                                        <div class="img-thumbnail p-10 bg-light">
                                            <a download href="{{ asset('storage'.$transfer->file) }}" class="text-success">{{ $transfer->file }}</a>
                                        </div>
                                    @else 
                                        Tidak ada
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <h6 class=" mt-5">Daftar Item </h6>
                    <hr>

                    <table class="table table-bordered mt-2">
                        <thead>
                            <th class="text-uppercase" style="width:80px">No. Rak</th>
                            <th class="text-uppercase" >Item</th>
                            <th class="text-uppercase text-center">QTY</th>
                            <th class="text-uppercase" style="width:250px !important">Catatan</th>
                        </thead>
                        <tbody class="item_form" id="itemDPM">
                            @foreach($transfer_items as $item) 
                                <input type="hidden" value="{{ $item->inventory_id }}" name="inv_id[]">
                                <input type="hidden" value="{{ $item->qty }}" name="qty[]">

                                <tr class="product_1">
                                    <td>{{ $item->code_rack }}</td>
                                    <td>
                                        {{ $item->productcode }} -  {{ $item->productname }} <br>
                                        <small>PN/SPEC: {{ $item->productpartnumber }}  </small></td>
                                    <td class="text-right"style="border-right:0 !important">{{ $item->qty }} {{ $item->productunit }}</td>
                                    <td>{{ $item->notes }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>  

                <div class="mt-4">
                    <a href="{{ route('logistic.transfer_out.index') }}" class="btn btn-light text-uppercase fsz-sm fw-600">{{ trans('Cancel') }}</a>
                    <input class="btn btn-success text-uppercase fsz-sm fw-600" type="submit" name="publish" id="btn-submit" value="Approve">
                    <input class="btn btn-danger text-uppercase fsz-sm fw-600 float-right" type="submit" name="reject" id="btn-reject" value="Reject">
                    <input type="hidden" value="2" name="status">
                </div>

	        {!! Form::close() !!}
	</div>
</div>
	
@stop

@section('js')
	<script  type='text/javascript'>
	$(document).ready(function() {
        $(document).on("click", "#btn-reject", function(e) {
            $('input[name="status"]').val('3');
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