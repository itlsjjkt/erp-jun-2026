@extends('layouts.app')

@section('content')

<div class="row gap-20 pos-r">
    {{-- ===================== KOLOM KIRI ===================== --}}
    <div class="col-lg-4 col-12">
        <div class="layers bd bgc-white mb-3">
            <div class="p-20 w-100">
                <div class="layer w-100" style="overflow-x: auto; white-space: nowrap;">
                    <div>
                        <h6><strong>PENGUMUMAN</strong></h6>
                    </div>
                    <br>
                    <div><strong>Welcome,</strong></div>
                    <div>Selamat datang {!! Auth::user()->name !!} di Shipping ERP System.</div>
                </div>
            </div>
        </div>

        <div class="layers bd bgc-white mb-3">
            <div class="layer w-100 text-end" style="padding-left: 20px; padding-top: 20px;">
                <h6><strong>JUMLAH REQUEST ITEM COMPANY</strong></h6>
            </div>
            <div class="layer w-100 p-20" style="display: flex; justify-content: center; align-items: center; flex-direction: column;">
                <div class="pt-2 pb-2 border border-primary mt-2 filter-company"
                    data-id=""
                    style="cursor: pointer; box-sizing: border-box; width: 100%; display: flex; justify-content: space-between; align-items: center; background-color: #e3f2fd; border-width: 2px !important;">
                    <span style="padding-left:10px;" class="c-blue-600"><strong>SHOW ALL COMPANY</strong></span>
                    <span style="padding-right:10px;" class="ti-layers-alt"></span>
                </div>
                @foreach ($dataCompany as $data)
                    <div class="pt-2 pb-2 border border-info mt-2 filter-company"
                        data-id="{{ $data->company_id }}"
                        style="cursor: pointer; box-sizing: border-box; width: 100%; display: flex; justify-content: space-between; align-items: center; background-color: rgba(128, 128, 128, 0.1);">
                        <span style="padding-left:10px;" class="c-grey-600 text-capitalize">
                            <strong>{{ strtoupper($data->company) }}</strong>
                        </span>
                        <span style="padding-right:10px;" class="c-grey-600 font-weight-bold">
                            {{ number_format($data->total, 0, ',', '.') }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ===================== KOLOM KANAN ===================== --}}
    <div class="col-lg-8 col-12">
        <div class="layers bd bgc-white p-20 mb-3">
            <div class="layer w-100 mt-1">

                {{-- Header: judul + tombol toggle export --}}
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0"><strong>DAFTAR REQUEST ITEM</strong></h6>
                    <button
                        class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1"
                        id="btnToggleExport"
                        type="button"
                        data-toggle="collapse"
                        data-target="#panelFilterExport"
                        aria-expanded="false"
                        aria-controls="panelFilterExport">
                        <i class="ti-download mr-1"></i>
                         Export
                        <i class="ti-angle-down ml-1" id="iconChevronExport" style="transition: transform .2s;"></i>
                    </button>
                </div>

                {{-- ===== PANEL FILTER & EXPORT (collapsible) ===== --}}
                <div class="collapse mb-3" id="panelFilterExport">
                    <div class="border rounded p-3" style="background-color: #f8f9fa;">
                        <form id="formExport" method="GET">
                            @csrf

                            <div class="form-row">
                                {{-- Company --}}
                                <div class="form-group col-md-6">
                                    <label class="small font-weight-bold text-muted mb-1">Company</label>
                                    @if(isAdministrator())
                                        {!! Form::select('company_id', $company, old('company_id'), [
                                            'class'       => 'form-control form-control-sm select2-export company-export',
                                            'id'          => 'export_company_id',
                                            'placeholder' => 'Silahkan pilih...'
                                        ]) !!}
                                    @else
                                        <input type="text" readonly class="form-control form-control-sm"
                                            value="{{ getDataByID('companies', Auth::user()->company_id)->name }}">
                                        <input type="hidden" name="company_id" value="{{ Auth::user()->company_id }}">
                                    @endif
                                </div>

                                {{-- Lokasi / Kapal --}}
                                <div class="form-group col-md-6">
                                    <label class="small font-weight-bold text-muted mb-1">Lokasi / Kapal</label>
                                    @if(isAdministrator() || isAdmin())
                                        <select class="form-control form-control-sm select2-export location-export"
                                            name="location_id" id="export_location_id">
                                            <option value="">Silahkan pilih...</option>
                                        </select>
                                    @elseif(isAdministratorCompany() || isLocationAdministrator() || isEmployeeAdministrator())
                                        {!! Form::select('location_id', $location, old('location_id'), [
                                            'class' => 'form-control form-control-sm location-export select2-export'
                                        ]) !!}
                                    @else
                                        <input type="text" readonly class="form-control form-control-sm"
                                            value="{{ getDataByID('locations', Auth::user()->location_id)->name }}">
                                        <input type="hidden" name="location_id" value="{{ Auth::user()->location_id }}">
                                    @endif
                                </div>
                            </div>

                            <div class="form-row">
                                {{-- Department --}}
                                <div class="form-group col-md-6">
                                    <label class="small font-weight-bold text-muted mb-1">Department</label>
                                    {!! Form::select('department_id', $department, old('department_id'), [
                                        'class' => 'form-control form-control-sm select2-export department-export'
                                    ]) !!}
                                </div>

                                {{-- Project --}}
                                <div class="form-group col-md-6">
                                    <label class="small font-weight-bold text-muted mb-1">Project</label>
                                    {!! Form::select('project_id', $project, old('project_id'), [
                                        'class' => 'form-control form-control-sm select2-export'
                                    ]) !!}
                                </div>
                            </div>

                            <div class="form-row">
                                {{-- Tanggal Input --}}
                                <div class="form-group col-12">
                                    <label class="small font-weight-bold text-muted mb-1">
                                        Tanggal Input <span class="text-danger">*</span>
                                        <span class="text-muted font-weight-normal">(maks. 31 hari)</span>
                                    </label>
                                    <div class="input-group input-group-sm">
                                        <input type="text" name="start_date"
                                            class="form-control datepicker"
                                            value="{{ date('m/d/Y') }}"
                                            placeholder="mm/dd/yyyy">
                                        <div class="input-group-prepend input-group-append">
                                            <div class="input-group-text">ke</div>
                                        </div>
                                        <input type="text" name="end_date"
                                            class="form-control datepicker"
                                            value="{{ date('m/d/Y') }}"
                                            placeholder="mm/dd/yyyy">
                                    </div>
                                </div>
                            </div>

                            <hr class="mt-1 mb-2">

                            {{-- Tombol Export --}}
                            <div class="d-flex flex-wrap" style="gap: 6px;">
                                <button type="submit" class="btn btn-warning btn-sm" id="btnExportNewFormat">
                                    <i class="ti-file mr-1"></i> Export New Format
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                {{-- ===== END PANEL FILTER & EXPORT ===== --}}

                {{-- Tabel DataTables --}}
                <div class="table-responsive mt-2" style="overflow-y: hidden !important;">
                    <table id="dataTablesItem" class="table table-striped table-bordered" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th>NAMA PRODUK</th>
                                <th>NO DPM</th>
                                <th>LOKASI/KAPAL</th>
                                <th>TGL PEMBUATAN DPM</th>
                                <th>STATUS</th>
                                <th class="text-center">#</th>
                            </tr>
                        </thead>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>

