<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'ERP Shipping') }}</title>
    <link href="{{ mix('/css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('/css/custom.css') }}" rel="stylesheet">
    <style>
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
        <span>{{ $bpb->doc_no }} - {{ \Carbon\Carbon::now()->format('d M Y H:i:s') }}</span>
        @if($bpb->verified_at)
            <span>Verified by {{ getUserByID($bpb->verified_by) }} pada {{ \Carbon\Carbon::parse($bpb->verified_at)->format('d M Y H:i:s') }}</span>
        @endif
    </div>

    <div class="mB-40">
        <div class="p-30">
            <table class="table border-0">
                <tr>
                    <td class="border-0 p-0" style="width:200px">
                        <img src="{{ asset('images/logo-shipping.jpeg') }}" alt="Logo" style="width:200px;left:10px;">
                    </td>
                    <td class="border-0" style="width:60%">
                        <span class="text-uppercase" style="font-weight:bold;font-size:18px">{{ config('app.company_name') }}</span><br>
                        {!! config('app.company_address') !!}<br>
                        Telp: {{ config('app.company_telp') }} <br>
                        Website: {{ config('app.company_web') }}
                    </td>
                    <td class="border-0 text-right" style="white-space:nowrap;">
                        <span style="font-weight:bold; font-size:17px; text-decoration:underline;">BUKTI PENERIMAAN BARANG</span><br>
                        {{ $bpb->doc_no }}
                        @if(!empty($bpb->uuid))
                            <br>
                            {!! QrCode::size(85)->generate(route('verify.bpb', $bpb->uuid)) !!}
                            <div style="font-size:10px;color:#666;">Scan untuk verifikasi keaslian</div>
                        @endif
                    </td>
                </tr>
            </table>

            <table class="table border-0">
                <tr>
                    <td class="border-0" style="width:45%; vertical-align:top;">
                        <table class="border-0 table">
                            <tr>
                                <td class="border-0 p-0" style="width:150px;">Nomor SPB</td>
                                <td class="border-0 p-0">: {{ $bpb->noSPB }}</td>
                            </tr>
                            <tr>
                                <td class="border-0 p-0">Penerima</td>
                                <td class="border-0 p-0">: {{ $bpb->received_by }}</td>
                            </tr>
                        </table>
                    </td>
                    <td class="border-0" style="width:20%; vertical-align:top;"></td>
                    <td class="border-0" style="width:35%; vertical-align:top;">
                        <table class="border-0 table">
                            <tr>
                                <td class="border-0 p-0" style="width:150px;">Dibuat Oleh</td>
                                <td class="border-0 p-0">: {{ $bpb->created }}</td>
                            </tr>
                            <tr>
                                <td class="border-0 p-0">Tgl Pembuatan BPB</td>
                                <td class="border-0 p-0">: {{ date('d M Y H:i:s', strtotime($bpb->created_at)) }}</td>
                            </tr>
                            <tr>
                                <td class="border-0 p-0">Status Verifikasi</td>
                                <td class="border-0 p-0">:
                                    @if($bpb->verified_at)
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

            <p>Adapun perincian barang yang kami terima sebagai berikut:</p>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th style="width:50px">No</th>
                        <th style="width:300px">Nama Barang</th>
                        <th>QTY</th>
                        <th style="width:150px">Nomor DPM</th>
                        <th style="width:150px">Nomor PO</th>
                        <th style="width:150px">Nomor LPB</th>
                        <th class="text-center">Catatan</th>
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
                                    PN : {!! $item->productPartNumber ? $item->productPartNumber : '-' !!} <br>
                                    Brand : {!! $item->productBrand ? $item->productBrand : '-' !!}
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

            {{-- TANDA TANGAN --}}
            @php $sameUser = $bpb->verified_at && $bpb->created_by == $bpb->verified_by; @endphp
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
                                <small>{{ \Carbon\Carbon::parse($bpb->created_at)->format('d M Y H:i') }}</small>
                            </td>
                        @else
                            <td style="height:80px; vertical-align:bottom; text-align:center;">
                                <small>{{ \Carbon\Carbon::parse($bpb->created_at)->format('d M Y H:i') }}</small>
                            </td>
                            <td style="height:80px; vertical-align:bottom; text-align:center;">
                                <small>
                                    @if($bpb->verified_at)
                                        {{ \Carbon\Carbon::parse($bpb->verified_at)->format('d M Y H:i') }}
                                    @else
                                        -
                                    @endif
                                </small>
                            </td>
                        @endif
                    </tr>
                    <tr>
                        @if($sameUser)
                            <td class="text-center">{{ $bpb->created }}</td>
                        @else
                            <td class="text-center">{{ $bpb->created }}</td>
                            <td class="text-center">
                                @if($bpb->verified_at)
                                    {{ getUserByID($bpb->verified_by) }}
                                @else
                                    -
                                @endif
                            </td>
                        @endif
                    </tr>
                </tbody>
            </table>

            <br>
            <strong>NOTE: </strong>
            <p>{!! $bpb->notes !!}</p>
        </div>
    </div>

</body>
</html>
