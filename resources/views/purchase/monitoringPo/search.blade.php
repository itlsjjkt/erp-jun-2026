@extends('layouts.app')

@section('page-header')
     Monitoring Item PO
@endsection

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item active" aria-current="page"> Monitoring Item PO</li>
    </ol>
@endsection

@section('content')
<div class="bgc-white bd bdrs-3 p-20 mB-20">

    <div class="mB-20 row">
        <div class="col-lg-12">
            <a href="{{ route('purchasing.monitoring_po') }}" class="nav-link float-left"><i class="ti-arrow-left"></i> Kembali</a>
            <a href="#" data-toggle="collapse" data-target="#filter"  class="btn btn-outline border-dark float-right"> <i class="ti-search icon-lg"></i> FILTER | EXPORT</a>
        </div>
    </div>
    <hr>

    <div class="col-12" aria-expanded="true" >
        <div class="collapse mB-20" id="filter" aria-expanded="false">
            <form class="form-horizontal" action="" method='GET' id="form">
                {{ csrf_field() }}
                <div class="bd p-20">
                    <div class="form-group row">
                        <div class="col-auto">
                            <label>Purchaser</label>
                            {!! Form::select('assigned_id', $purchaser, $data['assigned_id'], ['class' => 'form-control select2 assigned_id']) !!}
                        </div>
                        <div class="col-auto">
                            <label>Supplier</label>
                            {!! Form::select('supplier_id', $supplier, $data['supplier_id'], ['class' => 'form-control select2 supplier_id']) !!}
                        </div>
                        <div class="col-sm-4">
                            <label>Periode</label>
                            <div class="input-group w-100">
                                <input type="text" name="start_date" class="form-control datepicker m-r-n-1 start_date" placeholder="mm/dd/yyyy" value="{{ $data['start_date'] }}">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">ke</div>
                                </div>
                                <input type="text" name="end_date"  class="form-control datepicker end_date" placeholder="mm/dd/yyyy" value="{{ $data['end_date'] }}">
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="float-right">
                                <button type="submit" class="btn btn-danger" id="btn-filter">CARI</button>
                                {{-- <button type="submit" class="btn btn-success" id="btn-export">EXPORT DATA</button> --}}
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    <div class="alert alert-info">Monitoring Item PO digunakan untuk memonitoring item yang sudah PO</div>
        <div class="row gap-5">
            @php $countData = 0;@endphp
            @foreach($data2 as $val)
            @php
                $countData += $val->count ;
            @endphp
            @if($val->count > 0)
            <div class='col-md-2'>
                <div class="layers bd bgc-white p-20">
                    <div class="layer w-100">
                        <div class="peers ai-sb fxw-nw">
                            <div class="peer peer-greed">
                                {{ $val->name }}
                            </div>
                        </div>
                        <div class="peers ai-sb fxw-nw">
                            <div class="peer text-info fa-2x font-weight-bold">
                                {{$val->count}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
            @endforeach
            @if(Auth::user()->data_access == 1)
            <div class='col-md-2'>
                <div class="layers bd bgc-white p-20">
                    <div class="layer w-100">
                        <div class="peers ai-sb fxw-nw">
                            <div class="peer peer-greed">
                                Total Semua Data
                            </div>
                        </div>
                        <div class="peers ai-sb fxw-nw">
                            <div class="peer text-info font-weight-bold fa-2x">
                                {{$countData}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
        <br>
    <p>{!! $search !!}</p>
    <table id="dataTables" class="table table-striped table-bordered" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th style="min-width: 230px !important;">Nama Barang</th>
                <th style="max-width: 200px !important;">No PO</th>
                <th style="max-width: 230px !important;">No PR</th>
                <th style="max-width: 230px !important;">Supplier</th>
                <th style="max-width: 200px;">Harga Item</th>
                <th style="max-width: 100px;">Qty</th>
                <th style="max-width: 200px;">Purchaser</th>
                <th style="max-width: 150px;">Tgl Pembuatan PO</th>
                <th style="max-width: 100px;">Status Item</th>
                <th style="max-width: 50px;">Aksi</th>
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
            ajax: {
                url: '{{ route('purchasing.monitoring_po.datatables') }}',
                data: function(d) {
                    d.assigned_id = $('.assigned_id').val(),
                    d.supplier_id = $('.supplier_id').val(),
                    d.start_date = $('.start_date').val(),
                    d.end_date = $('.end_date').val();
                }
            },
            "pageLength": 50,
            columns: [
                { data: 'nameProduct', name: 'master_item_products.name'},
                { data: 'doc_noPo', name: 'po.doc_no'},
                { data: 'doc_noPr', name: 'purchase_requisitions.doc_no'},
                { data: 'supplierName', name: 'suppliers.name'},
                { data: 'price', name: 'po_items.price' ,orderable: false, searchable: false },
                { data: 'qty', name: 'po_items.qty' ,orderable: false, searchable: false },
                { data: 'userName', name: 'users.name'},
                { data: 'createddd', name: 'po.created_at', searchable: false },
                { data: 'statusss', name: 'po.status' , searchable: false },
                { data: 'action', name: 'action', orderable: false, searchable: false },
                { data: 'brandProduct', name: 'master_item_brands.name', visible: false },
                { data: 'partNumberProduct', name: 'master_item_products.part_number', visible: false }
            ],
            "order": [[ 7, "desc" ]]
        });

        $('#btn-filter').click(function() {
            $('form#form').attr('action', "{{ route('purchasing.monitoring_po.search') }}");
            if ($('form#form').valid()) {
                $('form#form').submit(); 
            }
        });
        $('#btn-export').click( function(e) {
            e.preventDefault();
            $('form#form').attr('action',"{{ route('purchasing.monitoring_po.export') }}");
            if($('form#form').valid()){
                $('form#form').submit();
            }
        });
    });

</script>
@stop
