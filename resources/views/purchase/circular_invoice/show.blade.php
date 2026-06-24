@extends('layouts.app')

@section('page-header')
    Sirkular Invoice
@endsection

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('purchasing.circular_invoice') }}">Sirkular Invoice</a></li>
        <li class="breadcrumb-item active" aria-current="page">Detail Sirkular Invoice</li>
    </ol>
@endsection

@section('content')
    <div class="mB-40">
        <div class="bgc-white p-30 bd">

            @php
                use Illuminate\Support\Facades\Gate;
            @endphp

            {{-- BUTTON PRINT --}}
            @if ($invoice->status == 1)
                <div class="row mb-1 justify-content-end">
                    <div class="col-sm-12">
                        <a class="btn btn-outline float-right" href="#"
                            onclick="openPrintWindow('{{ route('purchasing.circular_invoice.print', Hashids::encode($invoice->id)) }}')"
                            title="Print Data">
                            <i class="ti-printer icon-lg"></i>
                        </a>
                    </div>
                </div>
            @endif

            {{-- BUTTON TAB --}}
            <div class="d-block">
                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-toggle="tab" href="#tab1" role="tab">Sirkular Invoice</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#tab2" role="tab">Riwayat Sirkular Invoice</a>
                    </li>
                </ul>
            </div>

            <div class="tab-content mT-30">

                {{-- TAB 1 --}}
                <div class="tab-pane active" id="tab1" role="tabpanel" aria-labelledby="tab1">
                    <h6 class="text-center font-weight-bold mB-30" style="text-decoration:underline">{{ $invoice->doc_no }}
                    </h6>

                    {{-- PENERIMAAN --}}
                    {{-- @php
                        $doneHistory = isset($histories) ? collect($histories)->firstWhere('type', 'selesai') : null;
                    @endphp --}}

                    @if ((int) $invoice->status === 2)
                        <div class="alert alert-success si-receipt-box">
                            <div class="font-weight-bold mb-2">DETAIL PENERIMAAN :</div>

                            <table class="table table-bordered" style="width: 100%; table-layout: fixed;">
                                <colgroup>
                                    <col style="width: 30%;">
                                    <col style="width: 35%;">
                                    <col style="width: 35%;">
                                </colgroup>
                                <tr>
                                    <td>Nama Penerima</td>
                                    <td colspan="2">{{ strtoupper($invoice->recipient_name) ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td>Tanggal Penerimaan</td>
                                    <td colspan="2">
                                        {{ $invoice->receipt_date ? \Carbon\Carbon::parse($invoice->receipt_date)->format('d/m/Y') : '' }}
                                    </td>
                                </tr>
                                {{-- <tr>
                                    <td>Dibuat Oleh</td>
                                    <td colspan="2">
                                        {{ strtoupper($doneHistory->user_name) ?? strtoupper($invoice->updated_by_name ?? '-') }}
                                    </td>
                                </tr> --}}
                                <tr>
                                    <td>Catatan Penerimaan</td>
                                    <td colspan="2">{!! $invoice->receipt_note ? nl2br(e(strip_tags($invoice->receipt_note))) : '-' !!}</td>
                                </tr>
                                <tr>

                                </tr>
                            </table>
                        </div>
                    @endif

                    {{-- Data Table --}}
                    <table class="table table-bordered" style="width: 100%; table-layout: fixed;">
                        <colgroup>
                            <col style="width: 30%;">
                            <col style="width: 35%;">
                            <col style="width: 35%;">
                        </colgroup>
                        <tr>
                            <td>No Sirkular Invoice</td>
                            <td colspan="2">{{ $invoice->doc_no }}</td>
                        </tr>
                        <tr>
                            <td>Perusahaan</td>
                            <td colspan="2">{{ strtoupper($invoice->nama_pt) }}</td>
                        </tr>
                        <tr>
                            <td>Supplier</td>
                            <td colspan="2">{{ $invoice->nama_supplier }}</td>
                        </tr>
                        <tr>
                            <td>No PO / Tgl PO</td>
                            <td>{{ $invoice->po_number }}</td>
                            <td>{{ date('d/m/Y', strtotime($invoice->po_tgl)) }}</td>
                        </tr>
                        <tr>
                            <td>No PR / Tgl PR</td>
                            <td>{{ $invoice->pr_no }}</td>
                            <td>{{ date('d/m/Y', strtotime($invoice->pr_tgl)) }}</td>
                        </tr>
                        <tr>
                            <td>No Faktur Pajak</td>
                            <td colspan="2">{{ $invoice->tax_invoice }}</td>
                        </tr>
                        <tr>
                            <td>Tgl Surat Jalan</td>
                            <td colspan="2">{{ date('d/m/Y', strtotime($invoice->date_delivery_note)) }}</td>
                        </tr>
                        <tr>
                            <td>No Invoice Ext / Tgl Invoice Ext</td>
                            <td>{{ $invoice->invoice_number_ext }}</td>
                            <td>{{ date('d/m/Y', strtotime($invoice->date_invoice_ext)) }}</td>
                        </tr>
                        <tr>
                            <td>Tgl Terima Invoice Ext</td>
                            <td colspan="2">{{ date('d/m/Y', strtotime($invoice->date_received_invoice)) }}</td>
                        </tr>
                        <tr>
                            <td>Tgl Jatuh Tempo</td>
                            <td colspan="2">{{ date('d/m/Y', strtotime($invoice->due_date_payment)) }}</td>
                        </tr>
                        <tr>
                            <td>Termin Pembayaran</td>
                            <td colspan="2">{{ strtoupper($invoice->payment_terms_nama) }}</td>
                        </tr>
                        <tr>
                            <td>Tipe Pembayaran</td>
                            <td colspan="2">
                                @if ($invoice->type_payment == 1)
                                    {!! getTypeBodyEmal(1) !!}
                                @elseif($invoice->type_payment == 2)
                                    {!! getTypeBodyEmal(2) !!}
                                @elseif($invoice->type_payment == 3)
                                    {!! getTypeBodyEmal(3) !!}
                                @elseif($invoice->type_payment == 4)
                                    {!! getTypeBodyEmal(4) !!}
                                @else
                                    {!! getTypeBodyEmal(null) !!}
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td>Jumlah / Notes</td>
                            <td colspan="2">
                                <strong>{{ $invoice->po_mata_uang }}
                                    {{ number_format($invoice->payment_amount, 2, ',', '.') }}</strong>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="3">&nbsp;</td>
                        </tr>
                        <tr>
                            <td>Status Sirkular Invoice</td>
                            <td colspan="2">
                                @if ($invoice->status_si == 0)
                                    <span class='badge badge-warning'>Draft</span>
                                @elseif ($invoice->status_si == 1)
                                    <span class='badge badge-primary'>Publish</span>
                                @elseif ($invoice->status_si == 2)
                                    <span class='badge badge-success'>Selesai</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td>User Pembuat</td>
                            <td colspan="2">{!! $invoice->nama_pembuat !!}</td>
                        </tr>
                        <tr>
                            <td>Note</td>
                            <td colspan="2">{!! $invoice->note !!}</td>
                        </tr>
                    </table>
                </div>

                {{-- TAB 2 --}}
                @if (count($histories) > 0)
                    <div class="tab-pane" id="tab2" role="tabpanel" aria-labelledby="tab2">
                        <div class="timeline">

                            <div class="timeline__box">
                                <div class="timeline__date"
                                    style="width:auto !important;height: auto !important;border-radius: 0;left: 30px;">
                                    <span class="timeline__month">Status SI Saat Ini</span>
                                    <h5>{{ strtoupper($invoice->status == 1 ? 'Publish' : 'Draft') }}</h5>
                                </div>
                            </div>

                            <div class="timeline__group">
                                @foreach ($histories as $history)
                                    <div class="timeline__box">
                                        <div class="timeline__date"></div>
                                        <div class="timeline__post">
                                            <div class="timeline__content">
                                                <span>{{ \Carbon\Carbon::parse($history->created_at)->format('d/m/Y H:i') }}</span><br>
                                                <p>
                                                    <strong>{{ $history->user_name ?? 'System' }}</strong>
                                                    melakukan <strong>{{ ucfirst($history->type) }}</strong><br>
                                                    {!! $history->message !!}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                        </div>
                    </div>
                @endif

            </div>

        </div>
    </div>
@stop

@section('js')
    <script>
        function openPrintWindow(url) {
            var printWindow = window.open(url, "_blank", "width=1000,height=800");
            printWindow.focus();
            printWindow.onload = function() {
                setTimeout(function() {
                    printWindow.print();
                }, 500);
            };
        }
    </script>
@stop
