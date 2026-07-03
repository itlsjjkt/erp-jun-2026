<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Verifikasi Dokumen - {{ config('app.company_name', 'ERP Shipping') }}</title>
    <link href="{{ mix('/css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('/css/custom.css') }}" rel="stylesheet">
    <style>
        body { background:#f2f4f7; color:#212529; }
        .verify-wrap { max-width:820px; margin:0 auto; padding:12px; }
        .verify-card { background:#fff; border-radius:10px; box-shadow:0 1px 4px rgba(0,0,0,.08); padding:18px; margin-bottom:14px; }
        .verify-banner { border-radius:10px; padding:14px 16px; margin-bottom:14px; font-weight:bold; text-align:center; }
        .verify-banner.ok { background:#e6f6ea; color:#1b7d34; border:1px solid #bfe6c9; }
        .verify-banner.pending { background:#fff6e5; color:#9a6a00; border:1px solid #ffe2ad; }
        .verify-head { display:flex; align-items:center; gap:12px; flex-wrap:wrap; }
        .verify-head img { width:120px; height:auto; }
        .verify-company { font-weight:bold; font-size:16px; text-transform:uppercase; }
        .verify-title { font-size:15px; font-weight:bold; text-decoration:underline; margin-top:2px; }
        .verify-docno { font-family:monospace; font-size:15px; }
        .info-row { display:flex; padding:2px 0; }
        .info-row .label { width:150px; flex:none; color:#6b7280; }
        .info-row .val { flex:1; font-weight:600; word-break:break-word; }
        .section-title { font-weight:bold; border-bottom:2px solid #e5e7eb; padding-bottom:6px; margin-bottom:10px; }
        .table-responsive { overflow-x:auto; }
        table.items { width:100%; border-collapse:collapse; font-size:13px; }
        table.items th, table.items td { border:1px solid #e5e7eb; padding:6px 8px; vertical-align:top; }
        table.items th { background:#f8fafc; text-align:left; }
        .history li { margin-bottom:6px; }
        .verify-footer { text-align:center; color:#9aa0a6; font-size:11px; padding:8px 0 20px; }
        @media (max-width:576px){
            .info-row { flex-direction:column; }
            .info-row .label { width:auto; }
        }
    </style>
</head>

<body>
    <div class="verify-wrap">

        {{-- STATUS --}}
        @if($verifiedAt)
            <div class="verify-banner ok">&#10003; DOKUMEN TERVERIFIKASI</div>
        @else
            <div class="verify-banner pending">&#9203; DOKUMEN BELUM TERVERIFIKASI</div>
        @endif

        {{-- HEADER --}}
        <div class="verify-card">
            <div class="verify-head">
                <img src="{{ asset('images/logo-shipping.jpeg') }}" alt="Logo">
                <div>
                    <div class="verify-company">{{ config('app.company_name') }}</div>
                    <div>{!! config('app.company_address') !!}</div>
                    <div>Telp: {{ config('app.company_telp') }} &nbsp; Website: {{ config('app.company_web') }}</div>
                    <div class="verify-title">@yield('doc_title')</div>
                    <div class="verify-docno">@yield('doc_no')</div>
                </div>
            </div>
        </div>

        {{-- INFO --}}
        <div class="verify-card">
            <div class="section-title">Informasi Dokumen</div>
            @yield('info')
        </div>

        {{-- ITEMS --}}
        <div class="verify-card">
            <div class="section-title">Rincian Barang</div>
            <div class="table-responsive">
                @yield('items')
            </div>
        </div>

        {{-- HISTORY --}}
        <div class="verify-card">
            <div class="section-title">Riwayat Dokumen</div>
            <ul class="history" style="margin:0; padding-left:18px;">
                <li>Dibuat oleh <strong>{{ $createdBy ?: '-' }}</strong>
                    @if($createdAt) pada <strong>{{ \Carbon\Carbon::parse($createdAt)->format('d M Y H:i:s') }}</strong> @endif
                </li>
                @if($verifiedAt)
                    <li>Diverifikasi oleh <strong>{{ $verifiedBy ?: '-' }}</strong>
                        pada <strong>{{ \Carbon\Carbon::parse($verifiedAt)->format('d M Y H:i:s') }}</strong></li>
                @else
                    <li>Belum diverifikasi.</li>
                @endif
                @yield('history_extra')
            </ul>
        </div>

        <div class="verify-footer">
            Halaman verifikasi resmi &mdash; dibuka {{ \Carbon\Carbon::now()->format('d M Y H:i:s') }}
        </div>
    </div>
</body>
</html>
