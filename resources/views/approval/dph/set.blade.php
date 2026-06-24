@extends('layouts.app')

@section('page-header')
    Approval DPH
@stop

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('approval.dph.index') }}">Approval DPH</a></li>
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
        color: #282828;
    }
    .hidden-row {
        display: none; /* Hide these rows initially */
    }
    .see-more td {
        text-align: right; /* Align text in the see-more row to the right */
    }
    .table-bordered th,
    .table-bordered td,
    .border {
        border: 1px solid #282828 !important;
        border-bottom: 0.5px solid #282828 !important;
        border-right: 0.5px solid #282828 !important;

    }
</style>
<div class="mB-40">
    <div class="bgc-white p-30 bd">
        <div class="row mb-3 justify-content-end">
			<div class="col-sm-6 ">
				<a href="{{ route('approval.dph.index') }}" class="nav-link"> <i class="ti-arrow-left"></i> Kembali  </a>
            </div>
			<div class="col-sm-6">
			</div>
		</div>
        <hr>
        <div class="row mt-5" style="margin-left: 100px;">
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
                    <label class="col-sm-3">Catatan DPH</label>
                    <div class="col-sm-8"> {!! $dph->notes ?? '<div>: -</div>'!!}

                    </div>
                </div>
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
        </div>
        <div class="tab-content bd p-20 mt-5" id="myTabContent" style="overflow-x: auto; white-space: nowrap; display: flex; flex-wrap: nowrap; gap: 0; {{ count($suppliers) <= 2 ? 'justify-content: center; align-items: center;' : '' }} ">
            @php
                $index_sup = 1;
            @endphp
            
            @foreach ($suppliers as $sup)
                <div style="display: inline-block; margin-right: 0; flex-shrink: 0;"
                    class="tab-pane active"
                    id="supplier_{{$index_sup}}"
                    role="tabpanel"
                    aria-labelledby="head_sup_tab_{{$index_sup}}">

                    <div>
                        <br>
                        <table class="table table-bordered mt-2">
                            <thead>
                                <tr>
                                    @if($index_sup == 1)
                                        <td colspan="2" style="border-right:none !important;"></td>
                                    @endif
                                    <td colspan="6" style="border-left:none !important;height:300px !important;">
                                        <table style="width: 100%; border: none !important;">
                                            <tr>
                                                <td style="font-weight: bold; vertical-align:top !important; border: none !important;">Supplier</td>
                                                <td style="vertical-align:top !important; border: none !important;">
                                                    : {{ $sup->supplier ?? '-' }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="font-weight: bold; vertical-align:top !important; border: none !important;">Alamat</td>
                                                <td style="vertical-align:top !important; border: none !important;">
                                                    : {{ $sup->alamat_supplier ?? '-' }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="font-weight: bold; vertical-align:top !important; border: none !important;">PIC Supplier</td>
                                                <td style="vertical-align:top !important; border: none !important;">
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
                                                <td style="font-weight: bold; vertical-align:top !important; border: none !important;">Attachment</td>
                                                <td style="vertical-align:top !important; border: none !important;">:
                                                    @php
                                                        $file = $sup->file;
                                                        $maxLength = 14;
                                                        $displayFile = strlen($file) > $maxLength ? '...' . substr($file, -$maxLength) : $file;
                                                    @endphp
                                                    <code>{{ $displayFile }}</code>
                                                    <a href="#" class="icon-lg modalMR{{ $sup->id }}" title="Show Data" data-toggle="modal" data-target="#modalMR{{ $sup->id }}">
                                                        {!! $file ? '<span class="ti-eye"></span>' : ' -' !!}
                                                    </a>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    @if($index_sup == 1)
                                    <th class="text-center text-uppercase">No</th>
                                    <th class="text-center text-uppercase">Nama Barang</th>
                                    @endif
                                    <th  class="text-center text-uppercase">Catatan</th>
                                    <th  colspan="2" class="text-center text-uppercase">Jumlah</th>
                                    <th  class="text-center text-uppercase">Harga Satuan</th>
                                    <th  class="text-center text-uppercase">Disc (%)</th>
                                    <th  class="text-center text-uppercase">Total</th>
                                </tr>
                            </thead>
                            <tbody>
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
                                        <td style="vertical-align:top !important;width: 300px !important; word-wrap: break-word; overflow-wrap: break-word; white-space: normal;">
                                            {{ $di->product }}
                                            <small>
                                                {!! $di->productPartNumber != NULL ? '<br> PN/Spec: '.$di->productPartNumber : '<br> PN/Spec: -' !!} <br>
                                                {{ $di->productBrand != NULL ? 'Brand: '.$di->productBrand : 'Brand: -' }}
                                            </small>
                                        </td>
                                        @endif
                                        <td style="vertical-align: top !important;width: 250px !important; word-wrap: break-word; overflow-wrap: break-word; white-space: normal;">
                                            {!! $di->specification !!}
                                        </td>                                                        
                                        <td class="text-right" style="border-right:0 !important; vertical-align:top !important; width: 35px !important;">
                                            {{ $di->qty }}
                                        </td>
                                        <td class="text-left" style="border-left:0 !important; vertical-align:top !important; width: 35px !important;">
                                            {{ $di->measure }}
                                        </td>
                                        <td style="vertical-align:top !important; width: 150px !important; word-wrap: break-word; overflow-wrap: break-word; white-space: normal;">
                                            <div class="currency" data-content="{{ $sup->currencysymbol }}"> {{  format_number($di->price) }} </div>
                                        </td>
                                        <td class="text-center" style="vertical-align:top !important; width: 40px !important; word-wrap: break-word; overflow-wrap: break-word; white-space: normal;">
                                            @if($sup->discount_amount == 0 && $sup->discount_type == 0)
                                                <div class="currency" data-content="{{ $sup->currencysymbol }}"> </div>
                                            @endif
                                            {{ format_number($di->discount) }}
                                        </td>
                                        <td class="text-right" style="vertical-align:top !important; width: 150px !important; word-wrap: break-word; overflow-wrap: break-word; white-space: normal;">
                                            <div class="currency" data-content="{{ $sup->currencysymbol }}">
                                                <?php
                                                    $total = $di->price * $di->qty - (($di->price * $di->qty) *  $di->discount / 100);
                                                ?>
                                                {{ format_number($total) }}
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
                                            <td colspan="2" style="border-top: none !important; border-right: none !important; border-bottom: none !important;"></td>
                                        @endif
                                        <td colspan="3" rowspan="6" style="border-bottom:0 !important;border-top:0 !important; border-left:0 !important; vertical-align:top !important;">
                                            <table>
                                                <tr>
                                                    <td class="border-0 p-0" style="border:none !important">Payment Method</td>
                                                    <td class="border-0 p-0" style="border:none !important">: {{ $sup->payment_method ?? ' -' }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="border-0 p-0" style="border:none !important">Price Term</td>
                                                    <td class="border-0 p-0" style="border:none !important">: {{ $sup->price_term }} {{ $sup->price_term_location }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="border-0 p-0" style="border:none !important">Payment Term</td>
                                                    <td class="border-0 p-0" style="border:none !important">: {{ $sup->payment_term }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="border-0 p-0" style="border:none !important">Waktu Pengiriman</td>
                                                    <td class="border-0 p-0" style="border:none !important">: {!! $sup->estimated_delivery_day ? $sup->estimated_delivery_day.' Hari' : ' -' !!}</td>
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
                                            <td colspan="2" style="border-top: none !important; border-right: none !important; border-bottom: none !important;"></td>                                            
                                        @endif
                                        <td colspan="2" style="height: 60px;">Discount
                                            @if ($sup->discount_item == false &&  $sup->discount_type == 1 &&  $sup->discount_amount != 0)
                                                <small>({{ $sup->discount_amount  }}%)</small>
                                            @endif
                                        </td>
                                        <td class="text-right">
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
                                            <td colspan="2" style="border-top: none !important; border-right: none !important; border-bottom: none !important;"></td>
                                        @endif
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
                                            <td colspan="2" style="border-top: none !important; border-right: none !important; border-bottom: none !important;"></td>
                                        @endif
                                        <td colspan="2" style="height: 60px;">PPN @if($sup->ppn!=0)<small>({{$sup->ppn}} %)</small>@endif</td>
                                        <td class="text-right">
                                            <div class="currency" data-content="{{ $sup->currencysymbol }}">
                                                <?php
                                                    $ppn_ = $sup->ppn ?? 0;
                                                    $ppn = 	($ppn_ / 100) * $netto;
                                                    echo $ppn ? format_number($ppn) : '0,00';
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
                                            <td colspan="2" style="border-top: none !important; border-right: none !important; border-bottom: none !important;"></td>
                                        @endif
                                        <td colspan="2" style="height: 60px;">Biaya Kirim <small>@if($send_expense_ppn_caption)({{ $send_expense_ppn_caption }})@endif</small></td>
                                        <td class="text-right">
                                            <div class="currency" data-content="{{ $sup->currencysymbol }}">
                                                {{ $send_expense ? format_number($send_expense) : '0,00' }}
                                            </div>
                                        </td>
                                    </tr>
                                    <tr class="">
                                        @if($index_sup == 1)
                                            <td colspan="2" style="border-top: none !important; border-right: none !important; border-bottom: none !important;"></td>
                                        @endif
                                        <td colspan="2" style="height: 60px;">Total</td>
                                        <td class="text-right">
                                            <div class="currency" data-content="{{ $sup->currencysymbol }}">
                                                <?php
                                                $total =  $netto +  $ppn + $send_expense;
                                                echo $total ? format_number($total) : '0,00';
                                                ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr class="">
                                        @if($index_sup == 1)
                                            <td colspan="2" style="border-top: none !important; border-right: none !important; border-bottom: none !important;"></td>
                                        @endif
                                        <td colspan="3" style="border-bottom:0 !important;border-top:0 !important; border-left:0 !important">
                                            @if($dph->status == 1)
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
                                            <div class="currency" data-content="{{ $sup->currencysymbol }}"> {{ ($sup->currency=='IDR') ? format_number(($total * $sup->dp_percentage)/100) ?? '0,00' : format_number(($total * $sup->dp_percentage)/100) ?? '0,00' }} </div>
                                        </td>
                                    </tr>
                                    <tr class="see-more">
                                        <td colspan="8" style="text-align: left;"><i style="color:#a9e1b6;background-color:#c3e6cb" class="ti-layout-width-full"></i> : Item yang akan terbit PO</td>
                                    </tr>
                            </tbody>
                        </table>
                    </div>
                    @php
                        $index_sup = $index_sup + 1;
                    @endphp
                </div>
            @endforeach

        </div>
        @if($dph->status == 2)
       {!! Form::model($dph, [
            'action' => ['Approval\DphController@update', $dph->id],
            'method' => 'post',
            'class' => 'form-horizontal mt-3',
            'id'    => 'formPR',
            'files' => true
            ])
            !!}

            <div class="form-group mt-3">
                <label>Catatan Perbaikan</label>
            </div>
            <div class="form-group">
                <input id="form-message" type="hidden" name="message" value="{{null}}">
                <div style="width: 100%;">
                    <trix-editor input="form-message"></trix-editor>
                </div>
            </div>

            <div class="mt-4">
                <input class="btn btn-info text-uppercase fsz-sm fw-600 float-right" type="submit" name="save" value="Perbaiki" id="btn-draft">
                <input type="hidden" value="0" name="status">
                <input type="hidden" value="{{ $dph->id }}" name="id">
                <input class="btn btn-danger text-uppercase fsz-sm fw-600" type="submit" name="publish" id="btn-submit" value="Approve">
            </div>

        {!! Form::close() !!}
        @endif
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

        $(document).on('click', "#btn-draft", function(e) {
            e.preventDefault();
            var _this = $(this);
            var form = _this.parents('form');
            var message = $("#form-message").val();

            if (!message) {
                Swal.fire(
                    'Informasi',
                    'Untuk Memperbaiki DPH Catatan Harus Diisi',
                    'warning'
                );
                return;
            }

            $('<input>').attr({
                name: _this.attr('name'),
                value: _this.val()
            }).appendTo(form);

            form.submit();

        });
    });
</script>
@stop
