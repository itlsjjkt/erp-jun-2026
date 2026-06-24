@php
    use Carbon\Carbon;

    $fmtDate = function ($v) {
        return !empty($v) ? Carbon::parse($v)->format('d F Y') : '-';
    };

    $fmtNum = function ($v, $dec = 2) {
        return is_numeric($v) ? number_format($v, $dec, ',', '.') : '0,00';
    };

    // Info header dari $pc — bukan dari $components
    $company  = strtoupper($pc->nama_company ?? '');
    $supplier = $pc->nama_supplier ?? '-';
    $noPc     = $pc->no_pc ?? ($pc->doc_no ?? '');
    $noPo     = $pc->no_po ?? '-';
    $tglPo    = $fmtDate($pc->tgl_po ?? null);
    $noPr     = $pc->no_pr ?? '-';
    $tglPr    = $fmtDate($pc->tgl_pr ?? null);
    $currency = $pc->po_mata_uang ?? 'IDR';
    $termin   = $pc->payment_terms_nama ?? '-';
    $pairs    = $pairs ?? [];

    // Hitung total nilai invoice dari semua pairs
    $totalNilaiInvoice = collect($pairs)->sum(function ($p) {
        return is_numeric($p['nilai_invoice'] ?? null) ? $p['nilai_invoice'] : 0;
    });

    // Hitung total harga PO
    $total = 0;
    $poPo  = getDataByID('po', $pc->po_id);
    use App\Models\PurchaseOrder;
    $po_items = PurchaseOrder::getProductItem($poPo->id);
    foreach ($po_items as $item) {
        $total += $item->price * $item->qty - (($item->price * $item->qty) * $item->discount / 100);
    }
    if ($poPo->discount_item == false) {
        if ($poPo->discount_type == 1) {
            $poPo->discount_amount = $total * ((float)$poPo->discount_amount / 100);
        }
        $netto = $total - (float)$poPo->discount_amount;
    } else {
        $netto = $total;
    }
    if ((float)$poPo->send_expense_ppn == 1 || (float)$poPo->send_expense_ppn == 11) {
        $send_expense_ppn   = (11 / 100) * (float)$poPo->send_expense;
        $poPo->send_expense = (float)$send_expense_ppn + (float)$poPo->send_expense;
    }
    $poPo->ppn      = $netto * (float)$poPo->ppn / 100;
    $poPo->pph      = $netto * (float)$poPo->pph / 100;
    $payment_amount = $netto - (float)$poPo->pph + (float)$poPo->ppn + (float)$poPo->send_expense;

    // Timestamp
    $bulan     = ['','Januari','Februari','Maret','April','Mei','Juni',
                  'Juli','Agustus','September','Oktober','November','Desember'];
    $now       = Carbon::now();
    $printTime = '- Data ' . $now->day . ' ' . $bulan[$now->month] . ' ' . $now->year
               . ' ' . $now->format('H:i:s') . ' -';
@endphp

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>PAYMENT COMPLETION - CBD/COD/DP {{ $noPc }}</title>
    <style>
        @page { margin: 10mm 7mm 15mm 7mm; }

        body {
            font-family: sans-serif;
            font-size: 12px;
            margin: 20px;
            color: #000;
        }

        .header {
            text-align: center;
            font-weight: bold;
            margin-bottom: 10px;
            font-size: 14px;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 10px;
        }

        td, th {
            padding: 6px;
            vertical-align: middle;
            height: 35px;
        }

        .label {
            width: 30%;
            font-weight: bold;
        }

        .bordered td,
        .bordered th {
            border: 1px solid #000;
        }

        .mt-10 { margin-top: 10px; }

        .table-header th {
            background-color: #dbdbdb;
            text-align: center;
            font-weight: bold;
            height: 30px;
            border: 1px solid #000;
        }

        .text-center { text-align: center; }

        .print-timestamp {
            position: fixed;
            bottom: 2mm;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 10px;
            color: #555;
        }
    </style>
