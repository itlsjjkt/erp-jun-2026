@extends('layouts.app')

@section('page-header')
    Daftar Harga Terakhir 
@endsection

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item active" aria-current="page">Daftar Harga Terakhir</li>
    </ol>
@endsection

@section('content')

<div class="row mB-40">
 
    <div class="col-sm-12">
        <div class="bgc-white p-20 bd">


            <div class="bgc-white bd bdrs-3 p-20 mB-20">
                <table id="dataTables" class="table table-striped table-bordered" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th>Nomor PO</th>
                            <th>Nama Supplier</th>
                            <th>Nama Barang</th>
                            <th>PN/SPEC</th>
                            <th>Merek</th>
                            <th>Harga Terakhir</th>
                            <th>Pembelian Terakhir</th>
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
        $('#dataTables').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route('purchasing.itemslatestprice.datatables') }}',
            columns: [
		        {data: 'po_no', name: 'po_no'},
                {data: 'suppliername', name: 'suppliername'},
                {data: 'productname', name: 'productname'},
		        {data: 'partnumber', name: 'partnumber'},
		        {data: 'merek', name: 'merek'},
                {data: 'price', name: 'price', render: $.fn.dataTable.render.number( '.', ','), className: "text-right"},
                {data: 'tanggal', name: 'tanggal', searchable: false}
            ],
            "order": [[ 0, "asc" ]]
        });
    });
</script>
@stop
