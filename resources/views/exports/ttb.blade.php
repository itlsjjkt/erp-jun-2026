<table width="100%" style="border:1px solid #000">
    <thead>
        <tr>
            <th width="5" style="font-weight:bold">NO</th>
            <th style="font-weight:bold">TANGGAL TTB</th>
            <th width="25" style="font-weight:bold">NOMOR TTB</th>
            <th width="25" style="font-weight:bold">OPERATOR</th>
            <th width="25" style="font-weight:bold">PENERIMA</th>
            <th width="25" style="font-weight:bold">DEPTARTEMEN/KAPAL</th>
            <th width="25" style="font-weight:bold">PROJECT</th>
	        <th width="15" style="font-weight:bold">KODE BARANG</th>
            <th width="40" style="font-weight:bold">NAMA BARANG</th>
            <th width="15" style="font-weight:bold">PN/SPEC</th>
            <th style="font-weight:bold">QTY</th>
            <th style="font-weight:bold">SATUAN</th>
            <th style="font-weight:bold">PENGGUNAAN</th>
            <th style="font-weight:bold">CATATAN</th>
            <th style="font-weight:bold">HARGA SATUAN</th>
            <th style="font-weight:bold">HARGA SATUAN SETELAH DISCOUNT</th>
            <th style="font-weight:bold">TANGGAL INPUT</th>
        </tr>
    </thead>
    <tbody>
    @php 
        $no = 1
    @endphp
    @foreach($ttb as $val)
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
                @else
                     <td style="vertical-align:top;text-align:center">{{ $no }}</td>
                @endif
            	<td>{{ date('d/m/Y',strtotime( $item->date_transaction)) }}</td>
                <td>{{ $item->doc_no }}</td>
                <td>{{ $item->operator }}</td>
                <td>{{ $item->received }}</td>
                <td>{{ $item->department }}</td>
                <td>{{ $item->project }}</td>
		        <td>{{ $item->productCode }}</td>
                <td>{{ $item->productName }}</td>
                <td>{{ $item->productPartNumber }}</td>
                <td style="text-align:right">{{ $item->qty }}</td>
                <td>{{ $item->measure }}</td>
                <td>{{ $item->usage == 1 ? "Perusahaan" : "Perorangan" }}</td>
                <td>{{ $item->description }}</td>
                <td>{{ $item->price }}</td>
                <td>{{ ($item->price_after_discount=='0') ?  $item->price : $item->price_after_discount }}</td>
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