{{-- Modal Timeline --}}
<div class="modal fade" id="modalMd" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document" style="max-width: 80%;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalMdTitle">TIMELINE ITEM DPM</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="modalError"></div>
                <div id="modalMdContent"></div>
            </div>
        </div>
    </div>
</div>

@section('js')
<script>
$(document).ready(function () {

    // =========================================================
    // 1. DATATABLES
    // =========================================================
    var selectedCompanyId = '';

    var table = $('#dataTablesItem').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route('logistic.monitoring.item.datatables') }}',
            data: function (d) {
                d.company_id = selectedCompanyId;
            }
        },
        pageLength: 10,
        columns: [
            { data: 'product',     name: 'master_item_products.name' },
            { data: 'no_dpm',      name: 'purchases.doc_no' },
            { data: 'location',    name: 'locations.name' },
            { data: 'created',     name: 'purchases.created_at' },
            { data: 'status',      name: 'status', searchable: true },
            { data: 'action',      name: 'action', orderable: false, searchable: false },
            { data: 'part_number', name: 'master_item_products.part_number', visible: false },
            { data: 'brand',       name: 'master_item_brands.name', visible: false },
        ],
        order: [[3, 'desc']]
    });

    // =========================================================
    // 2. FILTER COMPANY (sidebar)
    // =========================================================
    $(document).on('click', '.filter-company', function (e) {
        e.preventDefault();

        selectedCompanyId = $(this).data('id');
        table.draw();

        $('.filter-company').css('background-color', 'rgba(128, 128, 128, 0.1)');
        $('.filter-company').removeClass('border-primary').addClass('border-info');

        if (selectedCompanyId === '' || selectedCompanyId === undefined) {
            $(this).css('background-color', '#e3f2fd');
        } else {
            $(this).css('background-color', 'rgba(0, 123, 255, 0.2)');
        }
        $(this).addClass('border-primary');
    });

    // =========================================================
    // 3. CHEVRON TOGGLE ANIMASI
    // =========================================================
    $('#btnToggleExport').on('click', function () {
        var isExpanded = $(this).attr('aria-expanded') === 'true';
        $('#iconChevronExport').css('transform', isExpanded ? 'rotate(0deg)' : 'rotate(180deg)');
    });

    // =========================================================
    // 4. SELECT2 PADA PANEL EXPORT
    // =========================================================
    $('.select2-export').select2({
        placeholder: 'Silahkan pilih...',
        allowClear: true,
        width: '100%'
    });

    // =========================================================
    // 5. DYNAMIC LOAD LOKASI SAAT COMPANY BERUBAH (panel export)
    // =========================================================
    $('.company-export').on('change', function () {
        var companyId = $(this).val();
        var $location = $('.location-export');
        var $department = $('.department-export');

        $location.empty().append('<option value="">Silahkan pilih...</option>').trigger('change');
        $department.empty().append('<option value="">Silahkan pilih...</option>').trigger('change');

        if (!companyId) return;

        $.ajax({
            url: '{{ route('master.get_location') }}/' + companyId,
            type: 'GET',
            success: function (data) {
                $.each(data, function (value, key) {
                    $location.append($('<option></option>').attr('value', value).text(key));
                });
                $location.trigger('change');
            }
        });

        $.ajax({
            url: '{{ route('master.get_department') }}/' + companyId,
            type: 'GET',
            success: function (data) {
                $.each(data, function (value, key) {
                    $department.append($('<option></option>').attr('value', value).text(key));
                });
                $department.trigger('change');
            }
        });
    });

    // =========================================================
    // 6. TOMBOL EXPORT NEW FORMAT
    // =========================================================
    $('#btnExportNewFormat').on('click', function (e) {
        e.preventDefault();
        $('#formExport').attr('action', '{{ route('purchase_request.export_new') }}').submit();
    });

    // =========================================================
    // 7. MODAL TIMELINE
    // =========================================================
    $(document).on('click', '.modalMd', function (e) {
        e.preventDefault();
        var url = $(this).attr('value');

        $('#modalMdContent').html('');
        $('.modalError').html('');

        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'html',
            success: function (response) {
                $('#modalMdContent').html(response);
                $('#modalMd').modal('show');
            },
            error: function () {
                $('.modalError').html('<div class="alert alert-danger">Failed to load history item. Please try again later.</div>');
            }
        });
    });

});
</script>
@stop

@endsection
