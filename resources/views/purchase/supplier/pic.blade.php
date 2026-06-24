@extends('layouts.app')

@section('page-header')
    Supplier PIC
@stop

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('purchasing.suppliers.index') }}">Supplier</a></li>
        <li class="breadcrumb-item active" aria-current="page">PIC</li>
    </ol>
@endsection


@section('content')
<div class="row mB-40">

	<div class="col-sm-12">
        <div class="bgc-white p-30 bd">
            <h6><a class="float-left" href="{{ route('purchasing.suppliers.index') }}"><i class="ti-arrow-left mR-10"></i></a> Supplier</h6>
            <hr class="mB-30">

            
            {!! Form::open(['method' => 'POST', 'route' => ['purchasing.suppliers.picStore'] ]) !!}
            {!! Form::hidden('supplier_id', $supplier->id) !!}

                <div class="form-group row">
                    <label class="col-sm-3 col-form-label text-right">Name </label>
                    <div class="col-sm-8">
                        : {{ $supplier->name }}
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-3 col-form-label text-right">Telp</label>
                    <div class="col-sm-8">
                        : {{ $supplier->telp }}
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-3 col-form-label text-right">Alamat </label>
                    <div class="col-sm-8">
                        : {{ $supplier->address }}
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-3 col-form-label text-right">Daftar PIC</label>
                    <div class="col-sm-9">
                        <?php if (isset($pic))  { ?>
                            <table class="table table-bordered dynatable">
                                <tr>
                                    <th>Nama</th>
                                    <th>Telepon</th>
                                    <th><a class="btn btn-info btn-sm pull-right add"  data-toggle="tooltip" data-placement="top" data-original-title="Tambah Rule"><i class="ti-plus"></i></a></th>
                                </tr>
                                <tbody class="advancedWrapper">
                                @if (count($pic) > 0)
                                    @php
                                        $no = 1
                                    @endphp
                                    @foreach ($pic as $key)
                                        <tr>
                                            <td>
                                                <input type="text" name="name[]" class ='form-control' value="<?php echo $key->name;?>" required>
                                            </td>
                                            <td>
                                                <input type="text" name="telp[]" class ='form-control' value="<?php echo $key->telp;?>" required>  
                                            </td>
                                            <td>
                                                <button class="btn btn-outline icon-lg text-danger remove" ><i class="ti-trash"></i></a>
                                            </td>
                                        </tr>
                                        @php
                                            $no++
                                        @endphp
                                    @endforeach
                                @endif
                                </tbody>
                            </table>
                        <?php }else { ?>
                            <table class="table table-bordered dynatable">
                                <tr>
                                    <th>Nama <span class="text-danger">*</span></th>
                                    <th>Telepon <span class="text-danger">*</span></th>
                                    <th><a class="btn btn-primary btn-sm pull-right add" data-toggle="tooltip" data-placement="top" data-original-title="Tambah Rule"><i class="ti-plus"></i></a></th>
                                </tr>
                                <tbody class="advancedWrapper"></tbody>
                            </table>
                        <?php } ?>
                    </div>
                </div>
                
                <div class="form-group row">
                    <label class="col-sm-3 col-form-label text-right"></label>
                    <div class="col-sm-8">
                        <a href="{{ route('purchasing.suppliers.index') }}" class="btn btn-light">{{ trans('Cancel') }}</a>
                        {!! Form::submit(trans('Submit'), ['class' => 'btn btn-danger']) !!}
                    </div>
                </div>
            
            {!! Form::close() !!}
           
		</div>  
	</div>
</div>
	
@stop

@section('js')
    <script>
        $(document).ready(function() {

            var wrapper = $(".advancedWrapper");
            var i = 0;
            $(document).on("click", ".add", function(e) {
                e.preventDefault();
                i++;
                $(wrapper).append('<tr>' + 
                    '<td><input type="text" class="form-control" name="name[]" required/></td>' + 
                    '<td><input type="text" class="form-control" name="telp[]" required/></td>' + 
                    '<td><button class="btn btn-outline text-danger icon-lg remove" ><i class="ti-trash"></i></button></td>'+
                '</tr>'); 
            });

            $(document).on("click", ".remove", function() {
                $(this).parents("tr").remove();
            });
        });
    </script>


@stop