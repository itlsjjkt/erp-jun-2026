<table width="100%" style="border:1px solid #000">
    <thead>
        <tr>
            <th width="5" style="font-weight:bold">NO</th>
            <th width="25" style="font-weight:bold">NOMOR ROT</th>
            <th width="25" style="font-weight:bold">OPERATOR</th>
    	    <th width="15" style="font-weight:bold">KODE BARANG</th>
            <th width="40" style="font-weight:bold">NAMA BARANG</th>
            <th width="15" style="font-weight:bold">PN/SPEC</th>
            <th style="font-weight:bold">NOMOR RAK</th>
            <th style="font-weight:bold">QTY RETUR</th>
            <th style="font-weight:bold">SATUAN</th>
            <th style="font-weight:bold">KETERANGAN</th>
            <th style="font-weight:bold">TGL INPUT</th>
        </tr>
    </thead>
    <tbody>
    @php 
        $no = 1
    @endphp
    @foreach($return_out as $val)
        <?php 
            $row      = count($val);
            $rowspan  = false;
            if($row > 1){
                $rowspan  = false;
            }
            $first = 1;
        ?>
        @foreach($val as $item)
            
            <tr>
                @if($first == 1 && $rowspan)
                    <td rowspan="{{ $row }}" style="vertical-align:top;text-align:center">{{ $no }}</td>
                    <td rowspan="{{ $row }}" style="vertical-align:top;">{{ $item->doc_no }}</td>
                    <td rowspan="{{ $row }}" style="vertical-align:top;">{{ $item->operator }}</td>
                @else
                    @if($rowspan == false )
                        <td style="vertical-align:top;text-align:center">{{ $no }}</td>
                        <td style="vertical-align:top;">{{ $item->doc_no }}</td>
                        <td style="vertical-align:top;">{{ $item->operator }}</td>
                     @endif
                @endif
		        <td>{{ $item->productCode }}</td>
                <td>{{ $item->productName }}</td>
                <td>{{ $item->productPartNumber }}</td>
                <td>{{ $item->code_rack }}</td>
                <td style="text-align:right">{{ $item->qty }}</td>
                <td>{{ $item->measure }}</td>
                <td>{{ $item->reason }}</td>
            	<td>{{ date('d/m/Y',strtotime( $item->created_at)) }}</td>
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

