@extends('layouts.app')

@section('page-header')
    Payment Term 
@endsection

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item active" aria-current="page">Payment Term</li>
    </ol>
@endsection

@section('content')

<div class="row mB-40">
 
    @include('purchase.master.menu')

    <div class="col-sm-9">
        <div class="bgc-white p-20 bd">

            <div class="mB-20">
                <a href="{{ route('purchasing.payment_terms.create') }}" class="btn btn-info">
                    Tambah Data
                </a>
            </div>


            <div class="bgc-white bd bdrs-3 p-20 mB-20">
                <table id="dataTables" class="table table-striped table-bordered" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th>Deskripsi</th>
                            <th>DP (%)</th>
                            <th>Type Body Email</th>
                            <th>Jumlah Hari setelah pengiriman</th>
                            <th>Status</th>
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
            ajax: '{{ route('purchasing.payment_terms.datatables') }}',
            columns: [
                {data: 'name', name: 'name'},
                {data: 'dp_percentage', name: 'dp_percentage'},
                {data: 'type_body_email', name: 'type_body_email'},
                {data: 'days_after_delivery', name: 'days_after_delivery'},
                {data: 'status', name: 'status',  orderable: false, searchable: false},
                {data: 'updated_at', name: 'updated_at', searchable: false},
                {data: 'action', name: 'action', orderable: false, searchable: false}
            ]
        });
    });
</script>
@stop