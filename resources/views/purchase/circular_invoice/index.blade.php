@extends('layouts.app')

@section('page-header')
    Sirkular Invoice
@endsection

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item active" aria-current="page">Sirkular Invoice</li>
    </ol>
@endsection

@section('content')
    <div class="bgc-white p-20 mB-20">

        {{-- Button Function --}}
        <div class="justify-content-between align-items-center mb-3">
            <div class="row mb-3">
                <div class="col-md-6">
                    <a href="{{ route('purchasing.circular_invoice.list') }}" class="btn btn-primary">
                        <i class="ti-pencil"></i>&nbsp; TAMBAH SIRKULAR INVOICE
                    </a>
                </div>
                <div class="col-md-6 text-right">
                    <a href="#" data-toggle="collapse" data-target="#filter" class="btn btn-outline border-dark">
                        <i class="ti-search icon"></i>&nbsp; FILTER
                    </a>
                    <button id="btnPrintMultiple" class="btn btn-success" disabled>
                        <i class="ti-printer"></i>&nbsp; PRINT MULTIPLE
                    </button>
                </div>
            </div>
        </div>

        {{-- Filter --}}
        <div class="collapse mB-20" id="filter" aria-expanded="false">
            <form class="form-horizontal" action="" method='GET' id="form">
                {{ csrf_field() }}
                <div class="bd p-20">
                    <input type="hidden" name="mode" value="search">

                    <div class="form-group row">
                        <div class="col-sm-2">
                            <label>Status SI</label>
                            {!! Form::select(
                                'status',
                                [
                                    '' => 'All',
                                    0 => 'Draft',
                                    1 => 'Publish',
                                    2 => 'Selesai',
                                ],
                                $filters['status'] ?? '',
                                ['class' => 'form-control select2'],
                            ) !!}
                        </div>

                        <div class="col-sm-2">
                            <label>Tipe Pembayaran</label>
                            {!! Form::select(
                                'type_payment',
                                [
                                    '' => 'All',
                                    1 => 'CBD',
                                    2 => 'COD',
                                    3 => 'DP',
                                    4 => 'Setelah Pekerjaan Selesai',
                                ],
                                $filters['type_payment'] ?? '',
                                ['class' => 'form-control select2'],
                            ) !!}
                        </div>

                        <div class="col-sm-2">
                            <label>Perusahaan</label>
                            {!! Form::select('company_id', ['' => 'All'] + $companies, $filters['company_id'] ?? '', [
                                'class' => 'form-control select2',
                            ]) !!}
                        </div>

                        <div class="col-sm-2">
                            <label>PO Payment Amount</label>
                            {!! Form::select(
                                'po_up_20m',
                                [
                                    '' => 'All',
                                    '0' => '< 20.000.000',
                                    '1' => '> 20.000.000',
                                ],
                                $filters['po_up_20m'] ?? '',
                                ['class' => 'form-control select2'],
                            ) !!}
                        </div>

                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="float-right">
                                <button type="submit" class="btn btn-danger" id="btn-filter">CARI</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        {{-- Group Data --}}
        <div class="row mb-3 mt-4">

            <div class="col-lg-3 mb-3">
                <form id="filterFormCBD" action="{{ route('purchasing.circular_invoice.search') }}" method="GET">
                    @csrf
                    <input type="hidden" name="type_payment" value="1">
                    <input type="hidden" name="status" value="">
                    <input type="hidden" name="company_id" value="">
                    <input type="hidden" name="po_up_20m" value="">
                    <div class="card layers bd p-20 clickable-card" data-target-form="filterFormCBD"
                        style="background-color: hsla(0,0%,86%,0.247); cursor: pointer;">
                        <div>
                            <div class="layer w-100">
                                <h6 class="mb-0">CBD</h6>
                            </div>
                            <div class="layer w-100">
                                <div class="peer peer-greed">
                                    <h5 class="font-weight-bold fa-2x mb-0 text-warning text-center">{{ $totalCBD }}
                                    </h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="col-lg-3 mb-3">
                <form id="filterFormCOD" action="{{ route('purchasing.circular_invoice.search') }}" method="GET">
                    @csrf
                    <input type="hidden" name="type_payment" value="2">
                    <input type="hidden" name="status" value="">
                    <input type="hidden" name="company_id" value="">
                    <input type="hidden" name="po_up_20m" value="">
                    <div class="card layers bd p-20 clickable-card" data-target-form="filterFormCOD"
                        style="background-color: hsla(0,0%,86%,0.247); cursor: pointer;">
                        <div>
                            <div class="layer w-100">
                                <h6 class="mb-0">COD</h6>
                            </div>
                            <div class="layer w-100">
                                <div class="peer peer-greed">
                                    <h5 class="font-weight-bold fa-2x mb-0 text-info text-center">{{ $totalCOD }}</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="col-lg-3 mb-3">
                <form id="filterFormDP" action="{{ route('purchasing.circular_invoice.search') }}" method="GET">
                    @csrf
                    <input type="hidden" name="type_payment" value="3">
                    <input type="hidden" name="status" value="">
                    <input type="hidden" name="company_id" value="">
                    <input type="hidden" name="po_up_20m" value="">
                    <div class="card layers bd p-20 clickable-card" data-target-form="filterFormDP"
                        style="background-color: hsla(0,0%,86%,0.247); cursor: pointer;">
                        <div>
                            <div class="layer w-100">
                                <h6 class="mb-0">DP</h6>
                            </div>
                            <div class="layer w-100">
                                <div class="peer peer-greed">
                                    <h5 class="font-weight-bold fa-2x mb-0 text-primary text-center">{{ $totalDP }}
                                    </h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="col-lg-3 mb-3">
                <form id="filterFormSelesai" action="{{ route('purchasing.circular_invoice.search') }}" method="GET">
                    @csrf
                    <input type="hidden" name="type_payment" value="4">
                    <input type="hidden" name="status" value="">
                    <input type="hidden" name="company_id" value="">
                    <input type="hidden" name="po_up_20m" value="">
                    <div class="card layers bd p-20 clickable-card" data-target-form="filterFormSelesai"
                        style="background-color: hsla(0,0%,86%,0.247); cursor: pointer;">
                        <div>
                            <div class="layer w-100">
                                <h6 class="mb-0">SETELAH PEKERJAAN SELESAI</h6>
                            </div>
                            <div class="layer w-100">
                                <div class="peer peer-greed">
                                    <h5 class="font-weight-bold fa-2x mb-0 text-danger text-center">{{ $totalSelesai }}
                                    </h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Table Data --}}
        <div class="table-responsive mt-3">
            <table id="dataTables" class="table table-striped table-bordered" cellspacing="0" width="100%">
                <thead>
                    <tr>
                        {{-- <th> <input type="checkbox" id="checkAll"> </th> --}}
                        <th> </th>
                        <th>No SI</th>
                        <th>No PO</th>
                        <th>No PR</th>
                        <th>No Invoice Ext</th>
                        <th>Tgl Invoice Ext</th>
                        <th>Tgl Jatuh Tempo</th>
                        <th>User</th>
                        <th>Tgl Pembuatan</th>
                        <th>Tipe Pembayaran</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
            </table>
        </div>

        {{-- Modal Terima / Selesai --}}
        <div class="modal fade" id="modalSetSelesai" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel"> <strong>SET SELESAI SIRKULAR INVOICE <span
                                    id="doc_spb"></strong></span></h5>
                        <button type="button" class="close close_modal_set" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div id="detailSetSelesai">
                            <form id="formSetSelesai" method="POST"
                                action="{{ route('purchasing.circular_invoice.selesai') }}">
                                @csrf
                                <input type="hidden" name="id" value="">

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label><b>Nama Penerima</b> <span class="text-danger">*</span></label>
                                            <input class="form-control" name="recipient_name" type="text" required
                                                autocomplete="off">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label><b>Tanggal Penerimaan</b> <span class="text-danger">*</span></label>
                                            <input class="form-control" name="receipt_date" type="date" required
                                                autocomplete="off">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label><b>Catatan Penerimaan</b></label>
                                    <input id="receipt_note_input" type="hidden" name="receipt_note">
                                    <trix-editor input="receipt_note_input" placeholder="Catatan"></trix-editor>
                                </div>

                                <div class="text-right">
                                    <button type="submit" class="btn btn-primary">SET SELESAI</button>
                                </div>
                            </form>
                        </div>

                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection


