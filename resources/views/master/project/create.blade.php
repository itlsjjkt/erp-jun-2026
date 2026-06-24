@extends('layouts.app')

@section('page-header')
    Project 
@endsection

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('master.project.index') }}">Project</a></li>
        <li class="breadcrumb-item active" aria-current="page">Create</li>
    </ol>
@endsection

@section('content')

	<div class="row mB-40">

        <div class="col-sm-12">
            <div class="bgc-white p-20 bd">
                
                <h6><a class="float-left" href="{{ route('master.project.index') }}"><i class="ti-arrow-left mR-10"></i></a> Project</h6>
                <hr class="mB-30">

                {!! Form::open(['method' => 'POST', 'route' => ['master.project.store']]) !!}
                    
                    @include ('master.project.form');
               
                {!! Form::close() !!}       
            </div>  
        </div>
    </div>
        
@endsection