@extends('layouts.app')

@section('page-header')
    Currency 
@endsection

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item active" aria-current="page">Currency</li>
    </ol>
@endsection

@section('content')

<div class="row mB-40">
    
    @include('purchase.master.menu')

    <div class="col-sm-9">
        <div class="bgc-white p-20 bd">

            <div class="mB-20">
                <a href="{{ route('purchasing.currency.create') }}" class="btn btn-info">
                    Tambah Data
                </a>
            </div>


            <div class="bgc-white bd bdrs-3 p-20 mB-20">
                <table id="dataTables" class="table table-striped table-bordered" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Symbol</th>
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
            ajax: '{{ route('purchasing.currency.datatables') }}',
            columns: [
                {data: 'name', name: 'name'},
                {data: 'symbol', name: 'symbol',  orderable: false, searchable: false},
                {data: 'updated_at', name: 'updated_at', searchable: false},
                {data: 'action', name: 'action', orderable: false, searchable: false}
            ]
        });
    });
</script>
@stop