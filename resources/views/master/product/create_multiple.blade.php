@extends('layouts.app')

@section('page-header')
Produk
@endsection

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('master.item_products.index') }}">Produk</a></li>
        <li class="breadcrumb-item active" aria-current="page">Create Multiple</li>
    </ol>
@endsection

@section('content')

	<div class="row mB-40">

        <div class="col-sm-12">
            <div class="bgc-white p-20 bd">
                @include('master.menulogistik')
                <h6><a class="float-left" href="{{ route('master.item_products.index') }}"><i class="ti-arrow-left mR-10"></i></a> Produk</h6>
                <hr class="mB-30">

                <div class="alert alert-info mb-5">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <strong>INFORMASI</strong> <br>
                    Mohon perhatikan pada saat Pembuatan Master Produk, sistem akan memberikan informasi Master product dengan nama yang mirip. <br>
                    Mohon perhatikan pada saat Pembuatan Master Produk, sistem akan memberikan informasi Part Number dengan nama yang mirip. <br>
                    Mohon perhatikan pada saat Pembuatan Master Produk secara multiple, dahulukan untuk menentukan jumlah form item yang akan dibuat (Max 20 Item), lalu dilanjutkan untuk mengisi data.
                </div>

                <div class="d-flex align-items-center mb-3">
                    <div class="border p-10 text-center mr-3">
                        <div class="text-left">Jumlah Item</div>
                        <h4 id="row-count-display" class="font-weight-bold mb-0 text-danger">1</h4>
                    </div>
                    <button type="button" class="btn btn-success ml-auto" id="add-row">
                        <i class="ti-plus"></i> Tambah Item
                    </button>
                </div>

                {!! Form::open(['route' => 'master.item_products.store_multiple', 'method' => 'POST']) !!}

                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <label> <strong>Kategori</strong> <span class="text-danger">*</span></label>
                                {!! Form::select('item_id', $item, old('item_id'), [
                                    'class' => 'form-control select2 item',
                                    'required' => ''
                                ]) !!}
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group">
                                <label><strong>Brand</strong></label>
                                <div class="d-flex align-items-center">
                                    <div style="flex: 1;">
                                        {!! Form::select('brand_id_master', $brand, null, ['class' => 'form-control select2', 'id' => 'main-brand', 'disabled' => true ]) !!}
                                        @if($errors->has('brand_id'))
                                            <p class="help-block text-danger">{{ $errors->first('brand_id') }}</p>
                                        @endif
                                    </div>
                                    <div class="ml-3 form-check form-switch">
                                        <input type="checkbox" class="form-check-input magic-checkbox"  id="brand_id_toggle" checked>
                                        {{-- <input type="checkbox" class="switch switch-info" id="brand_id_toggle" checked > --}}
                                        <label class="form-check-label" for="brand_id_toggle">Untuk Semua Brand</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="table-responsive">
                        <table class="table table-bordered" id="product-table">
                            <thead class="text-center">
                                <tr>
                                    <th rowspan="2" style="width: 220px;">Nama Produk <span class="text-danger">*</span></th>
                                    <th rowspan="2" style="width: 150px;">PN/SPEC</th>
                                    <th rowspan="2" style="width: 100px;">Brand</th>
                                    <th colspan="2" style="width: 200px;">Satuan <span class="text-danger">*</span></th>
                                    <th rowspan="2" style="width: 80px;">Konversi <span class="text-danger">*</span></th>
                                    <th rowspan="2" style="width: 90px;">Status</th>
                                    <th rowspan="2" style="width: 30px;">Aksi</th>
                                </tr>
                                <tr>
                                    <th style="width: 100px;">Pembelian <span class="text-danger">*</span></th>
                                    <th style="width: 100px;">Inventory <span class="text-danger">*</span></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="product-row">
                                    <td style="position: relative;">
                                        <input type="text" name="name[]" class="form-control productName" autocomplete="off" required>
                                        <div class="productListName">
                                            <div class="productListContent"></div>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="text" name="part_number[]" class="form-control productPartNumber" autocomplete="off">
                                        <div class="productListPartNumber">
                                            <div class="productListContentPN"></div>
                                        </div>
                                    </td>
                                    <td>
                                        {!! Form::select('brand_id[]', $brand, null, ['class' => 'form-control select2 brand-item']) !!}
                                        @if($errors->has('brand_id'))
                                            <p class="help-block text-danger">
                                                {{ $errors->first('brand_id') }}
                                            </p>
                                        @endif
                                    </td>
                                    <td>
                                        {!! Form::select('measure_id[]', $measure, old('measure_id'), ['class' => 'form-control select2 measure', 'required' => '']) !!}
                                        @if($errors->has('measure_id'))
                                            <p class="help-block">
                                                {{ $errors->first('measure_id') }}
                                            </p>
                                        @endif
                                    </td>
                                    <td>
                                        {!! Form::select('measure_inventory[]', $measure, old('measure_inventory'), ['class' => 'form-control select2 measure', 'required' => '']) !!}
                                        @if($errors->has('measure_inventory'))
                                        <p class="help-block">
                                            {{ $errors->first('measure_inventory') }}
                                        </p>
                                        @endif
                                    </td>
                                    <td><input type="number" name="conversion[]" class="form-control" autocomplete="off" required></td>
                                    <td>
                                        <select name="status" class="form-control form-control-sm" id="status" style="width: 100%;" readonly>
                                            <option value="1" {{ (old('status', $product->status ?? '') == 1) ? 'selected' : '' }}>Aktif</option>
                                        </select>
                                        @if($errors->has('status'))
                                            <p class="help-block text-danger">{{ $errors->first('status') }}</p>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-danger remove-row"> <i class="ti-trash"></i> </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
            </div>

            <div class="row mt-4">
                <div class="col">
                    <a href="#" onclick="history.back()" class="btn btn-light mr-1 text-uppercase fsz-sm fw-600">Kembali</a>
                </div>
                <div class="col d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary text-uppercase fsz-sm fw-600">Simpan Produk</button>
                </div>
            </div>

            {!! Form::close() !!}

        </div>
    </div>

