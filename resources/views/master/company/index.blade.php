@extends('layouts.app')

@section('page-header')
    Company 
@endsection

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item active" aria-current="page">Company</li>
    </ol>
@endsection

@section('content')

    <div class="mB-20">
        <a href="{{ route('company.create') }}" class="btn btn-info">
            {{ trans('app.add_button') }}
        </a>
    </div>


    <div class="bgc-white bd bdrs-3 p-20 mB-20">
        <table id="dataTable" class="table table-striped table-bordered" cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th>Alias</th>
                    <th>Name</th>
                    <th>Actions</th>
                </tr>
            </thead>
            
            <tbody>
                @foreach ($company as $item)
                    <tr>
                         <td>{{ $item->alias }}</td>
                        <td><a href="{{ route('company.edit', Hashids::encode($item->id)) }}">{{ $item->name }}</a></td>
                        <td>
                            <div class="btn-group">
                                <a href="{{ route('company.edit', Hashids::encode($item->id)) }}" title="{{ trans('Edit') }}" class="btn btn-outline c-grey-800" data-toggle="tooltip"><span class="ti-settings icon-lg"></span></a>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        
        </table>
    </div>

@endsection