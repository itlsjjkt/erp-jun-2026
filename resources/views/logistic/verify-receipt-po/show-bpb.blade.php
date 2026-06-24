@extends('layouts.app')

@section('page-header')
    Verify BPB <small>{{ $bpb->doc_no }}</small>
@endsection

@section('breadcrumbs')
<ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('logistic.verify-receipt-po.index') }}">Verify Receipt PO</a></li>
    <li class="breadcrumb-item active">Detail BPB</li>
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
                    onclick='printExternal("/logistic/bpb_franco_print/{{ Hashids::encode($bpb->id) }}/print")'>
                    <i class="ti-printer icon-lg"></i>
                </a>
            </div>
        </div>

        {{-- VERIFY BANNER --}}
        @if($bpb->verified_at)
            <div class="alert alert-success mb-4">
                <div>
                    <span><i class="ti-check mr-2"></i>Diverifikasi oleh <strong>{{ getUserByID($bpb->verified_by) }}</strong> pada {{ \Carbon\Carbon::parse($bpb->verified_at)->format('d/m/Y H:i') }}</span>
                    @if($bpb->verified_notes)
                        <br><small class="mt-1 d-block"><strong>Catatan:</strong> {{ $bpb->verified_notes }}</small>
                    @endif
                </div>
            </div>
        @else
            {{-- Tampilkan catatan request perbaikan jika ada --}}
            @if($bpb->verify_request_at)
                <div class="alert alert-danger mb-3" id="request-perbaikan-info">
                    <span><i class="ti-comment-alt mr-2"></i>Catatan dari <strong>{{ getUserByID($bpb->verify_request_by) }}</strong> pada {{ \Carbon\Carbon::parse($bpb->verify_request_at)->format('d/m/Y H:i') }}:</span>
                    <br><span class="mt-1 d-block">{{ $bpb->verify_request_notes }}</span>
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
                        placeholder="Tulis catatan perbaikan untuk pembuat dokumen...">{{ $bpb->verify_request_notes }}</textarea>
                    <div class="mt-2">
                        <button class="btn btn-warning btn-request-perbaikan"
                            data-id="{{ Hashids::encode($bpb->id) }}"
                            data-type="bpb">
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
                            data-id="{{ Hashids::encode($bpb->id) }}"
                            data-type="bpb">
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
                    <a class="nav-link active" data-toggle="tab" href="#tab1" role="tab">BPB</a>
                </li>
                <li class="nav-item">
                    @php
                        $badge = $bpb->attachment_file
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
                <h6 class="text-center font-weight-bold mB-30" style="text-decoration:underline">{{ $bpb->doc_no }}</h6>
                <div class="row">
                    <div class="col-sm-6">
                        <div class="row">
                            <label class="col-sm-4">Nomor BPB</label>
                            <div class="col-sm-7">: {{ $bpb->doc_no }}</div>
                        </div>
                        <div class="row">
                            <label class="col-sm-4">No. PO</label>
                            <div class="col-sm-7">: {{ $bpb->purchaseOrder ? $bpb->purchaseOrder->doc_no : '-' }}</div>
                        </div>
                        <div class="row">
                            <label class="col-sm-4">No. DPM</label>
                            <div class="col-sm-7">: {{ $bpb->purchaseOrder ? $bpb->purchaseOrder->purchaseRequisition->dpm_no : '-' }}</div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="row">
                            <label class="col-sm-4">Kapal/Departemen</label>
                            <div class="col-sm-7">: {{ $bpb->purchaseOrder ? $bpb->purchaseOrder->purchaseRequisition->department->name : '-' }}</div>
                        </div>
                        <div class="row">
                            <label class="col-sm-4">Supplier</label>
                            <div class="col-sm-7">: {{ $bpb->purchaseOrder ? $bpb->purchaseOrder->supplier->name : '-' }}</div>
                        </div>
                        <div class="row">
                            <label class="col-sm-4">Penerima</label>
                            <div class="col-sm-7">: {{ $bpb->received_by }}</div>
                        </div>
                        <div class="row">
                            <label class="col-sm-4">Dibuat Oleh</label>
                            <div class="col-sm-7">: {{ $bpb->creator ? $bpb->creator->name : '-' }} [{{ idDate($bpb->created_at) }}]</div>
                        </div>
                        <div class="row">
                            <label class="col-sm-4">Status</label>
                            <div class="col-sm-7">: {!! getStatusData($bpb->status) !!}</div>
                        </div>
                    </div>
                </div>

                <h6 class="mT-30">Daftar Barang</h6>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th style="width:50px">No</th>
                            <th style="width:300px">Nama Barang</th>
                            <th style="width:350px">Spesifikasi</th>
                            <th>QTY</th>
                            <th class="text-center">Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $no = 1; @endphp
                        @foreach ($bpb_items as $item)
                            <tr>
                                <td>{{ $no }}</td>
                                <td>
                                    [{{ $item->productCode }}] {{ $item->product }}<br>
                                    {!! $item->productPartNumber ? '<small>PN: '.$item->productPartNumber.'</small>' : '' !!}
                                    {!! $item->productBrand ? '<small>Brand: '.$item->productBrand.'</small>' : '' !!}
                                </td>
                                <td>{!! $item->specification !!}</td>
                                <td>{{ $item->qty }} {{ $item->measure }}</td>
                                <td>{!! $item->description !!}</td>
                            </tr>
                            @php $no++; @endphp
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- TAB ATTACHMENT --}}
            <div class="tab-pane" id="tab-attachment" role="tabpanel">
                <div class="row">
                    <div class="col-12">
                        @if ($bpb->attachment_file)
                            <p class="text-center">
                                <embed class="col align-self-center"
                                    src="{{ asset('storage'.$bpb->attachment_file) }}"
                                    width="600" height="800" alt="pdf" />
                            </p>
                        @else
                            <p class="text-center text-muted mt-4">Belum ada attachment dokumen BPB.</p>
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
            text: 'Apakah anda yakin ingin memverifikasi dokumen BPB ini?',
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
