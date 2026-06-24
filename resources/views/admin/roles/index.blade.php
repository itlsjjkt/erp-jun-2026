@inject('request', 'Illuminate\Http\Request')
@extends('layouts.app')

@section('page-header')
    @lang('global.roles.title') 
@endsection

@section('content')
    <div class="mB-20">
        <a href="{{ route('admin.roles.create') }}" class="btn btn-info">@lang('global.app_add_new')</a>
    </div>

    <div class="bgc-white bd bdrs-3 p-20 mB-20">
        <div class="table-responsive">
            <table class="table table-bordered table-striped {{ count($roles) > 0 ? 'datatable' : '' }} dt-select">
                <thead>
                    <tr>
                        <th style="text-align:center;"><input type="checkbox" id="select-all" /></th>
                        <th>@lang('global.roles.fields.name')</th>
                        <th>@lang('global.roles.fields.permission')</th>
                        <th>&nbsp;</th>
                    </tr>
                </thead>
                
                <tbody>
                    @if (count($roles) > 0)
                        @foreach ($roles as $role)
                            <tr data-entry-id="{{ $role->id }}">
                                <td></td>
                                <td>{{ $role->name }}</td>
                                <td>
                                    @foreach ($role->permissions()->pluck('name') as $permission)
                                        <span class="label label-info label-many">{{ $permission }}</span>
                                    @endforeach
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('admin.roles.edit',[$role->id]) }}" class="btn btn-icon "><i class="icon-lg ti-pencil"></i></a>
                                        {!! Form::open([
                                            'class'=>'delete',
                                            'url'  => route(ADMIN . '.roles.destroy', $role->id), 
                                            'method' => 'DELETE',
                                            ]) 
                                        !!}
                                            <button class="btn text-danger  btn-icon " title="{{ trans('app.delete_title') }}" type="submit" ><i class="icon-lg ti-trash"></i></button>
                                        {!! Form::close() !!}
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="6">@lang('global.app_no_entries_in_table')</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
@stop

@section('javascript') 
    <script>
        window.route_mass_crud_entries_destroy = '{{ route('admin.roles.mass_destroy') }}';
    </script>
@endsection