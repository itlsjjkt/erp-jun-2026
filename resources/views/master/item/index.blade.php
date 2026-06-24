@extends('layouts.app')

@section('page-header')
    Master Kategori Produk
@endsection

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item active" aria-current="page">Master Kategori Produk</li>
    </ol>
@endsection

@section('content')

<div class="row mB-40">
 

    <div class="col-sm-12">
        <div class="bgc-white p-20 bd">
            @include('master.menulogistik')
            <h6>Items</h6>
            <hr class="mB-30">

            <div class="row mB-20">
                <div class="col-sm-12">
                    @if(isAdministrator() || isAdministratorCompany())
                        <a href="{{ route('master.items.create') }}" class="btn btn-info text-uppercase fsz-sm fw-600">
                            Tambah
                        </a>
                    @endif
                    <a href="{{ route('master.items.export')}}" class="btn btn-outline border-dark float-right text-uppercase fsz-sm fw-600">
                        <i class="fa fa-file-excel-o text-success icon-lg"></i> Export Data
                    </a>
                </div>
            </div>


            <div class="bgc-white bd bdrs-3 p-20 mB-20">
                <table id="dataTables" class="table table-striped table-bordered" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Kode</th>
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
            processing: true,
            pageLength: 25,
            serverSide: true,
            ajax: '{{ route('master.items.datatables') }}',
            columns: [
                {data: 'id', name: 'id'},
                {data: 'code', name: 'code'},
                {data: 'name', name: 'name'},
                {data: 'status', name: 'status',  orderable: false, searchable: false},
                {data: 'updated_at', name: 'updated_at', searchable: false},
                {data: 'action', name: 'action', orderable: false, searchable: false}
            ]
        });
    });
</script>
@stop