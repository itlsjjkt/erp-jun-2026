<table style="border-collapse: collapse;">
    <tbody>
        @php
            $suppliers = getSupplierByDph($dph->id);
            $supplierTotals = array_fill(0, count($suppliers), 0);
            $discountValue = array_fill(0, count($suppliers), 0);
            $totalAfterDiscount = array_fill(0, count($suppliers), 0);
            $ppnValues = array_fill(0, count($suppliers), 0);
            $grandTotal = 0;
        @endphp

        <tr style="border: 2px solid black; padding: 5px;">
            @php
                $firstSup = true;
            @endphp
            @foreach($suppliers as $index => $sup)
                @if($firstSup)
                <td colspan="2" style="font-weight:bold;text-align:right;">
                    NO DPH : <br>
                    COMPANY : <br>
                    DEPARTMENT/KAPAL : <br>
                    PROJECT : <br>
                </td>
                <td colspan="2">
                    {{$dph->doc_no}} <br>
                    {{$dph->company}} <br>
                    {{$dph->department}} <br>
                    {{$dph->project}} <br>
                </td>
                @php
                    $firstSup = false;
                @endphp
                @endif
                <td colspan="1" style="height: 100px; font-weight: bold; text-align: right; vertical-align:top;">
                    Supplier :<br>
                    PIC :<br>
                    Phone :<br>
                    Email :<br>
                </td>
                <td colspan="6" style="text-align: left; vertical-align:top;">
                    {{$sup->supplier ?? '-'}} <br>
                    {{ $sup->picTitle ?? '' }} {{ $sup->picName ?? '-' }} <br>
                    {!! $sup->picTelp ?? '-' !!} <br>
                    {{ $sup->picEmail ?? '-' }} <br>
                </td>
            @endforeach
        </tr>

        <tr>
            <th style="border-bottom: 2px solid black;border-top:2px solid black;border-right:2px solid black;border-left:2px solid black; font-weight: bold;width:25px; height:25px; vertical-align:top;">NO</th>
            <th style="border-bottom: 2px solid black;border-top:2px solid black;border-right:2px solid black;border-left:2px solid black; font-weight: bold;width:150px;vertical-align:top;">Nama Item</th>
            <th style="border-bottom: 2px solid black;border-top:2px solid black;border-right:2px solid black;border-left:2px solid black; font-weight: bold;width:225px;vertical-align:top;">Brand</th>
            <th style="border-bottom: 2px solid black;border-top:2px solid black;border-right:2px solid black;border-left:2px solid black; font-weight: bold;width:225px;vertical-align:top;">Part Number</th>
            @foreach($suppliers as $sup)
                <th style="border-bottom: 2px solid black;border-top:2px solid black;border-right:2px solid black;border-left:2px solid black; vertical-align:top; font-weight: bold;width:150px;">Description</th>
                <th style="border-bottom: 2px solid black;border-top:2px solid black;border-right:2px solid black;border-left:2px solid black; vertical-align:top; font-weight: bold;width:150px;text-align:center">Qty</th>
                <th style="border-bottom: 2px solid black;border-top:2px solid black;border-right:2px solid black;border-left:2px solid black; vertical-align:top; font-weight: bold;width:150px;">Satuan</th>
                <th style="border-bottom: 2px solid black;border-top:2px solid black;border-right:2px solid black;border-left:2px solid black; vertical-align:top; font-weight: bold;width:150px;">Harga satuan</th>
                <th style="border-bottom: 2px solid black;border-top:2px solid black;border-right:2px solid black;border-left:2px solid black; vertical-align:top; font-weight: bold;width:50px;text-align:center">Disc @if($sup->discount_type == 1) (%) @endif</th>
                <th style="border-bottom: 2px solid black;border-top:2px solid black;border-right:2px solid black;border-left:2px solid black; vertical-align:top; font-weight: bold;width:150px; text-align:center;" colspan="2">Total</th>
            @endforeach
        </tr>

        @php
            $maxItems = 0;
            foreach($suppliers as $sup) {
                $items = getDphItemByDphSupplier($sup->id);
                if(count($items) > $maxItems) {
                    $maxItems = count($items);
                }
            }
        @endphp

        @for($i = 0; $i < $maxItems; $i++)
            <tr>
                @php
                    $firstItem = true;
                @endphp

                @foreach($suppliers as $index => $sup)
                    @php
                        $items = getDphItemByDphSupplier($sup->id);
                        $item = isset($items[$i]) ? $items[$i] : null;
                    @endphp

                    @if($firstItem)
                        {{-- NO, Produk, Partnumber --}}
                        <td style="vertical-align:middle;border-bottom: 2px solid black;border-top:2px solid black;border-right:2px solid black;border-left:2px solid black;text-align:center;vertical-align:top;">{{ $i+1 }}</td>
                        <td style="border-bottom: 2px solid black;border-top:2px solid black;border-right:2px solid black;border-left:2px solid black;vertical-align:top;">{{ $item->product ?? '-' }}</td>
                        <td style="border-bottom: 2px solid black;border-top:2px solid black;border-right:2px solid black;border-left:2px solid black;vertical-align:top;">{{ $item->productBrand ?? '-' }}</td>
                        <td style="border-bottom: 2px solid black;border-top:2px solid black;border-right:2px solid black;border-left:2px solid black;word-wrap: break-word; overflow-wrap: break-word;vertical-align:top;">{{ $item->productPartNumber ?? '-' }}</td>
                        @php
                            $firstItem = false;
                        @endphp
                    @endif

                    {{-- Qty, Satuan, Harga satuan, Disc, Total --}}
                    <td style="vertical-align:top;border-bottom: 2px solid black; border-top: 2px solid black; border-right: 2px solid black; border-left: 2px solid black; word-wrap: break-word; overflow-wrap: break-word; text-align: left; vertical-align: top; white-space: pre-wrap;@if($item->is_recomendation == 1) background-color:#c3e6cb; @endif">
                        {!! $item->specification ?? '-' !!}
                    </td>
                    <td style="border-bottom: 2px solid black;border-top:2px solid black;border-right:2px solid black;border-left:2px solid black;text-align:center;vertical-align:top;@if($item->is_recomendation == 1) background-color:#c3e6cb; @endif">{{ $item->qty ?? '-' }}</td>
                    <td style="border-bottom: 2px solid black;border-top:2px solid black;border-right:2px solid black;border-left:2px solid black;vertical-align:top;@if($item->is_recomendation == 1) background-color:#c3e6cb; @endif">{{ $item->measure ?? '-' }}</td>
                    <td style="border-bottom: 2px solid black;border-top:2px solid black;border-right:2px solid black;border-left:2px solid black;text-align: right;vertical-align:top;@if($item->is_recomendation == 1) background-color:#c3e6cb; @endif">{{ format_number($item->price) ?? '-' }}</td>
                    <td style="border-bottom: 2px solid black;border-top:2px solid black;border-right:2px solid black;border-left:2px solid black;text-align:center;vertical-align:top;@if($item->is_recomendation == 1) background-color:#c3e6cb; @endif">{{ $item->discount ?? '-' }}</td>
                    <td colspan="2" style="border-right: 2px solid black;border-bottom: 2px solid black;border-top:2px solid black;border-right:2px solid black;border-left:2px solid black;text-align: right;vertical-align:top;@if($item->is_recomendation == 1) background-color:#c3e6cb; @endif">
                        <?php
                            $total = $item->price * $item->qty - (($item->price * $item->qty) * $item->discount / 100);
                            $supplierTotals[$index] += $total;
                            $pay_term[$index] = $sup->payment_term;
                            $franco[$index] = $sup->price_term .' - '. $sup->price_term_location;
                            $w_kirim[$index] = $sup->estimated_delivery_day . ' Hari';
                            $m_uang[$index] = $sup->currency;
                            $pay_method[$index] = $sup->payment_method;
                        ?>
                        {{format_number($total)}}
                    </td>
                @endforeach
            </tr>
        @endfor

        {{-- Total per Supplier --}}
        <tr>
            <td style="border-left: 2px solid black;text-align: center;font-weight:bold" colspan="2">Prepared By</td>
            <td style="border-right: 2px solid black;text-align: center;font-weight:bold" colspan="2">Approved By</td>
            @foreach($supplierTotals as $idx => $totalz)
                <td style="vertical-align: top; word-wrap: break-word; white-space: normal;text-align:right;">
                    <span style="font-weight: bold;">Payment Method :
                </td>
                <td style="vertical-align: top; word-wrap: break-word; white-space: normal;" colspan="2">
                    <span style="font-weight: bold;">{{$pay_method[$idx]}}
                </td>
                <td style="height: 25px; text-align:right; vertical-align:top;">Sub Total :</td>
                <td style="text-align: right;">
                    {{$m_uang[$idx]}}
                </td>
                <td colspan="2" style="border-right: 2px solid black;text-align:right; vertical-align:top;">
                    {{ format_number($totalz) ?? '-' }} <br>
                </td>
            @endforeach
        </tr>

        {{-- Discount Row --}}
        <tr>

            <td rowspan="4"colspan="2" style="border-left: 2px solid black;"></td>
            <td rowspan="4"></td>
            <td rowspan="4"style="border-right: 2px solid black;"></td>
            @foreach($suppliers as $index => $sup)
                <td style="vertical-align: top; word-wrap: break-word; white-space: normal;text-align:right;">
                    <span style="font-weight: bold;">Payment Term :
                </td>
                <td style="vertical-align: top; word-wrap: break-word; white-space: normal;" colspan="2">
                    <span style="font-weight: bold;">{{$pay_term[$index]}}
                </td>
                <td style="height: 25px; text-align:right; vertical-align:top;">
                    Discount @if($sup->discount_item == false && $sup->discount_type == 1 && $sup->discount_amount != 0) (<small>{{$sup->discount_amount}}%</small>) @endif :
                </td>
                <td style="text-align: right;">{{$sup->currencysymbol}} </td>
                <td colspan="2" style="border-right: 2px solid black;text-align:right; vertical-align:top;">
                    <?php
                        if ($sup->discount_item == false && $sup->discount_type == 1) {
                            $discountValue[$index] = $supplierTotals[$index] * ($sup->discount_amount / 100);
                        } elseif ($sup->discount_item == true && $sup->discount_type == 1) {
                            $discountValue[$index] = 0;
                        } else {
                            $discountValue[$index] = $sup->discount_amount;
                        }
                        echo format_number($discountValue[$index]);
                    ?>
                </td>
            @endforeach
        </tr>

        {{-- After Discount Row --}}
        <tr>
            @foreach($suppliers as $index => $sup)
                <td style="vertical-align: top; word-wrap: break-word; white-space: normal;text-align:right;">
                    <span style="font-weight: bold;">Franco :
                </td>
                <td style="vertical-align: top; word-wrap: break-word; white-space: normal;" colspan="2">
                    <span style="font-weight: bold;">{{$franco[$index]}}
                </td>
                <td style="height: 25px; text-align:right; vertical-align:top;">
                    After Discount :
                </td>
                <td style="text-align: right;">{{$sup->currencysymbol}} </td>
                <td colspan="2" style="border-right: 2px solid black;text-align:right; vertical-align:top;">
                    <?php
                        // Hitung nilai setelah diskon
                        $totalAfterDiscount[$index] = $supplierTotals[$index] - $discountValue[$index];
                        echo format_number($totalAfterDiscount[$index]);
                    ?>
                </td>
            @endforeach
        </tr>

        {{-- PPN Row --}}
        <tr>
            @foreach($suppliers as $index => $sup)
                <td style="vertical-align: top; word-wrap: break-word; white-space: normal;text-align:right;">
                    <span style="font-weight: bold;">Waktu Pengiriman :
                </td>
                <td style="vertical-align: top; word-wrap: break-word; white-space: normal;" colspan="2">
                    <span style="font-weight: bold;">{{$w_kirim[$index]}}
                </td>
                <td style="height: 25px; text-align:right; vertical-align:top;">
                    PPN @if($sup->ppn != 0) ({{$sup->ppn}} %) @endif :
                </td>
                <td style="text-align: right;">{{$sup->currencysymbol}} </td>
                <td colspan="2" style="border-right: 2px solid black;text-align:right; vertical-align:top;">
                    <?php
                        $ppnValues[$index] = $totalAfterDiscount[$index] * ($sup->ppn / 100);
                        echo format_number($ppnValues[$index]);
                    ?>
                </td>
            @endforeach
        </tr>
        {{-- Biaya Pengiriman Row --}}
        <tr>
            @foreach($suppliers as $index => $sup)
                <td style="vertical-align: top; word-wrap: break-word; white-space: normal;text-align:right;">

                </td>
                <td colspan="2"></td>
                <td style="height: 25px; text-align:right; vertical-align:top;">
                    Biaya Pengiriman @if($sup->send_expense_ppn != 0) (+PPN{{$sup->send_expense_ppn}}%) @endif:
                </td>
                <td style="text-align: right;">{{$sup->currencysymbol}} </td>
                <td colspan="2" style="border-right: 2px solid black;text-align:right; vertical-align:top;">
                    <?php
                        $send_expense[$index] = $sup->send_expense;
                        $send_expense_ppn_caption[$index] = '';
                        if ($sup->send_expense_ppn != 0) {
                            $send_expense_ppn_caption[$index] = "+PPN ".$sup->send_expense_ppn."%";
                            $send_expense_ppn[$index] = ($sup->send_expense_ppn / 100) * $send_expense[$index];
                            $send_expense[$index] = $send_expense_ppn[$index] + $send_expense[$index];
                        }
                        echo format_number($send_expense[$index]);
                    ?>
                </td>
            @endforeach
        </tr>

        {{-- Total Row --}}
        <tr>
            {{-- <td style="border-left: 2px solid black;text-align: center;vertical-align:top; font-weight:bold;" colspan="2">Michael Salim</td> --}}
            <td style="border-left: 2px solid black;text-align: center;vertical-align:top; font-weight:bold;" colspan="2">{{getUserByID($dph->created_by)}}</td>
            <td style="text-align: center;vertical-align:top; font-weight:bold;">Denny Wahyono</td>
            <td style="border-right: 2px solid black;text-align: center;vertical-align:top; font-weight:bold;">Lim Liana Sarwono</td>
            @foreach($suppliers as $index => $sup)
                <td style="vertical-align: top; word-wrap: break-word; white-space: normal;text-align:right;">

                </td>
                <td colspan="2"></td>
                <td style="height: 25px; text-align:right; vertical-align:top;">
                    Total :
                </td>
                <td style="text-align: right;vertical-align:top;font-weight:bold;">{{$sup->currencysymbol}} </td>
                <td colspan="2" style="border-right: 2px solid black;text-align:right; vertical-align:top;font-weight:bold;">
                    <?php
                        $subTotal[$index] = $totalAfterDiscount[$index] + $ppnValues[$index] + $send_expense[$index];
                        echo format_number($subTotal[$index]);
                    ?>
                </td>
            @endforeach
        </tr>

        {{-- Payment Term Row --}}
        <tr>
            <td colspan="2" style="border-left: 2px solid black;border-bottom: 2px solid black;text-align: center;vertical-align:top;">Purchaser</td>
            <td style="border-bottom: 2px solid black;text-align: center;vertical-align:top;">Sot Purchasing</td>
            <td style="border-right: 2px solid black;border-bottom: 2px solid black;text-align: center;vertical-align:top;">Head of Dept.Purchasing</td>
            @foreach($suppliers as $index => $sup)
                <td style="border-bottom: 2px solid black; vertical-align: top; word-wrap: break-word; white-space: normal;text-align:right;">

                </td>
                <td style="border-bottom: 2px solid black;" colspan="2"></td>
                <td style="border-bottom: 2px solid black;height: 25px; text-align:right; vertical-align:top;">
                    Uang Muka :
                </td>
                <td style="border-bottom: 2px solid black;text-align: right; vertical-align:top;">{{$sup->currencysymbol}} </td>
                <td colspan="2" style="border-bottom: 2px solid black;border-right: 2px solid black;text-align:right; vertical-align:top;">
                    {{format_number($subTotal[$index] * $sup->dp_percentage / 100)}}
                </td>
            @endforeach
        </tr>
        <tr>
            <td></td>
        </tr>
        <tr>
            <td style="height: 30px; vertical-align:middle;font-weight:bold;">CATATAN :</td>
        </tr>
        <tr>
            <td></td>
            <td colspan="3" rowspan="10" style="vertical-align: top; margin-left:10px;">{!! $dph->notes !!}</td>
        </tr>
    </tbody>
</table>
