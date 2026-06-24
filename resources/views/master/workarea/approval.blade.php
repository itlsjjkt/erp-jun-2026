@extends('layouts.app')

@section('page-header')
    Work Area <small>{{ trans('app.update_item') }}</small>
@stop

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('company.index') }}">Company</a></li>
        <li class="breadcrumb-item"><a href="{{ route('workarea.index', ['id' => Hashids::encode($workarea->company_id)]) }}">Work Area</a></li>
        <li class="breadcrumb-item active" aria-current="page">Approval</li>
    </ol>
@endsection

@section('content')
   
	<div class="row mB-40">

        <div class="col-sm-12">
            <div class="bgc-white p-20 bd">
                <h6><a class="float-left" href="{{ route('workarea.index', ['id' => Hashids::encode($workarea->company_id)]) }}"> <i class="ti-arrow-left mR-10"></i></a> Kembali</h6>
                <hr class="mB-30">    
                {!! Form::open(['method' => 'POST', 'route' => ['workarea.approval.store'] ]) !!}
                    {!! Form::hidden('location_id', $workarea->id) !!}
                    {!! Form::hidden('company_id', $workarea->company_id,['id' => 'company_id']) !!}

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
                        </div>
                    </div>
            
                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label text-right">Approval</label>
                        <div class="col-sm-8">
                            <?php if (isset($approval))  { ?>
                                <table class="table table-bordered dynatable">
                                    <tr>
                                        <th style="width:75%">Approval</th>
                                        <th>Step</th>
                                        <th><a class="btn btn-info text-white btn-sm pull-right add"  data-toggle="tooltip" data-placement="top" data-original-title="Tambah Rule"><i class="ti-plus"></i></a></th>
                                    </tr>
                                    <tbody class="advancedWrapper">
                                    @if (count($approval) > 0)
                                        @php
                                            $no = 1
                                        @endphp
                                        @foreach ($approval as $key)
                                            <tr>
                                                <td>
                                                    <select name="user_id[]"  class="form-control select2 mB-5" id="users_{{$no}}" required>
                                                        <option value="{{$key->user_id}}" selected>{{$key->name}}</option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <input type="number" name="step[]" class ='form-control' value="<?php echo $key->step;?>" oninput="this.value = Math.abs(this.value)" required onwheel="return false;">
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
                                        <th style="width:75%">Approval</th>
                                        <th>Step</th>
                                        <th><a class="btn btn-primary text-white btn-sm pull-right add" data-toggle="tooltip" data-placement="top" data-original-title="Tambah Rule"><i class="ti-plus"></i></a></th>
                                    </tr>
                                    <tbody class="advancedWrapper"></tbody>
                                </table>
                            <?php } ?>
                        </div>
                    </div>
                    <hr>
                        <div class="text-right">
                            <a href="{{ route('workarea.index', ['id' => Hashids::encode($workarea->company_id)]) }}" class="btn btn-light text-uppercase fsz-sm fw-600">{{ trans('Cancel') }}</a>
                            {!! Form::submit(trans('Submit'), ['class' => 'btn btn-danger text-uppercase fsz-sm fw-600']) !!}
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
            '<td>' +
            '<select class="form-control" name="user_id[]" id="user_'+i+'" required>' +
            '</select>' +
            '</td>' + 
            '<td><input type="number" class="form-control" name="step[]" onwheel="return false;" oninput="this.value = Math.abs(this.value)" required/></td>' + 
            '<td><button class="btn btn-outline text-danger icon-lg remove" ><i class="ti-trash"></i></button></td>'+
            '</tr>'); 
            $('#user_'+i).select2({
                placeholder: 'Cari Pegawai...',
                ajax: {
                    url: '{{ route("master.get_user") }}',
                    dataType: 'json',
                    delay: 250,
                    processResults: function (data) {
                        return {
                            results:  $.map(data, function (item) {
                                return {
                                    text: item.name,
                                    id: item.id
                                }
                            })
                        };
                    },
                    cache: true
                }
            });
            
        });


        $(document).on("click", ".remove", function() {
            $(this).parents("tr").remove();
        });
    });
</script>

@if (count($approval) > 0)
    @php
        $no = 1
    @endphp
    @foreach ($approval as $item)
        <script  type='text/javascript'>
            $(document).ready(function() {
                var $item = $('#users_{{$no}}');

                $item.select2({
                        placeholder: "Silahkan pilih...",
                        ajax: {
                            url: "{{ route('master.get_user') }}",
                            dataType: 'json',
                            delay: 250,
                            processResults: function (data) {
                                return {
                                    results:  $.map(data, function (item) {
                                        return {
                                        text: item.name,
                                        id: item.id
                                        }
                                    })
                                };
                            },
                            cache: true
                        }
                }).trigger('change');

            });
        </script>
        @php
            $no++
        @endphp
    @endforeach
@endif

@stop