@endsection


@section('js')
{{-- Tambah Baris Form Multiple --}}
<script>
    $(document).ready(function () {
        const maxRows = 20;
        const brandOptions = `@foreach($brand as $key => $value)<option value="{{ $key }}">{{ $value }}</option>@endforeach`;
        const measureOptions = `@foreach($measure as $key => $value)<option value="{{ $key }}">{{ $value }}</option>@endforeach`;
        const measureInventoryOptions = `@foreach($measure as $key => $value)<option value="{{ $key }}">{{ $value }}</option>@endforeach`;

        function updateRowCount() {
            const count = $('#product-table tbody .product-row').length;
            $('#row-count-display').text(count);
        }

        $('#add-row').click(function () {
        const rowCount = $('#product-table tbody .product-row').length;
            if (rowCount < maxRows) {
                const newRow = `
                    <tr class="product-row">
                        <td>
                            <input type="text" name="name[]" class="form-control productName" autocomplete="off" required>
                            <div class="productListName">
                                <div class="productListContent"></div>
                            </div>
                        </td>

                        <td>
                            <input type="text" name="part_number[]" class="form-control productPartNumber" autocomplete="off">
                                <div class="productListPartNumber">
                                    <div class="productListContentPN"></div>
                                </div>
                        </td>

                        <td>
                            ${
                                $('#brand_id_toggle').is(':checked')
                                    ? `
                                        <input type="hidden" name="brand_id[]" value="${$('#main-brand').val()}" class="brand-item-hidden">

                                        <input type="text" class="form-control brand-item readonly" value="${brandMap[$('#main-brand').val()] ?? 'Unknown'}" readonly>
                                    `
                                    : `<select name="brand_id[]" class="form-control select2 brand-item">${brandOptions}</select>`
                            }
                        </td>

                        <td>
                            <select name="measure_id[]" class="form-control select2" required>
                                ${measureOptions}
                            </select>
                        </td>
                        <td>
                            <select name="measure_inventory[]" class="form-control select2" required>
                                ${measureInventoryOptions}
                            </select>
                        </td>
                        <td><input type="number" name="conversion[]" class="form-control" required></td>
                        <td>
                            <select name="status" class="form-control form-control-sm" readonly>
                                <option value="1" selected>Aktif</option>
                            </select>
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-danger remove-row"><i class="ti-trash"></i></button>
                        </td>
                    </tr>`;

                $('#product-table tbody').append(newRow);
                $('.select2').select2(); // Apply select2 ke semua

                // Jika toggle aktif, jalankan ulang toggle change untuk baris baru
                if ($('#brand_id_toggle').is(':checked')) {
                    $('#brand_id_toggle').trigger('change');
                }

                updateRowCount();
            } else {
                Swal.fire({
                    icon: 'warning',
                    title: 'Peringatan',
                    text: 'Maksimal 20 Item',
                    confirmButtonText: 'OK'
                });
            }
        });

        // hapus row / baris form
        $(document).on('click', '.remove-row', function () {
            const rowCount = $('#product-table tbody .product-row').length;
            if (rowCount > 1) {
                $(this).closest('tr').remove();
                updateRowCount();
            } else {
                Swal.fire({
                    icon: 'warning',
                    title: 'Peringatan',
                    text: 'Minimal 1 Item',
                    confirmButtonText: 'OK'
                });
            }
        });

        updateRowCount();
    });
