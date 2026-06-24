@extends('layouts.app')

@section('page-header')
    Create Inventory Asset
@endsection

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('logistic.inventory_asset.index') }}">Inventory Asset</a></li>
        <li class="breadcrumb-item active" aria-current="page">Create</li>
    </ol>
@endsection
@section('content')
<div class="row mB-40">
    <div class="col-sm-12">
        <div class="bgc-white p-20 bd">
            <h6><a class="float-left" href="{{ route('logistic.inventory_asset.index') }}"><i class="ti-arrow-left mR-10"></i></a></h6>
            <br>
            <hr>
            <div class="alert alert-info mb-3">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <strong>INFORMASI</strong><br>
                    <ul>
                        <li>Mohon perhatikan pada saat pembuatan data inventory asset untuk menghindari double data asset</li>
                        <li>Lakukan update jika terdapat form yang tidak terisi.</li>
                    </ul>
            </div>

            <form method="POST" action="{{ route('logistic.inventory_asset.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="row mB-30">
                    <div class="col-sm-2" style="text-align:left;back">
                        <small>
                            <button type="button" class="btn btn-success add_form"  title="Add Form">
                                <i class="ti-plus"></i> Form
                            </button>
                        </small>
                    </div>
                    <div class="col-sm-10" style="text-align:right;">
                        <div class="form-group row">
                            <label class="col-sm-10 col-form-label text-right"></label>
                            <div class="col-sm-2">
                                <a href="{{ route('logistic.inventory_asset.index') }}" class="btn btn-light text-uppercase fsz-sm fw-600">Cancel</a>
                                <button type="submit" class="btn btn-danger text-uppercase fsz-sm fw-600 btn-submittt">Submit</button>
                            </div>
                        </div>
                    </div>
                    <div class="row col-12" id="fom-location"></div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection


@section('js')
<script src="https://cdn.jsdelivr.net/npm/autonumeric@4.6.0"></script>
<script>
    $(document).ready(function () {
        let wrapper = $("#fom-location");
        let count = 0;
        const max = 9;
        $(document).on("click", ".add_form", function (e) {
            e.preventDefault();
            if (count < max) {
                count++;
                let index = count;
                const formHtml = `
                    <div class="col-md-4 mt-4 item-form" data-index="${index}" style="min-width: 400px;">
                        <div class="layers bd p-20" style="background-color: #f4f9ff;border-radius:7px;box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);">
                            <div class="layer w-100 mB-10 row">
                                <div class="col-sm-3 text-left">
                                    <a href="#formmm_${index}" data-toggle="collapse" class="btn" style="background-color: #0000001a; padding: 5px;border: 1px solid #6c757d;">
                                        <h6 style="margin: 0; font-weight: bold;">FORM-${index}</h6>
                                    </a>
                                </div>
                                <div class="col-sm-9 text-right">
                                    <small>
                                        <button type="button" class="btn btn-danger btn_delete_row" title="Delete Row">
                                            <i class="ti-trash"></i>
                                        </button>
                                    </small>
                                </div>
                            </div>
                            <div class="layer w-100 mB-10">
                                <div class="collapse" id="formmm_${index}" aria-expanded="false">

                                    <div class="form-group row mt-1">
                                        <label class="col-sm-3 col-form-label text-right">Company <span class="text-danger">*</span></label>
                                        <div class="col-sm-9">
                                            <select name="company_id[]" id="company_id_${index}" class="form-control select2 company_id required">
                                                @foreach($company as $comp => $cp)
                                                    <option value="{{ $comp }}">{{ $cp }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-group row mt-1">
                                        <label class="col-sm-3 col-form-label text-right">Produk <span class="text-danger">*</span></label>
                                        <div class="col-sm-9">
                                            <select name="product_id[]" id="product_id_${index}" class="form-control productItem select2" required></select>
                                        </div>
                                    </div>

                                    <div class="form-group row mt-1">
                                        <label class="col-sm-3 col-form-label text-right">Satuan</label>
                                        <div class="col-sm-9">
                                            <input type="text" name="measure[]" id="measure_${index}" class="form-control measure" readonly>
                                        </div>
                                    </div>

                                    <div class="form-group row mt-1">
                                        <label class="col-sm-3 col-form-label text-right">Lokasi Asset</label>
                                        <div class="col-sm-9">
                                            <select name="location_id[]" id="location_id_${index}" class="form-control select2 location_id">
                                                @foreach($lokasi as $l => $loc)
                                                    <option value="{{ $l }}">{{ $loc }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-group row mt-1">
                                        <label class="col-sm-3 col-form-label text-right">Department</label>
                                        <div class="col-sm-9">
                                            <select name="department_id[]" id="department_id_${index}" class="form-control select2 department_id">
                                                @foreach($department as $dp => $dept)
                                                    <option value="{{ $dp }}">{{ $dept }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-group row mt-1">
                                        <label class="col-sm-3 col-form-label text-right">Relasi</label>
                                        <div class="col-sm-9">
                                            <select name="type_relation[]" id="type_relation_${index}" class="form-control select2 type_relation">
                                                @foreach($type_relation as $key => $value)
                                                    <option value="{{ $key }}">{{ $value }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-group row mt-1">
                                        <label class="col-sm-3 col-form-label text-right">Relasi Data</label>
                                        <div class="col-sm-9">
                                            <select name="relation_item_id[]" id="relation_item_id_${index}" class="form-control select2 relation_item_id">
                                                @foreach($relation_item_id as $z => $val)
                                                    <option value="{{ $z }}">{{ $val }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-group row mt-1">
                                        <label class="col-sm-3 col-form-label text-right">Harga Asset</label>
                                        <div class="col-sm-9 input-group">
                                            <div class="input-group-prepend">
                                                <div class="input-group-text">IDR</div>
                                            </div>
                                            <input type="text" name="price[]" id="price_${index}" value="{{0}}" class="form-control currency">
                                        </div>
                                    </div>

                                    <div class="form-group row mt-1">
                                        <label class="col-sm-3 col-form-label text-right">Catatan</label>
                                        <div class="col-sm-9">
                                            <textarea name="notes[]" id="notes_${index}" class="form-control" style="min-height:120px; vertical-align: top; resize: vertical;"> </textarea>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-sm-3 col-form-label text-right">Status</label>
                                        <div class="col-sm-9">
                                            <select name="status[]" value="" id="status_${index}" class="form-control select2 status" required>
                                                @foreach($status as $s => $st)
                                                    <option value="{{ $s }}">{{ $st }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group row mt-1">
                                        <label class="col-sm-3 col-form-label text-right">Attachment <i class="fa fa-file-pdf-o text-danger"></i></label>
                                        <div class="col-sm-9">
                                            <input type="file" value="0" name="attachment[]" accept=".pdf" class="form-control" />
                                        </div>
                                    </div>
                                    <div class="form-group row mt-1">
                                        <label class="col-sm-3 col-form-label text-right">Gambar (png,jpg,jpeg)</label>
                                        <div class="col-sm-9">
                                            <input type="file" name="image[]" id="image_${index}" class="form-control" accept=".jpg,.jpeg,.png">
                                            <img src="" id="image_tag_${index}" width="200px" style="max-height: 200px; object-fit: contain;" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>`;
                const $formElement = $(formHtml).hide();
                $(wrapper).append($formElement);
                $formElement.fadeIn(300);

                $(`#product_id_${index}`).select2({
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
                                    let part_number = item.part_number ? `[${item.part_number}]` : `[${item.code} - ${item.brand}]`;
                                    return {
                                        id: item.id,
                                        text: `${item.name} ${part_number}`,
                                        measure: item.measure
                                    }
                                })
                            };
                        },
                        cache: false
                    }
                }).on('change', function () {
                    const product_id = $(`#product_id_${index}`).val();
                    const selected = $(`#product_id_${index}`).select2('data')[0];
                    const measure = selected ? selected.measure : '';
                    $(`#measure_${index}`).val(measure);
                });

                $(`#type_relation_${index}`).select2().on('change', function () {
                    const productId = $(`#product_id_${index}`).val();
                    const typeRelation = $(this).val();
                    const relationSelect = $(`#relation_item_id_${index}`);
                    if(productId){
                        $.ajax({
                            url: `{{ url('logistic/get_data_relation') }}/${typeRelation}/${productId}`,
                            type: 'GET',
                            success: function (data) {
                                relationSelect.empty();
                                relationSelect.append($('<option>', {
                                    value: '0',
                                    text: 'Silahkan pilih dokumen...'
                                }));
                                $.each(data, function (value, key) {
                                    relationSelect.append($('<option>', {
                                        value: value,
                                        text: key
                                    }));
                                });
                                relationSelect.val('0').trigger('change').select2();
                            },
                            error: function (xhr, status, error) {
                                console.error("AJAX Error Detail:", {
                                    status: status,
                                    error: error,
                                    response: xhr.responseText
                                });
                                alert(`Gagal mengambil data dokumen tujuan.
                                Status: ${status}
                                Error: ${error}
                                Response: ${xhr.responseText}`);
                            }
                        });
                    }
                });

                $(`#company_id_${index}`).select2().on('change', function () {
                    const company_id = $(`#company_id_${index}`).val();
                    const DepartmentSelect = $(`#department_id_${index}`);
                    const LocationSelect = $(`#location_id_${index}`);
                    if(company_id){
                        $.ajax({
                            url: `{{ url('logistic/get_data_department_by_company') }}/${company_id}`,
                            type: 'GET',
                            success: function (data) {
                                DepartmentSelect.empty();
                                DepartmentSelect.append($('<option>', {
                                    value: '0',
                                    text: 'Silahkan pilih department...'
                                }));
                                $.each(data, function (value, key) {
                                    DepartmentSelect.append($('<option>', {
                                        value: value,
                                        text: key
                                    }));
                                });
                                DepartmentSelect.val('0').trigger('change').select2();
                            },
                            error: function (xhr, status, error) {
                                console.error("AJAX Error Detail:", {
                                    status: status,
                                    error: error,
                                    response: xhr.responseText
                                });
                                alert(`Gagal mengambil data department tujuan.
                                Status: ${status}
                                Error: ${error}
                                Response: ${xhr.responseText}`);
                            }
                        });

                        $.ajax({
                            url: `{{ url('logistic/get_data_location_by_company') }}/${company_id}`,
                            type: 'GET',
                            success: function (data) {
                                LocationSelect.empty();
                                LocationSelect.append($('<option>', {
                                    value: '0',
                                    text: 'Silahkan pilih lokasi...'
                                }));
                                $.each(data, function (value, key) {
                                    LocationSelect.append($('<option>', {
                                        value: value,
                                        text: key
                                    }));
                                });
                                LocationSelect.val('0').trigger('change').select2();
                            },
                            error: function (xhr, status, error) {
                                console.error("AJAX Error Detail:", {
                                    status: status,
                                    error: error,
                                    response: xhr.responseText
                                });
                                alert(`Gagal mengambil data lokasi tujuan.
                                Status: ${status}
                                Error: ${error}
                                Response: ${xhr.responseText}`);
                            }
                        });
                    }
                });


                // AutoNumeric init
                new AutoNumeric(`#price_${index}`, {
                    digitGroupSeparator: ',',
                    decimalCharacter: '.',
                    decimalPlaces: 0,
                    unformatOnSubmit: true
                });

                // Init relation type
                $(`#type_relation_${index}`).select2();
                $(`#relation_item_id_${index}`).select2();
                $(`#user_asset_id_${index}`).select2();
                $(`#status_${index}`).select2();
                $(`#location_id_${index}`).select2();
                $(`#company_id_${index}`).select2();
                $(`#department_id_${index}`).select2();

                $(`#image_${index}`).change(function () {
                    readURL(this, index);
                });

                function readURL(input, index) {
                    if (input.files && input.files[0]) {
                        var reader = new FileReader();
                        reader.onload = function (e) {
                            $(`#image_tag_${index}`).attr('src', e.target.result);
                        }
                        reader.readAsDataURL(input.files[0]);
                    }
                }
            } else {
                Swal.fire('Peringatan!', 'Hanya bisa maksimal 9 form', 'warning');
            }
        });

        // Handle delete form
        $(document).on("click", ".btn_delete_row", function () {
            const $item = $(this).closest(".item-form");
            $item.fadeOut(300, function () {
                $item.remove();
                count--;
                reindexForms();
            });
        });
        // Reindexing after delete
        function reindexForms() {
            $(".item-form").each(function (i) {
                const newIndex = i + 1;
                $(this).attr("data-index", newIndex);
                $(this).find("h6").text(`FORM-${newIndex}`);
            });
        }

        $(document).on('click', ".btn-submittt", function(e) {
            var _this = $(this);
            var form = _this.parents('form');
            e.preventDefault();

            var isProductEmpty = false;
            var isCompanyEmpty = false;

            $("select[name='product_id[]']").each(function () {
                if (!$(this).val()) {
                    isProductEmpty = true;
                }
            });
            $("select[name='company_id[]']").each(function () {
                if (!$(this).val()) {
                    isCompanyEmpty = true;
                }
            });

            if (isProductEmpty || isCompanyEmpty) {
                let message = '';
                if (isCompanyEmpty) message += '• Pastikan semua field company telah diisi.<br>';
                if (isProductEmpty) message += '• Pastikan semua field produk telah diisi.<br>';
                Swal.fire({
                    icon: 'warning',
                    title: 'Data belum lengkap',
                    html: message,
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

@endsection
