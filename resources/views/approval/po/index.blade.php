@extends('layouts.app')

@section('page-header')
    Approval Purchase Order
@endsection

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item active" aria-current="page">Approval Purchase Order</li>
    </ol>
@endsection

@section('content')
<div id="validation-message"></div>
<div class="row mB-40">
    <div class="col-sm-12">
        <div class="bgc-white bd bdrs-3 p-20 mB-20">
            <div class="row">
                <div class="col-sm-6 ">
                    <a href="{{ route('purchasing.po.index') }}">
                        <i class="ti-arrow-left"></i> Kembali
                    </a>
                </div>
                <div class="col-sm-6">
                    <a href="#" id="btnApprove" class="btn btn-outline border-dark float-right  ml-2">
                        <i class="ti-check-box icon-lg"></i> Approve
                    </a>
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-sm-12">
                    <div class="alert alert-info">
                        <b>INFORMASI</b>: Anda dapat melakukan multiple Approve tanpa harus masuk ke Detail PO. Checklist pada Row Data kemudian klik Approve.
                    </div>
                    <table id="dataTables" class="table table-striped table-bordered" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th style="width:50px" class="text-center">
                                    <input class="magic-checkbox" name="select_all" type="checkbox"> <label></label>
                                </th>
                                <th>No. PO</th>
                                <th class="text-left">Total Amount</th>
                                <th>Supplier</th>
                                <th>Dibuat Oleh</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection


@section('js')
    <script>

    function approveAll() {
        var arr_id = [];

        $.each(rows_selected, function(index, rowId){
            arr_id.push(rowId);
        });

        var id = arr_id;

        Swal.fire({
            title: 'Konfirmasi', // Opération Dangereuse
            text: 'Apakah anda yakin melanjutkan ini?', // Êtes-vous sûr de continuer ?
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
                $.ajax({
                    type:'POST',
                    url:"{{ route('approval.po.update.multiple') }}",
                    data:{
                        'id':id,
                        '_token': $('meta[name="csrf-token"]').attr('content')
                    },
                    success:function(data){
                        $('#validation-message').append('<div class="alert dark alert-success alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span> </button> Approval PO Berhasil</div>');
                        setTimeout(function () { document.location.reload(true); }, 300);
                    },
                    error: function(xhr)
                    {
                        $('#validation-message').html('');
                        $.each(xhr.responseJSON.errors, function(key,value) {
                            $('#validation-message').append('<div class="alert dark alert-danger alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span> </button><b>'+value+'</b></div>');
                        });
                    }
                });
            }
        });

    }

    function updateDataTableSelectAllCtrl(table){
        var $table             = table.table().node();
        var $chkbox_all        = $('tbody input[type="checkbox"]', $table);
        var $chkbox_checked    = $('tbody input[type="checkbox"]:checked', $table);
        var chkbox_select_all  = $('thead input[name="select_all"]', $table).get(0);

        // If none of the checkboxes are checked
        if($chkbox_checked.length === 0){
            chkbox_select_all.checked = false;
            if('indeterminate' in chkbox_select_all){
                chkbox_select_all.indeterminate = false;
            }

        // If all of the checkboxes are checked
        } else if ($chkbox_checked.length === $chkbox_all.length){
            chkbox_select_all.checked = true;
            if('indeterminate' in chkbox_select_all){
                chkbox_select_all.indeterminate = false;
            }

        // If some of the checkboxes are checked
        } else {
            chkbox_select_all.checked = true;
            if('indeterminate' in chkbox_select_all){
                chkbox_select_all.indeterminate = true;
            }
        }
    }

    $(document).ready(function() {
        var rows_selected = [];
        var table = $('#dataTables').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route('approval.po.datatables') }}',
            "pageLength": 50,
            'columnDefs': [{
                'targets': 0,
                'searchable': false,
                'orderable': false,
                'width': '1%',
                'className': 'text-center',
                'render': function (data, type, full, meta){
                    return '<input type="checkbox" class="magic-checkbox po_id" value="'+ data +'" ><label></label>';
                }},
                { className: 'text-right', targets: [2] },
            ],
            columns: [
                {data: 'id'},
                {data: 'doc_no', name: 'po.doc_no'},
                {data: 'payment_amount', name: 'payment_amount', searchable: false},
                {data: 'supplier', name: 'suppliers.name'},
                {data: 'created', name: 'created', searchable: false},
                {data: 'action', name: 'action', orderable: false, searchable: false}
            ],
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
            updateDataTableSelectAllCtrl(table);
            e.stopPropagation();
        });

        $('#dataTables').on('click', 'tbody td, thead th:first-child', function(e){
            $(this).parent().find('input[type="checkbox"]').trigger('click');
        });

        $('thead input[name="select_all"]', table.table().container()).on('click', function(e){
            if(this.checked){
                $('#dataTables tbody input[type="checkbox"]:not(:checked)').trigger('click');
            } else {
                $('#dataTables tbody input[type="checkbox"]:checked').trigger('click');
            }

            e.stopPropagation();
        });

        table.on('draw', function(){
            updateDataTableSelectAllCtrl(table);
        });

        $(document).on("click", "#btnApprove", function(e) {
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

                Swal.fire({
                    title: 'Konfirmasi', // Opération Dangereuse
                    text: 'Apakah anda yakin melanjutkan ini?', // Êtes-vous sûr de continuer ?
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
                        $.ajax({
                            type:'POST',
                            url:"{{ route('approval.po.update.multiple') }}",
                            data:{
                                'id':id,
                                '_token': $('meta[name="csrf-token"]').attr('content')
                            },
                            success:function(data){
                                $('#validation-message').append('<div class="alert dark alert-success alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span> </button> Approval PO Berhasil</div>');
                                setTimeout(function () { document.location.reload(true); }, 300);
                            },
                            error: function(xhr)
                            {
                                $('#validation-message').html('');
                                $.each(xhr.responseJSON.errors, function(key,value) {
                                    $('#validation-message').append('<div class="alert dark alert-danger alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span> </button><b>'+value+'</b></div>');
                                });
                            }
                        });
                    }
                });
            }


        });
        

    });
</script>
@stop