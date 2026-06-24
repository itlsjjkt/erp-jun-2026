@extends('layouts.app')

@section('page-header')
    Adjustment Stock 
@stop

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('logistic.inventory.index') }}">Inventory</a></li>
        <li class="breadcrumb-item active" aria-current="page">Adjustment Stock </li>
    </ol>
@endsection


@section('content')
<div class="row mB-40">

	<div class="col-sm-12">
        <div class="bgc-white p-30 bd">
            <a href="{{ route('logistic.inventory.adjustment.add',['id' => Hashids::encode($inventory->id)]) }}" class="btn btn-success font-weight-bold btn-sm float-right" >
               INPUT ADJUSTMENT
            </a>
            <h6><a class="float-left" href="{{ route('logistic.inventory.index') }}"><i class="ti-arrow-left mR-10"></i></a> Inventory</h6>
            <hr class="mB-30">
                <div class="row">
                    <label class="col-sm-2">Inventory ID </label>
                    <div class="col-sm-4">: {{ $inventory->id }}</div>
                </div>
                <div class="row">
                    <label class="col-sm-2">Nomor Rak </label>
                    <div class="col-sm-4">: {{ $inventory->code_rack }}</div>
                </div>
                <div class="row">
                    <label class="col-sm-2">Nama Produk </label>
                    <div class="col-sm-6">: {{ $inventory->productCode }} - {{ $inventory->productName }}
                    </div>
                </div>
                <div class="row">
                    <label class="col-sm-2">PN/SPEC </label>
                    <div class="col-sm-4">: {{ $inventory->productPartNumber }}</div>
                </div>
                <div class="row">
                    <label class="col-sm-2">Lokasi Warehouse </label>
                    <div class="col-sm-4">: {{ $inventory->location }}  </div>
                </div>
                <div class="row">
                    <label class="col-sm-2">Stock </label>
                    <div class="col-sm-2">: <span class="font-weight-bold bd p-10 fsz-lg">{{ $inventory->stock_onhand }}</span> {{ $inventory->unit }}</div>
                </div>
              
                <hr>
                <table id="dataTables" class="table table-striped table-bordered" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Qty Awal</th>
                            <th>Qty Fisik (onHand)</th>
                            <th>Reason</th>
                            <th>Nama Pemeriksa</th>
                            <th>Tanggal Input</th>
                            <th>Action</th>
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
                ajax: '{{ route('logistic.inventory.adjustment.datatables',['id' => Hashids::encode($inventory->id)]) }}',
                columns: [
                    {data: 'doc_no', name: 'doc_no'},
                    {data: 'qty_awal', name: 'qty_awal', searchable: false},
                    {data: 'qty_fisik', name: 'qty_fisik', searchable: false},
                    {data: 'reason', name: 'reason', searchable: false},
                    {data: 'operator', name: 'operator'},
                    {data: 'created_at', name: 'created_at', searchable: false},
                    {data: 'action', name: 'action', searchable: false},
                ],
                "order": [[ 5, "desc" ]]
            });

           
        });
        function printExternal(url) {
            var printWindow = window.open(url, 'Print', 'left=200, top=200, width=950, height=800, toolbar=0, resizable=0');
            printWindow.addEventListener('load', function() {
                printWindow.print();
            }, true);
        }
    </script>
@stop