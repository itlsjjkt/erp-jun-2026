@extends('layouts.app')

@section('page-header')
    Verify Receipt PO
@endsection

@section('breadcrumbs')
<ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
    <li class="breadcrumb-item active">Verify Receipt PO</li>
</ol>
@endsection

@section('content')
<div class="bgc-white bd bdrs-3 p-20 mB-20">

    <ul class="nav nav-tabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" data-toggle="tab" href="#tab-lpb" role="tab">LPB</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#tab-bpb" role="tab">BPB LOKAL</a>
        </li>
    </ul>

    <div class="tab-content mt-4">

        {{-- TAB LPB --}}
        <div class="tab-pane active" id="tab-lpb" role="tabpanel">

            {{-- CARDS LPB --}}
            <div class="row mb-3">
                <div class="col-md-3 col-sm-4 mb-2">
                    <div class="card text-center status-card-lpb" data-verified="all" style="cursor:pointer;">
                        <div class="card-body p-2">
                            <h4 class="mb-0 font-weight-bold">{{ $lpbAll }}</h4>
                            <small class="text-muted">SEMUA</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-4 mb-2">
                    <div class="card text-center status-card-lpb" data-verified="0" style="cursor:pointer;">
                        <div class="card-body p-2">
                            <h4 class="mb-0 font-weight-bold text-warning">{{ $lpbBelum }}</h4>
                            <small class="text-muted">BELUM DIVERIFIKASI</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-4 mb-2">
                    <div class="card text-center status-card-lpb" data-verified="1" style="cursor:pointer;">
                        <div class="card-body p-2">
                            <h4 class="mb-0 font-weight-bold text-success">{{ $lpbSudah }}</h4>
                            <small class="text-muted">SUDAH DIVERIFIKASI</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-4 mb-2">
                    <div class="card text-center status-card-lpb" data-verified="pending" style="cursor:pointer;">
                        <div class="card-body p-2">
                            <h4 class="mb-0 font-weight-bold text-danger">{{ $lpbPending }}</h4>
                            <small class="text-muted">PENDING REQUEST</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="table-responsive" style="overflow-y:hidden !important">
                <table id="table-lpb" class="table table-striped table-bordered" width="100%">
                    <thead>
                        <tr>
                            <th>No. LPB</th>
                            <th>No. PO</th>
                            <th>No. PR</th>
                            <th>No. DPM</th>
                            <th>Dibuat Oleh</th>
                            <th>Tgl Dibuat</th>
                            <th>Status</th>
                            <th>Status Verifikasi</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>

        {{-- TAB BPB --}}
        <div class="tab-pane" id="tab-bpb" role="tabpanel">

            {{-- CARDS BPB --}}
            <div class="row mb-3">
                <div class="col-md-3 col-sm-4 mb-2">
                    <div class="card text-center status-card-bpb" data-verified="all" style="cursor:pointer;">
                        <div class="card-body p-2">
                            <h4 class="mb-0 font-weight-bold">{{ $bpbAll }}</h4>
                            <small class="text-muted">SEMUA</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-4 mb-2">
                    <div class="card text-center status-card-bpb" data-verified="0" style="cursor:pointer;">
                        <div class="card-body p-2">
                            <h4 class="mb-0 font-weight-bold text-warning">{{ $bpbBelum }}</h4>
                            <small class="text-muted">BELUM DIVERIFIKASI</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-4 mb-2">
                    <div class="card text-center status-card-bpb" data-verified="1" style="cursor:pointer;">
                        <div class="card-body p-2">
                            <h4 class="mb-0 font-weight-bold text-success">{{ $bpbSudah }}</h4>
                            <small class="text-muted">SUDAH DIVERIFIKASI</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-4 mb-2">
                    <div class="card text-center status-card-bpb" data-verified="pending" style="cursor:pointer;">
                        <div class="card-body p-2">
                            <h4 class="mb-0 font-weight-bold text-danger">{{ $bpbPending }}</h4>
                            <small class="text-muted">PENDING REQUEST</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="table-responsive" style="overflow-y:hidden !important">
                <table id="table-bpb" class="table table-striped table-bordered" width="100%">
                    <thead>
                        <tr>
                            <th>No. BPB</th>
                            <th>No. PO</th>
                            <th>Dibuat Oleh</th>
                            <th>Tgl Dibuat</th>
                            <th>Status</th>
                            <th>Status Verifikasi</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>

    </div>
