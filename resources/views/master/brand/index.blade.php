@extends('layouts.app')

@section('page-header')
    Merk
@endsection

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item active" aria-current="page">Master Logistik</li>
    </ol>
@endsection

@section('content')

<div class="row mB-40">
 
    <div class="col-sm-12">
        <div class="bgc-white p-20 bd">
            @include('master.menulogistik')
            <h6>Merk</h6>
            <hr class="mB-30">

            <div class="row mB-20">
                <div class="col-sm-12">
                    <a href="{{ route('master.brands.create') }}" class="btn btn-info text-uppercase fsz-sm fw-600">
                        TAMBAH
                    </a>

                    <a  href="{{ route('master.brands.export') }}" class="btn btn-outline border-dark float-right text-uppercase fsz-sm fw-600" aria-controls="export">
                        <i class="fa fa-file-excel-o text-success icon-lg"></i> Export
                    </a>
                    @if(isAdministrator() || isAdministratorCompany())
                        <a href="{{ route('master.brands.upload') }}" class="btn btn-outline border-dark float-right text-uppercase fsz-sm fw-600">
                            <i class="fa fa-file-excel-o text-danger icon-lg"></i> Upload
                        </a>
                    @endif
                    
                </div>
             </div>


            <div class="bgc-white bd bdrs-3 p-20 mB-20">
                <table id="dataTables" class="table table-striped table-bordered" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
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
            pageLength: 50,
            processing: true,
            serverSide: true,
            ajax: '{{ route('master.brands.datatables') }}',
            columns: [
                {data: 'id', name: 'id'},
                {data: 'name', name: 'name'},
                {data: 'status', name: 'status', searchable: false},
                {data: 'updated_at', name: 'updated_at', searchable: false},
                {data: 'action', name: 'action', orderable: false, searchable: false}
            ]
        });
    });
</script>
@stop