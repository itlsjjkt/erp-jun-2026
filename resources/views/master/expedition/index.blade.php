@extends('layouts.app')

@section('page-header')
    Ekspedisi / Vendor
@endsection

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item active" aria-current="page">Master Ekspedisi</li>
    </ol>
@endsection

@section('content')

<div class="row mB-40">
 
    <div class="col-sm-12">
        <div class="bgc-white p-20 bd">
            @include('master.menulogistik')
            <h6>Ekspedisi</h6>
            <hr class="mB-30">

            <div class="mB-20">
                <a href="{{ route('master.expeditions.create') }}" class="btn btn-info text-uppercase fsz-sm fw-600">
                    {{ trans('app.add_button') }}
                </a>
            </div>


            <div class="bgc-white bd bdrs-3 p-20 mB-20">
                <table id="dataTables" class="table table-striped table-bordered" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>PIC</th>
                            <th>Email</th>
                            <th>Telp</th>
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
            ajax: '{{ route('master.expeditions.datatables') }}',
            columns: [
                {data: 'name', name: 'name'},
                {data: 'pic', name: 'pic'},
                {data: 'email', name: 'email'},
                {data: 'telp', name: 'telp'},
                {data: 'status', name: 'status', searchable: false},
                {data: 'updated_at', name: 'updated_at', searchable: false},
                {data: 'action', name: 'action', orderable: false, searchable: false}
            ]
        });
    });
</script>
@stop