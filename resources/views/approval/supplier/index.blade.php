@extends('layouts.app')

@section('page-header')
    Approval Supplier
@endsection

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item active" aria-current="page">Approval Supplier</li>
    </ol>
@endsection

@section('content')
<div class="row mB-40">
    <div class="col-sm-12">
        <div class="bgc-white p-20 bd">

            <div class="row">
                <div class="col-sm-6">
                    <h5>Daftar Pengajuan Supplier</h5>
                </div>
            </div>

            <hr>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                </div>
            @endif

            <div class="bgc-white bd bdrs-3 p-20 mB-20">
                <table id="dataTables" class="table table-striped table-bordered" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th>Nama Supplier</th>
                            <th>Alamat</th>
                            <th>Dibuat Oleh</th>
                            <th>Tanggal Pengajuan</th>
                            <th style="width:60px">Step</th>
                            <th style="width:80px">Aksi</th>
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
            "pageLength": 25,
            ajax: '{{ route('approval.supplier.datatables') }}',
            columns: [
                {data: 'name',            name: 'suppliers.name'},
                {data: 'address',         name: 'suppliers.address'},
                {data: 'created_by_name', name: 'users.name',            orderable: false},
                {data: 'created_at',      name: 'suppliers.created_at'},
                {data: 'step',            name: 'suppliers.step',         orderable: false, searchable: false},
                {data: 'action',          name: 'action',                 orderable: false, searchable: false}
            ],
            "order": [[ 3, "desc" ]]
        });
    });
</script>
@stop
