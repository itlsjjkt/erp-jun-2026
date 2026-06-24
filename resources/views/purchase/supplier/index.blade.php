@extends('layouts.app')

@section('page-header')
    Supplier
@endsection

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item active" aria-current="page">Supplier</li>
    </ol>
@endsection

@section('content')

<div class="bgc-white bd bdrs-3 p-20 mB-20">

    {{-- CARD FILTER --}}
    <div class="row mb-3">
        <div class="col-lg-3 mb-3">
            <div class="layers bd p-20 card-filter" data-filter="all" style="background-color: hsla(0, 0%, 86%, 0.247); cursor: pointer;" id="cardAll">
                <div>
                    <div class="layer w-100">
                        <h6 class="mb-0">Semua</h6>
                    </div>
                    <div class="layer w-100">
                        <div class="peer peer-greed">
                            <h5 class="font-weight-bold fa-2x mb-0 text-dark" id="count-all">-</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 mb-3">
            <div class="layers bd p-20 card-filter" data-filter="approved" style="background-color: hsla(0, 0%, 86%, 0.247); cursor: pointer;" id="cardApproved">
                <div>
                    <div class="layer w-100">
                        <h6 class="mb-0">Approved</h6>
                    </div>
                    <div class="layer w-100">
                        <div class="peer peer-greed">
                            <h5 class="font-weight-bold fa-2x mb-0 text-success" id="count-approved">-</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 mb-3">
            <div class="layers bd p-20 card-filter" data-filter="revision" style="background-color: hsla(0, 0%, 86%, 0.247); cursor: pointer;">
                <div>
                    <div class="layer w-100">
                        <h6 class="mb-0">Perlu Diperbaiki</h6>
                    </div>
                    <div class="layer w-100">
                        <div class="peer peer-greed">
                            <h5 class="font-weight-bold fa-2x mb-0 text-danger" id="count-revision">-</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 mb-3">
            <div class="layers bd p-20 card-filter" data-filter="draft" style="background-color: hsla(0, 0%, 86%, 0.247); cursor: pointer;">
                <div>
                    <div class="layer w-100">
                        <h6 class="mb-0">Draft</h6>
                    </div>
                    <div class="layer w-100">
                        <div class="peer peer-greed">
                            <h5 class="font-weight-bold fa-2x mb-0 text-secondary" id="count-draft">-</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 mb-3">
            <div class="layers bd p-20 card-filter" data-filter="pending" style="background-color: hsla(0, 0%, 86%, 0.247); cursor: pointer;" id="cardPending">
                <div>
                    <div class="layer w-100">
                        <h6 class="mb-0">In Progress Approval</h6>
                    </div>
                    <div class="layer w-100">
                        <div class="peer peer-greed">
                            <h5 class="font-weight-bold fa-2x mb-0 text-warning" id="count-pending">-</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 mb-3">
            <div class="layers bd p-20 card-filter" data-filter="cancelled" style="background-color: hsla(0, 0%, 86%, 0.247); cursor: pointer;" id="cardCancelled">
                <div>
                    <div class="layer w-100">
                        <h6 class="mb-0">Dibatalkan</h6>
                    </div>
                    <div class="layer w-100">
                        <div class="peer peer-greed">
                            <h5 class="font-weight-bold fa-2x mb-0 text-dark" id="count-cancelled">-</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 mb-3">
            <div class="layers bd p-20 card-filter" data-filter="blacklist" style="background-color: hsla(0, 0%, 86%, 0.247); cursor: pointer;" id="cardBlacklist">
                <div>
                    <div class="layer w-100">
                        <h6 class="mb-0">Blacklist</h6>
                    </div>
                    <div class="layer w-100">
                        <div class="peer peer-greed">
                            <h5 class="font-weight-bold fa-2x mb-0 text-danger" id="count-blacklist">-</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mB-20">
        @if($type_user == 4 && $datAcc_user == 1 || $idSuper ==1)
        <a href="{{ route('purchasing.suppliers.create') }}" class="btn btn-info">
            Tambah Data
        </a>
        @endif
        <a href="{{ route('purchasing.suppliers.export') }}" class="btn btn-outline border-dark float-right">
            <i class="fa fa-file-excel-o text-success icon-lg"></i> Export Data
        </a>
        @if(isAdministrator())
            <a href="{{ route('purchasing.suppliers.import') }}" class="btn btn-outline border-dark float-right text-uppercase fsz-sm mr-2 fw-600">
                <i class="fa fa-file-excel-o text-danger icon-lg"></i> Upload
            </a>
        @endif
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ $errors->first() }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    <br>
    <div class="bgc-white bd bdrs-3 p-20 mB-20">
        <table id="dataTables" class="table table-striped table-bordered" cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th>Name</th>
                    <th style="max-width: 250px !important">Category</th>
                    <th style="max-width: 200px !important">Metode Pembayaran</th>
                    <th style="max-width: 350px !important">Payment Term</th>
                    <th class="text-center" style="width: 100px !important">PPN</th>
                    <th style="width: 100px !important">Status</th>
                    <th style="width: 140px !important">Approval</th>
                    <th style="width: 100px !important">Blacklist</th>
                    <th style="max-width: 100px !important">Updated</th>
                    <th style="max-width: 100px !important">Action</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

