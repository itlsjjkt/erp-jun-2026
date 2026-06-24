@extends('layouts.app')

@section('page-header')
    Detail DPH
@stop

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('purchasing.dph.index') }}">Daftar Perbandingan Harga</a></li>
        <li class="breadcrumb-item active" aria-current="page">Detail</li>
    </ol>
@endsection

@section('content')
<style>
    .monitoring .active {
        background-color: #d4edda !important;
        border-color: #c3e6cb !important;
        font-weight: bold;
    }
    .add {
        background-color: #d4eaed !important;
        border-color: #c9c9c9 !important;
        font-weight: bold;
    }
    .btn-outline-custom {
        border-color: #cecdcd;
        color: #7e7e7e;
    }
    .btn-outline-custom:hover {
        background-color: #d4edda;
        color: #000000;
    }
    .hidden-row {
        display: none;
    }
    .see-more td {
        text-align: right;
    }
</style>
<div class="mB-40">
    <div class="bgc-white p-30 bd">
        <div class="row mb-3 justify-content-end">
			<div class="col-sm-6 ">
				<a href="{{ route('purchasing.dph.index') }}" class="nav-link"> <i class="ti-arrow-left"></i> Kembali  </a>
            </div>
			<div class="col-sm-6 d-flex justify-content-end align-items-center">
                @php
                    $url_approved = "<form class='update' action='".route('purchasing.dph.toApproval', ['id' => $dph->id])."' method='POST'>
                                        ".csrf_field()."
                                        <button class='btn btn-outline' title='Lanjutkan Ke Approval DPH' data-toggle='tooltip'>
                                            <i class='ti-new-window icon-lg'></i>
                                        </button>
                                    </form>";
                @endphp
                <div class="btn-group" style="padding: 5px; border-radius: 5px;">
                    @if ($dph->status == 1)
                        {!! $url_approved !!}
                    @endif
                    <form action="{{ route('purchasing.dph.print') }}" method="GET" class="ml-2">
                        {{ csrf_field() }}
                        <input type="hidden" name="dph_id" value="{{ $dph->id }}">
                        <button type="submit" class="btn btn-outline" title="Print Data">
                            <i class="ti-printer icon-lg"></i>
                        </button>
                    </form>
                </div>
            </div>
		</div>
        <hr>
        <div class="row mt-5">
            <div class="col-sm-12">
                <h6 class="text-center font-weight-bold mB-30 " style="text-decoration:underline">{{ $dph->doc_no }}</h6>
            </div>
            <div class="col-sm-6">
				<div class="row">
					<label class="col-sm-3">No. DPM </label>
                    <div class="col-sm-8">: {{$dph->dpm_no}}</div>
                </div>
				<div class="row">
					<label class="col-sm-3">No. PR </label>
                    <div class="col-sm-8">: {{$dph->pr_no}}</div>
                </div>
                <div class="row">
                    <label class="col-sm-3">Kapal/Departemen </label>
                    <div class="col-sm-8">: {{$dph->department}}</div>
                </div>
                <div class="row">
                    <label class="col-sm-3">Project </label>
                    <div class="col-sm-8">:	{{$dph->project}}

                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="row">
                    <label class="col-sm-3">Dibuat Oleh</label>
                    <div class="col-sm-8">: {{$dph->created}} [{{idDate($dph->created_at)}}]</div>
                </div>
                <div class="row">
                    <label class="col-sm-3">Status</label>
                    <div class="col-sm-8">: {!!getStatusDph($dph->status)!!}</div>
                </div>
				@php
					$suppliers = getSupplierByDph($dph->id);
                    $index_sup = 1;
				@endphp
                @if($dph->status == 2)
                    <div class="row">
                        <label class="col-sm-3">Posisi Approval</label>
                        <div class="col-sm-8">: {{$dph->position}}</div>
                    </div>
                @endif
				<div class="row">
                    <label class="col-sm-3">Jumlah Supplier</label>
                    <div class="col-sm-8">: <span class='badge badge-secondary'>{{count($suppliers)}} Supplier</span></div>
                </div>
                <div class="row">
                    <label class="col-sm-3">History DPH</label>
                    <div class="col-sm-8">:
                        <a href="#" class="icon-lg modalHistory" title="Show Data" data-toggle="modal" data-target="#modalHistory"><span class="ti-time text-danger"></span></a>
                    </div>
                </div>
            </div>
            @if($dph->status == 3)
            <div class="col-sm-12 alert alert-danger" role="alert">
                <strong>CATATAN REVISI :</strong> {!! $dph->message ?? ' -'!!}
            </div>
            @endif
        </div>

        <div class="tab-content bd p-20 mt-5" id="myTabContent" style="overflow-x: auto; white-space: nowrap; display: flex; flex-wrap: nowrap; gap: 0;">
            @php
                $index_sup = 1;
            @endphp
            @foreach ($suppliers as $sup)
                <br>
                <table class="table table-bordered mt-2">
                    <tbody>
                        <tr style="border: none;" >
                            @if($index_sup == 1)
                                <td colspan="2" style="border: none;"></td>
                            @endif
                            <td colspan="6" style="border: none;">
                                <table style="width: 100%; border:none;">
                                    <tr>
                                        <td style="font-weight: bold; vertical-align:top !important;border:none;">Supplier</td>
                                        <td style="vertical-align:top !important;border:none;">
                                            : {{ $sup->supplier ?? '-' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="font-weight: bold; vertical-align:top !important;border:none;">Alamat</td>
                                        <td style="vertical-align:top !important;border:none;">
                                            : {{ $sup->alamat_supplier ?? '-' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="font-weight: bold; vertical-align:top !important;border:none;">PIC Supplier</td>
                                        <td style="vertical-align:top !important;border:none;">
                                            : {{ $sup->picTitle }} {{ $sup->picName }} <br>
                                            <small class="ml-2">
                                                Telepon: {!! str_replace('||', '<br>&nbsp;&nbsp;&nbsp;Mobile Phone: ', $sup->picTelp) !!}
                                                <br>&nbsp;&nbsp;&nbsp;Email:
                                                @foreach(explode(";", $sup->picEmail) as $email)
                                                    {{ $email }};
                                                @endforeach
                                            </small>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="font-weight: bold; vertical-align:top !important;border:none;">Attachment</td>
                                        <td style="vertical-align:top !important;border:none;">: 
                                            @php
                                                $file = $sup->file;
                                                $maxLength = 14;
                                                $displayFile = strlen($file) > $maxLength ? '...' . substr($file, -$maxLength) : $file;
                                            @endphp
                                            <code>{{ $displayFile }}</code>
                                            <a href="#" class="icon-lg modalMR{{ $sup->id }}" title="Show Data" data-toggle="modal" data-target="#modalMR{{ $sup->id }}">
                                                {!! $file ? '<span class="ti-eye"></span>' : ' -'!!}
                                            </a>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            @if($index_sup == 1)
                                <td class="text-center text-uppercase">No</td>
                                <td class="text-center text-uppercase">Nama Barang</td>
                            @endif
                            <td  class="text-center text-uppercase">Catatan</td>
                            <td  colspan="2" class="text-center text-uppercase">Jumlah</td>
                            <td  class="text-center text-uppercase">Harga Satuan</td>
                            <td  class="text-center text-uppercase">Disc (%)</td>
                            <td  class="text-center text-uppercase">Total</td>
                        </tr>
                        @php
                        $no = 1;
                        $subtotal = 0;
                        $dph_items = getDphItemByDphSupplier($sup->id)
                        @endphp
                        @foreach ($dph_items as $di)
                            <tr @if($di->is_recomendation === 1) style="background-color: #d4f1db; height:100px;" @else style="background-color: none; height:100px;" @endif >
                                @if($index_sup == 1)
                                    <td style="text-align:center; vertical-align:top !important;">
                                        {{ $no }}
                                    </td>
                                    <td style="vertical-align:top !important;">
                                        {{ $di->product }}
                                        <small>
                                            {!! $di->productPartNumber != NULL ? '<br> PN/Spec: '.$di->productPartNumber : '<br> PN/Spec: -' !!} <br>
                                            {{ $di->productBrand != NULL ? 'Brand: '.$di->productBrand : 'Brand: -' }}
                                        </small>
                                    </td>
                                @endif
                                <td style="vertical-align:top !important;">
                                    {!! $di->specification !!}
                                </td>
                                <td class="text-right" style="border-right:0 !important; vertical-align:top !important;">
                                    {{ $di->qty }}
                                </td>
                                <td class="text-left" style="border-left:0 !important; vertical-align:top !important;">
                                    {{ $di->measure }}
                                </td>
                                <td style="vertical-align:top !important; min-width:150px !important;">
                                    <div class="currency" data-content="{{ $sup->currencysymbol }}"> {{  format_number($di->price) }} </div>
                                </td>
                                <td class="text-center" style="vertical-align:top !important;">
                                    @if($sup->discount_amount == 0 && $sup->discount_type == 0)
                                        <div class="currency" data-content="{{ $sup->currencysymbol }}"> </div>
                                    @endif
                                    {{ format_number($di->discount) }}
                                </td>
                                <td class="text-right" style="vertical-align:top !important; min-width:150px !important;">
                                    @php
                                    $total = $di->price * $di->qty - (($di->price * $di->qty) * $di->discount / 100);
                                    @endphp
                                    <div class="currency" data-content="{{ $sup->currencysymbol }}">
                                        {!! format_number($total) !!} 
                                    </div>
                                </td>
                            </tr>
                            @php
                            $subtotal += $total;
                            $no++;
                            @endphp
                        @endforeach
                        <tr class="">
                            @if($index_sup == 1)
                                <td style="border: none !important;"></td>
                                <td style="border: none !important;"></td>
                            @endif
                            <td colspan="3" style="border-bottom:0 !important;border-top:0 !important">
                                <table>
                                    <tr>
                                        <td class="border-0 p-0" style="border:none !important">Payment Method</td>
                                        <td class="border-0 p-0" style="border:none !important">: {{ $sup->payment_method ?? ' -'}}</td>
                                    </tr>
                                </table>
                            </td>
                            <td colspan="2" style="height: 60px;">Sub Total</td>
                            <td class="text-right">
                                <div class="currency" data-content="{{ $sup->currencysymbol }}"> {{ format_number($subtotal)}} </div>
                            </td>
                        </tr>
                        <tr class="">
                            @if($index_sup == 1)
                                <td style="border: none !important;"></td>
                                <td style="border: none !important;"></td>
                            @endif
                            <td colspan="3" style="border-bottom:0 !important;border-top:0 !important">
                                <table>
                                    <tr>
                                        <td class="border-0 p-0" style="border:none !important">Price Term</td>
                                        <td class="border-0 p-0" style="border:none !important">: {{ $sup->price_term }} {{ $sup->price_term_location }}</td>
                                    </tr>
                                </table>
                            </td>
                            <td colspan="2" style="height: 60px;">Discount
                                @if ($sup->discount_item == false &&  $sup->discount_type == 1 &&  $sup->discount_amount != 0)
                                    <small>({{ $sup->discount_amount  }}%)</small>
                                @endif
                            </td>
                            <td class="text-right " style="max-width: 300px;">
                                <?php
                                    $total_discount = 0;
                                    if ($sup->discount_item == false) {
                                        $total_discount = $sup->discount_amount;
                                        if ($sup->discount_type == 1) $total_discount = $subtotal * ($sup->discount_amount / 100);
                                    }
                                ?>
                                <div class="currency" data-content="{{ $sup->currencysymbol }}">
                                    {{ format_number($total_discount) }}
                                </div>
                            </td>
                        </tr>
                        <tr class="">
                            @if($index_sup == 1)
                                <td style="border: none !important;"></td>
                                <td style="border: none !important;"></td>
                            @endif
                            <td colspan="3" style="border-bottom:0 !important;border-top:0 !important">
                                <table>
                                    <tr>
                                        <td class="border-0 p-0" style="border:none !important">Payment Term</td>
                                        <td class="border-0 p-0" style="border:none !important">: {{ $sup->payment_term }}</td>
                                    </tr>
                                </table>
                            </td>
                            <td colspan="2" style="height: 60px;">Netto</td>
                            <td class="text-right">
                                <div class="currency" data-content="{{ $sup->currencysymbol }}">
                                    <?php
                                    $netto = 	$subtotal - $total_discount;
                                    echo format_number($netto);
                                    ?>
                                </div>
                            </td>
                        </tr>
                        <tr class="">
                            @if($index_sup == 1)
                                <td style="border: none !important;"></td>
                                <td style="border: none !important;"></td>
                            @endif
                            <td colspan="3" rowspan="3" style="vertical-align:top !important;border-bottom:0 !important;border-top:0 !important">
                                <table>
                                    <tr>
                                        <td class="border-0 p-0" style="border:none !important">Waktu Pengiriman</td>
                                        <td class="border-0 p-0" style="border:none !important">: {!! $sup->estimated_delivery_day ? $sup->estimated_delivery_day.' Hari' : ' -' !!}</td>
                                    </tr>
                                </table>
                            </td>
                            <td colspan="2" style="height: 60px;">PPN @if($sup->ppn!=0)<small>({{$sup->ppn}} %)</small>@endif</td>
                            <td class="text-right">
                                <div class="currency" data-content="{{ $sup->currencysymbol }}">
                                    <?php
                                    $ppn = 0;
                                    if ($sup->ppn != "0") {
                                        $ppn = 	($sup->ppn / 100) * $netto;
                                        echo ($sup->currency=='IDR') ? format_number(numberPrecision($ppn)) : format_number($ppn);
                                    }else{
                                        echo "0,00";
                                    }
                                    ?>
                                </div>
                            </td>
                        </tr>
                        <?php
                        $send_expense = $sup->send_expense;
                        $send_expense_ppn_caption = '';
                        if ($sup->send_expense_ppn != 0) {
                            $send_expense_ppn_caption = "+PPN ".$sup->send_expense_ppn."%";
                            $send_expense_ppn = ($sup->send_expense_ppn / 100) * $send_expense;
                            $send_expense = $send_expense_ppn + $send_expense;
                        }
                        ?>
                        <tr class="">
                            @if($index_sup == 1)
                                <td style="border: none !important;"></td>
                                <td style="border: none !important;"></td>
                            @endif
                            <td colspan="2" style="height: 60px;">Biaya Kirim <small>@if($send_expense_ppn_caption)({{ $send_expense_ppn_caption }})@endif</small></td>
                            <td class="text-right">
                                <div class="currency" data-content="{{ $sup->currencysymbol }}">
                                    {{ ($sup->currency=='IDR') ? format_number(numberPrecision($send_expense)) : format_number($send_expense) }}
                                </div>
                            </td>
                        </tr>
                        <tr class="">
                            @if($index_sup == 1)
                                <td style="border: none !important;"></td>
                                <td style="border: none !important;"></td>
                            @endif
                            <td colspan="2" style="height: 60px;">Total</td>
                            <td class="text-right">
                                <div class="currency" data-content="{{ $sup->currencysymbol }}">
                                    <?php
                                    $total =  $netto +  $ppn + $send_expense;
                                    echo ($sup->currency=='IDR') ? format_number(numberPrecision($total)) : format_number($total);
                                    ?>
                                </div>
                            </td>
                        </tr>
                        <tr class="">
                            @if($index_sup == 1)
                                <td style="border: none !important;"></td>
                                <td style="border: none !important;"></td>
                            @endif
                            <td colspan="3" style="border-bottom:0 !important;border-top:0 !important">
                                @if($dph->status == 1 || $dph->status == 3)
                                    <table>
                                        <tr>
                                            <td class="border-0 p-0" style="border:none !important; vertical-align:bottom;"><h2><i style="color:#a9e1b6;background-color:#c3e6cb" class="ti-layout-width-full"></i></h2></td>
                                            <td class="border-0 p-0" style="border:none !important;font-weight:bold;">: Rekomendasi Approval</td>
                                        </tr>
                                    </table>
                                @endif
                            </td>
                            <td colspan="2" style="height: 60px;">Uang Muka</td>
                            <td class="text-right">
                                <div class="currency" data-content="{{ $sup->currencysymbol }}"> {{ ($sup->currency=='IDR') ? format_number(numberPrecision(($total * $sup->dp_percentage)/100)) : format_number(($total * $sup->dp_percentage)/100) }} </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
                @php
                    $index_sup = $index_sup + 1;
                @endphp
            @endforeach
        </div>
        <br>
        <div>
            <strong>CATATAN DPH : </strong>
        </div>
        <div class="form-control" style="margin-left:50px; width:95%;height: auto;">
            {!! $dph->notes ?? ' -' !!}
        </div>
    </div>
    <br>
</div>
@foreach($suppliers as $li)
<div class="modal fade" id="modalMR{{$li->id}}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	<div class="modal-dialog modal-xl" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="modalMdTitle">MR File</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			</div>
			<div class="modal-body">
				<embed class="col align-self-center" src="{{ asset('storage'.$li->file) }}" width="600" height="500" alt="pdf" />
			</div>
		</div>
	</div>
</div>
@endforeach
<div class="modal fade" id="modalHistory" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-l" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalMdTitle">History {{$dph->doc_no}}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="max-height: 400px; overflow-y: auto;">
                @php
                    $history = getHistoryDPH($dph->id);
                    $first = true;
                @endphp
                <div style="border-left: 2px solid #17a2b8;">
                    @foreach ($history as $his)
                    <div @if($first) style="background-color:#17a2b8; border-top-right-radius: 15px; border-bottom-right-radius: 15px;" @endif>
                        @if(!$first)
                        <div style="width: 15px; height: 15px; background-color: #17a2b8; border-radius: 50%; position: relative; left: -8px; top:20px;"></div>
                        @endif
                        <div style="margin-left: 15px;" @if($first) class="text-light" @endif>
                            {!! \Carbon\Carbon::parse($his->created_at)->format('d/M/Y H:i') . ' <strong>' . getUserByID($his->user_id) . '</strong> <br>' . $his->message !!}
                        </div>
                        <br>
                    </div>
                    @php
                        $first = false;
                    @endphp
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>


@stop

@section('js')
<script type='text/javascript'>
    $(document).ready(function() {

    });
</script>
@stop
