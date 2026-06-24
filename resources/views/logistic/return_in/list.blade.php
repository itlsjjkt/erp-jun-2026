@extends('layouts.app')

@section('page-header')
    Return In
@stop

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('logistic.inventory.index') }}">Inventory</a></li>
        <li class="breadcrumb-item"><a href="{{ route('logistic.return_in.index') }}">Return In</a></li>
        <li class="breadcrumb-item active" aria-current="page">List ROT</li>
    </ol>
@endsection


@section('content')
	<div class="bgc-white p-30 bd">
        <h6 class='mT-10'>List Return Out</h6>
        <hr>
        <div class="alert alert-info mT-3">
           Berikut daftar ROT yang belum diterbitkan RIN, Silahkan pilih ROT yang akan dibuatkan RIN. 
        </div>
        <table class="table table-bordered mt-2" id="dataTables">
            <thead>
                <tr>
                    <th>Nomor ROT</th>
                    <th>Operator</th>
                    <th>Tanggal Input</th>
                    <th>Aksi</th>
                </tr>
            </thead>
        </table>
    </div>

    <div class="modal fade" id="modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalMdTitle">Detail ROT</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="modalError"></div>
                    <div id="modalDocument"></div>
                </div>
            </div>
        </div>
    </div>

@stop


@section('js')

<script>
    $(document).ready(function() {
        var table =  $('#dataTables').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route('logistic.return_in.list.datatables') }}',
                "pageLength": 50,
            columns: [
                {data: 'doc_no', name: 'doc_no'},
                {data: 'operator', name: 'users.name'},
                {data: 'created_at', name: 'created_at'},
                {data: 'action', name: 'action', orderable: false, searchable: false}
            ],
        });
        $('#dataTables tbody').on('click', '.modalDoc', function(e){
            var $row = $(this).closest('tr');
            var data = table.row($row).data();
            $('#modalDocument').load($(this).attr('value'));
        });
    });
</script>

@stop