@extends('layouts.app')

@section('page-header')
    Monitoring Item DPM
@endsection

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('logistic.monitoring.item') }}">Monitoring Item DPM</a></li>
        <li class="breadcrumb-item active" aria-current="page">Pencarian</li>
    </ol>
@endsection

@section('content')
<div class="bgc-white bd bdrs-3 p-20 mB-20">
    <div class="mB-20 row">
        <div class="col-sm-6">
            <a href="{{ route('logistic.monitoring.item') }}">
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
        <form class="form-horizontal" action="{{ route('logistic.monitoring.item.search') }}" method="GET">
            {{ csrf_field() }}
            <div class="bgc-white bd bdrs-3 p-20">
                <h6>Form Pencarian</h6>
                <hr>
                <div class="form-group row">
                    <div class="col-sm-2">
                        <label>Company</label>
                        {!! Form::select('company_id', $company, request('company_id'), ['class' => 'form-control select2', 'id' => 'company_id']) !!}
                    </div>
                    <div class="col-sm-2">
                        <label>Lokasi</label>
                        @if(isAdministrator() || isAdministratorCompany() || isAdmin())
                            {!! Form::select('location_id', $location, request('location_id'), ['class' => 'form-control select2', 'id' => 'location_id']) !!}
                        @elseif(isAdministratorLocation())
                            <input type="text" readonly class="form-control" value="{{ getDataByID('locations', Auth::user()->location_id)->name ?? '-' }}">
                            <input type="hidden" name="location_id" id="location_id" value="{{ Auth::user()->location_id }}">
                        @else
                            <input type="text" readonly class="form-control" value="{{ getDataByID('locations', Auth::user()->location_id)->name ?? '-' }}">
                            <input type="hidden" name="location_id" id="location_id" value="{{ Auth::user()->location_id }}">
                        @endif
                    </div>
                    <div class="col-sm-2">
                        <label>Department</label>
                        {!! Form::select('department_id', $department, request('department_id'), ['class' => 'form-control select2', 'id' => 'department_id']) !!}
                    </div>
                    <div class="col-sm-2">
                        <label>Tipe DPM</label>
                        {!! Form::select('type_dpm', $type, $type_dpm, ['class' => 'form-control select2', 'id' => 'type_dpm']) !!}
                    </div>
                    <div class="col-sm-3">
                        <label>Periode</label>
                        <div class="input-group w-100">
                            <input type="text" name="start_date" class="form-control datepicker m-r-n-1" placeholder="mm/dd/yyyy" value="{{ request('start_date', date('m/d/Y')) }}">
                            <div class="input-group-prepend">
                                <div class="input-group-text">ke</div>
                            </div>
                            <input type="text" name="end_date" class="form-control datepicker" placeholder="mm/dd/yyyy" value="{{ request('end_date', date('m/d/Y')) }}">
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <button type="submit" class="btn btn-danger mt-4">Cari</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <p>{!! $search !!}</p>

    <table id="dataTables" class="table table-striped table-bordered" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th style="width: 300px;">Nama Barang</th>
                <th>Catatan Barang</th>
                <th style="width: 250px;">No. DPM</th>
                <th>Lokasi/Kapal</th>
                <th>Departemen</th>
                <th>Status</th>
                <th>Approval</th>
                <th>Tgl Input</th>
                <th>Aksi</th>
            </tr>
        </thead>
    </table>
</div>

<div class="modal fade" id="modalMd" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document" style="max-width: 80%; box-shadow: 0 4px 8px rgba(0,0,0,0.4);">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">TIMELINE ITEM DPM</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="modalError"></div>
                <div id="modalMdContent"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
    $(document).ready(function () {
        $('#dataTables').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('logistic.monitoring.item.search') }}',
                data: function(d) {
                    d.company_id    = '{{ request('company_id') }}';
                    d.location_id   = '{{ request('location_id') }}';
                    d.department_id = '{{ request('department_id') }}';
                    d.type_dpm      = '{{ request('type_dpm') }}';
                    d.start_date    = '{{ request('start_date') }}';
                    d.end_date      = '{{ request('end_date') }}';
                }
            },
            pageLength: 50,
            columns: [
                {data: 'product',     name: 'master_item_products.name'},
                {data: 'notes',       name: 'purchase_items.notes'},
                {data: 'no_dpm',      name: 'purchases.doc_no'},
                {data: 'location',    name: 'locations.name'},
                {data: 'department',  name: 'departments.name'},
                {data: 'status',      name: 'status', searchable: false},
                {data: 'approval',    name: 'approval', searchable: true},
                {data: 'created',     name: 'purchases.created_at'},
                {data: 'action',      name: 'action', orderable: false, searchable: false},
                {data: 'part_number', name: 'master_item_products.part_number', visible: false},
                {data: 'brand',       name: 'master_item_brands.name', visible: false},
            ],
            order: [[7, 'desc']]
        });

        $(document).on('click', '.modalMd', function (e) {
            e.preventDefault();
            var url = $(this).attr('value');

            $('#modalMdContent').html('');
            $('.modalError').html('');

            $.ajax({
                url: url,
                type: 'GET',
                dataType: 'html',
                success: function (response) {
                    $('#modalMdContent').html(response);
                    $('#modalMd').modal('show');
                },
                error: function () {
                    $('.modalError').html('<div class="alert alert-danger">Failed to load history item. Please try again later.</div>');
                }
            });
        });
    });
</script>
@endsection