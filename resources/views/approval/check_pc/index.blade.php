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

        {{-- Table Data --}}
        <input type="hidden" id="filter_type_payment" value="">
        <input type="hidden" id="statusss" value="">
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


    <style>
        .clickable-card {
            transition: all 0.25s ease-in-out;
            border-radius: 8px;
        }

        .clickable-card:hover {
            background-color: hsla(0, 0%, 78%, 0.35) !important;
            transform: translateY(-3px);
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.15);
            cursor: pointer;
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
                    url: '{{ route('approval.verify_pc.datatables') }}',
                    data: function (d) {
                        d.type_payment = $('#filter_type_payment').val();
                        d.status = $('#statusss').val();
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

    </script>
@endsection
