@extends('layouts.app')

@section('page-header')
    Warehouse Transfer In
@endsection

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('logistic.inventory.index') }}">Inventory</a></li>
        <li class="breadcrumb-item"><a href="{{ route('logistic.transfer_in.index') }}">Warehouse Transfer In </a></li>
        <li class="breadcrumb-item active" aria-current="page">Search</li>
    </ol>
@endsection

@section('content')

    <div class="bgc-white bd bdrs-3 p-20 mB-20">
        <div class="row">
            <div class="col-sm-6 ">
                <a href="{{ route('logistic.transfer_in.index') }}" class="nav-link" >
                    <i class="ti-arrow-left"></i> Kembali
                </a>
            </div>
            <div class="col-sm-6">
                <a href="#" data-toggle="collapse" data-target="#export" class="btn btn-outline border-dark float-right">
                    FILTER | EXPORT DATA
                </a>
            </div>
        </div>

        <hr>

        <div class="collapse mB-20" id="export" aria-expanded="false">
            <form class="form-horizontal" id="form" method='GET'>
                {{ csrf_field() }}
                <div class="bgc-white bd bdrs-3 p-20">
                    <h6>FILTER | EXPORT DATA</h6>
                    <hr>
                    <div class="row">
                        <div class="col-sm-3">
                            <label>Lokasi</label>
                            @if(isAdministrator() || isAdministratorCompany() || isAdmin())
                                {!! Form::select('location_id', $location, $data['location_id'], ['class' => 'form-control select2']) !!}
                            @elseif(isAdministratorLocation())
                                <input type="text" readonly class="form-control" value="{{ getDataByID('locations',Auth::user()->location_id )->name }}">
                                <input type="hidden" name="location_id" id="location_id" value="{{ Auth::user()->location_id }}">
                            @else
                                <input type="text" readonly class="form-control" value="{{ getDataByID('locations',Auth::user()->location_id)->name }}">
                                <input type="hidden" name="location_id" id="location_id" value="{{Auth::user()->location_id}}">
                            @endif
                        </div>
                        <div class="col-sm-3">
                            <label>Type</label>
                            {!! Form::select('type', $type, $data['type'], ['class' => 'form-control select2']) !!}
                        </div>
                        <div class="col-sm-4">
                            <label>Tanggal Input</label>
                            <div class="input-group w-100">
                                <input type="text" name="start_date" class="form-control datepicker m-r-n-1" placeholder="mm/dd/yyyy" value="{{ $data['start_date'] }}">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">ke</div>
                                </div>
                                <input type="text" name="end_date"  class="form-control datepicker" placeholder="mm/dd/yyyy" value="{{ $data['end_date'] }}">
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


        <p>{!! $search !!}</p>
        <table id="dataTables" class="table table-striped table-bordered" cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th>No WTI</th>
                    <th>Tanggal WTI</th>
                    <th>No WTO</th>
                    <th>Tanggal WTO</th>
                    <th>Type</th>
                    <th>Penerima</th>
                    <th>Status</th>
                    <th>Aksi</th>
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
                "pageLength": 50,
                ajax: '{{ route('logistic.transfer_in.datatables',$query) }}',
                columns: [
                    {data: 'doc_no', name: 'doc_no'},
                    {data: 'created_at', name: 'created_at', searchable: false},
                    {data: 'doc_no_wto', name: 'inventory_transfer_out.doc_no'},
                    {data: 'created_at_wto', name: 'inventory_transfer_out.created_at', searchable: false},
                    {data: 'type', name: 'type'},
                    {data: 'received', name: 'received'},
                    {data: 'status', name: 'status'},
                    {data: 'action', name: 'action',orderable:false, searchable: false},
                ],
                "order": [[ 2, "desc" ]]
            });


            $('#btn-filter').click( function() {
                $('form#form').attr('action',"{{ route('logistic.transfer_in.search') }}");
                if($('form#form').valid()){
                    $('form#form').submit();
                }
            });

            $('#btn-export').click( function(e) {
                e.preventDefault();
                $('form#form').attr('action',"{{ route('logistic.transfer_in.export') }}");
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

