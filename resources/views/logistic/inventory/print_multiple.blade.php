<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Cetak Barcode Multiple</title>
    <link rel="icon" href="{!! asset('images/favicon.ico') !!}" />
    <style>
        @page {
            size: {{ $tinggiMM * 3 }}mm {{ $tinggiMM }}mm;
            margin: 0;
        }

        html,
        body {
            margin: 0;
            padding: 1mm;
            font-family: sans-serif;
            font-size: {{ $tinggiMM / 3.7 }}px;
            height: 99%;
            justify-content: center;
            align-items: center;
            display: flex;
        }
    </style>

</head>
@foreach ($result as $data)

    <body>
        <table style="margin-left:0.5mm;margin-top:1.5mm;border: 0.5mm solid #000;width:99%;" cellspacing="0">
            <tr>
                <td style="width:30%;">
                    <img src="{{ $data->id }}" alt="QR Code" type="png">
                </td>
                <td style="vertical-align: top;">
                    <strong style="font-size:{{ $tinggiMM / 2 }}px;margin-left:1mm">{{ $data->doc_no .' ('.$data->lokasi_kode.'-'.$data->company_kode.')' }}</strong><br>
                    <table cellspacing="2" style="width:100%;margin-top:2px;margin-left:2px;">
                        <tr>
                            <td style="width:17%;vertical-align:top;">
                                PRODUCT
                            </td>
                            <td style="width:1%;vertical-align:top;">:</td>
                            <td>
                                {{ $data->produk ? strtoupper($data->produk) : ' -' }}
                            </td>
                        </tr>
                        <tr>
                            <td style="width:17%;vertical-align:top;">
                                PN/SPEC
                            </td>
                            <td style="width:1%;vertical-align:top;">:</td>
                            <td>
                                {{ $data->produkpn ? Str::limit(strtoupper($data->produkpn), 50, ' ...') : ' -' }}
                            </td>
                        </tr>
                        <tr>
                            <td style="width:17%;vertical-align:top;">
                                UOM
                            </td>
                            <td style="width:1%;vertical-align:top;">:</td>
                            <td>
                                {{ $data->measure ? strtoupper($data->measure) : ' -' }}
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </body>
@endforeach

</html>
