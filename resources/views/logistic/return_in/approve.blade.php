@extends('layouts.app')

@section('page-header')
    Return In
@stop

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('logistic.inventory.index') }}">Inventory</a></li>
        <li class="breadcrumb-item"><a href="{{ route('logistic.return_in.index') }}">Return In</a></li>
        <li class="breadcrumb-item active" aria-current="page">Approve</li>
    </ol>
@endsection


@section('content')
<div class="row mB-40">

	<div class="col-sm-12">
   
        {!! Form::open(['method' => 'POST', 'route' => ['logistic.return_in.approve.store'], 'id'=>'form-dpm', 'files' => true]) !!}

        <input type="hidden" name="inventory_return_in_id" value="{{ $return_in->id }}">

        <div class="bgc-white p-30 bd">
            <h6><a class="float-left" href="{{ route('logistic.return_in.index') }}"><i class="ti-arrow-left mR-10"></i></a> Kembali</h6>
            <hr class="mB-30">
            <h6 class="text-center font-weight-bold mB-40" style="text-decoration:underline">{{ $return_in->doc_no }}</h6>


            <div class="form-group row">
                <label  class="col-sm-2">Nomor Return Out<span class="text-danger">*</span></label>
                <div class="col-sm-3">
                    : {{ $return_in->doc_rot }}
                </div>
            </div>


            <div class="row">
                <div class="col-sm-6">
                    <div class="row">
                        <label  class="col-sm-4">Penerima <span class="text-danger">*</span></label>
                        <div class="col-sm-6">
                            {!! Form::text('received', old('received'), ['class' => 'form-control', 'required' => '']) !!}
                            <p class="help-block"></p>
                            @if($errors->has('received'))
                                <p class="help-block">
                                    {{ $errors->first('received') }}
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="row">
                        <label  class="col-sm-4">Tgl Penerima <span class="text-danger">*</span></label>
                        <div class="col-sm-6">
                            {!! Form::text('received_date', old('received_date'), ['class' => 'form-control datepicker ', 'required' => '']) !!}
                            <p class="help-block"></p>
                            @if($errors->has('received_date'))
                                <p class="help-block">
                                    {{ $errors->first('received_date') }}
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <h6 class=" mt-5">Daftar Item </h6>
            <hr>

            <div class="alert alert-info mb-4">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h6 class="alert-heading">Informasi</h6>
                - Stok pada Item Produk yang dipilih akan ditambahkan otomatis ke Inventory dan tercatat di Mutasi Barang<br>
            </div>

            <table class="table table-bordered mt-2">
                <thead>
                    <th rowspan="2" style="width:50px"><input class="magic-checkbox" name="checkedAll" id="checkedAll" type="checkbox"><label for="checkedAll"></label>  </th>
                    <th class="text-uppercase" style="width:80px">No. Rak</th>
                    <th class="text-uppercase" style="width:400px !important">Item</th>
                    <th class="text-uppercase text-center" style="width:150px" colspan="2">QTY</th>
                    <th class="text-uppercase" style="width:250px !important">Catatan</th>
                </thead>
                <tbody class="item_form" id="itemDPM">
                    @foreach($return_in_items as $item) 
                        <tr class="product_1">
                            <input type="hidden" value="{{ $item->id }}" name="id[]">
                            <input type="hidden" value="{{ $item->inv_id }}" name="inv_id[]">
                            <input type="hidden" value="{{ $item->stock_onhand }}" name="qty_stock[]">
                            <input type="hidden" value="{{ $item->stock_max }}" name="stock_max[]">
                            <input type="hidden" value="{{ $item->stock_min }}" name="stock_min[]">
                            <input type="hidden" value="{{ $item->stock_in }}" name="qty_in[]">
                            <td class="text-center"><input type="checkbox" name="iscreateRIN[]" class="checkSingle form-control magic-checkbox"  value="{{ $item->id }}" id="checkbox_{{ $item->id }}"><label for="checkbox_{{ $item->id }}"></label></td>
                            <td>{{ $item->code_rack }}</td>
                            <td>
                                {{ $item->productCode }} -  {{ $item->productName }} <br>
                                <small>PN/SPEC: {{ $item->productPartNumber }}  </small></td>
                            <td class="text-right"style="border-right:0 !important">{{ $item->qty }}</td>
                            <input name="qty[]" type="hidden" value="{{$item->qty }}" >
                            <td class="text-left" style="border-left:0 !important">{{ $item->unit }}</td>
                            <td>{{ $item->notes }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
		</div> 

        <div class="mt-4">
            <a href="{{ route('logistic.return_in.index') }}" class="btn btn-light mr-1 text-uppercase fsz-sm fw-600">{{ trans('Cancel') }}</a>
            <input class="btn btn-danger text-uppercase fsz-sm fw-600" type="submit" name="publish" id="btn-submit" value="Approve">
        </div>
    {!! Form::close() !!} 
	</div>
</div>
	
@stop

@section('js')

<script  type='text/javascript'>

	$(document).ready(function() {
       
        $('#form-dpm').validate({
            rules: {
                location_id: "required",
                department_id: "required",
            },
            onfocusout: false,
            invalidHandler: function(form, validator) {
                var errors = validator.numberOfInvalids();
                if (errors) {                    
                    validator.errorList[0].element.focus();
                }
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

            var checkbox = document.querySelector('input[name="iscreateRIN[]"]:checked');
            if(!checkbox) {
                Swal.fire(
                    'Informasi',
                    'Minimal Checklist 1 Item untuk pembuatan RIN',
                    'warning'
                );
                return false;
            }

            e.preventDefault();
            if (form.valid()) {
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