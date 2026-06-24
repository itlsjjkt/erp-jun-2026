<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

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
	<link href="{{ mix('/css/app.css') }}" rel="stylesheet">
	<link href="{{ asset ('/css/custom.css') }}" rel="stylesheet">

	@yield('css')

</head>

<body class="app">
    <div class="row">
        <!-- #Left Sidebar ==================== -->
        <!-- #Main ============================ -->
        <!-- ### $App Screen Content ### -->
        <div id=''>
            <div class="">
                <h4 class="c-grey-900 mT-10">@yield('page-header')</h4>
                <div aria-label="breadcrumb">@yield('breadcrumbs')</div>
                @yield('content')
            </div>
        </div>
    </div>

    <script src="{{ mix('/js/app.js') }}"></script>
    <script src="{{ asset('/js/index.js') }}"></script>

    @yield('js')

</body>

</html>
