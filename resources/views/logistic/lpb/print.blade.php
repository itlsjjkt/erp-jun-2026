<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>ERP SHIPPING</title>
    <link href="{{ mix('/css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('/css/custom.css') }}" rel="stylesheet">
    <style>
        .table td {
            padding: .3rem !important;
        }
        .print-footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            font-size: 11px;
            color: #888;
            border-top: 1px solid #ddd;
            padding: 4px 20px;
            display: flex;
            justify-content: space-between;
            background: white;
        }
    </style>
</head>

<body>

    {{-- FOOTER --}}
    <div class="print-footer">
        <span>{{ $lpb->doc_no }} - {{ \Carbon\Carbon::now()->format('d M Y H:i:s') }}</span>
        @if($lpb->verified_at)
            <span>Verified by {{ getUserByID($lpb->verified_by) }} pada {{ \Carbon\Carbon::parse($lpb->verified_at)->format('d M Y H:i:s') }}</span>
        @endif
    </div>

    <div class="mB-40">
        <div class="p-30">

            {{-- HEADER --}}
            <table class="table border-0">
                <tr>
                    <td class="border-0 p-0" style="width:200px">
                        <img src="{{ asset('images/logo-shipping.jpeg') }}" alt="Logo" style="width:200px;left:10px;">
                    </td>
                    <td class="border-0" style="width:60%">
                        <span class="text-uppercase" style="font-weight:bold;font-size:18px">{{ config('app.company_name') }}</span><br>
                        {!! config('app.company_address') !!}<br>
                        Telp: {{ config('app.company_telp') }} Website: {{ config('app.company_web') }}
                    </td>
                    <td class="border-0 text-right" style="white-space:nowrap;">
                        <span style="font-weight:bold; font-size:17px; text-decoration:underline;">LAPORAN PENERIMAAN BARANG</span><br>
                        {{ $lpb->doc_no }}
                    </td>
                </tr>
            </table>

            {{-- INFO --}}
            <table class="table border-0">
                <tr>
                    <td class="border-0" style="width:45%; vertical-align:top;">
                        <table class="border-0 table">
                            <tr>
                                <td class="border-0 p-0" style="width:150px;">No. PO</td>
                                <td class="border-0 p-0">: {{ $lpb->po_no }}</td>
                            </tr>
                            <tr>
                                <td class="border-0 p-0">No. PR</td>
                                <td class="border-0 p-0">: {{ $lpb->pr_no }}</td>
                            </tr>
                            <tr>
                                <td class="border-0 p-0">No. DPM</td>
                                <td class="border-0 p-0">: {{ $lpb->dpm_no }}</td>
                            </tr>
                            <tr>
                                <td class="border-0 p-0">Kapal / Departement</td>
                                <td class="border-0 p-0">: {{ $lpb->department }}</td>
                            </tr>
                            <tr>
                                <td class="border-0 p-0">Supplier</td>
                                <td class="border-0 p-0">: {{ $lpb->supplier }}</td>
                            </tr>
                        </table>
                    </td>
                    <td class="border-0" style="width:20%; vertical-align:top;"></td>
                    <td class="border-0" style="width:35%; vertical-align:top;">
                        <table class="border-0 table">
                            <tr>
                                <td class="border-0 p-0">Dibuat Oleh</td>
                                <td class="border-0 p-0">: {{ $lpb->created }}</td>
                            </tr>
                            <tr>
                                <td class="border-0 p-0">Tgl Pembuatan LPB</td>
                                <td class="border-0 p-0">: {{ date('d M Y H:i:s', strtotime($lpb->created_at)) }}</td>
                            </tr>
                            <tr>
                                <td class="border-0 p-0">Penerima</td>
                                <td class="border-0 p-0">: {{ $lpb->received_by }}</td>
                            </tr>
                            <tr>
                                <td class="border-0 p-0">Status Verifikasi</td>
                                <td class="border-0 p-0">:
                                    @if($lpb->verified_at)
                                        <span style="color:green; font-weight:bold;">TERVERIFIKASI</span>
                                    @else
                                        <span style="color:orange; font-weight:bold;">BELUM TERVERIFIKASI</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
            <div style="font-weight:bold;">
                CHECKED BY : {{ strtoupper($lpb->received_by) }}
            </div>

            {{-- TABLE ITEM --}}
            <table class="table table-bordered mt-4">
                <thead>
                    <tr>
                        <th rowspan="2" style="width:50px">No</th>
                        <th rowspan="2" style="width:300px">Nama Barang</th>
                        <th rowspan="2">Spesifikasi</th>
                        <th colspan="2" class="text-center">Jumlah</th>
                        <th rowspan="2" style="width:75px" class="text-center">Satuan</th>
                        <th rowspan="2" class="text-center">Catatan</th>
                    </tr>
                    <tr>
                        <th style="width:75px" class="text-center">Dipesan</th>
                        <th style="width:75px" class="text-center">Diterima</th>
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
                                    PN : {!! $item->productPartNumber ? $item->productPartNumber : '-' !!} <br>
                                    Brand : {!! $item->productBrand ? $item->productBrand : '-' !!}
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

            {{-- TANDA TANGAN --}}
            @php $sameUser = $lpb->verified_at && $lpb->created_by == $lpb->verified_by; @endphp
            <table class="table table-bordered mt-4"
                style="width:{{ $sameUser ? '25%' : '50%' }}; margin-left:auto; margin-right:0;">
                <thead>
                    <tr>
                        @if($sameUser)
                            <th class="text-center">Prepared & Verified By</th>
                        @else
                            <th class="text-center">Prepared By</th>
                            <th class="text-center">Verified By</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        @if($sameUser)
                            <td style="height:80px; vertical-align:bottom; text-align:center;">
                                <small>{{ \Carbon\Carbon::parse($lpb->created_at)->format('d M Y H:i') }}</small>
                            </td>
                        @else
                            <td style="height:80px; vertical-align:bottom; text-align:center;">
                                <small>{{ \Carbon\Carbon::parse($lpb->created_at)->format('d M Y H:i') }}</small>
                            </td>
                            <td style="height:80px; vertical-align:bottom; text-align:center;">
                                <small>
                                    @if($lpb->verified_at)
                                        {{ \Carbon\Carbon::parse($lpb->verified_at)->format('d M Y H:i') }}
                                    @else
                                        -
                                    @endif
                                </small>
                            </td>
                        @endif
                    </tr>
                    <tr>
                        @if($sameUser)
                            <td class="text-center">{{ $lpb->created }}</td>
                        @else
                            <td class="text-center">{{ $lpb->created }}</td>
                            <td class="text-center">
                                @if($lpb->verified_at)
                                    {{ getUserByID($lpb->verified_by) }}
                                @else
                                    -
                                @endif
                            </td>
                        @endif
                    </tr>
                </tbody>
            </table>

        </div>
    </div>

</body>
</html>
