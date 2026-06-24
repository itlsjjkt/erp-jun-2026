@extends('layouts.app')

@section('page-header')
     Monitoring Item LPB
@endsection

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item active" aria-current="page"> Monitoring Item LPB</li>
    </ol>
@endsection
@section('content')
<div class="bgc-white bd bdrs-3 p-20 mB-20">

    <div class="row">
        <div class="col-sm-12">
            <a href="#" data-toggle="collapse" data-target="#export"  class="btn btn-outline border-dark float-right"><i class="ti-search icon-lg"></i>
                 FILTER | EXPORT
            </a>
        </div>
    </div>
    <hr>

    <div class="collapse mB-20" id="export" aria-expanded="false">
        <form class="form-horizontal" id="form" method='GET'>
            {{ csrf_field() }}
            <div class="p-20">
                <div class="row justify-content-end">
                    <div class="col-sm-12 text-right">
                        <input type="hidden" value="" name="statusExport">
                        <button type="submit" class="btn btn-success" id="btn-export-item" style="text-transform: none;">Export Data SOH</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="alert alert-info">
        - Monitoring item LPB digunakan untuk memonitoring IN, OUT dan SOH item LPB.
    </div>
    <table id="dataTables" class="table table-striped table-bordered" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th>Nama Item</th>
                <th>Company</th>
                <th style="text-align:center; width:100px;">IN</th>
                <th style="text-align:center; width:100px;">OUT</th>
                <th style="text-align:center; width:100px;">SOH</th>
                <th style="text-align:center;">Satuan</th>
                <th style="text-align:center;">Status</th>
                <th style="text-align:center;">Action</th>
            </tr>
        </thead>
    </table>
</div>
<div class="modal fade" id="modalShow" tabindex="-1" role="dialog" aria-labelledby="exampleModalLongTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" style="color: black;font-weight:normal;" id="exampleModalLongTitle">Data Masuk Item : <span style="font-weight: bold;" class="product_info"></span></h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <table id="dataTablesShowProduct" class="table table-bordered dataTablesShowProduct" cellspacing="0" width="100%">
            <thead>
                <tr style="background-color: rgb(226, 226, 226);">
                    <th >No. PO</th>
                    <th >No. LPB</th>
                    <th >QTY</th>
                    <th >Tgl Masuk</th>
                    <th >Dibuat Oleh</th>
                    <th >Action</th>
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
            ajax: '{{ route('logistic.monitoring.item_lpb.datatables') }}',
            "pageLength": 50,
            columns: [
                {data: 'product', name: 'master_item_products.name'},
                {data: 'company', name: 'companies.name'},
                {data: 'in', name: 'lpb_items.qty',searchable: false, orderable: false},
                {data: 'out', name: 'lpb_items.qty',searchable: false, orderable: false},
                {data: 'soh', name: 'lpb_items.qty',searchable: false, orderable: false},
                {data: 'satuanBeli', name: 'satuan_beli.name', orderable: false},
                {data: 'status', name: 'lpb_items.qty',searchable: false, orderable: false},
                {data: 'action', name: 'action',searchable: false, orderable: false},
				{data: 'code', name: 'master_item_products.code', visible: false},
				{data: 'part_number', name: 'master_item_products.part_number', visible: false},
				{data: 'brand', name: 'master_item_brands.name', visible: false},
            ]
        });

        $('#btn-export-item').click( function() {
            $('form#form').attr('action',"{{ route('logistic.monitoring_lpb.export') }}");
            if($('form#form').valid()){
                $('form#form').submit();
            }
        });

        $(document).on('click', '.btnShow', function() {
            var product_id = $(this).data('product_id');
            var company_id = $(this).data('company_id');
            var product_info = $(this).data('product_info');

            $('.product_info').text(product_info);

            // Hancurkan DataTable jika sudah ada
            if ($.fn.dataTable.isDataTable('.dataTablesShowProduct')) {
                $('.dataTablesShowProduct').DataTable().clear().destroy();
            }

            // Inisialisasi DataTable
            $('.dataTablesShowProduct').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('logistic.monitoring_lpb.data_item_lpb', ['product_id' => '__product_id__', 'company_id' => '__company_id__']) }}'
                        .replace('__product_id__', product_id)
                        .replace('__company_id__', company_id),
                    error: function(xhr, error, thrown) {
                        console.error('Error fetching data: ', error);
                    }
                },
                pageLength: 10,
                autoWidth: false,
                columns: [
                    { data: 'no_po', name: 'po.doc_no', orderable: false },
                    { data: 'doc_no', name: 'lpb.doc_no', orderable: false },
                    { data: 'qty', name: 'lpb_items.qty', orderable: false },
                    { data: 'created_at', name: 'lpb.created_at', orderable: false },
                    { data: 'created_by', name: 'lpb.created_by', orderable: false },
                    { data: 'status', name: 'lpb.status', orderable: false, searchable: false }
                ],
                order: [[3, "DESC"]]
            });
        });
    });
</script>
@stop
