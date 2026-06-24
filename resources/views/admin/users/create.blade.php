@extends('layouts.app')

@section('page-header')
	User 
@stop

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">User Management</a></li>
        <li class="breadcrumb-item active" aria-current="page">Tambah</li>
    </ol>
@endsection

@section('content')
  
	
        <div class="bgc-white p-30 bd">
            <h6>Add User</h6>
			<hr>
            {!! Form::open([
                'method' => 'POST', 
                'route' => ['admin.users.store'],
                'files' => true
            ]) !!}


            <div class="alert alert-info">
                <b>INFORMASI</b> <br> 
                - Default Password: 123456, Silahkan instruksikan user untuk mengganti password setelah akun user dibuat. <br>
                - Pembuatan User untuk pegawai dilakukan di menu Data Pegawai
            </div>

            <div class="form-group row  mt-5">
                <label class="col-sm-3 col-form-label text-right">Level Data Akses<span class="text-danger">*</span></label>
                <div class="col-sm-3">
                    {!! Form::select('type', $type, old('type'), ['class' => 'form-control select2', 'required' => '','id' => 'type']) !!}
                    <p class="help-block"></p>
                    @if($errors->has('type'))
                    <p class="help-block">
                        {{ $errors->first('type') }}
                    </p>
                    @endif
                </div>
            </div>

            <div class="form-group row">
                {!! Form::label('name', 'Data User', ['class' => 'col-form-label col-sm-3']) !!}
                <div class="col-sm-3">
                    <label>Nama<span class="text-danger">*</span></label>
                    {!! Form::text('name', old('name'), ['class' => 'form-control', 'placeholder' => '', 'required' => '','id' => 'name']) !!}
                    <p class="help-block"></p>
                    @if($errors->has('name'))
                        <p class="help-block">
                            {{ $errors->first('name') }}
                        </p>
                    @endif
                </div>
                <div class="col-sm-3">
                    <label>Email<span class="text-danger">*</span></label>
                    {!! Form::email('email', old('email'), ['class' => 'form-control', 'placeholder' => '', 'required' => '','id' => 'email']) !!}
                    <p class="help-block"></p>
                    @if($errors->has('email'))
                        <p class="help-block">
                            {{ $errors->first('email') }}
                        </p>
                    @endif
                </div>
            </div>

            <div class="form-group row">
                <label class="col-sm-3 col-form-label text-right"></label>
                <div class="col-sm-3">
                    <label>Roles<span class="text-danger">*</span></label>
                    {!! Form::select('roles[]', $roles, old('roles'), ['class' => 'form-control select2', 'multiple' => 'multiple', 'required' => '']) !!}
                    <p class="help-block"></p>
                    @if($errors->has('roles'))
                        <p class="help-block">
                            {{ $errors->first('roles') }}
                        </p>
                    @endif
                </div>
                <div class="col-sm-3">
                    <label>Data Akses<span class="text-danger">*</span></label>
                    {!! Form::select('data_access', $access, old('data_access'), ['class' => 'form-control select2', 'required' => '']) !!}
                </div>
            </div>

            <div class="form-group row">
                <label class="col-sm-3 col-form-label text-right"></label>
                <div class="col-sm-3">
                    <label>Dashboard<span class="text-danger">*</span></label>
					{!! Form::select('dashboard', $dashboard, old('dashboard'), ['class' => 'form-control select2', 'required' => '','id' => 'dashboard']) !!}
                </div>
            </div>
            
            <div class="form-group row" id="organisasi" style="display: none">
                {!! Form::label('name', 'Organisasi', ['class' => 'col-form-label col-sm-3']) !!}

                <div class="col-sm-3" id="company" style="display: none">
                    <label>Company<span class="text-danger">*</span></label>
                    {!! Form::select('company_id', $company, old('company_id'), ['class' => 'form-control select2 company','required' => 'required']) !!}
                </div>
                <div class="col-sm-3" id="location" style="display: none">
                    <label>Location<span class="text-danger">*</span></label>
                    <select name="location_id" class="select2 form-control location" required></select>
                </div>
            </div>

            <div class="form-group row">
                <label class="col-sm-3 col-form-label text-right">Akses Mobile Apps <span class="text-danger">*</span></label>
                <div class="col-sm-6">
                    <input type="checkbox" name="is_mobile" class="switch switch-info" id="is_mobile" value="{{ $user->is_mobile ?? 'false'}}">
                    <p class="help-block"></p>
                    @if($errors->has('is_mobile'))
                        <p class="help-block">
                            {{ $errors->first('is_mobile') }}
                        </p>
                    @endif
                </div>
            </div>

            <div class="form-group row">
                <label class="col-sm-3 col-form-label text-right">No Whatsapp</label>
                <div class="col-sm-3">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text text-secondary">+</span>
                        </div>
                        {!! Form::text('telp', old('telp'), ['class' => 'form-control','id' => 'telp','placeholder'=>'Contoh : 62123456789']) !!}
                    </div>
                </div>
            </div>

            <div class="form-group row">
                <label class="col-sm-3 col-form-label text-right">Notifikasi API Whatsapp Approval DPM</label>
                <div class="col-sm-3">
                    <input type="checkbox" name="is_whatsapp" class="switch switch-info" id="is_whatsapp" value="{{false}}">
                    <p class="help-block"></p>
                    @if($errors->has('is_whatsapp'))
                        <p class="help-block">
                            {{ $errors->first('is_whatsapp') }}
                        </p>
                    @endif
                </div>
            </div>

            <div class="form-group row">
                <label class="col-sm-3 col-form-label text-right"></label>
                <div class="col-sm-8">
                    <label>Photo Profile </label>
                    {!! Form::myFile('photo', '',['id' => 'profile-img']) !!}
                    <img src="" id="profile-img-tag" width="200px" />
                </div>
            </div>

            <div class="form-group row">
                <label class="col-sm-3 col-form-label text-right"></label>
                <div class="col-sm-8">
                    <label>Tanda Tangan </label>
                    {!! Form::myFile('ttd', '',['id' => 'profile-ttd']) !!}
                    <img src="" id="profile-ttd-tag" width="200px" />
                </div>
            </div>

            <hr>
            <div class="row form-group">
				<div class="col-sm-12">
                    {!! Form::submit(trans('Submit'), ['class' => 'btn btn-danger float-right','id' => 'btn-submit']) !!}
                    <a href="{{ route('admin.users.index') }}" class="btn btn-light float-right mr-2 ">{{ trans('Cancel') }}</a>
                </div>
            </div>
            {!! Form::close() !!}
        </div>

