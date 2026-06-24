@extends('layouts.app')

@section('page-header')
     Monitoring DPM Pending
@endsection

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item active" aria-current="page"> Monitoring DPM Pending</li>
    </ol>
@endsection

@section('content')
<div class="bgc-white bd bdrs-3 p-20 mB-20">
    <div class="alert alert-warning">Monitoring DPM Pending digunakan untuk memonitoring DPM yang belum tuntas hingga BPB</div>
    <table id="dataTables" class="table table-striped table-bordered" cellspacing="0" width="100%">
        <thead>
            <tr style="background-color: rgb(226, 226, 226);">
                <th>No DPM</th>
                <th style="max-width: 18%;">Kapal/Departement</th>
                <th style="max-width: 18%;">Project</th>
                <th style="max-width: 18%;">Dibuat Oleh</th>
                <th style="max-width: 18%;">Tgl Input</th>
                <th style="max-width: 5%;">Aksi</th>
            </tr>
        </thead>
    
    </table>

</div>



@endsection


@section('js')
    <script>
    $(document).ready(function() {
        $('#dataTables').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route('logistic.monitoring.dpm.datatables_pending') }}',
            "pageLength": 50,
            columns: [
                {data: 'doc_no', name: 'purchases.doc_no'},
                {data: 'kd', name: 'departments.name'},
                {data: 'project', name: 'projects.name'},
                {data: 'created', name: 'users.name'},
                {data: 'created_at', name: 'purchases.created_at',searchable: false},
                {data: 'action', name: 'action', orderable: false, searchable: false}
            ],
            "order": [[ 4, "asc" ]]
        });
    });
</script>
@stop