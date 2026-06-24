@extends('layouts.app')

@section('page-header')
     Monitoring Item PR
@endsection

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item active" aria-current="page"> Monitoring Item PR</li>
    </ol>
@endsection

@section('content')
<div class="bgc-white bd bdrs-3 p-20 mB-20">
    @php
        use Illuminate\Support\Facades\Gate;
    @endphp
    @if(!Gate::allows('pr_monitoring'))
        <div class="mB-5 row">
            <div class="col-lg-12">
                <a href="#" data-toggle="collapse" data-target="#filter"  class="btn btn-outline border-dark float-right"> <i class="ti-search icon-lg"></i> FILTER | EXPORT</a>
            </div>
        </div>
        <hr>
	@endif


    <div class="collapse mB-20" id="filter" aria-expanded="false">
        <form class="form-horizontal" action="" method='GET' id="form">
            {{ csrf_field() }}
            <div class="bd p-20">
                <div class="form-group row">
                    <div class="col-auto">
                        <label>Purchaser</label>
                        {!! Form::select('assigned_id', $purchaser, old('assigned_id'), ['class' => 'form-control select2']) !!}
                    </div>
                    <div class="col-auto">
                        <label>Tipe PR</label>
                        {!! Form::select('type', $type, old('type'), ['class' => 'form-control select2']) !!}
                    </div>
                    <div class="col-auto">
                        <label>Project</label>
                        {!! Form::select('project_id', $project, old('project_id'), ['class' => 'form-control select2']) !!}
                    </div>
                    <div class="col-auto">
                        <label>Lokasi</label>
                        {!! Form::select('location_id', $location, old('location_id'), ['class' => 'form-control select2']) !!}
                    </div>
                    <div class="col-sm-4">
                        <label>Periode</label>
                        <div class="input-group w-100">
                            <input type="text" name="start_date" class="form-control datepicker m-r-n-1" placeholder="mm/dd/yyyy" value="{{ date('m/d/Y') }}">
                            <div class="input-group-prepend">
                                <div class="input-group-text">ke</div>
                            </div>
                            <input type="text" name="end_date"  class="form-control datepicker" placeholder="mm/dd/yyyy" value="{{ date('m/d/Y') }}">
                        </div>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-12">
                        <div class="float-right">
                            <button type="submit" class="btn btn-danger" id="btn-filter">CARI</button>
                            <button type="submit" class="btn btn-success" id="btn-export">EXPORT DATA</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
{{-- Total Data Purchasing--}}
    <div class="col-12"  id="purc" aria-expanded="true" >
    <div class="alert alert-info">Monitoring Item PR digunakan untuk memonitoring PR yang sudah di-assign namun belum dibuatkan PO atau Set Selesai untuk PR dengan tipe IM/Petty Cash</div>
        <div class="row gap-5">
            @php $countData = 0;@endphp
            @foreach($data as $val)
            @php
                $countData += $val->count ;
            @endphp
            <div class='col-md-2'>
                <div class="layers bd bgc-white p-20">
                    <div class="layer w-100">
                        <div class="peers ai-sb fxw-nw">
                            <div class="peer peer-greed">
                                {{ $val->name }}
                            </div>
                        </div>
                        <div class="peers ai-sb fxw-nw">
                            <div class="peer text-info fa-2x font-weight-bold">
                                {{$val->count}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
            @if(Auth::user()->data_access == 1)
            <div class='col-md-2'>
                <div class="layers bd bgc-white p-20">
                    <div class="layer w-100">
                        <div class="peers ai-sb fxw-nw">
                            <div class="peer peer-greed">
                                Total Semua Item
                            </div>
                        </div>
                        <div class="peers ai-sb fxw-nw">
                            <div class="peer text-info font-weight-bold fa-2x">
                                {{$countData}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
		</div>
	</div><br>
    <table id="dataTables" class="table table-striped table-bordered" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th>Nama Barang</th>
                <th>QTY</th>
                <th>STN</th>
                <th>No. PR</th>
                <th>Department/Kapal</th>
                <th>Purchaser</th>
                <th>Tipe</th>
                <th>Tgl Input</th>
                <th>Aksi</th>
            </tr>
        </thead>
    </table>
</div>



@endsection

@section('js')
    <script>
        $(document).ready(function() {
            $('#dataTables').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('purchasing.monitoring_pr.datatables') }}',
                "pageLength": 50,
                columns: [
                    {data: 'product', name: 'master_item_products.name'},
                    {data: 'qty', name: 'qty', searchable: false},
                    {data: 'measure', name: 'measure', searchable: false},
                    {data: 'no_pr', name: 'purchase_requisitions.doc_no'},
                    {data: 'department', name: 'departments.name'},
                    {data: 'purchaser', name: 'users.name'},
                    {data: 'type', name: 'purchase_requisitions.type'},
                    {data: 'created', name: 'purchases.created_at'},
                    {data: 'action', name: 'action', orderable: false, searchable: false},
					{data: 'PN', name: 'master_item_products.part_number', visible: false }
                ],
                "order": [[ 7, "desc" ]]
            });

            $('#btn-filter').click( function() {
                $('form#form').attr('action',"{{ route('purchasing.monitoring_pr.search') }}");
                if($('form#form').valid()){
                    $('form#form').submit();
                }
            });

            $('#btn-export').click( function(e) {
                e.preventDefault();
                $('form#form').attr('action',"{{ route('purchasing.monitoring_pr.export') }}");
                if($('form#form').valid()){
                    $('form#form').submit();
                }
            });

        });
    </script>
@stop