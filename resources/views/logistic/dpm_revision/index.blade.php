@extends('layouts.app')

@section('page-header')
    DPM Revision
@endsection

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item active" aria-current="page">DPM Revision</li>
    </ol>
@endsection

@section('content')

    <div class="bgc-white bd bdrs-3 p-20 mB-20">

        <div class="row mB-20">
            <div class="col-sm-6 ">
                <a href="{{ route('purchase_request.index') }}" class="" >
                    <i class="ti-arrow-left"></i> Kembali
                </a>
            </div>
        </div>

        <hr>

        <div class="alert alert-warning">
            <b>INFORMASI</b><br> Halaman ini DPM yang sudah terbit PR namun ada permintaan revisi dari Purchasing dan DPM Hold.
             
        </div>

        <div class="table-responsive mt-5">
            <table id="dataTables" class="table table-striped table-bordered" cellspacing="0" width="100%">
                <thead>
                    <tr>
                        <th>No. DPM</th>
                        <th>Kapal/Departemen</th>
                        <th>Project</th>
                        <th>Dibuat Oleh</th>
                        <th>Tgl Input</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
            </table>
        </div>

    </div>

@endsection


@section('js')
    <script>
    $(document).ready(function() {
        $('#dataTables').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route('purchase_revision.datatables') }}',
            "pageLength": 50,
            columns: [
                {data: 'doc_no', name: 'doc_no'},
                {data: 'department', name: 'departments.name'},
                {data: 'project', name: 'projects.name'},
                {data: 'created', name: 'users.name'},
                {data: 'updated_at', name: 'updated_at', searchable: false},
                {data: 'status', name: 'status', searchable: false},
                {data: 'action', name: 'action', orderable: false, searchable: false}
            ],
            "order": [[ 4, "desc" ]]
        });


    });
</script>
@stop
