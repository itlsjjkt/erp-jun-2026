@extends('layouts.app')

@section('page-header')
    Monitoring DPM
@endsection

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('logistic.monitoring.dpm') }}">Monitoring DPM</a></li>
        <li class="breadcrumb-item active" aria-current="page">Pencarian</li>
    </ol>
@endsection

@section('content')
    <div class="bgc-white bd bdrs-3 p-20 mB-20">    

        <div class="mB-20 row">
            <div class="col-sm-6 ">
                <a href="{{ route('logistic.monitoring.dpm') }}" class="" >
                    <i class="ti-arrow-left"></i> Kembali
                </a>
            </div>
            <div class="col-lg-6">
                <a href="#" class="btn btn-success float-right" data-toggle="collapse" data-target="#filter">
                    <i class="ti-search"></i> Pencarian
                </a>
            </div>
        </div>
        <hr>

        <div class="collapse mB-20" id="filter" aria-expanded="false">
            <form class="form-horizontal" action="{{ route('logistic.monitoring.dpm.search') }}" method='GET'>
                {{ csrf_field() }}
                <div class="bd bdrs-3 p-20">
                    <h6>Form Pencarian </h6>
                    <hr>
                    <div class="form-group row">
                        <div class="col-sm-3">
                            <label>Nama Barang</label>
                            <input type="text" name="product_name" class="form-control">
                        </div>
                        <div class="col-sm-3">
                            <label>Tipe DPM</label>
                            {!! Form::select('type_dpm', $type, $type_dpm, ['class' => 'form-control select2']) !!}
                        </div>
                        <div class="col-sm-3">
                            <label>Deskripsi DPM</label>
                            <input type="text" name="description" class="form-control">
                        </div>
                        <div class="col-sm-3">
                            <label>No. PR</label>
                            <input type="text" name="pr_no" class="form-control">
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-sm-3">
                            <label>Lokasi</label>
                            @if(isAdministrator() || isAdministratorCompany() || isAdmin())
                                {!! Form::select('location_id', $location, old('location_id'), ['class' => 'form-control select2','id'=>'location_id']) !!}
                            @elseif(isAdministratorLocation())
                                <input type="text" readonly class="form-control" value="{{ getDataByID('locations',Auth::user()->location_id )->name }}">
                                <input type="hidden" name="location_id" id="location_id" value="{{ Auth::user()->location_id }}">
                            @else
                                <input type="text" readonly class="form-control" value="{{ getDataByID('locations',Auth::user()->location_id)->name }}">
                                <input type="hidden" name="location_id" id="location_id" value="{{Auth::user()->location_id}}">
                            @endif
                        </div>
                        <div class="col-sm-4">
                            <label>Periode</label>
                            <div class="input-group w-100">
                                <input type="text" name="start_date"  class="form-control datepicker m-r-n-1" placeholder="mm/dd/yyyy" id="inlineDate" value="{{ $start_date }}">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">ke</div>
                                </div>
                                <input type="text" name="end_date"  class="form-control datepicker" placeholder="mm/dd/yyyy" id="inlineDate"  value="{{ $end_date }}">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-12">
                        <div class="float-right">
                            <button type="submit" class="btn btn-danger mt-3">Cari</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>


        <p>{!! $search !!}</p>
        <table id="dataTables" class="table table-striped table-bordered" cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th>No. DPM</th>
                    <th>Kapal/Departemen</th>
                    <th>Project</th>
                    <th>Type Dpm</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
                @foreach ($pr as $item)
                    <tr >
                        <td>{{ $item->doc_no }}</td>
                        <td>{{ $item->department }}</td>
                        <td>{{ $item->project }}</td>
                        <td>{{ strtoupper($item->type) }}</td>
                        <td>
                            <a href="{{ route('logistic.monitoring.dpm.detail', ['id' => Hashids::encode($item->id)]) }}" title="{{ trans('app.show_title') }}" data-toggle='tooltip' class='btn btn-outline'><span class='ti-eye icon-lg'></span> </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        {{ $pr->links() }}
    </div>

@endsection
