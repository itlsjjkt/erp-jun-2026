@section('content')
    @php
        $product = getItemDpmById($pid);
        $itempr = getItemPrByIdItemDpm($pid);
        $itempo = getItemPoByIdItemDpm($pid);
        $itemlpb = getItemLpbByIdItemDpm($pid);
        $itemspb = getItemSpbByIdItemDpm($pid);
        $itembpb = getItemBpbByIdItemDpm($pid);
    @endphp

    <style>

        .container {
            width: 100%;
            margin: 30px auto;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .history-title {
            text-align: center;
            margin-bottom: 40px;
            font-size: 24px;
            color: #333;
        }

        .step-container {
            display: flex;
            justify-content: flex-start;
            align-items: flex-start;
            position: relative;
        }



        .step {
            position: relative;
            text-align: center;
            flex: 1;
            padding-right: 20px;
        }

        .step:last-child {
            margin-right: 0;
        }


        .step-icon {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background-color: #ddd;
            margin: 0 auto 10px;
            line-height: 30px;
            color: white;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }

        .step-title {
            font-size: 16px;
            color: #333;
            font-weight: bold;
        }

        .step-description {
            font-size: 14px;
            color: #4e4e4e;
            text-align: left;
        }

        .dpm .step-icon {
            background-color: #17a2b8;
        }

        .pr .step-icon {
            background-color: #0056b3;
        }

        .po .step-icon {
            background-color: #f1c40f;
        }

        .lpb .step-icon {
            background-color: #f39c12;
        }

        .spb .step-icon {
            background-color: #76d793;
        }

        .bpb .step-icon {
            background-color: #28a745;
        }

        .step-container::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            width: 100%;
            height: 2px;
            background-color: #ddd;
            z-index: -1;
        }

        .step-container .step:first-child::before {
            display: none;
        }

        .dpm ~ .step-container::before {
            background-color: #17a2b8;
        }

        .pr ~ .step-container::before {
            background-color: #0056b3;
        }

        .po ~ .step-container::before {
            background-color: #f1c40f;
        }

        .lpb ~ .step-container::before {
            background-color: #f39c12;
        }

        .spb ~ .step-container::before {
            background-color: #76d793;
        }

        .bpb ~ .step-container::before {
            background-color: #28a745;
        }
    </style>

    <div style="margin-top:10px">
        <div>
            <h6 class="isi-dpm">
                <strong>{{$product->product_name}}</strong><br>
                <small>
                    <table style="width:100%;">
                        <tbody>
                            <tr>
                                <td style="width:50%">
                                    <table style="padding-left: 20px;" class="border-0">
                                        <tbody>
                                            <tr>
                                                <td>
                                                    PN
                                                </td>
                                                <td style="padding-left: 5px;">
                                                    : {{$product->part_number ?? '-'}}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    Brand
                                                </td>
                                                <td style="padding-left: 5px;">
                                                    : {{$product->brand ?? '-'}}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    QTY
                                                </td>
                                                <td style="padding-left: 5px;">
                                                    : {{$product->qty ?? '-'}} {{ $product->measure}}
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                                <td style="width:50%">
                                    <table class="border-0">
                                        <tbody>
                                            <tr>
                                                <td>
                                                    NO DPM
                                                </td>
                                                <td style="padding-left: 5px;">
                                                    : {{$product->no_dpm ?? '-'}}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    KAPAL/DEPARTMENT
                                                </td>
                                                <td style="padding-left: 5px;">
                                                    : {{$product->department ?? '-'}}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    PIC LOGISTIK
                                                </td>
                                                <td style="padding-left: 5px;">
                                                    : {{$product->createdbydpm ?? '-'}}
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </small>
            </h6>
        </div>
        <div style="padding:10px;">
            <div class="step-container">
                <table style="width:100%;">
                    <tbody>
                        <tr>
                            @if($product)
                                <td style="vertical-align: top; position: relative;">
                                    <div style="background: {{ count($itempr)>0 ? 'linear-gradient(to right, #17a2b8, #0056b3)' : 'transparent'}}; height: 5px; width: 95%; position: absolute; top: 42%; left: 50%; transform: translate(-50%, -50%);"></div>
                                    <div class="step dpm" style="display: inline-block; text-align: left; text-align: center; padding-top: 20px;">
                                        <div class="step-icon icon-1">
                                            <span class="ti-notepad"></span>
                                        </div>
                                        <div class="step-title">DPM</div>
                                    </div>
                                </td>
                            @endif
                            @if(count($itempr)>0)
                                <td style="vertical-align: top; position: relative;">
                                    <div style="background: {{ count($itempo)>0 ? 'linear-gradient(to right, #0056b3, #f1c40f)' : 'transparent'}}; height: 5px; width: 95%; position: absolute; top: 42%; left: 50%; transform: translate(-50%, -50%);"></div>
                                    <div style="background: #0056b3; height: 5px; width: 6%; position: absolute; top: 42%; left: 0%; transform: translate(-50%, -50%);"></div>
                                    <div class="step pr"style="display:inline-block;text-align:left;text-align: center; padding-top: 20px;">
                                        <div class="step-icon icon-2"><span class="ti-bookmark-alt"></span></div>
                                        <div class="step-title">PR</div>
                                    </div>
                                </td>
                            @endif
                            @if(count($itempo)>0)
                                <td style="vertical-align: top;position: relative;">
                                    <div style="background:
                                        {{ count($itemlpb) > 0
                                            ? 'linear-gradient(to right, #f1c40f, #f39c12)'
                                            : (count($itembpb) > 0
                                                ? 'linear-gradient(to right, #f1c40f, #28a745)'
                                                : 'transparent')
                                        }}; height: 5px; width: 95%; position: absolute; top: 42%; left: 50%; transform: translate(-50%, -50%);">
                                    </div>
                                    <div style="background: #f1c40f; height: 5px; width: 6%; position: absolute; top: 42%; left: 0%; transform: translate(-50%, -50%);"></div>
                                    <div class="step po"style="display:inline-block;text-align:left;text-align: center; padding-top: 20px;">
                                        <div class="step-icon icon-3"><span class="ti-file"></span></div>
                                        <div class="step-title">PO</div>
                                    </div>
                                </td>
                            @endif
                            @if(count($itemlpb)>0)
                                <td style="vertical-align: top;position: relative;">
                                    <div style="background: {{ count($itemspb)>0 ? 'linear-gradient(to right, #f39c12, #76d793)' : 'transparent'}}; height: 5px; width: 95%; position: absolute; top: 42%; left: 50%; transform: translate(-50%, -50%);"></div>
                                    <div style="background: #f39c12; height: 5px; width: 6%; position: absolute; top: 42%; left: 0%; transform: translate(-50%, -50%);"></div>
                                    <div class="step lpb"style="display:inline-block;text-align:left;text-align: center; padding-top: 20px;">
                                        <div class="step-icon icon-4"><span class="ti-receipt"></span></div>
                                        <div class="step-title">LPB</div>
                                    </div>
                                </td>
                            @endif
                            @if(count($itemspb)>0)
                                <td style="vertical-align: top;position: relative;">
                                    <div style="background: {{ count($itembpb)>0 ? 'linear-gradient(to right, #76d793, #28a745)' : 'transparent'}}; height: 5px; width: 95%; position: absolute; top: 42%; left: 50%; transform: translate(-50%, -50%);"></div>
                                    <div style="background: #76d793; height: 5px; width: 6.5%; position: absolute; top: 42%; left: 0%; transform: translate(-50%, -50%);"></div>
                                    <div class="step spb"style="display:inline-block;text-align:left;text-align: center; padding-top: 20px;">
                                        <div class="step-icon icon-5"><span class="ti-envelope"></span></div>
                                        <div class="step-title">SPB</div>
                                    </div>
                                </td>
                            @endif
                            @if(count($itembpb)>0)
                                <td style="vertical-align: top;position: relative;">
                                    <div style="background: #28a745; height: 5px; width: 6.5%; position: absolute; top: 42%; left: 0%; transform: translate(-50%, -50%);"></div>
                                    <div class="step bpb"style="display:inline-block;text-align:left;text-align:left;text-align: center; padding-top: 20px;">
                                        <div class="step-icon icon-6"><span class="ti-envelope"></span></div>
                                        <div class="step-title">BPB</div>
                                    </div>
                                </td>
                            @endif
                        </tr>
                        <tr>
                            @if($product)
                                <td style="vertical-align: top;">
                                    <div class="step dpm">
                                        <div class="step-description">
                                            <li>
                                                {{ $product->tgl_dpm ? getDateId($product->tgl_dpm) : '-'}}
                                                <br>
                                                {{$product->no_dpm}} <br>
                                                <span style="font-weight: bold">[{{$product->qty.' '.$product->measure}}]</span>
                                                <span>{!! getStatusStepItemDpm($product->status, $product->statusdpm) !!}</span>
                                            </li>
                                        </div>
                                    </div>
                                </td>
                            @endif
                            @if(count($itempr)>0)
                                <td style="vertical-align: top;">
                                    <div class="step pr">
                                        <div class="step-description">
                                            @foreach ($itempr as $ipr)
                                                <li>
                                                    {{$ipr->last_approved_at ? getDateId($ipr->last_approved_at) : ($ipr->tgl_pr? getDateId($ipr->tgl_pr) : '-')}}
                                                    <br>
                                                    {{$ipr->no_pr}} <br>
                                                    <span style="font-weight: bold">[{{$ipr->qty.' '.$ipr->measure}}]</span>
                                                    <span>{!! getStatusStepItemPR($ipr->pr_status, $ipr->po_status, (($ipr->qty - getQtyItemPoByPrItemId($ipr->id)) == $ipr->qty ? 0 : ($ipr->qty - getQtyItemPoByPrItemId($ipr->id))) ?? 0, $ipr->type_pr) !!}</span>
                                                </li>
                                            @endforeach
                                        </div>
                                    </div>
                                </td>
                            @endif
                            @if(count($itempo)>0)
                                <td style="vertical-align: top;">
                                    <div class="step po">
                                        <div class="step-description">
                                            @foreach ($itempo as $ipo)
                                                <li>
                                                    {{$ipo->tgl_po ? getDateId($ipo->tgl_po) : '-'}}
                                                    <br>
                                                    {{$ipo->no_po}} <br>
                                                    <span style="font-weight: bold">[{{$ipo->qty.' '.$ipo->measure}}]</span>
                                                    <span>{!! getStatusStepItemPO($ipo->statusPo,$ipo->typePo,$ipo->lpb_status) !!}</span>
                                                </li>
                                            @endforeach
                                        </div>
                                    </div>
                                </td>
                            @endif
                            @if(count($itemlpb)>0)
                                <td style="vertical-align: top;">
                                    <div class="step lpb">
                                        <div class="step-description">
                                            @foreach ($itemlpb as $ilpb)
                                                <li>
                                                    {{$ilpb->tgl_lpb ? getDateId($ilpb->tgl_lpb) : '-'}}
                                                    <br>
                                                    {{$ilpb->no_lpb}} <br>
                                                    <span style="font-weight: bold">[{{$ilpb->qty.' '.$ilpb->satuan}}]</span>
                                                    <span>{!! getStatusStepItemLpb($ilpb->status,$ilpb->statusLpb,$ilpb->spb_statusLpb) !!}</span>
                                                </li>
                                            @endforeach
                                        </div>
                                    </div>
                                </td>
                            @endif
                            @if(count($itemspb)>0)
                                <td style="vertical-align: top;">
                                    <div class="step spb">
                                        <div class="step-description">
                                            @foreach ($itemspb as $ispb)
                                                <li>
                                                    {{$ispb->tgl_spb ? getDateId($ispb->tgl_spb) : '-'}}
                                                    <br>
                                                    {{$ispb->no_spb}} <br>
                                                    <span style="font-weight: bold">[{{$ispb->qty.' '.$ispb->satuan}}]</span>
                                                    <span>{!! getStatusStepItemSpb($ispb->bpb_status,$ispb->statusSpb) !!}</span>
                                                </li>
                                            @endforeach
                                        </div>
                                    </div>
                                </td>
                            @endif
                            @if(count($itembpb)>0)
                                <td style="vertical-align: top;">
                                    <div class="step bpb">
                                        <div class="step-description">
                                            @foreach ($itembpb as $ibpb)
                                                <li>
                                                    {{$ibpb->tgl_bpb ? getDateId($ibpb->tgl_bpb) : '-'}}
                                                    <br>
                                                    {{$ibpb->no_bpb}} <br>
                                                    <span style="font-weight: bold">[{{$ibpb->qty.' '.$ibpb->satuan}}]</span>
                                                    <span>{!! getStatusStepItemBpb($ibpb->statusBpb) !!}</span>
                                                </li>
                                            @endforeach
                                        </div>
                                    </div>
                                </td>
                            @endif
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
