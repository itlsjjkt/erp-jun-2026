@extends('layouts.app')

@section('page-header')
    Warehouse Transfer In
@stop

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('logistic.inventory.index') }}">Inventory</a></li>
        <li class="breadcrumb-item"><a href="{{ route('logistic.transfer_in.index') }}">Warehouse Transfer In</a></li>
        <li class="breadcrumb-item active" aria-current="page">Detail</li>
    </ol>
@endsection


@section('content')
<div class="row mB-40">

	<div class="col-sm-12">
        <div class="bgc-white p-30 bd">
            <div class="float-right">
                <a class="btn btn-outline float-right" href="#" title="Print Data" onclick="printExternal('{{ route('logistic.transfer_in.print',Hashids::encode($transfer->id) ) }}')"><i class="ti-printer icon-lg"></i></a>
            </div>
            <h6><a class="float-left" href="{{ route('logistic.transfer_in.index') }}"><i class="ti-arrow-left mR-10"></i></a> Kembali</h6>
            <hr class="mB-30">

            <h6 class="text-center font-weight-bold mB-40" style="text-decoration:underline">{{ $transfer->doc_no }}</h6>

            <div class="row">
                <div class="col-sm-6">
                    <div class="row">
                        <label class="col-sm-3">No Transfer Out</label>
                        <div class="col-sm-8">
                            : {{ $transfer->out_doc_no }}
                        </div>
                    </div>
                    <div class="row">
                        <label class="col-sm-3">Tgl Transfer Out</label>
                        <div class="col-sm-8">
                            : {{ date('d F Y',strtotime( $transfer->created_at_wto)) }}
                        </div>
                    </div>
                    <div class="row">
                        <label class="col-sm-3">Lokasi Asal</label>
                        <div class="col-sm-8">
                            : {{ $transfer->lokasiasal.' - '.$transfer->comAsalCode }}
                        </div>
                    </div>
                    <div class="row">
                        <label class="col-sm-3">Operator</label>
                        <div class="col-sm-8">
                            : {{ $transfer->operator_wto }}
                        </div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="row">
                        <label class="col-sm-3">Penerima</label>
                        <div class="col-sm-8">
                            : {{ $transfer->received }}
                        </div>
                    </div>
                    <div class="row">
                        <label class="col-sm-3">Lokasi Tujuan</label>
                        <div class="col-sm-8">
                            : {{ $transfer->location.' - '.$transfer->companyCode }}
                        </div>
                    </div>
                    <div class="row">
                        <label class="col-sm-3">Tanggal diterima</label>
                        <div class="col-sm-8">
                            : {{ date('d F Y',strtotime( $transfer->received_date )) }}
                        </div>
                    </div>
                    <div class="row">
                        <label class="col-sm-3">Status WTI</label>
                        <div class="col-sm-8">
                            : {!! getStatusTransferIn($transfer->status,$transfer->type,$transfer->type_status) !!}
                        </div>
                    </div>
                </div>
            </div>

            <h6 class=" mt-5">Daftar Item </h6>
            <hr>

            <table class="table table-bordered mt-2">
                <thead>
                    <th class="text-uppercase" >Item</th>
                    <th class="text-uppercase text-center">QTY</th>
                    <th class="text-uppercase text-center">Satuan</th>
                    <th class="text-uppercase" style="width:250px !important">Catatan</th>
                    @if($transfer->status == 1 && $transfer->type == 1 ) {{--PEMINJAMAN DAN STATUS NYA SUDAH DI CEK--}}
                    <th class="text-uppercase">Detail Penggantian</th>
                    @endif
                </thead>
                <tbody class="item_form" id="itemDPM">
                    @foreach($transfer_items as $item)
                        <tr class="product_1">
                           <td>
                                [{{ $item->productcode }}] -  {{ $item->productname }} <br>
                                <small>
                                    PN/SPEC: {{ $item->productpartnumber ?? '-'}}
                                </small>
                            </td>
                            <td class="text-center">{{ $item->qty }}</td>
                            <td class="text-center">{{ $item->productunit }}</td>
                            <td>{{ $item->notes }}</td>
                            @if($transfer->status == 1 && $transfer->type == 1 )
                               <td>
                                    <small>
                                        <table style="border:none; border-collapse: collapse;">
                                            <tbody>
                                                <tr>
                                                    <td style="border:none;">Penggantian DPM</td>
                                                    <td style="border:none;">
                                                        : @if($item->type_replacement == 1)
                                                            <b style="color:green">Ya</b>
                                                        @else
                                                            <b style="color:red">Tidak</b>
                                                        @endif
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td style="border:none;">Notes</td>
                                                    <td style="border:none;">: {{$item->type_replacement_notes ?? '-'}}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </small>
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
		</div>
	</div>
</div>

@stop

@section('js')
	<script  type='text/javascript'>
	    function printExternal(url) {
			var printWindow = window.open(url, 'Print', 'left=200, top=200, width=950, height=800, toolbar=0, resizable=0');
			printWindow.addEventListener('load', function() {
				printWindow.print();
			}, true);
		}
	</script>
@stop
