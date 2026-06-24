@extends('layouts.app')

@section('page-header')
    Surat Pengantar Barang
@stop

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('logistic.spb.index') }}">Surat Pengantar Barang</a></li>
        <li class="breadcrumb-item active" aria-current="page">Daftar LPB</li>
    </ol>
@endsection


@section('content')

  

	<div class="bgc-white p-30 bd">
        <div class="row mb-1 justify-content-end">
            <div class="col-sm-6">
                <a href="{{ route('logistic.spb.index') }}"><i class="ti-arrow-left"></i> Kembali</a>
            </div>
			<div class="col-sm-6">
                {!! Form::open(['method' => 'GET', 'route' => ['logistic.spb.create'], 'id' => 'formSPB']) !!}
                    {{ csrf_field() }}
                    <input type="hidden" name="lpb_id">
                    <input class="btn btn-danger text-uppercase fsz-sm fw-600 float-right" type="submit" id="btn-submit" value="Buat SPB">
                {!! Form::close() !!}
			</div>
		</div>
       
        <hr>
        <div class="alert alert-info mT-3">
           Berikut daftar LPB yang belum diterbitkan SPB, Silahkan Checklist Minimal 1 LPB yang akan dibuatkan SPB. 
        </div>
       
        <table class="table table-bordered mt-2" id="dataTables" >
            <thead>
                <tr>
                    <th style="width:50px" class="text-center">
                        <input class="magic-checkbox" name="select_all" type="checkbox"> <label></label>
                    </th>
                    <th>Nomor LPB</th>
                    <th>Nomor PO</th>
                    <th>Nomor DPM</th>
                    <th>Departemen/Nama Kapal</th>
                    <th>Supplier</th>
                </tr>
            </thead >
        </table>
        
    </div>
   
@stop


@section('js')

<script  type='text/javascript'>

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
            ajax: '{{ route('logistic.spb.list.datatables') }}',
            "pageLength": 50,
            'columnDefs': [{
                'targets': 0,
                'searchable': false,
                'orderable': false,
                'width': '1%',
                'className': 'text-center',
                'render': function (data, type, full, meta){
                    return '<input name="lpb_id[]" type="checkbox" class="lpb_id magic-checkbox" value="'+ data +'"><label></label>';
                }
            }],
            columns: [
                {data: 'id'},
                {data: 'doc_no', name: 'doc_no'},
                {data: 'po_no', name: 'po.doc_no'},
                {data: 'dpm_no', name: 'purchase_requisitions.dpm_no'},
                {data: 'department', name: 'departments.name'},
                {data: 'supplier', name: 'suppliers.name'},
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
                        'Minimal Checklist 1 Item LPB',
                        'warning'
                    );
                    return false;
            }else{
                e.preventDefault();
                $('input[name="lpb_id"]').val(arr_id);

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
