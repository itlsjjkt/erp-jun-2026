@extends('layouts.app')

@section('page-header')
    Supplier 
@endsection

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item active" aria-current="page">Supplier</li>
    </ol>
@endsection

@section('content')

<div class="row mB-40">
 
    <div class="col-sm-12">
        <div class="bgc-white p-20 bd">

            <div class="mB-20">
				@if($type_user == 4 && $datAcc_user == 1 || $idSuper ==1)
					<a href="{{ route('purchasing.suppliers.create') }}" class="btn btn-info">
						Tambah Data
					</a>
				@endif
                <a href="{{ route('purchasing.suppliers.export') }}"   class="btn btn-outline border-dark float-right">
                    <i class="fa fa-file-excel-o text-success icon-lg"></i> Export Data
                </a>
                @if(isAdministrator())
                    <a href="{{ route('purchasing.suppliers.import') }}" class="btn btn-outline border-dark float-right text-uppercase fsz-sm mr-2 fw-600">
                        <i class="fa fa-file-excel-o text-danger icon-lg"></i> Upload
                    </a>
                @endif
            </div>
			<br>

            <div class="bgc-white bd bdrs-3 p-20 mB-20">
                <table id="dataTables" class="table table-striped table-bordered" cellspacing="0" width="100%">
                    <thead>
                        <tr>
							<th>Name</th>
							<th style="max-width: 400px !important">Category</th>
							<th style="max-width: 120px !important">Status</th>
							<th style="max-width: 100px !important">Blacklist</th>
							<th style="max-width: 100px !important">Updated</th>
							<th style="max-width: 100px !important">Action</th>
                        </tr>
                    </thead>
               
                </table>
            </div>
        </div>
    </div>
</div>
<!-- Delete Modal -->
<div class="modal fade" tabindex="-1" role="dialog" id="modalBlock">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
        <form id="blockForm" action="" method="post">
        @csrf
            <div class="modal-header">
                <h5 class="modal-title">Alasan Blacklist</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <textarea name="block_reason" class="form-control"></textarea>
            </div>
            <div class="modal-footer">
                <input type="hidden" name="id" id="supplier_id">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-danger">Submit</button>
            </div>
        </form>
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
            "pageLength": 50,
            ajax: '{{ route('purchasing.suppliers.datatables') }}',
            columns: [
                {data: 'name', name: 'name'},
                {data: 'all_category_ids', name: 'all_category_ids'},
                {data: 'status', name: 'status',  orderable: false, searchable: false},
                {data: 'block', name: 'status',  orderable: false, searchable: false},
                {data: 'updated_at', name: 'updated_at', searchable: false},
                {data: 'action', name: 'action', orderable: false, searchable: false}
            ],
            "order": [[ 0, "asc" ]]
        });
        $('#modalBlock').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var id = button.data('id');
            $('#blockForm').attr("action", "{{ route('purchasing.suppliers.blacklist') }}");
            $('#supplier_id').attr("value",id);
		});
	});
</script>
@stop