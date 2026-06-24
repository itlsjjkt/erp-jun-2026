@extends('layouts.app')

@section('page-header')
    DPM
@stop

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('purchase_request.index') }}">DPM</a></li>
        <li class="breadcrumb-item active" aria-current="page">Tambah</li>
    </ol>
@endsection


@section('content')
    {!! Form::open(['method' => 'GET', 'route' => ['purchase_request.item']]) !!}
        <div class="bgc-white p-30 bd">
            <h6>Pengajuan DPM</h6>
            <hr class='mB-30'>
            <p>Step 1 dari 2</p>
            <div class="alert alert-info mb-4">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h6 class="alert-heading">Informasi</h6>
                - Pilih Lokasi, Nama Kapal, Project terlebih dahulu <br>
                - Untuk tipe IM/PETTY CASH hanya berhenti di PR tidak dilakukan PO-Inventory Control
            </div>

            <div class="row mt-5">
                <div class="col-lg-6">
                    <div class="row mb-3">
                        <label class="col-sm-3">Tipe Unit<span class="text-danger">*</span></label>
                        <div class="col-sm-7">
                            <select name="type" class="form-control select2">
                                <option value="po">PO</option>
                                <option value="im">IM</option>
                                <option value="petty_cash">Petty Cash</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <label class="col-sm-3">Lokasi/Kapal  <span class="text-danger">*</span></label>
                        <div class="col-sm-7">
                            @if(isAdministrator() || isAdministratorCompany() || isEmployeeAdministrator()  || isAdmin() )
                                {!! Form::select('location_id', $location, old('location_id'), ['class' => 'form-control select2', 'required' => '','id'=>'location_id']) !!}
                            @elseif(isAdministratorLocation())
                                <input type="text" readonly class="form-control" value="{{ getDataByID('locations',Auth::user()->location_id )->name }}">
                                <input type="hidden" name="location_id" id="location_id" value="{{ Auth::user()->location_id }}">
                            @else
                                <input type="text" readonly class="form-control" value="{{ getDataByID('locations',Auth::user()->location_id)->name }}">
                                <input type="hidden" name="location_id" id="location_id" value="{{Auth::user()->location_id}}">
                            @endif
                            <p class="help-block"></p>
                            @if($errors->has('location_id'))
                                <p class="help-block">
                                    {{ $errors->first('location_id') }}
                                </p>
                            @endif
                        </div>
                    </div>
        
                    
                </div>

                <div class="col-lg-6">
                    <div class="row">
                        <label  class="col-sm-3">Departemen </label>
                        <div class="col-sm-7">
                            {!! Form::select('department_id', $department, old('department_id'), ['class' => 'form-control select2', 'id' =>'department']) !!}
                            <p class="help-block"></p>
                            @if($errors->has('department_id'))
                                <p class="help-block">
                                    {{ $errors->first('department_id') }}
                                </p>
                            @endif
                        </div>
                    </div>
                    <div class="row">
                        <label  class="col-sm-3">Project  <span class="text-danger">*</span></label>
                        <div class="col-sm-7">
                            {!! Form::select('project_id', $project, old('project_id'), ['class' => 'form-control select2', 'required' => '','id' =>'project']) !!}
                            <p class="help-block"></p>
                            @if($errors->has('project_id'))
                                <p class="help-block">
                                    {{ $errors->first('project_id') }}
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            
            <hr>
            <div class="mt-4">
                <a href="{{ route('purchase_request.index') }}" class="btn btn-light mr-1 text-uppercase fsz-sm fw-600">{{ trans('Cancel') }}</a>
                <button class="btn btn-danger text-uppercase fsz-sm fw-600" type="submit" >Lanjut</button>
            </div>
        </div>
        
    {!! Form::close() !!}
@stop

@section('js')

<script  type='text/javascript'>

	$(document).ready(function() {


        $('#department').select2().on('change', function() {
            $(this).valid();
        });
        $('#category').select2().on('change', function() {
            $(this).valid();
        });

        $('#form-dpm').validate({
            rules: {
                location_id: "required",
                category_id: "required",
                department_id: "required",
            },
            onfocusout: false,
            invalidHandler: function(form, validator) {
                var errors = validator.numberOfInvalids();
                if (errors) {
                    validator.errorList[0].element.focus();
                }
            }
        });



    });
    </script>


@stop
