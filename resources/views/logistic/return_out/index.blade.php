@extends('layouts.app')

@section('page-header')
    Return Out
@stop

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('logistic.inventory.index') }}">Inventory</a></li>
        <li class="breadcrumb-item active" aria-current="page">Return Out</li>
    </ol>
@endsection


@section('content')
<div class="row mB-40">

	<div class="col-sm-12">
        <div class="bgc-white p-30 bd">

            <div class="row">
                <div class="col">
                    <a href="#" data-toggle="collapse" data-target="#filter" class="btn btn-outline border-dark float-right">
                        FILTER | EXPORT DATA
                    </a>
                </div>
            </div>
            
            <hr class="mB-30">
            <div class="collapse mB-20" id="filter" aria-expanded="false">
                <form class="form-horizontal" id="form" method='GET'>
                    {{ csrf_field() }}
                    <div class="bgc-white bd bdrs-3 p-20">
                        <h6>Form Filter | Export Data </h6>
                        <hr>
                        <div class="form-group row">
                            <div class="col-sm-3">
                                <label>Lokasi</label>
                                @if(isAdministrator() || isAdministratorCompany() || isAdmin())
                                    {!! Form::select('location_id', $location, old('location_id'), ['class' => 'form-control select2']) !!}
                                @elseif(isAdministratorLocation())
                                    <input type="text" readonly class="form-control" value="{{ getDataByID('locations',Auth::user()->location_id )->name }}">
                                    <input type="hidden" name="location_id" id="location_id" value="{{ Auth::user()->location_id }}">
                                @else
                                    <input type="text" readonly class="form-control" value="{{ getDataByID('locations',Auth::user()->location_id)->name }}">
                                    <input type="hidden" name="location_id" id="location_id" value="{{Auth::user()->location_id}}">
                                @endif
                            </div>
                            <div class="col-sm-4">
                                <label>Periode</label>
                                <div class="input-group w-100">
                                    <input name="start_date" type="text" class="form-control datepicker m-r-n-1" placeholder="mm/dd/yyyy" value="{{ date('m/d/Y') }}">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text">ke</div>
                                    </div>
                                    <input type="text" name="end_date" class="form-control datepicker" placeholder="mm/dd/yyyy" value="{{ date('m/d/Y') }}">
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <button type="submit" class="btn btn-info mt-4" id="btn-filter">FILTER</button>
                                <button type="submit" class="btn btn-success mt-4" id="btn-export">EXPORT</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>  


            <div class="alert alert-info">Pembuatan Return Out dilakukan di Menu Inventory</div>
            <table id="dataTables" class="table table-striped table-bordered" cellspacing="0" width="100%">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Operator</th>
                        <th>Tanggal Input</th>
                        <th>Status</th>
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
                ajax: '{{ route('logistic.return_out.datatables') }}',
                columns: [
                    {data: 'doc_no', name: 'doc_no'},
                    {data: 'operator', name: 'operator'},
                    {data: 'created_at', name: 'created_at', searchable: false},
                    {data: 'status', name: 'status', orderable:false,searchable: false},
                    {data: 'action', name: 'action',orderable:false, searchable: false},
                ],
                "order": [[ 3, "desc" ]]
            });

            $('#btn-filter').click( function() {
                $('form#form').attr('action',"{{ route('logistic.return_out.search') }}");
                if($('form#form').valid()){
                    $('form#form').submit();
                }
            });

            $('#btn-export').click( function(e) {
                e.preventDefault();
                $('form#form').attr('action',"{{ route('logistic.return_out.export') }}");
                if($('form#form').valid()){
                    $('form#form').submit();
                }
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