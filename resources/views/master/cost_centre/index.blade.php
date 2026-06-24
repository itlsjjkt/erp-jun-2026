@extends('layouts.app')

@section('page-header')
    Cost Centre 
@endsection

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('company.index') }}">Company</a></li>
        <li class="breadcrumb-item active" aria-current="page">Cost Centre</li>
    </ol>
@endsection

@section('content')

<div class="row mB-40">

	@include('master.menu')

    <div class="col-sm-9">
        <div class="bgc-white p-20 bd">
            <h6> Cost Centre</h6>
            <hr class="mB-30">

            <div class="mB-20">
                <a href="{{ route('cost_centre.create', ['id' => Hashids::encode($company->id)]) }}" class="btn btn-info">
                    {{ trans('app.add_button') }}
                </a>
                <a  href="{{ route('cost_centre.export',['id' => Hashids::encode($company->id)]) }}" class="btn btn-outline border-dark float-right text-uppercase fsz-sm fw-600" aria-controls="export">
                    <i class="fa fa-file-excel-o text-success icon-lg"></i> Export
                </a>
                @if(isAdministrator() || isAdministratorCompany())
                    <a href="{{ route('cost_centre.upload',['id' => Hashids::encode($company->id)]) }}" class="btn btn-outline border-dark float-right text-uppercase fsz-sm mr-2 fw-600">
                        <i class="fa fa-file-excel-o text-danger icon-lg"></i> Upload
                    </a>
                @endif
            </div>


            <div class="bgc-white bd bdrs-3 p-20 mB-20">
                <table id="dataTables" class="table table-striped table-bordered" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Code</th>
                            <th>Status</th>
                            <th>Updated</th>
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
            pageLength: 25,
            processing: true,
            serverSide: true,
            ajax: '{{ route('cost_centre.datatables', $company->id) }}',
            columns: [
                {data: 'name', name: 'name'},
                {data: 'code', name: 'code'},
                {data: 'status', name: 'status', searchable: false},
                {data: 'updated_at', name: 'updated_at', searchable: false},
                {data: 'action', name: 'action', orderable: false, searchable: false}
            ]
        });
    });
</script>
@stop