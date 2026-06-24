<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Styles -->
    <link href="{{ mix('/css/app.css') }}" rel="stylesheet"> 
    <link href="{{ asset ('/css/custom.css') }}" rel="stylesheet"> 

    <!-- PERBESAR FONT WEIGHT DAN FONT SIZE -->
    <style>
        body, table, td, th, span, strong {
            font-size: 16pt !important;    /* PERBESAR SEDIKIT */
        }

    </style>

</head>

<body>

<div class="mB-40">
    <div class="p-30">
        <table class="table border-0">
            <tr>
                <td class="border-0 p-0" style="width:200px"> 
                    <img src="{{ asset('storage'.$ttb->companyLogo) }}" alt="Logo" style="width:180px">
                </td>
                <td class="border-0" style="width:60%;"> 
                    <strong style="font-size: 32pt !important;">{{ strtoupper($ttb->company) }}</strong><br>
                    <span style="font-size: 24pt !important;">SITE {{ $ttb->location }}</span>
                </td>
                <td class="border-0 text-right">
                    <span class="text-bold" style="font-size: 19px; text-decoration:underline;"> 
                        TANDA TERIMA BARANG 
                    </span> <br>
                    {{ $ttb->doc_no }}
                </td>
            </tr>
        </table>
    
        <table class="table border-0 mt-5">
            <tr>
                <td class="border-0" style="width:150px"> 
                    Tanggal TTB
                </td>
                <td class="border-0">
                    : {{ date('d/m/Y',strtotime( $ttb->date_transaction)) }}
                </td>
            </tr>
            <tr>
                <td class="border-0"> 
                    Project
                </td>
                <td class="border-0">
                    : {{ $ttb->project }}
                </td>
            </tr>
            <tr>
                <td class="border-0"> 
                    Department/Kapal
                </td>
                <td class="border-0">
                    : {{ $ttb->department }} 
                </td>
            </tr>
        </table>
        
        Telah diterima Barang dari Logistik dengan spesifikasi dibawah ini:
        
        <table class="table table-bordered mt-2">
            <thead>
                <th class="text-uppercase" style="width:80px">No</th>
                <th class="text-uppercase" style="width:500px">Item</th>
                <th class="text-center text-uppercase" style="width:150px" colspan="2">QTY</th>
                <th class="text-uppercase">Catatan</th>
            </thead>
            <tbody class="item_form" id="itemDPM">
                @php  $no = 1; @endphp
                @foreach($ttb_items as $item) 
                    <tr class="product_1">
                        <td>{{ $no }}</td>
                        <td>
                            {{ $item->productCode }} - {{ $item->productName }} <br>
                            {!! $item->productPartNumber != NULL ? '<small> PN: '.$item->productPartNumber.'</small>' : '' !!}
                        </td>
                        <td class="text-right" style="border-right:0 !important">{{ $item->qty }}</td>
                        <td class="text-left" style="border-left:0 !important">{{ $item->unit }}</td>
                        <td>{{ $item->description }}</td>
                    </tr>
                    @php  $no++ @endphp
                @endforeach
            </tbody>
        </table>

        <table class="table border-0 mt-5">
            <tr>
                <td class="border-0" style="width:50%"> 
                    {{ $ttb->location }}, {{ date('d F Y',strtotime( $ttb->created_at)) }} <br>
                    <strong>Yang Menyerahkan,</strong><br><br><br><br>
                    {{ $ttb->operator }}
                </td>
                <td class="border-0" style="width:50%">
                    <br><br><strong>Yang Menerima,</strong><br><br><br><br>
                    {{ $ttb->received }}<br>
                </td>
            </tr>
        </table>

    </div>
</div>

</body>

</html>
