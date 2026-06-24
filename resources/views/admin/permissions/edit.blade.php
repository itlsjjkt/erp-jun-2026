@extends('layouts.app')

@section('page-header')
    @lang('global.permissions.title') <small>{{ trans('app.update_item') }}</small>
@endsection

@section('content')
    
    {!! Form::model($permission, ['method' => 'PUT', 'route' => ['admin.permissions.update', $permission->id]]) !!}

        <div class="bgc-white bd bdrs-3 p-20 mB-20">

            <div class="form-group">
                {!! Form::label('name', 'Name*', ['class' => 'control-label']) !!}
                {!! Form::text('name', old('title'), ['class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}
                <p class="help-block"></p>
                @if($errors->has('name'))
                    <p class="help-block">
                        {{ $errors->first('name') }}
                    </p>
                @endif
            </div>
            
        </div>

    {!! Form::submit(trans('global.app_update'), ['class' => 'btn btn-danger']) !!}
    {!! Form::close() !!}
@stop

