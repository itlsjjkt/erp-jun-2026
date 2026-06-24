@extends('layouts.app')

@section('page-header')
    Inventory Asset
@endsection

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item active" aria-current="page">Inventory Asset</li>
    </ol>
@endsection
@section('content')
<div class="row mB-40">
    <div class="col-sm-12">
        <div class="bgc-white p-20 bd">
            <div class="row mB-20">
                <div class="col-sm-11">
                    <span>
                        <a href="{{ route('logistic.inventory_asset.create') }}" class="btn btn-info text-uppercase fsz-sm fw-600">
                            <i class="ti-plus"></i> ASSET
                        </a>
                    </span>
                    <span>
                        <a href="#" class="btn btn-info text-uppercase fsz-sm fw-600" data-toggle="modal" data-target="#modalInstant">
                            <i class="ti-plus"></i> INSTAN ASSET
                        </a>
                    </span>
                </div>
                <div class="col-sm-1">
                    <form id="formPrintUkuran" target="_blank" action="{{ route('logistic.inventory_asset.print_merge') }}" method="POST">
                        @csrf
                        <input type="hidden" name="ast_id">
                        <input type="hidden" name="ukuran" id="ukuranInput" value="36">

                        <div class="dropdown">
                            <button class="btn btn-outline border-dark dropdown-toggle" type="button" id="dropdownUkuranBtn" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fa fa-qrcode icon-lg"></i> Print
                            </button>
                            <div class="dropdown-menu" aria-labelledby="dropdownUkuranBtn">
                                <a class="dropdown-item ukuran-option" href="#" data-ukuran="36">Label 36mm</a>
                                <a class="dropdown-item ukuran-option" href="#" data-ukuran="24">Label 24mm</a>
                            </div>
                        </div>
                    </form>
                </div>

             </div>
            <div class="bgc-white bd bdrs-3 p-20 mB-20 table-responsive">
                <table id="dataTables" class="table table-bordered table-striped" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th style="width:50px" class="text-center">
                                <input class="magic-checkbox" name="select_all" id="select_all" type="checkbox"> <label for="select_all"></label>
                            </th>
                            <th>Produk</th>
                            <th>Kode Asset</th>
                            <th>Dia</th>
                            <th>Tgl Dokumen</th>
                            <th>Dibuat Oleh</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalInstant" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-lg" role="document" style="box-shadow: 0 4px 8px rgba(0, 0, 0, 0.4);">
        <div class="modal-content" style="border: 2px solid #0088c1; border-radius: 10px;">
            <form class="instant" action="{{ route('logistic.inventory_asset.create_instant') }}" method="POST" id="form-instant">
                @csrf
                <div class="modal-header" style="background-color: #0088c1">
                    <h5 class="modal-title" style="color: white" id="modalInstantTitle">Instan Asset</h5>
                    <button type="button" class="close" style="color: white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" style="margin: 20px; max-height: 600px; overflow-y: auto;">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="alert alert-info mb-3">
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                                <strong>INFORMASI</strong><br>
                                <ul>
                                    <li>Instan asset akan menghasilkan data asset berstatus draft, anda harus update informasi setelah data tersimpan dan lakukan perubahan status.</li>
                                    <li>Jika terdapat kesalahan input data asset, data asset akan tersimpan permanen dan tersimpan history pembuatan nya.</li>
                                    <li>Batas jumlah data asset 1-50 data.</li>
                                    <li>Edit data asset tidak dapat merubah data Company dan Code Produk.</li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-sm-12 mt-2">
                            <div class="row">
                                <div class="col-sm-3">
                                    Company <span class="text-danger">*</span>
                                </div>
                                <div class="col-sm-9">
                                    {!! Form::select('company_id', $company,'',['class'=>'form-control select2 company_id' , 'required' => '']) !!}
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12 mt-2">
                            <div class="row">
                                <div class="col-sm-3">
                                    Product <span class="text-danger">*</span>
                                </div>
                                <div class="col-sm-9">
                                    <select name="product_id" id="product_id_" class="form-control product_id select2" value="0" required ></select>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12 mt-2">
                            <div class="row">
                                <div class="col-sm-3">
                                    Jumlah Asset <span class="text-danger">*</span>
                                </div>
                                <div class="col-sm-9">
                                <input class="form-control count_barcode"
                                    type="number"
                                    name="count_barcode"
                                    value=""
                                    min="1"
                                    max="50"
                                    placeholder="0"
                                    required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary px-3 py-1 btn-inputtt" style="border-radius: 4px;">
                        Input
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="modalShow" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
    data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-lg" role="document" style="box-shadow: 0 4px 8px rgba(0, 0, 0, 0.4);">
        <div class="modal-content" style="border: 2px solid #0088c1; border-radius: 10px;">
            <div class="modal-header" style="background-color: #0088c1">
                <h5 class="modal-title" style="color: white" id="modalShowTitle">SHOW DATA</h5>
                <button type="button" class="close" style="color: white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="margin: 20px; max-height: 600px; overflow-y: auto;">
                <div class="modalError"></div>
                <div id="modalShowContent"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEdit" tabindex="-1" role="dialog" aria-labelledby="modalEditLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content" style="border: 2px solid #000; border-radius: 10px;">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalEditLabel">Edit Data</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="modalEditContent">
                <p class="text-center">Loading...</p>
            </div>
        </div>
    </div>
