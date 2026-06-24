@extends('layouts.app')

@section('page-header')
    Change Profile
@endsection

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item active" aria-current="page">Change Profile</li>
    </ol>
@endsection

@section('content')
        {!! Form::model($users, [
                    'route' => ['profile.change_profile'],
					'method' => 'POST', 
					'files' => true
				])
			!!}
        {{ csrf_field() }}

        <div class="bgc-white bd bdrs-3 p-20 mB-20">
            <h6>Form Change Profile</h6>
            <hr class="mB-30">

            <div class="form-group{{ $errors->has('name') ? ' has-error' : '' }} row">
                <label for="email" class="col-md-4 col-form-label">Name</label>

                <div class="col-md-4">
                    <input id="name" type="text" class="form-control" name="name" required value="{{ $users->name }}">

                    @if ($errors->has('name'))
                        <span class="help-block">
                            {{ $errors->first('name') }}
                        </span>
                    @endif
                </div>
            </div>

            <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }} row">
                <label for="email" class="col-md-4 col-form-label">Email Address</label>

                <div class="col-md-4">
                    {!! Form::text('email', old('email'), ['class' => 'form-control', 'placeholder' => '', 'required' => '', 'readonly' => '']) !!}

                    @if ($errors->has('email'))
                        <span class="help-block">
                            {{ $errors->first('email') }}
                        </span>
                    @endif
                </div>
            </div>

            <div class="form-group row">
                <label class="col-sm-4 col-form-label text-right">Notification Email</label>
                <div class="col-sm-6">
                    <input type="checkbox" name="notification_email" class="switch switch-info" id="notification_email" value="{{ $users->notification_email or '0'}}">
                    <p class="help-block"></p>
                    @if($errors->has('notification_email'))
                        <p class="help-block">
                            {{ $errors->first('notification_email') }}
                        </p>
                    @endif
                </div>
            </div>

            <div class="form-group row">
                <label class="col-sm-4 col-form-label text-right">Background Color</label>
                <div class="col-sm-6">
                    <div class="custom-control custom-checkbox float-left">
                        <input type="checkbox" name="background" class="custom-control-input bg-list" id="customCheck" value="e6edf5" {{ getChecked($users->background,'e6edf5') }}>
                        <label class="custom-control-label light" for="customCheck"></label>
                    </div>
                    <div class="custom-control custom-checkbox float-left ml-2">
                        <input type="checkbox" name="background" class="custom-control-input bg-list" id="customCheck1" value="ffd04b" {{ getChecked($users->background,'ffd04b') }} >
                        <label class="custom-control-label yellow" for="customCheck1"></label>
                    </div>
                    <div class="custom-control custom-checkbox float-left ml-2">
                        <input type="checkbox" name="background" class="custom-control-input bg-list" id="customCheck2" value="00dca9" {{ getChecked($users->background,'00dca9') }}>
                        <label class="custom-control-label green" for="customCheck2"></label>
                    </div>
                </div>
            </div>
            
            <div class="form-group row mt-5">
                <label class="col-sm-4 col-form-label text-right">Photo Profile </label>
                <div class="col-sm-8">
                    {!! Form::myFile('photo', '',['id' => 'profile-img']) !!}
                    <img src="" id="profile-img-tag"  class="img-fluid" style="width:100px"/>
                </div>
            </div>
            <div class="form-group row">
                <label class="col-sm-4 col-form-label text-right"> </label>
                <div class="col-sm-2">
                    @if ($users->photo)
                        <img src="{{ asset('storage'.$users->photo) }}" id="profile-img-exits" class="img-fluid img-thumbnail w-75">
                    @endif
                </div>
            </div>

           
            

            

            <hr>
            <div class="form-group row">
                <div class="col-sm-12">
                    <button type="submit" class="btn btn-primary float-right">Submit</button>
                </div>
            </div>

        </div>

      
    </form>
@endsection

		
@section('js')
	<script type="text/javascript">
        $(document).ready(function() {
          

            $('.bg-list').on('change', function() {
                $('.bg-list').not(this).prop('checked', false);  
            });
            $("input:checkbox").click(function() {
                if ($(this).is(":checked") === true) {
                    var group = "input:checkbox[name='" + $(this).attr("name") + "']";
                    $(group).attr("checked", false);
                    $(this).attr("checked", true);
                } else {
                    $(this).is(":checked", false);
                }
            });

        });
        
		function readURL(input) {
			if (input.files && input.files[0]) {
				var reader = new FileReader();
				
				reader.onload = function (e) {
					$('#profile-img-tag').attr('src', e.target.result);
				}
				reader.readAsDataURL(input.files[0]);
			}
		}
		$("#profile-img").change(function(){
			readURL(this);
            $("#profile-img-exits").hide();
		});
        if($('#notification_email').val()=='1'){
            $('#notification_email').attr('checked','checked').iCheck('update');
        }
        $('#notification_email').on('ifChecked', function(){
            $("#notification_email" ).attr('value', '1');
        });
        $('#notification_email').on('ifUnchecked', function(){
            $("#notification_email" ).attr('value', '0');
        });
	</script>
@stop