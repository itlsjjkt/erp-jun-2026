<table width="100%" style="border:1px solid #000">
    <thead>
        <tr>
            <td rowspan="2" width="25" style="font-weight:bold;text-align:center;vertical-align:middle;background:#fff200">LOGISTIC</td>
            <?php 
                foreach ($result_dpm as $val){
                $name = '';
                foreach($val as $item){
                    $name = $item->name;
                }
                echo '<td colspan="2" style="text-align:center;font-weight:bold;;background:#fff200">'.$name.'</td>';
                echo '<td rowspan="2" style="text-align:center;background:#71b8e8;font-weight:bold"> YTD <br>'.$name.' </td>';
            } ?>
            <td rowspan="2" style="text-align:center;font-weight:bold;background:#fff200"> TOTAL <br> {{ $company }}</td>
        </tr>
        <tr>
            <?php 
                foreach ($result_dpm as $val){
                echo '<td style="text-align:center;font-weight:bold;background:#fff200"> As of '.date('M', mktime(0, 0, 0, $month-1, 10)) .' </td>';
                echo '<td style="text-align:center;font-weight:bold;background:#fff200">'.date('M', mktime(0, 0, 0, $month, 10)).'</td>';
            } ?>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>DPM Entry</td>
            <?php 
                $totalAllDPM = 0;
                foreach ($result_dpm as $val){
                    $totalDPM = 0;
                    foreach($val as $item) {
                        echo "<td style='text-align:right'>".$item->dpm_previous."</td>";
                        echo "<td style='text-align:right'>".$item->dpm_current."</td>";
                        $totalDPM = $item->dpm_previous+$item->dpm_current;
                        $totalAllDPM += $totalDPM;
                    }
                    echo "<td style='text-align:right;background:#71b8e8'>".$totalDPM."</td>";
                }
                echo "<td style='text-align:right'>".$totalAllDPM."</td>";
            ?>
        </tr>
        <tr>
            <td>Items</td>
            <?php
                $totalAllItem = 0;
                foreach ($result_item as $val){
                    $totalItem = 0;
                    foreach($val as $item) {
                        echo "<td style='text-align:right'>".$item->item_previous."</td>";
                        echo "<td style='text-align:right'>".$item->item_current."</td>";
                        $totalItem = $item->item_previous+$item->item_current;
                        $totalAllItem += $totalItem;
                    }
                    echo "<td style='text-align:right;background:#71b8e8'>".$totalItem."</td>";
                }
                echo "<td style='text-align:right'>".$totalAllItem."</td>";
            ?>
        </tr>
        <tr>
            <td>On Progress Items</td>
            <?php
                $totalAllOnProgressItem = 0;
                foreach ($result_item as $val){
                    $totalOnProgressItem = 0;
                    foreach($val as $item) {
                        echo "<td style='text-align:right'>".$item->item_onprogress_previous."</td>";
                        echo "<td style='text-align:right'>".$item->item_onprogress_current."</td>";
                        $totalOnProgressItem = $item->item_onprogress_previous+$item->item_onprogress_current;
                        $totalAllOnProgressItem += $totalOnProgressItem;
                    }
                    echo "<td style='text-align:right;background:#71b8e8'>".$totalOnProgressItem."</td>";
                }
                echo "<td style='text-align:right'>".$totalAllOnProgressItem."</td>";
            ?>
        </tr>
        <tr>
            <td>Rejected Items</td>
            <?php 
                $totalAllItemReject = 0;
                foreach ($result_item as $val){
                    $totalItemReject = 0;
                    foreach($val as $item) {
                        echo "<td style='text-align:right'>".$item->item_reject_previous."</td>";
                        echo "<td style='text-align:right'>".$item->item_reject_current."</td>";
                        $totalItemReject = $item->item_reject_previous+$item->item_reject_current;
                        $totalAllItemReject += $totalItemReject;
                    }
                    echo "<td style='text-align:right;background:#71b8e8'>".$totalItemReject."</td>";
                }
                echo "<td style='text-align:right'>".$totalAllItemReject."</td>";
            ?>
        </tr>
        <tr>
            <td>Approved Items</td>
            <?php 
                $totalAllItemApproved = 0;
                foreach ($result_item as $val){
                    $totalItemApproved = 0;
                    foreach($val as $item) {
                        echo "<td style='text-align:right'>".$item->item_approved_previous."</td>";
                        echo "<td style='text-align:right'>". $item->item_approved_current ."</td>";
                        $totalItemApproved =  $item->item_approved_previous + $item->item_approved_current;
                        $totalAllItemApproved += $totalItemApproved;
                    }
                    echo "<td style='text-align:right;background:#71b8e8'>".$totalItemApproved."</td>";
                }
                echo "<td style='text-align:right'>".$totalAllItemApproved."</td>";
            ?>
        </tr>
    </tbody>
</table>


