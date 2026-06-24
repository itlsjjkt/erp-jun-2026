@extends('layouts.app')

@section('page-header')
    Users
@endsection

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item active" aria-current="page">User Management</li>
    </ol>
@endsection

@section('content')

    <div class="mB-20">
        <a href="{{ route(ADMIN . '.users.create') }}" class="btn btn-info">
            {{ trans('app.add_button') }}
        </a>
    </div>

    <div class="bgc-white bd bdrs-3 p-20 mB-20">
        <table id="dataTables" class="table table-striped table-bordered" cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Level Akses</th>
                    <th>Updated</th>
                    <th>Actions</th>
                </tr>
            </thead>
        </table>
    </div>

@endsection

@section('js')
    <script>
    $(document).ready(function() {
        $('#dataTables').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route(ADMIN . '.users.datatables') }}',
            columns: [
                {data: 'name', name: 'name'},
                {data: 'email', name: 'email'},
                {data: 'type', name: 'type'},
                {data: 'updated_at', name: 'updated_at', searchable: false},
                {data: 'action', name: 'action', orderable: false, searchable: false}
            ],
            "pageLength": 50
        });
    });
</script>
@stop