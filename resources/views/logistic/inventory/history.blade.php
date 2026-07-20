@extends('layouts.app')

@section('page-header')
    Kartu Stock
@stop

@php 
    use Illuminate\Support\Facades\Gate;
@endphp

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        @if(GATE::allows('inventory_monitoring'))
            <li class="breadcrumb-item"><a href="{{ route('logistic.monitoring.inv') }}">Monitoring Inventory</a></li>
        @else
            <li class="breadcrumb-item"><a href="{{ route('logistic.inventory.index') }}">Inventory</a></li>
        @endif
        <li class="breadcrumb-item active" aria-current="page">Kartu Stock</li>
    </ol>
@endsection


@section('content')
<div class="row mB-40">

	<div class="col-sm-12">
        <div class="bgc-white p-30 bd">
            {{-- @if(!Gate::allows('inventory_monitoring')) --}}
                <h6><a class="float-left" href="{{ route('logistic.inventory.index') }}"><i class="ti-arrow-left mR-10"></i></a> Inventory</h6>
                <a href="#" class="btn btn-outline border-dark fsz-sm fw-600  float-right mr-2 " data-toggle="collapse" data-target="#filter">
                    <i class="fa fa-file-excel-o text-success"></i> Export
                </a>
                <hr class="mB-30">
            {{-- @endif --}}
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
                            <div class="col-sm-6">
                                <button type="submit" class="btn btn-danger text-uppercase fsz-sm fw-600">Export</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
               
                <div class="row justify-content-end">
                    <div class="col-lg-6">
                        <h4 class="mb-0">{{ $inventory->productCode }} - {{ $inventory->productName }}</h4>
                        <h5 class="m-0 text-uppercase">
                            {!! getStatusInventory($inventory->stock_max, $inventory->stock_min, $inventory->stock_onhand ) !!}
                        </h5>
                    </div>
                    <div class="col-lg-6">
                        <div class="row justify-content-end">
                            <div class="col-auto border text-center pt-2">
                                MIN
                                <h3 class="font-weight-bold">{{ $inventory->stock_min }}</h3>
                            </div>
                            <div class="col-auto border text-center text-center pt-2">
                                MAX
                                <h3 class="font-weight-bold">{{ $inventory->stock_max }}</h3>
                            </div>
                            <div class="col-auto border text-center text-center pt-2">
                                ONHAND
                                <h3 class="font-weight-bold">{{ $inventory->stock_onhand }} </h3>
                            </div>
                            <div class="col-auto border text-center text-center pt-3">
                                <h3 class="font-weight-bold">{{ $inventory->unit }} </h3>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row mt-4">
                    <div class="col-sm-6">
                         <div class="row">
                            <label class="col-sm-3">Inventory ID </label>
                            <div class="col-sm-6">: {{ $inventory->id }}</div>
                        </div>
                        <div class="row">
                            <label class="col-sm-3">Nomor Rak </label>
                            <div class="col-sm-6">: {{ $inventory->code_rack }}</div>
                        </div>
                        
                        <div class="row">
                            <label class="col-sm-3">PN/SPEC </label>
                            <div class="col-sm-6">: {{ $inventory->productPartNumber }}</div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="row">
                            <label class="col-sm-3">Perusahan </label>
                            <div class="col-sm-6">: {{ $inventory->company }}  </div>
                        </div>
                        <div class="row">
                            <label class="col-sm-3">Warehouse </label>
                            <div class="col-sm-6">: {{ $inventory->location }}  </div>
                        </div>
                        <div class="row">
                            @if ($inventory->image)
                                <div class="col-sm-3">
                                    <a id="btn-image">
                                        <img src="{{ asset('storage'.$inventory->image) }}" alt="Sample Image" id="inventory-image">
                                    </a>
                                </div>
                            @endif
                        </div>
                       
                    </div>
                </div>
               
                <hr>
                <h6 class="mb-0 text-uppercase">Mutasi Data</h6>
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
        $(document).on('click', "#btn-image", function(e) {
            Swal.fire({
            title: "<br><small>{{ $inventory->productName }}</small>",
            html: `
                <img src="{{ asset('storage'.$inventory->image) }}" alt="Sample Image" style="max-height:80vh;">
            `,
            showCloseButton: true,
            showConfirmButton: false,
            });
        });

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
                "lengthChange": false,
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

            function readURL(input) {
                if (input.files && input.files[0]) {
                    var reader = new FileReader();
                    
                    reader.onload = function (e) {
                        $('#inventory-image-tag').attr('src', e.target.result);
                    }
                    reader.readAsDataURL(input.files[0]);
                }
            }

        });
    </script>
@stop