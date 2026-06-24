@extends('layouts.app')

@section('page-header')
    Data Report
@endsection

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item active" aria-current="page">Report Data</li>
    </ol>
@endsection
@section('content')
<div class="row mB-40">
    <div class="col-sm-12">
        <div class="bgc-white p-20 bd row">
            @if(Gate::allows('master_data_report_all'))
                <div class="carddd row">
                    <button onclick="confirmExportPendingPO('{{ route('logistic.master_data_report.pending_po') }}')"
                            class="btn btn-hover-machine open-modal"
                            style="margin-left:20px;width:250px !important; height:150px !important; border:1px solid black; color:black;"
                            title="Export Data">
                        <span class="ti-file icon-lg btn_pending_po" style="font-size: 2rem;"></span><br>
                        <span>EXPORT PENDING PO <br><strong class="text-warning">(Belum LPB/BPB)</strong></span>
                    </button>
                </div>
            @endif
            <div class="carddd row" style="margin-left:20px;">
                <button onclick="confirmExportPendingPR('{{ route('logistic.master_data_report.pending_pr') }}')"
                        class="btn btn-hover-machine open-modal"
                        style="margin-left:20px;width:250px !important; height:150px !important; border:1px solid black; color:black;"
                        title="Export Data">
                    <span class="ti-file icon-lg btn_pending_po" style="font-size: 2rem;"></span><br>
                    <span>EXPORT PENDING PR <br> <strong class="text-primary">(Biweekly)</strong></span>
                </button>
            </div>
            <div class="carddd row" style="margin-left:20px;">
                <button onclick="confirmExportPendingApproval('{{ route('logistic.master_data_report.pending_approval') }}')"
                        class="btn btn-hover-machine open-modal"
                        style="margin-left:20px;width:250px !important; height:150px !important; border:1px solid black; color:black;"
                        title="Export Data">
                    <span class="ti-thumb-up icon-lg btn_pending_approval" style="font-size: 2rem;"></span><br>
                    <span>EXPORT PENDING <br> <strong class="text-danger">(APPROVAL DPM)</strong></span>
                </button>
            </div>
            @if(Gate::allows('master_data_report_all'))
                <div class="carddd row" style="margin-left:20px;">
                    <button onclick="confirmExportPendingDPM('{{ route('export_instan_pending_table') }}')"
                            class="btn btn-hover-machine open-modal"
                            style="margin-left:20px;width:250px !important; height:150px !important; border:1px solid black; color:black;"
                            title="Export Data">
                        <span class="ti-layout-accordion-merged icon-lg btn_pending_approval" style="font-size: 2rem;"></span><br>
                        <span>EXPORT PENDING <br><small><strong class="text-info">(DPM BELUM TUNTAS BPB)</strong></small></span>
                    </button>
                </div>
                <div class="carddd row" style="margin-left:20px;">
                    <button onclick="confirmExportPendingSPB('{{ route('logistic.master_data_report.spb') }}')"
                            class="btn btn-hover-machine open-modal"
                            style="margin-left:20px;width:250px !important; height:150px !important; border:1px solid black; color:black;"
                            title="Export Data">
                        <span class="ti-layout-media-right icon-lg" style="font-size: 2rem;"></span><br>
                        <span>EXPORT PENDING <br><small><strong class="text-success">(SPB BELUM TUNTAS BPB)</strong></small></span>
                    </button>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function confirmExportPendingPO(url) {
        Swal.fire({
            title: 'Export Pending PO?',
            text: "Data akan diekspor sekarang.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Export!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = url;
            }
        });
    }
    function confirmExportPendingPR(url) {
        Swal.fire({
            title: 'Export Pending PR?',
            text: "Data akan diekspor sekarang.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Export!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = url;
            }
        });
    }
    function confirmExportPendingApproval(url) {
        Swal.fire({
            title: 'Export Pending Approval DPM?',
            text: "Data akan diekspor sekarang.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Export!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = url;
            }
        });
    }
    function confirmExportPendingDPM(url) {
        Swal.fire({
            title: 'Export Pending DPM?',
            text: "Data akan diekspor sekarang.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Export!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = url;
            }
        });
    }
    function confirmExportPendingSPB(url) {
        Swal.fire({
            title: 'Export Pending SPB?',
            text: "Data akan diekspor sekarang.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Export!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = url;
            }
        });
    }
</script>

@stop
