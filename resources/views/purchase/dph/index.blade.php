@extends('layouts.app')

@section('page-header')
    Daftar Perbandingan Harga
@endsection

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item active" aria-current="page">Daftar Perbandingan Harga </li>
    </ol>
@endsection

@section('content')
    
    <div class="bgc-white p-20 mB-20">
        <div class="table-responsive mt-5">
            <table id="dataTables" class="table table-striped table-bordered" cellspacing="0" width="100%">
                <thead>
                    <tr>
                        <th></th>
                        <th>No. DPH</th>
                        <th>No. PR</th>
                        @if(Auth::user()->data_access == 1)
                            <th>Purchaser</th>
                        @endif
                        <th>Tgl Pembuatan</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

@endsection


@section('js')
    <script>
        $(document).ready(function() {
            var rows_selected = [];
            var table =  $('#dataTables').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('purchasing.dph.datatables') }}',
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
                    @if(Auth::user()->data_access == 1)
                        {data: 'created', name: 'users.name'},
                    @endif
                    {data: 'created_at', name: 'dph.created_at', searchable: false},
                    {data: 'status', name: 'status', searchable: false},
                    {data: 'action', name: 'action', orderable: false, searchable: false}
                ],
                @if(Auth::user()->data_access == 1)
                    "order": [[ 4, "desc" ]],
                @else
                    "order": [[ 3, "desc" ]],
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

            //CANCEL
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
                        text: 'Anda Yakin untuk melakukan Cancel DPH?', // Êtes-vous sûr de continuer ?
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


            //PRINT
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

            $('#btn-export').click( function(e) {
                $('input[name="statusExport"]').val('1');
                e.preventDefault();
                $('form#form').attr('action',"{{ route('purchasing.dph.print') }}");
                if($('form#form').valid()){
                    $('form#form').submit();
                }
            });

            $(document).on('click', "#btn-export-item", function(e) {
                $('input[name="statusExport"]').val('0');
                e.preventDefault();
                $('form#form').attr('action',"{{ route('purchasing.dph.print') }}");
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
       
    </script>
@stop
