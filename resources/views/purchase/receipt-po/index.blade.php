@extends('layouts.app')

@section('page-header')
    Receipt Purchase Order
@endsection

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item active" aria-current="page">Receipt Purchase Order</li>
    </ol>
@endsection

@section('content')
<div class="bgc-white bd bdrs-3 p-20 mB-20">
    <div class="table-responsive mt-3" style="overflow-y: hidden !important;">
        <table id="dataTables" class="table table-striped table-bordered" cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th>No. PO</th>
                    <th>Supplier</th>
                    <th>Purchaser</th>
                    <th>Tgl Dibuat</th>
                    <th>Type Penerimaan</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
        </table>
    </div>
</div>
@endsection

@section('js')
<script>
$(document).ready(function () {
    $('#dataTables').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 50,
        ajax: {
            url: '{{ route("purchasing.receipt-po.datatables") }}',
        },
        columns: [
            { data: 'doc_no',     name: 'po.doc_no' },
            { data: 'supplier',   name: 'suppliers.name' },
            { data: 'created',    name: 'users.name' },
            { data: 'created_at', name: 'po.created_at', searchable: false },
            { data: 'type',       name: 'po.type',    searchable: false, orderable: false },
            { data: 'status',     name: 'po.status',     searchable: false, orderable: false },
            { data: 'action',     name: 'action',        orderable: false,  searchable: false },
        ],
        order: [[3, 'desc']],
    });
});
</script>
@stop
