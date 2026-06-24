@extends('layouts.app')

@section('page-header')
Laporan Penerimaan Barang 
@stop

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('logistic.lpb.index') }}">Laporan Penerimaan Barang</a></li>
        <li class="breadcrumb-item active" aria-current="page">Daftar PO</li>
    </ol>
@endsection


@section('content')
	<div class="bgc-white p-30 bd">
        <h6 class='mT-10'>Daftar Purchase Order (PO)</h6>
        <hr>
        <div class="alert alert-info mT-3">
           Berikut daftar PO yang belum diterbitkan LPB, Silahkan pilih PO yang akan diterbitkan menjadi LPB. 
        </div>
        <div class="table-responsive">
            <table class="table table-bordered mt-2" style="width:99%" id="dataTables">
                <thead class="item_form">
                    <tr>
                        <th>Nomor PO</th>
                        <th>Nomor PR</th>
                        <th>Nomor DPM</th>
                        <th>Department</th>
                        <th>Franco/Loco</th>
                        <th>Tanggal Input</th>
                        <th>Status</th>
                        <th style="width:150px;">Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Information</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <ul>
                    <li>
                        <h4>
                            Silahkan hubungi pic sunda kelapa untuk melakukan pembuatan dokumen LPB untuk dokumen PO "Franco Jakarta"
                        </h4>
                    </li>
                </ul>
            </div>
            </div>
        </div>
    </div>
   
@stop


@section('js')

<script>
    $(document).ready(function() {
        $('#dataTables').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route('logistic.lpb.list.datatables') }}',
                "pageLength": 50,
            columns: [
                {data: 'doc_no', name: 'doc_no'},
                {data: 'pr_no', name: 'purchase_requisitions.doc_no'},
                {data: 'dpm_no', name: 'purchase_requisitions.dpm_no'},
                {data: 'department', name: 'departments.name'},
                {data: 'price_term_location', name: 'po.price_term_location'},
                {data: 'updated_at', name: 'updated_at'},
                {data: 'status', name: 'status'},
                {data: 'action', name: 'action', orderable: false, searchable: false}
            ],
            "order": [[ 5, "desc" ]]

        });
    });
</script>

@stop
