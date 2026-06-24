

<table width="100%" style="border:1px solid #000" class="table table-bordered">
    <thead>
        <tr>
            <th width="5" style="font-weight:bold">NO</th>
            <th width="27" style="font-weight:bold">NOMOR DPM</th>
            <th width="18" style="font-weight:bold">TGL INPUT DPM</th>
    	    <th width="15" style="font-weight:bold">KODE BARANG</th>
            <th width="40" style="font-weight:bold">NAMA BARANG</th>
            <th width="15" style="font-weight:bold">PN/SPEC</th>
            <th width="15" style="font-weight:bold">MERK</th>
            <th width="15" style="font-weight:bold">DPM QTY</th>
	        <th width="10" style="font-weight:bold">SATUAN</th>
            <th width="25" style="font-weight:bold">CATATAN/SPESIFIKASI</th>
            <th width="25" style="font-weight:bold">DEPARTEMEN</th>
            <th width="25" style="font-weight:bold">LOKASI</th>
            <th width="30" style="font-weight:bold">TGL PUBLISH DPM</th>
            <?php for ($i= 0;$i<10;$i++) { ?>
                <?php if ($i == 0) { ?>
                    <th width="42" style="font-weight:bold">JEDA TGL PUBLISH DPM DENGAN APPROVAL {{ $i+1 }}</th>
                <?php } else if ($i > 0) {?>
                    <th width="38" style="font-weight:bold">JEDA APPROVAL {{ $i }} DENGAN APPROVAL {{ $i+1 }}</th>
                <?php } ?>
                <th width="30" style="font-weight:bold">TGL APPROVAL {{ $i+1 }}</th>
                <th width="25" style="font-weight:bold">APPROVAL {{ $i+1 }}</th>
                <th width="20" style="font-weight:bold">STATUS APPROVAL {{ $i+1 }}</th>
            <?php } ?>
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
                <td>{{ $item[0]->dpm_created == NULL ? " " : date('d/m/Y',strtotime( $item[0]->dpm_created)) }}</td>
                <td>{{ $item[0]->productCode }}</td>
                <td>{{ $item[0]->productName }}</td>
                <td>{{ $item[0]->productPartNumber }}</td>
                <td>{{ $item[0]->productBrand }}</td>
                <td>{{ $item[0]->qty }}</td>
                <td>{{ $item[0]->measure }}</td>
                <td>{{ $item[0]->notes }}</td>
                <td>{{ $item[0]->department }}</td>
                <td>{{ $item[0]->location }}</td>
                <td>{{ $item[0]->dpm_publish == NULL ? " " : Carbon\Carbon::parse($item[0]->dpm_publish)->formatLocalized('%d %B %Y Jam %H:%M') }}</td>
                <?php array_unique(array_column($dpm_approve[$item[0]->dpm_no][$item[0]->id], 'approved')); ?>
                <?php
                    for ($i= 0;$i<10;$i++) {
                        if(isset($dpm_approve[$item[0]->dpm_no][$item[0]->id][$i]) &&  $dpm_approve[$item[0]->dpm_no][$item[0]->id][$i]['approved'] !='') {  ?>
                                <td>
                                <?php
                                if ($i == 0) {
                                    if ($dpm_approve[$item[0]->dpm_no][$item[0]->id][$i]['date_approved'] == NULL)
                                    {
                                        echo "-";
                                    } else {
                                        $from = Carbon\Carbon::parse($item[0]->dpm_publish);
                                        $to = Carbon\Carbon::parse($dpm_approve[$item[0]->dpm_no][$item[0]->id][$i]['date_approved']);
                                        $diff_in_hours = $to->diffInHours($from);
                                        if ($diff_in_hours > 24) {
                                            $hours = $diff_in_hours % 24;
                                            $days = ($diff_in_hours - $hours)/ 24;
                                            echo $days." hari ". $hours." jam";
                                        } else {
                                            echo $diff_in_hours." jam";
                                        }
                                    }
                                } else if ($i > 0) {
                                    if ($dpm_approve[$item[0]->dpm_no][$item[0]->id][$i]['date_approved'] == NULL)
                                    {
                                        echo "-";
                                    } else {
                                        $from = Carbon\Carbon::parse($dpm_approve[$item[0]->dpm_no][$item[0]->id][$i-1]['date_approved']);
                                        $to = Carbon\Carbon::parse($dpm_approve[$item[0]->dpm_no][$item[0]->id][$i]['date_approved']);
                                        $diff_in_hours = $to->diffInHours($from);
                                        if ($diff_in_hours > 24) {
                                            $hours = $diff_in_hours % 24;
                                            $days = ($diff_in_hours - $hours)/ 24;
                                            echo $days." hari ". $hours." jam";
                                        } else {
                                            echo $diff_in_hours." jam";
                                        }
                                    }
                                } ?>
                            </td>
                            <td>{{ $dpm_approve[$item[0]->dpm_no][$item[0]->id][$i]['date_approved'] == NULL ? " " :  Carbon\Carbon::parse($dpm_approve[$item[0]->dpm_no][$item[0]->id][$i]['date_approved'])->formatLocalized('%d %B %Y Jam %H:%M') }}</td>
                            <td>{{ $dpm_approve[$item[0]->dpm_no][$item[0]->id][$i]['approved'] }}</td>
                            <?php if ($item[0]->status == 1) { ?>
                                <td>Approve</td>
                            <?php } else if ($item[0]->status == 2) { ?>
                                <?php if ($dpm_approve[$item[0]->dpm_no][$item[0]->id][$i]['approved_id'] == $item[0]->last_approved) { ?>
                                    <td>Reject</td>
                                <?php } else { ?>
                                    <td>Approve</td>
                                <?php } ?>
                            <?php } else if ($item[0]->status == 3) { ?>
                                <?php if ($dpm_approve[$item[0]->dpm_no][$item[0]->id][$i]['approved_id'] == $item[0]->last_approved) { ?>
                                    <td>Cancel</td>
                                <?php } else { ?>
                                    <td>Approve</td>
                                <?php } ?>
                            <?php } else if ($item[0]->status == 4) { ?>
                                <td>Approve</td>
                            <?php } ?>
                        <?php
                            } else{
                            echo "<td></td>  <td></td>  <td></td> <td></td>";
                            } ?>
                <?php } ?>
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

