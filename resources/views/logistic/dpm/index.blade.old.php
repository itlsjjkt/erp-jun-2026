@extends('layouts.app')

@section('page-header')
    DPM
@endsection

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item active" aria-current="page">DPM</li>
    </ol>
@endsection

@section('content')

    <div class="bgc-white bd bdrs-3 p-20 mB-20">

        <div class="mB-20">
            <a href="{{ route('purchase_request.create') }}" class="btn btn-info text-uppercase fsz-sm fw-600">
                Tambah DPM
            </a>
            <a href="#" class="btn btn btn-outline border-dark text-uppercase fsz-sm  float-right mr-2 fw-600" data-toggle="collapse" data-target="#filter">
                FILTER | EXPORT
            </a>
        </div>
        
        <hr>
       
        <div class="collapse mB-20" id="filter" aria-expanded="false">
            <form class="form-horizontal" id="form" method='GET'>
                {{ csrf_field() }}
                <div class="form-group row">
                    <div class="col-auto">
                        <label>Company </label>
                        @if(isAdministrator())
                            {!! Form::select('company_id', $company, old('company_id'), ['class' => 'form-control select2 company', 'id'=>'company_id']) !!}
                        @else
                            <input type="text" readonly class="form-control" value="{{ getDataByID('companies',Auth::user()->company_id )->name }}">
                            <input type="hidden" name="company_id" value="{{ Auth::user()->company_id }}">
                        @endif
                    </div>
                    <div class="col-auto">
                        <label>Lokasi</label>
                        @if(isAdministrator() || isAdmin())
                            <select class="form-control select2 location" name="location_id" id="location"></select>
                        @elseif(isAdministratorCompany() || isLocationAdministrator() ||  isEmployeeAdministrator() )
                            {!! Form::select('location_id', $location, old('location_id'), ['class' => 'form-control location select2']) !!}
                        @else
                            <input type="text" readonly class="form-control" value="{{ getDataByID('locations',Auth::user()->location_id )->name }}">
                            <input type="hidden" name="location_id" id="location_id" value="{{ Auth::user()->location_id }}">
                        @endif
                    </div>
                    <div class="col-auto">
                        <label>Kapal</label>
                        {!! Form::select('department_id', $department, old('department_id'), ['class' => 'form-control select2 department']) !!}
                    </div>
                    <div class="col-auto">
                        <label>Project</label>
                        {!! Form::select('project_id', $project, old('project_id'), ['class' => 'form-control select2']) !!}
                    </div>
                    <div class="col-auto">
                        <label>Tanggal Input <span class="text-danger">*</span></label>
                        <div class="input-group w-100">
                            <input type="text" name="start_date" class="form-control datepicker m-r-n-1" value="{{ date('m/d/Y')}}" id="inlineDate" >
                            <div class="input-group-prepend">
                                <div class="input-group-text">ke</div>
                            </div>
                            <input type="text" name="end_date"  class="form-control datepicker" placeholder="mm/dd/yyyy" id="inlineDate" value="{{ date('m/d/Y') }}">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col">
                        <label></label>
                        <button type="submit" class="btn btn-primary mt-3 text-uppercase" id="btn-filter">FILTER</button>
                        <button type="submit" class="btn btn-success mt-3 text-uppercase" id="btn-export">EXPORT DATA</button>
                        <button type="submit" class="btn btn-danger mt-3 text-uppercase fsz-sm fw-600"  id="btn-export-history">EXPORT HISTORICAL APPROVAL</button>
                    </div>
                </div>
            </form>
        </div>

        <div class="alert alert-warning mb-4">
            <span class="alert-heading mb-0 font-weight-bold">INFORMASI</span><br>
            Halaman ini menampikan DPM yang masih on progress Approval. Gunakan fitur monitoring DPM untuk melihat DPM yang sudah menjadi PR
        </div>

        <div class="table-responsive mt-5">
            <table id="dataTables" class="table table-striped table-bordered" cellspacing="0" width="100%">
                <thead>
                    <tr>
                        <th>No. DPM</th>
                        <th>Kapal/Departemen</th>
                        <th>Project</th>
                        <th>Dibuat Oleh</th>
                        <th>Tgl Input</th>
                        <th></th>
                    </tr>
                </thead>
            </table>
        </div>

    </div>

@endsection


@section('js')
    <script>
    $(document).ready(function() {
        $('#dataTables').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route('purchase_request.datatables') }}',
            "pageLength": 50,
            columns: [
                {data: 'doc_no', name: 'doc_no'},
                {data: 'department', name: 'departments.name'},
                {data: 'project', name: 'projects.name'},
                {data: 'created', name: 'users.name'},
                {data: 'updated_at', name: 'updated_at', searchable: false},
                {data: 'action', name: 'action', orderable: false, searchable: false}
            ],
            "order": [[ 4, "desc" ]]
        });

        var company    = $('.company');
        var location   = $('.location');
        var department = $('.department');

        company.select2({
            placeholder: "Silahkan pilih...",
            allowClear : true
        }).on('change', function() {
            location.empty();
            $.ajax({
                url:"{{ route('master.get_location') }}/" + company.val(), // if you say $(this) here it will refer to the ajax call not $('.item')
                type:'GET',
                success:function(data) {
                    location.empty();
					location.append($("<option></option>").attr("value", "").text("Silahkan pilih..."));
                    $.each(data, function(value, key) {
                        location.append($("<option></option>").attr("value", value).text(key)); // name refers to the objects value when you do you ->lists('name', 'id') in laravel
                    });
                    location.select2(); //reload the list and select the first option
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
                    department.select2(); //reload the list and select the first option
                }
            });
        });


        $('#btn-filter').click( function() {
            $('form#form').attr('action',"{{ route('purchase_request.search') }}");
            if($('form#form').valid()){
                $('form#form').submit();
            }
        });

        $('#btn-export').click( function(e) {
            e.preventDefault();
            $('form#form').attr('action',"{{ route('purchase_request.export') }}");
            if($('form#form').valid()){
                $('form#form').submit();
            }
        });

        $('#btn-export-history').click( function(e) {
            e.preventDefault();
            $('form#form').attr('action',"{{ route('purchase_request.export_historical') }}");
            if($('form#form').valid()){
                $('form#form').submit();
            }
        });


    });
</script>
@stop
