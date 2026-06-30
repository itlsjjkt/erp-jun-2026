@extends('layouts.app')

@section('page-header')
   Bukti Penerimaan Barang
@endsection

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item active" aria-current="page">Bukti Penerimaan Barang</li>
    </ol>
@endsection

@section('content')

<div class="bgc-white bd bdrs-3 p-20 mB-20">
    <div class="mB-20">
            @php
                use Illuminate\Support\Facades\Gate;
            @endphp
            @if(!GATE::allows('bpb_monitoring'))
                {{-- @if(Auth::user()->id == 1) --}}
                <a href="{{ route('logistic.bpb.list') }}" class="btn btn-success">
                    Input BPB
                </a>
                {{-- @endif --}}
                <a  href="#" data-toggle="collapse" data-target="#filter" class="btn btn-outline border-dark float-right">
                    Filter | Export Data
                </a>
            @endif
        </div>

        <div class="collapse mB-20 mt-5" id="filter" aria-expanded="false">
            <form class="form-horizontal" id="form" method='GET'>
                {{ csrf_field() }}
                <div class="bgc-white bd bdrs-3 p-20">
                    <h6>Filter | Export Data</h6>
                    <hr>
                    <div class="form-group row">
                        <div class="col-sm-4">
                            <label>Periode </label>
                            <div class="input-group w-100">
                                <input type="text" name="start_date"  class="form-control datepicker m-r-n-1" placeholder="mm/dd/yyyy" required value="{{ date('m/d/Y') }}">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">ke</div>
                                </div>
                                <input type="text" name="end_date"  class="form-control datepicker" placeholder="mm/dd/yyyy" required value="{{ date('m/d/Y') }}">
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <button type="submit" class="btn btn-info mt-4" id="btn-filter">FILTER</button>
                            <button type="submit" class="btn btn-danger mt-4" id="btn-export">EXPORT</button>
                        </div>
                    </div>
                    
                </div>

            </form>
        </div>

        <div class="table-responsive mT-40">
     
            <table id="dataTables" class="table table-striped table-bordered" cellspacing="0" width="100%">
                <thead>
                    <tr>
                        <th>No. BPB</th>
                        <th>No. SPB</th>
                        <th>Penerima</th>
                        <th>Status</th>
                        <th>Status Verifikasi</th>
                        <th>Tgl Input</th>
                        <th></th>
                    </tr>
                </thead>
            </table>

        </div>

    </div>

@endsection


@section('js')
    <script>
        $(document).ready(function() {
            $('#dataTables').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('logistic.bpb.datatables') }}',
                "pageLength": 50,
                columns: [
                    {data: 'doc_no', name: 'doc_no'},
                    {data: 'noSPB',  name: 'spb.doc_no'},
                    {data: 'received_by', name: 'received_by'},
                    {data: 'status', name: 'status', searchable: false},
                    {data: 'status_verifikasi', name: 'status_verifikasi', searchable: false, orderable: false},
                    {data: 'created_at', name: 'created_at', searchable: false},
                    {data: 'action', name: 'action', orderable: false, searchable: false}
                ],
                "order": [[ 5, "DESC" ]]
            });

            $('#btn-filter').click( function() {
                $('form#form').attr('action',"{{ route('logistic.bpb.search') }}");
                if($('form#form').valid()){
                    $('form#form').submit();
                }
            });

            $('#btn-export').click( function(e) {
                e.preventDefault();
                $('form#form').attr('action',"{{ route('logistic.bpb.export') }}");
                if($('form#form').valid()){
                    $('form#form').submit();
                }
            });
            
        });
    </script>
@stop