</script>

{{-- Suggestion Form Product by Name --}}
<script>
    // Suggestion Data Get
    $(document).on('keyup', '.productName', function () {
        var $this = $(this);
        var productName = $this.val();

        // Ambil Elemen Suggestion Berdasarkan Input
        var $wrapper = $this.closest('td');
        var $list = $wrapper.find('.productListName');
        var $content = $wrapper.find('.productListContent');

            if (productName.length > 3) {
                $.ajax({
                    url: "{{ route('master.get_product') }}_name?q=" + productName,
                    type: 'GET',
                    cache: false,
                    success: function (data) {
                        if ($.trim(data) !== '') {
                            $list.show();
                            $content.html(data);

                            // Nonaktifkan Semua Klik Dalam Suggestion = Cache
                            $content.find('*').css('pointer-events', 'none');
                        } else {
                            $list.hide();
                            $content.html('');
                        }
                    },
                    error: function(xhr) {
                        console.log("AJAX Error:", xhr.responseText);
                    }
                });
            } else {
                $list.hide();
                $content.html('');
            }
        });

    // Blokir Event Click Dalam Suggestion
    $(document).on('click', '.productListContent', function (e) {
        e.preventDefault();
        e.stopPropagation();
    });

    // === Suggestion Release OnChange / OnClick ===
    // klik input .productName
    $(document).on('click', '.productName', function () {
        var $this = $(this);
        var productName = $this.val();

        // tutup semua suggestion sebelumnya
        $('.productListName').each(function () {
            $(this).hide();
            $(this).find('.productListContent').html('');
        });

        // jika isi teks dalam form cukup, maka ambil suggestion baru
        if (productName.length > 3) {
            var $wrapper = $this.closest('td');
            var $list = $wrapper.find('.productListName');
            var $content = $wrapper.find('.productListContent');

            $.ajax({
                url: "{{ route('master.get_product') }}_name?q=" + productName,
                type: 'GET',
                cache: false,
                success: function (data) {
                    if ($.trim(data) !== '') {
                        $list.show();
                        $content.html(data);
                        $content.find('*').css('pointer-events', 'none');
                    } else {
                        $list.hide();
                        $content.html('');
                    }
                },
                error: function(xhr) {
                    console.log("AJAX Error:", xhr.responseText);
                }
            });
        }
    });

    // Tutup Suggestion
    $(document).on('click', function (e) {
        if (!$(e.target).closest('.productName, .productListName').length) {
            $('.productListName').each(function () {
                $(this).hide();
                $(this).find('.productListContent').html('');
            });
        }
    });
</script>

{{-- Suggestion Form Product by Part Number --}}
<script>
    $(document).on('keyup', '.productPartNumber', function () {
        var $this = $(this);
        var productPartNumber = $this.val();

        // Ambil Elemen Suggestion Berdasarkan Input
        var $wrapper = $this.closest('td');
        var $list = $wrapper.find('.productListPartNumber');
        var $content = $wrapper.find('.productListContentPN');

            if (productPartNumber.length > 3) {
                $.ajax({
                    url: "{{ route('master.get_product') }}_part_number?q=" + productPartNumber,
                    type: 'GET',
                    cache: false,
                    success: function (data) {
                        if ($.trim(data) !== '') {
                            $list.show();
                            $content.html(data);

                            // Nonaktifkan Semua Klik Dalam Suggestion = Cache
                            $content.find('*').css('pointer-events', 'none');
                        } else {
                            $list.hide();
                            $content.html('');
                        }
                    },
                    error: function(xhr) {
                        console.log("AJAX Error:", xhr.responseText);
                    }
                });
            } else {
                $list.hide();
                $content.html('');
            }
        });

    // Blokir Event Click Dalam Suggestion
    $(document).on('click', '.productListContentPN', function (e) {
        e.preventDefault();
        e.stopPropagation();
    });

    // === Suggestion Release OnChange / OnClick ===
    // klik input .productPartNumber
    $(document).on('click', '.productPartNumber', function () {
        var $this = $(this);
        var productPartNumber = $this.val();

        // tutup semua suggestion sebelumnya
        $('.productListPartNumber').each(function () {
            $(this).hide();
            $(this).find('.productListContentPN').html('');
        });

        // jika isi teks dalam form cukup, maka ambil suggestion baru
        if (productPartNumber.length > 3) {
            var $wrapper = $this.closest('td');
            var $list = $wrapper.find('.productListPartNumber');
            var $content = $wrapper.find('.productListContentPN');

            $.ajax({
                url: "{{ route('master.get_product') }}_part_number?q=" + productPartNumber,
                type: 'GET',
                cache: false,
                success: function (data) {
                    if ($.trim(data) !== '') {
                        $list.show();
                        $content.html(data);
                        $content.find('*').css('pointer-events', 'none');
                    } else {
                        $list.hide();
                        $content.html('');
                    }
                },
                error: function(xhr) {
                    console.log("AJAX Error:", xhr.responseText);
                }
            });
        }
    });

    // Tutup Suggestion
    $(document).on('click', function (e) {
        if (!$(e.target).closest('.productPartNumber, .productListPartNumber').length) {
            $('.productListPartNumber').each(function () {
                $(this).hide();
                $(this).find('.productListContentPN').html('');
            });
        }
    });
