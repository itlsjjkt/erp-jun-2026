<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Barcode {{ $data->doc_no }}</title>
    <link rel="icon" href="{!! asset('images/favicon.ico') !!}"/>
    <style>
        @page {
            size: {{ ($tinggiMM * 3)}}mm {{ $tinggiMM }}mm;
            margin: 0;
        }

        body {
            margin: 0;
            padding: 1mm;
            padding-top: 1.5mm !important;
            font-family: sans-serif;
            font-size: {{ $tinggiMM / 3.7 }}px;
            height: 99%;
            text-align: center;
            vertical-align: middle;
            display: table-cell;
        }
    </style>
</head>
<body>
    <table style="margin-left:0.5mm;border: 0.5mm solid #000;width:99%;" cellspacing="0">
        <tr>
            <td style="width:30%;">
                <img src="{{ $qrCodeImage }}" alt="QR Code" type="png">
            </td>
            <td style="vertical-align: top;">
                <strong style="font-size:{{$tinggiMM/2.6}}px;margin-left:1mm">{{$data->doc_no}}</strong><br>
                <table cellspacing="2" style="width:100%;margin-top:0.5mm;margin-left:0.5mm;">
                    <tr>
                        <td style="width:17%;vertical-align:top;">
                            PRODUCT
                        </td>
                        <td style="width:1%;vertical-align:top;">:</td>
                        <td>
                            {{$data->produk? strtoupper($data->produk): ' -'}}
                        </td>
                    </tr>
                    <tr>
                        <td style="width:17%;vertical-align:top;">
                            CODE
                        </td>
                        <td style="width:1%;vertical-align:top;">:</td>
                        <td>
                            {{$data->produkcode? strtoupper($data->produkcode) : ' -'}}
                        </td>
                    </tr>
                    <tr>
                        <td style="width:17%;vertical-align:top;">
                            PN/SPEC
                        </td>
                        <td style="width:1%;vertical-align:top;">:</td>
                        <td>
                            {{$data->produkpn? strtoupper($data->produkpn):' -'}}
                        </td>
                    </tr>
                    <tr>
                        <td style="width:17%;vertical-align:top;">
                            UOM
                        </td>
                        <td style="width:1%;vertical-align:top;">:</td>
                        <td>
                            {{$data->measure? strtoupper($data->measure):' -'}}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
