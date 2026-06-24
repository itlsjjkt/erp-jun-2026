@extends('layouts.app')

@section('page-header')
    Bukti Penerimaan Barang
@stop

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('logistic.bpb.index') }}">Bukti Penerimaan Barang</a></li>
        <li class="breadcrumb-item active" aria-current="page">Daftar SPB</li>
    </ol>
@endsection


@section('content')
	<div class="bgc-white p-30 bd">
        <h6 class='mT-10'>List Surat Pengantar Barang</h6>
        <hr>
        <div class="alert alert-info mT-3">
           Berikut daftar SPB yang belum diterbitkan BPB, Silahkan pilih SPB yang akan dibuatkan BPB. 
        </div>
        <table class="table table-bordered mt-2" id="dataTables">
            <thead>
                <tr>
                    <th>Nomor SPB</th>
                    <th>Jenis SPB</th>
                    <th>Operator</th>
                    <th>PIC Penerima</th>
                    <th>Aksi</th>
                </tr>
            </thead>
        </table>
    </div>

    <div class="modal fade" id="modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalMdTitle">Detail SPB</h5>
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
            ajax: '{{ route('logistic.bpb.list.datatables') }}',
                "pageLength": 50,
            columns: [
                {data: 'doc_no', name: 'doc_no'},
                {data: 'type', name: 'type'},
                {data: 'operator', name: 'operator'},
                {data: 'received_pic', name: 'received_pic'},
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