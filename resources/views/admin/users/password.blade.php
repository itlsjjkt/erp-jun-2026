@extends('layouts.app')

@section('page-header')
	User 
@stop

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">User Management</a></li>
        <li class="breadcrumb-item active" aria-current="page">Reset Password</li>
    </ol>
@endsection

@section('content')
	<div class="bgc-white p-30 bd">

		{!! Form::model($user, [
				'action' => ['Admin\UsersController@update_password', $user->id],
				'method' => 'post', 
				'files' => true
			])
		!!}

			<h6>Reset Password</h6>
			<hr>
			<div class="form-group row">
                {!! Form::label('name', 'Name*', ['class' => 'col-form-label col-sm-3']) !!}
                <div class="col-sm-6">
				<input class="form-control" disabled value="{{ $user->name }}">
                </div>
            </div>

            <div class="form-group row">
                {!! Form::label('email', 'Email*', ['class' => 'col-form-label col-sm-3']) !!}
                <div class="col-sm-6">
				<input class="form-control" disabled value="{{ $user->email }}">
                </div>
            </div>
		
			<div class="form-group{{ $errors->has('new-password') ? ' has-error' : '' }} row mt-4">
                <label class="col-md-3 col-form-label"></label>
                <div class="col-md-3">
					<label>Password Baru</label>
                    <input id="new-password" type="password" class="form-control" name="new-password" required>
                    @if ($errors->has('new-password'))
                        <span class="help-block">
                            {{ $errors->first('new-password') }}
                        </span>
                    @endif
                </div>
				<div class="col-md-3">
                	<label>Konfirmasi Password</label>
					<input id="new-password-confirm" type="password" class="form-control" name="new-password_confirmation" required>
                </div>
            </div>

			<hr>
			<div class="form-group row">
				<div class="col-sm-12">
					<button type="submit" class="btn btn-primary float-right">{{ trans('app.edit_button') }}</button>
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
	
		$(document).ready(function() {

			var type = $("#type");

			type.select2().on('change', function() {
				
				if(type.val() == 2 || type.val() == 3 || type.val() == 4 ){
					$("#organisasi").show();
				}else{
					$("#organisasi").hide();
				}

				if(type.val() == 2 || type.val() == 3 || type.val() == 4  ){
					$("#company").show();
				}else{
					$("#company").hide();
				}

				if(type.val() == 3 || type.val() == 4  ){
					$("#location").show();
				}else{
					$("#location").hide();
				}

				if(type.val() == 4 ){
					$("#department").show();
				}else{
					$("#department").hide();
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

				$.ajax({
					url:"{{ route('master.get_department') }}/" + company.val(), // if you say $(this) here it will refer to the ajax call not $('.item')
					type:'GET',
					success:function(data) {
						department.empty();
						department.append($("<option></option>").attr("value", "").text("Silahkan pilih...")); 
						$.each(data, function(value, key) {
							department.append($("<option></option>").attr("value", value).text(key)); // name refers to the objects value when you do you ->lists('name', 'id') in laravel
						});
						department.select2(); 
					}
				});
			});

			$("#profile-img").change(function(){
				readURL(this);
				$("#profile-img-exits").hide();
			});
		});
	</script>
@stop

 

