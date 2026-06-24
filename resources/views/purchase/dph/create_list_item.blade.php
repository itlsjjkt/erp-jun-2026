@extends('layouts.app')

@section('page-header')
    Daftar Perbandingan Harga
@endsection

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('purchasing.pr') }}">Purchase Requisition</a></li>
        <li class="breadcrumb-item active" aria-current="page">Pembuatan DPH</li>
    </ol>
@endsection

@section('content')

    {!! Form::open(['method' => 'GET', 'route' => ['purchasing.dph.create'], 'id' => 'form-pr']) !!}
    <div class="bgc-white p-30 bd">
        <input type="hidden" name="pr_id" value="{{$pr->id}}">
        <p>Step 1 dari 2</p>
        <hr>
        <div class="alert alert-info">
            - Checklist Daftar Barang yang akan diterbitkan DPH (Daftar Perbandingan Harga) <br>
            - Jika Satuan Produk tidak sesuai silahkan konfirmasi ke bagian admin Logistik karena berhubungan dengan Inventory Control. <br>
        </div>

        <div class="row mt-4">
            <div class="col-lg-12">
                <h6 class='mT-10' style="font-weight:bold;">DAFTAR ITEM {{ $pr->doc_no }}</h6>
            </div>
        </div>

        <div class="row mt-4">
            <table class="table table-bordered">
                <thead>
                    <tr class="bg-grey-custome">
                        <th style="width:50px">
                            <input class="magic-checkbox" name="checkedAll" id="checkedAll" type="checkbox"> <label for="checkedAll"></label>
                        </th>
                        <th>PRODUK</th>
                        <th>CATATAN</th>
                        <th class="text-center">QTY PR</th>
                        <th class="text-center">QTY DPH</th>
                        <th class="text-center">QTY PO</th>
                        <th class="text-center">SATUAN</th>
                    </tr>
                </thead>
                <tbody class="item_form">
                    @if (count($pr_items) > 0)
                        @php
                            $no = 1
                        @endphp
                        @foreach ($pr_items as $item)
                            <tr>
                                <td>
                                    <input type="checkbox" name="iscreateDPH[]" class="checkSingle magic-checkbox" value="{{ $item->id }}" id="checkbox_{{ $item->id }}"><label for="checkbox_{{ $item->id }}"></label>
                                </td>
                                <td>
                                    {{ $item->product ? $item->product : '-' }} <br>
                                    <small>
                                        PN :{!! $item->productPartNumber != NULL ? $item->productPartNumber : '-' !!} <br>
                                        Brand : {{ $item->productBrand != NULL ? $item->productBrand : '-' }}
                                    </small>
                                </td>
                                <td>
                                    {!! $item->notes !!}
                                </td>
                                <td class="text-center">
                                    {{$item->qty}} <br>
                                    <small>
                                        <strong>
                                            sisa : {{$item->qty-getQtyItemDphByPrItemId($item->id)}}
                                        </strong>
                                    </small>
                                </td>
                                <td class="text-center">
                                    {{getQtyItemDphByPrItemId($item->id)}}
                                </td>
                                <td class="text-center">
                                    {{getQtyItemPoByPrItemId($item->id)}}
                                </td>
                                <td class="text-center">{{ $item->measure }}</td>
                            </tr>
                            @php
                                $no++
                            @endphp
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>

        <br>
        <div class="row row-mt-4">
            <label class="col-1 mt-2" for="count_form" style="font-weight:bold;">Jumlah Supplier<span class="text-danger">*</span></label>
            <input class="col-1 form form-control" id="count_form" type="number" value="" min="1" name="count_form">
        </div>

    </div>
    <div class="mt-4">
        <a href="{{ route('purchasing.dph.index') }}" class="btn btn-light text-uppercase fsz-sm fw-600">{{ trans('Cancel') }}</a>
        <input type="hidden" value="{{$pr->location}}" name="location_id">
        <input class="btn btn-danger text-uppercase fsz-sm fw-600" type="submit" name="publish" id="btn-submit-dph" value="Lanjut">
    </div>
    {!! Form::close() !!}
@stop

@section('js')
<script type='text/javascript'>

    $(document).ready(function() {
        $("#checkedAll").change(function(){
            $(".checkSingle").prop("checked", this.checked);
        });

        $(".checkSingle").change(function () {
            var allChecked = $(".checkSingle").length === $(".checkSingle:checked").length;
            $("#checkedAll").prop("checked", allChecked);
        });

        $(document).on('click', "#btn-submit-dph", function(e) {
            e.preventDefault();
            var form = $("#form-pr");

            form.validate({
                rules: {
                    count_form: "required",
                },
                onfocusout: function(element) {
                    if (!this.checkable(element) && (element.name in this.submitted || !this.optional(element))) {
                        this.element(element);
                    }
                },
                invalidHandler: function(form, validator) {
                    var errors = validator.numberOfInvalids();
                    if (errors) {
                        validator.errorList[0].element.focus();
                    }
                }
            });

            $(".checkSingle").each(function() {
                if (!this.checked) {
                    form.find('input[name="pr_item_id[]"][value="' + $(this).val() + '"]').remove();
                }
            });

            if (!form.find('input[name="iscreateDPH[]"]:checked').length) {
                Swal.fire(
                    'Informasi',
                    'Minimal Checklist 1 Item untuk pembuatan DPH',
                    'warning'
                );
                return false;
            }

            Swal.fire({
                title: 'Konfirmasi',
                text: 'Apakah anda yakin melanjutkan ini?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#aaa',
                confirmButtonText: 'Ya, lanjut',
                cancelButtonText: 'Batal',
            }).then(result => {
                if (result.value) {
                    form.submit();
                }
            });
        });
    });
</script>
@stop
