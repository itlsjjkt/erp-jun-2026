@php
    $verifiedAt = $lpb->verified_at;
    $verifiedBy = $lpb->verified_at ? getUserByID($lpb->verified_by) : null;
    $createdAt  = $lpb->created_at;
    $createdBy  = $lpb->created;
@endphp

@extends('verify.layout')

@section('doc_title', 'LAPORAN PENERIMAAN BARANG')
@section('doc_no', $lpb->doc_no)

@section('info')
    <div class="info-row"><div class="label">No. PO</div><div class="val">{{ $lpb->po_no }}</div></div>
    <div class="info-row"><div class="label">No. PR</div><div class="val">{{ $lpb->pr_no }}</div></div>
    <div class="info-row"><div class="label">No. DPM</div><div class="val">{{ $lpb->dpm_no }}</div></div>
    <div class="info-row"><div class="label">Kapal / Departement</div><div class="val">{{ $lpb->department }}</div></div>
    <div class="info-row"><div class="label">Supplier</div><div class="val">{{ $lpb->supplier }}</div></div>
    <div class="info-row"><div class="label">Penerima</div><div class="val">{{ $lpb->received_by }}</div></div>
@endsection

@section('items')
    <table class="items">
        <thead>
            <tr>
                <th style="width:40px">No</th>
                <th>Nama Barang</th>
                <th>Spesifikasi</th>
                <th style="width:70px" class="text-center">Dipesan</th>
                <th style="width:70px" class="text-center">Diterima</th>
                <th style="width:70px">Satuan</th>
                <th>Catatan</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @foreach ($lpb_items as $item)
                <tr>
                    <td>{{ $no }}</td>
                    <td>
                        [{{ $item->productCode }}] - {{ $item->product }}<br>
                        <small>
                            PN : {!! $item->productPartNumber ?: '-' !!}<br>
                            Brand : {!! $item->productBrand ?: '-' !!}
                        </small>
                    </td>
                    <td>{!! $item->specification !!}</td>
                    <td class="text-center">{{ $item->qtyPO }}</td>
                    <td class="text-center">{{ $item->qty }}</td>
                    <td>{{ $item->measure }}</td>
                    <td>{{ $item->notes }}</td>
                </tr>
                @php $no++; @endphp
            @endforeach
        </tbody>
    </table>
@endsection