@section('js')
    <script>
        $(document).ready(function() {

            // === DataTables ===
            $('#dataTables').DataTable({
                processing: true,
                serverSide: true,
                // ajax: '{{ route('purchasing.circular_invoice.datatables') }}',
                stateSave: false,
                ajax: {
                    url: '{{ route('purchasing.circular_invoice.datatables') }}',
                    data: function(d) {
                        // kirim nilai filter dari server (hasil submit search)
                        d.type_payment = "{{ $filters['type_payment'] ?? '' }}";
                        d.company_id = "{{ $filters['company_id'] ?? '' }}";
                        d.status = "{{ $filters['status'] ?? '' }}";
                        d.po_up_20m = "{{ $filters['po_up_20m'] ?? '' }}";
                    }
                },
                pageLength: 50,
                order: [
                    [1, 'desc']
                ],
                columnDefs: [{
                    targets: 0,
                    orderable: false,
                    searchable: false,
                    className: 'text-center',
                    render: function(data, type, row, meta) {
                        // return '<input type="checkbox" class="item-checkbox" value="' + row.CIID + '">';
                        return '<input type="checkbox" class="magic-checkbox item-checkbox po_id" value="' +
                            row.CIID + '" id="cb_' + row.CIID + '"><label for="cb_' + row.CIID +
                            '"></label>';
                        return '<div class="d-flex justify-content-center align-items-center" style="height:100%;">' +
                            '<input type="checkbox" class="magic-checkbox item-checkbox po_id" value="' +
                            row.CIID + '" id="cb_' + row.CIID + '">' +
                            '<label for="cb_' + row.CIID + '"></label>' +
                            '</div>';
                    }
                }],
                columns: [{
                        data: null,
                        name: 'checkbox',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'no_si',
                        name: 'circular_invoices.doc_no'
                    },
                    {
                        data: 'no_po',
                        name: 'po.doc_no'
                    },
                    {
                        data: 'no_pr',
                        name: 'purchase_requisitions.doc_no'
                    },
                    {
                        data: 'no_invoice_ext',
                        name: 'circular_invoices.invoice_number_ext'
                    },
                    {
                        data: 'date_invoice_ext',
                        name: 'circular_invoices.date_invoice_ext'
                    },
                    {
                        data: 'tgl_tempo_pembayaran',
                        name: 'circular_invoices.due_date_payment'
                    },
                    {
                        data: 'user_nama',
                        name: 'users.name'
                    },
                    {
                        data: 'tgl_pembuatan',
                        name: 'circular_invoices.created_at'
                    },
                    {
                        data: 'type_payment',
                        name: 'circular_invoices.type_payment'
                    },
                    {
                        data: 'status',
                        name: 'circular_invoices.status'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    },
                ]
            });

            // === Checkbox Select All ====
            $('#checkAll').on('click', function() {
                $('.item-checkbox').prop('checked', this.checked);
            });

            // === Kondisi Tombol Print Multiple Aktif / Non Aktif ===
            $(document).on('change', '.item-checkbox, #checkAll', function() {
                const anyChecked = $('.item-checkbox:checked').length > 0;
                $('#btnPrintMultiple').prop('disabled', !anyChecked);
            });

            // === Fungsi Tombol Run Print Multiple ===
            $('#btnPrintMultiple').on('click', function() {
                const selectedIds = $('.item-checkbox:checked')
                    .map(function() {
                        return $(this).val();
                    }).get();

                if (selectedIds.length > 0) {
                    const url = "{{ route('purchasing.circular_invoice.print_multiple') }}";
                    window.open(url + '?ids=' + selectedIds.join(','), '_blank');
                }
            });

            // === Fungsi Tombol Filter ===
            $('#btn-filter').click(function() {
                $('form#form').attr('action', "{{ route('purchasing.circular_invoice.search') }}");
                if ($('form#form').valid()) {
                    $('form#form').submit();
                }
            });

            // Inisialisasi Select2 Default "All"
            $('select[name="company_id"], select[name="po_up_20m"], select[name="status"], select[name="type_payment"]')
                .select2({
                    allowClear: false,
                    placeholder: null
                });

            // Kembalikan Select All Search, Jika Refresh Halaman
            $(function() {
                // Init Select2 Tanpa Placeholder
                const $sels = $(
                    'select[name="status"], select[name="type_payment"], select[name="company_id"], select[name="po_up_20m"]'
                );
                $sels.each(function() {
                    if ($(this).hasClass('select2-hidden-accessible')) $(this).select2('destroy');
                });
                $sels.select2({
                    allowClear: false,
                    placeholder: null
                });

                // Data Filter Dari Server
                const filters = @json($filters);

                // Jika Request Kosong (''), Kembalikan Menjadi All
                if (!filters.status) $('select[name="status"]').val('').trigger('change.select2');
                if (!filters.type_payment) $('select[name="type_payment"]').val('').trigger(
                    'change.select2');
                if (!filters.company_id) $('select[name="company_id"]').val('').trigger('change.select2');
                if (!filters.po_up_20m) $('select[name="po_up_20m"]').val('').trigger('change.select2');

                $('#form').attr('autocomplete', 'off');
            });

            // Klik Card Untuk Get Tipe Pembayaran CI
            $('.clickable-card').on('click', function() {
                const formId = $(this).data('target-form');
                $('#' + formId).trigger('submit');
            });

            // Modal Action Selesai / Terima SI
            $(function() {
                $('#modalSetSelesai').on('show.bs.modal', function(event) {
                    var button = $(event.relatedTarget);
                    var hashId = button.data('id') || '';
                    var doc = button.data('doc') || '';

                    var $form = $('#formSetSelesai');

                    // Reset Form Error
                    if ($form.length) {
                        $form[0].reset();
                        $form.find('.is-invalid').removeClass('is-invalid');
                        $form.find('.invalid-feedback').remove();
                        $form.find('input[name="id"]').val(hashId);
                    }

                    var trix = document.querySelector('trix-editor[input="receipt_note_input"]');
                    if (trix && trix.editor) trix.editor.loadHTML('');

                    // Judul Dokumen
                    $('#doc_spb').text(doc ? ' - ' + doc : '');
                });

                // Bersihkan Jika Modal Close
                $('#modalSetSelesai').on('hidden.bs.modal', function() {
                    var $form = $('#formSetSelesai');
                    if ($form.length) {
                        $form[0].reset();
                        $form.find('.is-invalid').removeClass('is-invalid');
                        $form.find('.invalid-feedback').remove();
                    }
                    var trix = document.querySelector('trix-editor[input="receipt_note_input"]');
                    if (trix && trix.editor) trix.editor.loadHTML('');
                    $('#doc_spb').text('');
                });
            });

        });
    </script>

    {{-- Konfirmasi Cancel / Publish --}}
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
                title: 'Konfirmasi',
                text: doc ? ('Publish Sirkular Invoice ' + doc) : 'Publish data ini?',
                type: 'question',
                showCancelButton: true,
                confirmButtonClass: 'btn btn-danger',
                cancelButtonClass: 'btn btn-primary',
                confirmButtonText: 'Publish',
                cancelButtonText: 'Batal'
            }).then(function(res) {
                if (res.value) postAndFollow(url);
            });
        });

        // Konfirmasi Cancel
        $(document).on('click', '.btn-cancel', function() {
            var url = $(this).data('url');
            var doc = $(this).data('doc') || '';
            Swal.fire({
                title: 'Konfirmasi',
                text: doc ? ('Batalkan Sirkular Invoice ' + doc) : 'Batalkan data ini?',
                type: 'warning',
                showCancelButton: true,
                confirmButtonClass: 'btn btn-danger',
                cancelButtonClass: 'btn btn-primary',
                confirmButtonText: 'Cancel',
                cancelButtonText: 'Batal'
            }).then(function(res) {
                if (res.value) postAndFollow(url);
            });
        });
    </script>
@endsection


@section('css')
    <style>
        .clickable-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            cursor: pointer;
        }

        .clickable-card:hover {
            transform: translateY(-5px) scale(1.03);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        }
    </style>
@endsection
