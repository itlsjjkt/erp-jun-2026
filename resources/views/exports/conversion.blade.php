<table width="100%" style="border:1px solid #000">
    <thead>
        <tr>
            <th width="5" style="font-weight:bold">NO</th>
            <th width="25" style="font-weight:bold">NOMOR KONVERSI</th>
            <th width="25" style="font-weight:bold">OPERATOR</th>
            <th style="font-weight:bold">NOMOR RAK AWAL</th>
	        <th width="15" style="font-weight:bold">KODE BARANG AWAL</th>
            <th width="40" style="font-weight:bold">NAMA BARANG AWAL</th>
            <th width="15" style="font-weight:bold">PN/SPEC AWAL</th>
            <th style="font-weight:bold">QTY AWAL</th>
            <th style="font-weight:bold">SATUAN AWAL</th>
            <th style="font-weight:bold">NOMOR RAK KONVERSI</th>
	        <th width="15" style="font-weight:bold">KODE BARANG KONVERSI</th>
            <th width="40" style="font-weight:bold">NAMA BARANG KONVERSI</th>
            <th width="15" style="font-weight:bold">PN/SPEC KONVERSI</th>
            <th style="font-weight:bold">QTY KONVERSI</th>
            <th style="font-weight:bold">SATUAN KONVERSI</th>
            <th style="font-weight:bold">TGL INPUT</th>
        </tr>
    </thead>
    <tbody>
    @php 
        $no = 1
    @endphp
    @foreach($conversion as $val)
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
		        <td>{{ $item->coderack1 }}</td>
		        <td>{{ $item->productcode1 }}</td>
                <td>{{ $item->productname1 }}</td>
                <td>{{ $item->productpartnumber1 }}</td>
                <td style="text-align:right">{{ $item->qty_stock }}</td>
                <td>{{ $item->productunit1 }}</td>
                <td>{{ $item->coderack2 }}</td>
		        <td>{{ $item->productcode2 }}</td>
                <td>{{ $item->productname2 }}</td>
                <td>{{ $item->productpartnumber2 }}</td>
                <td style="text-align:right">{{ $item->qty_conversion }}</td>
                <td>{{ $item->productunit2 }}</td>
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

