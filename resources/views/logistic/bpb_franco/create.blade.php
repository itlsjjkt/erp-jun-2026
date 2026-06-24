@extends('layouts.app')

@section('page-header')
    Bukti Penerimaan Barang Lokal
@stop

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('logistic.bpb_franco.index') }}">Bukti Penerimaan Barang Lokal</a></li>
        <li class="breadcrumb-item active" aria-current="page">Tambah</li>
    </ol>
@endsection


@section('content')
    {!! Form::open([
        'method' => 'POST',
        'route' => ['logistic.bpb_franco.store'],
        'id' => 'formBPB',
        'files' => true
    ]) !!}
    <input type="hidden" name="po_id" value="{{ $po->id }}">
    <input type="hidden" name="locationID" value="{{ $po->purchaseRequisition->location_id }}">

    <div class="bgc-white p-30 bd">
        <h6><a class="float-left" href="{{ route('logistic.bpb_franco.index') }}"><i class="ti-arrow-left mR-10"></i></a> {{ trans('Back') }}</h6>
        <hr class='mB-30'>

        <div class="row mt-5">
            <div class="col-sm-6">
                <div class="row">
                    <label class="col-sm-3">Nomor PO </label>
                    <div class="col-sm-8">: {{ $po->doc_no }}</div>
                </div>
                <div class="row">
                    <label class="col-sm-3">No. DPM </label>
                    <div class="col-sm-8">: <a href="{{ route('logistic.monitoring.dpm.detail', ['id' => Hashids::encode($po->purchaseRequisition->purchase_id)]) }}" target="_blank"> {{ $po->purchaseRequisition->dpm_no }} </a></div>
                </div>
                <div class="row">
                    <label class="col-sm-3">Kapal/Departemen </label>
                    <div class="col-sm-8">: {{ $po->purchaseRequisition->department->name }}</div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="row">
                    <label class="col-sm-3">Lokasi </label>
                    <div class="col-sm-8">: {{ $po->purchaseRequisition->location->name ?? ' -' }}</div>
                </div>
                <div class="row">
                    <label class="col-sm-3">Project </label>
                    <div class="col-sm-8">: {{ $po->purchaseRequisition->project->name }}</div>
                </div>
                <div class="row">
                    <label class="col-sm-3">Dibuat Tanggal</label>
                    <div class="col-sm-8">: {{ idDate($po->created_at) }}</div>
                </div>
            </div>
        </div>
        <hr>
        <div class="row mt-5">
            <div class="col-lg-6">
                <div class="form-group row">
                    <label class="col-sm-3">Penerima <span class="text-danger">*</span></label>
                    <div class="col-sm-8">
                        {!! Form::text('received_by', old('received_by'), ['class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-3">Attachment <small>(pdf)</small> <span class="text-danger">*</span></label>
                    <div class="col-sm-8">
                        <input class="mb-3" name="attachment_file" type="file" accept="application/pdf" value="" required/>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="form-group row">
                    <label class="col-sm-3">Catatan </label>
                    <div class="col-sm-9">
                        {!! Form::textarea('notes', old('notes'), ['class' => 'form-control', 'placeholder' => '','rows' => 2]) !!}
                    </div>
                </div>
            </div>
        </div>

        <div class="alert alert-info mT-3">
           <strong>INFORMASI</strong> <br>
            - Jika produk terdapat pada inventory maka akan menambahkan pada Stock On Hand (SOH), Jika ada maka akan menambahkan produk baru pada inventory. <br>
            - Jika QTY atau Item produk yang diterima tidak sesuai, bisa dilakukan BPB Parsial.
        </div>
        <p>Daftar Surat Pengantar Barang (SPB) yang akan dibuatkan Berita Penerimaan Barang (BPB).</p>
        <?php
			$no =  explode('-',$po->doc_no);
		?>
        <table class="table table-bordered">
					<thead>
						<tr>
							<th class="text-center">No</th>
                            <th rowspan="2" style="width:50px"><input class="magic-checkbox" name="checkedAll" id="checkedAll" type="checkbox"><label for="checkedAll"></label>  </th>
							<th style="width:300px">Nama Barang</th>
							<th style="width:300px" >Catatan</th>
							<th class="text-center" style="min-width:100px">QTY </th>
                            <th class="text-center">SATUAN</th>
							<th style="min-width:100px" class="text-center">QTY <br> Diterima</th>
							<th class="text-center">Keterangan</th>
						</tr>
					</thead>
					<tbody>

							@php
								$no = 1;
							@endphp
							@foreach ($po_items as $data)
								<tr>
                                    <input name="po_item_id[]" type="hidden" value="{{ $data->id }}">
                                    <input name="pr_item_id[]" type="hidden" value="{{ $data->pr_item_id }}">
                                    <input name="price[]" type="hidden" value="{{ $data->price }}">
                                    <input name="price_after_discount[]" type="hidden" value="{{ ($data->price_after_discount!='0') ? $data->price_after_discount : $data->price }}">
                                    <input name="discount[]" type="hidden" value="{{ $data->price_discount }}">
                                    <input name="conversion[]" type="hidden" value="{{ $data->productConversion }}">
                                    <input name="measure_id[]" type="hidden" value="{{ $data->productMeasure }}">

							        <td class="text-center">{{ $no }}</td>
                                    <td class="text-center"><input type="checkbox" name="iscreateBPB[]" class="checkSingle form-control magic-checkbox"  value="{{ $data->id }}" id="checkbox_{{ $data->id }}"><label for="checkbox_{{ $data->id }}"></label></td>
									<td>[{{ $data->productCode }}] - {{ $data->product }} <br>
                                        <small>
                                            {!! $data->productPartNumber != NULL ? 'PN/Spec: '.$data->productPartNumber : 'PN/Spec: -' !!} <br>
                                            {{ $data->productBrand != NULL ? 'Brand: '.$data->productBrand : 'Brand: -' }}
                                            @if ($data->request_type_item == 1)
                                                <br>
                                                <span class="text-danger">
                                                    Direct To : <br>
                                                        {{getCompanyByLocationId($data->return_location)->alias.' - '.getLocationByID($data->return_location)->name}}
                                                </span>
                                            @endif
                                            <input type="hidden" name="request_type_item[]" value="{{$data->request_type_item}}">
                                            <input type="hidden" name="return_location[]" value="{{$data->return_location}}">
                                            <input type="hidden" name="location_id[]" value="{{$data->location_dpm}}">
                                            <input type="hidden" name="file_dpm[]" value="{{$data->file_dpm}}">
                                        </small>
						                <input name="product_id[]" type="hidden" value="{{ $data->productID }}" >
                                    </td>
									<td>{!! $data->specification !!}</td>
                                    <td class="text-center">
                                        @if ($data->lpb_status == 2)
                                            {{$data->qty_parsial}}
                                            <input name="qty_po[]" type="hidden" value="{{$data->qty_parsial}}"  id='qty_po_{{$data->id}}'>
                                        @else
                                            {{$data->qty}}
                                            <input name="qty_po[]" type="hidden" value="{{$data->qty}}"  id='qty_po_{{$data->id}}'>
                                        @endif
                                    </td>
                                    <td>{{ $data->measure }}</td>
                                    <td>
                                        @if ($data->lpb_status == 2)
                                            <input type='number' name="qty_bpb[]" class="form-control text-center" id='qty_bpb_{{$data->id}}' required  oninput="this.value = Math.abs(this.value)"  value="{{$data->qty_parsial}}">
                                        @else
                                            <input type='number' name="qty_bpb[]" class="form-control text-center" id='qty_bpb_{{$data->id}}' required  oninput="this.value = Math.abs(this.value)"  value="{{$data->qty}}">
                                        @endif
                                    </td>
                                    <td><textarea name="description[]" class="form-control"></textarea></td>
								</tr>
								@php
									$no++;
								@endphp

							@endforeach

					</tbody>

				</table>
    </div>
    <div class="mt-4">
        <a href="{{ route('logistic.bpb_franco.index') }}" id="btn-cancel" class="btn btn-light text-uppercase fsz-sm fw-600 mr-1">{{ trans('Cancel') }}</a>
        {{-- <button class="btn btn-info text-uppercase fsz-sm fw-600" type="submit" name="save" value="submit" id="btn-draft"><i class="fa fa-save mR-10"></i>Save as Draft</button> --}}
        <input type="hidden" value="1" name="status">
        <button class="btn btn-danger text-uppercase fsz-sm fw-600 float-right" type="submit" name="publish" id="btn-submit" value="submit"><i class="fa fa-send mR-10"></i>Publish BPB</button>
    </div>
    {!! Form::close() !!}

@stop

@section('js')
<script  type='text/javascript'>
	$(document).ready(function() {

        @foreach ($po_items as $item)
         $('#qty_bpb_{{ $item->id }}').on('keyup', function(e) {
                var qty_po  = $('#qty_po_{{ $item->id }}').val();
                var qty_bpb = $('#qty_bpb_{{ $item->id }}').val();
                if(parseInt(qty_bpb) > parseInt(qty_po)){
                    e.preventDefault();
                    Swal.fire(
                    'Peringatan!',
                        'QTY BPB tidak boleh melebihi QTY SPB',
                        'warning'
                    );
                    $('#qty_bpb_{{ $item->id }}').val(0);
                }
            });
        @endforeach

        $("#formBPB").validate({
            rules: {
                    "iscreateBPB[]": {
                        required: true,
                        minlength: 1
                    }
            },
            messages: {
                    "iscreateBPB[]": "Minimal Checklist 1 Item"
            }
        });

        $(document).on("click", "#btn-submit", function(e) {
            $('input[name="status"]').val('1');
            var _this = $(this);
            var form = _this.parents('form');

            form.validate({
                onfocusout: false,
                invalidHandler: function(form, validator) {
                    var errors = validator.numberOfInvalids();
                    if (errors) {
                        validator.errorList[0].element.focus();
                    }
                }
            });

            var checkbox = document.querySelector('input[name="iscreateBPB[]"]:checked');
            if(!checkbox) {
                Swal.fire(
                    'Informasi',
                    'Minimal Checklist 1 Item untuk pembuatan BPB',
                    'warning'
                );
                return false;
            }

            var arr_id     = [];
            var validform  = '';

            $('input[name="iscreateBPB[]"]:checked').each(function() {
                var checkbox = $(this).val();
                var qty      = $("#qty_bpb_"+checkbox).val();

                if (qty == 0){
                    arr_id.push(checkbox);
                    var current_arr = arr_id.pop();
                    $('.product_'+current_arr).css("background-color", "#ffd5d5");
                    $('.product_'+current_arr).find('input').focus();
                    validform = false;
                    Swal.fire(
                        'Informasi',
                        'Minimal QTY BPB harus diisi 1',
                        'warning'
                    );
                    return false;
                }else{
                    validform = true;
                }
            });

            e.preventDefault();
            if (form.valid() && validform === true) {
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
                        $('#btn-cancel').attr('disabled', true);
                        $('#btn-draft').attr('disabled', true);
                        $('#btn-submit').attr('disabled', true);
                        $('#btn-submit').html('<span class="spinner"><i class="fa fa-spinner fa-spin"></i></span> Publishing BPB');
                        Swal.fire({
                            title: 'Publishing BPB',
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
            }
        });

        $(document).on("click", "#btn-draft", function(e) {
            $('input[name="status"]').val('1');

            var _this = $(this);
            var form = _this.parents('form');

            form.validate({
                onfocusout: false,
                invalidHandler: function(form, validator) {
                    var errors = validator.numberOfInvalids();
                    if (errors) {
                        validator.errorList[0].element.focus();
                    }
                }
            });

            var checkbox= document.querySelector('input[name="iscreateBPB[]"]:checked');
            if(!checkbox) {
                Swal.fire(
                    'Informasi',
                    'Minimal Checklist 1 Item untuk pembuatan BPB',
                    'warning'
                );
                return false;
            }

            var arr_id     = [];
            var validform  = '';

            $('input[name="iscreateBPB[]"]:checked').each(function() {
                var checkbox = $(this).val();
                var qty      = $("#qty_bpb_"+checkbox).val();

                if (qty == 0){
                    arr_id.push(checkbox);
                    var current_arr = arr_id.pop();
                    $('.product_'+current_arr).css("background-color", "#ffd5d5");
                    $('.product_'+current_arr).find('input').focus();
                    validform = false;
                    Swal.fire(
                        'Informasi',
                        'Minimal QTY BPB harus diisi 1',
                        'warning'
                    );
                    return false;
                }else{
                    validform = true;
                }
            });

            e.preventDefault();
            if (form.valid()  && validform === true) {
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
                        $('#btn-cancel').attr('disabled', true);
                        $('#btn-draft').attr('disabled', true);
                        $('#btn-submit').attr('disabled', true);
                        $('#btn-draft').html('<span class="spinner"><i class="fa fa-spinner fa-spin"></i></span> Saving BPB as Draft');
                        Swal.fire({
                            title: 'Saving BPB as Draft',
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
            }

        });

        $("#checkedAll").change(function(){
            if(this.checked){
            $(".checkSingle").each(function(){
                this.checked=true;
            })
            }else{
            $(".checkSingle").each(function(){
                this.checked=false;
            })
            }
        });

        $(".checkSingle").click(function () {
            if ($(this).is(":checked")){
            var isAllChecked = 0;
            $(".checkSingle").each(function(){
                if(!this.checked)
                isAllChecked = 1;
            })
            if(isAllChecked == 0){ $("#checkedAll").prop("checked", true); }
            }else {
            $("#checkedAll").prop("checked", false);
            }
        });


    });
    </script>
@stop
