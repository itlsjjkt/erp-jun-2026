@extends('layouts.app')

@section('page-header')
    Sirkular Invoice
@stop


@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('purchasing.circular_invoice') }}">Sirkular Invoice</a></li>
        <li class="breadcrumb-item active" aria-current="page">Daftar PO</li>
    </ol>
@endsection


@section('content')
    <div class="bgc-white p-30 bd">

        <div class="alert alert-info mT-3">
            Berikut daftar PO dengan status : <b>Issued</b>, <b>Parsial</b>, <b>Done</b> yang belum diterbitkan Sirkular
            Invoice, Silahkan pilih
            PO yang akan diterbitkan menjadi Sirkular Invoice
        </div>

        {{-- Button Function --}}
        <div class="justify-content-between align-items-center mb-3">
            <div class="row mb-3">
                <div class="col-md-6">
                </div>
                <div class="col-md-6 text-right">
                    <a href="#" data-toggle="collapse" data-target="#filter" class="btn btn-outline border-dark">
                        <i class="ti-search icon"></i>&nbsp; FILTER
                    </a>
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
                            <label>Status PO</label>
                            {!! Form::select(
                                'status',
                                [
                                    '' => 'All',
                                    2 => 'Issued',
                                    4 => 'Parsial',
                                    5 => 'Done',
                                ],
                                $filters['status'] ?? '',
                                ['class' => 'form-control select2'],
                            ) !!}
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

        {{-- Data Table --}}
        <div class="table-responsive mt-3">
            <table id="dataTables" class="table table-striped table-bordered" cellspacing="0" width="100%">
                <colgroup>
                    <col style="width: 12%;">
                    <col style="width: 15%;">
                    <col style="width: 14%;">
                    <col style="width: 19%;">
                    <col style="width: 10%;">
                    <col style="width: 10%;">
                    <col style="width: 7%;">
                    <col style="width: 5%;">
                    <col style="width: 6%;">
                    <col style="width: 16%;">
                </colgroup>
                <thead class="item_form">
                    <tr>
                        <th>No PO</th>
                        <th>No PR</th>
                        <th>Perusahaan</th>
                        <th>Supplier</th>
                        <th>Harga PO</th>
                        <th>Harga SI</th>
                        <th>Status</th>
                        <th>Status Harga</th>
                        <th>Jumlah SI</th>
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
                    // ajax: '{{ route('purchasing.circular_invoice.list_datatables') }}',
                    stateSave: false,
                    ajax: {
                        url: '{{ route('purchasing.circular_invoice.list_datatables') }}',
                        data: function(d) {
                            // kirim nilai filter dari server (hasil submit search)
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
                            data: 'harga_po',
                            name: 'po.payment_amount',
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: 'total_harga_si',
                            name: 'total_harga_si',
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: 'po_status',
                            name: 'po_status',
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: 'status_harga',
                            name: 'status_harga',
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: 'jumlah_si',
                            name: 'jumlah_si',
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
                $('form#form').attr('action', "{{ route('purchasing.circular_invoice.search_po') }}");
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
                // Init Select2 Tanpa Placeholder
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

                // Jika Request Kosong (''), Kembalikan Menjadi All
                if (!filters.status) $('select[name="status"]').val('').trigger('change.select2');
                if (!filters.po_up_20m) $('select[name="po_up_20m"]').val('').trigger('change.select2');

                $('#form').attr('autocomplete', 'off');
            });

        });
    </script>
@stop
