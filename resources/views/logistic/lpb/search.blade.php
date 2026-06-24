@extends('layouts.app')

@section('page-header')
    Laporan Penerimaan Barang
@endsection

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('logistic.lpb.index') }}">Laporan Penerimaan Barang</a></li>
        <li class="breadcrumb-item active" aria-current="page">Search</li>
    </ol>
@endsection

@section('content')

    <div class="bgc-white bd bdrs-3 p-20 mB-20">
        <div class="row mB-20">
            <div class="col-sm-6 ">
                <a href="{{ route('logistic.lpb.index') }}" class="" >
                    <i class="ti-arrow-left"></i> Kembali
                </a>
            </div>
            <div class="col-sm-6">
                <a href="#" data-toggle="collapse" data-target="#filter" class="btn btn-outline border-dark float-right text-uppercase fsz-sm fw-600">
                    FILTER | EXPORT DATA
                </a>
            </div>
        </div>

        <div class="collapse mB-20" id="filter" aria-expanded="false">
            <form class="form-horizontal" id="form" method='GET'>
                {{ csrf_field() }}

                <div class="bgc-white bd bdrs-3 p-20">
                    <h6>Pencarian Data</h6>
                    <hr>
                    <div class="form-group row">
                        <div class="col-sm-3">
                            <label>Location </label>
                            @if(isAdministrator() || isAdministratorCompany() || isAdmin())
                                {!! Form::select('location_id', $location, $data['location_id'], ['class' => 'form-control select2', 'id'=>'location_id']) !!}
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
                                <input type="text" name="start_date" class="form-control datepicker m-r-n-1" placeholder="mm/dd/yyyy" value="{{ $data['start_date'] }}">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">ke</div>
                                </div>
                                <input type="text" name="end_date"  class="form-control datepicker" placeholder="mm/dd/yyyy" value="{{ $data['end_date'] }}">
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <button type="submit" class="btn btn-info mt-4" id="btn-filter">FILTER</button>
                            @php
                                use Illuminate\Support\Facades\Gate;
                            @endphp
                            @if(!GATE::allows('lpb_monitoring'))
                                <button type="submit" class="btn btn-danger mt-4" id="btn-export">EXPORT</button>
                            @endif
                        </div>
                    </div>
                </div>

            </form>
        </div>

        <hr>


        <p>{!! $search !!}</p>
        <div class="table-responsive mt-5">
            <table id="dataTables" class="table table-striped table-bordered" cellspacing="0" width="100%">
                <thead>
                    <tr>
                        <th>No. LPB</th>
                        <th>No. PO</th>
                        <th>No. PR</th>
                        <th>No. DPM</th>
                        <th>Penerima</th>
                        <th>Dibuat Oleh</th>
                        <th>Tgl Buat</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
            </table>
        <div>
    </div>

@endsection


@section('js')
    <script>
        $(document).ready(function() {

            $('#dataTables').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('logistic.lpb.datatables',$query) }}',
                "pageLength": 50,
                columns: [
                    {data: 'doc_no', name: 'doc_no'},
                    {data: 'po_no', name: 'po.doc_no'},
                    {data: 'pr_no', name: 'purchase_requisitions.doc_no'},
                    {data: 'dpm_no', name: 'purchase_requisitions.dpm_no'},
                    {data: 'received_by', name: 'received_by'},
                    {data: 'created_by', name: 'created_by', searchable: false},
                    {data: 'created_at', name: 'created_at', searchable: false},
                    {data: 'status', name: 'status', searchable: false},
                    {data: 'action', name: 'action', orderable: false, searchable: false}
                ],
                "order": [[ 6, "DESC" ]]
            });

            $('#btn-filter').click( function() {
                $('form#form').attr('action',"{{ route('logistic.lpb.search') }}");
                if($('form#form').valid()){
                    $('form#form').submit();
                }
            });

            $('#btn-export').click( function(e) {
                e.preventDefault();
                $('form#form').attr('action',"{{ route('logistic.lpb.export') }}");
                if($('form#form').valid()){
                    $('form#form').submit();
                }
            });

        });
    </script>
@stop
