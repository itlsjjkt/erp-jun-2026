@extends('layouts.app')

@section('page-header')
    Warehouse Transfer In
@stop

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('logistic.inventory.index') }}">Inventory</a></li>
        <li class="breadcrumb-item"><a href="{{ route('logistic.transfer_in.index') }}">Warehouse Transfer In</a></li>
        <li class="breadcrumb-item active" aria-current="page">List Transfer </li>
    </ol>
@endsection


@section('content')
<div class="row mB-40">

	<div class="col-sm-12">
        <div class="bgc-white p-30 bd">
            <h6><a class="float-left" href="{{ route('logistic.transfer_in.index') }}"><i class="ti-arrow-left mR-10"></i></a> Kembali</h6>
            <hr class="mB-30">
            <div class="alert alert-danger">Data Warehouse Transfer Stock yang belum dilakukan penerimaan oleh Warehouse Tujuan</div>
            <table id="dataTables" class="table table-striped table-bordered" cellspacing="0" width="100%">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Operator</th>
                        <th>Tanggal Input</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
            </table>
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
                "pageLength": 50,
                ajax: '{{ route('logistic.transfer_in.add.datatables') }}',
                columns: [
                    {data: 'doc_no', name: 'doc_no'},
                    {data: 'operator', name: 'operator'},
                    {data: 'created_at', name: 'created_at', searchable: false},
                    {data: 'action', name: 'action',orderable:false, searchable: false},
                ],
                "order": [[ 3, "desc" ]]
            });

        });

        function printExternal(url) {
			var printWindow = window.open(url, 'Print', 'left=200, top=200, width=950, height=800, toolbar=0, resizable=0');
			printWindow.addEventListener('load', function() {
				printWindow.print();
			}, true);
		}
    </script>
@stop