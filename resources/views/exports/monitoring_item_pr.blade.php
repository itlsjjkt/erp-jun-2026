<table width="100%" style="border:1px solid #000">
    <thead>
        <tr>
            <th width="5" style="font-weight:bold">NO</th>
            <th width="40" style="font-weight:bold">NAMA BARANG</th>
            <th width="15" style="font-weight:bold">PN/SPEC</th>
            <th width="15" style="font-weight:bold">MERK</th>
            <th style="font-weight:bold">QTY</th>
            <th style="font-weight:bold">SATUAN</th>
            <th width="25" style="font-weight:bold">NO. PR</th>
            <th width="25" style="font-weight:bold">PURCHASER</th>
            <th width="25" style="font-weight:bold">LOKASI</th>
            <th style="font-weight:bold">CATATAN</th>
        </tr>
    </thead>
    <tbody>
    @php 
        $no = 1
    @endphp
    @foreach($pr as $val)
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
                    <td rowspan="{{ $row }}" style="vertical-align:top;text-align:center">{{ $no }}</td>
                @else
                    @if($rowspan == false )
                        <td style="vertical-align:top;text-align:center">{{ $no }}</td>
                     @endif
                @endif
                <td>{{ $item->productName }}</td>
                <td>{{ $item->productPartNumber }}</td>
                <td>{{ $item->productBrand }}</td>
                <td style="text-align:right">{{ $item->qty }}</td>
                <td>{{ $item->measure }}</td>
                <td>{{ $item->doc_no }}</td>
                <td>{{ $item->purchaser }}</td>
                <td>{{ $item->location}}</td>
                <td>{{ $item->notes }}</td>
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


