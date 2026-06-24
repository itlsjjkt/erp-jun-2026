@extends('layouts.app')

@section('page-header')
    Tanda Terima Barang (TTB)
@endsection

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('logistic.inventory.index') }}">Inventory</a></li>
        <li class="breadcrumb-item"><a href="{{ route('logistic.ttb.index') }}">Tanda Terima Barang (TTB) </a></li>
        <li class="breadcrumb-item active" aria-current="page">Search</li>
    </ol>
@endsection

@section('content')

    <div class="bgc-white bd bdrs-3 p-20 mB-20">
        <div class="row">
            <div class="col-sm-6 ">
                <h6><a class="float-left" href="{{ route('logistic.ttb.index') }}"><i class="ti-arrow-left mR-10"></i></a> {{ trans('Back') }}</h6>
            </div>
            <div class="col-sm-6">
                <a href="#" data-toggle="collapse" data-target="#export" class="btn btn-outline border-dark float-right">
                    FILTER | EXPORT DATA
               </a>
            </div>
        </div>

        <hr>

        <div class="collapse mB-20" id="export" aria-expanded="false">
            <form class="form-horizontal" id="form">
                {{ csrf_field() }}
                <div class="bgc-white bd bdrs-3 p-20">
                    <h6>Filter | Export Data</h6>
                    <hr>
                    <div class="row">
                        <div class="col-sm-3">
                            <label>Lokasi</label>
                            @if(isAdministrator() || isAdministratorCompany() || isAdmin())
                                {!! Form::select('location_id', $location, $data['location_id'], ['class' => 'form-control select2', 'id'=>'location_id']) !!}
                            @elseif(isAdministratorLocation())
                                <input type="text" readonly class="form-control" value="{{ getDataByID('locations',Auth::user()->location_id )->name }}">
                                <input type="hidden" name="location_id" id="location_id" value="{{ Auth::user()->location_id }}">
                            @else
                                <input type="text" readonly class="form-control" value="{{ getDataByID('locations',Auth::user()->location_id)->name }}">
                                <input type="hidden" name="location_id" id="location_id" value="{{Auth::user()->location_id}}">
                            @endif
                        </div>
                        <div class="col-sm-3">
                            <label>Project</label>
                            {!! Form::select('project_id', $project, $data['project_id'], ['class' => 'form-control select2']) !!}
                        </div>
                        <div class="col-sm-3">
                            <label>Kapal/Departemen</label>
                            {!! Form::select('department_id', $department, $data['department_id'], ['class' => 'form-control select2']) !!}
                        </div>
                        <div class="col-sm-3">
                            <label>Periode</label>
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
                    <th>No.</th>
                    <th>Operator</th>
                    <th>Penerima</th>
                    <th>Project</th>
                    <th>Departemen/Kapal</th>
                    <th>Tanggal Input</th>
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
                ajax: "{{ route('logistic.ttb.datatables',$query) }}",
                columns: [
                    {data: 'doc_no', name: 'doc_no'},
                    {data: 'operator', name: 'operator'},
                    {data: 'received', name: 'received'},
                    {data: 'project', name: 'projects.name'},
                    {data: 'department', name: 'departments.name'},
                    {data: 'created_at', name: 'created_at', searchable: false},
                    {data: 'status', name: 'status', orderable:false,searchable: false},
                    {data: 'action', name: 'action',orderable:false, searchable: false},
                ],
                "order": [[ 5, "desc" ]]
            });


            $('#btn-filter').click( function() {
                $('form#form').attr('action',"{{ route('logistic.ttb.search') }}");
                if($('form#form').valid()){
                    $('form#form').submit();
                }
            });

            $('#btn-export').click( function(e) {
                e.preventDefault();
                $('form#form').attr('action',"{{ route('logistic.ttb.export') }}");
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