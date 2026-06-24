@extends('layouts.app')

@section('page-header')
    Payment Completion
@endsection

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item active" aria-current="page">Payment Completion</li>
    </ol>
@endsection

@section('content')
    <div class="bgc-white p-20 mB-20">
        <div class="justify-content-between align-items-center mb-3">
            <div class="row mb-3">
                <div class="col-md-6">
                    @if(Gate::allows('payment_completion_admin'))
                    <a href="{{ route('purchasing.payment_completion.list') }}" class="btn btn-primary">
                        <i class="ti-plus"></i>&nbsp; TAMBAH
                    </a>
                    @endif
                    <a href="#" class="btn btn-info" id="btnOpenSearchInvoice">
                        <i class="ti-search"></i> Cari Invoice
                    </a>
                    <a href="#" class="btn btn-success" id="btnOpenExport">
                        <i class="ti-export"></i> Export Excel
                    </a>
                </div>
                <div class="col-md-6 text-right">
                    <div class="row">
                        <div class="col">
                            <a href="#" data-toggle="collapse" data-target="#filter" class="btn btn-outline border-dark float-right">
                                FILTER
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <hr>
        <div class="mb-3 mt-3">
            <div class="collapse mB-20" id="filter" aria-expanded="false">
                <form id="filterForm" class="form-inline">
                    <div class="row w-100 align-items-end">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="font-weight-bold">PAYMENT TYPE</label>
                                <select id="filter_type_payment" class="form-control select2">
                                    <option value="0">ALL TYPE [{{ $allpayment }}]</option>
                                    <option value="1">TEMPO [{{ $payment1 }}]</option>
                                    <option value="2">CBD / COD / DP [{{ $payment2 }}]</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="font-weight-bold">STATUS</label>
                                <select id="statusss" class="form-control select2">
                                    <option value="NULL">ALL STATUS</option>
                                    <option value="0">DRAFT</option>
                                    <option value="1">ON PROGRESS CHECKING</option>
                                    <option value="2">DONE</option>
                                    <option value="3">REJECTED</option>
                                    <option value="4">PENDING COMPLETENESS</option>
                                    <option value="5">PO RELATION CHANGED</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4 text-right">
                            <button type="button" id="btnFilter" class="btn btn-primary mb-2" style="margin-right: -28px;">
                                Filter
                            </button>
                        </div>
                    </div>

                </form>
            </div>
        </div>

        {{-- STATUS CARDS --}}
        <div class="row mb-2">
            <div class="col-md-3 col-sm-4 mb-2">
                <div class="card text-center clickable-card status-card" data-status="NULL" style="cursor:pointer; border-color:#dee2e6;">
                    <div class="card-body p-2">
                        <h4 class="mb-0 font-weight-bold">{{ $allpayment }}</h4>
                        <small class="text-muted">ALL</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-4 mb-2">
                <div class="card text-center clickable-card status-card" data-status="0" style="cursor:pointer; border-color:#dee2e6;">
                    <div class="card-body p-2">
                        <h4 class="mb-0 font-weight-bold text-warning">{{ $countDraft }}</h4>
                        <small class="text-muted">DRAFT</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-4 mb-2">
                <div class="card text-center clickable-card status-card" data-status="4" style="cursor:pointer; border-color:#dee2e6;">
                    <div class="card-body p-2">
                        <h4 class="mb-0 font-weight-bold text-primary">{{ $countPending }}</h4>
                        <small class="text-muted">PENDING</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-4 mb-2">
                <div class="card text-center clickable-card status-card" data-status="1" style="cursor:pointer; border-color:#dee2e6;">
                    <div class="card-body p-2">
                        <h4 class="mb-0 font-weight-bold text-info">{{ $countProgress }}</h4>
                        <small class="text-muted">ON PROGRESS</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-4 mb-2">
                <div class="card text-center clickable-card status-card" data-status="2" style="cursor:pointer; border-color:#dee2e6;">
                    <div class="card-body p-2">
                        <h4 class="mb-0 font-weight-bold text-success">{{ $countDone }}</h4>
                        <small class="text-muted">DONE</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-4 mb-2">
                <div class="card text-center clickable-card status-card" data-status="3" style="cursor:pointer; border-color:#dee2e6;">
                    <div class="card-body p-2">
                        <h4 class="mb-0 font-weight-bold text-danger">{{ $countRejected }}</h4>
                        <small class="text-muted">REJECTED</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-4 mb-2">
                <div class="card text-center clickable-card status-card" data-status="5" style="cursor:pointer; border-color:#dee2e6;">
                    <div class="card-body p-2">
                        <h4 class="mb-0 font-weight-bold text-danger">{{ $countPoChanged }}</h4>
                        <small class="text-muted">PO CHANGED</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-4 mb-2">
                <div class="card text-center clickable-card status-card"
                    data-status="need_change_po"
                    style="cursor:pointer; border-color:#dee2e6;">
                    <div class="card-body p-2">
                        <h4 class="mb-0 font-weight-bold" style="color:rgb(255,0,191);">
                            {{ $countNeedChangePo }}
                        </h4>
                        <small class="text-muted">NEED CHANGE PO</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- Table Data --}}
        <input type="hidden" id="filter_type_payment" value="">
        <input type="hidden" id="statusss" value="">
        <input type="hidden" id="filterNeedChangePo" value="0">
        <div class="table-responsive mt-1">
            <table id="dataTables" class="table table-striped table-bordered" cellspacing="0" width="100%">
                <thead>
                    <tr>
                        <th>No PR</th>
                        <th>No PO</th>
                        <th>No PC</th>
                        <th>Supplier</th>
                        <th>Verify Progress</th>
                        <th>Created User</th>
                        <th>Created Date</th>
                        <th>Payment Type</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    <div class="modal fade" id="modalSetReject" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel"> <strong>REJECT PAYMENT COMPLETION <span
                                id="doc_spb"></strong></span></h5>
                    <button type="button" class="close close_modal_set" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="detailSetReject">
                        <form id="formSetSelesai" method="POST"
                            action="{{ route('purchasing.payment_completion.reject') }}">
                            @csrf
                            <input type="hidden" name="id" value="">

                            <div class="form-group">
                                <label><b>Reject Reason</b></label>
                                <textarea name="receipt_note" class="form-control" placeholder="Reason" required></textarea>
                            </div>

                            <div class="text-right">
                                <button type="submit" class="btn btn-danger">REJECT</button>
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- MODAL CHANGE PO --}}
    <div class="modal fade" id="modalChangePo" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><strong>CHANGE RELATION PO</strong></h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <form id="formChangePo" method="POST">
                        @csrf

                        <div class="form-group">
                            <span>OLD PO <strong id="oldPoText">-</strong></span>
                        </div>
                        <div class="form-group">
                            <label><b>New Document PO <span class="text-danger">*</span></b></label>
                            <select name="new_po_id" class="form-control select2" required>
                                <option value="">Silahkan pilih...</option>
                            </select>
                        </div>
                        <div class="text-right">
                            <button type="submit" class="btn btn-danger">CHANGE</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalSearchInvoice" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Cari No Invoice / No Proforma Invoice</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="input-group mb-3">
                        <input type="text" id="searchInvoice" class="form-control"
                            placeholder="Ketik No Invoice / No Proforma Invoice...">
                        <div class="input-group-append">
                            <button class="btn btn-primary" id="btnSearchInvoice">
                                <i class="ti-search"></i> Cari
                            </button>
                        </div>
                    </div>

                    <div id="searchResult" style="display:none;">
                        <table class="table table-bordered table-sm">
                            <thead class="thead-light">
                                <tr>
                                    <th>No PC</th>
                                    <th>Tipe</th>
                                    <th>No Invoice / Proforma</th>
                                    <th>No PO</th>
                                    <th>Supplier</th>
                                    <th>Status</th>
                                    <th style="width:60px;" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="searchResultBody"></tbody>
                        </table>
                    </div>

                    <div id="searchEmpty" style="display:none;">
                        <p class="text-center text-muted mt-3">Data tidak ditemukan</p>
                    </div>

                    <div id="searchLoading" style="display:none;">
                        <p class="text-center mt-3"><i class="ti-reload"></i> Mencari...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalExportExcel" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><strong>Export Excel Payment Completion</strong></h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form method="GET" action="{{ route('purchasing.payment_completion.export_excel') }}">
                    <div class="modal-body">
                        <div class="form-group">
                            <label><b>Company</b></label>
                            <select name="company_id" class="form-control select2">
                                <option value="">Semua Company</option>
                                @foreach ($companies as $company)
                                    <option value="{{ $company->id }}">{{ $company->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label><b>Type PC</b></label>
                            <select name="type_payment" class="form-control">
                                <option value="">Semua Type</option>
                                <option value="1">TEMPO</option>
                                <option value="2">CBD / COD / DP</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label><b>Tanggal Dari</b></label>
                            <input type="date" name="tgl_dari" class="form-control">
                        </div>
                        <div class="form-group">
                            <label><b>Tanggal Sampai</b></label>
                            <input type="date" name="tgl_sampai" class="form-control">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success">
                            <i class="ti-export"></i> Export
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <style>
        .clickable-card {
            transition: all 0.25s ease-in-out;
            border-radius: 4px;
        }

        .clickable-card:hover {
            background-color: hsla(0, 0%, 78%, 0.35) !important;
            transform: translateY(-3px);
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.15);
            cursor: pointer;
        }
        .status-card {
            border-width: 2px !important;
            border-style: solid !important;
            background-color: #f8f9fa;
            transition: all 0.2s ease-in-out;
        }

        .status-card:hover {
            background-color: #fff;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }

        .status-card.active-card {
            background-color: #e0dddd;
            box-shadow: 0 4px 15px rgba(0,0,0,0.15);
            transform: translateY(-3px);
        }
    </style>
@endsection

@section('js')
    {{-- DataTables --}}
    <script>
        (function () {
            const table = $('#dataTables').DataTable({
                processing: true,
                serverSide: true,
                stateSave: false,
                ajax: {
                    url: '{{ route('purchasing.payment_completion.datatables') }}',
                    data: function (d) {
                        d.type_payment = $('#filter_type_payment').val();
                        d.status = $('#statusss').val();
                        d.need_change_po    = $('#filterNeedChangePo').val();
                    }
                },
                pageLength: 50,
                order: [[8, 'desc']],
                columns: [
                    { data: 'no_pr', name: 'pr.doc_no' },
                    { data: 'no_po', name: 'po.doc_no' },
                    { data: 'doc_no', name: 'payment_completions.doc_no' },
                    { data: 'supplier', name: 'suppliers.name' },
                    {
                        data: 'verify_progress',
                        name: 'verify_progress',
                        orderable: false,
                        searchable: false
                    },
                    { data: 'user_nama', name: 'u.name' },
                    { data: 'tgl_pembuatan', name: 'payment_completions.created_at' },
                    { data: 'type_payment', name: 'payment_completions.type_payment' },
                    { data: 'status', name: 'payment_completions.status' },
                    {
                        data: 'action',
                        orderable: false,
                        searchable: false
                    }
                ]
            });

            // Klik tombol Filter
            $('#btnFilter').on('click', function () {
                table.ajax.reload();
            });

        })();


    </script>

    {{-- Konfirmasi Draft / Publish / Done --}}
    <script>
        function postAndFollow(url) {
            var f = document.createElement('form');
            f.method = 'POST';
            f.action = url;

            // CSRF
            var t = document.createElement('input');
            t.type = 'hidden';
            t.name = '_token';
            t.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            f.appendChild(t);

            document.body.appendChild(f);
            f.submit();
        }

        // Konfirmasi Publish
        $(document).on('click', '.btn-publish', function() {
            var url = $(this).data('url');
            var doc = $(this).data('doc') || '';
            Swal.fire({
                title: 'Confirmation',
                text: doc ? ('Change Publish Payment Completion ' + doc) : 'Publish ?',
                type: 'question',
                showCancelButton: true,
                confirmButtonClass: 'btn btn-danger',
                cancelButtonClass: 'btn btn-primary',
                confirmButtonText: 'Publish',
                cancelButtonText: 'Cancel'
            }).then(function(res) {
                if (res.value) postAndFollow(url);
            });
        });

        // Konfirmasi Draft
        $(document).on('click', '.btn-cancel', function() {
            var url = $(this).data('url');
            var doc = $(this).data('doc') || '';
            Swal.fire({
                title: 'Confirmation',
                text: doc ? ('Change Draft Payment Completion ' + doc) : 'Draft ?',
                type: 'warning',
                showCancelButton: true,
                confirmButtonClass: 'btn btn-danger',
                cancelButtonClass: 'btn btn-primary',
                confirmButtonText: 'Draft',
                cancelButtonText: 'Cancel'
            }).then(function(res) {
                if (res.value) postAndFollow(url);
            });
        });

        // Konfirmasi Done
        $(document).on('click', '.btn-done', function() {
            var url = $(this).data('url');
            var doc = $(this).data('doc') || '';
            Swal.fire({
                title: 'Confirmation',
                text: doc ? ('Change Done Payment Completion ' + doc) : 'Done ?',
                type: 'question',
                showCancelButton: true,
                confirmButtonClass: 'btn btn-danger',
                cancelButtonClass: 'btn btn-primary',
                confirmButtonText: 'Done',
                cancelButtonText: 'Cancel'
            }).then(function(res) {
                if (res.value) postAndFollow(url);
            });
        });

        $(document).on('click', '.btn-reject', function() {
            let id = $(this).data('id');
            let doc = $(this).data('doc');
            $('#doc_spb').text(doc);
            $('input[name="id"]').val(id);
            $('#modalSetReject').modal('show');
        });
        $(document).on('click', '.btn-tambahkelengkapan', function() {
            var url = $(this).data('url');
            Swal.fire({
                title: 'Confirmation',
                text: 'Kembalikan ke pending kelengkapan pembayaran?',
                type: 'question',
                showCancelButton: true,
                confirmButtonClass: 'btn btn-danger',
                cancelButtonClass: 'btn btn-primary',
                confirmButtonText: 'Ya',
                cancelButtonText: 'Cancel'
            }).then(function(res) {
                if (res.value) postAndFollow(url);
            });
        });
        $(document).on('click', '.btn-change-po', function () {
            let actionUrl = $(this).data('url');
            let poId = $(this).data('po-id');
            let poNo = $(this).data('po-no');


            // set form action & old po
            $('#formChangePo').attr('action', actionUrl);
            $('#oldPoText').text(poNo);

            let $select = $('select[name="new_po_id"]');
            $select.html('<option value="">Loading...</option>');

            let getPoUrl = "{{ route('purchasing.payment_completion.get_po_list', ':id') }}";
            getPoUrl = getPoUrl.replace(':id', poId);

            $.get(getPoUrl, function (res) {
                let options = '<option value="">Silahkan pilih...</option>';
                $.each(res, function (id, docNo) {
                    options += `<option value="${id}">${docNo}</option>`;
                });
                $select.html(options).trigger('change');
            });

            $('#modalChangePo').modal('show');
        });

        // Set Done dari index
        $(document).on('click', '.btn-done-index', function () {
            var url = $(this).data('url');
            var doc = $(this).data('doc') || '';
            Swal.fire({
                title: 'Confirmation',
                text: 'Set Done Payment Completion ' + doc + '?',
                type: 'question',
                showCancelButton: true,
                confirmButtonClass: 'btn btn-success',
                cancelButtonClass: 'btn btn-secondary',
                confirmButtonText: 'Done',
                cancelButtonText: 'Cancel'
            }).then(function (res) {
                if (res.value) {
                    var f = document.createElement('form');
                    f.method = 'POST';
                    f.action = url;
                    var t = document.createElement('input');
                    t.type = 'hidden'; t.name = '_token';
                    t.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    f.appendChild(t);
                    document.body.appendChild(f);
                    f.submit();
                }
            });
        });

        // Buka modal
        $('#btnOpenSearchInvoice').on('click', function () {
            $('#searchInvoice').val('');
            $('#searchResult').hide();
            $('#searchEmpty').hide();
            $('#searchResultBody').html('');
            $('#modalSearchInvoice').modal('show');
        });

        // Cari
        $('#btnSearchInvoice').on('click', function () {
            var keyword = $('#searchInvoice').val().trim();
            if (!keyword) return;

            $('#searchResult').hide();
            $('#searchEmpty').hide();
            $('#searchLoading').show();

            $.get("{{ route('purchasing.payment_completion.search_invoice') }}", { q: keyword }, function (res) {
                $('#searchLoading').hide();
                $('#searchResultBody').html('');

                if (res.length === 0) {
                    $('#searchEmpty').show();
                } else {
                    $.each(res, function (i, row) {
                        $('#searchResultBody').append(`
                            <tr>
                                <td>${row.doc_no}</td>
                                <td><span class="badge badge-info">${row.component}</span></td>
                                <td><strong>${row.no_invoice}</strong></td>
                                <td>${row.no_po}</td>
                                <td>${row.supplier}</td>
                                <td>${row.status}</td>
                                <td class="text-center">
                                    <a href="${row.url_show}" class="btn btn-sm btn-outline" title="Lihat PC">
                                        <i class="ti-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        `);
                    });
                    $('#searchResult').show();
                }
            });
        });

        // Enter to search
        $('#searchInvoice').on('keypress', function (e) {
            if (e.which === 13) $('#btnSearchInvoice').click();
        });

       $(document).on('click', '.status-card', function () {
            var status = $(this).data('status');
            $('.status-card').removeClass('active-card');
            $(this).addClass('active-card');
            if (status === 'need_change_po') {
                $('#statusss').val('NULL').trigger('change');
                $('#filterNeedChangePo').val('1');
            } else {
                $('#filterNeedChangePo').val('0');
                $('#statusss').val(status).trigger('change');
            }
            $('#dataTables').DataTable().ajax.reload();
        });

        $('#btnOpenExport').on('click', function (e) {
            e.preventDefault();
            $('#modalExportExcel').modal('show');
        });


    </script>
@endsection
