@extends('layouts.app')

@section('page-header')
    Kategori Asset 
@endsection

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item active" aria-current="page">Kategori Asset</li>
    </ol>
@endsection

@section('content')

<div class="row mB-40">
 
    <div class="col-sm-12">
        <div class="bgc-white p-20 bd">

            <div class="mB-20">
                <a href="{{ route('accounting.asset_category.create') }}" class="btn btn-info">
                    Tambah Data
                </a>
            </div>


            <div class="bgc-white bd bdrs-3 p-20 mB-20">
                <table id="dataTables" class="table table-striped table-bordered" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Periodik</th>
                            <th>Depresiasi Method</th>
                            <th>Updated</th>
                            <th>Action</th>
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
            serverSide: true,
            "pageLength": 50,
            ajax: '{{ route('accounting.asset_category.datatables') }}',
            columns: [
                {data: 'name', name: 'name'},
                {data: 'time_method', name: 'time_method',  orderable: false, searchable: false},
                {data: 'compute_method', name: 'compute_method',  orderable: false, searchable: false},
                {data: 'updated_at', name: 'updated_at', searchable: false},
                {data: 'action', name: 'action', orderable: false, searchable: false}
            ],
            "order": [[ 0, "asc" ]]
        });
    });
</script>
@stop