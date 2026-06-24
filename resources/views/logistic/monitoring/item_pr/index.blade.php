@extends('layouts.app')

@section('page-header')
     Monitoring Item PR
@endsection

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item active" aria-current="page"> Monitoring Item PR</li>
    </ol>
@endsection

@section('content')
<div class="bgc-white bd bdrs-3 p-20 mB-20">

    <div class="alert alert-info">
        - Monitoring item PR digunakan untuk memonitoring item PR.
    </div>
    <div class="table-responsive">
        <table id="dataTables" class="table table-striped table-bordered" cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th>Nama Barang</th>
                    <th>Quantity</th>
                    <th>Satuan</th>
                    <th>No. PR</th>
                    <th>No. DPM</th>
                    <th>Department/Kapal</th>
                    <th>Purchaser</th>
                    <th>Tipe</th>
                    <th>Tgl Input</th>
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
        $(document).ready(function() {
            $('#dataTables').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('logistic.monitoring.item_pr.datatables') }}',
                "pageLength": 50,
                columns: [
                    {data: 'product', name: 'master_item_products.name'},
                    {data: 'qty', name: 'qty', searchable: false},
                    {data: 'measure', name: 'measure', searchable: false},
                    {data: 'no_pr', name: 'purchase_requisitions.doc_no'},
                        { data: 'no_dpm', name: 'purchases.doc_no' },
                    {data: 'department', name: 'departments.name'},
                    {data: 'purchaser', name: 'users.name'},
                    {data: 'type', name: 'purchase_requisitions.type'},
                    {data: 'created', name: 'purchases.created_at'},
                    {data: 'status', name: 'purchase_requisitions.status', orderable: false, searchable: false},
                    {data: 'action', name: 'action', orderable: false, searchable: false},
					{data: 'PN', name: 'master_item_products.part_number', visible: false }
                ],
                "order": [[ 7, "desc" ]]
            });
        });
    </script>
@stop
