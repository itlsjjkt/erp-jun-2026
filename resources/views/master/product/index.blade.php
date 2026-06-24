@extends('layouts.app')

@section('page-header')
    Produk
@endsection

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item active" aria-current="page">Produk</li>
    </ol>
@endsection

@section('content')

<div class="row mB-40">

    <div class="col-sm-12">
        <div class="bgc-white p-20 bd">

            <div class="mB-20 row">
                <div class="col-lg-6">
                    @can('master.product-action')
                        <a href="{{ route('master.item_products.create') }}" class="btn btn-info text-uppercase fsz-sm fw-600">
                            <i class="ti-plus"></i> Tambah
                        </a>
                        <a href="{{ route('master.item_products.create_multiple') }}" class="btn btn-info text-uppercase fsz-sm fw-600">
                            <i class="ti-plus"></i> Tambah Multiple
                        </a>
                    @endcan
                </div>
                <div class="col-lg-6">
                    <a  href="#" data-toggle="collapse" data-target="#filter" class="btn btn-outline border-dark mL-3 float-right text-uppercase fsz-sm fw-600" aria-controls="filter">
                        <i class="ti-search"></i> Filter | Export Data
                    </a>
                    @can('master.product-action')
                        <a href="{{ route('master.item_products.upload') }}" class="btn btn-outline border-dark float-right text-uppercase fsz-sm fw-600">
                            <i class="fa fa-file-excel-o text-danger icon-lg"></i> Upload
                        </a>
                    @endif
                </div>
            </div>
            <hr class="mB-30">

            <div class="collapse mB-20 multi-collapse" id="filter" aria-expanded="false">
                <form class="form-horizontal" method='GET' id="form">
                    {{ csrf_field() }}
                    <div class="bd bdrs-3 p-20">
                        <div class="row">
                            <div class="col-sm-3">
                                <label>Kategori</label>
                                {!! Form::select('item_id', $item, old('item_id'), ['class' => 'form-control select2 item']) !!}
                            </div>
                            <div class="col-sm-3">
                                <label>Brand</label>
                                {!! Form::select('brand_id', $brand, old('brand_id'), ['class' => 'form-control select2 brand']) !!}
                            </div>
                            <div class="col-sm-3">
                                @php
                                    use Illuminate\Support\Facades\Gate;
                                @endphp
                                @if(GATE::allows('master.product-action'))
                                    <button type="submit" class="btn btn-success mt-4" id="btn-export">Export</button>
                                @endif
                                <button type="submit" class="btn btn-info mt-4"  id="btn-filter">Filter</button>
                            </div>
                        </div>
                    </div>

                </form>
            </div>

            <table id="dataTables" class="table table-striped table-bordered" cellspacing="0" width="100%">
                <thead>
                    <tr>
                        <th style="width:120px">Code</th>
                        <th>Name</th>
                        <th>PN/SPEC</th>
                        <th>Brand</th>
                        <th>Satuan</th>
                        <th>Status</th>
                        <th>Dibuat Oleh</th>
                        <th>Tgl Update</th>
                        <th>Action</th>
                    </tr>
                </thead>

            </table>
            
        </div>
    </div>
</div>
@endsection

@section('js')
    <script>
    $(document).ready(function() {
        $('.type_id').select2({
            placeholder: "Silahkan Pilih"
        });

        $('#dataTables').DataTable({
            pageLength: 25,
            processing: true,
            serverSide: true,
            ajax: '{{ route('master.item_products.datatables') }}',
            columns: [
                {data: 'code', name: 'code'},
                {data: 'name', name: 'name'},
                {data: 'part_number', name: 'part_number'},
                {data: 'brand', name: 'master_item_brands.name'},
                {data: 'unit', name: 'measures.name',  orderable: false, searchable: true},
                {data: 'status', name: 'status', searchable: false},
                {data: 'created_by', name: 'users.name'},
                {data: 'updated_at', name: 'updated_at', searchable: false},
                {data: 'action', name: 'action', orderable: false, searchable: false}
            ],
            "order": [[ 7, "desc" ]]
        });

        $('#btn-filter').click( function() {
            $('form#form').attr('action',"{{ route('master.item_products.search') }}");
            if($('form#form').valid()){
                $('form#form').submit();
            }
        });

        $('#btn-export').click( function(e) {
            e.preventDefault();
            $('form#form').attr('action',"{{ route('master.item_products.export') }}");
            if($('form#form').valid()){
                $('form#form').submit();
            }
        });
    });
</script>
@stop
