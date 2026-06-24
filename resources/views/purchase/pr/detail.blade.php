@extends('layouts.app')

@section('page-header')
    Detail PR - DPH
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
</style>

<div class="mB-40">
    <div class="bgc-white p-30 bd">
        <h6>Monitoring PR - DPH</h6>
        <hr>
        <div class="row mt-5">
            {{-- <div class="col-sm-12">
                <form action="{{ route('purchasing.dph.export') }}" method="GET" id="exportDetailDphPrForm">
                    <input type="hidden" name="pr_id" value="{{$pr->id}}">
                    <a class="btn float-right" id="exportDetailDphPr">
                        <i class='ti-printer icon-lg'></i>
                    </a>
                </form>
            </div> --}}
            <div class="col-sm-12">
                <h6 class="text-center font-weight-bold mB-30 " style="text-decoration:underline">{{ $pr->doc_no }}</h6>
            </div>
            <div class="col-sm-6">
                <div class="row">
                    <label class="col-sm-3">Tipe DPM </label>
                    <div class="col-sm-8">: {{ strtoupper($pr->type) }}</div>
                </div>
                <div class="row">
                    <label class="col-sm-3">No. DPM </label>
                    <div class="col-sm-8">: <a href="{{ route('purchase_request.show', Hashids::encode($pr->purchase_id)) }}" target="_blank"> {{ $pr->dpm_no }} </a></div>
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
                    <div class="col-sm-8">:
                        {{ $pr->project->name ?? ($pr->project->name ?? '-') }}
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="row">
                    <label class="col-sm-3">Deskripsi DPM</label>
                    <div class="col-sm-8">: {{ ($pr->PurchaseRequest) ? $pr->PurchaseRequest->description : '-' }}</div>
                </div>
                <div class="row">
                    <label class="col-sm-3">Dibuat Oleh</label>
                    <div class="col-sm-8">: {{ ($pr->PurchaseRequest) ? $pr->PurchaseRequest->creator->name : '-' }}</div>
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
        <ul class="nav nav-tabs mt-5 monitoring" id="myTab" role="tablist">
            <li class="nav-item">
                <a style="border: 2px solid #ddd; border-radius: 0; padding: 10px 12px; font-size: 14px;" class="nav-link active" id="home-tab" data-toggle="tab" href="#home" role="tab" aria-controls="home" aria-selected="true">PR <br> <small>Purchase Requisitions </small></a>
            </li>
            @if(count($dph) > 0)
                <li class="arrow" style="padding: 20px 0; font-size: 20px;"><i class="ti-arrow-right"></i></li>
                <li class="nav-item">
                    <a style="border: 2px solid #ddd; border-radius: 0; padding: 10px 12px; font-size: 14px;" class="nav-link" id="dph-tab" data-toggle="tab" href="#dph" role="tab" aria-controls="dph" aria-selected="false">{{ count($dph) }} DPH <br><small>Daftar Perbandingan Harga </small></a>
                </li>
            @endif
        </ul>

        <div class="tab-content mt-4" id="myTabContent">
            {{-- PR --}}
            <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">
                <h6 class="mT-30">Daftar Barang</h6>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th style="width:300px">Nama Barang</th>
                            <th>Catatan</th>
                            <th style="min-width:100px">QTY</th>
                            <th>Flag </th>
                            <th>Status</th>
                            <th>Tgl Dibutuhkan</th>
                            <th>Purchaser</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if (count($pr_items) > 0)
                        @php
                            $no = 1
                        @endphp
                        @foreach ($pr_items as $item)
                            <tr data-entry-id="{{ $item->id }}">
                                <td>{{ $no }}</td>
                                <td>
                                    [{{ $item->productCode }}]
                                    {{ $item->product }} <br>
                                    <small>
                                        {!! $item->productPartNumber != NULL ? '<br> PN/Spec: '.$item->productPartNumber : 'PN/Spec: -' !!}
                                        <br>{{ $item->productBrand != NULL ? 'Brand: '.$item->productBrand : 'Brand: -' }}
                                    </small>
                                </td>
                                <td>
                                    {!! $item->notes !!}
                                </td>
                                @if ($item->po_status == 2)
                                    <td>{{ $item->qty_parsial<0 ? $item->qty - $item->qty_parsial : $item->qty_parsial }} {{ $item->measure }}</td>
                                @else
                                    <td>{{ $item->qty}} {{ $item->measure }}</td>
                                @endif

                                <td>{{ $item->flag == "normal" ? "Normal" : "Urgent" }} </td>
                                <td>{!! getStatusItemPR($item->pr_status, $item->po_status, $item->qty_parsial,$pr->type) !!} </td>
                                <td>{{ date('d/m/Y',strtotime( $item->needed_on_date)) }}</td>
                                <td>{{ ($item->purchaser) ? $item->purchaser : '-'}}</td>
                                <td>
                                    @if(getDPMLog($item->id) > 0)
                                        <a href="#" value="{{ action('Logistic\PurchaseRequestController@getPrItemNotes',['id'=>$item->id]) }}" class="icon-lg modalMd" title="Show Data" data-toggle="modal" data-target="#modalMd"><span class="ti-eye text-danger"></span></a>
                                    @else
                                        <a href="#" value="{{ action('Logistic\PurchaseRequestController@getPrItemNotes',['id'=>$item->id]) }}" class="icon-lg modalMd" title="Show Data" data-toggle="modal" data-target="#modalMd"><span class="ti-eye text-muted"></span></a>
                                    @endif
                                    <a href="#" value="{{ action('Purchasing\PoController@getItems',['id'=>$item->product_id]) }}" class="icon-lg modalMdPO ml-1" data-toggle="modal" data-target="#modalHistoryPO"><span class="ti-shopping-cart text-muted"></span></a>
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
            </div>

            {{-- DPH --}}
            @if(count($dph) > 0)
            <div class="tab-pane fade" id="dph" role="tabpanel" aria-labelledby="dph-tab">
                @foreach ($dph as $val)
                    <div class="bd p-20 mt-5" style="background: #f8f9fa;">
                        <div class="row">
                            <div class="col-sm-12">
                                {{-- @if (in_array($val->status, [2, 4, 5]))
                                    <a class="btn btn-outline float-right" href="#" title="Print Data" onclick='printExternal("/purchasing/po_print/{{ Hashids::encode($val->id) }}/print")'><i class="ti-printer icon-lg"></i></a>
                                @endif --}}
                                <h6 class="font-weight-bold mB-10">{{ $val->doc_no }}</h6>
                                <hr>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-6">
                                <div class="row">
                                    <label class="col-sm-4">Dibuat Oleh</label>
                                    <div class="col-sm-8">:
                                        {{ $val->created }} [ {{ idDate($val->created_at) }} ]
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-4">Status</label>
                                    <div class="col-sm-8">:
                                        {!! getStatusDPH($val->status) !!}
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="row">
                                    <label class="col-sm-3">Supplier</label>
                                    <div class="col-sm-9">:
                                        {{ $val->supplier }}
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-sm-3">Supplier Kontak</label>
                                    <div class="col-sm-9">:
                                        {{ $val->picTitle }} {{ $val->picName }} <br> <small class="ml-2"> Telepon : {!! str_replace('||', '<br>&nbsp;&nbsp;&nbsp;Mobile Phone : ', $val->picTelp) !!}
                                            <br>&nbsp;&nbsp;&nbsp;Email : {{ $val->picEmail }} </small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <table class="table table-bordered mt-2">
                            <thead>
                                <tr>
                                    <th class="text-center text-uppercase">No</th>
                                    <th class="text-center text-uppercase">Nama Barang</th>
                                    <th class="text-center text-uppercase" style="width: 340px;">Spesifikasi</th>
                                    <th colspan="2" class="text-center text-uppercase">Jumlah</th>
                                    <th class="text-center text-uppercase">Harga Satuan</th>
                                    <th class="text-center text-uppercase" style="width: 80px;">Disc @if($val->discount_type == 1) (%) @endif</th>
                                    <th class="text-center text-uppercase">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                $no = 1;
                                $subtotal = 0;
                                $dph_items = getDphItem($val->id)
                                @endphp
                                @foreach ($dph_items as $di)
                                    <tr>
                                        <td style="text-align:center">{{ $no }}</td>
                                        <td>{{ $di->product }} {!! $di->productPartNumber != NULL ? '<br> PN/Spec: '.$di->productPartNumber : '' !!} </td>
                                        <td style="width: 250px;">
                                            {!! $di->specification !!} <br>
                                            {{ $di->productBrand != NULL ? 'Brand: '.$di->productBrand : 'Brand: -' }}
                                        </td>
                                        <td class="text-right" style="border-right:0 !important;">{{ $di->qty }}</td>
                                        <td class="text-left" style="border-left:0 !important;">{{ $di->measure }}</td>
                                        <td>
                                            <div class="currency" data-content="{{ $val->currencysymbol }}"> {{  format_number($di->price) }} </div>
                                        </td>
                                        <td class="text-center">
                                            @if($val->discount_amount == 0 && $val->discount_type == 0)
                                                <div class="currency" data-content="{{ $val->currencysymbol }}"> </div>
                                            @endif
                                            {{ format_number($di->discount) }}
                                        </td>
                                        <td class="text-right">
                                            <div class="currency" data-content="{{ $val->currencysymbol }}">
                                                <?php
                                                    $total = $di->price * $di->qty - (($di->price * $di->qty) *  $di->discount / 100);
                                                    echo format_number($total)
                                                ?>
                                            </div>
                                        </td>
                                    </tr>
                                    @php
                                    $subtotal += $total;
                                    $no++;
                                    @endphp
                                    @endforeach
                                    <tr>
                                        <td colspan="5" style="border-bottom:0 !important;border-top:0 !important">
                                            <table class="border-0">
                                                <tr>
                                                    <td class="border-0 p-0" style="width: 200px;border:none !important">Metode Pembayaran</td>
                                                    <td class="border-0 p-0" style="border:none !important">: {{ $val->payment_method }}</td>
                                                </tr>
                                            </table>
                                        </td>
                                        <td colspan="2">Sub Total</td>
                                        <td class="text-right">
                                            <div class="currency" data-content="{{ $val->currencysymbol }}"> {{ format_number($subtotal)}} </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="5" style="border-bottom:0 !important;border-top:0 !important">
                                            <table>
                                                <tr>
                                                    <td class="border-0 p-0" style="width: 200px;border:none !important">Price Term</td>
                                                    <td class="border-0 p-0" style="border:none !important">: {{ $val->price_term }} {{ $val->price_term_location }}</td>
                                                </tr>
                                            </table>
                                        </td>
                                        <td colspan="2">Discount
                                            @if ($val->discount_item == false &&  $val->discount_type == 1 &&  $val->discount_amount != 0)
                                                <small>{{ $val->discount_amount  }}%</small>
                                            @endif
                                        </td>
                                        <td class="text-right " style="max-width: 300px;">
                                            <?php
                                                $total_discount = 0;
                                                if ($val->discount_item == false) {
                                                    $total_discount = $val->discount_amount;
                                                    if ($val->discount_type == 1) $total_discount = $subtotal * ($val->discount_amount / 100);
                                                }
                                            ?>
                                            <div class="currency" data-content="{{ $val->currencysymbol }}">
                                                {{ format_number($total_discount) }}
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="5" style="border-bottom:0 !important;border-top:0 !important">
                                            <table>
                                                <tr>
                                                    <td class="border-0 p-0" style="width: 200px;border:none !important">Payment Term</td>
                                                    <td class="border-0 p-0" style="border:none !important">: {{ $val->payment_term }}</td>
                                                </tr>
                                            </table>
                                        </td>
                                        <td colspan="2">Netto</td>
                                        <td class="text-right">
                                            <div class="currency" data-content="{{ $val->currencysymbol }}">
                                                <?php
                                                $netto = 	$subtotal - $total_discount;
                                                echo format_number($netto);
                                                ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="5" rowspan="5" style="vertical-align:top !important;border-bottom:0 !important;border-top:0 !important">
                                            <table>
                                                <tr>
                                                    <span class="row">
                                                        <td class="border-0 p-0" style="width: 200px;border:none !important; vertical-align:top !important;">Kontak PIC</td>
                                                        <td class="border-0 p-0" style="border:none !important; width:350px"><div>:</div><div style="margin-top:-1.3rem !important;margin-left:0.5rem !important;" class="ms-2">{!! $val->notesDescription !!}</div></td>
                                                    </span>
                                                </tr>
                                                <tr>
                                                    <span class="row">
                                                        <td class="border-0 p-0" style="width: 200px;border:none !important; vertical-align:top !important;margin-top: 3rem !important;">Catatan</td>
                                                        <td class="border-0 p-0" style="border:none !important;"><div>:</div><div style="margin-top:-1.3rem !important;margin-left:0.5rem !important;width:300px !important;" class="ms-2"> {!! $val->notes?$val->notes:' -' !!}</div></td>
                                                    </span>
                                                </tr>
                                            </table>
                                        </td>
                                        <td colspan="2">PPH <small>({{ $val->pph }} %)</small></td>
                                        <td class="text-right">
                                            <div class="currency" data-content="{{ $val->currencysymbol }}">
                                                (<?php
                                                    $pph = 	($val->pph / 100) * $netto;
                                                    echo ($val->currency=='IDR') ? format_number(numberPrecision($pph)) : format_number($pph);
                                                ?>)
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="2">PPN <small>(11%)</small></td>
                                        <td class="text-right">
                                            <div class="currency" data-content="{{ $val->currencysymbol }}">
                                                <?php
                                                $ppn = 0;
                                                if ($val->ppn == "11") {
                                                    $ppn = 	(11 / 100) * $netto;
                                                    echo ($val->currency=='IDR') ? format_number(numberPrecision($ppn)) : format_number($ppn);
                                                }
                                                ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php
                                    $send_expense = $val->send_expense;
                                    $send_expense_ppn_caption = '';
                                    if ($val->send_expense_ppn == 1) {
                                        $send_expense_ppn_caption = "+ PPN 11%";
                                        $send_expense_ppn = (11 / 100) * $send_expense;
                                        $send_expense = $send_expense_ppn + $send_expense;
                                    }
                                    ?>
                                    <tr>
                                        <td colspan="2">Biaya Kirim<small> {{ $send_expense_ppn_caption }}</small></td>
                                        <td class="text-right">
                                            <div class="currency" data-content="{{ $val->currencysymbol }}">
                                                {{ ($val->currency=='IDR') ? format_number(numberPrecision($send_expense)) : format_number($send_expense) }}
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="2">Total</td>
                                        <td class="text-right">
                                            <div class="currency" data-content="{{ $val->currencysymbol }}">
                                                <?php
                                                $total =  ($netto +  $ppn + $send_expense) - $pph;
                                                echo ($val->currency=='IDR') ? format_number(numberPrecision($total)) : format_number($total);
                                                ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="2">Uang Muka</td>
                                        <td class="text-right">
                                            <div class="currency" data-content="{{ $val->currencysymbol }}"> {{ ($val->currency=='IDR') ? format_number(numberPrecision($val->down_payment)) : format_number($val->down_payment) }} </div>
                                        </td>
                                    </tr>
                            </tbody>
                        </table>
                    </div>
                @endforeach
            </div>
        @endif
        </div>
    </div>
