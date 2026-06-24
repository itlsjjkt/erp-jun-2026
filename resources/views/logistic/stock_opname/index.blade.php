@extends('layouts.app')

@section('page-header')
    Stock Opname
@endsection

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item active" aria-current="page">Stock Opname</li>
    </ol>
@endsection

@section('content')
<style>
    #imagePreviewContainer {
        display: none;
        position: fixed;
        z-index: 9999;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background-color: rgba(0,0,0,0.85);
        justify-content: center;
        align-items: center;
        cursor: zoom-out;
    }

    #imagePreviewContainer img {
        max-width: 90vw;
        max-height: 90vh;
        border-radius: 10px;
        box-shadow: 0 0 10px #000;
    }
</style>

<div class="row mB-40">
    <div id="imagePreviewContainer" onclick="closeImagePreview()">
        <img id="previewImage" src="" alt="Preview">
    </div>
    <div class="col-sm-12">
        <div class="bgc-white p-20 bd">

            <div class="table-responsive mt-4">
                <table id="dataTables" class="table table-bordered" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th style="min-width: 300px;">Product</th>
                            <th style="width:75px">Qty SOH</th>
                            <th style="width:75px">Qty Aktual</th>
                            <th style="width:80px">Satuan</th>
                            <th style="width:150px">Notes</th>
                            <th style="width:80px">Status</th>
                            <th style="width:200px">Scan By</th>
                            <th style="width:200px">Tgl Buat</th>
                            <th style="width:50px">Action</th>
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
                "pageLength": 50,
                ajax: '{{ route('logistic.stock_opname.datatables') }}',
                columns: [
                    {data: 'produk', name: 'master_item_products.name'},
                    {data: 'stock_onhand', name: 'inventory_stock_opnames.stock_onhand'},
                    {data: 'actual_qty', name: 'inventory_stock_opnames.actual_qty'},
                    {data: 'measure', name: 'measures.name'},
                    {data: 'note', name: 'inventory_stock_opnames.note'},
                    {data: 'status_difference', name: 'inventory_stock_opnames.status_difference'},
                    {data: 'scandby', name: 'users.name'},
                    {data: 'created_at', name: 'inventory_stock_opnames.created_at'},
                    {data: 'action', name: 'action', searchable: false},
                    {data: 'companyCode', name: 'companies.code', visible:false },
                    {data: 'produk_pn', name: 'master_item_products.part_number', visible:false },
                    {data: 'produk_code', name: 'master_item_products.code', visible:false },
                    {data: 'doc_no', name: 'inventory_stock_opnames.doc_no', visible:false},
                    {data: 'location', name: 'locations.name', visible:false},

                ],
                "order": [[ 7, "desc" ]]
            });

        });
    </script>
    <script>
        function previewImage(url) {
            document.getElementById('previewImage').src = url;
            document.getElementById('imagePreviewContainer').style.display = 'flex';
        }

        function closeImagePreview() {
            document.getElementById('imagePreviewContainer').style.display = 'none';
        }

    </script>
@stop
