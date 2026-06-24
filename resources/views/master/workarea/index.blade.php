@extends('layouts.app')

@section('page-header')
    Work Area 
@endsection

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('company.index') }}">Company</a></li>
        <li class="breadcrumb-item active" aria-current="page">Work Area</li>
    </ol>
@endsection

@section('content')

<div class="row mB-40">
 
    @include('master.menu')

    <div class="col-sm-9">
        <div class="bgc-white p-20 bd">
            <h6> Work Area</h6>
            <hr class="mB-30">

            <div class="mB-20">
                <a href="{{ route('workarea.create', ['id' => Hashids::encode($company->id)]) }}" class="btn btn-info">
                    {{ trans('app.add_button') }}
                </a>
            </div>


            <div class="bgc-white bd bdrs-3 p-20 mB-20">
                <table id="dataTables" class="table table-striped table-bordered" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Alias</th>
                            <th>Area</th>
                            <th>Updated</th>
                            <th>Status</th>
                            <th>isDPM</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
               
                </table>
            </div>
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
            ajax: '{{ route('workarea.datatables', $company->id) }}',
            columns: [
                {data: 'name', name: 'name'},
                {data: 'alias', name: 'alias'},
                {data: 'area', name: 'areas.name'},
                {data: 'updated_at', name: 'updated_at', searchable: false},
                {data: 'status', name: 'status', searchable: false},
                {data: 'isDPM', name: 'isDPM', searchable: false},
                {data: 'action', name: 'action', orderable: false, searchable: false}
            ]
        });
    });
</script>
@stop