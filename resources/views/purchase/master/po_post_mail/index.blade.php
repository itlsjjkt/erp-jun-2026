@extends('layouts.app')

@section('page-header')
    CC Email PO
@endsection

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item active" aria-current="page">CC Email PO</li>
    </ol>
@endsection

@section('content')

<div class="row mB-40">

    @include('purchase.master.menu')

    <div class="col-sm-9">
        <div class="bgc-white p-20 bd">

            <div class="mB-20">
                <a href="{{ route('purchasing.po_post_mails.create') }}" class="btn btn-info">
                    Tambah Data
                </a>
            </div>


            <div class="bgc-white bd bdrs-3 p-20 mB-20">
                <table id="dataTables" class="table table-striped table-bordered" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th>Email</th>
                            <th style="width: 150px;">Status</th>
                            <th style="width:50px;">Action</th>
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
            ajax: '{{ route('purchasing.po_post_mails.datatables') }}',
            columns: [
                {data: 'email', name: 'email'},
                {data: 'status', name: 'status',  orderable: false, searchable: false},
                {data: 'action', name: 'action', orderable: false, searchable: false}
            ]
        });
    });
</script>
@stop
