

<table width="100%" style="border:1px solid #000" class="table table-bordered">
    <thead>
        <tr>
            <th width="5" style="font-weight:bold">NO</th>
            <th style="font-weight:bold">NAMA SUPPLIER</th>
            <th style="font-weight:bold">NAMA PIC</th>
            <th style="font-weight:bold">TELP PIC</th>
            <th style="font-weight:bold">EMAIL PIC</th>
            <th style="font-weight:bold">TERAKHIR DIPERBAHARUI</th>
            <th style="font-weight:bold">STATUS</th>
	    </tr>
    </thead>
    <tbody>
    @php 
        $no = 1
    @endphp
    @foreach($data as $item)

        <?php
            $row      = count($item);
            $rowspan  = false;
            if($row > 1){
                $rowspan  = true;
            }
            $first = 1;
        ?>
        @foreach($item as $val)
            <tr>
                @if($first == 1 && $rowspan)
                    <td rowspan="{{ $row }}" style="vertical-align:top;text-align:center">{{ $no }}</td>
                    <td rowspan="{{ $row }}" style="vertical-align:top;">{{ $val->name }}</td>
                @else
                    @if($rowspan == false )
                        <td style="vertical-align:top;text-align:center">{{ $no }}</td>
                        <td>{{ $val->name }}</td>
                    @endif
                @endif
                <td>{{ $val->pic_name }}</td>
                <td>{{ $val->pic_telp }}</td>
                <td>{{ $val->pic_email }}</td>
                @if($first == 1 && $rowspan)
                    <td rowspan="{{ $row }}" style="vertical-align:top;">{{ $val->status == 1 ? "Aktif" : "Non Aktif" }}</td>
                    <td rowspan="{{ $row }}" style="vertical-align:top;">{{ date('d/m/Y',strtotime( $val->updated_at)) }}</td>
                @else
                    @if($rowspan == false )
                        <td>{{ $val->status == 1 ? "Aktif" : "Non Aktif" }}</td>
                        <td>{{ date('d/m/Y',strtotime( $val->updated_at)) }}</td>

                    @endif
                @endif
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

