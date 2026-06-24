@extends('layouts.app')

@section('page-header')
    Project 
@endsection

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item active" aria-current="page">Project</li>
    </ol>
@endsection

@section('content')

<div class="row mB-40">

    <div class="col-sm-12">
        <div class="bgc-white p-20 bd">
            <h6> Project</h6>
            <hr class="mB-30">

            <div class="mB-20">
                <a href="{{ route('master.project.create') }}" class="btn btn-info">
                    {{ trans('app.add_button') }}
                </a>
                <a  href="{{ route('master.project.export') }}" class="btn btn-outline border-dark float-right text-uppercase fsz-sm fw-600" aria-controls="export">
                    <i class="fa fa-file-excel-o text-success icon-lg"></i> Export
                </a>
            </div>


            <div class="bgc-white bd bdrs-3 p-20 mB-20">
                <table id="dataTables" class="table table-striped table-bordered" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Code</th>
                            <th>Kategori</th>
                            <th>Status</th>
                            <th>Updated</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
    <script>
    $(document).ready(function() {
        $('#dataTables').DataTable({
            pageLength: 25,
            processing: true,
            serverSide: true,
            ajax: '{{ route('master.project.datatables') }}',
            columns: [
                {data: 'name', name: 'name'},
                {data: 'code', name: 'code'},
                {data: 'category', name: 'category', orderable: false, searchable: false},
                {data: 'status', name: 'status', searchable: false},
                {data: 'updated_at', name: 'updated_at', searchable: false},
                {data: 'action', name: 'action', orderable: false, searchable: false}
            ]
        });
    });
</script>
@stop