<table width="100%" style="border:1px solid #000">
    <thead>
        <tr>
            <td rowspan="2" width="25" style="font-weight:bold;text-align:center;vertical-align:middle;background:#fff200">PURCHASING</td>
            <?php 
                foreach ($result_dpm as $val){
                $name = '';
                foreach($val as $item){
                    $name = $item->name;
                }
                echo '<td colspan="2" style="text-align:center;font-weight:bold;;background:#fff200">'.$name.'</td>';
                echo '<td rowspan="2" style="text-align:center;background:#71b8e8;font-weight:bold"> TOTAL <br>'.$name.' </td>';
            } ?>
            <td rowspan="2" style="text-align:center;font-weight:bold;background:#fff200"> TOTAL <br> {{ $company }}</td>
        </tr>
        <tr>
            <?php 
                foreach ($result_dpm as $val){
                echo '<td style="text-align:center;font-weight:bold;background:#fff200"> As of '.date('M', mktime(0, 0, 0, $month-1, 10)) .' </td>';
                echo '<td style="text-align:center;font-weight:bold;background:#fff200">'.date('M', mktime(0, 0, 0, $month, 10)).'</td>';
            } ?>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Items from Logistic</td>
            <?php 
                $totalAllItemApproved = 0;
                foreach ($result_item as $val){
                    $totalItemApproved = 0;
                    foreach($val as $item) {
                        echo "<td style='text-align:right'>". $item->item_approved_previous ."</td>";
                        echo "<td style='text-align:right'>". $item->item_approved_current ."</td>";
                        $totalItemApproved =  $item->item_approved_previous + $item->item_approved_current;
                        $totalAllItemApproved += $totalItemApproved;
                    }
                    echo "<td style='text-align:right;background:#71b8e8'>".$totalItemApproved."</td>";
                }
                echo "<td style='text-align:right'>".$totalAllItemApproved."</td>";
            ?>
        </tr>

        <tr>
            <td>PO Done</td>
            <?php 
                $totalAllPO = 0;
                foreach ($result_item as $val){
                    $totalPO = 0;
                    foreach($val as $item) {
                        echo "<td style='text-align:right'>". $item->po_previous ."</td>";
                        echo "<td style='text-align:right'>". $item->po_current ."</td>";
                        $totalPO =  $item->po_previous + $item->po_current;
                        $totalAllPO += $totalPO;
                    }
                    echo "<td style='text-align:right;background:#71b8e8'>".$totalPO."</td>";
                }
                echo "<td style='text-align:right'>".$totalAllPO."</td>";
            ?>
        </tr>

        <tr>
            <td>PR Closed</td>
            <?php 
                $totalAllPRClosed = 0;
                foreach ($result_item as $val){
                    $totalPRClosed = 0;
                    foreach($val as $item) {
                        echo "<td style='text-align:right'>". $item->pr_close_previous ."</td>";
                        echo "<td style='text-align:right'>". $item->pr_close_current ."</td>";
                        $totalPRClosed =  $item->pr_close_previous + $item->pr_close_current;
                        $totalAllPRClosed += $totalPRClosed;
                    }
                    echo "<td style='text-align:right;background:#71b8e8'>".$totalPRClosed."</td>";
                }
                echo "<td style='text-align:right'>".$totalAllPRClosed."</td>";
            ?>
        </tr>


        <tr>
            <td>PR Aging</td>
            <?php 
                $totalAllPR = 0;
                foreach ($result_item as $val){
                    $totalPR = 0;
                    foreach($val as $item) {
                        echo "<td style='text-align:right'>". $item->pr_previous ."</td>";
                        echo "<td style='text-align:right'>". $item->pr_current ."</td>";
                        $totalPR =  $item->pr_previous + $item->pr_current;
                        $totalAllPR += $totalPR;
                    }
                    echo "<td style='text-align:right;background:#71b8e8'>".$totalPR."</td>";
                }
                echo "<td style='text-align:right'>".$totalAllPR."</td>";
            ?>
        </tr>

        <tr>
            <td>{{ date('F', mktime(0, 0, 0, $month, 10)) }} {{ $year }}</td>
            <?php 
                $totalAllPR = 0;
                foreach ($result_item as $val){
                    $totalPR = 0;
                    foreach($val as $item) {
                        $pr_current  = $item->pr_current;

                        echo "<td style='text-align:right'>-</td>";
                        echo "<td style='text-align:right'>". $pr_current ."</td>";
                        $totalPR =  $pr_current;
                        $totalAllPR += $totalPR;
                    }
                    echo "<td style='text-align:right;background:#71b8e8'>".$totalPR."</td>";
                }
                echo "<td style='text-align:right'>".$totalAllPR."</td>";
            ?>
        </tr>
        
        <?php 
            for($i = 0; $i < count($month_pr);$i++) {
                $totalPR = 0;
                echo "<tr>";
                    echo "<td>".$month_pr[$i]." ".$year_pr[$i]."</td>";
                        foreach ($result_pr as $val){
                        $pr = '';
                        $bln  = strtolower($month_pr[$i]);
                        foreach($val as $item){
                            $pr = $item->$bln;
                            $totalPR += $pr;
                        }
                        if($pr == 0){
                            $pr = '-';
                        }
                        echo '<td style="text-align:right">'.$pr.'</td>';
                        echo '<td style="text-align:right">-</td>';
                        echo '<td style="text-align:right;background:#71b8e8">'. $pr.'</td>';
                    }
                    echo '<td style="text-align:right;background:#fff200">'. $totalPR .'</td>';
                echo "</tr>";
            }
        
        ?>


    </tbody>
</table>
