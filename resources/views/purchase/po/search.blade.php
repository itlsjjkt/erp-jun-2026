@extends('layouts.app')

@section('page-header')
    Purchase Order <small>(PO)</small>
@endsection

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('purchasing.po.index') }}"> Purchase Order</a></li>
        <li class="breadcrumb-item active" aria-current="page">Search</li>
    </ol>
@endsection

@section('content')

    <div class="bgc-white bd bdrs-3 p-20 mB-20">
     <div class="row">
            <div class="col-sm-6 ">
                <a href="{{ route('purchasing.po.index') }}" class="nav-link"> <i class="ti-arrow-left"></i> Kembali  </a>
            </div>
            <div class="col-sm-6">
                 @php
                    use Illuminate\Support\Facades\Gate;
                @endphp
                @if(!Gate::allows('po_monitoring'))
                    <a href="#" onclick="printAll()" class="btn btn-outline border-dark float-right  ml-2">
                        <i class="ti-printer icon-lg"></i> Print
                    </a>
                    <a href="#" class="btn btn-outline border-dark float-right" data-toggle="collapse" data-target="#filter">
                        <i class="ti-search icon-lg"></i> FILTER  | EXPORT
                    </a>
                @endif
            </div>
        </div>
        <hr>

        <div class="collapse mB-20" id="filter" aria-expanded="false">
            <form class="form-horizontal" id="form" method='GET'>
                {{ csrf_field() }}
                <div class="bd p-20">
                    <div class="form-group row">
                        @if(Auth::user()->data_access == 1)
                            <div class="col-2">
                                <label>Purchaser</label>
                                {!! Form::select('purchaser_id', $purchaser, $data['purchaser_id'], ['class' => 'form-control select2']) !!}
                            </div>
                        @endif
                        <div class="col-2">
                            <label>Project</label>
                            {!! Form::select('project_id', $project, $data['project_id'], ['class' => 'form-control select2']) !!}
                        </div>
                        <div class="col-2">
                            <label>Department</label>
                            {!! Form::select('department_id', $department, old('department_id'), ['class' => 'form-control select2']) !!}
                        </div>
                        <div class="col-2">
                            <label>Supplier</label>
                            {!! Form::select('supplier_id', $supplier, $data['supplier_id'], ['class' => 'form-control select2']) !!}
                        </div>
                        <div class="col-3">
                            <label>Periode</label>
                            <div class="input-group w-100">
                                <input type="text" name="start_date" class="form-control datepicker m-r-n-1" placeholder="mm/dd/yyyy"  required value="{{ $data['start_date'] }}">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">ke</div>
                                </div>
                                <input type="text" name="end_date"  class="form-control datepicker" placeholder="mm/dd/yyyy"  required  value="{{ $data['end_date'] }}">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <input type="hidden" value="" name="statusExport">
                            <button type="submit" class="btn btn-info"  id="btn-filter">CARI</button>
                            <button type="submit" class="btn btn-success" id="btn-export-item">EXPORT ITEM</button>
                            <button type="submit" class="btn btn-warning" id="btn-export">EXPORT PO</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <p>{!! $search !!}</p>
        <div class="table-responsive mt-2">
            <table id="dataTables" class="table table-striped table-bordered" cellspacing="0" width="100%">
                <thead>
                    <tr>
                        <th> </th>
                        <th>No. PO</th>
                        <th>No. PR</th>
                        <th>Supplier</th>
                        @if(Auth::user()->data_access == 1)
                            <th>Purchaser</th>
                        @endif
                        <th>Tgl Update</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    <div class="modal fade" id="modalUpdateTimePO" tabindex="-1" role="dialog" aria-labelledby="modalUpdateTimePOLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalUpdateTimePOLabel">UPDATE PO</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="detailFormEdit"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalCancelPo2" tabindex="-1" role="dialog" aria-labelledby="modalCancelPo2Label" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalCancelPo2Label">CANCEL PO</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="detailFormCancelPo2"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalMdEmail" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
        data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-lg" role="document" style="box-shadow: 0 4px 8px rgba(0, 0, 0, 0.4);">
            <div class="modal-content" style="border: 2px solid #0088c1; border-radius: 10px;">
                <div class="modal-header" style="background-color: #0088c1">
                    <h5 class="modal-title" style="color: white" id="modalMdEmailTitle">PUSH MAIL PO</h5>
                    <button type="button" class="close" style="color: white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" style="margin: 20px; max-height: 600px; overflow-y: auto;">
                    <div class="modalError"></div>
                    <div id="modalMdEmailContent"></div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('js')
    <script>
        window.addEventListener("pageshow", function (event) {
            if (event.persisted) {
            window.location.reload();
            }
        });
