@extends('layouts.app')

@section('page-header')
    Approval DPH
@endsection

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item active" aria-current="page">Approval DPH</li>
    </ol>
@endsection

@section('content')
<div id="validation-message"></div>
<div class="row mB-40">
    <div class="col-sm-12">
        <div class="bgc-white bd bdrs-3 p-20 mB-20">
            <div class="row">
                <div class="col-sm-6 ">
                    <a href="{{ route('purchasing.dph.index') }}">
                        <i class="ti-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-sm-12">
                    <table id="dataTables" class="table table-striped table-bordered" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th>No. DPH</th>
                                <th>Dibuat Oleh</th>
                                <th>Jumlah Supplier</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                    </table>
                </div>
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
            "pageLength": 10,
            ajax: '{{ route('approval.dph.datatables') }}',
            columns: [
                {data: 'doc_no', name: 'dph.doc_no'},
                {data: 'created', name: 'created', searchable: false},
                {data: 'supplier_count', name: 'supplier_count', orderable: false, searchable: false},
                {data: 'action', name: 'action', orderable: false, searchable: false}
            ],
            "order": [[ 0, "asc" ]]
        });
    });
</script>
@stop
