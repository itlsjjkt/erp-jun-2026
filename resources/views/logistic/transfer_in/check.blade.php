@extends('layouts.app')

@section('page-header')
    Warehouse Transfer In
@stop

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('logistic.inventory.index') }}">Inventory</a></li>
        <li class="breadcrumb-item"><a href="{{ route('logistic.transfer_in.index') }}">Warehouse Transfer In</a></li>
        <li class="breadcrumb-item active" aria-current="page">Check</li>
    </ol>
@endsection


@section('content')
<div class="row mB-40">

	<div class="col-sm-12">
        <div class="bgc-white p-30 bd">
            <h6><a class="float-left" href="{{ route('logistic.transfer_in.index') }}"><i class="ti-arrow-left mR-10"></i></a> Kembali</h6>
            <hr class="mB-30">

            <h6 class="text-center font-weight-bold mB-40" style="text-decoration:underline">{{ $transfer->doc_no }}</h6>

            <div class="row">
                <div class="col-sm-6">
                    <div class="row">
                        <label class="col-sm-3">No Transfer Out</label>
                        <div class="col-sm-8">
                            : {{ $transfer->out_doc_no }}
                        </div>
                    </div>
                    <div class="row">
                        <label class="col-sm-3">Tgl Transfer Out</label>
                        <div class="col-sm-8">
                            : {{ date('d F Y',strtotime( $transfer->created_at_wto)) }}
                        </div>
                    </div>
                    <div class="row">
                        <label class="col-sm-3">Lokasi Asal</label>
                        <div class="col-sm-8">
                            : {{ $transfer->lokasiasal.' - '.$transfer->comAsalCode }}
                        </div>
                    </div>
                    <div class="row">
                        <label class="col-sm-3">Operator</label>
                        <div class="col-sm-8">
                            : {{ $transfer->operator_wto }}
                        </div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="row">
                        <label class="col-sm-3">Penerima</label>
                        <div class="col-sm-8">
                            : {{ $transfer->received }}
                        </div>
                    </div>
                    <div class="row">
                        <label class="col-sm-3">Lokasi Tujuan</label>
                        <div class="col-sm-8">
                            : {{ $transfer->location.' - '.$transfer->companyCode }}
                        </div>
                    </div>
                    <div class="row">
                        <label class="col-sm-3">Tanggal diterima</label>
                        <div class="col-sm-8">
                            : {{ date('d F Y',strtotime( $transfer->received_date )) }}
                        </div>
                    </div>
                    <div class="row">
                        <label class="col-sm-3">Status WTI</label>
                        <div class="col-sm-8">
                            : {!! getStatusTransferIn($transfer->status,$transfer->type,$transfer->type_status) !!}
                        </div>
                    </div>
                </div>
            </div>

            <h6 class=" mt-5">Daftar Item </h6>
            <hr>

            {!! Form::model($transfer, [
                'action' => ['Logistic\InventoryTransferInController@check_store', $transfer->id],
                'method' => 'put',
                'class' => 'form-horizontal mt-3',
                'id'    => 'formPR',
                'files' => true
            ]) !!}

                <table class="table table-bordered mt-2">
                    <thead style="background-color: rgb(216, 216, 216);">
                        <th class="text-uppercase">Produk</th>
                        <th class="text-uppercase text-center">QTY</th>
                        <th class="text-uppercase text-center">Satuan</th>
                        <th class="text-uppercase" style="width:250px !important">Catatan</th>
                        <th class="text-uppercase">Buatkan DPM Penggantian</th>
                        <th class="text-uppercase">Catatan Pengecekan</th>
                    </thead>
                    <tbody class="item_form" id="itemDPM">
                        @foreach($transfer_items as $item)
                            <tr class="product_1">
                                <td>
                                    [{{ $item->productcode }}] -  {{ $item->productname }} <br>
                                    <small>
                                        PN/SPEC: {{ $item->productpartnumber ?? '-'}}
                                    </small>
                                </td>
                                <td class="text-center" style="border-right:0 !important">
                                    {{ $item->qty }}
                                </td>
                                <td class="text-center">
                                    {{ $item->productunit }}
                                </td>
                                <td>{{ $item->notes }}</td>
                                <td style="width:250px;" class="text-center">
                                    <input type="hidden" name="id_inv_in_item[]" value="{{$item->id}}">
                                    {!! Form::select(
                                        'type_replacement[]',
                                        $type_replacement,
                                        $item->type_replacement,
                                        ['class' => 'form-control select2 type-replacement', 'required' => '','onchange' => 'updateRowColor(this)']
                                    ) !!}
                                </td>
                                <td>
                                    <input class="form-control" type="text" name="type_replacement_notes[]" value="{{$item->type_replacement_notes}}" placeholder="Catatan Pengecekan">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="text-right mt-3">
                    <button type="submit" class="btn btn-primary btn-submitt">
                        <i class="fa fa-save"></i> Submit
                    </button>
                </div>

            {!! Form::close() !!}

		</div>
	</div>
</div>

@stop

@section('js')
    <script  type='text/javascript'>
        $(document).on('click', ".btn-submitt", function(e) {
            var _this = $(this);
            var form = _this.parents('form');
            e.preventDefault();
            if (form.valid() ) {
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
                       _this.closest("form").submit();
                    }
                });
            }
        });
        function updateRowColor(select) {
            const tr = select.closest('tr'); // ambil row parent
            if (select.value == '0') {
                tr.style.backgroundColor = '#f8d7da'; // merah
            } else if (select.value == '1') {
                tr.style.backgroundColor = '#d4edda'; // hijau
            } else {
                tr.style.backgroundColor = '';
            }
        }

        // Set warna awal saat halaman load
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.type-replacement').forEach(function(select) {
                updateRowColor(select);
            });
        });
    </script>
@stop
