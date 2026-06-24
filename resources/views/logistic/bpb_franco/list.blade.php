@extends('layouts.app')

@section('page-header')
    Bukti Penerimaan Barang Lokal
@stop

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('logistic.bpb_franco.index') }}">Bukti Penerimaan Barang Lokal</a></li>
        <li class="breadcrumb-item active" aria-current="page">Daftar PO</li>
    </ol>
@endsection


@section('content')
	<div class="bgc-white p-30 bd">
        <h6><a class="float-left" href="{{ route('logistic.bpb_franco.index') }}"><i class="ti-arrow-left mR-10"></i></a> {{ trans('Back') }}</h6>
        <hr class='mB-30'>
        
        <div class="alert alert-info mT-3">
           Berikut daftar PO Franco Selain Jakarta yang belum diterbitkan BPB Franco, Silahkan pilih PO yang akan dibuatkan BPB. 
        </div>
        <table class="table table-bordered mt-2" id="dataTables">
            <thead>
                <tr>
                    <th>Nomor PO</th>
                    <th>Nomor DPM</th>
                    <th>Lokasi</th>
                    <th>Project</th>
                    <th>Department</th>
                    <th>Aksi</th>
                </tr>
            </thead>
        </table>
    </div>

    <div class="modal fade" id="modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalMdTitle">Detail PO</h5>
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
            ajax: '{{ route('logistic.bpb_franco.list.datatables') }}',
                "pageLength": 50,
            columns: [
                {data: 'doc_no', name: 'doc_no'},
                {data: 'dpm_no', name: 'purchase_requisitions.dpm_no'},
                {data: 'location', name: 'locations.name',searchable: true},
                {data: 'project', name: 'projects.name'},
                {data: 'department', name: 'departments.name'},
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