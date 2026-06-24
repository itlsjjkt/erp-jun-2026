@extends('layouts.app')

@section('page-header')
    Master User Asset
@endsection

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item active" aria-current="page">Master User Asset</li>
    </ol>
@endsection
@section('content')
<div class="row mB-40">
    <div class="col-sm-12">
        <div class="bgc-white p-20 bd">
            <hr class="mB-30">
            <div class="row mB-20">
                <div class="col-sm-12">
                    <a href="{{ route('logistic.master_user_asset.create') }}" class="btn btn-info text-uppercase fsz-sm fw-600">
                        TAMBAH USER
                    </a>
                </div>
             </div>
            <div class="bgc-white bd bdrs-3 p-20 mB-20 table-responsive">
                <table id="dataTables" class="table table-striped table-bordered" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th style="width:50%;">Name</th>
                            <th style="width:35%;">NIK</th>
                            <th style="width:10%;">Status</th>
                            <th style="width:5%;">Actions</th>
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
            ajax: '{{ route('logistic.master_user_asset_datatables.datatables') }}',
            columns: [
                {data: 'name', name: 'name'},
                {data: 'nik', name: 'nik'},
                {data: 'status', name: 'status', searchable: false},
                {data: 'action', name: 'action', orderable: false, searchable: false}
            ]
        });
    });

    $(document).on('click', ".btn_deleteee", function(e) {
        var _this = $(this);
        var form = _this.parents('form');
        e.preventDefault();
        if (form.valid() ) {
            Swal.fire({
                title: 'Delete',
                text: 'Apakah anda yakin untuk menghapus data?',
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: 'null',
                cancelButtonColor: 'null',
                confirmButtonClass: 'btn btn-danger',
                confirmButtonColor: '#d33',
                cancelButtonClass: 'btn btn-primary',
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal'
            }).then(res => {
                if (res.value) {
                    _this.closest("form").submit();
                }
            });
        }
    });
</script>
@stop
