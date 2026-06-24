@extends('layouts.app')

@section('page-header')
Asuransi
@stop

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('logistic.insurance.index') }}"> Asuransi</a></li>
        <li class="breadcrumb-item active" aria-current="page">Daftar SPB</li>
    </ol>
@endsection


@section('content')
    {!! Form::open(['method' => 'GET', 'route' => ['logistic.insurance.create'], 'id' => 'formSPB']) !!}
	    <div class="bgc-white p-30 bd">
            <div class="row">
                <div class="col-lg-6">
                    <h6 class='mT-10'>Daftar Surat Pengiriman Barang (SPB)</h6>
                </div>
                <div class="col-lg-6">
                    <input class="btn btn-success text-uppercase fsz-sm fw-600 float-right mr-2" type="submit" id="btn-submit" value="BUAT ASURANSI">
                </div>
            </div>
        <hr>
        <div class="alert alert-info mT-3">
           Berikut daftar SPB yang belum diterbitkan Asuransi, Silahkan Checklist Minimal 1 SPB. 
        </div>
            <table class="table table-bordered mt-2" style="width: 100%;" id="dataTables">
                <thead>
                    <tr>
                        <th style="width:50px;" class="text-center">
                            <input class="magic-checkbox" name="select_all" type="checkbox"> <label></label>
                        </th>
                        <th>Nomor SPB</th>
                        <th>Jenis SPB</th>
                        <th>Operator</th>
                        <th>PIC Penerima</th>
                        <th>Aksi</th>
                    </tr>
                </thead >
            </table>
            
        </div>
       
    {!! Form::close() !!}

    <div class="modal fade" id="modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalMdTitle">Detail SPB</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="modalError"></div>
                    <div id="modalDocument"></div>
                </div>
            </div>
        </div>
    </div>
@stop


@section('js')

    <script>

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
            var table =  $('#dataTables').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('logistic.insurance.list.datatables') }}',
                "pageLength": 50,
                'columnDefs': [{
                    'targets': 0,
                    'searchable': false,
                    'orderable': false,
                    'width': '1%',
                    'className': 'text-center',
                    'render': function (data, type, full, meta){
                        return '<input name="spb_id[]" type="checkbox" class="spb_id magic-checkbox" value="'+ data +'"><label></label>';
                    }
                }],
                columns: [
                    {data: 'id'},
                    {data: 'doc_no', name: 'doc_no'},
                    {data: 'type', name: 'type'},
                    {data: 'operator', name: 'operator'},
                    {data: 'received_pic', name: 'received_pic'},
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

                $('#showSelected').text('');
                $('#showSelected').text('Data Selected:'+ rows_selected.length);
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

            $('#dataTables tbody').on('click', '.modalDoc', function(e){
                var $row = $(this).closest('tr');
                var data = table.row($row).data();
                $('#modalDocument').load($(this).attr('value'));
            });

            $(document).on('click', "#btn-submit", function(e) {
                var _this = $(this);
                var form = _this.parents('form');
                var arr_id = [];

                $.each(rows_selected, function(index, rowId){
                    arr_id.push(rowId);
                });
                if(arr_id.length === 0){
                        Swal.fire(
                            'Informasi',
                            'Minimal Checklist 1 Item SPB',
                            'warning'
                        );
                        return false;
                }else{
                    e.preventDefault();
                    if (form.valid() ) {
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
                            _this.closest("form").submit();
                            }
                        });
                    }
                }
            });

            $(document).on('click', "#btn-close", function(e) {
                var _this = $(this);
                var form = _this.parents('form');
                var arr_id = [];

                $.each(rows_selected, function(index, rowId){
                    arr_id.push(rowId);
                });
                if(arr_id.length === 0){
                        Swal.fire(
                            'Informasi',
                            'Minimal Checklist 1 Item SPB',
                            'warning'
                        );
                        return false;
                }else{
                    e.preventDefault();
                    if (form.valid() ) {
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
                            _this.closest("form").submit();
                            }
                        });
                    }
                }
            });



        });

    </script>

@stop

