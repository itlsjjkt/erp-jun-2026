@extends('layouts.app')

@section('page-header')
    Daftar Permintaan Material (DPM)
@stop

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('purchase_request.index') }}">DPM</a></li>
        <li class="breadcrumb-item active" aria-current="page">Tambah</li>
    </ol>
@endsection


@section('content')
	<div class="bgc-white p-30 bd">
        <div class="alert alert-info mT-3">
            <ul>
                <li>
                    Silahkan pilih item yang akan dilakukan penggantian
                </li>
                <li>
                    Data lokasi WTI (Tujuan) harus tidak boleh lebih dari 1 lokasi
                </li>
                <li>
                    Berikut daftar Data Warehouse Transfer dengan type pinjaman yang belum dilakukan penggantian
                </li>
            </ul>
        </div>
        <div class="row">
            <h6 class="text-left col-6">
                Step 1 of 2
            </h6>
            <div class="text-right col-6">
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-striped mt-2" id="dataTables">
                <thead class="item_form">
                    <tr>
                        <th>NO WTO</th>
                        <th>Lokasi WTO (Asal)</th>
                        <th>NO WTI</th>
                        <th>Lokasi WTI (Tujuan)</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($data as $val)
                        <tr class="selectable-row">
                            <td>
                                {{ $val->doc_no_wto }}
                            </td>
                            <td>
                                {{ getCompanyByLocationId($val->location_id_wto)->code.'-'.getLocationByID($val->location_id_wto)->name }}
                            </td>
                            <td>
                                {{ $val->doc_no }}
                            </td>
                            <td>
                                {{ getCompanyByLocationId($val->location_id)->code.'-'.getLocationByID($val->location_id)->name }}
                            </td>
                            <td>
                                <form method="POST" action="{{ route('purchase_request.purchase_request_create_by_wti') }}">
                                    @csrf
                                    <input type="hidden" name="location_asal" value="{{ $val->location_id_wto }}">
                                    <input type="hidden" name="location_tujuan" value="{{ $val->location_id }}">
                                    <input type="hidden" name="wti_id" value="{{ $val->id }}">
                                    <button type="submit" class="btn btn-primary"
                                        style="padding: 0.25rem 0.5rem; font-size: 0.45rem; line-height: 1;">
                                        Buat DPM <i class="ti-arrow-right"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@stop

@section('js')
<script>
    // DataTables
    $(document).ready(function(){
        $('#dataTables').DataTable({
            processing: true,
            order: [],
            pageLength: 50,
            columnDefs: [
                {
                    targets: [0,4],
                    orderable: false,
                    searchable: false
                },
                {
                    targets: [1,2,3],
                    orderable: false,
                    searchable: true
                }
            ]
        });
    });
</script>
@stop