</div>

<style>
    .status-card-lpb,
    .status-card-bpb {
        border-width: 2px !important;
        border-style: solid !important;
        border-color: #dee2e6;
        background-color: #f8f9fa;
        transition: all 0.2s ease-in-out;
        border-radius: 4px;
    }
    .status-card-lpb:hover,
    .status-card-bpb:hover {
        background-color: #fff;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        transform: translateY(-2px);
    }
    .status-card-lpb.active-card,
    .status-card-bpb.active-card {
        background-color: #e0dddd;
        box-shadow: 0 4px 15px rgba(0,0,0,0.15);
        transform: translateY(-3px);
    }
</style>
@endsection

@section('js')
<script>
$(document).ready(function () {

    var verifiedLpb = 'all';
    var verifiedBpb = 'all';

    var tableLpb = $('#table-lpb').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 25,
        ajax: {
            url: '{{ route("logistic.verify-receipt-po.lpb") }}',
            data: function (d) {
                d.verified = verifiedLpb === 'all' ? '' : verifiedLpb;
            }
        },
        columns: [
            { data: 'doc_no',            name: 'lpb.doc_no' },
            { data: 'po_no',             name: 'po.doc_no' },
            { data: 'pr_no',             name: 'purchase_requisitions.doc_no' },
            { data: 'dpm_no',            name: 'purchase_requisitions.dpm_no' },
            { data: 'created',           name: 'users.name' },
            { data: 'created_at',        name: 'lpb.created_at',     searchable: false },
            { data: 'status',            name: 'lpb.status',         searchable: false, orderable: false },
            { data: 'status_verifikasi', name: 'status_verifikasi',  searchable: false, orderable: false },
            { data: 'aksi',              name: 'aksi',               searchable: false, orderable: false },
        ],
        order: [[5, 'desc']],
    });

    var tableBpb = $('#table-bpb').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 25,
        ajax: {
            url: '{{ route("logistic.verify-receipt-po.bpb") }}',
            data: function (d) {
                d.verified = verifiedBpb === 'all' ? '' : verifiedBpb;
            }
        },
        columns: [
            { data: 'doc_no',            name: 'bpb.doc_no' },
            { data: 'po_no',             name: 'po.doc_no' },
            { data: 'created',           name: 'users.name' },
            { data: 'created_at',        name: 'bpb.created_at',     searchable: false },
            { data: 'status',            name: 'bpb.status',         searchable: false, orderable: false },
            { data: 'status_verifikasi', name: 'status_verifikasi',  searchable: false, orderable: false },
            { data: 'aksi',              name: 'aksi',               searchable: false, orderable: false },
        ],
        order: [[3, 'desc']],
    });

    // Klik card LPB
    $(document).on('click', '.status-card-lpb', function () {
        $('.status-card-lpb').removeClass('active-card');
        $(this).addClass('active-card');
        verifiedLpb = $(this).data('verified');
        tableLpb.ajax.reload();
    });

    // Klik card BPB
    $(document).on('click', '.status-card-bpb', function () {
        $('.status-card-bpb').removeClass('active-card');
        $(this).addClass('active-card');
        verifiedBpb = $(this).data('verified');
        tableBpb.ajax.reload();
    });

    // Reload tabel saat pindah tab
    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        if ($(e.target).attr('href') === '#tab-lpb') tableLpb.ajax.reload();
        if ($(e.target).attr('href') === '#tab-bpb') tableBpb.ajax.reload();
    });

    $(document).on('click', '.btn-verify-receipt', function () {

        var id   = $(this).data('id');
        var type = $(this).data('type');

        Swal.fire({
            title: 'Konfirmasi Verifikasi',
            text: 'Apakah anda yakin ingin memverifikasi dokumen ' + type.toUpperCase() + ' ini?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Verify',
            cancelButtonText: 'Batal',
        }).then(function (res) {

            if (res.value) {

                $.ajax({
                    url: '{{ route("logistic.verify-receipt-po.verify") }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        id: id,
                        type: type
                    },

                    success: function (response) {
                        Swal.fire('Berhasil', response.message, 'success');

                        tableLpb.ajax.reload(null, false);
                        tableBpb.ajax.reload(null, false);
                    },

                    error: function () {
                        Swal.fire('Gagal', 'Terjadi kesalahan saat verifikasi.', 'error');
                    }
                });

            }
        });

    });
});
</script>
@stop
