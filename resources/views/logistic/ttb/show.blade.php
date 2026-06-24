@extends('layouts.app')

@section('page-header')
    Tanda Terima Barang (TTB)
@stop

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('logistic.inventory.index') }}">Inventory</a></li>
        <li class="breadcrumb-item"><a href="{{ route('logistic.ttb.index') }}">Tanda Terima Barang (TTB)</a></li>
        <li class="breadcrumb-item active" aria-current="page">Detail</li>
    </ol>
@endsection


@section('content')
<div class="row mB-40">

	<div class="col-sm-12">
        <div class="bgc-white p-30 bd">
            <div class="float-right">
                    <a class="btn btn-outline float-right" href="#" title="Print Data" onclick='printExternal("/logistic/ttb_print/{{ Hashids::encode($ttb->id) }}")'><i class="ti-printer icon-lg"></i></a>
            </div>
            <h6><a class="float-left" href="{{ route('logistic.ttb.index') }}"><i class="ti-arrow-left mR-10"></i></a> Kembali</h6>
            <hr class="mB-30">
            <div class="d-block">
                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-toggle="tab" href="#tab1" role="tab">Data TTB</a>
                    </li>
                    <li class="nav-item">
                        <?php
                        if ($ttb->file)
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
                    <h6 class="text-center font-weight-bold mB-40" style="text-decoration:underline">{{ $ttb->doc_no }}</h6>

                    <div class="row">
                        <div class="col-sm-6">
                            <div class="row">
                                <label class="col-sm-4">Tanggal TTB</label>
                                <div class="col-sm-8">
                                    : {{ date('d/m/Y',strtotime( $ttb->date_transaction)) }}
                                </div>
                            </div>
                            <div class="row">
                                <label class="col-sm-4">Operator</label>
                                <div class="col-sm-8">
                                    : {{ $ttb->operator }}
                                </div>
                            </div>
                            <div class="row">
                                <label class="col-sm-4">Penerima</label>
                                <div class="col-sm-8">
                                    : {{ $ttb->received }}
                                </div>
                            </div>
                            @if ($ttb->file)
                            <div class="row">
                                <label class="col-sm-4">Attachment Dokumen</label>
                                <div class="col-sm-8">
                                    : <a targethref="{{ asset('storage'.$ttb->file) }}">Download</a>
                                </div>
                            </div>
                            @endif
                        </div>
                        <div class="col-sm-6">
                            <div class="row">
                                <label class="col-sm-3">Project</label>
                                <div class="col-sm-8">
                                    : {{ $ttb->project }}
                                </div>
                            </div>
                            <div class="row">
                                <label class="col-sm-3">Department/Kapal</label>
                                <div class="col-sm-8">
                                    : {{ $ttb->department }}
                                </div>
                            </div>
                            @if ($ttb->notes)
                                <div class="row">
                                    <label class="col-sm-3">Catatan</label>
                                    <div class="col-sm-8">
                                        : {{ $ttb->notes }}
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <h6 class=" mt-5">Daftar Item </h6>

                    <table class="table table-bordered mt-2">
                        <thead>
                            <th class="text-uppercase" style="width:50px">No</th>
                            <th class="text-uppercase" >Item</th>
                            <th class="text-uppercase text-center" style="width:100px">QTY</th>
                            <th class="text-uppercase text-center" style="width:100px">SATUAN</th>
                            <th class="text-uppercase" style="width:250px !important">Catatan</th>
                        </thead>
                        <tbody class="item_form" id="itemDPM">
                            @php  $no = 1; @endphp
                            @foreach($ttb_items as $item)
                                <tr class="product_1">
                                    <td>{{ $no }}</td>
                                    <td>
                                        {{ $item->productCode }} -  {{ $item->productName }} <br>
                                        {!! $item->productPartNumber != NULL ? '<small> PN: '.$item->productPartNumber.'</small>' : '' !!}
                                    </td>
                                    <td class="text-center">{{ $item->qty }}</td>
                                    <td class="text-center">{{ $item->unit }}</td>
                                    <td>{{ $item->description }}</td>
                                </tr>
                                @php
                                    $no++;
                                @endphp
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="tab-pane" id="tab2" role="tabpanel" aria-labelledby="tab2">
                    <div class="row">
                        <div class="col-12">
                            @if ($ttb->file)
                                <embed class="col align-self-center" src="{{ asset('storage'.$ttb->file) }}" width="600" height="500" alt="pdf" />
                            @else
                                <p class="text-center">Tidak ada lampiran Dokumen</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            @if ($ttb->status == '0'  )

                <div class="mt-4 btn-group">

                    <a href="{{ route('logistic.ttb.edit',Hashids::encode($ttb->id)) }}" class="btn btn-info mr-1 text-uppercase fsz-sm fw-600">Edit Draft</a>

                    <form class='delete' action="{{ route('logistic.ttb.delete', ['id' => $ttb->id]) }}" method='POST'>
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
