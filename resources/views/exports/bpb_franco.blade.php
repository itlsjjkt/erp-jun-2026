<table width="100%" style="border:1px solid #000">
    <thead>
        <tr>
            <th width="5" style="font-weight:bold">NO</th>
            <th style="font-weight:bold">TANGGAL TTB</th>
            <th width="25" style="font-weight:bold">NO. DPM</th>
            <th width="25" style="font-weight:bold">NO. PR</th>
            <th width="25" style="font-weight:bold">NO. PO</th>
            <th width="25" style="font-weight:bold">NO. BPB</th>
            <th width="15" style="font-weight:bold">KODE BARANG</th>
            <th width="40" style="font-weight:bold">NAMA BARANG</th>
            <th width="15" style="font-weight:bold">PN/SPEC</th>
            <th width="15" style="font-weight:bold">MERK</th>
            <th width="25" style="font-weight:bold">SPESIFIKASI</th>
            <th style="font-weight:bold">QTY</th>
            <th style="font-weight:bold">SATUAN</th>
            <th style="font-weight:bold">HARGA SATUAN</th>
            <th style="font-weight:bold">HARGA SATUAN SETELAH DISCOUNT</th>
            <th style="font-weight:bold">CATATAN BPB</th>
            <th style="font-weight:bold">DEPARTEMEN</th>
        </tr>
    </thead>
    <tbody>
    @php 
        $no = 1
    @endphp
    @foreach($bpb as $val)
        <?php 
            $row      = count($val);
            $rowspan  = false;
            if($row > 1){
                $rowspan  = true;
            }
            $first = 1;
        ?>
        @foreach($val as $item)
            
            <tr>
                @if($first == 1 && $rowspan)
                    <td style="vertical-align:top;text-align:center">{{ $no }}</td>
                @else
                    @if($rowspan == false )
                        <td style="vertical-align:top;text-align:center">{{ $no }}</td>
                    @else
                        <td style="vertical-align:top;text-align:center"></td>
                     @endif
                @endif
            	<td>{{ date('d/m/Y',strtotime( $item->created_at)) }}</td>
                <td>{{ $item->noDPM }}</td>
                <td>{{ $item->noPR }}</td>
                <td>{{ $item->noPO }}</td>
                <td>{{ $item->doc_no }}</td>
                <td>{{ $item->productCode }}</td>
                <td>{{ $item->productName }}</td>
                <td>{{ $item->productPartNumber }}</td>
                <td>{{ $item->productBrand }}</td>
                <td>{!! $item->notes !!}</td>
                <td>{{ $item->qty }}</td>
                <td>{{ $item->measure }}</td>
                <td>{{ $item->price }}</td>
                <td>{{ ($item->price_discount=='0') ?  $item->price : $item->price_discount }}</td>
                <td>{{ $item->description }}</td>
                <td>{{ $item->department }}</td>
            </tr>
            @php 
                $first++;
            @endphp
        @endforeach
        @php 
            $no++
        @endphp
    @endforeach
    </tbody>
</table>

