@extends('layouts.app')

@section('page-header')
     Monitoring Item BPB Jakarta
@endsection

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item active" aria-current="page"> Monitoring Item BPB Jakarta</li>
    </ol>
@endsection

@section('content')
<div class="bgc-white bd bdrs-3 p-20 mB-20">

    <div class="alert alert-info">
        - Monitoring item BPB digunakan untuk memonitoring item BPB Jakarta.
    </div>

    <table id="dataTables" class="table table-striped table-bordered" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th>Nama Item</th>
                <th>Qty</th>
                <th>Satuan</th>
                <th>No. BPB</th>
                <th>Tgl Input</th>
                <th>Penerima Oleh</th>
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
    const bpb_id = '{{ $bpb_id ?? '' }}';

    $('#dataTables').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route('logistic.monitoring.item_bpb_jakarta.datatables') }}',
            data: { bpb_id: bpb_id }
        },
        "pageLength": 50,
        order: [[3, 'desc']], 
        columns: [
            { data: 'product', name: 'master_item_products.name' },
            { data: 'qty', name: 'qty' }, 
            { data: 'measure_name', name: 'measure_name' }, 
            { data: 'bpb_doc_no', name: 'bpb.doc_no' },
            { data: 'bpb_created_at', name: 'bpb.created_at' },
            { data: 'bpb_penerima', name: 'bpb.received_by' },
            { data: 'company', name: 'companies.name' },
            { data: 'action', name: 'action', orderable: false, searchable: false },
        ]
    });
});
</script>





@endsection