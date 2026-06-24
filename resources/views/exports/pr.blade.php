<table width="100%" style="border:1px solid #000">
    <thead>
        <tr>
            <th width="5" style="font-weight:bold">NO</th>
            <th width="25" style="font-weight:bold">NOMOR PR</th>
            <th width="40" style="font-weight:bold">NAMA BARANG</th>
            <th width="15" style="font-weight:bold">PN/SPEC</th>
            <th width="15" style="font-weight:bold">MERK</th>
            <th width="25" style="font-weight:bold">CATATAN</th>
            <th style="font-weight:bold">QTY</th>
            <th style="font-weight:bold">SATUAN</th>
            <th width="25" style="font-weight:bold">TIPE</th>
            <th width="20" style="font-weight:bold">PURCHASER</th>
            <th style="font-weight:bold">TGL INPUT</th>
            <th width="25" style="font-weight:bold">DEPARTEMEN/KAPAL</th>
            <th width="25" style="font-weight:bold">PROJECT</th>
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
                    <td rowspan="{{ $row }}" style="vertical-align:top;">{{ $item->doc_no }}</td>
                @else
                    @if($rowspan == false )
                        <td style="vertical-align:top;text-align:center">{{ $no }}</td>
                        <td style="vertical-align:top;">{{$item->doc_no }}</td>
                     @endif
                @endif
                <td>{{ $item->productName }}</td>
                <td>{{ $item->productPartNumber }}</td>
                <td>{{ $item->productBrand }}</td>
                <td>{!! $item->notes !!}</td>
                <td style="text-align:right">{{ $item->qty }}</td>
                <td>{{ $item->measure }}</td>
                <td>{{ strtoupper($item->type) }}</td>
                <td>{{ $item->purchaser }}</td>
             	<td>{{ date('d/m/Y',strtotime( $item->created_at)) }}</td>
                 <td>{{ $item->department }}</td>
                <td>{{ $item->project}}</td>
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

