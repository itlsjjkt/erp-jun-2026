<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>
	{{-- TRIX --}}
    <link rel="stylesheet" type="text/css" href="https://unpkg.com/trix@2.0.8/dist/trix.css">
    <script type="text/javascript" src="https://unpkg.com/trix@2.0.8/dist/trix.umd.min.js"></script>
    <style>
      trix-toolbar [data-trix-button-group]{
        display: none;
      }
    </style>
    <!-- Styles -->
    <link rel="icon" href="{!! asset('images/favicon.ico') !!}"/>
    <link href="{{ asset('/css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('/css/custom.css') }}" rel="stylesheet">
  </head>
  <body class="app">

      @include('layouts.partials.spinner')

      <div class="peers ai-s fxw-nw h-100vh">
        <div class="d-n@sm- peer peer-greed  pos-r"  style="background-image:url('/images/bg.png');background-size:cover"></div>
        <div class="col-12 col-md-4 peer pX-60 pY-150 h-100 bgc-white scrollable pos-r" style='min-width: 320px;'>
          @yield('content')
        </div>
      </div>

  </body>
</html>
