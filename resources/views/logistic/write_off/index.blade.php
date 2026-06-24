@extends('layouts.app')

@section('page-header')
    Write Off
@stop

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('logistic.inventory.index') }}">Inventory</a></li>
        <li class="breadcrumb-item active" aria-current="page">Write Off</li>
    </ol>
@endsection


@section('content')
<div class="row mB-40">

	<div class="col-sm-12">
        <div class="bgc-white p-30 bd">

            <div class="row">
                <div class="col">
                    <a href="#" data-toggle="collapse" data-target="#filter" class="btn btn-outline border-dark float-right">
                       FILTER | EXPORT DATA
                    </a>
                </div>
            </div>

            <hr class="mB-30">

            <div class="alert alert-info">Pembuatan Write Off dilakukan di Menu Inventory</div>

            <div class="collapse mB-20" id="filter" aria-expanded="false">
                <form class="form-horizontal" method='GET' id="form">
                    {{ csrf_field() }}
                    <div class="bgc-white bd bdrs-3 p-20">
                        <h6>Form Filter | Export </h6>
                        <hr>
                        <div class="form-group row">
                            <div class="col-sm-3">
                                <label>Lokasi</label>
                                @if(isAdministrator() || isAdministratorCompany() || isAdmin())
                                    {!! Form::select('location_id', $location, old('location_id'), ['class' => 'form-control select2', 'id'=>'location_id']) !!}
                                @elseif(isAdministratorLocation())
                                    <input type="text" readonly class="form-control" value="{{ getDataByID('locations',Auth::user()->location_id )->name }}">
                                    <input type="hidden" name="location_id" id="location_id" value="{{ Auth::user()->location_id }}">
                                @else
                                    <input type="text" readonly class="form-control" value="{{ getDataByID('locations',Auth::user()->location_id)->name }}">
                                    <input type="hidden" name="location_id" id="location_id" value="{{Auth::user()->location_id}}">
                                @endif
                            </div>
                            <div class="col-sm-3">
                                <label>Periode</label>
                                <div class="input-group w-100">
                                    <input name="start_date" type="text" class="form-control datepicker m-r-n-1" placeholder="mm/dd/yyyy" required value="{{ date('m/d/Y') }}">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text">ke</div>
                                    </div>
                                    <input type="text" name="end_date" class="form-control datepicker" placeholder="mm/dd/yyyy" required value="{{ date('m/d/Y') }}">
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <button type="submit" class="btn btn-info mt-4" id="btn-filter">FILTER</button>
                                <button type="submit" class="btn btn-success mt-4" id="btn-export">EXPORT</button>
                            </div>
                        </div>
                    </div>
                   
                </form>
            </div>   

            <table id="dataTables" class="table table-striped table-bordered" cellspacing="0" width="100%">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Rak</th>
                        <th>Produk</th>
                        <th>Reason</th>
                        <th>Tanggal Input</th>
                        <th>Input Oleh</th>
                    </tr>
                </thead>
            </table>
		</div>  
	</div>
</div>
	
@stop

@section('js')
    <script>
        $(document).ready(function() {
      
            $('#dataTables').DataTable({
                "pageLength": 50,
                processing: true,
                serverSide: true,
                ajax: '{{ route('logistic.write_off.datatables') }}',
                columns: [
                    {data: 'doc_no', name: 'doc_no'},
                    {data: 'code_rack', name: 'inventories.code_rack'},
                    {data: 'productName', name: 'master_item_products.name'},
                    {data: 'reason', name: 'reason', searchable: false},
                    {data: 'created_at', name: 'created_at', searchable: false},
                    {data: 'created', name: 'created', searchable: false},
                ],
                "order": [[ 4, "desc" ]]
            });


            $('#btn-filter').click( function() {
                $('form#form').attr('action',"{{ route('logistic.write_off.search') }}");
                if($('form#form').valid()){
                    $('form#form').submit();
                }
            });

            $('#btn-export').click( function(e) {
                e.preventDefault();
                $('form#form').attr('action',"{{ route('logistic.write_off.export') }}");
                if($('form#form').valid()){
                    $('form#form').submit();
                }
            });


        });
    </script>
@stop