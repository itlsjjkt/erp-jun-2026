@extends('layouts.app')

@section('page-header')
    Return In
@stop

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('logistic.inventory.index') }}">Inventory</a></li>
        <li class="breadcrumb-item"><a href="{{ route('logistic.return_in.index') }}">Return In</a></li>
        <li class="breadcrumb-item active" aria-current="page">Input</li>
    </ol>
@endsection


@section('content')
    {!! Form::open(['method' => 'POST', 'route' => ['logistic.return_in.store'], 'id'=>'form-dpm', 'files' => true]) !!}

        <input type="hidden" name="inventory_return_out_id" value="{{ $return_out->id }}">
        <input type="hidden" name="companyCode" value="{{$return_out->companyCode}}">
        <input type="hidden" name="locationCode" value="{{$return_out->locationCode}}">
        <input type="hidden" name="locationID" value="{{$return_out->locationID}}">

        <div class="bgc-white p-30 bd">
            <h6>Input Return In</h6>
            <hr class='mB-30'>
            <div class="form-group row">
                <label  class="col-sm-3 text-right">Nomor ROT <span class="text-danger">*</span></label>
                <div class="col-sm-3">
                    <input type="text" value="{{ $return_out->doc_no }}" class="form-control" readonly>
                </div>
            </div>
            <div class="form-group row">
                <label  class="col-sm-3 text-right">Operator <span class="text-danger">*</span></label>
                <div class="col-sm-4">
                    {!! Form::text('operator', old('operator'), ['class' => 'form-control', 'required' => '']) !!}
                    <p class="help-block"></p>
                    @if($errors->has('operator'))
                        <p class="help-block">
                            {{ $errors->first('operator') }}
                        </p>
                    @endif
                </div>
            </div>
            

            <h6 >Daftar Item </h6>
            <hr>

            <table class="table table-bordered mt-2">
                <thead>
                    <th rowspan="2" style="width:50px"><input class="magic-checkbox" name="checkedAll" id="checkedAll" type="checkbox"><label for="checkedAll"></label>  </th>
                    <th style="width:80px">NO. RAK</th>
                    <th style="width:400px !important">ITEM</th>
                    <th class="text-center">QTY RETUR</th>
                    <th class="text-center">SATUAN</th>
                    <th style="width:250px !important">CATATAN</th>
                </thead>
                <tbody class="item_form" id="itemDPM">
                    @foreach($return_out_items as $item) 
                        <tr class="product_1">
                            <input type="hidden" value="{{ $item->inv_id }}" name="inv_id[]">
                            <input type="hidden" value="{{ $item->id }}" name="id[]">
                            <td class="text-center"><input type="checkbox" name="iscreateRIN[]" class="checkSingle form-control magic-checkbox"  value="{{ $item->id }}" id="checkbox_{{ $item->id }}"><label for="checkbox_{{ $item->id }}"></label></td>
                            <td>{{ $item->code_rack }}</td>
                            <td>
                                {{ $item->productCode }} - {{ $item->productName }}<br>
                                {!! $item->productPartNumber != NULL ? '<small> PN: '.$item->productPartNumber.'</small>' : '' !!} 
                            </td>
                            <td class="text-center">{{ $item->qty }}</td>
                            <td class="text-center">{{ $item->unit }}</td>
                            <input name="qty[]" type="hidden" value="{{$item->qty }}" >
                            <td>
                                {!! Form::textarea('notes[]', old('notes'), ['class' => 'form-control', 'rows' => 2, 'placeholder' => '']) !!}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

        </div>
        <div class="mt-4">
            <a href="{{ route('logistic.return_in.index') }}" class="btn btn-light mr-1 text-uppercase fsz-sm fw-600">{{ trans('Cancel') }}</a>
            <input class="btn btn-info text-uppercase fsz-sm fw-600" type="submit" name="save" id="btn-draft" value="Save as Draft">
            <input type="hidden" value="0" name="status">
            <input class="btn btn-danger text-uppercase fsz-sm fw-600 float-right" type="submit" name="publish" id="btn-submit" value="Publish">
        </div>
    {!! Form::close() !!}
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
