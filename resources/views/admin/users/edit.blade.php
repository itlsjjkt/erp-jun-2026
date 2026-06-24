@extends('layouts.app')

@section('page-header')
	User 
@stop

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">User Management</a></li>
        <li class="breadcrumb-item active" aria-current="page">Edit</li>
    </ol>
@endsection

@section('content')
	<div class="bgc-white p-30 bd">

		{!! Form::model($user, [
				'action' => ['Admin\UsersController@update', $user->id],
				'method' => 'put', 
				'files' => true
			])
		!!}

			<h6>Edit User</h6>
			<hr>
			<div class="form-group row">
                {!! Form::label('name', 'Name*', ['class' => 'col-form-label col-sm-3']) !!}
                <div class="col-sm-6">
                {!! Form::text('name', old('name'), ['class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}
                <p class="help-block"></p>
                @if($errors->has('name'))
                    <p class="help-block">
                        {{ $errors->first('name') }}
                    </p>
                @endif
                </div>
            </div>

            <div class="form-group row">
                {!! Form::label('email', 'Email*', ['class' => 'col-form-label col-sm-3']) !!}
                <div class="col-sm-6">
                {!! Form::email('email', old('email'), ['class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}
                <p class="help-block"></p>
                @if($errors->has('email'))
                    <p class="help-block">
                        {{ $errors->first('email') }}
                    </p>
                @endif
                </div>
            </div>
		
			<div class="form-group row">
				{!! Form::label('roles', 'Roles*', ['class' => 'col-form-label col-sm-3']) !!}
				<div class="col-sm-6">
					{!! Form::select('roles[]', $roles, old('roles'), ['class' => 'form-control select2', 'multiple' => 'multiple', 'required' => '']) !!}
					<p class="help-block"></p>
					@if($errors->has('roles'))
						<p class="help-block">
							{{ $errors->first('roles') }}
						</p>
					@endif
				</div>
			</div>
		
			<div class="form-group row">
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
				<div class="col-sm-3">
					{!! Form::select('data_access', $access, old('data_access'), ['class' => 'form-control select2', 'required' => '']) !!}
				</div>
			</div>

			<div class="form-group row">
				<label class="col-sm-3 col-form-label text-right">Dashboard<span class="text-danger">*</span></label>
				<div class="col-sm-3">
					{!! Form::select('dashboard', $dashboard, old('dashboard'), ['class' => 'form-control select2', 'required' => '','id' => 'dashboard']) !!}
					<input type="hidden" id="val_dashboard" value="{{$user->dashboard}}">
				</div>
			</div>
			
			<div class="form-group row" id="organisasi">
				{!! Form::label('name', 'Organisasi', ['class' => 'col-form-label col-sm-3']) !!}
				<div class="col-sm-3" id="company">
					<label>Company<span class="text-danger">*</span></label>
					{!! Form::select('company_id', $company, old('company_id'), ['class' => 'form-control select2 company' ,'required' => 'required']) !!}
				</div>
				<div class="col-sm-3" id="location">
					<label>Location<span class="text-danger">*</span></label>
					{!! Form::select('location_id', $location, old('location_id'), ['class' => 'form-control select2 location','required' => 'required']) !!}
				</div>
			</div>
			

			<div class="form-group row">
                <label class="col-sm-3 col-form-label text-right">Akses Mobile Apps <span class="text-danger">*</span></label>
                <div class="col-sm-3">
                    <input type="checkbox" name="is_mobile" class="switch switch-info" id="is_mobile" value="{{ $is_mobile }}">
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
                    <input type="checkbox" name="is_whatsapp" class="switch switch-info" id="is_whatsapp" value="{{ $is_whatsapp }}">
                    <p class="help-block"></p>
                    @if($errors->has('is_whatsapp'))
                        <p class="help-block">
                            {{ $errors->first('is_whatsapp') }}
                        </p>
                    @endif
                </div>
            </div>
			
			<div class="form-group row">
				<label class="col-sm-3 col-form-label text-right">Photo Profile </label>
				<div class="col-sm-8">
					{!! Form::myFile('photo', '',['id' => 'profile-img']) !!}
					<img src="" id="profile-img-tag" width="200px" />
				</div>
			</div>

			@if ($user->photo)
				<div class="form-group row">
					<label class="col-sm-3 col-form-label text-right"> </label>
					<div class="col-sm-2">
						<img src="{{ asset('storage'.$user->photo) }}" class="img-fluid img-thumbnail w-75" id="profile-img-exits">
					</div>
				</div>
			@endif

			<div class="form-group row">
				<label class="col-sm-3 col-form-label text-right">Tanda Tangan </label>
				<div class="col-sm-8">
					{!! Form::myFile('ttd', '',['id' => 'profile-ttd']) !!}
					<img src="" id="profile-ttd-tag" width="200px" />
				</div>
			</div>

			@if ($user->ttd)
				<div class="form-group row">
					<label class="col-sm-3 col-form-label text-right"> </label>
					<div class="col-sm-2">
						<img src="{{ asset('storage'.$user->ttd) }}" class="img-fluid img-thumbnail w-75" id="profile-ttd-exits">
					</div>
				</div>
			@endif

			<hr>
			<div class="form-group row">
				<div class="col-sm-12">
					<button type="submit" class="btn btn-primary float-right" id="btn-submit">{{ trans('app.save_button') }}</button>
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

			var delBy = $('#val_dashboard').val();
			$('#dashboard').val(delBy).trigger('change');

			var type = $("#type");

			@if ($user->type == 2 || $user->type == 3 || $user->type == 5)
				$("#organisasi").show();
				$("#company").show();
			@else
				$("#organisasi").hide();
				$("#company").hide();
			@endif


			@if ($user->type == 3)
				$("#location").show();
			@else
				$("#location").hide();
			@endif


			type.select2().on('change', function() {
				
                if(type.val() == 2 || type.val() == 3 || type.val() == 5){
					$("#organisasi").show();
					$("#company").show();
				}else{
					$("#organisasi").hide();
					$("#company").hide();
				}

				if(type.val() == 3  ){
					$("#location").show();
				}else{
					$("#location").hide();
				}
			});


			var company  = $(".company");
			var location = $(".location");

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

			if($('#is_mobile').val() =='true'){
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

 

