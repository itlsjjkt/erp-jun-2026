@extends('layouts.app')

@section('page-header')
     Monitoring Item SPB
@endsection

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item active" aria-current="page"> Monitoring Item SPB</li>
    </ol>
@endsection

@section('content')
<div class="bgc-white bd bdrs-3 p-20 mB-20">

    <div class="alert alert-info">
        - Monitoring item SPB digunakan untuk memonitoring item SPB.
    </div>

    <table id="dataTables" class="table table-striped table-bordered" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th>Nama Item</th>
                <th>Quantity</th>
                <th>Satuan</th>
                <th>No. SPB</th>
                <th>Tipe</th>
                <th>Dibuat Oleh</th>
                <th>Tgl Input</th>
                <th>Company</th>
                <th style="text-align:center;">Action</th>
            </tr>
        </thead>
    </table>
</div>
@endsection

@section('js')

<script>
$(document).ready(function () {
    $('#dataTables').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route('logistic.monitoring.item_spb.datatables') }}',
        "pageLength": 50,
        order: [[3, 'desc']], 
        columns: [
            { data: 'product', name: 'master_item_products.name' },
            { data: 'quantity', name: 'spb_kolis.qty' },
            { data: 'satuan', name: 'measures.name' },
            { data: 'doc_no', name: 'spb.doc_no' },
            { data: 'type', name: 'spb.type' },
            { data: 'creator_name', name: 'users.name' },
            { data: 'created_at', name: 'spb.created_at' },
            { data: 'company', name: 'companies.name' },
            { data: 'action', name: 'action', orderable: false, searchable: false },
        ]
    });
});
</script>



@endsection
