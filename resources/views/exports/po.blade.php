<table width="100%" style="border:1px solid #000">
    <thead>
        <tr>
            <th width="5" style="font-weight:bold">NO</th>
            <th width="25" style="font-weight:bold">NOMOR DPM</th>
	        <th width="25" style="font-weight:bold">NOMOR PR</th>
            <th width="25" style="font-weight:bold">NOMOR PO</th>
            <th width="40" style="font-weight:bold">NAMA SUPPLIER</th>
            <th width="40" style="font-weight:bold">NAMA BARANG</th>
            <th width="40" style="font-weight:bold">KODE BARANG</th>
            <th width="15" style="font-weight:bold">PN/SPEC</th>
            <th width="15" style="font-weight:bold">MERK</th>
            <th width="25" style="font-weight:bold">SPESIFIKASI</th>
            <th style="font-weight:bold">QTY</th>
            <th style="font-weight:bold">SATUAN</th>
            <th style="font-weight:bold">HARGA</th>
            <th style="font-weight:bold">HARGA SETELAH DISKON</th>
            <th style="font-weight:bold">DISKON</th>
            <th style="font-weight:bold">TOTAL</th>
            <th style="font-weight:bold">TGL PENGIRIMAN</th>
            <th style="font-weight:bold">TANGGAL INPUT PO</th>
            <th style="font-weight:bold">PURCHASER</th>
            <th style="font-weight:bold">PROJECT</th>
        </tr>
    </thead>
    <tbody>
    @php 
        $no = 1
    @endphp
    @foreach($po as $val)
        <?php 
            $row      = count($val);
            $rowspan  = false;
            if($row > 1){
                $rowspan  = true;
                $row = count($val);
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

	
                <td>{{ $item->dpm_no }}</td>
                <td>{{ $item->pr_no }}</td>
                <td>{{ $item->doc_no }}</td>
                <td>{{ $item->supplier }}</td> 
                <td>{{ $item->productName }}</td>
                <td>{{ $item->productCodeNumber }}</td>
                <td>{{ $item->productPartNumber }}</td>
                <td>{{ $item->productBrand }}</td>
                <td>{!! $item->specification !!}</td>
                <td style="text-align:right">{{ $item->qty }}</td>
                <td>{{ $item->measure }}</td>
                <td>{{ $item->price }}</td>
                <td>{{ $item->price_discount }}</td>
                <td>{{ $item->discount }}</td>
                <td>
                    <?php 
                        $total= $item->price * $item->qty - (($item->price * $item->qty) *  $item->discount /100);
                        echo number_format($total,2,".",',')
                    ?>
                </td>
                <td>{{ date('d/m/Y',strtotime( $item->delivery_date)) }}</td>
            	<td>{{ date('d/m/Y',strtotime( $item->created_at)) }}</td>
                <td>{{ $item->purchaser }}</td>
                <td>{{ $item->project }}</td>
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