</script>

{{-- Main Brand Checklist Multiple --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const toggle = document.getElementById('brand_id_toggle');
        const brandSelect = $('#main-brand');

        // fungsi untuk mengatur enable/disable
        function updateBrandSelect() {
            if (toggle.checked) {
                brandSelect.prop('disabled', false).trigger('change.select2');
            } else {
                brandSelect.prop('disabled', true).trigger('change.select2');
            }
        }

        // jalankan fungsi saat toggle diubah
        toggle.addEventListener('change', updateBrandSelect);

        // toggle "change" untuk menjalankan semua efek
        toggle.dispatchEvent(new Event('change'));

        // kondisi awal: toggle off, select disabled
        updateBrandSelect();
    });
</script>

{{-- Row Form Multiple Brand --}}
<script>
    const brandMap = @json($brand);

    $(document).ready(function() {
        $('#brand_id_toggle').on('change', function() {
            const isChecked = $(this).is(':checked');
            const selectedBrand = $('#main-brand').val();

            $('.brand-item').each(function() {
                const currentEl = $(this);
                const td = currentEl.closest('td');

                if (isChecked) {
                    // const val = currentEl.is('select') ? currentEl.val() : selectedBrand;
                    const val = selectedBrand; // ketika mainbrand unchecklist dan di checklist
                    const brandName = brandMap[val] ?? 'Unknown';

                    // sembunyikqn input brand_id
                    const hiddenInput = $('<input>', {
                        type: 'hidden',
                        name: currentEl.attr('name'),
                        value: val,
                        class: 'brand-item-hidden'
                    });

                    // tampilkan input brand name
                    const displayInput = $('<input>', {
                        type: 'text',
                        value: brandName,
                        class: 'form-control brand-item readonly',
                        readonly: true
                    });

                    if (currentEl.is('select')) currentEl.select2('destroy');
                    currentEl.remove();
                    td.append(hiddenInput).append(displayInput);
                } else {
                    const val = td.find('input[type="hidden"]').val();
                    const nameAttr = td.find('input[type="hidden"]').attr('name');

                    const select = $('<select>', {
                        name: nameAttr,
                        class: 'form-control select2 brand-item'
                    });

                    for (const [key, value] of Object.entries(brandMap)) {
                        select.append($('<option>', {
                            value: key,
                            text: value,
                            selected: key == val
                        }));
                    }

                    td.find('input').remove();
                    td.append(select);
                    select.select2();
                }
            });
        });

        $('#main-brand').on('change', function() {
            if ($('#brand_id_toggle').is(':checked')) {
                const selectedBrand = $(this).val();
                $('.brand-item-hidden').val(selectedBrand);
                $('.brand-item.readonly').val(brandMap[selectedBrand] ?? 'Unknown');
            }
        });

        if ($('#brand_id_toggle').is(':checked')) {
            $('#brand_id_toggle').trigger('change');
        }
    });
</script>
@endsection


@section('css')
<style>
    td {
        position: relative;
    }

    /* Name */
    .productListName {
        display: none;
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 999;
        width: 600px;
        max-height: 500px;
        overflow-y: auto;
    }

    .productListContent * {
        pointer-events: none;
    }

    /* Part Number */
    .productListPartNumber {
        display: none;
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 999;
        width: 600px;
        max-height: 500px;
        overflow-y: auto;
    }

    .productListContentPN * {
        pointer-events: none;
    }

    .table-fixed-header thead {
        position: sticky;
        top: 0;
        background-color: #dc3545;
        color: #fff;
        z-index: 2;
    }

    select.readonly {
        pointer-events: none;
        background-color: #e9ecef;
    }
</style>
@endsection
