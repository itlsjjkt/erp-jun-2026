<!DOCTYPE html>
<html>

<head>
    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
            margin: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            padding: 6px;
            border: 1px solid #000;
            text-align: left;
        }

        .header {
            text-align: center;
            font-weight: bold;
            margin-bottom: 10px;
            font-size: 14px;
        }

        .highlight {
            background-color: #f7d770;
            font-weight: bold;
        }

        /* .page-break { page-break-after: always; } */
    </style>
</head>

<body>

    <div class="header" style="text-align: center; margin-bottom: 20px;">
        <h2 style="text-transform: uppercase; font-weight: bold;">Sirkular Invoice</h2>
        <h2 style="text-transform: uppercase; font-weight: bold;">{{ $invoice->nama_pt }}</h2>
    </div>

    <table border="1" cellpadding="6" cellspacing="0" width="100%" style="border-collapse: collapse; font-size: 14px;">
        <tr>
            <td style="height: 35px;">No Sirkular Invoice</td>
            <td colspan="2" style="height: 35px;">{{ $invoice->doc_no }}</td>
        </tr>
        <tr>
            <td style="height: 35px;">Nama Supplier</td>
            <td colspan="2" style="height: 35px;">{{ strtoupper($invoice->nama_supplier) }}</td>
        </tr>
        <tr>
            <td style="height: 35px; width: 30%;">No PO / Tgl PO</td>
            <td style="height: 35px; width: 30%;">{{ $invoice->po_number }}</td>
            <td style="height: 35px; width: 30%;">{{ date('d/m/Y', strtotime($invoice->po_tgl)) }}</td>
        </tr>
        <tr>
            <td style="height: 35px;">No PR / Tgl PR</td>
            <td style="height: 35px;">{{ $invoice->pr_no }}</td>
            <td style="height: 35px;">{{ date('d/m/Y', strtotime($invoice->pr_tgl)) }}</td>
        </tr>
        <tr>
            <td style="height: 35px;">Tgl Terima Invoice Ext / Tgl Invoice Ext</td>
            <td style="height: 35px;">{{ date('d/m/Y', strtotime($invoice->date_received_invoice)) }}</td>
            <td style="height: 35px;">{{ date('d/m/Y', strtotime($invoice->date_invoice_ext)) }}</td>
        </tr>
        <tr>
            <td style="height: 35px;">No Invoice Ext / Faktur Pajak</td>
            <td style="height: 35px;">{{ $invoice->invoice_number_ext }}</td>
            <td style="height: 35px;">{{ $invoice->tax_invoice }}</td>
        </tr>
        <tr>
            <td style="height: 35px;">No Surat Jalan / Tgl Jatuh Tempo</td>
            <td style="height: 35px;">{{ date('d/m/Y', strtotime($invoice->date_delivery_note)) }}</td>
            <td style="height: 35px;background-color: yellow;">{{ date('d/m/Y', strtotime($invoice->due_date_payment)) }}</td>
        </tr>
        <tr>
            <td style="height: 35px;">Termin Pembayaran</td>
            <td colspan="2" style="height: 35px;">{{ strtoupper($invoice->payment_terms_nama) }}</td>
        </tr>
        <tr>
            <td style="height: 70px;">Jumlah / Notes</td>
            <td style="height: 70px;"><strong>{{ $invoice->po_mata_uang }}
                    {{ number_format($invoice->payment_amount, 2, ',', '.') }}</strong></td>
            <td style="height: 70px;"> <small>{!! $invoice->note !!}</small> </td>
        </tr>
    </table>

    <br>

    <table>
        <thead>
            <tr>
                <th style="height: 30px;">No</th>
                <th style="width: 100px; height: 30px;">Diterima Oleh</th>
                <th style="width: 50px; height: 30px;">Dept</th>
                <th style="width: 70px; height: 30px;">Tgl Terima</th>
                <th style="height: 30px;">TTD</th>
                <th style="width: 70px; height: 30px;">Tgl Selesai</th>
                <th style="width: 200px; height: 30px;">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="height: 20px;"></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td style="height: 20px;"></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td style="height: 20px;"></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
        </tbody>
    </table>

</body>

</html>
