@php
    $verifiedAt = $bpb->verified_at;
    $verifiedBy = $bpb->verified_at ? getUserByID($bpb->verified_by) : null;
    $createdAt  = $bpb->created_at;
    $createdBy  = $bpb->creator ? $bpb->creator->name : '-';
    $po         = $bpb->purchaseOrder;
@endphp

@extends('verify.layout')

@section('doc_title', 'BUKTI PENERIMAAN BARANG (LOKAL)')
@section('doc_no', $bpb->doc_no)

@section('info')
    <div class="info-row"><div class="label">Nomor PO</div><div class="val">{{ $po ? $po->doc_no : '-' }}</div></div>
    <div class="info-row"><div class="label">Nomor PR</div><div class="val">{{ $po && $po->purchaseRequisition ? $po->purchaseRequisition->doc_no : '-' }}</div></div>
    <div class="info-row"><div class="label">Nomor DPM</div><div class="val">{{ $po && $po->purchaseRequisition ? $po->purchaseRequisition->dpm_no : '-' }}</div></div>
    <div class="info-row"><div class="label">Supplier</div><div class="val">{{ $po && $po->supplier ? $po->supplier->name : '-' }}</div></div>
    <div class="info-row"><div class="label">Penerima</div><div class="val">{{ $bpb->received_by }}</div></div>
@endsection

@section('items')
    <table class="items">
        <thead>
            <tr>
                <th style="width:40px">No</th>
                <th>Nama Barang</th>
                <th style="width:120px">QTY</th>
                <th>Catatan</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @foreach ($bpb_items as $item)
                <tr>
                    <td>{{ $no }}</td>
                    <td>
                        [{{ $item->productCode }}] - {{ $item->product }}<br>
                        <small>
                            PN : {!! $item->productPartNumber ?: '-' !!}<br>
                            Brand : {!! $item->productBrand ?: '-' !!}
                        </small>
                    </td>
                    <td>{{ $item->qty }} {{ $item->measure }}</td>
                    <td>{!! $item->description !!}</td>
                </tr>
                @php $no++; @endphp
            @endforeach
        </tbody>
    </table>
@endsection

@section('history_extra')
    @if(!empty($bpb->notes))
        <li>Catatan: {!! $bpb->notes !!}</li>
    @endif
@endsection
