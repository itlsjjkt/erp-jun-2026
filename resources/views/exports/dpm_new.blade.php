<table width="100%" style="border:1px solid #000" class="table table-bordered">
    <thead>
        <tr>
            <th width="5" style="height: 25px; font-weight:bold;">NO</th>
            <th width="25" style="height: 25px; font-weight:bold">NOMOR DPM</th>
            <th style="height: 25px; font-weight:bold">TGL PUBLISH DPM</th>
            <th style="height: 25px; font-weight:bold">NO PR</th>
	        <th style="height: 25px; font-weight:bold">TGL PR</th>
            <th style="height: 25px; font-weight:bold">PURCHASER</th>
    	    <th width="15" style="height: 25px; font-weight:bold">KODE BARANG</th>
            <th width="40" style="height: 25px; font-weight:bold">NAMA BARANG</th>
            <th width="15" style="height: 25px; font-weight:bold">PN/SPEC</th>
            <th width="15" style="height: 25px; font-weight:bold">MERK</th>
            <th style="height: 25px; font-weight:bold">DPM QTY</th>
	        <th style="height: 25px; font-weight:bold">SATUAN</th>
            <th style="height: 25px; font-weight:bold">CATATAN</th>
            <th style="height: 25px; font-weight:bold">FLAG</th>
            <th style="height: 25px; font-weight:bold">FRANCO</th>
            <th style="height: 25px; font-weight:bold">TGL DIBUTUHKAN</th>
            <th style="height: 25px; font-weight:bold">TGL APPROVED</th>
            <th style="height: 25px; font-weight:bold">DEPARTMENT</th>
            <th style="height: 25px; font-weight:bold">PROJECT</th>
            <th style="height: 25px; font-weight:bold">STATUS</th>
            <th style="height: 25px; font-weight:bold">ALASAN REJECT DPM</th>
            <th style="height: 25px; font-weight:bold">CLOSE PR BY</th>
            <th style="height: 25px; font-weight:bold">ALASAN CLOSE PR</th>
            <th style="height: 25px; font-weight:bold">CREATED BY</th>
            <th width="25" style="height: 25px;font-weight:bold">LOKASI</th>
            <th style="height: 25px; font-weight:bold">PO </th>
            <th style="height: 25px; font-weight:bold">QTY PO</th>
            <th style="height: 25px; font-weight:bold">SUPPLIER PO</th>
            <th style="height: 25px; font-weight:bold">LPB</th>
            <th style="height: 25px; font-weight:bold">TGL PUBLISH LPB</th>
            <th style="height: 25px; font-weight:bold">QTY LPB</th>
            <th style="height: 25px; font-weight:bold">SPB</th>
            <th style="height: 25px; font-weight:bold">TGL PUBLISH SPB</th>
            <th style="height: 25px; font-weight:bold">QTY SPB</th>
            <th style="height: 25px; font-weight:bold">BPB</th>
            <th style="height: 25px; font-weight:bold">TGL PUBLISH BPB</th>
            <th style="height: 25px; font-weight:bold">QTY BPB</th>
            <th width="10" style=" height: 25px; font-weight:bold">END USER</th>
	    </tr>
    </thead>
    <tbody>
        <?php $no = 1 ?>
        @foreach($dpm_item as $val)
        <tr>
            <td style="height: 25px; width: 50px;" >{{$no}}</td>
            <td style="height: 25px; width: 200px;" >{{$val->no_dpm}}</td>
            <td style="height: 25px; width: 200px;" >{{$val->tgl_dpm == NULL ? " " : date('d/m/Y',strtotime($val->tgl_dpm))}}</td>
            <td style="height: 25px; width: 200px;" >{{$val->no_pr}}</td>
            <td style="height: 25px; width: 200px;" >{{$val->tgl_pr == NULL ? " " : date('d/m/Y',strtotime($val->tgl_pr))}}</td>
            <td style="height: 25px; width: 200px;" >{{$val->purchaser}}</td>
            <td style="height: 25px; width: 200px;" >{{$val->product_code}}</td>
            <td style="height: 25px; width: 200px;" >{{$val->product_name}}</td>
            <td style="height: 25px; width: 200px;" >{{$val->product_part_number}}</td>
            <td style="height: 25px; width: 200px;" >{{$val->product_brand}}</td>
            <td style="height: 25px; width: 200px;" >{{$val->dpm_qty}}</td>
            <td style="height: 25px; width: 200px;" >{{$val->dpm_satuan}}</td>
            <td style="height: 25px; width: 200px;" >{!!$val->dpm_notes!!}</td>
            <td style="height: 25px; width: 200px;" >{{$val->dpm_flag == 0 ? "Normal" : "Urgent"}}</td>
            <td style="height: 25px; width: 200px;" >{{$val->po_price_term.' '.$val->po_price_term_location}}</td>
            <td style="height: 25px; width: 200px;" >{{$val->dpm_needed == NULL ? " " : date('d/m/Y',strtotime($val->dpm_needed))}}</td>
            <td style="height: 25px; width: 200px;" >{{$val->last_approval == NULL ? " " : date('d/m/Y',strtotime($val->last_approval))}}</td>
            <td style="height: 25px; width: 200px;" >{{$val->department}}</td>
            <td style="height: 25px; width: 200px;" >{{$val->project}}</td>
            <td style="height: 25px; width: 200px;" >{{getStatusItemExportDPM($val->status, $val->pr_status, $val->po_status,$val->qty_parsial, $val->po_lpb_status, $val->po_qty_parsial, $val->spb_status, $val->bpb_status)}}</td>
            <td style="height: 25px; width: 200px;" >{{$val->alasan_reject_dpm}}</td>
            <td style="height: 25px; width: 200px;" >{{$val->close_pr_by}}</td>
            <td style="height: 25px; width: 200px;" >{{$val->alasan_close_pr}}</td>
            <td style="height: 25px; width: 200px;" >{{$val->created_by}}</td>
            <td style="height: 25px; width: 200px;" >{{$val->location}}</td>
            <td style="height: 25px; width: 200px;" >{{$val->no_po}}</td>
            <td style="height: 25px; width: 200px;" >{{$val->qty_po}}</td>
            <td style="height: 25px; width: 200px;" >{{$val->supplier}}</td>
            <td style="height: 25px; width: 200px;" >{{$val->no_lpb}}</td>
            <td style="height: 25px; width: 200px;" >{{$val->publish_lpb == NULL ? " " : date('d/m/Y',strtotime($val->publish_lpb))}}</td>
            <td style="height: 25px; width: 200px;" >{{$val->qty_lpb}}</td>
            <td style="height: 25px; width: 200px;" >{{$val->no_spb}}</td>
            <td style="height: 25px; width: 200px;" >{{$val->publish_spb == NULL ? " " : date('d/m/Y',strtotime($val->publish_spb))}}</td>
            <td style="height: 25px; width: 200px;" >{{$val->qty_spb}}</td>
            <td style="height: 25px; width: 200px;" >{{$val->no_bpb}}</td>
            <td style="height: 25px; width: 200px;" >{{$val->publish_bpb == NULL ? " " : date('d/m/Y',strtotime($val->publish_bpb))}}</td>
            <td style="height: 25px; width: 200px;" >{{$val->qty_bpb}}</td>
            <td style="height: 25px; width: 200px;" >{{$val->received_bpb}}</td>
        </tr>
        <?php $no++ ?>
        @endforeach
    </tbody>
</table>

