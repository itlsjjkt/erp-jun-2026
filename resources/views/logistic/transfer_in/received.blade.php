@extends('layouts.app')

@section('page-header')
    Warehouse Transfer In
@stop

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('logistic.inventory.index') }}">Inventory</a></li>
        <li class="breadcrumb-item"><a href="{{ route('logistic.transfer_in.index') }}">Warehouse Transfer In</a></li>
        <li class="breadcrumb-item active" aria-current="page">Penerimaan</li>
    </ol>
@endsection


@section('content')
    <div class="row mB-40">
        <div class="col-sm-12">
            {!! Form::model($transfer, [
                    'action' => ['Logistic\InventoryTransferInController@received'],
                    'method' => 'post',
                    'class' => 'form-horizontal mt-3',
                    'id'    => 'form',
                ])
            !!}
                <input type="hidden" name="transfer_out_id" value="{{ $transfer->id }}">
                <input type="hidden" name="transfer_out_doc_no" value="{{ $transfer->doc_no }}">
                <input type="hidden" name="location_destination" value="{{ $transfer->location_destination }}">
                <input type="hidden" name="location_id" value="{{ $transfer->location_id }}">
                <input type="hidden" name="companyCode" value="{{ $transfer->companyCode }}">
                <input type="hidden" name="locationCode" value="{{ $transfer->location_destination_code }}">
                <input type="hidden" name="wtotype" value="{{ $transfer->type }}">

                <div class="bgc-white p-30 bd">
                    <h6>
                        <a class="float-left" href="{{ route('logistic.transfer_in.index') }}"><i class="ti-arrow-left mR-10"></i></a> Kembali
                    </h6>
                    <hr class="mB-30">

                    <h6 class="text-center font-weight-bold mB-40" style="text-decoration:underline">{{ $transfer->doc_no }}</h6>

                    <div class="row">
                        <div class="col-lg-6">
                            <div class="form-group row">
                                <label class="col-sm-3">Penerima <span class="text-danger">*</span></label>
                                <div class="col-sm-6">
                                    {!! Form::text('received', old('received'), ['class' => 'form-control', 'required' => '']) !!}
                                    @if($errors->has('received'))
                                        <p class="help-block text-danger">{{ $errors->first('received') }}</p>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3">Tanggal diterima <span class="text-danger">*</span></label>
                                <div class="col-sm-6">
                                    {!! Form::text('received_date', old('received_date'), ['class' => 'form-control datepicker', 'required' => '']) !!}
                                    @if($errors->has('received_date'))
                                        <p class="help-block text-danger">{{ $errors->first('received_date') }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="row">
                                <label class="col-sm-3">Operator</label>
                                <div class="col-sm-8">
                                    : {{ $transfer->operator }}
                                </div>
                            </div>
                            <div class="row">
                                <label class="col-sm-3">Lokasi Warehouse Tujuan</label>
                                <div class="col-sm-8">
                                    : {{ $transfer->location_destination_name }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <h6 class="mt-5">Daftar Item</h6>
                    <hr>
                    <table class="table table-bordered mt-2">
                        <thead>
                            <tr>
                                <th rowspan="2" style="width:50px; display:none;"></th>
                                <th rowspan="2" class="text-uppercase text-center">Item</th>
                                <th colspan="3" class="text-uppercase text-center">Pengiriman</th>
                                <th colspan="3" class="text-uppercase text-center">Penerimaan</th>
                            </tr>
                            <tr>
                                <th class="text-uppercase text-center">QTY</th>
                                <th class="text-uppercase text-center">Satuan</th>
                                <th class="text-uppercase text-center" style="width:250px !important">Catatan</th>
                                <th class="text-uppercase text-center">QTY</th>
                                <th class="text-uppercase text-center">Satuan</th>
                                <th class="text-uppercase text-center" style="width:250px !important">Catatan</th>
                            </tr>
                        </thead>
                        <tbody class="item_form" id="itemDPM">
                            @foreach($transfer_items as $item)
                                <input type="hidden" value="{{ $item->id }}" name="transfer_item_id[]">
                                <input type="hidden" value="{{ $item->inventory_id }}" name="inv_id[]">
                                <input type="hidden" value="{{ $item->product_id }}" name="product_id[]">
                                <input type="hidden" value="{{ $item->price }}" name="product_price[]">
                                <input type="hidden" value="{{ $item->productsatuanidinv }}" name="productsatuanidinv[]">
                                <input type="hidden" value="{{ $item->price_after_discount }}" name="product_price_after_discount[]">

                                <tr class="product_1">
                                    <td class="text-center" style="display:none;">
                                        <input type="checkbox" name="iscreate[]" value="{{ $item->id }}" checked hidden>
                                    </td>
                                    <td>
                                        {{ $item->productcode }} - {{ $item->productname }} <br>
                                        <small>PN/SPEC: {{ $item->productpartnumber }}</small>
                                    </td>
                                    <td class="text-center">
                                        @if ($item->status == 2)
                                            {{ $item->qty_parsial }}
                                            <input type="hidden" value="{{ $item->qty_parsial }}" name="qty_transfer[]" id="qty_transfer_{{$item->id}}">
                                        @else
                                            {{ $item->qty }}
                                            <input type="hidden" value="{{ $item->qty }}" name="qty_transfer[]" id="qty_transfer_{{$item->id}}">
                                        @endif
                                    </td>
                                    <td class="text-center">{{ $item->productunit }}</td>
                                    <td>{{ $item->notes }}</td>
                                    <td style="width:150px;">
                                        <input type="number" class="form-control text-center" value="{{$item->status == 2 ? $item->qty_parsial : $item->qty}}" name="qty[]" id="qty_{{$item->id}}" onwheel="return false;" readonly>
                                    </td>
                                    <td class="text-center">{{ $item->productunit }}</td>
                                    <td>{!! Form::textarea('notes['.$loop->index.']', old('notes.'.$loop->index), ['class' => 'form-control', 'rows' => 2, 'placeholder' => '']) !!}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    <a href="{{ route('logistic.transfer_in.index') }}" class="btn btn-light mr-1 text-uppercase fsz-sm fw-600">{{ trans('Cancel') }}</a>
                    <input type="hidden" value="0" name="status">
                    <input class="btn btn-danger text-uppercase fsz-sm fw-600 float-right" type="submit" name="publish" id="btn-submit" value="Publish">
                </div>

            {!! Form::close() !!}
        </div>
    </div>
@stop


@section('js')
<script type='text/javascript'>
$(document).ready(function() {

    // ✅ Auto check semua item (dan hidden)
    $('input[name="iscreate[]"]').prop('checked', true).hide();

    // Validasi jumlah item
    $("#form").validate({
        rules: {
            "iscreate[]": {
                required: true,
                minlength: 1
            }
        },
        messages: {
            "iscreate[]": "Minimal Checklist 1 Item"
        }
    });

    // Validasi QTY tidak melebihi QTY Transfer
    @foreach ($transfer_items as $item)
        $('#qty_{{$item->id}}').on('keyup', function(e) {
            var qty = $('#qty_{{$item->id}}').val();
            var qty_transfer = $('#qty_transfer_{{$item->id}}').val();
            if(parseInt(qty) > parseInt(qty_transfer)){
                e.preventDefault();
                Swal.fire(
                    'Peringatan!',
                    'QTY Transfer tidak boleh melebihi QTY Stock',
                    'warning'
                );
                $('#qty_{{$item->id}}').val('');
            }
        });
    @endforeach

    // Tombol Submit
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

        var validform = true;
        $('input[name="iscreate[]"]:checked').each(function() {
            var checkbox = $(this).val();
            var qty = $("#qty_" + checkbox).val();

            if (qty == 0) {
                $('.product_' + checkbox).css("background-color", "#ffd5d5");
                Swal.fire('Informasi', 'Minimal QTY Transfer harus diisi 1 Item', 'warning');
                validform = false;
                return false;
            }
        });

        e.preventDefault();
        if (form.valid() && validform === true) {
            Swal.fire({
                title: 'Konfirmasi',
                text: 'Apakah anda yakin melanjutkan ini?',
                type: 'question',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, lanjut',
                cancelButtonText: 'Batal',
            }).then(res => {
                if (res.value) {
                    _this.closest("form").submit();
                }
            });
        }
    });

});
</script>
@stop