</head>
<body>

    {{-- Header --}}
    <div class="header" style="margin-bottom:20px;">
        <h2 style="text-transform:uppercase; font-weight:bold; margin:0;">PAYMENT COMPLETION - CBD / COD / DP</h2>
        <h2 style="text-transform:uppercase; font-weight:bold; margin:0;">{{ $company }}</h2>
    </div>

    {{-- Info Dokumen --}}
    <table class="bordered">
        <tr>
            <td class="label">No PC</td>
            <td colspan="3">{{ $noPc }}</td>
        </tr>
        <tr>
            <td class="label">Supplier</td>
            <td colspan="3">{{ $supplier }}</td>
        </tr>
        <tr>
            <td class="label">No PO / Tgl PO</td>
            <td colspan="2">{{ $noPo }}</td>
            <td>{{ $tglPo }}</td>
        </tr>
        <tr>
            <td class="label">No PR / Tgl PR</td>
            <td colspan="2">{{ $noPr }}</td>
            <td>{{ $tglPr }}</td>
        </tr>
        <tr>
            <td class="label">Termin Pembayaran</td>
            <td colspan="3">{{ $termin }}</td>
        </tr>
        <tr>
            <td class="label">Jumlah Nilai Invoice</td>
            <td colspan="2">{{ $currency }} {{ $fmtNum($totalNilaiInvoice) }}</td>
            <td>-</td>
        </tr>
        <tr>
            <td class="label">Nilai PO</td>
            <td colspan="3">{{ $currency }} {{ $fmtNum($payment_amount,($currency == 'IDR'?0:2)) }}
                
            </td>
        </tr>
    </table>

    <div class="mt-10"></div>

    {{-- Tabel Invoice --}}
    <table class="bordered">
        <thead>
            <tr class="table-header">
                <th class="text-center" style="width:10%;">No</th>
                <th style="{{ $pc->is_form_faktur == 1 ? 'width:35%;' : ($pc->is_form_proforma == 1 ? 'width:55%;' : 'width:90%;') }}">Invoice</th>
                @if ($pc->is_form_faktur == 1)
                    <th style="width:30%;">Faktur Pajak</th>
                @endif
                @if ($pc->is_form_proforma == 1)
                    <th style="width:30%;">Proforma Invoice</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @php $invSum = 0; @endphp
            @forelse ($pairs as $i => $row)
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td style="vertical-align:top;">
                        <strong>{{ $row['invoice'] ?: '-' }}</strong><br>
                        Nilai Invoice: {{ $currency }} {{ $fmtNum($row['nilai_invoice']) }}<br>
                        Tgl Akhir Bayar: {{ $fmtDate($row['tgl_jatuh_tempo'] ?? null) }}<br>
                        @if (!empty($row['detail_notes']))
                            Catatan: {{ $row['detail_notes'] }}<br>
                        @endif
                        @if ($row['invoice_verify_status'] == 1)
                            <small>
                                Verify by: {{ $row['invoice_verify_user'] }}<br>
                                Verify at: {{ idDate($row['invoice_verify_date'], 'd F Y H:i:s') }}
                            </small>
                        @endif
                    </td>
                    @if ($pc->is_form_faktur == 1)
                        <td style="vertical-align:top;">
                            @if ($row['faktur_pajak'])
                                <strong>{{ $row['faktur_pajak'] }}</strong><br>
                                @if ($row['faktur_verify_status'] == 1)
                                    <small>
                                        Verify by: {{ $row['faktur_verify_user'] }}<br>
                                        Verify at: {{ idDate($row['faktur_verify_date'], 'd F Y H:i:s') }}
                                    </small>
                                @endif
                            @else
                                -
                            @endif
                        </td>
                    @endif
                    @if ($pc->is_form_proforma == 1)
                        <td style="vertical-align:top;">
                            <strong>{{ $row['proforma_invoice'] ?? '-' }}</strong><br>
                            Nilai: {{ $currency }} {{ $fmtNum($row['nilai_proforma_invoice'] ?? null) }}
                        </td>
                    @endif
                </tr>
                @php $invSum += ($row['nilai_invoice'] ?? 0); @endphp
            @empty
                <tr>
                    <td colspan="{{ 2 + ($pc->is_form_faktur == 1 ? 1 : 0) + ($pc->is_form_proforma == 1 ? 1 : 0) }}" class="text-center">
                        Tidak Ada Invoice / Faktur Pajak
                    </td>
                </tr>
            @endforelse
            @if (count($pairs) > 0)
                <tr>
                    <td class="text-center" style="font-weight:bold;">Total</td>
                    <td style="font-weight:bold;">{{ $currency }} {{ $fmtNum($invSum) }}</td>
                    @if ($pc->is_form_faktur == 1)
                        <td></td>
                    @endif
                    @if ($pc->is_form_proforma == 1)
                        <td></td>
                    @endif
                </tr>
            @endif
        </tbody>
    </table>

    {{-- Timestamp halaman utama --}}
    <div class="print-timestamp">{{ $printTime }}</div>

    {{-- Page break sebelum history --}}
    <div style="page-break-after:always;"></div>

    {{-- ── HISTORY — halaman tersendiri ── --}}
    <div class="header" style="margin-bottom:20px;">
        <h3 style="font-weight:bold; margin:0;">RIWAYAT DOKUMEN</h3>
        <p style="margin:4px 0 0 0; font-size:11px;">{{ $noPc }}</p>
    </div>
    <table class="bordered">
        <thead>
            <tr class="table-header">
                <th style="width:50px;">No</th>
                <th style="width:150px;">Tanggal</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($historyPc as $i => $hispc)
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td style="padding-left:10px;">
                        {{ idDate($hispc->created_at, 'd F Y H:i:s') }}
                    </td>
                    <td style="padding-left:10px;">{{ $hispc->message }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Timestamp halaman history --}}
    <div class="print-timestamp">{{ $printTime }}</div>

</body>
</html>
