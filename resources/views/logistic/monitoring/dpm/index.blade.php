@extends('layouts.app')

@section('page-header')
     Monitoring DPM
@endsection

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item active" aria-current="page"> Monitoring DPM</li>
    </ol>
@endsection

@section('content')
<div class="bgc-white bd bdrs-3 p-20 mB-20">

    <div class="mB-20 row">
            <div class="col-lg-12">
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
                        {!! Form::select('type_dpm', $type, old('type_dpm'), ['class' => 'form-control select2']) !!}
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
                        @if(isAdministrator() || isAdministratorCompany() || isAdmin() || isPurchasing() )
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
                            <input type="text" name="start_date" class="form-control datepicker m-r-n-1" placeholder="mm/dd/yyyy" id="inlineDate"  value="{{ date('m/d/Y') }}">
                            <div class="input-group-prepend">
                                <div class="input-group-text">ke</div>
                            </div>
                            <input type="text" name="end_date"  class="form-control datepicker" placeholder="mm/dd/yyyy" id="inlineDate"   value="{{ date('m/d/Y') }}">
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

     <div class="alert alert-info">Monitoring DPM digunakan untuk memonitoring DPM yang sudah terbit Nomor PR/PO maupun yang sedang On progress approvalnya</div>
    <table id="dataTables" class="table table-striped table-bordered" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th style="width:400px">No. DPM</th>
                <th>Lokasi/Kapal</th>
                <th>Departemen</th>
                <th>Project</th>
                <th>Type DPM</th>
                <th>Tgl Input</th>
                <th></th>
            </tr>
        </thead>
    
    </table>

</div>



@endsection


@section('js')
    <script>
    $(document).ready(function() {
        $('#dataTables').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route('logistic.monitoring.dpm.datatables') }}',
            "pageLength": 50,
            columns: [
                {data: 'doc_no', name: 'doc_no'},
                {data: 'locationnn', name: 'locations.name'},
                {data: 'department', name: 'departments.name'},
                {data: 'project', name: 'projects.name'},
                {data: 'type', name: 'type'},
                {data: 'created_at', name: 'created_at'},
                {data: 'action', name: 'action', orderable: false, searchable: false}
            ],
            "order": [[ 5, "desc" ]]
        });
    });
</script>
@stop