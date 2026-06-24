@extends('layouts.app')

@section('page-header')
    Change Password 
@endsection

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('profile') }}">Profile</a></li>
        <li class="breadcrumb-item active" aria-current="page">Change Password</li>
    </ol>
@endsection

@section('content')
    <form class="form-horizontal" method="POST" action="{{ route('profile.change_password') }}">
        {{ csrf_field() }}

        <div class="bgc-white bd bdrs-3 p-20 mB-20">
            <h6>Form Change Password</h6>
            <hr class="mB-30">

            <div class="form-group{{ $errors->has('current-password') ? ' has-error' : '' }} row">
                <label for="new-password" class="col-md-4 col-form-label">Current Password</label>

                <div class="col-md-4">
                    <input id="current-password" type="password" class="form-control" name="current-password" required>

                    @if ($errors->has('current-password'))
                        <span class="help-block">
                            {{ $errors->first('current-password') }}
                        </span>
                    @endif
                </div>
            </div>

            <div class="form-group{{ $errors->has('new-password') ? ' has-error' : '' }} row">
                <label for="new-password" class="col-md-4 col-form-label">New Password</label>

                <div class="col-md-4">
                    <input id="new-password" type="password" class="form-control" name="new-password" required>

                    @if ($errors->has('new-password'))
                        <span class="help-block">
                            {{ $errors->first('new-password') }}
                        </span>
                    @endif
                </div>
            </div>

            <div class="form-group row">
                <label for="new-password-confirm" class="col-md-4 col-form-label">Confirm New Password</label>

                <div class="col-md-4">
                    <input id="new-password-confirm" type="password" class="form-control" name="new-password_confirmation" required>
                </div>
            </div>
        </div>

        
        <button type="submit" class="btn btn-primary">
            Change Password
        </button>
    </form>
@endsection