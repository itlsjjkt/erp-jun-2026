@extends('layouts.app')

@section('page-header')
    DPM 
@endsection

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('purchase_request.index') }}">DPM</a></li>
        <li class="breadcrumb-item active" aria-current="page">Search</li>
    </ol>
@endsection

@section('content')
<div class="bgc-white bd bdrs-3 p-20 mB-20">
    <div class="row mB-20">
        <div class="col-sm-6 ">
            <a href="{{ route('purchase_request_revision.index') }}" class="" >
                <i class="ti-arrow-left"></i> Kembali
            </a>
        </div>
        <div class="col-sm-6">
            <a href="#" class="btn btn-info float-right text-uppercase fsz-sm fw-600" data-toggle="collapse" data-target="#filter">
                <i class="ti-search"></i> Pencarian
            </a>
        </div>
    </div>

    <hr>

    <div class="collapse mB-20" id="filter" aria-expanded="false">
        <form class="form-horizontal" id="form" method='GET'>
            {{ csrf_field() }}
            <div class="form-group row">
                <div class="col-auto">
                    <label>Company </label>
                    @if(isAdministrator())
                        {!! Form::select('company_id', $company, $data['company_id'], ['class' => 'form-control select2 company', 'id'=>'company_id']) !!}
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
                        {!! Form::select('location_id', $location, $data['location_id'], ['class' => 'form-control location select2']) !!}
                    @else
                        <input type="text" readonly class="form-control" value="{{ getDataByID('locations',Auth::user()->location_id )->name }}">
                        <input type="hidden" name="location_id" id="location_id" value="{{ Auth::user()->location_id }}">
                    @endif
                </div>
                <div class="col-auto">
                    <label>Kapal</label>
                    {!! Form::select('department_id', $department, $data['department_id'], ['class' => 'form-control select2 department']) !!}
                </div>
                <div class="col-auto">
                    <label>Project</label>
                    {!! Form::select('project_id', $project, $data['project_id'], ['class' => 'form-control select2']) !!}
                </div>
                <div class="col-auto">
                    <label>Tanggal Input <span class="text-danger">*</span></label>
                    <div class="input-group w-100">
                        <input type="text" name="start_date" class="form-control datepicker m-r-n-1" value="{{ $data['start_date'] }}" id="inlineDate" >
                        <div class="input-group-prepend">
                            <div class="input-group-text">ke</div>
                        </div>
                        <input type="text" name="end_date"  class="form-control datepicker" placeholder="mm/dd/yyyy" id="inlineDate" value="{{ $data['end_date'] }}">
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

    <div class="table-responsive mt-4">
        <p>{!! $search !!}</p>
        <table id="dataTables" class="table table-striped table-bordered" cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th>No. DPM</th>
                    <th>Kapal/Departemen</th>
                    <th>Project</th>
                    <th>Dibuat Oleh</th>
                    <th>Tgl Input</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
                @if(count($pr) > 0)
                    @foreach ($pr as $item)
                        <tr >
                            <td>{{ $item->doc_no }}</td>
                            <td>{{ $item->department }}</td>
                            <td>{{ $item->project }}</td>
                            <td>{{ $item->created }}</td>
                            <td>{{ date('d/m/Y',strtotime( $item->created_at)) }}</td>
                            <td>
                                <a href="{{ route('purchase_request.show', Hashids::encode($item->id)) }}" title="{{ trans('app.show_title') }}" data-toggle='tooltip' class='btn btn-outline'><span class='ti-eye icon-lg'></span> </a>
                            </td>
                        </tr>
                    @endforeach
                @else
                    <tr><td colspan="5" class="text-center"> Data tidak ditemukan</td></tr>
                @endif
            </tbody>
        </table>
        {{ $pr->links() }}
    </div>
</div>

@endsection


@section('js')
    <script>
    $(document).ready(function() {

        $('.location').select2({
            placeholder: "Silahkan pilih...",
            allowClear : true
        })



        var $company  = $('.company');
        var $location = $(".location");

        $company.select2({
            placeholder: "Silahkan pilih...",
            allowClear : true
        }).on('change', function() {
            $location.empty();
            $.ajax({
                url:"{{ route('master.get_location') }}/" + $company.val(), // if you say $(this) here it will refer to the ajax call not $('.item')
                type:'GET',
                success:function(data) {
                    $location.empty();
					$location.append($("<option></option>").attr("value", "").text("Silahkan pilih...")); 
                    $.each(data, function(value, key) {
                        $location.append($("<option></option>").attr("value", value).text(key)); // name refers to the objects value when you do you ->lists('name', 'id') in laravel
                    });
                    $location.select2(); //reload the list and select the first option
                }
            });
        });

    });
</script>
@stop