@stop

@section('js')
	<script type="text/javascript">
		function readURL(input) {
			if (input.files && input.files[0]) {
				var reader = new FileReader();
				reader.onload = function (e) {
					$('#profile-img-tag').attr('src', e.target.result);
				}
				reader.readAsDataURL(input.files[0]);
			}
		}
        function readURLttd(input) {
			if (input.files && input.files[0]) {
				var reader = new FileReader();
				reader.onload = function (e) {
					$('#profile-ttd-tag').attr('src', e.target.result);
				}
				reader.readAsDataURL(input.files[0]);
			}
		}
        $(document).ready(function() {

            var type = $("#type");

            type.select2().on('change', function() {
                
                if(type.val() == 2 || type.val() == 3 || type.val() == 5){
                    $("#organisasi").show();
                }else{
                    $("#organisasi").hide();
                }

                if(type.val() == 2 || type.val() == 3 || type.val() == 5){
                    $("#company").show();
                }else{
                    $("#company").hide();
                }

                if(type.val() == 3 ){
                    $("#location").show();
                }else{
                    $("#location").hide();
                }

             
            });


            var company  = $(".company");
            var location = $(".location");
            var department = $(".department");

            company.select2({
                placeholder: "Silahkan pilih Perusahaan...",
                allowClear: true
            }).on('change', function() {

                $.ajax({
                    url:"{{ route('master.get_location') }}/" + company.val(), // if you say $(this) here it will refer to the ajax call not $('.item')
                    type:'GET',
                    success:function(data) {
                        location.empty();
                        location.append($("<option></option>").attr("value", "").text("Silahkan pilih...")); 
                        $.each(data, function(value, key) {
                            location.append($("<option></option>").attr("value", value).text(key)); // name refers to the objects value when you do you ->lists('name', 'id') in laravel
                        });
                        location.select2(); 
                    }
                });

            });

            $("#profile-img").change(function(){
                readURL(this);
                $("#profile-img-exits").hide();
            });

            $("#profile-ttd").change(function(){
                readURLttd(this);
                $("#profile-ttd-exits").hide();
            });

            if($('#is_mobile').val()=='true'){
                $('#is_mobile').attr('checked','checked').iCheck('update');
            }
            $('#is_mobile').on('ifChecked', function(){
                $("#is_mobile" ).attr('value', 'true');
            });
            $('#is_mobile').on('ifUnchecked', function(){
                $("#is_mobile" ).attr('value', 'false');
            });

            if($('#is_whatsapp').val() =='true'){
                $('#is_whatsapp').attr('checked','checked').iCheck('update');
            }
            $('#is_whatsapp').on('ifChecked', function(){
                $("#is_whatsapp" ).attr('value', 'true');
            });
            $('#is_whatsapp').on('ifUnchecked', function(){
                $("#is_whatsapp" ).attr('value', 'false');
            });

        });
	</script>
@stop
