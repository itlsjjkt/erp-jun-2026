@extends('layouts.app')

@section('page-header')
  Surat Pengantar Barang
@endsection

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item active" aria-current="page">Surat Pengantar Barang</li>
    </ol>
@endsection

@section('content')
<div class="bgc-white bd bdrs-3 p-20 mB-20">

    <div class="mB-20">
        @php
            use Illuminate\Support\Facades\Gate;
        @endphp
        @if(!GATE::allows('spb_monitoring'))
            <a href="{{ route('logistic.spb.list') }}" class="btn btn-success text-uppercase fsz-sm fw-600">
                <i class="ti-plus"></i> Input SPB
            </a>
            <a  href="#" data-toggle="collapse" data-target="#filter" class="btn btn-outline border-dark float-right text-uppercase fsz-sm fw-600">
                EXPORT DATA
            </a>
        @endif
    </div>
    <div class="collapse mB-20" id="filter" aria-expanded="false">
        <form class="form-horizontal"id="form" method='GET'>
            {{ csrf_field() }}
            <div class="bgc-white bd bdrs-3 p-20">
                <h6>Pencarian Data</h6>
                <hr>
                <div class="form-group row">
                    <div class="col-sm-3">
                        <label>Tipe SPB</label>
                        {!! Form::select('type', $type, old('type'), ['class' => 'form-control select2', 'id'=>'type']) !!}
                    </div>
                    <div class="col-sm-3">
                        <label>Cost SPB</label>
                        {!! Form::select('company_id', $company, old('company_id'), ['class' => 'form-control select2']) !!}
                    </div>
                    <div class="col-sm-4">
                        <label>Periode</label>
                        <div class="input-group w-100">
                            <input type="text" name="start_date"  class="form-control datepicker m-r-n-1" placeholder="mm/dd/yyyy" id="inlineDate" value="{{ date('m/d/Y') }}">
                            <div class="input-group-prepend">
                                <div class="input-group-text">ke</div>
                            </div>
                            <input type="text" name="end_date"  class="form-control datepicker" placeholder="mm/dd/yyyy" id="inlineDate" value="{{ date('m/d/Y') }}">
                        </div>
                    </div>
                    <div class="col-sm-3">
                        {{-- <button type="submit" class="btn btn-info mt-4" id="btn-filter">FILTER</button> --}}
                        <button type="submit" class="btn btn-danger mt-4" id="btn-export">EXPORT</button>
                    </div>
                </div>
            </div>

        </form>
    </div>

    
    <div class="table-responsive mt-5">
        <div class="alert alert-info">
            <b>INFORMASI</b>: Halaman ini menampilkan Data SPB tahun berjalan. Gunakan fitur pencarian untuk melihat Histori SPB dengan status lainnya.
        </div>
        <table id="dataTables" class="table table-striped table-bordered" cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th>No. SPB</th>
                    <th>Tipe</th>
                    <th>Dibuat Oleh</th>
                    <th>Status</th>
                    <th>Tgl Input</th>
                    <th></th>
                </tr>
            </thead>
        </table>
    </div>
</div>

<div class="modal fade" id="modalSetSelesai" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">SET SELESAI SPB <span id="doc_spb"></span></h5>
                <button type="button" class="close close_modal_set" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="detailSetSelesai"></div>
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
                ajax: '{{ route('logistic.spb.datatables') }}',
                "pageLength": 50,
                columns: [
                    {data: 'doc_no', name: 'doc_no'},
                    {data: 'type', name: 'type', searchable: true},
                    {data: 'created', name: 'users.name'},
                    {data: 'status', name: 'status', searchable: false},
                    {data: 'created_at', name: 'created_at', searchable: false},
                    {data: 'action', name: 'action', orderable: false, searchable: false}
                ],
                "order": [[ 4, "DESC" ]]
            });


            $('#btn-filter').click( function() {
                $('form#form').attr('action',"{{ route('logistic.spb.search') }}");
                if($('form#form').valid()){
                    $('form#form').submit();
                }
            });

            $('#btn-export').click( function(e) {
                e.preventDefault();
                $('form#form').attr('action',"{{ route('logistic.spb.export') }}");
                if($('form#form').valid()){
                    $('form#form').submit();
                }
            });
        });

        $(document).on('click', '.btn_set_done', function() {
            var spb_id = $(this).data('id');
            var doc_spb = $(this).data('doc');
            var url_item = "{{route('logistic.spb.getItem', ['id' => ':id']) }}".replace(':id',spb_id);
            $.ajax({
                url: url_item,
                method: 'GET',
                success: function(response){
                    var itemsHtml = '';
                    response.forEach(function(item) {
                        itemsHtml += `
                            <tr>
                                <td>${item.product} <br>
                                    <small>
                                        PN/Spec : ${item.productPartNumber ?? '-'} <br>
                                        Brand   : ${item.productBrand ?? '-'}
                                    </small>
                                </td>
                                <td>${item.qtyKoli}</td>
                                <td>${item.measure}</td>
                            </tr>
                        `;
                    });
                    $('#detailSetSelesai').html(`
                        <form action="{{ route('logistic.spb.set_done', ['id' => 'ID_PLACEHOLDER']) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="row" style="margin-bottom: 20px;">
                                <div class="col-6">
                                    <div style="font-weight: bold; margin-right: 10px; min-width: 120px;">Diterima Oleh <span class="text-danger">*</span> :</div>
                                    <div style="flex-grow: 1; text-align: left;">
                                        <input required class="form-control" name="receipt_by" type="text" value="" />
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div style="font-weight: bold; margin-right: 10px; min-width: 120px;">Tanggal Penerimaan <span class="text-danger">*</span> :</div>
                                    <div style="flex-grow: 1; text-align: left;">
                                        <input required class="form-control" name="receipt_date" type="date" value="" />
                                    </div>
                                </div>
                            </div>
                            <div style="margin-bottom: 20px;">
                                <div style="font-weight: bold; margin-right: 10px; min-width: 120px;">Catatan Penerimaan :</div>
                                <div style="flex-grow: 1; text-align: left;">
                                    <input id="receipt_notes" type="hidden" name="receipt_notes" value="" class="form-control">
                                    <trix-editor placeholder="Input catatan penerimaan..." input="receipt_notes"></trix-editor>
                                </div>
                            </div>
                            <hr>
                            <div style="margin-bottom: 20px;">
                                <div style="font-weight: bold; margin-right: 10px; min-width: 120px;">Detail Item ${doc_spb} :</div>
                                <div style="flex-grow: 1; text-align: left;">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Product</th>
                                                <th>Qty</th>
                                                <th>Satuan</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            ${itemsHtml}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div style="display: flex; justify-content: flex-end; margin-top: 10px;">
                                <button class="btn btn-primary" type="submit">Set Selesai</button>
                            </div>
                        </form>
                    `);

                    $('#detailSetSelesai form').attr('action', function(i, val) {
                        return val.replace('ID_PLACEHOLDER', spb_id);
                    });
                },
                error: function() {
                    $('#detailSetSelesai').html('<p>Error loading SPB details.</p>');
                }
            })
        });
    </script>
@stop