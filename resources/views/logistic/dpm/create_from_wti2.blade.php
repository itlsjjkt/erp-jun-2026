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
    {!! Form::open(['method' => 'POST', 'route' => ['purchase_request.purchase_request_store_by_wti'], 'id'=>'form-dpm', 'files' => true]) !!}
        <input type="hidden" name="status" value="{{1}}">
        <input type="hidden" name="lokasi_wto" value="{{$locationAsal}}">
        <input type="hidden" name="lokasi_wti" value="{{$locationTujuan}}">
        <input type="hidden" name="wti_id" value="{{$dataWti->id}}">
        <div class="bgc-white p-30 bd">
            <div class="row">
                <h6 class="text-left col-6">
                    Step 2 of 2
                </h6>
                <div class="text-right col-6">
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-lg-6">
                    <div class="alert alert-info mT-3">
                        <div class="mb-3"><strong>Data Transfer Out</strong></div>
                        <div class="row">
                            <label class="col-sm-3">No Dokumen</label>
                            <div class="col-sm-7">
                                : {{$dataWti->doc_no_wto}}
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label class="col-sm-3">Lokasi WTO</label>
                            <div class="col-sm-7">
                                : {{getCompanyByLocationId($dataWti->location_id_wto)->code.' - '.getLocationByID($dataWti->location_id_wto)->name}}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="alert alert-info mT-3">
                        <div class="mb-3"><strong>Data Transfer In</strong></div>
                        <div class="row">
                            <label class="col-sm-3">No Dokumen</label>
                            <div class="col-sm-7">
                                : {{ $dataWti->doc_no }}
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label class="col-sm-3">Lokasi WTI</label>
                            <div class="col-sm-7">
                                : {{getCompanyByLocationId($dataWti->location_id)->code.' - '.getLocationByID($dataWti->location_id)->name}}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="row mb-3">
                        <label class="col-sm-3">Tipe Unit<span class="text-danger">*</span></label>
                        <div class="col-sm-7">
                            <select name="type" class="form-control select2">
                                <option value="po">PO</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <label class="col-sm-3">Location  <span class="text-danger">*</span></label>
                        <div class="col-sm-7">
                            <input type="text" readonly class="form-control" value="{{ $dataCompanyTujuan->code . ' - ' . $dataLokasiTujuan->name }}">
                            <input type="hidden" name="location_tujuan" id="location_tujuan" value="{{ $dataLokasiTujuan->id }}">
                            <p class="help-block"></p>
                            @if($errors->has('location_tujuan'))
                                <p class="help-block">
                                    {{ $errors->first('location_tujuan') }}
                                </p>
                            @endif
                        </div>
                    </div>
                    <div class="row">
                        <label  class="col-sm-3">Kapal/Departemen <span class="text-danger">*</span></label>
                        <div class="col-sm-7">
                            {!! Form::select('department_id', $department, old('department_id'), ['class' => 'form-control select2', 'id' =>'department']) !!}
                            <p class="help-block"></p>
                            @if($errors->has('department_id'))
                                <p class="help-block">
                                    {{ $errors->first('department_id') }}
                                </p>
                            @endif
                        </div>
                    </div>
                    <div class="row">
                        <label  class="col-sm-3">Project  <span class="text-danger">*</span></label>
                        <div class="col-sm-7">
                            @if ($selectedProject)
                                {!! Form::select(
                                    'project_id',
                                    $project,
                                    $selectedProject->id,
                                    ['class' => 'form-control select2', 'id' => 'project', 'disabled' => true]
                                ) !!}
                                {!! Form::hidden('project_id', $selectedProject->id) !!}
                            @else
                                {!! Form::select(
                                    'project_id',
                                    $project,
                                    old('project_id'),
                                    ['class' => 'form-control select2', 'required' => '', 'id' => 'project']
                                ) !!}
                            @endif

                            <p class="help-block"></p>
                            @if($errors->has('project_id'))
                                <p class="help-block">
                                    {{ $errors->first('project_id') }}
                                </p>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="row">
                        <label class="col-sm-3">Flag & Tanggal dibutuhkan <span class="text-danger">*</span></label>
                        <div class="col-sm-3">
                            <select name="flag" id="flag_" class="form-control select2" required>
                                <option value="normal">Normal</option>
                                <option value="urgent">Urgent</option>
                            </select>
                        </div>
                        <div class="col-sm-4">
                            {!! Form::date('needed_on_date', date("Y-m-d", strtotime("+7 days")), [
                                'class' => 'form-control needed_on_date',
                                'placeholder' => '',
                                'required' => '',
                                'id' => 'needed_on_date_'
                            ]) !!}
                        </div>
                    </div>
                    <div class="row mt-3">
                        <label  class="col-sm-3">Deskripsi</label>
                        <div class="col-sm-7">
                            <textarea type="text" class="form-control" name="description" id="description" ></textarea>
                        </div>
                        <p class="help-block"></p>
                        @if($errors->has('description'))
                            <p class="help-block">
                                {{ $errors->first('description') }}
                            </p>
                        @endif
                    </div>
                    <div class="row mt-3">
                        <label class="col-sm-3">Attachment <i class="fa fa-file-pdf-o text-danger"></i><span class="text-danger">*</span></label>
                        <div class="col-sm-7">
                            {!! Form::myFile('mr_file', '', ['class' => '', 'id' => 'mr_file', 'accept' => 'application/pdf']) !!}
                            <p class="help-block"></p>
                            @if($errors->has('mr_file'))
                                <p class="help-block">
                                    {{ $errors->first('mr_file') }}
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-hover mt-2" id="dataTables">
                    <thead class="item_form">
                        <tr>
                            <th style="width: 50px;">NO</th>
                            <th>Nama Barang</th>
                            <th>QTY</th>
                            <th>Satuan</th>
                            <th>Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $index = 1;
                        @endphp
                        @foreach ($dataWtiItem as $item)
                            <input type="hidden" name="index[]" value="{{$index}}">
                            <input type="hidden" name="product_id[]" value="{{$item->productId}}">
                            <input type="hidden" name="wti_item_id[]" value="{{$item->id}}">
                            <tr class="selectable-row">
                                <td class="text-center">
                                    {{$index}}
                                    @php
                                        $index++;
                                    @endphp
                                </td>
                                <td>
                                    {{'['.$item->productCode.'] '.$item->produkName}} <br>
                                    <small>
                                        PN/Spec : {{$item->ProductPn ?? '-'}} <br>
                                        Brand : {{$item->productBrand}}
                                    </small>
                                </td>
                                <td>
                                    {{($item->qty/$item->productConversion)}}
                                    <input type="hidden" name="qty[]" value="{{($item->qty/$item->productConversion)}}">
                                </td>
                                <td>
                                    {{$item->satuanPembelianName}}
                                    <input type="hidden" name="measure[]" value="{{$item->satuanPembelianName}}">
                                </td>
                                <td style="width: 300px;">
                                    <div style="width: 250px;">
                                        <input type="hidden" name="notes[]" class="form-control" id="notes{{$item->id}}">
                                        <trix-editor input="notes{{$item->id}}"></trix-editor>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-4">
            <a href="#" onclick="history.back()" class="btn btn-light mr-1 text-uppercase fsz-sm fw-600">Kembali</a>
            <input class="btn btn-danger text-uppercase fsz-sm fw-600 float-right" type="submit" id="btn-submit-dpm" value="Create DPM">
        </div>
    {!! Form::close() !!}
