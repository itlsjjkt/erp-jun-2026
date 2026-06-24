@extends('layouts.app')

@section('page-header')
    @lang('global.roles.title') <small>{{ trans('app.add_new_item') }}</small>
@endsection

@section('content')
    {!! Form::open(['method' => 'POST', 'route' => ['admin.roles.store']]) !!}

        
        <div class="bgc-white bd bdrs-3 p-20 mB-20">
            <div class="form-group">
                {!! Form::label('name', 'Name*', ['class' => 'control-label']) !!}
                {!! Form::text('name', old('name'), ['class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}
                <p class="help-block"></p>
                @if($errors->has('name'))
                    <p class="help-block">
                        {{ $errors->first('name') }}
                    </p>
                @endif
            </div>
            <div class="form-group">
                {!! Form::label('permission', 'Permissions', ['class' => 'control-label']) !!}
                {!! Form::select('permission[]', $permissions, old('permission'), ['class' => 'form-control select2', 'multiple' => 'multiple']) !!}
                <p class="help-block"></p>
                @if($errors->has('permission'))
                    <p class="help-block">
                        {{ $errors->first('permission') }}
                    </p>
                @endif
            </div>
            
        </div>

    {!! Form::submit(trans('Submit'), ['class' => 'btn btn-danger']) !!}
    {!! Form::close() !!}
@stop

