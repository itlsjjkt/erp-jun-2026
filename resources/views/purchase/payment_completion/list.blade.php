@extends('layouts.app')

@section('page-header')
    Payment Completion
@stop


@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('purchasing.payment_completion') }}">Payment Completion</a></li>
        <li class="breadcrumb-item active" aria-current="page">Payment Completion PO</li>
    </ol>
@endsection


@section('content')
    <div class="bgc-white p-30 bd">

        <div class="alert alert-info mT-3">
            Berikut daftar PO dengan status : <b>Issued</b>, <b>Parsial</b>, <b>Done</b> yang belum diterbitkan Payment
            Completion, Silahkan pilih PO yang akan diterbitkan menjadi Payment Completion
        </div>

        {{-- Data Table --}}
        <div class="table-responsive mt-3">
            <table id="dataTables" class="table table-striped table-bordered" cellspacing="0" width="100%">
                <colgroup>
                    <col style="width: 17%;">
                    <col style="width: 15%;">
                    <col style="width: 15%;">
                    <col style="width: 15%;">
                    <col style="width: 15%;">
                    <col style="width: 6%;">
                    <col style="width: 10%;">
                </colgroup>
                <thead class="item_form">
                    <tr>
                        <th>No PO</th>
                        <th>No PR</th>
                        <th>Perusahaan</th>
                        <th>Supplier</th>
                        <th>Payment Term</th>
                        <th>Status PO</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>

                </tbody>
            </table>
        </div>

    </div>
@stop


@section('js')
    <script>
        $(document).ready(function() {

            // Data Tables
            $(document).ready(function() {
                $('#dataTables').DataTable({
                    processing: true,
                    serverSide: true,
                    stateSave: false,
                    ajax: {
                        url: '{{ route('purchasing.payment_completion.list_po_datatables') }}',
                        data: function(d) {
                            d.status = "{{ $filters['status'] ?? '' }}";
                            d.po_up_20m = "{{ $filters['po_up_20m'] ?? '' }}";
                        }
                    },
                    "pageLength": 50,
                    columns: [{
                            data: 'po_no',
                            name: 'po.doc_no'
                        },
                        {
                            data: 'no_pr',
                            name: 'purchase_requisitions.doc_no'
                        },
                        {
                            data: 'pt_nama',
                            name: 'companies.name'
                        },
                        {
                            data: 'supplier_nama',
                            name: 'suppliers.name'
                        },
                        {
                            data: 'payment_term',
                            name: 'payment_terms.name'
                        },
                        {
                            data: 'po_status',
                            name: 'po_status',
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: 'action',
                            name: 'action',
                            orderable: false,
                            searchable: false
                        }
                    ],
                    "order": [
                        [1, "desc"]
                    ]
                });
            });

            // === Fungsi Tombol Filter ===
            $('#btn-filter').click(function() {
                $('form#form').attr('action', "{{ route('purchasing.payment_completion.search_po') }}");
                if ($('form#form').valid()) {
                    $('form#form').submit();
                }
            });

            // Inisialisasi Select2 Default "All"
            $('select[name="status"], select[name="po_up_20m"]').select2({
                allowClear: false,
                placeholder: null
            });

            // Kembalikan Select All Search, Jika Refresh Halaman
            $(function() {
                const $sels = $(
                    'select[name="status"], select[name="po_up_20m"]'
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

                if (!filters.status) $('select[name="status"]').val('').trigger('change.select2');
                if (!filters.po_up_20m) $('select[name="po_up_20m"]').val('').trigger('change.select2');

                $('#form').attr('autocomplete', 'off');
            });

        });
    </script>
@stop
