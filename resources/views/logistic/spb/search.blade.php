@extends('layouts.app')

@section('page-header')
    Surat Pengantar Barang
@endsection

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('logistic.spb.index') }}">Surat Pengantar Barang</a></li>
        <li class="breadcrumb-item active" aria-current="page">Search</li>
    </ol>
@endsection

@section('content')

    <div class="bgc-white bd bdrs-3 p-20 mB-20">
        <div class="row mB-20">
            <div class="col-sm-6 ">
                <a href="{{ route('logistic.spb.index') }}" class="" >
                    <i class="ti-arrow-left"></i> Kembali
                </a>
            </div>
            <div class="col-sm-6">
                <a  href="#" data-toggle="collapse" data-target="#filter" class="btn btn-outline border-dark float-right text-uppercase fsz-sm fw-600">
                    FILTER | EXPORT DATA
                </a>
            </div>
        </div>

        <div class="collapse mB-20" id="filter" aria-expanded="false">
            <form class="form-horizontal" id="form" method='GET'>
                {{ csrf_field() }}
                <div class="bgc-white bd bdrs-3 p-20">
                    <h6>Pencarian Data</h6>
                    <hr>
                    <div class="form-group row">
                        <div class="col-sm-3">
                            <label>Tipe SPB</label>
                            {!! Form::select('type', $type, isset($data['type']) ? $data['type'] : '', ['class' => 'form-control select2', 'id'=>'type']) !!}
                        </div>
                        <div class="col-sm-3">
                            <label>Cost SPB</label>
                            {!! Form::select('company_id', $company, isset($data['company_id']) ? $data['company_id'] : '', ['class' => 'form-control select2']) !!}
                        </div>
                        <div class="col-sm-4">
                            <label>Periode</label>
                            <div class="input-group w-100">
                                <input type="text" name="start_date" class="form-control datepicker m-r-n-1" placeholder="mm/dd/yyyy" value="{{ $data['start_date'] }}">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">ke</div>
                                </div>
                                <input type="text" name="end_date"  class="form-control datepicker" placeholder="mm/dd/yyyy" value="{{ $data['end_date'] }}">
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <button type="submit" class="btn btn-info mt-4" id="btn-filter">FILTER</button>
                            <button type="submit" class="btn btn-danger mt-4" id="btn-export">EXPORT</button>
                        </div>
                    </div>
                </div>

            </form>
        </div>
        <hr>

        <p>{!! $search !!}</p>
        <table id="dataTables" class="table table-striped table-bordered" cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th>No. SPB</th>
                    <th>Tipe</th>
                    <th>Dibuat Oleh</th>
                    <th>Status</th>
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
                ajax: '{{ route('logistic.spb.datatables',$query) }}',
                "pageLength": 50,
                columns: [
                    {data: 'doc_no', name: 'doc_no'},
                    {data: 'type', name: 'type', searchable: false},
                    {data: 'created', name: 'users.name'},
                    {data: 'status', name: 'status', searchable: false},
                    {data: 'created_at', name: 'created_at', searchable: false},
                    {data: 'action', name: 'action', orderable: false, searchable: false}
                ],
                "order": [[ 4, "DESC" ]]
            });

            $('#btn-filter').click( function() {
                $('form#form').attr('action',"{{ route('logistic.spb.search') }}");
                if($('form#form').valid()){
                    $('form#form').submit();
                }
            });

            $('#btn-export').click( function(e) {
                e.preventDefault();
                $('form#form').attr('action',"{{ route('logistic.spb.export') }}");
                if($('form#form').valid()){
                    $('form#form').submit();
                }
            });

        });
    </script>
@stop