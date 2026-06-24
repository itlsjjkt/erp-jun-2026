@inject('request', 'Illuminate\Http\Request')
@extends('layouts.app')

@section('page-header')
    @lang('global.permissions.title')
@endsection

@section('content')
   
    <div class="mB-20">
        <a href="{{ route('admin.permissions.create') }}" class="btn btn-info">@lang('global.app_add_new')</a>
    </div>

    <div class="bgc-white bd bdrs-3 p-20 mB-20">
        <div class="table-responsive">
            <table id="dataTables" class="table table-striped table-bordered" cellspacing="0" width="100%">
                <thead>
                    <tr>
                        <th>@lang('global.permissions.fields.name')</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            $('#dataTables').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route(ADMIN . '.permissions.datatables') }}',
                columns: [
                    {data: 'name', name: 'name'},
                    {data: 'action', name: 'action', orderable: false, searchable: false}
                ],
                "pageLength": 50
            });
        });
    </script>
@stop