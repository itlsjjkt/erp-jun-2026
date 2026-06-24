@extends('layouts.app')

@section('page-header')
    Change Type PO
@endsection

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item active" aria-current="page">Change Type PO</li>
    </ol>
@endsection

@section('content')

    <div class="bgc-white bd bdrs-3 p-20 mB-20">
        <div class="row">
            <div class="col-sm-12">
                <button type="button" class="btn btn-outline float-right border-dark" id="btnChangeTypeBulk">
                    <i class="ti-exchange-vertical"></i> Change Type PO Multiple
                </button>
            </div>
        </div>
        <hr>

        <div id="showSelected"></div>

        <div class="table-responsive mt-5">
            <table id="dataTables" class="table table-striped table-bordered" cellspacing="0" width="100%">
                <thead>
                    <tr>
                        <th> </th>
                        <th>No. PO</th>
                        <th>Type PO</th>
                        <th>No. DPH</th>
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

    <!-- Modal Change Type PO Single -->
    <div class="modal fade" id="modalChangeTypePO" tabindex="-1" role="dialog" aria-labelledby="changeTypePOModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form id="formChangeTypePO" method="POST" action="{{ route('logistic.ctp.changeType') }}">
                @csrf
                <input type="hidden" name="po_id" id="changeTypePOId">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Change Type PO</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        
                        <div class="form-group">
                            <label>Nomor PO</label>
                            <div id="docNoPOLink" style="font-weight: bold;"></div>
                        </div>
                        
                        <div class="form-group">
                            <label for="po_type">Pilih Type PO <span class="text-danger">*</span></label>
                            <select name="po_type" id="poTypeSelect" class="form-control select2" required>
                                <option value="lpb">LPB</option>
                                <option value="non_lpb">Non LPB</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="remark">Remark <span class="text-danger">*</span></label>
                            <textarea name="remark" id="remark" class="form-control" rows="3" placeholder="tambahkan catatan ..." required></textarea>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Simpan</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Change Type Multi -->
    <div class="modal fade" id="modalChangeTypeBulk" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <form id="formChangeTypeBulk" method="POST" action="{{ route('logistic.ctp.changeTypeMultiple') }}">
                @csrf
                <input type="hidden" name="po_id" id="bulkPoIds">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Change Type PO Multiple</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body">

                        <div class="form-group">
                            <label>Nomor PO</label>
                            <div id="docNoPOBulkLinks" style="max-height: 150px; overflow-y: auto; font-size: 14px; line-height: 1.4;">
                                {{-- Data Item PO --}}
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="po_type">Pilih Type PO <span class="text-danger">*</span></label>
                            <select name="po_type" class="form-control select2" required>
                                <option value="lpb">LPB</option>
                                <option value="non_lpb">Non LPB</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="remark">Remark <span class="text-danger">*</span></label>
                            <textarea name="remark" id="remark" class="form-control" rows="3" placeholder="tambahkan catatan ..." required></textarea>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Simpan</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

@endsection

{{-- @section('js')
    <script>
        // === Data Tables ===
        $(document).ready(function() {
            var rows_selected = [];
            var table =  $('#dataTables').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route("logistic.ctp.datatables") }}',
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
                    { data: 'id'},
                    { data: 'doc_no', name: 'doc_no'},
                    { data: 'type', name: 'po.type' },
                    { data: 'no_dph', name: 'dph.doc_no'},
                    { data: 'no_pr', name: 'purchase_requisitions.doc_no'},
                    { data: 'supplier', name: 'suppliers.name'},
                    @if(Auth::user()->data_access == 1)
                        {data: 'created', name: 'users.name'},
                    @endif
                    { data: 'created_at', name: 'po.created_at', searchable: false},
                    { data: 'status', name: 'status', searchable: false},
                    { data: 'action', name: 'action', orderable: false, searchable: false}
                ],
                @if(Auth::user()->data_access == 1)
                    "order": [[ 6, "desc" ]],
                @else
                    "order": [[ 5, "desc" ]],
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

            $(document).on('click', ".btn-email", function(e) {
                var _this = $(this);
                var form = _this.parents('form');

                e.preventDefault();

                Swal.fire({
                    title: 'Konfirmasi', // Opération Dangereuse
                    text: 'Apakah anda yakin untuk mengirimkan email untuk PO ini?', // Êtes-vous sûr de continuer ?
                    type: 'question',
                    showCancelButton: true,
                    confirmButtonColor: 'null',
                    cancelButtonColor: 'null',
                    confirmButtonClass: 'btn btn-primary',
                    cancelButtonClass: 'btn btn-danger',
                    confirmButtonText: 'Ya, kirim', // Oui, sûr
                    cancelButtonText: 'Batal', // Annuler
                }).then(res => {
                    if (res.value) {
                        Swal.fire({
                            title: 'Sending Email',
                            html: 'Don\'t refresh or close your browser until process is completed',
                            showConfirmButton: false,
                            allowOutsideClick: false,
                            width: '700px',
                            onBeforeOpen: () => {
                                Swal.showLoading();
                            },
                        });
                        _this.closest("form").submit();
                    }
                });


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

        // === Change Type PO Single ===
        $(document).on('click', '.btn-change-type', function () {
            let poId = $(this).data('id');
            let poType = $(this).data('type');
            let docNo = $(this).data('doc_no');

            let textHtml = `<div style="font-weight:bold;">${docNo}</div>`;

            $('#changeTypePOId').val(poId);
            $('#poTypeSelect').val(poType).trigger('change');
            $('#docNoPOLink').html(textHtml); 

            $('#modalChangeTypePO').modal('show');
        });

        // === Change Type PO Multiple ===
        $('#btnChangeTypeBulk').on('click', function() {
        var selectedIds = [];
        var selectedDocNos = [];
        var selectedLinksHtml = '';

        $('#dataTables tbody input.po_id:checked').each(function() {
            var row = $(this).closest('tr');
            var data = $('#dataTables').DataTable().row(row).data();

            selectedIds.push(data.id);
            selectedDocNos.push(data.doc_no);

            var urlDetail = `{{ url('po') }}/${data.id}`;
            selectedLinksHtml += `<a target="_blank" href="${urlDetail}" title="Detail PO" data-toggle="tooltip" style="font-weight:bold; display:block; margin-bottom:4px;">${data.doc_no}</a>`;
        });

            // Minimum 1 Item 
            if (selectedIds.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Peringatan',
                    text: 'Pilih minimal 1 PO untuk perubahan Type PO Multiple'
                });
                return;
            }
            // Maksimum 10 Item 
            if (selectedIds.length > 10) {
                Swal.fire({
                    icon: 'error',
                    title: 'Peringatan',
                    text: 'Pilih maksimal 10 PO untuk perubahan Type PO Multiple'
                });
                return;
            }

            $('#docNoPOBulkLinks').html(selectedLinksHtml);
            $('#bulkPoIds').val(selectedIds.join(','));
            $('#modalChangeTypeBulk').modal('show');
        });







    </script>
@stop --}}

