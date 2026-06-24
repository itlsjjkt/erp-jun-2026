<table width="100%" style="border:1px solid #000">
    <thead>
        <tr>
            <th rowspan="2" height="30" style="font-weight:bold; background-color: yellow;">Company</th>
            <th rowspan="2" style="font-weight:bold; background-color: yellow;">PR</th>
            <th rowspan="2" style="font-weight:bold; background-color: yellow;">Department/Kapal</th>
            <th rowspan="2" style="font-weight:bold; background-color: yellow;">PO</th>
            <th rowspan="2" style="font-weight:bold; background-color: yellow;">Purchaser</th>
            <th rowspan="2" style="font-weight:bold; background-color: yellow;">PO Date</th>
            <th rowspan="2" style="font-weight:bold; background-color: yellow;">Supplier</th>
            <th rowspan="2" style="font-weight:bold; background-color: yellow;">Produk</th>
            <th rowspan="2" style="font-weight:bold; background-color: yellow;">PN/Spec</th>
            <th rowspan="2" style="font-weight:bold; background-color: yellow;">Brand</th>
            <th rowspan="2" style="font-weight:bold; background-color: yellow;">Satuan</th>
            <th rowspan="2" style="font-weight:bold; background-color: yellow;">Qty</th>
            <th rowspan="2" style="font-weight:bold; background-color: yellow;">Mata Uang</th>
            <th rowspan="2" style="font-weight:bold; background-color: yellow;">Harga Satuan</th>
            <th rowspan="2" style="font-weight:bold; background-color: yellow;">Discount<br>Satuan(%)</th>
            <th rowspan="2" style="font-weight:bold; background-color: yellow;">Amount Item</th>
            <th rowspan="2" style="font-weight:bold; background-color: yellow;">SubTotal</th>
            <th colspan="2" style="font-weight:bold; background-color: yellow;">Discount</th>
            <th rowspan="2" style="font-weight:bold; background-color: yellow;">Netto</th>
            <th colspan="2" style="font-weight:bold; background-color: yellow;">PPH</th>
            <th colspan="2" style="font-weight:bold; background-color: yellow;">PPN</th>
            <th rowspan="2" style="font-weight:bold; background-color: yellow;">Biaya Kirim</th>
            <th rowspan="2" style="font-weight:bold; background-color: yellow;">Total</th>
        </tr>
        <tr>
            <th style="font-weight:bold; background-color: yellow;">(%)</th>
            <th style="font-weight:bold; background-color: yellow;">Nilai</th>
            <th style="font-weight:bold; background-color: yellow;">(%)</th>
            <th style="font-weight:bold; background-color: yellow;">Nilai</th>
            <th style="font-weight:bold; background-color: yellow;">(%)</th>
            <th style="font-weight:bold; background-color: yellow;">Nilai</th>
        </tr>
    </thead>
    <tbody>
        @foreach($po as $group)
            @php
                $rowspan = count($group);
                $subtotal = 0;
                foreach($group as $item) {
                    $qty = $item->qty ?? 0;
                    $price = $item->price ?? 0;
                    $discount = $item->discount ?? 0;
                    $total = $qty * $price * (1 - ($discount / 100));
                    $subtotal += $total;
                }
            @endphp

            @foreach($group as $index => $item)
                @php
                    $qty = $item->qty ?? 0;
                    $price = $item->price ?? 0;
                    $discount = $item->discount ?? 0;
                    $total = $qty * $price * (1 - ($discount / 100));
                @endphp

                <tr>
                    @if($index === 0)
                        <td rowspan="{{ $rowspan }}">{{ $item->company_alias ?? '-' }}</td>
                        <td rowspan="{{ $rowspan }}">{{ $item->pr_no ?? '-' }}</td>
                        <td rowspan="{{ $rowspan }}">{{ $item->department ?? '-' }}</td>
                        <td rowspan="{{ $rowspan }}">{{ $item->nopo ?? '-' }}</td>
                        <td rowspan="{{ $rowspan }}">{{ $item->purchaser ?? '-' }}</td>
                        <td rowspan="{{ $rowspan }}">{{ $item->tglpembuatanpo ?? '-' }}</td>
                        <td rowspan="{{ $rowspan }}">{{ $item->supplier ?? '-' }}</td>
                    @endif

                    <td>{{ $item->productName ?? '-' }}</td>
                    <td>{{ $item->productPartNumber ?? '-' }}</td>
                    <td>{{ $item->productBrand ?? '-' }}</td>
                    <td>{{ $item->satuan ?? '-' }}</td>
                    <td>{{ $item->qty ?? '-' }}</td>
                    <td>{{ $item->currency_po ?? '-' }}</td>
                    <td>{{ $item->price ?? '-' }}</td>
                    <td>{{ $item->discount ?? '-' }}</td>
                    <td>{{ number_format($total, 0, ',', '') }}</td>

                    @if($index === 0)
                        <td rowspan="{{ $rowspan }}">{{ number_format($subtotal, 0, ',', '') }}</td>
                        <td rowspan="{{ $rowspan }}">
                            @if ($item->discount_item_po == false &&  $item->disc_type_po == 1 &&  $item->discount_amount_po != 0)
                                {{$item->discount_amount_po ?? 0}}
                            @elseif ($item->discount_item_po == false && $item->disc_type_po == 0)

                            @else
                                0
                            @endif
                        </td>
                        <td rowspan="{{ $rowspan }}">
                            @php
                                $total_discount = null;
                                if ($item->discount_item_po == false && $item->disc_type_po == 0) {
                                    $total_discount = $item->discount_amount_po;
                                }
                                echo $total_discount;
                                $diskondokumen = $total_discount;
                                if ($item->disc_type_po == 1 && $item->discount_item_po == false){
                                    $diskondokumen = $subtotal * ($item->discount_amount_po / 100);
                                }
                            @endphp
                        </td>
                        <td rowspan="{{ $rowspan }}">
                            {{$subtotal - $diskondokumen}}
                        </td>

                        {{-- PPH --}}
                        <td rowspan="{{ $rowspan }}">
                            {{$item->pph_po ?? 0}}
                        </td>
                        <td rowspan="{{ $rowspan }}">
                            @php
                                $pph_po = 	(($item->pph_po ?? 0) / 100) * ($subtotal - $diskondokumen);
                                echo $pph_po;
                            @endphp
                        </td>

                        {{-- PPN --}}
                        <td rowspan="{{ $rowspan }}">
                            {{$item->ppn_po ?? 0}}
                        </td>
                        <td rowspan="{{ $rowspan }}">
                            @php
                                $ppn_ = $item->ppn_po ?? 0;
                                $ppn_po = 	($ppn_ / 100) * ($subtotal - $diskondokumen);
                                echo $ppn_po;
                            @endphp
                        </td>

                        <td rowspan="{{ $rowspan }}">
                            @php
                                $send_expense_po = $item->send_expense_po ?? 0;
                                if ($item->send_expense_ppn_po == 1 || $item->send_expense_ppn_po == 11) {
                                    $send_expense_ppn_po = (11 / 100) * $send_expense_po;
                                    $send_expense_po = $send_expense_ppn_po + $send_expense_po;
                                }
                                echo $send_expense_po;
                            @endphp
                        </td>
                        <td rowspan="{{ $rowspan }}">
                            @php
                                $totalAkhir =  (($subtotal - $diskondokumen) +  $ppn_po + $send_expense_po) - $pph_po;
                                echo $totalAkhir;
                            @endphp
                        </td>
                    @endif
                </tr>
            @endforeach
        @endforeach
    </tbody>
</table>
