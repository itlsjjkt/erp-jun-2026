@extends('layouts.app')

@section('page-header')
    Approval DPM
@endsection

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item active" aria-current="page">Approval DPM</li>
    </ol>
@endsection

@section('content')

<div class="row mB-40">
    <div class="col-sm-12">
        <div class="bgc-white bd bdrs-3 p-20 mB-20">
            <table id="dataTables" class="table table-striped table-bordered" cellspacing="0" width="100%">
                <thead>
                    <tr>
                        <th>No. DPM</th>
                        <th>Lokasi/Kapal</th>
                        <th>Departemen</th>
                        <th>Tipe</th>
                        <th>Dibuat Oleh</th>
                        <th>Tgl Input</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

@endsection


@section('js')
    <script>
        $(document).ready(function() {

            $('#dataTables').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('approval.purchase.datatables') }}',
                "pageLength": 50,
                "columnDefs" : [{"targets":3, "type":"date-eu"}],
                columns: [
                    {data: 'doc_no', name: 'doc_no'},
                    {data: 'location', name: 'locations.name'},
                    {data: 'department', name: 'departments.name'},
                    {data: 'type', name: 'type'},
                    {data: 'created', name: 'users.name', searchable: true},
                    {data: 'updated_at', name: 'updated_at'},
                    {data: 'action', name: 'action', orderable: false, searchable: false}
                ],
                "order": [[ 5, "asc" ]]
            });
        });
    </script>

@stop
