@extends('layouts.app')

@section('page-header')
    Daftar Inventory Asset
@endsection

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item active" aria-current="page">Daftar Inventory Asset</li>
    </ol>
@endsection
@section('content')
<div class="row mB-40">
    <div class="col-sm-12">
        <div class="bgc-white p-20 bd">
            <div class="row">
                <div class="col-sm-12">
                    <span>
                        <a href="{{ route('logistic.inventory_asset.create') }}" class="btn btn-info text-uppercase fsz-sm fw-600">
                            <i class="ti-plus"></i> DIA
                        </a>
                    </span>
                    <span>
                        <a href="#" class="btn btn-info text-uppercase fsz-sm fw-600" data-toggle="modal" data-target="#modalInstant">
                            <i class="ti-plus"></i> INSTAN DIA
                        </a>
                    </span>
                </div>
            </div>
            <div class="bgc-white bd bdrs-3 p-20 mB-20 table-responsive mt-3">
                <table id="dataTables" class="table table-bordered table-striped" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th>Daftar Inventory Asset</th>
                            <th>Tgl Buat</th>
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

@endsection
@section('js')
    <script>
        $(document).ready(function() {
            $('#dataTables').DataTable({
                pageLength: 50,
                processing: true,
                serverSide: true,
                ajax: '{{ route('logistic.parent_inventory_asset_datatables.datatables') }}',
                columns: [
                    {data: 'doc_no', name: 'parent_inventory_assets.doc_no'},
                    {data: 'created_at', name: 'parent_inventory_assets.created_at', orderable: false, searchable: false},
                    {data: 'created_by', name: 'parent_inventory_assets.created_by'},
                    {data: 'status', name: 'status', searchable: false},
                    {data: 'action', name: 'action', orderable: false, searchable: false}
                ]
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
        });
    </script>
@stop
