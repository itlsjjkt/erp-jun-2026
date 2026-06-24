@extends('layouts.app')

@section('page-header')
  Asuransi Cargo
@endsection

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item active" aria-current="page">Asuransi Cargo</li>
    </ol>
@endsection

@section('content')
   

    <div class="bgc-white bd bdrs-3 p-20 mB-20">

        <div class="mB-20">
            <a href="{{ route('logistic.insurance_cargo.list') }}" class="btn btn-info">
                Buat Asuransi
            </a>
            <a  href="#" data-toggle="collapse" data-target="#export" class="btn btn-outline border-dark float-right">
                <i class="fa fa-file-excel-o text-success icon-lg"></i> Export Data
            </a>
        </div>

        <div class="collapse mB-20" id="export" aria-expanded="false">
            <form class="form-horizontal" action="{{ route('logistic.insurance_cargo.export')}}" method='POST'>
                {{ csrf_field() }}
                <div class="bgc-white bd bdrs-3 p-20">
                    <h6>Export Data</h6>
                    <hr>
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group row">
                                <label class="col-form-label col-sm-3">Tanggal Input</label>
                                <div class="col-sm-9">
                                    <div class="input-group w-100">
                                        <input type="text" name="start_date" class="form-control datepicker m-r-n-1" placeholder="mm/dd/yyyy" required>
                                        <div class="input-group-prepend">
                                            <div class="input-group-text">ke</div>
                                        </div>
                                        <input type="text" name="end_date" class="form-control datepicker" placeholder="mm/dd/yyyy" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <button type="submit" class="btn btn-danger">Export</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="table-responsive mt-4">
            <table id="dataTables" class="table table-striped table-bordered" cellspacing="0" width="100%">
                <thead>
                    <tr>
                        <th>Delivery Number</th>
                        <th>Ekspedisi</th>
                        <th>Periode</th>
                        <th>Dibuat Oleh</th>
                        <th>Tgl Input</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            $('#dataTables').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('logistic.insurance_cargo.datatables') }}',
                "pageLength": 50,
                columns: [
                    {data: 'doc_no', name: 'doc_no'},
                    {data: 'expedition', name: 'expeditions.name', searchable: true},
                    {data: 'period', name: 'period', searchable: false},
                    {data: 'created', name: 'users.name', searchable: true},
                    {data: 'created_at', name: 'created_at', searchable: false},
                    {data: 'status', name: 'status', searchable: false},
                    {data: 'action', name: 'action', orderable: false, searchable: false}
                ],
                "order": [[ 4, "desc" ]]

            });

        });
    </script>
@stop