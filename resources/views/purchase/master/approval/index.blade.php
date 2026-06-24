@extends('layouts.app')

@section('page-header')
    Setting Approval
@endsection

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item active" aria-current="page">Setting Approval</li>
    </ol>
@endsection

@section('content')

<div class="row mB-40">

    @include('purchase.master.menu')

    <div class="col-sm-9">
        <div class="bgc-white p-20 bd">
            <h6>Approval</h6>
            <hr class="mB-30">
            <div class="bgc-white bd bdrs-3 p-20 mB-20">
                <div class="mB-20">
                    <a href="{{ route('purchasing.approval.supplier.config') }}" class="btn btn-info">
                        <i class="ti-settings mR-5"></i> Config Approval Supplier
                    </a>
                </div>
                <table id="dataTables" class="table table-striped table-bordered" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th>Perusahaan</th>
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
            serverSide: true,
            ajax: '{{ route('purchasing.approval.datatables') }}',
            columns: [
                {data: 'name', name: 'name'},
                {data: 'updated_at', name: 'updated_at', searchable: false},
                {data: 'action', name: 'action', orderable: false, searchable: false}
            ]
        });
    });
</script>
@stop
