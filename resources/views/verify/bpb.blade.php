@php
    $verifiedAt = $bpb->verified_at;
    $verifiedBy = $bpb->verified_at ? getUserByID($bpb->verified_by) : null;
    $createdAt  = $bpb->created_at;
    $createdBy  = $bpb->created;
@endphp

@extends('verify.layout')

@section('doc_title', 'BUKTI PENERIMAAN BARANG (JAKARTA)')
@section('doc_no', $bpb->doc_no)

@section('info')
    <div class="info-row"><div class="label">Nomor SPB</div><div class="val">{{ $bpb->noSPB }}</div></div>
    <div class="info-row"><div class="label">Penerima</div><div class="val">{{ $bpb->received_by }}</div></div>
@endsection

@section('items')
    <table class="items">
        <thead>
            <tr>
                <th style="width:40px">No</th>
                <th>Nama Barang</th>
                <th style="width:90px">QTY</th>
                <th>Nomor DPM</th>
                <th>Nomor PO</th>
                <th>Nomor LPB</th>
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
                    <td>{{ $item->noDPM }}</td>
                    <td>{{ $item->noPO }}</td>
                    <td>{{ $item->noLPB }}</td>
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
