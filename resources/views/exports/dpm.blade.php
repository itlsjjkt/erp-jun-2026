

<table width="100%" style="border:1px solid #000" class="table table-bordered">
    <thead>
        <tr>
            <th width="5" style="font-weight:bold">NO</th>
            <th width="25" style="font-weight:bold">NOMOR DPM</th>
            <th style="font-weight:bold">TGL PUBLISH DPM</th>
            <th style="font-weight:bold">NO PR</th>
	        <th style="font-weight:bold">TGL PR</th>
            <th style="font-weight:bold">PURCHASER</th>
    	    <th width="15" style="font-weight:bold">KODE BARANG</th>
            <th width="40" style="font-weight:bold">NAMA BARANG</th>
            <th width="15" style="font-weight:bold">PN/SPEC</th>
            <th width="15" style="font-weight:bold">MERK</th>
            <th style="font-weight:bold">DPM QTY</th>
	        <th style="font-weight:bold">SATUAN</th>
            <th style="font-weight:bold">CATATAN</th>
            <th style="font-weight:bold">FLAG</th>
            <th style="font-weight:bold">TGL DIBUTUHKAN</th>
            <th style="font-weight:bold">LAST APPROVED</th>
            <th style="font-weight:bold">TGL APPROVED</th>
            <th style="font-weight:bold">NEXT APPROVAL</th>
            <th style="font-weight:bold">DEPARTMENT</th>
            <th style="font-weight:bold">PROJECT</th>
            <th style="font-weight:bold">STATUS</th>
            <th style="font-weight:bold">ALASAN REJECT DPM</th>
            <th style="font-weight:bold">CLOSE PR BY</th>
            <th style="font-weight:bold">ALASAN CLOSE PR</th>
            <th style="font-weight:bold">CREATED BY</th>
            <th width="25" style="font-weight:bold">LOKASI</th>
            <?php for ($i= 0;$i<4;$i++) { ?>
                <th style="font-weight:bold">PO {{ $i+1 }}</th>
                <th style="font-weight:bold">TGL PENGIRIMAN {{ $i+1 }}</th>
                <th style="font-weight:bold">QTY PO {{ $i+1 }}</th>
                <th style="font-weight:bold">TIPE PO {{ $i+1 }}</th>
                <th style="font-weight:bold">SUPPLIER PO {{ $i+1 }}</th>
            <?php } ?>

            <?php for ($i= 0;$i<10;$i++) { ?>
                <th style="font-weight:bold">LPB {{ $i+1 }}</th>
                <th style="font-weight:bold">TGL PUBLISH LPB {{ $i+1 }}</th>
                <th style="font-weight:bold">QTY LPB{{ $i+1 }}</th>
            <?php } ?>

            <?php for ($i= 0;$i<10;$i++) { ?>
                <th style="font-weight:bold">SPB {{ $i+1 }}</th>
                <th style="font-weight:bold">TGL PUBLISH SPB{{ $i+1 }}</th>
                <th style="font-weight:bold">QTY SPB{{ $i+1 }}</th>
            <?php } ?>

            <?php for ($i= 0;$i<10;$i++) { ?>
                <th style="font-weight:bold">BPB {{ $i+1 }}</th>
                <th style="font-weight:bold">TGL PUBLISH BPB {{ $i+1 }}</th>
                <th style="font-weight:bold">QTY BPB {{ $i+1 }}</th>
            <?php } ?>
            <th width="10" style="font-weight:bold">QTY PO FULL</th>
            <th width="10" style="font-weight:bold">QTY LPB FULL</th>
            <th width="10" style="font-weight:bold">QTY SPB FULL</th>
            <th width="10" style="font-weight:bold">QTY BPB FULL</th>
	    </tr>
    </thead>
    <tbody>
    @php
        $no = 1
    @endphp
    @foreach($dpm_item as $val)
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
                $dateApprove    = '';
                if ($item[0]->last_approved_at != NULL){
                    $dateApprove = date('d/m/Y',strtotime( $item[0]->last_approved_at));
                }
                $nextApproval   = '';
                if (in_array($item[0]->status, array(1))){
                    $nextApproval = getNextApprovalDPM($item[0]->location_id,$item[0]->step);
                }

                $reject_by = $reject_reason ='';
                if($item[0]->po_status == 3){
                    $reject_by      = $item[0]->reject_by;
                    $reject_reason  = $item[0]->reject_reason;
                }
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
                <td>{{ $item[0]->dpm_no }}</td>
                <td>{{ $item[0]->dpm_publish == NULL ? " " : date('d/m/Y',strtotime( $item[0]->dpm_publish)) }}</td>
                <td>{{ $item[0]->pr_no }}</td>
                <td>{{ $item[0]->pr_publish == NULL ? " " : date('d/m/Y',strtotime( $item[0]->pr_publish)) }}</td>
                <td>{{ $item[0]->purchaser }}</td>
                <td>{{ $item[0]->productCode }}</td>
                <td>{{ $item[0]->productName }}</td>
                <td>{{ $item[0]->productPartNumber }}</td>
                <td>{{ $item[0]->productBrand }}</td>
                <td>{{ $item[0]->qty }}</td>
                <td>{{ $item[0]->measure }}</td>
                <td>{!! $item[0]->notes !!}</td>
                <td>{{ $item[0]->flag == 1 ? "Normal" : "Urgent" }}</td>
                <td>{{ date('d/m/Y',strtotime( $item[0]->needed_on_date)) }}</td>
                <td>{{ $item[0]->approved }}</td>
                <td>{{ $dateApprove }}</td>
                <td>{{ $nextApproval }}</td>
                <td>{{ $item[0]->department }}</td>
                <td>{{ $item[0]->project }}</td>
                <td>{{ getStatusItemExportDPM($item[0]->status, $item[0]->pr_status, $item[0]->po_status,$item[0]->qty_parsial, $item[0]->po_lpb_status, $item[0]->po_qty_parsial, $item[0]->spb_status, $item[0]->bpb_status) }}</td>
		        <td>{{ $item[0]->reason }}</td>
		        <td>{{ $reject_by }}</td>
		        <td>{{ $reject_reason }}</td>
		        <td>{{ $item[0]->created }}</td>
		        <td>{{ $item[0]->location }}</td>

                <?php array_unique(array_column($po[$item[0]->dpm_no][$item[0]->id], 'po_no')); ?>
                <?php
                    $qty_po_full = 0;
                    for ($i= 0;$i<4;$i++) {
                        if(isset($po[$item[0]->dpm_no][$item[0]->id][$i]) &&  $po[$item[0]->dpm_no][$item[0]->id][$i]['po_no'] !='') {  ?>
                            <td>{{ $po[$item[0]->dpm_no][$item[0]->id][$i]['po_no'] }}</td>
			                <td>{{ $po[$item[0]->dpm_no][$item[0]->id][$i]['po_delivery_date'] == NULL ? " " : date('d/m/Y',strtotime( $po[$item[0]->dpm_no][$item[0]->id][$i]['po_delivery_date'])) }}</td>
                            <td>{{ $po[$item[0]->dpm_no][$item[0]->id][$i]['po_qty'] }}</td>
                            <td>{{ $po[$item[0]->dpm_no][$item[0]->id][$i]['po_ppn'] == 10 ? "PPN" : "NON PPN" }}</td>
                            <td>{{ $po[$item[0]->dpm_no][$item[0]->id][$i]['po_supplier'] }}</td>
                        <?php
                           $qty_po_full += $po[$item[0]->dpm_no][$item[0]->id][$i]['po_qty'];
                        }
                        else{
                            echo "<td></td> <td></td> <td></td> <td></td> <td></td>";
                    } ?>
                <?php } ?>

                <?php
                    $qty_lpb_full = 0;
                    for ($i= 0;$i<10;$i++) {
                    if( isset($lpb[$item[0]->dpm_no][$item[0]->id][$item[0]->po_no][$i]) &&  $lpb[$item[0]->dpm_no][$item[0]->id][$item[0]->po_no][$i]['lpb_no'] != '' ) { ?>
                        <td>{{ $lpb[$item[0]->dpm_no][$item[0]->id][$item[0]->po_no][$i]['lpb_no'] }}</td>
                        <td>{{ $lpb[$item[0]->dpm_no][$item[0]->id][$item[0]->po_no][$i]['lpb_publish'] == NULL ? " " : date('d/m/Y',strtotime( $lpb[$item[0]->dpm_no][$item[0]->id][$item[0]->po_no][$i]['lpb_publish'])) }}</td>
                        <td>{{ $lpb[$item[0]->dpm_no][$item[0]->id][$item[0]->po_no][$i]['lpb_qty'] }}</td>
                    <?php
                        $qty_lpb_full += $lpb[$item[0]->dpm_no][$item[0]->id][$item[0]->po_no][$i]['lpb_qty'] ;
                    }
                    else{
                        echo "<td></td><td></td><td></td>";
                    } ?>
                <?php } ?>

                <?php
                /*
                    $qtySameSpb = 0;
                    $countSameSpb = 0;
                    $newSpbNo = array();
                    $newSpbPublish = array();
                    $newSpbQty = array();
                    for ($i= 0;$i<10;$i++) {
                    if( isset($spb[$item[0]->dpm_no][$item[0]->id][$item[0]->po_no][$item[0]->lpb_no][$i]) &&  $spb[$item[0]->dpm_no][$item[0]->id][$item[0]->po_no][$item[0]->lpb_no][$i]['spb_no'] != '' ) {
                    if ($i > 0) {
                        if ($spb[$item[0]->dpm_no][$item[0]->id][$item[0]->po_no][$item[0]->lpb_no][$i-1]['spb_no'] === $spb[$item[0]->dpm_no][$item[0]->id][$item[0]->po_no][$item[0]->lpb_no][$i]['spb_no']) {
                            $qtySameSpb = $qtySameSpb + $spb[$item[0]->dpm_no][$item[0]->id][$item[0]->po_no][$item[0]->lpb_no][$i]['spb_qty'];
                            $countSameSpb += 1;
                        }
                    } else {
                        $qtySameSpb = $spb[$item[0]->dpm_no][$item[0]->id][$item[0]->po_no][$item[0]->lpb_no][0]['spb_qty'];
                        $countSameSpb += 0;
                    }
                }
                }
                */
                ?>
                <?php
                    $qty_spb_full = 0;
                    for ($i= 0;$i<10;$i++) {
                    if( isset($spb[$item[0]->dpm_no][$item[0]->id][$item[0]->po_no][$item[0]->lpb_no][$i]) &&  $spb[$item[0]->dpm_no][$item[0]->id][$item[0]->po_no][$item[0]->lpb_no][$i]['spb_no'] != '' ) {
                    ?>
                        <td>{{ $spb[$item[0]->dpm_no][$item[0]->id][$item[0]->po_no][$item[0]->lpb_no][$i]['spb_no'] }}</td>
                        <td>{{ $spb[$item[0]->dpm_no][$item[0]->id][$item[0]->po_no][$item[0]->lpb_no][$i]['spb_publish'] == NULL ? " " : date('d/m/Y',strtotime( $spb[$item[0]->dpm_no][$item[0]->id][$item[0]->po_no][$item[0]->lpb_no][$i]['spb_publish'])) }}</td>
                        <td>{{$spb[$item[0]->dpm_no][$item[0]->id][$item[0]->po_no][$item[0]->lpb_no][$i]['spb_qty']}}</td>
                    <?php
                        $qty_spb_full += $spb[$item[0]->dpm_no][$item[0]->id][$item[0]->po_no][$item[0]->lpb_no][$i]['spb_qty'] ;
                    }
                    else{
                        echo "<td></td><td></td><td></td>";
                    } ?>
                <?php } ?>

                <?php
                    $qty_bpb_full = 0;
                    for ($i= 0;$i<10;$i++) {
                    if(isset($bpb[$item[0]->dpm_no][$item[0]->id][$item[0]->po_no][$item[0]->lpb_no][$item[0]->spb_no][$i]) &&  $bpb[$item[0]->dpm_no][$item[0]->id][$item[0]->po_no][$item[0]->lpb_no][$item[0]->spb_no][$i]['bpb_no'] != ''  ) { ?>
                        <td>{{ $bpb[$item[0]->dpm_no][$item[0]->id][$item[0]->po_no][$item[0]->lpb_no][$item[0]->spb_no][$i]['bpb_no'] }}</td>
                        <td>{{ $bpb[$item[0]->dpm_no][$item[0]->id][$item[0]->po_no][$item[0]->lpb_no][$item[0]->spb_no][$i]['bpb_publish'] == NULL ? " " : date('d/m/Y',strtotime( $bpb[$item[0]->dpm_no][$item[0]->id][$item[0]->po_no][$item[0]->lpb_no][$item[0]->spb_no][$i]['bpb_publish'])) }}</td>
                        <td>{{ $bpb[$item[0]->dpm_no][$item[0]->id][$item[0]->po_no][$item[0]->lpb_no][$item[0]->spb_no][$i]['bpb_qty'] }}</td>
                    <?php
                        $qty_bpb_full += $bpb[$item[0]->dpm_no][$item[0]->id][$item[0]->po_no][$item[0]->lpb_no][$item[0]->spb_no][$i]['bpb_qty'];
                    }
                    else{
                        echo "<td></td><td></td><td></td>";
                    } ?>
                <?php } ?>

                <td>{{ $qty_po_full }}</td>
                <td>{{ $qty_lpb_full }}</td>
                <td>{{ $qty_spb_full }}</td>
                <td>{{ $qty_bpb_full }}</td>

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