$(document).on('click', '.btn-update-time-po', function() {
            var po_id = $(this).data('id');
            var url = "{{ route('purchasing.po.getUpdateDate', ['id' => ':id']) }}".replace(':id', po_id);
            $.ajax({
                url: url,
                method: 'GET',
                success: function(response) {
                    $('#detailFormEdit').html(`
                        <div style="display: flex; justify-content: flex-start; margin-bottom: 10px;">
                            <span style="font-weight: bold; margin-right: 10px; min-width: 120px;">NO PO</span>
                            <span style="flex-grow: 1; text-align: left;">: ${response.doc_no}</span>
                        </div>
                        <br>
                        <div style="display: flex; justify-content: flex-start; margin-bottom: 10px;">
                            <span style="font-weight: bold; margin-right: 10px; min-width: 120px;"></span>
                            <span style="flex-grow: 1; text-align: left;">: mm/dd/yyyy</span>
                        </div>
                        <form action="{{ route('purchasing.po.update_date', ['id' => 'ID_PLACEHOLDER']) }}" method="POST" enctype="multipart/form-data">
                            @method('PUT')
                            @csrf
                            <input name="po_id" type="hidden" value="${response.id}" />
                            <div style="display: flex; justify-content: flex-start; margin-bottom: 10px;">
                                <span style="font-weight: bold; margin-right: 10px; min-width: 120px;">Tanggal Kirim</span>
                                <span style="flex-grow: 1; text-align: left;">
                                    <input class="form-control" name="delivery_date" type="date" value="${response.delivery_date}" />
                                </span>
                            </div>
                            <div style="display: flex; justify-content: flex-start; margin-bottom: 10px;">
                                <span style="font-weight: bold; margin-right: 10px; min-width: 120px;">Estimasi Tiba</span>
                                <span style="flex-grow: 1; text-align: left;">
                                    <input class="form-control" name="estimated_receipt" type="date" value="${response.estimated_receipt}" />
                                </span>
                            </div>
                            <div style="display: flex; justify-content: flex-end; margin-top: 10px;">
                                <button class="btn btn-danger" type="submit">Update</button>
                            </div>
                        </form>
                    `);

                    $('#detailFormEdit form').attr('action', function(i, val) {
                        return val.replace('ID_PLACEHOLDER', response.id);
                    });
                },
                error: function() {
                    $('#detailFormEdit').html('<p>Error loading PO details.</p>');
                }
            });
        });

        $(document).ready(function() {
            var rows_selected = [];
            var table =  $('#dataTables').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('purchasing.po.datatables', $query) }}',
                "pageLength": 50,
                'columnDefs': [{
                    'targets': 0,
                    'searchable': false,
                    'orderable': false,
                    'width': '1%',
                    'className': 'text-center',
                    'render': function (data, type, full, meta){
                        return '<input type="checkbox" class="magic-checkbox po_id" value="'+ data +'" ><label></label>';
                    }}
                ],
                columns: [
                    {data: 'id'},
                    {data: 'doc_no', name: 'doc_no'},
                    {data: 'no_pr', name: 'purchase_requisitions.doc_no'},
                    {data: 'supplier', name: 'suppliers.name'},
                    @if(Auth::user()->data_access == 1)
                        {data: 'created', name: 'users.name'},
                    @endif
                    {data: 'created_at', name: 'created_at', searchable: false},
                    {data: 'status', name: 'status', searchable: false},
                    {data: 'action', name: 'action', orderable: false, searchable: false}
                ],
                @if(Auth::user()->data_access == 1)
                    "order": [[ 5, "desc" ]],
                @else
                    "order": [[ 4, "desc" ]],
                @endif
                'rowCallback': function(row, data, dataIndex){
                    var rowId = data['id'];
                    if($.inArray(rowId, rows_selected) !== -1){
                        $(row).find('input[type="checkbox"]').prop('checked', true);
                        $(row).addClass('selected');
                    }
                }

            });


            $('#dataTables tbody').on('click', 'input[type="checkbox"]', function(e){
                var $row = $(this).closest('tr');
                var data = table.row($row).data();
                var rowId = data['id'];
                var index = $.inArray(rowId, rows_selected);
                if(this.checked && index === -1){
                    rows_selected.push(rowId);
                } else if (!this.checked && index !== -1){
                    rows_selected.splice(index, 1);
                }
                if(this.checked){
                    $row.addClass('selected');
                } else {
                    $row.removeClass('selected');
                }
                $('#showSelected').text('');
                $('#showSelected').text('Data Selected:'+ rows_selected.length);
                e.stopPropagation();
            });


            $('#dataTables').on('click', 'tbody td, thead th:first-child', function(e){
                $(this).parent().find('input[type="checkbox"]').trigger('click');
            });

            $(document).on('click', ".btn-revision", function(e) {

                var _this = $(this);
                var form = _this.parents('form');

                form.validate({
                    onfocusout: false,
                    invalidHandler: function(form, validator) {
                        var errors = validator.numberOfInvalids();
                        if (errors) {
                            validator.errorList[0].element.focus();
                        }
                    }
                });

                e.preventDefault();
                if (form.valid()) {
                    Swal.fire({
                        title: 'Konfirmasi', // Opération Dangereuse
                        text: 'Data PO akan dikembalikan ke Purchaser untuk diperbaiki, Apakah anda yakin melanjutkan ini?', // Êtes-vous sûr de continuer ?
                        type: 'question',
                        showCancelButton: true,
                        confirmButtonColor: 'null',
                        cancelButtonColor: 'null',
                        confirmButtonClass: 'btn btn-danger',
                        cancelButtonClass: 'btn btn-primary',
                        confirmButtonText: 'Ya, lanjut', // Oui, sûr
                        cancelButtonText: 'Batal', // Annuler
                    }).then(res => {
                        if (res.value) {
                        _this.closest("form").submit();
                        }
                    });
                }

            });

            $(document).on('click', ".btn-cancel", function(e) {

            var _this = $(this);
            var form  = _this.parents('form');
            var input = _this.parents('form').find('input[name*="isPR"]');

            form.validate({
                onfocusout: false,
                invalidHandler: function(form, validator) {
                    var errors = validator.numberOfInvalids();
                    if (errors) {
                        validator.errorList[0].element.focus();
                    }
                }
            });

            e.preventDefault();
            if (form.valid()) {
                Swal.fire({
                    title: 'Konfirmasi', // Opération Dangereuse
                    text: 'Anda Yakin untuk melakukan Cancel PO?', // Êtes-vous sûr de continuer ?
                    input: 'checkbox',
                    inputValue: 0,
                    inputPlaceholder: 'Checklist jika akan meng-cancel sampai dengan Purchase Request (PR). dan PR tidak dapat dibuat PO kembali',
                    showCancelButton: true,
                    confirmButtonClass: 'btn btn-danger',
                    cancelButtonClass: 'btn btn-primary',
                    confirmButtonText: 'Ya, lanjut', // Oui, sûr
                    cancelButtonText: 'Batal', // Annuler

                })
                .then(res => {
                    if (res.value == 0 || res.value == 1) {
                        input.val(res.value);
                        console.log(res.value);
                        _this.closest("form").submit();
                    }
                });

            }

            });


            $(document).on("click", "#btnPrint", function(e) {
                var form = this;
                var arr_id = [];

                $.each(rows_selected, function(index, rowId){
                    arr_id.push(rowId);
                });
                if(arr_id.length === 0){
                        Swal.fire(
                            'Informasi',
                            'Minimal Checklist 1 Item PO',
                            'warning'
                        );
                        return false;
                }else{
                    var id = arr_id;
                    $('input[name="po_id"]').val(id);
					setTimeout(function() {
                        window.location.reload();
                    }, 1000);
                }
            });


            $('#btn-filter').click( function() {
                $('form#form').attr('action',"{{ route('purchasing.po.search') }}");
                if($('form#form').valid()){
                    $('form#form').submit();
                }
            });

            $('#btn-export').click( function(e) {
                $('input[name="statusExport"]').val('1');
                e.preventDefault();
                $('form#form').attr('action',"{{ route('purchasing.po.export') }}");
                if($('form#form').valid()){
                    $('form#form').submit();
                }
            });

            $(document).on('click', "#btn-export-item", function(e) {
                $('input[name="statusExport"]').val('0');
                e.preventDefault();
                $('form#form').attr('action',"{{ route('purchasing.po.export') }}");
                if($('form#form').valid()){
                    $('form#form').submit();
                }
            });

        });

        function printExternal(url) {
            var printWindow = window.open(url, 'Print', 'left=200, top=200, width=950, height=800, toolbar=0, resizable=0');
            printWindow.addEventListener('load', function() {
                printWindow.print();
            }, true);
        }

        $(document).on('click', '.modalMdEmail', function (e) {
            e.preventDefault();
            var url = $(this).attr('value');
            $('#modalMdEmailContent').html('');
            $('.modalError').html('');

            $.ajax({
                url: url,
                type: 'GET',
                dataType: 'html',
                success: function (response) {
                    $('#modalMdEmailContent').html(response);
                    $('#modalMdEmail').modal('show');
                },
                error: function (xhr, status, error) {
                    $('.modalError').html('<div class="alert alert-danger">Failed to load history item. Please try again later.</div>');
                }
            });
        });

        $(document).on('click', '.btn-cancel-po-2', function () {
            var po_id = $(this).data('id');
            var actionUrl = $(this).data('url');
            var csrfToken = $('meta[name="csrf-token"]').attr('content');
            $('#detailFormCancelPo2').html(`
                <form id="formCancelPo${po_id}" action="${actionUrl}" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="_token" value="${csrfToken}">
                    <input name="po_id" type="hidden" value="${po_id}" />

                    <label>Alasan Cancel : </label>
                    <br>
                    <input id="reason${po_id}" type="hidden" name="reason" value="">
                    <div style="width: 100%;">
                        <trix-editor input="reason${po_id}"></trix-editor>
                    </div>
                    <!-- Tempat munculnya error -->
                    <div id="error-reason-${po_id}" class="text-danger" style="margin-top: 5px; display: none;"></div>

                    <br><br>
                    <input type="hidden" name="isPR" value="0" />
                    <label>
                        <input class="magic-checkbox" name="isPR" type="checkbox" value="1" id="checkisPR${po_id}" />
                        <label for="checkisPR${po_id}">
                            Checklist Jika Akan Cancel Sampai Dengan Purchase Request (PR) dan Item PR Tidak Dapat Dibuat PO Kembali
                        </label>
                    </label>
                    <hr>
                    <div style="display: flex; justify-content: flex-end; margin-top: 10px;">
                        <button class="btn btn-danger" type="submit">Submit</button>
                    </div>
                </form>
            `);
            $(`#formCancelPo${po_id}`).on('submit', function (e) {
                var reasonValue = $(`#reason${po_id}`).val().trim();
                if (reasonValue === '') {
                    e.preventDefault();
                    $(`#error-reason-${po_id}`).text('Alasan cancel wajib diisi.').show();
                } else {
                    $(`#error-reason-${po_id}`).text('').hide();
                }
            });
        });
    </script>
@stop
