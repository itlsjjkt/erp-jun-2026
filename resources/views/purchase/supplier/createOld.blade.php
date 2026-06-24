@extends('layouts.app')

@section('page-header')
    Supplier 
@endsection

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('purchasing.suppliers.index') }}">Supplier</a></li>
        <li class="breadcrumb-item active" aria-current="page">Create</li>
    </ol>
@endsection

@section('content')

	<div class="row mB-40">
   
        <div class="col-sm-12">
            <div class="bgc-white p-20 bd">
                
                <h6><a class="float-left" href="{{ route('purchasing.suppliers.index') }}"><i class="ti-arrow-left mR-10"></i></a> Supplier</h6>
                <hr class="mB-30">

                {!! Form::open(['method' => 'POST', 'route' => ['purchasing.suppliers.store']]) !!}

                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label text-right">Nama Perusahaan <span class="text-danger">*</span></label>
                        <div class="col-sm-8">
                            {!! Form::text('name', old('name'), ['class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}
                            <p class="help-block"></p>
                            @if($errors->has('name'))
                                <p class="help-block">
                                    {{ $errors->first('name') }}
                                </p>
                            @endif
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label text-right"></label>
                        <div class="col-sm-4">
                            <div class="form-group">
                            <label>Category<span class="text-danger">*</span></label>
                                {!! Form::select('master_items[]', $master_items, old('master_items'), ['class' => 'form-control select2', 'multiple' => 'multiple','required']) !!}
                            </div>
                            <label>Payment Term</label>
                            {!! Form::select('payment_term', $payment_term, old('payment_term'), ['class' => 'form-control select2']) !!}
                            <p class="help-block"></p>
                            @if($errors->has('payment_term'))
                            <p class="help-block">
                                {{ $errors->first('payment_term') }}
                            </p>
                            @endif
                        </div>
                        <div class="col-sm-4">
                            <label>Alamat<span class="text-danger">*</span></small></label>
                            {!! Form::textarea('address', old('address'), ['class' => 'form-control', 'rows' => 2, 'placeholder' => '', 'required', 'style' => 'min-height: 118px;']) !!}
                            <p class="help-block"></p>
                            @if($errors->has('address'))
                                <p class="help-block">
                                    {{ $errors->first('address') }}
                                </p>
                            @endif
                        </div>
					</div>

                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label text-right"></label>
                        <div class="col-sm-4">
                            <label>Nomor Pokok Wajib Pajak ( NPWP )</label><br>
                            {!! Form::text('npwp', old('npwp'), ['class' => 'form-control']) !!}
                        </div>
                        <div class="col-sm-4">
                            <label>Nomor Induk Berusaha ( NIB )</label><br>
                            {!! Form::text('nib', old('nib'), ['class' => 'form-control']) !!}
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label text-right"></label>
                        <div class="col-sm-4">
                            <label>Nomor Pengusaha Wajib Pajak ( PKP )</label><br>
                            {!! Form::text('pkp', old('pkp'), ['class' => 'form-control']) !!}
                        </div>
                        <div class="col-sm-4">
                            <label>Nomor Surat Agent</label><br>
                            {!! Form::text('surat_agent', old('surat_agent'), ['class' => 'form-control']) !!}
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label text-right"></label>
                        <div class="col-sm-4">
                            <label>PPN <br> <small>Jika Diaktifkan maka PPN otomatis terpilih  pada pembuatan PO</small></label> <br>
                            <input type="checkbox" name="is_ppn" class="switch switch-info" id="is_ppn" value="1">
                        </div>
                        <div class="col-sm-4">
                            <label>Status <br> <small>Jika Nonaktif maka supplier tidak dapat dipilih pada pembuatan PO</small></label>
                            <input type="checkbox" name="status" class="switch switch-info" id="status" value="1">
                            <p class="help-block"></p>
                            @if($errors->has('status'))
                                <p class="help-block">
                                    {{ $errors->first('status') }}
                                </p>
                            @endif
                        </div>
                    </div>
                

                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label text-right">PIC <br> <small>Jika PIC lebih dari 1 gunakan tombol + untuk menambahkan tipe</small></label>
                        <div class="col-sm-8">
                            <table class="table table-bordered dynatable">
                                <tr>
                                    <th>Title </th>
                                    <th>Nama </th>
									<th>Telepon<span class="text-danger">*</span></th>
                                    <th>Mobile Phone</th>
                                    <th>Email</th>
                                    <th><a href="#" class="btn btn-primary btn-sm pull-right add" data-toggle="tooltip" data-placement="top" data-original-title="Tambah PIC"><i class="ti-plus"></i></a></th>
                                </tr>
                                <tbody class="advancedWrapper"></tbody>
                            </table>
							<div class="text-danger ms-2" id="noticeAddd"></div>
                        </div>
                    </div>
                    <hr>

                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label text-right"></label>
                        <div class="col-sm-8">
	                        <a href="{{ route('purchasing.suppliers.index') }}" class="btn btn-light">{{ trans('Cancel') }}</a>
                            {!! Form::submit(trans('Submit'), ['class' => 'btn btn-success']) !!}
                        </div>
                    </div>

                {!! Form::close() !!}       
            </div>  
        </div>
    </div>
        
@endsection

@section('js')
    <script>
        $(document).ready(function() {
            if($('#status').val()=='1'){
                $('#status').attr('checked','checked').iCheck('update');
            }
            $('#status').on('ifChecked', function(){
                $("#status" ).attr('value', '1');
            });
            $('#status').on('ifUnchecked', function(){
                $("#status" ).attr('value', '0');
            });

            if($('#is_ppn').val()=='1'){
                $('#is_ppn').attr('checked','checked').iCheck('update');
            }
            $('#is_ppn').on('ifChecked', function(){
                $("#is_ppn" ).attr('value', '1');
            });
            $('#is_ppn').on('ifUnchecked', function(){
                $("#is_ppn" ).attr('value', '0');
            });


            var wrapper = $(".advancedWrapper");
            var i = 0;
            $(document).on("click", ".add", function(e) {
                e.preventDefault();
                i++;
                $(wrapper).append(
                '<tr>' + 
                    '<td><select class="form-control" name="pic_Title[]">'+
                        '<option value="Bapak">Bapak</option>'+
                        '<option value="Ibu">Ibu</option>'+
                        '<option value="Bapak/Ibu">Bapak/Ibu</option>'+
                        '<option value="Mr">Mr</option>'+
                        '<option value="Mrs">Mrs</option>'+
                        '<option value="Mr/Mrs">Mr/Mrs</option>'+
                    '<select></td>' +
                    '<td><input type="text" class="form-control" name="picName[]"></td>' +
                    '<td><input type="text" class="form-control" name="picMobilePhone[]" required></td>' +
                    '<td><input type="text" class="form-control" name="picTelp[]"></td>' +
                    '<td><input type="text" class="form-control" name="picEmail[]"></td>' +
                    '<td><button class="btn btn-danger remove float-right" ><i class="ti-trash"></i></button></td>' +
                '</tr>');
                $('#noticeAddd').empty();
            });
            $('form').submit(function(e) {
                if ($('.advancedWrapper tr').length === 0) {
                    $('#noticeAddd').text("Anda harus menambahkan setidaknya satu PIC sebelum Submit.");
                    e.preventDefault();
                }
			});

            $(document).on("click", ".remove", function() {
                $(this).parents("tr").remove();
            });



        });
    </script>
@stop