</div>



@endsection
@section('js')
    <script>
        function updateDataTableSelectAllCtrl(table){
            var $table             = table.table().node();
            var $chkbox_all        = $('tbody input[type="checkbox"]', $table);
            var $chkbox_checked    = $('tbody input[type="checkbox"]:checked', $table);
            var chkbox_select_all  = $('thead input[name="select_all"]', $table).get(0);

            if($chkbox_checked.length === 0){
                chkbox_select_all.checked = false;
                chkbox_select_all.indeterminate = false;
            } else if ($chkbox_checked.length === $chkbox_all.length){
                chkbox_select_all.checked = true;
                chkbox_select_all.indeterminate = false;
            } else {
                chkbox_select_all.checked = true;
                chkbox_select_all.indeterminate = true;
            }
        }


        $(document).ready(function() {
            var rows_selected = [];
            var table = $('#dataTables').DataTable({
                pageLength: 50,
                processing: true,
                serverSide: true,
                ajax: '{{ route('logistic.inventory_asset_datatables.datatables') }}',
                'columnDefs': [{
                    'targets': 0,
                    'searchable': false,
                    'orderable': false,
                    'width': '1%',
                    'className': 'text-center',
                    'render': function (data, type, full, meta){
                        return '<input name="ast_id[]" type="checkbox" class="ast_id magic-checkbox" id="check-'+data+'" value="'+ data +'"><label for="check-'+data+'"></label>';
                    }
                }],
                columns: [
                    {data: 'id'},
                    {data: 'produk', name: 'master_item_products.name'},
                    {data: 'doc_no', name: 'inventory_assets.doc_no'},
                    {data: 'parent_doc_no', name: 'parent_inventory_assets.doc_no'},
                    {data: 'created_at', name: 'inventory_assets.created_at'},
                    {data: 'created_by', name: 'inventory_assets.created_by'},
                    {data: 'status', name: 'status', searchable: false},
                    {data: 'action', name: 'action', orderable: false, searchable: false},
                    {data: 'produkpn', name: 'master_item_products.part_number', visible: false},
                    {data: 'brand', name: 'master_item_brands.name', visible: false}
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

                $('#showSelected').text('Data Selected:' + rows_selected.length);
                e.stopPropagation();
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


            $('#product_id_').select2({
                placeholder: 'Cari produk...',
                minimumInputLength: 2,
                allowClear: true,
                ajax: {
                    url: "{{ route('logistic.get_product_by_req') }}",
                    dataType: 'json',
                    delay: 250,
                    processResults: function (data) {
                        return {
                            results: $.map(data, function (item) {
                                let part_number = item.part_number
                                    ? `[${item.part_number}] - [${item.code} - ${item.brand}]`
                                    : `[${item.code} - ${item.brand}]`;

                                return {
                                    id: item.id,
                                    text: `${item.name} ${part_number}`,
                                    measure: item.measure
                                };
                            })
                        };
                    },
                    cache: false
                }
            });

            $(document).on('click', ".btn-inputtt", function(e) {
                var _this = $(this);
                var form = _this.parents('form');
                e.preventDefault();

                var company = $('.company_id').val();
                var produk = $('.product_id').val();
                var jml = $('.count_barcode').val();
                debugger;

                if (company === null || produk === null || jml === '') {
                    let message = '';
                    if (!company) message += '• Pastikan Form Company Telah Diisi.<br>';
                    if (!produk) message += '• Pastikan Form Produk Telah Diisi.<br>';
                    if (!jml) message += '• Pastikan Form Jumlah Telah Diisi.<br>';
                    debugger
                    Swal.fire({
                        icon: 'warning',
                        title: 'Data belum lengkap',
                        html: message,
                        confirmButtonText: 'Oke',
                    });
                    return;
                }

                if(jml<1 || jml>50){
                    Swal.fire({
                        icon: 'warning',
                        title: 'Data tidak sesuai',
                        html: '<strong>Minimal Jumlah Data 1 dan Maksimal Jumlah Data 50.</strong>',
                        confirmButtonText: 'Oke',
                    });
                    return;
                }
                Swal.fire({
                    title: 'Konfirmasi',
                    text: 'Apakah anda yakin untuk input data?',
                    type: 'question',
                    showCancelButton: true,
                    confirmButtonColor: 'null',
                    cancelButtonColor: 'null',
                    confirmButtonClass: 'btn btn-primary',
                    cancelButtonClass: 'btn btn-danger',
                    confirmButtonText: 'Ya',
                    cancelButtonText: 'Batal',
                }).then(res => {
                    if (res.value) {
                        Swal.fire({
                            title: 'Create Data',
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

            $(document).on('click', '.modalShow', function (e) {
                e.preventDefault();
                var url = $(this).attr('value');
                $('#modalShowContent').html('');
                $('.modalError').html('');
                $.ajax({
                    url: url,
                    type: 'GET',
                    dataType: 'html',
                    success: function (response) {
                        $('#modalShowContent').html(response);
                        $('#modalShow').modal('show');
                    },
                    error: function (xhr, status, error) {
                        $('.modalError').html('<div class="alert alert-danger">Failed to load data. Please try again later.</div>');
                    }
                });
            });

            $('.ukuran-option').on('click', function(e) {
                e.preventDefault();

                if (rows_selected.length === 0) {
                    Swal.fire(
                        'Informasi',
                        'Minimal Checklist 1 QR',
                        'warning'
                    );
                    return false;
                }

                var ukuran = $(this).data('ukuran');
                $('#ukuranInput').val(ukuran);
                $('input[name="ast_id"]').val(rows_selected.join(','));
                $('#formPrintUkuran').submit();
            });

            $(document).on('click', '.modalEdit', function(e) {
                e.preventDefault();
                var url = $(this).data('url');
                $('#modalEdit').modal('show');
                $('#modalEditContent').html('<p class="text-center">Loading...</p>');
                $.get(url)
                    .done(function(data) {
                        $('#modalEditContent').html(data);
                    })
                    .fail(function(jqXHR, textStatus, errorThrown) {
                        let errorMessage = '<div class="alert alert-danger text-center">' +
                                        'Terjadi kesalahan saat memuat form:<br>' +
                                        '<strong>' + jqXHR.status + ' ' + errorThrown + '</strong>' +
                                        '</div>';
                        $('#modalEditContent').html(errorMessage);
                    });
            });
        });
    </script>
@stop
