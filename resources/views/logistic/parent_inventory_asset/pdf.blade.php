<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Inventory Asset - {{ $parent->doc_no }}</title>

    <style>
        @page {
            margin: 30px 20px 30px 20px;
        }

        body {
            font-family: sans-serif;
            font-size: 9pt;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            display: table-header-group;
        }

        tbody {
            display: table-row-group;
        }

        th, td {
            border: 1px solid #333;
            padding: 5px 8px;
            vertical-align: top;
        }

        thead th {
            background-color: #f2f2f2;
            text-align: center;
        }

        .text-center {
            text-align: center;
        }

        .header-no-border {
            text-align: center;
            font-size: 11pt;
            line-height: 1.4;
            padding: 10px 0;
        }

        .header-no-border .title {
            font-weight: bold;
            font-size: 14pt;
            text-decoration: underline;
        }

        .header-no-border .doc {
            font-weight: bold;
            font-size: 12pt;
        }

        .header-no-border .date {
            font-size: 10pt;
        }
    </style>
</head>
<body>

    <table>
        <thead>
            <tr>
                <td colspan="5" class="header-no-border" style="border: none;">
                    <div class="title">INVENTORY ASSET REPORT</div>
                    <div class="doc">{{ $parent->doc_no }}</div>
                    <div class="date">Created: {{ \Carbon\Carbon::parse($parent->created_at)->format('d-m-Y H:i') }}</div>
                </td>
            </tr>
            <tr>
                <th style="width:10px;">No</th>
                <th style="width:50px;">QR Code</th>
                <th style="width:300px;">Product</th>
                <th style="width:200px;">Detail Asset</th>
                <th style="width:100px;">Note</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @foreach($items as $item)
                <tr>
                    <td class="text-center">{{ $no++ }}</td>
                    <td class="text-center" style="vertical-align: middle;">
                        <img src="{{ $item->id }}" alt="QR Code" style="width:85px; height:85px;">
                    </td>
                    <td>
                        <div style="line-height: 1.6;">
                            <div><b>{{ $item->doc_no ?? '-' }}</b></div>
                            <div>{{ $item->product_name ?? '-' }}</div>
                            <small>
                                <div><span style="display:inline-block; width:70px;">PN/SPEC</span>: {{ $item->part_number ?? '-' }}</div>
                                <div><span style="display:inline-block; width:70px;">Brand</span>: {{ $item->product_brand ?? '-' }}</div>
                                <div><span style="display:inline-block; width:70px;">UOM</span>: {{ $item->measure ?? '-' }}</div>
                                <div><span style="display:inline-block; width:70px;">Relation</span>:
                                    @if ($item->type_relation === 'po')
                                        {{ $item->po_number ?? '-' }}
                                    @elseif ($item->type_relation === 'bpb')
                                        {{ $item->bpb_number ?? '-' }}
                                    @else
                                        -
                                    @endif
                                </div>
                            </small>
                        </div>
                    </td>
                    <td>
                        <div style="line-height: 1.6;">
                            <div><span style="display:inline-block; width:90px;">Company</span>: {{ $item->comp_name ?? '-' }}</div>
                            <div><span style="display:inline-block; width:90px;">Department</span>: {{ $item->dept_name ?? '-' }}</div>
                            <div><span style="display:inline-block; width:90px;">Location</span>: {{ $item->location_name ?? '-' }}</div>
                        </div>
                    </td>
                    <td>{{ $item->notes ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

</body>
</html>
