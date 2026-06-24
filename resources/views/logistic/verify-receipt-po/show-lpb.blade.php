@extends('layouts.app')

@section('page-header')
    Verify LPB <small>{{ $lpb->doc_no }}</small>
@endsection

@section('breadcrumbs')
<ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('logistic.verify-receipt-po.index') }}">Verify Receipt PO</a></li>
    <li class="breadcrumb-item active">Detail LPB</li>
</ol>
@endsection

@section('content')
<div class="mB-40">
    <div class="bgc-white p-30 bd">

        <div class="row mb-3">
            <div class="col-sm-6">
                <a href="{{ route('logistic.verify-receipt-po.index') }}" class="nav-link">
                    <i class="ti-arrow-left"></i> Kembali
                </a>
            </div>
            <div class="col-sm-6 text-right">
                <a href="#" class="btn btn-outline"
                    onclick='printExternal("/logistic/lpb_print/{{ Hashids::encode($lpb->id) }}/print")'>
                    <i class="ti-printer icon-lg"></i>
                </a>
            </div>
        </div>

        {{-- VERIFY BANNER --}}
        @if($lpb->verified_at)
            <div class="alert alert-success mb-4">
                <div>
                    <span><i class="ti-check mr-2"></i>Diverifikasi oleh <strong>{{ getUserByID($lpb->verified_by) }}</strong> pada {{ \Carbon\Carbon::parse($lpb->verified_at)->format('d/m/Y H:i') }}</span>
                    @if($lpb->verified_notes)
                        <br><small class="mt-1 d-block"><strong>Catatan:</strong> {{ $lpb->verified_notes }}</small>
                    @endif
                </div>
            </div>
        @else
            {{-- Tampilkan catatan request perbaikan jika ada --}}
            @if($lpb->verify_request_at)
                <div class="alert alert-danger mb-3" id="request-perbaikan-info">
                    <span><i class="ti-comment-alt mr-2"></i>Catatan dari <strong>{{ getUserByID($lpb->verify_request_by) }}</strong> pada {{ \Carbon\Carbon::parse($lpb->verify_request_at)->format('d/m/Y H:i') }}:</span>
                    <br><span class="mt-1 d-block">{{ $lpb->verify_request_notes }}</span>
                </div>
            @endif

            <div class="alert alert-warning mb-4" id="verify-banner">
                <div class="d-flex justify-content-between align-items-start">
                    <span><i class="ti-info-alt mr-2"></i>Dokumen ini belum diverifikasi.</span>
                </div>

                {{-- Form Request Perbaikan --}}
                <div class="mt-3 border-top pt-3">
                    <label class="font-weight-bold text-danger">
                        <i class="ti-comment-alt mr-1"></i> Minta Perbaikan
                        <small class="text-muted font-weight-normal">(opsional, isi jika ada yang perlu diperbaiki)</small>
                    </label>
                    <textarea id="verify-request-notes-input" class="form-control mt-1" rows="2"
                        placeholder="Tulis catatan perbaikan untuk pembuat dokumen...">{{ $lpb->verify_request_notes }}</textarea>
                    <div class="mt-2">
                        <button class="btn btn-warning btn-request-perbaikan"
                            data-id="{{ Hashids::encode($lpb->id) }}"
                            data-type="lpb">
                            <i class="ti-comment-alt mr-1"></i> Kirim Catatan
                        </button>
                    </div>
                </div>

                {{-- Form Verify --}}
                <div class="mt-3 border-top pt-3">
                    <label class="font-weight-bold text-success">
                        Catatan Verifikasi
                        <small class="text-muted font-weight-normal">(opsional)</small>
                    </label>
                    <textarea id="verified-notes-input" class="form-control mt-1" rows="2"
                        placeholder="Tulis catatan verifikasi..."></textarea>
                    <div class="mt-2 text-right">
                        <button class="btn btn-success btn-verify-receipt"
                            data-id="{{ Hashids::encode($lpb->id) }}"
                            data-type="lpb">
                            <i class="ti-check mr-1"></i> Verify Dokumen
                        </button>
                    </div>
                </div>
            </div>
        @endif

        {{-- TABS --}}
        <div class="d-block">
            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" data-toggle="tab" href="#tab1" role="tab">LPB</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#tab-history" role="tab">Histori</a>
                </li>
                <li class="nav-item">
                    @php
                        $badge = $lpb->attachment_file
                            ? "<sup class='badge'><span class='bg-success rounded-circle text-success' style='padding:0 5px'></span></sup>"
                            : "";
                    @endphp
                    <a class="nav-link" data-toggle="tab" href="#tab-attachment" role="tab">Attachment {!! $badge !!}</a>
                </li>
            </ul>
        </div>

        <div class="tab-content mt-4">

            {{-- TAB 1: DETAIL --}}
            <div class="tab-pane active" id="tab1" role="tabpanel">
                <h6 class="text-center font-weight-bold mB-30" style="text-decoration:underline">{{ $lpb->doc_no }}</h6>
                <div class="row">
                    <div class="col-sm-6">
                        <div class="row">
                            <label class="col-sm-4">No. PO</label>
                            <div class="col-sm-7">: {{ $lpb->po_no }}</div>
                        </div>
                        <div class="row">
                            <label class="col-sm-4">No. PR</label>
                            <div class="col-sm-7">: {{ $lpb->pr_no }}</div>
                        </div>
                        <div class="row">
                            <label class="col-sm-4">No. DPM</label>
                            <div class="col-sm-7">: {{ $lpb->dpm_no }}</div>
                        </div>
                        <div class="row">
                            <label class="col-sm-4">Kapal / Departement</label>
                            <div class="col-sm-7">: {{ $lpb->department }}</div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="row">
                            <label class="col-sm-3">Penerima</label>
                            <div class="col-sm-7">: {{ $lpb->received_by }}</div>
                        </div>
                        <div class="row">
                            <label class="col-sm-3">Dibuat Oleh</label>
                            <div class="col-sm-7">: {{ $lpb->created }} [{{ idDate($lpb->created_at) }}]</div>
                        </div>
                        <div class="row">
                            <label class="col-sm-3">Supplier</label>
                            <div class="col-sm-8">: {{ $lpb->supplier }}<br>
                                <small>[ Nama: {{ $lpb->supplierPIC }} / Telp. {{ $lpb->supplierTelp }} ]</small>
                            </div>
                        </div>
                    </div>
                </div>

                @if ($lpb->status == 3)
                    <hr>
                    <div class="alert alert-danger"><strong>DITUTUP</strong><br><small>{!! $lpb->reason !!}</small></div>
                @endif

                <h6 class="mT-30">Daftar Barang</h6>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th rowspan="2" style="width:50px">No</th>
                            <th rowspan="2" style="width:350px">Nama Barang</th>
                            <th rowspan="2" style="width:300px">Spesifikasi</th>
                            <th colspan="2" class="text-center">Jumlah</th>
                            <th rowspan="2" class="text-center">Satuan</th>
                            <th rowspan="2" class="text-center">Catatan</th>
                        </tr>
                        <tr>
                            <th class="text-center" style="width:150px">Dipesan</th>
                            <th class="text-center" style="width:150px">Diterima</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $no = 1; @endphp
                        @foreach ($lpb_items as $item)
                            <tr>
                                <td>{{ $no }}</td>
                                <td>
                                    {{ $item->productCode }} - {{ $item->product }}
                                    {!! $item->productPartNumber ? '<br>PN/Spec: '.$item->productPartNumber : '' !!}
                                    {{ $item->productBrand ? 'Brand: '.$item->productBrand : '' }}
                                </td>
                                <td>{!! $item->specification !!}</td>
                                <td class="text-center">{{ $item->qtyPO }}</td>
                                <td class="text-center">{{ $item->qty }}</td>
                                <td>{{ $item->measure }}</td>
                                <td>{{ $item->notes }}</td>
                            </tr>
                            @php $no++; @endphp
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- TAB HISTORI --}}
            <div class="tab-pane" id="tab-history" role="tabpanel">
                <div class="timeline">
                    <div class="timeline__group">
                        @foreach ($lpb_history as $val)
                            <div class="timeline__box">
                                <div class="timeline__date"></div>
                                <div class="timeline__post">
                                    <div class="timeline__content">
                                        <span>{{ date('d/m/Y H:i A', strtotime($val->created_at)) }}</span><br>
                                        <strong>{{ ucwords(strtolower($val->employee)) }}</strong>
                                        @if($val->jenis == 'insert') melakukan pengajuan LPB
                                        @elseif($val->jenis == 'draft') melakukan pengajuan LPB dengan status Draft
                                        @elseif($val->jenis == 'revisi') melakukan revisi LPB
                                        @elseif($val->jenis == 'update_dokumen') melakukan update attachment dokumen LPB
                                        @else melakukan publish LPB
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- TAB ATTACHMENT --}}
            <div class="tab-pane" id="tab-attachment" role="tabpanel">
                <div class="row">
                    <div class="col-12">
                        @if ($lpb->attachment_file)
                            <p class="text-center">
                                <embed class="col align-self-center"
                                    src="{{ asset('storage'.$lpb->attachment_file) }}"
                                    width="600" height="800" alt="pdf" />
                            </p>
                        @else
                            <p class="text-center text-muted mt-4">Belum ada attachment dokumen LPB.</p>
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@section('js')
<script>
    function printExternal(url) {
        var printWindow = window.open(url, 'Print', 'left=200, top=200, width=950, height=800, toolbar=0, resizable=0');
        printWindow.addEventListener('load', function() { printWindow.print(); }, true);
    }

    $(document).on('click', '.btn-request-perbaikan', function () {
        var id    = $(this).data('id');
        var type  = $(this).data('type');
        var notes = $('#verify-request-notes-input').val().trim();

        if (!notes) {
            Swal.fire('Perhatian', 'Catatan perbaikan tidak boleh kosong.', 'warning');
            return;
        }

        Swal.fire({
            title: 'Kirim Catatan Perbaikan?',
            text: 'Catatan ini akan dikirimkan ke pembuat dokumen.',
            type: 'question',
            showCancelButton: true,
            confirmButtonClass: 'btn btn-warning',
            cancelButtonClass: 'btn btn-secondary',
            confirmButtonText: 'Ya, Kirim',
            cancelButtonText: 'Batal',
        }).then(function (res) {
            if (res.value) {
                $.ajax({
                    url: '{{ route("logistic.verify-receipt-po.request-perbaikan") }}',
                    method: 'POST',
                    data: {
                        _token               : '{{ csrf_token() }}',
                        id                   : id,
                        type                 : type,
                        verify_request_notes : notes,
                    },
                    success: function (response) {
                        var newAlert =
                            '<div class="alert alert-danger mb-3" id="request-perbaikan-info">' +
                                '<span><i class="ti-comment-alt mr-2"></i>Catatan dari <strong>' +
                                response.verify_request_by + '</strong> pada ' + response.verify_request_at + ':</span>' +
                                '<br><span class="mt-1 d-block">' + response.verify_request_notes + '</span>' +
                            '</div>';

                        $('#request-perbaikan-info').remove();
                        $('#verify-banner').before(newAlert);
                        Swal.fire('Berhasil', response.message, 'success');
                    },
                    error: function () {
                        Swal.fire('Gagal', 'Terjadi kesalahan saat mengirim catatan.', 'error');
                    }
                });
            }
        });
    });

    $(document).on('click', '.btn-verify-receipt', function () {
        var id    = $(this).data('id');
        var type  = $(this).data('type');
        var notes = $('#verified-notes-input').val();

        Swal.fire({
            title: 'Konfirmasi Verifikasi',
            text: 'Apakah anda yakin ingin memverifikasi dokumen LPB ini?',
            type: 'question',
            showCancelButton: true,
            confirmButtonClass: 'btn btn-success',
            cancelButtonClass: 'btn btn-secondary',
            confirmButtonText: 'Ya, Verify',
            cancelButtonText: 'Batal',
        }).then(function (res) {
            if (res.value) {
                $.ajax({
                    url: '{{ route("logistic.verify-receipt-po.verify") }}',
                    method: 'POST',
                    data: {
                        _token         : '{{ csrf_token() }}',
                        id             : id,
                        type           : type,
                        verified_notes : notes,
                    },
                    success: function (response) {
                        var notesHtml = response.verified_notes
                            ? '<br><small class="mt-1 d-block"><strong>Catatan:</strong> ' + response.verified_notes + '</small>'
                            : '';
                        $('#request-perbaikan-info').remove();
                        $('#verify-banner')
                            .removeClass('alert-warning')
                            .addClass('alert-success')
                            .html(
                                '<div><span><i class="ti-check mr-2"></i>Diverifikasi oleh <strong>' +
                                response.verified_by + '</strong> pada ' + response.verified_at + '</span>' +
                                notesHtml + '</div>'
                            );
                        Swal.fire('Berhasil', response.message, 'success');
                    },
                    error: function () {
                        Swal.fire('Gagal', 'Terjadi kesalahan saat verifikasi.', 'error');
                    }
                });
            }
        });
    });
</script>
@endsection
