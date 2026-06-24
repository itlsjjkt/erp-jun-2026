@extends('layouts.app')

@section('page-header')
    Kartu Stock
@stop

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('logistic.inventory.index') }}">Inventory</a></li>
        <li class="breadcrumb-item active" aria-current="page">Kartu Stock</li>
    </ol>
@endsection


@section('content')
<div class="row mB-40">

	<div class="col-sm-12">
        <div class="bgc-white p-30 bd">
            <a href="#" class="btn btn-outline border-dark fsz-sm fw-600  float-right mr-2 " data-toggle="collapse" data-target="#filter">
                <i class="fa fa-file-excel-o text-success"></i> Export
            </a>
            <h6><a class="float-left" href="{{ route('logistic.inventory.index') }}"><i class="ti-arrow-left mR-10"></i></a> Inventory</h6>
            <hr class="mB-30">
            <div class="collapse mB-20" id="filter" aria-expanded="false">
                <form class="form-horizontal" action="{{ route('logistic.inventory.history.export',$inventory->id)}}" method='GET'>
                    {{ csrf_field() }}
                    <div class="bgc-white bd bdrs-3 p-20">
                        <h6>Form Export </h6>
                        <hr>
                        <div class="form-group row">
                            <div class="col-sm-5">
                                <div class="input-group w-100">
                                    <input type="text" name="start_date" class="form-control datepicker m-r-n-1" placeholder="mm/dd/yyyy">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text">ke</div>
                                    </div>
                                    <input type="text" name="end_date"  class="form-control datepicker" placeholder="mm/dd/yyyy">
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <button type="submit" class="btn btn-danger text-uppercase fsz-sm fw-600">Export</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
                <div class="row">
                    <div class="col-sm-6">
                         <div class="row">
                            <label class="col-sm-3">Inventory ID </label>
                            <div class="col-sm-4">: {{ $inventory->id }}</div>
                        </div>
                        <div class="row">
                            <label class="col-sm-3">Nomor Rak </label>
                            <div class="col-sm-4">: {{ $inventory->code_rack }}</div>
                        </div>
                        <div class="row">
                            <label class="col-sm-3">Nama Produk </label>
                            <div class="col-sm-6">: {{ $inventory->productCode }} - {{ $inventory->productName }}
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-sm-3">PN/SPEC </label>
                            <div class="col-sm-4">: {{ $inventory->productPartNumber }}</div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="row">
                            <label class="col-sm-3">Lokasi Warehouse </label>
                            <div class="col-sm-4">: {{ $inventory->location }}  </div>
                        </div>
                        <div class="row">
                            <label class="col-sm-3">Stock </label>
                            <div class="col-sm-2">: <span class="font-weight-bold fsize-14"> {{ $inventory->stock_onhand }} </span> </div>
                        </div>
                        <div class="row">
                            <label class="col-sm-3">Satuan</label>
                            <div class="col-sm-4">: {{ $inventory->unit }}</div>
                        </div>
                    </div>
                </div>
              
                <hr>
                <table id="dataTables" class="table table-striped table-bordered" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th rowspan="2" style="width:150px">TGL INPUT</th>
                            <th rowspan="2" style="width:300px">NO DOKUMEN</th>
                            <th rowspan="2">DESKRIPSI</th>
                            <th class="text-center" colspan="4">QTY</th>
                        </tr>
                        <tr>
                            <th class="text-center" style="width:100px">STOCK AWAL</th>
                            <th class="text-center" style="width:100px">IN</th>
                            <th class="text-center" style="width:100px">OUT</th>
                            <th class="text-center" style="width:100px">STOCK</th>
                        </tr>
                    </thead>
                </table>
		</div>  
	</div>
</div>
	
@stop

@section('js')
    <script>
        $(document).ready(function() {
      
            $('#dataTables').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('logistic.inventory.history.datatables',['id' => Hashids::encode($inventory->id)]) }}',
                columnDefs: [
                    {
                        targets: [3,4,5,6],
                        className: 'text-center'
                    }
                ],
                columns: [
                    {data: 'created_at', name: 'created_at', searchable: false, orderable: false},
                    {data: 'doc_no', name: 'doc_no', searchable: false, orderable: false},
                    {data: 'description', name: 'description', searchable: false, orderable: false},
                    {data: 'stock_awal', name: 'stock_awal', searchable: false, orderable: false},
                    {data: 'qty_in', name: 'qty_in', searchable: false, orderable: false},
                    {data: 'qty_out', name: 'qty_out', searchable: false, orderable: false},
                    {data: 'stock', name: 'stock', searchable: false, orderable: false},
                ]
            });

        });
    </script>
@stop