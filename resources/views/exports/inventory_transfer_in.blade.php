<table width="100%" style="border:1px solid #000">
    <thead>
        <tr>
            <th style="text-align: center;font-weight:bold;" colspan="6">
                PRODUK
            </th>
            <th style="text-align: center;font-weight:bold;" colspan="3">
                DATA WTO
            </th>
            <th style="text-align: center;font-weight:bold;" colspan="5">
                DATA WTI
            </th>
        </tr>
        <tr>
            {{-- PRODUK --}}
            <th style="font-weight:bold">KODE</th>
            <th style="font-weight:bold">ITEM</th>
            <th style="font-weight:bold">PN/SPEC</th>
            <th style="font-weight:bold">QTY</th>
            <th style="font-weight:bold">SATUAN</th>
            <th style="font-weight:bold">CATATAN</th>
            {{-- WTO DATA --}}
            <th style="font-weight:bold">NO WTO</th>
            <th style="font-weight:bold">TYPE WTO</th>
            <th style="font-weight:bold">TGL WTO</th>
            {{-- WTI DATA --}}
            <th style="font-weight:bold">NO WTI</th>
            <th style="font-weight:bold">TYPE WTI</th>
            <th style="font-weight:bold">TGL WTI</th>
            <th style="font-weight:bold">PENERIMA WTI</th>
            <th style="font-weight:bold">STATUS WTI</th>
        </tr>
    </thead>
    <tbody>
        @foreach($transfer as $val)
            @foreach($val as $item)
                <tr>
                    {{-- PRODUK --}}
                    <td>{{ $item->productCode }}</td>
                    <td>{{ $item->productName }}</td>
                    <td>{{ $item->productPartNumber }}</td>
                    <td>{{ number_format($item->qty,0) }}</td>
                    <td>{{ $item->measure }}</td>
                    <td>{{ $item->notes }}</td>
                    {{-- WTO --}}
                    <td>{{ $item->doc_no_wto }}</td>
                    <td>{{ getTypeWto($item->type_wto) }}</td>
                    <td>{{ $item->created_at_wto }}</td>
                    {{-- WTI --}}
                    <td>{{ $item->doc_no }}</td>
                    <td>{{ getTypeWto($item->typeWti) }}</td>
                    <td>{{ date('d/m/Y',strtotime( $item->created_at)) }}</td>
                    <td>{{ $item->received }}</td>
                    <td>{{ getStatusTransferIn($item->statusWti,$item->typeWti,$item->typeStatusWti,'row') }}</td>
                </tr>
            @endforeach
        @endforeach
    </tbody>
</table>