@stop

@section('js')
<script>
    // DataTables
    $(document).ready(function () {
        function updateNeededOnDate() {
            const flag = $('#flag_').val();
            const daysToAdd = flag === 'urgent' ? 3 : 7;

            const today = new Date();
            today.setDate(today.getDate() + daysToAdd);

            const year = today.getFullYear();
            const month = String(today.getMonth() + 1).padStart(2, '0');
            const day = String(today.getDate()).padStart(2, '0');

            const newDate = `${year}-${month}-${day}`;
            $('#needed_on_date_').val(newDate);
        }
        updateNeededOnDate();
        $('#flag_').on('change', updateNeededOnDate);

        $(document).on("click", "#btn-submit-dpm", function(e) {
            var _this = $(this);
            var form = _this.parents('form');
            e.preventDefault();
            Swal.fire({
                title: 'Konfirmasi',
                text: 'Apakah anda yakin melanjutkan ini?',
                type: 'question',
                showCancelButton: true,
                confirmButtonColor: 'null',
                cancelButtonColor: 'null',
                confirmButtonClass: 'btn btn-danger',
                cancelButtonClass: 'btn btn-primary',
                confirmButtonText: 'Ya, lanjut',
                cancelButtonText: 'Batal',
            }).then(res => {
                if (res.value) {
                    _this.closest("form").submit();
                }
            });
        });
    });

    </script>
@stop
