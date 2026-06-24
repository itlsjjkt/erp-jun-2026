<table width="100%" style="border:1px solid #000">
    <thead>
        <tr>
            <th style="font-weight:bold">NO</th>
            <th style="font-weight:bold">TGL INPUT</th>
            <th style="font-weight:bold">NOMOR WTO</th>
            <th style="font-weight:bold">TYPE WTO</th>
            <th style="font-weight:bold">OPERATOR</th>
            <th style="font-weight:bold">LOKASI ASAL</th>
            <th style="font-weight:bold">LOKASI TUJUAN</th>
	        <th style="font-weight:bold">KODE BARANG</th>
            <th style="font-weight:bold">NAMA BARANG</th>
            <th style="font-weight:bold">PN/SPEC</th>
            <th style="font-weight:bold">QTY</th>
            <th style="font-weight:bold">SATUAN</th>
            <th style="font-weight:bold">CATATAN</th>
            <th style="font-weight:bold">HARGA SATUAN</th>
            <th style="font-weight:bold">HARGA SATUAN SETELAH DISCOUNT</th>
            <th style="font-weight:bold">TOTAL HARGA</th>
            <th style="font-weight:bold">STATUS</th>
        </tr>
    </thead>
    <tbody>
        @php
            $no = 1;
        @endphp
        @foreach($transfer as $val)
            @foreach($val as $item)
                <tr>
                    <td>{{ $no }}</td>
                    <td>{{ date('d/m/Y',strtotime( $item->created_at)) }}</td>
                    <td>{{ $item->doc_no }}</td>
                    <td>{{ getTypeWto($item->typeWto) }}</td>
                    <td>{{ $item->operator }}</td>
                    <td>{{ $item->lokasiAsal.' - '.$item->companyAsal }}</td>
                    <td>{{ $item->lokasiTujuan.' - '.$item->companyTujuan }}</td>
                    <td>{{ $item->productCode }}</td>
                    <td>{{ $item->productName }}</td>
                    <td>{{ $item->productPartNumber }}</td>
                    <td>{{ number_format($item->qty, 0) }}</td>
                    <td>{{ $item->measure }}</td>
                    <td>{{ str_replace('&nbsp;', ' ', strip_tags($item->notes)) }}</td>
                    <td>{{$item->price}}</td>
                    <td>{{$item->price_after_discount}}</td>
                    <td>{{$item->price_after_discount * $item->qty}}</td>
                    <td>{{ getStatusTransferInventory($item->statusWto,'row') }}</td>
                </tr>
            @php
                $no ++;
            @endphp
            @endforeach
        @endforeach
    </tbody>
</table>

