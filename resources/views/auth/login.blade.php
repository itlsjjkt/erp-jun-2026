@extends('layouts.auth')

@section('content')
    <div class="text-center">
        <a class="logo" href="/"> <img src="/images/{{ config('app.logo') }}" alt=""></a>
    </div>
    <h5 class="fw-300 c-grey-900 mT-40 mb-0">Login</h5>
    <p class="text-muted mB-20 ">Masukan Username & password Anda.</p>
    <form class="form-horizontal" method="POST" action="{{ route('login') }}">
        {{ csrf_field() }}

        <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
            <label for="email" class="text-normal text-dark">Email</label>
            <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}" required autofocus>

            @if ($errors->has('email'))
                <span class="form-text text-danger">
                    <small>{{ $errors->first('email') }}</small>
                </span>
            @endif
        </div>

        <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
            <label for="password" class="text-normal text-dark">Password</label>
            <input id="password" type="password" class="form-control" name="password" required>

            @if ($errors->has('password'))
                <span class="form-text text-danger">
                    <small>{{ $errors->first('password') }}</small>
                </span>
            @endif
        </div>

        <div class="form-group">
            <div class="peers ai-c jc-sb fxw-nw">
                <div class="peer">
                    <div class="checkbox checkbox-circle checkbox-info peers ai-c">
                        <input type="checkbox" id="remember" name="remember" class="peer" {{ old('remember') ? 'checked' : '' }}>
                        <label for="remember" class=" peers peer-greed js-sb ai-c">
                            <span class="peer peer-greed">Remember Me</span>
                        </label>
                    </div>
                </div>
                <div class="peer">
                    <button class="btn btn-primary">Login</button>
                </div>
            </div>
        </div>
        <div class="peers ai-c jc-sb fxw-nw">
            <div class="peer">
                <a class="btn btn-link" href="{{ route('password.request') }}">
                    Forgot Your Password?
                </a>
            </div>
        </div>
        <div class="peers ai-c jc-sb fxw-nw mT-30">
        <small class="c-grey-900">
                Copyright &copy; 2022 {{ config('app.copyright') }} <br>All Right Reserved.
        </small>    
        </div>
    </form>

@endsection
