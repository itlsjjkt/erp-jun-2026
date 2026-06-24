@extends('layouts.app')

@section('page-header')
   Ringkasan Mutasi Barang Per Bulan
@endsection

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('logistic.inventory.mutation') }}">Mutasi Barang</a></li>
        <li class="breadcrumb-item active" aria-current="page">Ringkasan Mutasi Barang Per Bulan</li>
    </ol>
@endsection

@section('content')

<div class="row mB-40">
 
    <div class="col-sm-12">
        <div class="bgc-white p-20 bd">
        
            <div class="mB-20 mT-10">
                <a  href="{{ route('logistic.inventory.mutation') }}" class="fsz-sm">
                    <i class="ti-arrow-left "></i> Kembali
                </a>
                <hr>
                <div class="row">
                    <div class="col-sm-6">
                   
               
                        <form class="form-horizontal" action="{{ route('logistic.inventory.mutation.summary_month')}}" method='GET' id="formId">
                            {{ csrf_field() }}
                            <div class="row">
                                <div class="col-sm-5">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="ti-calendar"></i></span>
                                        </div>
                                        <input type="text" name="month" class="form-control monthpicker" id="monthpicker_id" value="<?php echo $month ;?>">
                                        <input type="text" name="year"  class="form-control yearpicker"  id="yearpicker_id" required value="<?php echo $year ;?>">

                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                     <div class="col-sm-6">
                        <a  href="#" data-toggle="collapse" data-target="#export" class="btn btn-outline border-dark float-right text-uppercase fsz-sm fw-600">
                            <i class="fa fa-file-excel-o text-success icon-lg"></i> Export Data
                        </a>
                    </div>
                </div>
            </div>
            <hr>

            <div class="collapse mB-20" id="export" aria-expanded="false">
                <form class="form-horizontal" action="{{ route('logistic.inventory.mutation.summary_month.export')}}" method='POST'>
                    {{ csrf_field() }}
                    <div class="bgc-white bd bdrs-3 p-20">
                        <h6>Export Data</h6>
                        <hr>
                        <div class="row">
                            <div class="col-sm-3">
                                <label>Lokasi</label>
                                @if(isAdministrator() || isAdministratorCompany())
                                    {!! Form::select('location_id', $location, old('location_id'), ['class' => 'form-control select2', 'id'=>'location_id','required' => 'required']) !!}
                                @elseif(isAdministratorLocation())
                                    <input type="text" readonly class="form-control" value="{{ getDataByID('locations',Auth::user()->location_id )->name }}">
                                    <input type="hidden" name="location_id" id="location_id" value="{{ Auth::user()->location_id }}">
                                @else
                                    <input type="text" readonly class="form-control" value="{{ getDataByID('locations',Auth::user()->location_id)->name }}">
                                    <input type="hidden" name="location_id" id="location_id" value="{{Auth::user()->location_id}}">
                                @endif
                            </div>
                            <div class="col-sm-3">
                                <label>Bulan</label>
                                <input type="text" name="month" class="form-control monthpicker" value="<?php echo $month;?>">
                            </div>
                            <div class="col-sm-2">
                                <label>Tahun</label>
                                <input type="text" name="year" class="form-control yearpicker" value="<?php echo $year;?>">
                            </div>
                            <div class="col-sm-3">
                                <button type="submit" class="btn btn-danger mt-4">Export</button>
                            </div>
                        </div>
                    </div>
                  
                </form>
            </div> 

            <div id="showSelected"></div>
            <div class="table-responsive mt-4">
                <table id="dataTables" class="table table-bordered" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th style="width:80px">ID</th>
                            <th style="width:120px">KODE</th>
                            <th>NAMA BARANG</th>
                            <th>STN</th>
                            @if(isAdministrator() || isAdministratorCompany() || isLocationAdministrator() ||  isEmployeeAdministrator() )
                                <th>Location</th>
                            @endif
                            <th>S. AWAL</th>
                            <th>MASUK</th>
                            <th>KELUAR</th>
                            <th>S. AKHIR</th>
                            <th>LOKASI RAK</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
    <script>
    $(document).ready(function() {
        var rows_selected = [];
        var table = $('#dataTables').DataTable({
            processing: true,
            serverSide: true,
            "pageLength": 50,
            ajax: '/logistic/inventory_mutation_summary_month_datatables/{{$month}}/{{$year}}',
            orderFixed: [0, 'desc'],
            columns: [
                {data: 'id', name: 'id'},
                {data: 'productcode', name: 'productcode'},
                {data: 'productname', name: 'productname'},
                {data: 'unit', name: 'unit', searchable: false},
                @if(isAdministrator() || isAdministratorCompany() || isLocationAdministrator() ||  isEmployeeAdministrator() )
                    {data: 'location', name: 'location', searchable: false},
                @endif
                {data: 'initial', name: 'initial', searchable: false},
                {data: 'in', name: 'in', searchable: false},
                {data: 'out', name: 'out', searchable: false},
                {data: 'soh', name: 'soh', searchable: false},
                {data: 'code_rack', name: 'code_rack'},
            ],
            "order": [[ 1, "DESC" ]],
        });

        $("#monthpicker_id").on('change', function() {
            $('#formId').submit();
            $('#month_export').val( this.value);
        });

        $("#yearpicker_id").on('change', function() {
            $('#formId').submit();
            $('#year_export').val( this.value);
        });

        $('.monthpicker').datepicker({
            startView: 1, 
            minViewMode: 1,
            autoclose: true,
            format: 'MM',
        });

        $('.yearpicker').datepicker({
            minViewMode: 2,
            format: 'yyyy',
            autoclose: true
        });


    });
</script>
@stop