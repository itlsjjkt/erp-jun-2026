<table width="100%" style="border:1px solid #000">
    <thead>
        <tr>
            <th width="5" style="font-weight:bold">NO</th>
            <th width="25" style="font-weight:bold">NO. Asuransi</th>
            <th width="25" style="font-weight:bold">NO. SPB</th>
            <th width="25" style="font-weight:bold">NO. LPB</th>
            <th width="25" style="font-weight:bold">NO. PO</th>
            <th width="25" style="font-weight:bold">NO. PR</th>
            <th width="25" style="font-weight:bold">NO. DPM</th>
            <th width="15" style="font-weight:bold">KODE BARANG</th>
            <th width="40" style="font-weight:bold">NAMA BARANG</th>
            <th width="15" style="font-weight:bold">PN/SPEC</th>
            <th width="15" style="font-weight:bold">MERK</th>
            <th style="font-weight:bold">QTY</th>
            <th style="font-weight:bold">SATUAN</th>
            <th style="font-weight:bold">HARGA</th>
            <th style="font-weight:bold">DISKON(%)</th>
            <th style="font-weight:bold">PPN(%)</th>
            <th style="font-weight:bold">TOTAL</th>
        </tr>
    </thead>
    <tbody>
    @php 
        $no = 1
    @endphp
    @foreach($insurance as $val)
        <?php 
            $row      = count($val);
            $rowspan  = false;
            if($row > 1){
                $rowspan  = true;
            }
            $first = 1;
        ?>
        @foreach($val as $item)
            <?php 
                $total = 0;
                $total_ = $item->price - ($item->price * $item->discount/100);
                $total_ += ($total_*$item->ppn/100);
                $total_ = $total_ * $item->qtyKoli;
            ?>
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
                <td>{{ $item->doc_no }}</td>
                <td>{{ $item->noSPB }}</td>
                <td>{{ $item->noLPB }}</td>
                <td>{{ $item->noPO }}</td>
                <td>{{ $item->noPR }}</td>
                <td>{{ $item->noDPM }}</td>
                <td>{{ $item->productCode }}</td>
                <td>{{ $item->productName }}</td>
                <td>{{ $item->productPartNumber }}</td>
                <td>{{ $item->productBrand }}</td>
                <td>{{ $item->qtyKoli }}</td>
                <td>{{ $item->measure }}</td>
                <td>{{ number_format($item->price ,2,".",',') }}</td>
                <td>{{ $item->discount }}</td>
                <td>{{ $item->ppn}}</td>
                <td>{{ number_format($total_ ,2,".",',') }}</td>
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

