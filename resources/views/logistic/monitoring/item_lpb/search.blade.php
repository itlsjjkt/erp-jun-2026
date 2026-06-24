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
            <div class="bd p-20">
                <div class="form-group row">
                    <div class="col-auto">
                        <label>Status</label>
                        {!! Form::select('status', $status, old('status'), ['class' => 'form-control select2']) !!}
                    </div>
                    <div class="col-auto">
                        <label>Periode</label>
                        <div class="input-group w-100">
                            <input type="text" name="start_date" class="form-control datepicker m-r-n-1" placeholder="dd/mm/yyyy"  required value="{{ date('d/m/Y')}}">
                            <div class="input-group-prepend">
                                <div class="input-group-text">ke</div>
                            </div>
                            <input type="text" name="end_date"  class="form-control datepicker" placeholder="dd/mm/yyyy"  required  value="{{ date('d/m/Y')}}">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12">
                        <input type="hidden" value="" name="statusExport">
                        <button type="submit" class="btn btn-info"  id="btn-filter">Cari</button>
                        <button type="submit" class="btn btn-success" id="btn-export-item">Export Data</button>
                    </div>
                </div>
            </div>
        </form>
    </div>


    <div class="alert alert-info">Monitoring Item LPB</div>
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

<div class="modal fade" id="exampleModalDetail" tabindex="-1" role="dialog" aria-labelledby="exampleModalLongTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" style="color: black;" id="exampleModalLongTitle">Detail Item</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <table id="dataTablesSPBP" class="table table-bordered" cellspacing="0" width="100%">
            <thead>
                <tr style="background-color: rgb(226, 226, 226);">
                    <th >No. SPB</th>
                    <th >No. DPM <span style="float: right;">[Dibuat Oleh]</span></th>
                    <th >Tgl SPB</th>
                    <th >Status SPB</th>
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
				{data: 'brand', name: 'master_item_brands.name', visible: false},
            ]
        });

        $('#btn-filter').click( function() {
            debugger;
            $('form#form').attr('action',"{{ route('logistic.monitoring_lpb.search') }}");
            if($('form#form').valid()){
                $('form#form').submit();
            }
        });
    });
</script>
@stop
