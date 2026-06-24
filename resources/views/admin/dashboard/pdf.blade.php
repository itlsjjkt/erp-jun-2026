<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <style type="text/css">
        @page { margin: 20px 20px 0px 20px; }
        body {
            font-size: 9pt;
        }
        table.table-bordered {
            font-size: 9pt;
            border-left: 0.01em solid #333;
            border-top: 0.01em solid #333;
            border-collapse: collapse;
            width: 100%;
        }
        table.table-bordered td,
        table.table-bordered th {
            border-right: 0.01em solid #333;
            border-bottom: 0.01em solid #333;
            padding: 5px 8px;
        }
        .no-border {
            font-size: 9pt;
            border-left: none;
            border-right: none;
            border-bottom: none;
            border-top: none;
            border-collapse: none;
            width: 100%;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .text-left {
            text-align: left;
        }
        .text-uppercase {
            text-transform: uppercase;
        }
    </style>
</head>
<body>
    <br><br><br><br>
    <div class="text-center" style="vertical-align: middle;">
        DATA JUMLAH DOKUMEN ERP [{{strtoupper($bulanNama.'/'.$tahun)}}]
    </div>
    <hr>
    <br>
    <table class="table table-bordered" style="width: 100%;">
        <thead>
            <tr>
                <th rowspan="2">X</th>
                <th rowspan="2" style="width: 25%;">DPM</th>
                <th rowspan="2" style="width: 20%;">PR</th>
                <th colspan="2" style="width: 25%;">PO</th>
                <th colspan="2" style="width: 25%;">PO Done BPB</th>
            </tr>
        </thead>
        <thead>
            <tr>
                <th>Jakarta</th>
                <th>Lokal</th>
                <th>Jakarta</th>
                <th>Lokal</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td></td>
                <td style="vertical-align: top; width:23%;">
                    <table class="no-border" style="width: 100%;">
                        <tr>
                            {{-- status dpm 0 --}}
                            <td style="border: none">Draft</td>
                            <td style="border: none">: {{$cdpm0}}</td>
                        </tr>
                        <tr>
                            {{-- status dpm 1, item=>status=1,pr_status=0 --}}
                            <td style="border: none">On Progress Approval</td>
                            <td style="border: none">: {{$capdpm}}</td>
                        </tr>
                        <tr>
                            {{-- status dpm 4 + 5 + 1 - oprog approval --}}
                            <td style="border: none">PR Issued</td>
                            <td style="border: none">: {{$cdpm4+$cdpm1+$cdpm5-$capdpm}}</td>
                        </tr>
                        <tr>
                            {{-- status dpm 6 & 3 --}}
                            <td style="border: none">Revisi / Hold</td>
                            <td style="border: none">: {{$cdpm3+$cdpm6}}</td>
                        </tr>
                        <tr>
                            {{-- status dpm 2 --}}
                            <td style="border: none">Reject</td>
                            <td style="border: none">: {{$cdpm2}}</td>
                        </tr>
                    </table>
                </td>                               
                <td style="vertical-align: top; width:23%;">
                    <table class="no-border" style="width: 100%;">
                        <tr>
                            <td style="border: none">On Progress PR</td>
                            <td style="border: none">: {{$cpr0+$cpr1+$cpr2+$cpr3+$cprNull+$cpr6}}</td>
                        </tr>
                        <tr>
                            <td style="border: none">Done</td>
                            <td style="border: none">: {{$cpr4}}</td>
                        </tr>
                        <tr>
                            <td style="border: none">Closed</td>
                            <td style="border: none">: {{$cpr5}}</td>
                        </tr>
                    </table>
                </td>
                <td style="vertical-align: top; width:23%;">
                    <table class="no-border" style="width: 100%;">
                        <tr>
                            <td style="border: none">On Progress</td>
                            <td style="border: none">: {{$cpoj0+$cpoj1+$cpoj2+$cpoj3+$cpoj4}}</td>
                        </tr>
                        <tr>
                            <td style="border: none">Done</td>
                            <td style="border: none">: {{$cpoj5}}</td>
                        </tr>
                        <tr>
                            <td style="border: none">Cancel</td>
                            <td style="border: none">: {{$cpoj6}}</td>
                        </tr>
                    </table>
                </td>
                <td style="vertical-align: top; width:23%;">
                    <table class="no-border" style="width: 100%;">
                        <tr>
                            <td style="border: none">On Progress</td>
                            <td style="border: none">: {{$cpol0+$cpol1+$cpol2+$cpol3+$cpol4}}</td>
                        </tr>
                        <tr>
                            <td style="border: none">Done</td>
                            <td style="border: none">: {{$cpol5}}</td>
                        </tr>
                        <tr>
                            <td style="border: none">Cancel</td>
                            <td style="border: none">: {{$cpol6}}</td>
                        </tr>
                    </table>
                </td>
                <td style="vertical-align: top; width:23%;">
                    <table class="no-border" style="width: 100%;">
                        <tr>
                            <td style="border: none">Draft</td>
                            <td style="border: none">: {{$cbpb0}}</td>
                        </tr>
                        <tr>
                            <td style="border: none">Published</td>
                            <td style="border: none">: {{$cbpb1}}</td>
                        </tr>
                    </table>
                </td>
                <td style="vertical-align: top; width:23%;">
                    <table class="no-border" style="width: 100%;">
                        <tr>
                            <td style="border: none">Draft</td>
                            <td style="border: none">: {{$cbpbf0}}</td>
                        </tr>
                        <tr>
                            <td style="border: none">Published</td>
                            <td style="border: none">: {{$cbpbf1}}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </tbody>
        <tfoot>
            <tr>
                <td>Jumlah</td>
                <td class="text-center">{{$cdpm}}</td>
                <td class="text-center">{{$cpr}}</td>
                <td class="text-center">{{$cpoj}}</td>
                <td class="text-center">{{$cpol}}</td>
                <td class="text-center">{{$cbpb}}</td>
                <td class="text-center">{{$cbpbf}}</td>
            </tr>
        </tfoot>
    </table>
    <br><br>
    <div class="col-mb-3">
        <div style="font-weight: bold;">Keterangan :</div><br>
        <table class="no-border" style="width: 40%;">
            <tr>
                <td style="border: none">DPM</td>
                <td style="border: none">: Daftar Permintaan Material</td>
            </tr>
            <tr>
                <td style="border: none">PR</td>
                <td style="border: none">: Purchase Requisition</td>
            </tr>
            <tr>
                <td style="border: none">PO</td>
                <td style="border: none">: Purchase Order</td>
            </tr>
            <tr>
                <td style="border: none">BPB</td>
                <td style="border: none">: Bukti Penerimaan Barang</td>
            </tr>
        </table>
    </div>
    <div style="position: fixed;top: 10px;right: 10px;z-index: 9999;">
        <small>
            <span>
                Data: <?php echo date('d-M-Y H:i:s'); ?>
            </span>
        </small>        
    </div>
</body>
</html>