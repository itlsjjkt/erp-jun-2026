@extends('layouts.app')

@section('page-header')
    Email SPB
@stop

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('master.email_spb.index') }}">Email SPB</a></li>
        <li class="breadcrumb-item active" aria-current="page">Manage</li>
    </ol>
@stop

@section('content')
   
	<div class="row mB-40">

        <div class="col-sm-12">
            <div class="bgc-white p-20 bd">
                @include('master.menulogistik')
                <h6> Form Email CC SPB </h6>
                <hr class="mB-30">    
                {!! Form::open(['method' => 'POST', 'route' => ['master.email_spb.config'] ]) !!}
                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label text-right">Company Name </label>
                        <div class="col-sm-4">
                            {!! Form::text('name', $company->name, ['class' => 'form-control', 'placeholder' => '', 'required' => '','readonly' => '']) !!}
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label text-right">Location</label>
                        <div class="col-sm-4">
                            {!! Form::text('name', $workarea->name, ['class' => 'form-control', 'placeholder' => '', 'required' => '','readonly' => '']) !!}
                            <input type="hidden" name="location_id" class ='form-control' value="<?php echo $location_id;?>">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label text-right">Email</label>
                        <div class="col-sm-8">
                            <?php if (isset($email))  { ?>
                                <table class="table table-bordered dynatable">
                                    <tr>
                                        <th>Email Address</th>
                                        <th style="width:80px"><a class="btn btn-info text-white btn-sm pull-right add"  data-toggle="tooltip" data-placement="top" data-original-title="Tambah"><i class="ti-plus"></i></a></th>
                                    </tr>
                                    <tbody class="advancedWrapper">
                                    @if (count($email) > 0)
                                        @php
                                            $no = 1
                                        @endphp
                                        @foreach ($email as $key)
                                            <tr>
                                                <td>
                                                     <input type="email" name="email[]" class ='form-control' value="<?php echo $key->email;?>">
                                                </td>
                                                <td>
                                                    <button class="btn btn-outline icon-lg text-danger remove" ><i class="ti-trash"></i></a>
                                                </td>
                                            </tr>
                                            @php
                                                $no++
                                            @endphp
                                        @endforeach
                                    @endif
                                    </tbody>
                                </table>
                            <?php }else { ?>
                                <table class="table table-bordered dynatable">
                                    <tr>
                                        <th>Email Address</th>
                                        <th style="width:80px"><a class="btn btn-primary text-white btn-sm pull-right add" data-toggle="tooltip" data-placement="top" data-original-title="Tambah"><i class="ti-plus"></i></a></th>
                                    </tr>
                                    <tbody class="advancedWrapper"></tbody>
                                </table>
                            <?php } ?>
                        </div>
                    </div>
                    <hr>
                    <div class="text-right">
                        {!! Form::submit(trans('Submit'), ['class' => 'btn btn-danger']) !!}
                    </div>
                {!! Form::close() !!}
            </div>  
        </div>
    </div>
	
@stop

@section('js')

<script  type='text/javascript'>
	$(document).ready(function() {
        var wrapper = $(".advancedWrapper");
        var i = 0;
        $(document).on("click", ".add", function(e) {
            e.preventDefault();
            i++;
            $(wrapper).append('<tr>' + 
            '<td><input type="email" class="form-control" value="" name="email[]"/></td>' + 
            '<td><button class="btn btn-outline text-danger icon-lg remove" ><i class="ti-trash"></i></button></td>'+
            '</tr>'); 
        });

        $(document).on("click", ".remove", function() {
            $(this).parents("tr").remove();
        });
    });
</script>

@stop