@section('js')
<script>
    $(document).ready(function() {
        let rows_selected = [];
        let rows_selected_data = [];

        // === Data Tables ===
        const table = $('#dataTables').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route("logistic.ctp.datatables") }}',
            pageLength: 50,
            columnDefs: [{
                targets: 0,
                searchable: false,
                orderable: false,
                width: '1%',
                className: 'text-center',
                render: function(data) {
                    return '<input type="checkbox" class="magic-checkbox po_id" value="' + data + '"><label></label>';
                }
            }],
            columns: [
                { data: 'id' },
                { data: 'doc_no', name: 'doc_no' },
                { data: 'type', name: 'po.type' },
                { data: 'no_dph', name: 'dph.doc_no' },
                { data: 'no_pr', name: 'purchase_requisitions.doc_no' },
                { data: 'supplier', name: 'suppliers.name' },
                @if(Auth::user()->data_access == 1)
                    { data: 'created', name: 'users.name' },
                @endif
                { data: 'created_at', name: 'po.created_at', searchable: false },
                { data: 'status', name: 'status', searchable: false },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ],
            @if(Auth::user()->data_access == 1)
                order: [[6, "desc"]],
            @else
                order: [[5, "desc"]],
            @endif
            rowCallback: function(row, data) {
                const rowId = data.id;
                if ($.inArray(rowId, rows_selected) !== -1) {
                    $(row).find('input[type="checkbox"]').prop('checked', true);
                    $(row).addClass('selected');
                }
            }
        });

        $('#dataTables tbody').on('click', 'input[type="checkbox"]', function(e) {
            const $row = $(this).closest('tr');
            const data = table.row($row).data();
            const rowId = data.id;
            const index = $.inArray(rowId, rows_selected);

            if (this.checked && index === -1) {
                rows_selected.push(rowId);
                rows_selected_data.push(data);
            } else if (!this.checked && index !== -1) {
                rows_selected.splice(index, 1);
                rows_selected_data = rows_selected_data.filter(item => item.id !== rowId);
            }

            if (this.checked) {
                $row.addClass('selected');
            } else {
                $row.removeClass('selected');
            }

            $('#showSelected').text('Data Selected: ' + rows_selected.length);
            e.stopPropagation();
        });

        $('#dataTables').on('click', 'tbody td, thead th:first-child', function(e) {
            $(this).parent().find('input[type="checkbox"]').trigger('click');
        });

        // === Change Type PO MULTIPLE ===
        $('#btnChangeTypeBulk').on('click', function () {
            if (rows_selected.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Peringatan',
                    text: 'Pilih minimal 1 PO untuk perubahan Type PO Multiple'
                });
                return;
            }

            if (rows_selected.length > 10) {
                Swal.fire({
                    icon: 'error',
                    title: 'Peringatan',
                    text: 'Pilih maksimal 10 PO untuk perubahan Type PO Multiple'
                });
                return;
            }

            let selectedLinksHtml = '';
            rows_selected_data.forEach(function (data) {
                const urlDetail = `{{ url('po') }}/${data.id}`;
                selectedLinksHtml += `<a target="_blank" href="${urlDetail}" title="Detail PO" style="font-weight:bold; display:block; margin-bottom:4px;">${data.doc_no}</a>`;
            });

            $('#docNoPOBulkLinks').html(selectedLinksHtml);
            $('#bulkPoIds').val(rows_selected.join(','));
            $('#modalChangeTypeBulk').modal('show');
        });

        // === Change Type PO SINGLE ===
        $(document).on('click', '.btn-change-type', function () {
            let poId = $(this).data('id');
            let poType = $(this).data('type');
            let docNo = $(this).data('doc_no');
            let textHtml = `<div style="font-weight:bold;">${docNo}</div>`;

            $('#changeTypePOId').val(poId);
            $('#poTypeSelect').val(poType).trigger('change');
            $('#docNoPOLink').html(textHtml);
            $('#modalChangeTypePO').modal('show');
        });

        // Optional: Reset checkbox
        $('#btnResetSelection').on('click', function () {
            rows_selected = [];
            rows_selected_data = [];
            table.rows().deselect();
            $('#dataTables input.po_id:checked').prop('checked', false).closest('tr').removeClass('selected');
            $('#showSelected').text('');
        });

    });
</script>
@endsection

