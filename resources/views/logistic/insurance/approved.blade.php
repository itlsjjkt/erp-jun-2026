@extends('layouts.app')

@section('page-header')
    Asuransi
@stop

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('logistic.insurance.index') }}"> Asuransi</a></li>
        <li class="breadcrumb-item active" aria-current="page">Approval</li>
    </ol>
@endsection

@section('content')

<div class="mB-40">
        <div class="bgc-white p-30 bd">
            <h6 class="text-center font-weight-bold mB-40">== APPROVAL ASURANSI ==</h6>
            <div class="row">
                <div class="col-sm-7"> 
                    <div class="row">
                        <label class="col-sm-4">COMPANY</label>
                        <div class="col-sm-7">: {{ $insurance->company ?? '-' }}</div>
                    </div>
                    <div class="row">
                        <label class="col-sm-4">PROJECT</label>
                        <div class="col-sm-7">: {{ $insurance->project ?? '-' }}</div>
                    </div>
                    <div class="row">
                        <label class="col-sm-4">INSURANCE NUMBER</label>
                        <div class="col-sm-7">: {{ $insurance->doc_no }}</div>
                    </div>
                    <div class="row">
                        <label class="col-sm-4">MANIFEST NUMBER</label>
                        <div class="col-sm-7">
                            @php
                                $previousNoSPB = null;
                            @endphp
                            @foreach($insurance_items as $val)
                                @if ($val->noSPB != $previousNoSPB)
                                    <li>{{ $val->noSPB }}</li>
                                @endif
                                @php
                                    $previousNoSPB = $val->noSPB;
                                @endphp
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="col-sm-5">
                    <div class="row">
                        <label class="col-sm-4">EKSPEDISI / FORWARDER</label>
                        <div class="col-sm-7">: {{ $insurance->expedition_forwarder ?? '-' }}
                        </div>
                    </div>
                    <div class="row">
                        <label class="col-sm-4">RISK LOCATION</label>
                        <div class="col-sm-7">: {{ $insurance->risk_location ?? '-' }}
                        </div>
                    </div>
                    <div class="row">
                        <label class="col-sm-4">ETD / ETA</label>
                        <div class="col-sm-7">: {{ $insurance->etd_eta ? idDate2($insurance->etd_eta) : '-' }}
                        </div>
                    </div>
                    <div class="row">
                        <label class="col-sm-4">READY TO SHIPPED BY</label>
                        <div class="col-sm-7">: {{ $insurance->shipped_by ?? '-' }}
                        </div>
                    </div>
                    <div class="row">
                        <label class="col-sm-4">Dibuat Oleh</label>
                        <div class="col-sm-7">: {{  $insurance->created ?? '-' }} [ {{ $insurance->created_at ? idDate($insurance->created_at) : '-' }}]
                        </div>
                    </div>
                </div>
            </div>
    
            <h6 class="mT-30">Daftar Barang</h6>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 60%" colspan="4">DIKIRIM</th>
                        <th class="text-center" colspan="7">ASURANSI</th>
                    </tr>
                    <tr>
                        <th rowspan="2">Nama Barang</th>
                        <th rowspan="2" style="width:300px">Spesifikasi</th>
                        <th colspan="2" class="text-center" style="width: 150px !important;">Item</th>
                        <th rowspan="2" class="text-center" style="width: 150px !important;">Harga / Item</th>
                        <th rowspan="2" class="text-center" style="width:60px">Disc(%)</th>
                        <th rowspan="2" class="text-center" style="width:60px">PPN(%)</th>
                        <th rowspan="2" class="text-center" style="width: 200px !important;">Total</th>
                    </tr>
                    <tr>
                        <th class="text-center" style="">QTY</th>
                        <th class="text-center" style="">UOM</th>
                    </tr>
                </thead>
                <tbody>
                        @php
                            $no = 1;
                            $totalharga = 0;
                        @endphp
                        <?php $akhir = 0; ?>
                        @foreach ($insurance_items as $item)
                            @php
                                $total= $item->price * $item->qtyKoli - (($item->price * $item->qtyKoli) *  $item->discount /100);
                                if($item->ppn == 1){
                                    $subtotal = $total + ( $total * 11/100);
                                }else{
                                    $subtotal = $total;
                                   }
    
                                $totalharga += $subtotal;
                            @endphp
                            <tr>
                                <td>{{'['.$item->productCode.']'}} <br>{{$item->product}}<br> <small>{{$item->productBrand?'Brand : '.$item->productBrand : 'Brand : -'}}</small></td>
                                <td>{!!$item->productPartNumber?$item->productPartNumber:'-'!!}</td>
                                <td class="text-center">{{$item->qtyKoli}}</td>
                                <td class="text-center">{{$item->measure}}</td>
                                <td><div class="currency" data-content="{{$item->symbol.'.'}}">{{number_format($item->price,2,",",'.')}}</div></td>
                                <td class="text-center">{{$item->discount}}</td>
                                <td class="text-center">{{$item->ppn}}</td>
                                <?php
                                    $harga_diskon = $item->price * (1-($item->discount/100));
                                    $harga_ppn = $harga_diskon * ($item->ppn/100);
                                    $total = ($harga_diskon + $harga_ppn ) * $item->qtyKoli;
                                    $akhir += $total;
                                    $mataUang = $item->symbol;
                                ?>
                                <td style="text-align: right">
                                    <div class="currency" data-content="{{$item->symbol.'.'}}">{{number_format($total,2,",",'.')}}</div>
                                </td>
                                </style>
                            </tr>
                        @php
                            $no++;
                        @endphp
                        @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="6"></td>
                        <td colspan="1" class="text-center font-weight-bold">TOTAL</td>
                        <td colspan="1" class="text-right font-weight-bold"><div class="currency" data-content="{{$mataUang.'.'}}">{{number_format($akhir ,2,",",'.')}}</div></td>
                    </tr>
                </tfoot>
            </table>
            <hr>
            {!! Form::open(['method' => 'POST', 'route' => ['logistic.insurance.update_approved'],'files' => true ]) !!}
            <div class="col-12 row">
                <div class="col-6">
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label text-right">Acc </label>
                        <div class="col-sm-8">
                            <input type="checkbox" name="is_acc" class="switch switch-info" id="is_acc" value="{{false}}">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label text-right">File </label>
                        <div class="col-sm-8">
                            {!! Form::file('mr_file', ['class' => 'form-control', 'accept' => '.pdf', 'required' => false]) !!}
                        </div>
                    </div>                    
                </div>
                <div class="col-6">
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label text-right">Notes </label>
                        <div class="col-sm-8">
                            <input id="notes" type="hidden" name="notes" class="form-control" value="{{$insurance->notes}}">
                            <div style="width: 250px;">
                                <trix-editor input="notes"></trix-editor>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <input type="hidden" name="id" value="{{$insurance->id}}">
            <input id="status_" type="hidden" name="status_" value="0">
            <div class="mb-4">
                <input class="btn btn-danger text-uppercase float-right"  type="submit" name="publish" id="btn-submit" value="Issued">
            </div>
        </div>
    {!! Form::close() !!}
</div>
	
@stop


@section('js')

<script  type='text/javascript'>
	$(document).ready(function() {
        $("#status_").val(3);
        $('#is_acc').on('ifChecked', function(){
            $("#status_" ).val(2);
        });
        $('#is_acc').on('ifUnchecked', function(){
            $("#status_" ).val(3);
        });
    });
    </script>
@stop

