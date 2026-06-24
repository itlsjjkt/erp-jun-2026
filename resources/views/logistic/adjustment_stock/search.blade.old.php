@extends('layouts.app')

@section('page-header')
    Adjustment Stock
@endsection

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('logistic.inventory.index') }}">Inventory</a></li>
        <li class="breadcrumb-item"><a href="{{ route('logistic.adjustment_stock.index') }}">Adjustment Stock</a></li>
        <li class="breadcrumb-item active" aria-current="page">Search</li>
    </ol>
@endsection

@section('content')

    <div class="bgc-white bd bdrs-3 p-20 mB-20">
        <div class="row">
            <div class="col-sm-6 ">
                <a href="{{ route('logistic.adjustment_stock.index') }}" class="nav-link" >
                    <i class="ti-arrow-left"></i> Kembali
                </a>
            </div>
            <div class="col-sm-6">
                <a href="#" data-toggle="collapse" data-target="#filter" class="btn btn-outline border-dark float-right">
                    FILTER | EXPORT DATA
                </a>
            </div>
        </div>

        <hr>

        <div class="collapse mB-20" id="filter" aria-expanded="false">
            <form class="form-horizontal" id="form" method='GET'>
                {{ csrf_field() }}
                <div class="bgc-white bd bdrs-3 p-20">
                    <h6>Form Filter | Export Data </h6>
                    <hr>
                    <div class="form-group row">
                        <div class="col-sm-3">
                            <label>Lokasi</label>
                            @if(isAdministrator() || isAdministratorCompany() || isAdmin())
                                {!! Form::select('location_id', $location, $data['location_id'], ['class' => 'form-control select2']) !!}
                            @elseif(isAdministratorLocation())
                                <input type="text" readonly class="form-control" value="{{ getDataByID('locations',Auth::user()->location_id )->name }}">
                                <input type="hidden" name="location_id" id="location_id" value="{{ Auth::user()->location_id }}">
                            @else
                                <input type="text" readonly class="form-control" value="{{ getDataByID('locations',Auth::user()->location_id)->name }}">
                                <input type="hidden" name="location_id" id="location_id" value="{{Auth::user()->location_id}}">
                            @endif
                        </div>
                        <div class="col-sm-3">
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
                            <button type="submit" class="btn btn-success mt-4" id="btn-export">EXPORT</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>  
        
        <p>{!! $search !!}</p>
        <table id="dataTables" class="table table-striped table-bordered" cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Produk</th>
                    <th>Qty Awal</th>
                    <th>Qty Fisik</th>
                    <th>Satuan</th>
                    <th>Tanggal Input</th>
                    <th>Input Oleh</th>
                    <th>Aksi</th>
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
                ajax: '{{ route('logistic.adjustment_stock.datatables',$query) }}',
                columns: [
                    {data: 'doc_no', name: 'doc_no'},
                    {data: 'productName', name: 'master_item_products.name'},
                    {data: 'qty_awal', name: 'qty_awal', searchable: false},
                    {data: 'qty_fisik', name: 'qty_fisik', searchable: false},
                    {data: 'measure', name: 'measure', searchable: false},
                    {data: 'created_at', name: 'created_at', searchable: false},
                    {data: 'created', name: 'created', searchable: false},
                    {data: 'action', name: 'action', searchable: false},
                ],
                "order": [[ 6, "desc" ]]
            });
      
            $('#btn-filter').click( function() {
                $('form#form').attr('action',"{{ route('logistic.adjustment_stock.search') }}");
                if($('form#form').valid()){
                    $('form#form').submit();
                }
            });

            $('#btn-export').click( function(e) {
                e.preventDefault();
                $('form#form').attr('action',"{{ route('logistic.adjustment_stock.export') }}");
                if($('form#form').valid()){
                    $('form#form').submit();
                }
            });

        });
    </script>
@stop