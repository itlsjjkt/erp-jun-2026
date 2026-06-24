@extends('layouts.app')

@section('page-header')
	Approval Purchase Order
@stop

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('approval.po.index') }}">Approval Purchase Order</a></li>
        <li class="breadcrumb-item active" aria-current="page">Approval</li>
    </ol>
@endsection


@section('content')

<div class="mB-40">
    {!! Form::model($po, [
            'action' => ['Approval\PoController@update', $po->id],
            'method' => 'post',
            'class' => 'form-horizontal mt-3',
            'id'    => 'formPR',
            'files' => true
        ])
    !!}

    <input name="pr_id" type="hidden" value="{{ $po->purchase_id }}">

		<div class="bgc-white p-30 bd">
            <div class="d-block">
                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-toggle="tab" href="#tab1" role="tab">Purchase Order</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#tab3" role="tab">Histori</a>
                    </li>
                </ul>
            </div>

            <div class="tab-content mt-5" >
                <div class="tab-pane active" id="tab1" role="tabpanel" aria-labelledby="tab1">
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="row">
                                <label class="col-sm-4">No. PO </label>
                                <div class="col-sm-7">: {{ $po->doc_no }}</div>
                            </div>
                            @if ($po->delivery_date != NULL)
                                <div class="row">
                                    <label class="col-sm-4">Tanggal Pengiriman </label>
                                    <div class="col-sm-7">: {{ date('d/m/Y',strtotime( $po->delivery_date)) }} </div>
                                </div>
                            @endif
                            <div class="row">
                                <label class="col-sm-4">Dibuat Oleh</label>
                                <div class="col-sm-7">: {{ $po->created }} [ {{ idDate($po->created_at) }}]
                                </div>
                            </div>
                            <div class="row">
                                <label class="col-sm-4">Department/Kapal</label>
                                <div class="col-sm-7">: {{ $po->department }}</div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="row">
                                <label class="col-sm-3">No. PR </label>
                                <div class="col-sm-8">: {{ $po->pr_no }}</div>
                            </div>
                            <div class="row">
                                <label class="col-sm-3">Supplier</label>
                                <div class="col-sm-8">:
                                    {{ $po->supplier }}
                                </div>
                            </div>
                            <div class="row">
                                <label class="col-sm-3">Supplier Kontak</label>
                                <div class="col-sm-8">:
                                    {{ $po->picTitle }} {{ $po->picName }} / <small class="ml-2"> Telp. {{ $po->picTelp }}  Email. {{ $po->picEmail }}  </small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <h6 class="mT-30">Daftar Barang</h6>
                    <table class="table table-bordered">
                        <thead>
                            <th style="width:60px">No</th>
                            <th style="width:100px">Jumlah</th>
                            <th>Nama Barang</th>
                            <th>Spesification</th>
                            <th style="width:150px">Harga Satuan</th>
                            <th style="width:70px">Disc (%)</th>
				            <th style="width:150px">Total</th>
                        </thead>
                        <tbody>
                                @php
                                    $no = 1;
                                    $subtotal = 0;
							    @endphp
                                @foreach ($po_items as $item)
                                <tr>
                                    <td class="text-center">
                                        {{ $no }}
                                    </td>
                                    <td class="text-center">{{ $item->qty }} {{ $item->measure }}</td>
									<td style="min-width:300px">{{ $item->product }} {!! $item->productPartNumber != NULL ? '<br> PN/Spec: '.$item->productPartNumber : '' !!}
                                    <br>
                                    <a href="#" value="{{ action('Purchasing\PoController@getItems',['id'=>$item->product_id]) }}" class="icon-lg modalMdPO ml-1" data-toggle="modal" data-target="#modalHistoryPO"><span class="ti-shopping-cart text-muted"></span></a>
                                    </td>
                                    <td style="min-width:150px">
										{!! $item->specification !!} <br>
                                        {{ $item->productBrand != NULL ? 'Brand: '.$item->productBrand : '' }}
									</td>
                                    <td><div class="currency" data-content="{{ $po->currencysymbol }}"> {{ number_format($item->price,2,".",',') }} </div></td>
									<td class="text-center">{{ $item->discount }}</td>
                                    <td class="text-right">
                                        <div class="currency" data-content="{{ $po->currencysymbol }}">
                                            <?php
												$total= $item->price * $item->qty - (($item->price * $item->qty) *  $item->discount /100);
												echo number_format($total,2,".",',')
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
                                    <td colspan="2" class="border-0">Payment Method</td>
                                    <td colspan="2" class="border-0">: {{ $po->payment_method }}</td>
                                    <td colspan="2" >Sub Total</td>
                                    <td class="text-right"> <div class="currency" data-content="{{ $po->currencysymbol }}">  {{ number_format($subtotal,2,".",',') }} </div></td>
                                </tr>
                                <tr>
                                    <td colspan="2" class="border-0">Price Term</td>
                                    <td colspan="2" class="border-0">: {{ $po->price_term }}  {{ $po->price_term_location }}</td>
                                    <td colspan="2" >Discount
                                        <?php
                                            if($po->discount_type == 1){
                                                echo "<small>".$po->discount_amount."%</small>";
                                            }
                                        ?>
                                    </td>
                                    <td class="text-right">
                                        <?php
                                            $total_discount = $po->discount_amount;
                                            if($po->discount_type == 1){
                                                $total_discount = $subtotal * ($po->discount_amount/100);
                                            }
                                        ?>
                                        <div class="currency" data-content="{{ $po->currencysymbol }}"> {{ number_format($total_discount,2,".",',') }} </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2" class="border-0">Payment Term</td>
                                    <td colspan="2" class="border-0">: {{ $po->payment_term }}</td>
                                    <td colspan="2" >Netto</td>
                                    <td class="text-right">
                                        <div class="currency" data-content="{{ $po->currencysymbol }}">
                                        <?php
                                            $netto = $subtotal - $total_discount;
                                            echo  number_format($netto,2,".",',');
                                        ?>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2" class="border-0">Mata Uang</td>
                                    <td colspan="2" class="border-0">: {{ $po->currency }}</td>
                                    <td colspan="2" >PPH <small>{{ $po->pph }} %</small></td>
                                    <td class="text-right">
                                        <div class="currency" data-content="{{ $po->currencysymbol }}">
                                            <?php
                                                $pph = 	($po->pph/100) * $netto;
                                                echo number_format($pph,2,".",',');
                                            ?>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2" class="border-0" style="vertical-align:top !important">Waktu Pengiriman</td>
                                    <td colspan="2" class="border-0" style="vertical-align:top !important">: {{ $po->estimated_delivery_day != 0 ? $po->estimated_delivery_day . ' Hari' : ' -' }}</td>
                                    <td colspan="2" >PPN <small>({{$po->ppn}}%)</small></td>
                                    <td class="text-right">
                                        <div class="currency" data-content="{{ $po->currencysymbol }}">
                                            <?php
                                            $ppn = 0;
                                            if($po->ppn != 0){
                                                $ppn = 	($po->ppn/100) * $netto;
                                                echo number_format($ppn,2,".",',');
                                            }
                                            ?>
                                        </div>
                                    </td>
                                </tr>

                                <?php
                                    $send_expense = 0;
                                    $send_expense_ppn_caption ='';
                                    if($po->send_expense_ppn == 1){
                                        $send_expense_ppn = (11/100) * $po->send_expense;
                                        $send_expense = $send_expense_ppn + $po->send_expense;
                                        $send_expense_ppn_caption = "+ PPN 11%";
                                    }else{
                                        $send_expense = $po->send_expense;
                                    }
                                ?>
                                <tr>
                                    <td colspan="2"  rowspan="3" class="border-0" style="vertical-align:top !important">Notes</td>
                                    <td colspan="2"  rowspan="3" class="border-0" style="vertical-align:top !important"> {!! $po->notes ?? '<div>: -</div>' !!}</td>
                                    <td colspan="2" >Biaya Kirim <small>{{ $send_expense_ppn_caption }}</small></td>
                                    <td class="text-right">
                                        <div class="currency" data-content="{{ $po->currencysymbol }}">
                                            {{  number_format($send_expense,2,".",',') }}
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2" >Total</td>
                                    <td class="text-right">
                                        <div class="currency" data-content="{{ $po->currencysymbol }}">
                                            <?php
                                                $total =  $netto + $pph + $ppn + $send_expense;
                                                echo number_format($total ,2,".",',');
                                            ?>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2" >Down Payment</td>
                                    <td class="text-right"><div class="currency" data-content="{{ $po->currencysymbol }}"> {{ number_format($po->down_payment,2,".",',') }} </div></td>
                                </tr>
                        </tbody>

                    </table>

                    <div class="form-group">
                        <label>Catatan </label>
                         {!! Form::textarea('message', null, ['class' => 'form-control', 'rows' => 2, 'placeholder' => '']) !!}
                    </div>
                </div>

                <div class="tab-pane" id="tab3" role="tabpanel" aria-labelledby="tab3">
                    <div class="timeline">
                        <div class="timeline__group">
                            <?php foreach($po_history as $val){?>
                                <div class="timeline__box">
                                    <div class="timeline__date"></div>
                                    <div class="timeline__post">
                                        <div class="timeline__content">
                                            <?php if($val->date_approved==NULL){
                                                $employeeName = $val->employee;
                                                echo "<span>". date('d/m/Y H:i A',strtotime($val->created_at)). "</span><br>";
                                                echo "<strong>".ucwords(strtolower($employeeName))."</strong> ";
                                                if($val->jenis=='insert'){
                                                    echo  "melakukan pengajuan PO ";
                                                }elseif($val->jenis=='draft'){
                                                    echo  "melakukan pengajuan PO dengan status Draft</p>";
                                                }else{
                                                    echo  "melakukan perubahan PO ";
                                                }
                                            }else{
                                                $employeeName = $val->employee;
                                                if($val->jenis=='done'){
                                                    echo  "<p>Approval PO disetujui <br> oleh <strong>".ucwords(strtolower($employeeName))."</strong> pada tanggal ".date('d/m/Y H:i A',strtotime($val->date_approved))." (FINAL)</p>";
                                                    echo  "<p><strong>Catatan: </strong>".$val->message."</p>";
                                                }else{
                                                    echo  "<p>Approval PO <br> oleh <strong>".ucwords(strtolower($employeeName))."</strong> pada tanggal ".date('d/m/Y H:i A',strtotime($val->date_approved))."</p>";
                                                    echo  "<p><strong>Catatan: </strong>".$val->message."</p>";
                                                }
                                            } ?>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>

		    </div>
		</div>


        <div class="mt-4">
            <a href="{{ route('approval.po.index') }}" class="btn btn-light text-uppercase fsz-sm fw-600 mr-1">{{ trans('Cancel') }}</a>
            <input class="btn btn-info text-uppercase fsz-sm fw-600 float-right" type="submit" name="save" value="Perbaiki" id="btn-draft">
            <input type="hidden" value="0" name="status">
            <input class="btn btn-danger text-uppercase fsz-sm fw-600" type="submit" name="publish" id="btn-submit" value="Approve">
            <!-- <input class="btn btn-success text-uppercase fsz-sm fw-600" type="submit" name="publish" id="btn-submit-print" value="Approve & PRINT"> -->
        </div>

	{!! Form::close() !!}
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


@stop


@section('js')

<script  type='text/javascript'>
    function printExternal(url) {
        var printWindow = window.open(url, 'Print', 'left=200, top=200, width=950, height=800, toolbar=0, resizable=0');
        printWindow.addEventListener('load', function() {
            printWindow.print();
        }, true);
    }

	$(document).ready(function() {

        $(document).on("click", "#btn-submit", function(e) {
            $('input[name="status"]').val('1');
        });

        $(document).on("click", "#btn-draft", function(e) {
            $('input[name="status"]').val('0');
        });

        $("#formPR").validate({
            rules: {
                "pr_item[]": {
                    required: true,
                    minlength: 1
                }
            },
            messages: {
                    "pr_item[]": "Minimal Checklist 1 Item"
            }
        });


        $(document).on('click', "#btn-submit-print", function(e) {
            $('input[name="status"]').val('1');
            var _this = $(this);
            var form = _this.parents('form');

            form.validate({
                onfocusout: false,
                invalidHandler: function(form, validator) {
                    var errors = validator.numberOfInvalids();
                    if (errors) {
                        validator.errorList[0].element.focus();
                    }
                }
            });

            e.preventDefault();
            if (form.valid() ) {
                Swal.fire({
                    title: 'Konfirmasi', // Opération Dangereuse
                    text: 'Apakah anda yakin melanjutkan ini?', // Êtes-vous sûr de continuer ?
                    type: 'question',
                    showCancelButton: true,
                    confirmButtonColor: 'null',
                    cancelButtonColor: 'null',
                    confirmButtonClass: 'btn btn-danger',
                    cancelButtonClass: 'btn btn-primary',
                    confirmButtonText: 'Ya, lanjut', // Oui, sûr
                    cancelButtonText: 'Batal', // Annuler
                }).then(res => {
                    if (res.value) {
                       _this.closest("form").submit();
                        var url = '/purchasing/po_print/{{ Hashids::encode($po->id) }}/print';
                        printExternal(url);
                    }
                });
            }
        });


    });
</script>

@stop
