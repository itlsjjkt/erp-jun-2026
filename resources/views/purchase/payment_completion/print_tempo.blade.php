@php
    use Carbon\Carbon;

    $fmt = function ($v) {
        return !empty($v) ? Carbon::parse($v)->format('d F Y') : '-';
    };

    $fmtNum = function ($v, $dec = 2) {
        return is_numeric($v) ? number_format($v, $dec) : 'N/A';
    };

    // Info header dari PC — diambil dari $pc, bukan $components
    $company  = strtoupper($pc->nama_company ?? '');
    $noPo     = $pc->no_po ?? '-';
    $tglPo    = $fmt($pc->tgl_po ?? null);
    $noPr     = $pc->no_pr ?? '-';
    $tglPr    = $fmt($pc->tgl_pr ?? null);
    $supplier = strtoupper($pc->nama_supplier ?? '-');
    $currency = $pc->po_mata_uang ?? '-';
    $note     = $pc->notes ?? '';
    $termin   = $pc->payment_terms_nama ?? '-';

    // Hitung total harga PO
    $total = 0;
    $poN   = getDataByID('po', $pc->po_id);
    use App\Models\PurchaseOrder;
    $po_items = PurchaseOrder::getProductItem($poN->id);
    foreach ($po_items as $item) {
        $total += $item->price * $item->qty - (($item->price * $item->qty) * $item->discount / 100);
    }
    if ($poN->discount_item == false) {
        if ($poN->discount_type == 1) {
            $poN->discount_amount = $total * ((float)$poN->discount_amount / 100);
        }
        $netto = $total - (float)$poN->discount_amount;
    } else {
        $netto = $total;
    }
    if ((float)$poN->send_expense_ppn == 1 || (float)$poN->send_expense_ppn == 11) {
        $poN->send_expense += (11 / 100) * (float)$poN->send_expense;
    }
    $poN->ppn       = $netto * (float)$poN->ppn / 100;
    $poN->pph       = $netto * (float)$poN->pph / 100;
    $payment_amount = $netto - (float)$poN->pph + (float)$poN->ppn + (float)$poN->send_expense;

    // $components = array[$index][$component_key] = value (multi-row)

    // Timestamp saat dokumen di-generate/print
    $bulan = ['','Januari','Februari','Maret','April','Mei','Juni',
              'Juli','Agustus','September','Oktober','November','Desember'];
    $now       = Carbon::now();
    $printTime = '- Data ' . $now->day . ' ' . $bulan[$now->month] . ' ' . $now->year
               . ' ' . $now->format('H:i:s').' -';
