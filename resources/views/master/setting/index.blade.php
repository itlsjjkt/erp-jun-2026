@extends('layouts.app')

@section('page-header')
    Setting
@endsection

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item" aria-current="page">Master Logistik</li>
        <li class="breadcrumb-item active" aria-current="page">Setting</li>
    </ol>
@endsection

@section('content')
    <div class="row mB-40">
    
     <div class="col-sm-12">
        <div class="bgc-white bd bdrs-3 p-20 mB-20">
             @include('master.menulogistik')
            <h6>Setting</h6>
            <hr class="mB-30">

            <ul class="nav nav-tabs" id="myTab" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" >SPB OPERATOR</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" >ASURANSI</a>
                </li>
            </ul>

            {!! Form::open(['method' => 'POST', 'route' => ['master.setting.spb.store'],'files' => true ]) !!}

                <div class="form-group row mt-5">
                    <label class="col-sm-2 col-form-label text-right">Operator</label>
                    <div class="col-sm-9">
                        <?php if (isset($operator))  { ?>
                            <table class="table table-bordered dynatable">
                                <tr>
                                    <th>Nama</th>
                                    <th style="width:300px">TTD</th>
                                    <th style="width:80px"><a class="btn btn-info text-white btn-sm pull-right addOperator"  data-toggle="tooltip" data-placement="top" data-original-title="Tambah"><i class="ti-plus"></i></a></th>
                                </tr>
                                <tbody class="wrapperOperator">
                                    <?php 

                                    ?>
                                    @php
                                        $no = 1
                                    @endphp
                                    @foreach ($operator as $val)
                                        <tr>
                                            <td>
                                                <input type="text" name="name[]" class ='form-control' value="<?php echo $val->name;?>"  required>
                                            </td>
                                            <td>
                                                <img src="{{ asset('storage'.$val->sign) }}" id="profile-img-exits" class="img-fluid img-thumbnail" style="width:100px">
                                                <input type="hidden" name="sign_exist[]" class ='form-control' value="<?php echo $val->sign;?>"  required>
                                                <input type="file" name="sign[]" class ='form-control'>
                                            </td>
                                            <td>
                                                <button class="btn btn-outline icon-lg text-danger removeOperator" ><i class="ti-trash"></i></a>
                                            </td>
                                        </tr>
                                        @php
                                            $no++
                                        @endphp
                                    @endforeach
                                </tbody>
                            </table>
                        <?php }else { ?>
                            <table class="table table-bordered dynatable">
                                <tr>
                                    <th>Nama</th>
                                    <th>TTD</th>
                                    <th style="width:80px"><a class="btn btn-primary text-white btn-sm pull-right addOperator" data-toggle="tooltip" data-placement="top" data-original-title="Tambah"><i class="ti-plus"></i></a></th>
                                </tr>
                                <tbody class="wrapperOperator"></tbody>
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

@endsection

@section('js')
    <script>

        $(document).ready(function() {
            
            var wrapper = $(".wrapperOperator");
            var i = 0;
            $(document).on("click", ".addOperator", function(e) {
                e.preventDefault();
                i++;
                $(wrapper).append('<tr>' + 
                '<td><input type="text" class="form-control" value="" name="name[]"  required/></td>' + 
                '<td><input type="file" class="form-control" value="" name="sign[]"  required/><input type="hidden" class="form-control" value="new" name="sign_exist[]"/></td>' + 
                '<td><button class="btn btn-outline text-danger icon-lg removeOperator" ><i class="ti-trash"></i></button></td>'+
                '</tr>'); 

                
            });

            $(document).on("click", ".removeOperator", function() {
                $(this).parents("tr").remove();
            });

           
            $("#profile-img").change(function(){
                readURL(this);
                $("#profile-img-exits").hide();
            });
       
    });
</script>
@stop