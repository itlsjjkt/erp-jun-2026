@extends('layouts.app')

@section('page-header')
    Warehouse Transfer Out
@stop

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('logistic.inventory.index') }}">Inventory</a></li>
        <li class="breadcrumb-item"><a href="{{ route('logistic.transfer_out.index') }}">Warehouse Transfer Out</a></li>
        <li class="breadcrumb-item active" aria-current="page">Detail</li>
    </ol>
@endsection


@section('content')
<div class="row mB-40">

	<div class="col-sm-12">
        <div class="bgc-white p-30 bd">
            <div class="float-right">
                <a class="btn btn-outline float-right" href="#" title="Print Data" onclick="printExternal('{{ route('logistic.transfer_out.print',Hashids::encode($transfer->id) ) }}')"><i class="ti-printer icon-lg"></i></a>
            </div>
            <h6><a class="float-left" href="{{ route('logistic.transfer_out.index') }}"><i class="ti-arrow-left mR-10"></i></a> Kembali</h6>
            <hr class="mB-30">

            <h6 class="text-center font-weight-bold mB-40" style="text-decoration:underline">{{ $transfer->doc_no }}</h6>

            <div class="row">
                <div class="col-sm-6">
                    <div class="row">
                        <label class="col-sm-4">Type WTO</label>
                        <div class="col-sm-7">
                            : {{ getTypeWto($transfer->type) }}
                        </div>
                    </div>
                    <div class="row">
                        <label class="col-sm-4">Operator</label>
                        <div class="col-sm-7">
                            : {{ $transfer->operator }}
                        </div>
                    </div>
                    <div class="row">
                        <label class="col-sm-4">Lokasi Warehouse Asal</label>
                        <div class="col-sm-7">
                            : {{ $transfer->location.' - '.$transfer->companyCode }}
                        </div>
                    </div>
                    <div class="row">
                        <label class="col-sm-4">Lokasi Warehouse Tujuan</label>
                        <div class="col-sm-7">
                            : {{ $transfer->location_destination_name.' - '.$transfer->comdestinasiCode }}
                        </div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="row">
                        <label class="col-sm-4">Attachment Dokumen</label>
                        <div class="col-sm-7">
                            @if( $transfer->file )
                                <div class="img-thumbnail p-10 bg-light">
                                    @php
                                        $fileName = $transfer->file;
                                        $shortFileName = strlen($fileName) > 10 ? '...' . substr($fileName, -10) : $fileName;
                                    @endphp
                                    <a target="_blank" href="{{ asset('storage/' . $fileName) }}" class="text-success">{{ $shortFileName }}</a>
                                </div>
                            @else
                                : Tidak ada
                            @endif
                        </div>
                    </div>
                    <div class="row">
                        <label class="col-sm-4">Status</label>
                        <div class="col-sm-7">
                            : {!! getStatusTransferInventory($transfer->status) !!}
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

            @if ($transfer->status == '0'  )

                <div class="mt-4 btn-group">

                    <a href="{{ route('logistic.transfer_out.edit', Hashids::encode($transfer->id)) }}" class="btn btn-info mr-1 text-uppercase fsz-sm fw-600">Edit Draft</a>

                    <form class='delete' action="{{ route('logistic.transfer_out.delete', ['id' => $transfer->id]) }}" method='POST'>
                        {{ csrf_field() }}
                        <button class='btn btn-danger mr-1 text-uppercase fsz-sm fw-600' title='Hapus'>Hapus Draft</button>
                    </form>
                </div>

            @endif
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
