@extends('layouts.app')

@section('page-header')
    Adjustment Stock
@stop

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('logistic.inventory.index') }}">Inventory</a></li>
        <li class="breadcrumb-item"><a href="{{ route('logistic.adjustment_stock.index') }}">Adjustment Stock</a></li>
        <li class="breadcrumb-item active" aria-current="page">Detail</li>
    </ol>
@endsection


@section('content')
<div class="row mB-40">

	<div class="col-sm-12">
        <div class="bgc-white p-30 bd">
            <div class="float-right">
                    <a class="btn btn-outline float-right" href="#" title="Print Data" onclick='printExternal("/logistic/adjustment_print/{{ Hashids::encode($data->id) }}")'><i class="ti-printer icon-lg"></i></a>
            </div>
            <h6><a class="float-left" href="{{ route('logistic.adjustment_stock.index') }}"><i class="ti-arrow-left mR-10"></i></a> Kembali</h6>
            <hr class="mB-30">
            <div class="d-block">
                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-toggle="tab" href="#tab1" role="tab">Data Adjustment</a>
                    </li>
                    <li class="nav-item">
                        <?php 
                        if ($data->file)
                            $badge = "<sup class='badge '><span class='bg-success rounded-circle text-success' style='padding:0 5px'></span></sup>";
                        else
                            $badge = "";
                        ?>
                        <a class="nav-link" data-toggle="tab" href="#tab2" role="tab">Dokumen  {!! $badge !!}</a>
                    </li>
                </ul>
            </div>
            <div class="tab-content mT-30" >
                <div class="tab-pane active" id="tab1" role="tabpanel" aria-labelledby="tab1">
                    <h6 class="text-center font-weight-bold mB-40" style="text-decoration:underline">{{ $data->doc_no }}</h6>

                    <div class="row">
                        <div class="col-sm-6">
                            <div class="row">
                                <label class="col-sm-4">Tanggal</label>
                                <div class="col-sm-8">
                                    : {{ date('d/m/Y',strtotime( $data->created_at)) }}
                                </div>
                            </div>
                           
                            @if ($data->file)
                                <div class="row">
                                    <label class="col-sm-4">Attachment Dokumen</label>
                                    <div class="col-sm-8">
                                        : <a targethref="{{ asset('storage'.$data->file) }}">Download</a>
                                    </div>
                                </div>
                            @endif
                        </div>
                        <div class="col-sm-6">
                            <div class="row">
                                <label class="col-sm-4">Operator</label>
                                <div class="col-sm-8">
                                    : {{ $data->operator }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <h6 class=" mt-5">Daftar Item </h6>

                    <table class="table table-bordered mt-2">
                        <thead>
                            <th class="text-uppercase" style="width:80px">No. Rak</th>
                            <th class="text-uppercase" >Nama Barang</th>
                            <th class="text-uppercase text-center" style="width:100px">QTY AWAL</th>
                            <th class="text-uppercase text-center" style="width:100px">QTY FISIK</th>
                            <th class="text-uppercase text-center" style="width:100px">STN</th>
                            <th class="text-uppercase" style="width:250px !important">Alasan</th>
                        </thead>

                        <tbody class="item_form" id="itemDPM">
                            @if($data->inventory)
                                <tr class="product_1">
                                    <td>{{ ($data->inventory) ? $data->inventory->code_rack : '' }}</td>
                                    <td>
                                        {{ ($data->inventory->product) ? $data->inventory->product->code : '' }} -  {{ ($data->inventory->product) ? $data->inventory->product->name : '' }} <br>
                                        <small>Part Number: {!! ($data->inventory->product) ? $data->inventory->product->part_number : '-' !!}</small>
                                    </td>
                                    <td>{{ $data->qty_awal }}</td>
                                    <td>{{ $data->qty_fisik }}</td>
                                    <td>{{ ($data->inventory->measure) ? $data->inventory->measure->name : '' }}</td>
                                    <td>{{ $data->reason }}</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                <div class="tab-pane" id="tab2" role="tabpanel" aria-labelledby="tab2">
                    <div class="row">
                        <div class="col-12">
                            @if ($data->file)
                                <embed class="col align-self-center" src="{{ asset('storage'.$data->file) }}" width="600" height="500" alt="pdf" />
                            @else
                                <p class="text-center">Tidak ada lampiran Dokumen</p>
                            @endif
                        </div>
                    </div>
                  </div>
            </div>

            @if ($data->status == '0'  )

                <div class="mt-4 btn-group">

                    <a href="{{ route('logistic.adjustment_stock.edit',Hashids::encode($data->id)) }}" class="btn btn-info mr-1 text-uppercase fsz-sm fw-600">Edit Draft</a>

                    <form class='delete' action="{{ route('logistic.adjustment_stock.delete', ['id' => $data->id]) }}" method='POST'>
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