<!-- Modal Blacklist -->
<div class="modal fade" tabindex="-1" role="dialog" id="modalBlock">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="blockForm" action="" method="post">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Alasan Blacklist</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <textarea name="block_reason" class="form-control"></textarea>
                </div>
                <div class="modal-footer">
                    <input type="hidden" name="id" id="supplier_id">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-danger">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Cancel Pengajuan -->
<div class="modal fade" tabindex="-1" role="dialog" id="modalCancel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="cancelForm" action="" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Batalkan Pengajuan Supplier</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Yakin ingin membatalkan pengajuan supplier <strong id="cancelSupplierName"></strong>?</p>
                    <div class="form-group">
                        <label>Alasan Pembatalan</label>
                        <textarea name="message" class="form-control" rows="3" placeholder="Isi alasan pembatalan..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-dark">Ya, Batalkan</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('js')
<script>
    var activeFilter = 'all';
    var table;

    $(document).ready(function() {

        table = $('#dataTables').DataTable({
            processing: true,
            serverSide: true,
            "pageLength": 50,
            ajax: {
                url: '{{ route('purchasing.suppliers.datatables') }}',
                data: function(d) {
                    d.filter = activeFilter;
                }
            },
            columns: [
                {data: 'name',           name: 'name'},
                {data: 'all_category_ids', name: 'all_category_ids'},
                {data: 'payment_method', name: 'payment_methods.name', orderable: false, searchable: false},
                {data: 'payment_term',   name: 'payment_terms.name',   orderable: false, searchable: false},
                {data: 'is_ppn',         name: 'suppliers.is_ppn',     orderable: false, searchable: false},
                {data: 'status',         name: 'status',               orderable: false, searchable: false},
                {data: 'approval_badge', name: 'approval_status',      orderable: false, searchable: false},
                {data: 'block',          name: 'is_block',             orderable: false, searchable: false},
                {data: 'updated_at',     name: 'updated_at',           searchable: false},
                {data: 'action',         name: 'action',               orderable: false, searchable: false}
            ],
            "order": [[ 0, "asc" ]],
            "initComplete": function() {
                loadCounts();
            }
        });

        $(document).on('click', '.card-filter', function() {
            $('.card-filter').css('border', '');
            $(this).css('border', '2px solid #4CAF50');
            activeFilter = $(this).data('filter');
            table.ajax.reload();
        });

        $('#modalBlock').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var id     = button.data('id');
            $('#blockForm').attr("action", "{{ route('purchasing.suppliers.blacklist') }}");
            $('#supplier_id').attr("value", id);
        });

        $('#modalCancel').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var id     = button.data('id');
            var name   = button.data('name');
            $('#cancelForm').attr('action', '{{ url('purchasing/suppliers_cancel') }}/' + id);
            $('#cancelSupplierName').text(name);
        });
    });

    function loadCounts() {
        $.ajax({
            url: '{{ route('purchasing.suppliers.counts') }}',
            type: 'GET',
            success: function(data) {
                $('#count-all').text(data.all);
                $('#count-approved').text(data.approved);
                $('#count-pending').text(data.pending);
                $('#count-revision').text(data.revision);
                $('#count-draft').text(data.draft);
                $('#count-cancelled').text(data.cancelled);
                $('#count-blacklist').text(data.blacklist);
            }
        });
    }
</script>
@stop