</div>

<div class="modal fade" id="modalMd" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="modalMdTitle">History Data</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			</div>
			<div class="modal-body">
				<div class="modalError"></div>
				<div id="modalMdContent"></div>
			</div>
		</div>
	</div>
</div>


<div class="modal fade" id="modalHistoryPO" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="modalMdTitle">History Purchase Order</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			</div>
			<div class="modal-body">
				<div class="modalError"></div>
				<div id="modalMdContentPO"></div>
			</div>
		</div>
	</div>
</div>


<div class="modal fade" tabindex="-1" role="dialog" id="modalPR">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<form id="prForm" action="" method="post">
				<input type="hidden" name="pr_id" value="{{ $pr->id }}">
				@csrf
				<div class="modal-header">
					<h5 class="modal-title">Alasan</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<textarea name="reason" class="form-control" required></textarea>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
					<button type="submit" class="btn btn-danger" id="btn-submit">Submit</button>
				</div>
			</form>
		</div>
	</div>
</div>
@stop

@section('js')
<script type='text/javascript'>
    $(document).ready(function() {

        // $('#exportDetailDphPr').click(function() {
        //     $('#exportDetailDphPrForm').submit();
        // });

        $('.modalMd').off('click').on('click', function () {
			$('#modalMdContent').load($(this).attr('value'));
		});

        $('#modalAssigned').on('show.bs.modal', function (e) {
            var id = $(e.relatedTarget).data('id');
            $('#modalAssignedContent').load("{{ route('purchasing.pr.assign') }}?id="+ id);
        });

        $('.modalMdPO').off('click').on('click', function () {
            $('#modalMdContentPO').load($(this).attr('value'));
        });

        $('.modalRevision').on('click', function () {
            $('#prForm').attr("action", "{{ route('purchasing.pr.revision') }}");
        });

        $('.modalClose').on('click', function () {
            $('#prForm').attr("action", "{{ route('purchasing.pr.close') }}");
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
