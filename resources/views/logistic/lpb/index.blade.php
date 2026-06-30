@extends('layouts.app')

@section('page-header')
   Laporan Penerimaan Barang
@endsection

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item active" aria-current="page">Laporan Penerimaan Barang</li>
    </ol>
@endsection

@section('content')

<div class="bgc-white bd bdrs-3 p-20 mB-20">
    @php
        use Illuminate\Support\Facades\Gate;
    @endphp
    <div class="mB-20">
        @if(!GATE::allows('lpb_monitoring'))
            <a href="{{ route('logistic.lpb.list') }}" class="btn btn-success text-uppercase fsz-sm fw-600">
                <i class="ti-plus"></i> Input LPB
            </a>
        @endif
        <a  href="#" data-toggle="collapse" data-target="#filter" class="btn btn-outline border-dark float-right text-uppercase fsz-sm fw-600">
            FILTER | EXPORT DATA
        </a>
    </div>

    <div class="collapse mB-20" id="filter" aria-expanded="false">
        <form class="form-horizontal" id="form" method='GET'>
            {{ csrf_field() }}

            <div class="bgc-white bd bdrs-3 p-20">
                <h6>Filter | Export Data</h6>
                <hr>
                <div class="form-group row">
                    <div class="col-sm-3">
                        <label>Location </label>
                        @if(isAdministrator() || isAdministratorCompany() || isAdmin())
                            {!! Form::select('location_id', $location, old('location_id'), ['class' => 'form-control select2', 'id'=>'location_id']) !!}
                        @elseif(isAdministratorLocation())
                            <input type="text" readonly class="form-control" value="{{ getDataByID('locations',Auth::user()->location_id )->name }}">
                            <input type="hidden" name="location_id" id="location_id" value="{{ Auth::user()->location_id }}">
                        @else
                            @if(Auth::user()->location_id)
                                <input type="text" readonly class="form-control" value="{{ getDataByID('locations',Auth::user()->location_id)->name }}">
                                <input type="hidden" name="location_id" id="location_id" value="{{Auth::user()->location_id}}">
                            @else
                                {!! Form::select('location_id', $location, old('location_id'), ['class' => 'form-control select2', 'id'=>'location_id']) !!}
                            @endif
                        @endif
                    </div>
                    <div class="col-sm-4">
                        <label>Periode</label>
                        <div class="input-group w-100">
                            <input type="text" name="start_date"  class="form-control datepicker m-r-n-1" placeholder="mm/dd/yyyy" id="inlineDate" value="{{ date('m/d/Y') }}">
                            <div class="input-group-prepend">
                                <div class="input-group-text">ke</div>
                            </div>
                            <input type="text" name="end_date"  class="form-control datepicker" placeholder="mm/dd/yyyy" id="inlineDate" value="{{ date('m/d/Y') }}">
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <button type="submit" class="btn btn-info mt-4" id="btn-filter">FILTER</button>
                        @if(!GATE::allows('lpb_monitoring'))
                            <button type="submit" class="btn btn-danger mt-4" id="btn-export">EXPORT</button>
                        @endif
                    </div>
                </div>
            </div>

        </form>
    </div>


    <div class="table-responsive mt-5">
        <div class="alert alert-info">
            <b>INFORMASI</b>: Halaman ini menampilkan Data LPB tahun berjalan. Gunakan fitur pencarian untuk melihat Histori LPB dengan status lainnya.
        </div>
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
                    <th>Status Verifikasi</th>
                    <th>Action</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

<div class="modal fade" id="closeModal" tabindex="-1" role="dialog" aria-labelledby="cancelModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cancelModalLabel">Close LPB</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="cancelForm" action="" method="POST">
                    <?= csrf_field(); ?>
                    <div class="form-group">
                        <label for="reason">Alasan Close LPB<span class="text-danger">*</span> :</label>
                        <input id="reason" type="hidden" name="reason" value="" class="form-control" required>
                        <trix-editor input="reason"></trix-editor>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" id="submitClose">Submit</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('js')
    <script>
        document.getElementById('submitClose').addEventListener('click', function() {
            const reasonInput = document.getElementById('reason');
            if (reasonInput.value.trim() !== '') {
                const form = document.querySelector('.update');
                const modalForm = document.getElementById('cancelForm');
                modalForm.action = form.action;
                modalForm.submit();
            } else {
                alert('Alasan pembatalan harus diisi!');
            }
        });

        $(document).ready(function() {
            $('#dataTables').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('logistic.lpb.datatables') }}',
                "pageLength": 50,
                columns: [
                    {data: 'doc_no', name: 'doc_no'},
                    {data: 'po_no', name: 'po.doc_no'},
                    {data: 'pr_no', name: 'purchase_requisitions.doc_no'},
                    {data: 'dpm_no', name: 'purchase_requisitions.dpm_no'},
                    {data: 'received_by', name: 'received_by'},
                    {data: 'created_by', name: 'created_by'},
                    {data: 'created_at', name: 'created_at', searchable: false},
                    {data: 'status', name: 'status', searchable: false},
                    {data: 'status_verifikasi', name: 'status_verifikasi', searchable: false, orderable: false},
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
        document.addEventListener('trix-file-accept', function(e){
            e.preventDefault();
        });
    </script>
@stop