@endphp

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>SIRKULAR INVOICE {{ $pc->doc_no }}</title>
    <style>
        @page { margin: 10mm 7mm 7mm 7mm; }

        body {
            font-family: sans-serif;
            font-size: 12px;
            margin: 20px;
            color: #000;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th, td {
            padding: 6px;
            border: 1px solid #000;
            text-align: left;
            vertical-align: middle;
        }

        .header {
            text-align: center;
            font-weight: bold;
            margin-bottom: 10px;
            font-size: 14px;
        }

        .highlight {
            background-color: yellow;
            font-weight: bold;
        }

        .label {
            width: 30%;
            font-weight: bold;
        }

        .table-header th {
            background-color: #dbdbdb;
            text-align: center;
            font-weight: bold;
            height: 30px;
            border: 1px solid #000;
        }

        .table-body td {
            height: 25px;
            border: 1px solid #000;
            padding: 4px;
        }

        .page-break {
            page-break-after: always;
        }

        .text-center { text-align: center; }
        .text-right  { text-align: right; }

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

    {{-- ── LOOP SEMUA ROW / INDEX ── --}}
    @foreach ($components as $index => $comp)
        @php
            $noSi             = $comp['no_si'] ?? '';
            $invoice          = $comp['invoice'] ?? '-';
            $nilaiInvoice     = $comp['nilai_invoice'] ?? null;
            $tglInvoice       = $fmt($comp['tgl_invoice'] ?? null);
            $tglTerimaInvoice = $fmt($comp['tgl_terima_invoice'] ?? null);
            $periodeTempo     = $comp['periode_tempo'] ?? 0;
            $tglSuratJalan    = $fmt($comp['tgl_surat_jalan'] ?? null);
            $tglJatuhTempo    = $fmt($comp['tgl_jatuh_tempo'] ?? null);
            $fakturPajak         = $comp['faktur_pajak'] ?? '-';
            $detailNotes         = $comp['detail_notes'] ?? '';
            $proformaInvoice     = $comp['proforma_invoice'] ?? '-';
            $nilaiProforma       = $comp['nilai_proforma_invoice'] ?? null;
            $totalRows           = count($components);
        @endphp

        {{-- Header --}}
        <div class="header" style="margin-bottom:20px;">
            <h2 style="text-transform:uppercase; font-weight:bold; margin:0;">SIRKULAR INVOICE</h2>
            <h2 style="text-transform:uppercase; font-weight:bold; margin:0;">{{ $company }}</h2>
            {{-- @if ($totalRows > 1)
                <p style="margin:4px 0 0 0; font-size:13px;">
                    Form {{ $loop->iteration }} dari {{ $totalRows }}
                </p>
            @endif --}}
        </div>

        {{-- Info Dokumen --}}
        <table>
            <tr>
                <td class="label" style="height:35px;">No Sirkular Invoice</td>
                <td colspan="2" style="height:35px; text-align:center; font-weight:bold;">
                    {{ $noSi ?: $pc->doc_no }}
                </td>
            </tr>
            <tr>
                <td class="label" style="height:35px;">Supplier</td>
                <td colspan="2" style="height:35px; font-weight:bold; text-align:center;">
                    {{ $supplier }}
                </td>
            </tr>
            <tr>
                <td class="label" style="height:35px;">No PO / Tgl PO</td>
                <td style="height:35px;">{{ $noPo }}</td>
                <td style="height:35px;">{{ $tglPo }}</td>
            </tr>
            <tr>
                <td class="label" style="height:35px;">No PR / Tgl PR</td>
                <td style="height:35px;">{{ $noPr }}</td>
                <td style="height:35px;">{{ $tglPr }}</td>
            </tr>
            <tr>
                <td class="label" style="height:35px;">Tgl Surat Jalan / Tgl Invoice</td>
                <td style="height:35px;">{{ $tglSuratJalan }}</td>
                <td style="height:35px;">{{ $tglInvoice }}</td>
            </tr>
            <tr>
                <td class="label" style="height:35px;">No Invoice / No Faktur Pajak</td>
                <td style="height:35px;">{{ $invoice }}</td>
                <td style="height:35px;">{{ $fakturPajak }}</td>
            </tr>
            <tr>
                <td class="label" style="height:35px;">Nilai Invoice</td>
                <td colspan="2" style="height:35px; font-weight:bold;">
                    {{ $currency }} {{ $fmtNum($nilaiInvoice) }}
                </td>
            </tr>
            <tr>
                <td class="label" style="height:35px;">Periode Jatuh Tempo</td>
                <td colspan="2" style="height:35px;">
                    {{ $periodeTempo }} Hari
                </td>
            </tr>
            <tr>
                <td class="label" style="height:35px;">Tgl Terima Invoice / Tgl Jatuh Tempo</td>
                <td style="height:35px;">{{ $tglTerimaInvoice }}</td>
                <td style="height:35px;" class="highlight">{{ $tglJatuhTempo }}</td>
            </tr>
            <tr>
                <td class="label" style="height:70px;">Total</td>
                <td style="height:70px;">
                    <strong>{{ $currency }} {{ $fmtNum($payment_amount,($currency == 'IDR'?0:2)) }}</strong>
                </td>
                <td style="height:70px;">
                    @if (!empty($detailNotes))
                        <small>{!! $detailNotes !!}</small>
                    @endif
                </td>
            </tr>
            @if ($pc->is_form_proforma == 1)
                <tr>
                    <td class="label" style="height:35px;">No Proforma Invoice</td>
                    <td colspan="2" style="height:35px;">{{ $proformaInvoice }}</td>
                </tr>
                <tr>
                    <td class="label" style="height:35px;">Nilai Proforma Invoice</td>
                    <td colspan="2" style="height:35px; font-weight:bold;">
                        {{ $currency }} {{ $fmtNum($nilaiProforma) }}
                    </td>
                </tr>
            @endif
        </table>

        {{-- Timestamp print --}}
        <div class="print-timestamp">{{ $printTime }}</div>

        {{-- Page break antar row --}}
        <div class="page-break"></div>

    @endforeach

    {{-- ── HISTORY — halaman tersendiri setelah semua row ── --}}
    <div class="header" style="margin-bottom:20px;">
        <h3 style="font-weight:bold; margin:0;">RIWAYAT DOKUMEN</h3>
        <p style="margin:4px 0 0 0; font-size:11px;">{{ $pc->doc_no }}</p>
    </div>
    <table>
        <thead>
            <tr class="table-header">
                <th style="width:50px;">No</th>
                <th style="width:150px;">Tanggal</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody class="table-body">
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

    {{-- Timestamp print halaman history --}}
    <div class="print-timestamp">{{ $printTime }}</div>

</body>
</html>
