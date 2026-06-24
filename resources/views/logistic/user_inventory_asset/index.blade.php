@extends('layouts.app')

@section('page-header')
    Daftar Inventory Asset
@endsection

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item active" aria-current="page">User Inventory Asset</li>
    </ol>
@endsection
@section('content')
<div class="row mB-40">
    <div class="col-sm-12">
        <div class="bgc-white p-20 bd">
            <div class="bgc-white bd bdrs-3 p-20 mB-20 table-responsive mt-3">
                <table id="dataTables" class="table table-bordered table-striped" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>NIK</th>
                            <th>Jumlah Asset</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="modalShow" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
    data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-xl" role="document" style="box-shadow: 0 4px 8px rgba(0, 0, 0, 0.4);">
        <div class="modal-content" style="border: 2px solid #0088c1; border-radius: 10px;">
            <div class="modal-header" style="background-color: #0088c1">
                <h5 class="modal-title" style="color: white" id="modalShowTitle">SHOW DATA</h5>
                <button type="button" class="close" style="color: white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="margin: 20px; max-height: 600px; overflow-y: auto;">
                <div class="modalError"></div>
                <div id="modalShowContent"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
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
                ajax: '{{ route('logistic.user_inventory_asset_datatables.datatables') }}',
                columns: [
                    {data: 'name', name: 'user_assets.name'},
                    {data: 'nik', name: 'user_assets.nik'},
                    {data: 'count', name: 'count',orderable: false, searchable: false},
                    {data: 'action', name: 'action', orderable: false, searchable: false}
                ]
            });

            $(document).on('click', '.modalShow', function (e) {
                e.preventDefault();
                var url = $(this).attr('value');
                $('#modalShowContent').html('');
                $('.modalError').html('');
                $.ajax({
                    url: url,
                    type: 'GET',
                    dataType: 'html',
                    success: function (response) {
                        $('#modalShowContent').html(response);
                        $('#modalShow').modal('show');
                    },
                    error: function (xhr, status, error) {
                        $('.modalError').html('<div class="alert alert-danger">Failed to load data. Please try again later.</div>');
                    }
                });
            });
        });
    </script>
@stop
