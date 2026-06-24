@extends('layouts.app')

@section('page-header')
    Currency
@endsection

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('purchasing.currency.index') }}">Currency</a></li>
        <li class="breadcrumb-item active" aria-current="page">Create</li>
    </ol>
@endsection

@section('content')

	<div class="row mB-40">
   
        @include('purchase.master.menu')

        <div class="col-sm-9">
            <div class="bgc-white p-20 bd">
                
                <h6><a class="float-left" href="{{ route('purchasing.currency.index') }}"><i class="ti-arrow-left mR-10"></i></a> Currency</h6>
                <hr class="mB-30">

                {!! Form::open(['method' => 'POST', 'route' => ['purchasing.currency.store']]) !!}

                   @include('purchase.master.currency.form')

                {!! Form::close() !!}       
            </div>  
        </div>
    </div>
        
@endsection

@section('js')
    <script>
        $(document).ready(function() {
            if($('#status').val()=='1'){
                $('#status').attr('checked','checked').iCheck('update');
            }
            $('#status').on('ifChecked', function(){
                $("#status" ).attr('value', '1');
            });
            $('#status').on('ifUnchecked', function(){
                $("#status" ).attr('value', '0');
            });
        });
    </script>
@stop