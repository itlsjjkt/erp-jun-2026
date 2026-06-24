@extends('layouts.app')

@section('content')

<div class="row gap-20 pos-r">
    <div class="col-12 row">
        <div class='col-md-4'>
            <div class="layers bd bgc-white p-20"style="min-height: 60px;">
                <div class="layer w-100 mB-10" style="display: flex;justify-content:space-between;align-items:center;">
                    <span class="mb-0"><strong>Daftar Permintaan Material [DPM]</strong>
                        <br>Jumlah Dokumen DPM Belum Terbit <br>Purchase Requisition [PR]
                    </span>
                    <a href="#" type="button" data-toggle="modal" data-target="#exampleModalDPMP"><span class="d-ib lh-0 va-m fw-600 bdrs-10em pX-15 pY-15 bgc-red-50 c-red-500">{{$countDPMP}}</span></a>
                </div>
            </div>
        </div>
        <div class='col-md-4'>
            <div class="layers bd bgc-white p-20"style="min-height: 60px;">
                <div class="layer w-100 mB-10" style="display: flex;justify-content:space-between;align-items:center;">
                    <span class="mb-0"><strong>Purchase Requisition [PR]</strong>
                        <br>Jumlah Dokumen PR Belum Tuntas Dibuatkan <br>Purchase Order [PO]
                    </span>
                    <a href="#" type="button" data-toggle="modal" data-target="#exampleModalPRP"><span class="d-ib lh-0 va-m fw-600 bdrs-10em pX-15 pY-15 bgc-red-50 c-red-500">{{$countPRP}}</span></a>
                </div>
            </div>
        </div>
        <div class='col-md-4'>
            <div class="layers bd bgc-white p-20"style="min-height: 60px;">
                <div class="layer w-100 mB-10" style="display: flex;justify-content:space-between;align-items:center;">
                    <span class="mb-0"><strong>Purchase Order [PO] Franco Lokal</strong>
                        <br>Jumlah Dokumen PO Belum Tuntas Dibuatkan <br>Bukti Penerimaan Barang [BPB] Lokal
                    </span>
                    <a href="#" type="button" data-toggle="modal" data-target="#exampleModalPOLP"><span class="d-ib lh-0 va-m fw-600 bdrs-10em pX-15 pY-15 bgc-red-50 c-red-500">{{$countPOLP}}</span></a>
                </div>
            </div>
        </div>
        {{-- <div class='col-md-3'>
            <div class="layers bd bgc-white p-20"style="min-height: 60px;">
                <div class="layer w-100 mB-10" style="display: flex;justify-content:space-between;align-items:center;">
                    <span class="mb-0"><strong>Bukti Penerimaan Barang [BPB] Lokal</strong>
                        <br>Jumlah Dokumen BPB Lokal Belum Publish
                    </span>
                    <span class="d-ib lh-0 va-m fw-600 bdrs-10em pX-15 pY-15 bgc-red-50 c-red-500">{{$countBPBLP}}</span>
                </div>
            </div>
        </div> --}}
    </div>
    <div class="col-12 row">
        <div class='col-md-4'>
            <div class="layers bd bgc-white p-20"style="min-height: 60px;">
                <div class="layer w-100 mB-10" style="display: flex;justify-content:space-between;align-items:center;">
                    <span class="mb-0"><strong>Purchase Order [PO] Franco Jakarta</strong>
                        <br>Jumlah Dokumen PO Belum Tuntas Dibuatkan <br>Laporan Penerimaan Barang [LPB]
                    </span>
                    <a href="#" type="button" data-toggle="modal" data-target="#exampleModalPOJP"><span class="d-ib lh-0 va-m fw-600 bdrs-10em pX-15 pY-15 bgc-red-50 c-red-500">{{$countPOJP}}</span></a>
                </div>
            </div>
        </div>
        <div class='col-md-4'>
            <div class="layers bd bgc-white p-20"style="min-height: 60px;">
                <div class="layer w-100 mB-10" style="display: flex;justify-content:space-between;align-items:center;">
                    <span class="mb-0"><strong>Laporan Penerimaan Barang [LPB]</strong>
                        <br>Jumlah Dokumen LPB Belum Tuntas Dibuatkan <br>Surat Pengantar Barang [SPB]
                    </span>
                    <a href="#" type="button" data-toggle="modal" data-target="#exampleModalLPBP"><span class="d-ib lh-0 va-m fw-600 bdrs-10em pX-15 pY-15 bgc-red-50 c-red-500">{{$countLPBP}}</span></a>
                </div>
            </div>
        </div>
        <div class='col-md-4'>
            <div class="layers bd bgc-white p-20"style="min-height: 60px;">
                <div class="layer w-100 mB-10" style="display: flex;justify-content:space-between;align-items:center;">
                    <span class="mb-0"><strong>Surat Pengantar Barang [SPB]</strong>
                        <br>Jumlah Dokumen SPB Belum Tuntas Dibuatkan <br>Bukti Penerimaan Barang [BPB] Jakarta
                    </span>
                    <a href="#" type="button" data-toggle="modal" data-target="#exampleModalSPBP"><span class="d-ib lh-0 va-m fw-600 bdrs-10em pX-15 pY-15 bgc-red-50 c-red-500">{{$countSPBP}}</span></a>
                </div>
            </div>
        </div>
        {{-- <div class='col-md-3'>
            <div class="layers bd bgc-white p-20"style="min-height: 60px;">
                <div class="layer w-100 mB-10" style="display: flex;justify-content:space-between;align-items:center;">
                    <span class="mb-0"><strong>Bukti Penerimaan Barang [BPB] Jakarta</strong>
                        <br>Jumlah Dokumen BPB Jakarta Belum Publish
                    </span>
                    <span class="d-ib lh-0 va-m fw-600 bdrs-10em pX-15 pY-15 bgc-red-50 c-red-500">{{$countBPBJP}}</span>
                </div>
            </div>
        </div> --}}
    </div>
    <div class="col-12 row">
        <div class="col-6 mb-4">
            <div class="layers bd bgc-white p-20">
                <div class="layer w-100">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <a href="{{ route('export_instan_pending_table') }}" class="text-center">
                            <span class="d-ib lh-0 va-m fw-600 bdrs-10em pX-15 pY-15 bgc-green-50 c-green-500"><i class="fa fa-download"></i></span>
                        </a>
                        <a target="_blank" href="{{ route('logistic.monitoring.dpm_pending') }}">
                            <span class="d-ib lh-0 va-m fw-600 bdrs-10em pX-15 pY-15 bgc-green-50 c-green-500">Tampilkan Semua Dokumen DPM Pending</span>
                        </a>
                    </div>
                    <br>
                <h6 class="lh-1">Daftar Dokumen Pending</h6>
                </div>                
                <div class="layer w-100">
                    <div class="table-responsive">
                        <table id="dataTables" class="table table-bordered" cellspacing="0" width="100%">
                            <thead>
                                <tr style="background-color: rgb(226, 226, 226);">
                                    <th>No DPM</th>
                                    <th style="max-width: 20%;">Kapal/Departement</th>
                                    <th style="max-width: 20%;">Dibuat Oleh</th>
                                    <th style="max-width: 20%;">Tgl Input</th>
                                    <th style="max-width: 5%;">Aksi</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 mb-3">
            <div class="bd bg-white" style="overflow-x: auto;">
                <div class="text-center">
                    <br>
                    STATISTIK ADMINISTRASI DOKUMEN DPM LOGISTIK
                    <br>
                </div>
                <div class="peers fxw-nw@lg+ ai-s">
                    <div class="peer peer-greed w-70p@lg+ w-100@lg- p-20">
                        <div class="layers">
                            <div class="layer w-100">
                                <!-- canvas dengan lebar dinamis -->
                                <canvas id="canvas" style="height:400px; width: 100%;"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="exampleModalDPMP" tabindex="-1" role="dialog" aria-labelledby="exampleModalLongTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" style="color: black;" id="exampleModalLongTitle">Daftar Dokumen DPM Belum Terbit Purchase Requisition [PR]</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
            <div class="table-responsive mt-5">
                <table id="dataTablesDPMP" class="table table-bordered" cellspacing="0" width="100%">
                    <thead>
                        <tr style="background-color: rgb(226, 226, 226);">
                            <th>No. DPM</th>
                            <th>Dibuat Oleh</th>
                            <th>Tgl Buat</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
      </div>
    </div>
</div>

<div class="modal fade" id="exampleModalPRP" tabindex="-1" role="dialog" aria-labelledby="exampleModalLongTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" style="color: black;" id="exampleModalLongTitle">Daftar Dokumen PR Belum Tuntas Dibuatkan Purchase Order [PO]</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            <div class="table-responsive mt-5">
                <table id="dataTablesPRP" class="table table-bordered" cellspacing="0" width="100%">
                    <thead>
                        <tr style="background-color: rgb(226, 226, 226);">
                            <th >No. PR</th>
                            <th >No. DPM</th>
                            <th >Dibuat Oleh [DPM]</th>
                            <th >Tgl Terbit PR</th>
                            <th >Status PR</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
      </div>
    </div>
</div>

<div class="modal fade" id="exampleModalPOLP" tabindex="-1" role="dialog" aria-labelledby="exampleModalLongTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" style="color: black;" id="exampleModalLongTitle">Daftar Dokumen PO Lokal Belum Tuntas Dibuatkan Bukti Penerimaan Barang [BPB] Lokal
            </h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
            <div class="table-responsive mt-5">
                <table id="dataTablesPOLP" class="table table-bordered" cellspacing="0" width="100%">
                    <thead>
                        <tr style="background-color: rgb(226, 226, 226);">
                            <th >No. PO</th>
                            <th >No. DPM</th>
                            <th >Dibuat Oleh [DPM]</th>
                            <th >Tgl PO</th>
                            <th >Status PO</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
      </div>
    </div>
</div>
<div class="modal fade" id="exampleModalPOJP" tabindex="-1" role="dialog" aria-labelledby="exampleModalLongTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
            <h5 class="modal-title" style="color: black;" id="exampleModalLongTitle">Daftar Dokumen PO Jakarta Belum Tuntas Dibuatkan Laporan Penerimaan Barang [LPB]
                </h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            </div>
            <div class="modal-body">
                <div class="table-responsive mt-5">
                    <table id="dataTablesPOJP" class="table table-bordered" cellspacing="0" width="100%">
                        <thead>
                            <tr style="background-color: rgb(226, 226, 226);">
                                <th >No. PO</th>
                                <th >No. DPM</th>
                                <th >Dibuat Oleh [DPM]</th>
                                <th >Tgl PO</th>
                                <th >Status PO</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="exampleModalLPBP" tabindex="-1" role="dialog" aria-labelledby="exampleModalLongTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
            <h5 class="modal-title" style="color: black;" id="exampleModalLongTitle">Daftar Dokumen LPB Belum Tuntas Dibuatkan Surat Pengantar Barang [SPB]
                </h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            </div>
            <div class="modal-body">
                <div>
                    <a href="{{ route('logistic.monitoring.export_lpb_pending') }}" class="text-center">
                        <span style="margin-top: 20px;" class="d-ib lh-0 va-m fw-600 bdrs-10em pX-15 pY-15 bgc-red-50 c-red-500">Download Data Item LPB Pending</span>
                    </a>
                </div>
                <br>
                <div class="table-responsive mt-5">
                    <table id="dataTablesLPBP" class="table table-bordered" cellspacing="0" width="100%">
                        <thead>
                            <tr style="background-color: rgb(226, 226, 226);">
                                <th >No. LPB</th>
                                <th >No. DPM</th>
                                <th >Dibuat Oleh [DPM]</th>
                                <th >Tgl LPB</th>
                                <th >Status LPB</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="exampleModalSPBP" tabindex="-1" role="dialog" aria-labelledby="exampleModalLongTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
            <h5 class="modal-title" style="color: black;" id="exampleModalLongTitle">Daftar Dokumen SPB Belum Tuntas Dibuatkan Bukti Penerimaan Barang [BPB] Jakarta
                </h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            </div>
            <div class="modal-body">
                <div class="table-responsive mt-5">
                    <table id="dataTablesSPBP" class="table table-bordered" cellspacing="0" width="100%">
                        <thead>
                            <tr style="background-color: rgb(226, 226, 226);">
                                <th >No. SPB</th>
                                <th >No. DPM <span style="float: right;">[Dibuat Oleh]</span></th>
                                <th >Tgl SPB</th>
                                <th >Status SPB</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
    <script>
        $(document).ready(function() {

            $('#dataTables').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('logistic.monitoring.dpm.datatables_logistic') }}',
                "pageLength":5,
                columns: [
                    {data: 'doc_no', name: 'purchases.doc_no', orderable: false},
                    {data: 'kd', name: 'departments.name', orderable: false},
                    {data: 'created', name: 'users.name', orderable: false},
                    {data: 'created_at', name: 'purchases.created_at',searchable: false},
                    {data: 'action', name: 'action', orderable: false, searchable: false}
                ],
                "order": [[ 3, "ASC" ]]
            });

            //COUNT DPM P
            $('#dataTablesDPMP').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('logistic.monitoring.monitoring_count_dpmp') }}',
                pageLength: 10,
                autoWidth: false,
                columns: [
                    { data: 'doc_no', name: 'purchases.doc_no', orderable: false },
                    { data: 'created_by', name: 'users.name', orderable: false },
                    { data: 'created_at', name: 'purchases.created_at', searchable: false },
                    { data: 'status', name: 'purchases.status', orderable: false, searchable: false }
                ],
                order: [[2, 'asc']],
                columnDefs: [
                    { width: '30%', targets: 0 }, 
                    { width: '30%', targets: 1 },
                    { width: '25%', targets: 2 }, 
                    { width: '15%', targets: 3 }  
                ]
            });

            //COUNT PR P
            $('#dataTablesPRP').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('logistic.monitoring.monitoring_count_prp') }}',
                "pageLength":10,
                autoWidth: false,
                columns: [
                    {data: 'doc_no_pr', name: 'purchase_requisitions.doc_no', orderable: false},
                    {data: 'doc_no_dpm', name: 'purchases.doc_no', orderable: false},
                    {data: 'created_by', name: 'users.name', orderable: false},
                    {data: 'created_at', name: 'purchase_requisitions.created_at',searchable: false},
                    {data: 'status', name: 'purchase_requisitions.status', orderable: false, searchable: false}
                ],
                "order": [[ 3, "ASC" ]],
                columnDefs: [
                    { width: '25%', targets: 0 }, 
                    { width: '25%', targets: 1 },
                    { width: '20%', targets: 2 }, 
                    { width: '15%', targets: 3 },  
                    { width: '15%', targets: 4 }  
                ]
            });

            //COUNT PO L P
            $('#dataTablesPOLP').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('logistic.monitoring.monitoring_count_polp') }}',
                "pageLength": 10,
                autoWidth: false,
                columns: [
                    {data: 'doc_no_po', name: 'po.doc_no', orderable: false},
                    {data: 'doc_no_dpm', name: 'purchases.doc_no', orderable: false},
                    {data: 'created_by', name: 'users.name', orderable: false},
                    {data: 'created_at', name: 'po.created_at',searchable: false},
                    {data: 'status', name: 'po.status', orderable: false, searchable: false}
                ],
                "order": [[ 3, "ASC" ]],
                columnDefs: [
                    { width: '25%', targets: 0 }, 
                    { width: '25%', targets: 1 },
                    { width: '20%', targets: 2 }, 
                    { width: '15%', targets: 3 },  
                    { width: '15%', targets: 4 }  
                ]
            });

            //COUNT PO J P
            $('#dataTablesPOJP').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('logistic.monitoring.monitoring_count_pojp') }}',
                "pageLength": 10,
                autoWidth: false,
                columns: [
                    {data: 'doc_no_po', name: 'po.doc_no', orderable: false},
                    {data: 'doc_no_dpm', name: 'purchases.doc_no', orderable: false},
                    {data: 'created_by', name: 'users.name', orderable: false},
                    {data: 'created_at', name: 'po.created_at',searchable: false},
                    {data: 'status', name: 'po.status', orderable: false, searchable: false}
                ],
                "order": [[ 3, "ASC" ]],
                columnDefs: [
                    { width: '25%', targets: 0 }, 
                    { width: '25%', targets: 1 },
                    { width: '20%', targets: 2 }, 
                    { width: '15%', targets: 3 },  
                    { width: '15%', targets: 4 }  
                ]
            });

            //COUNT LPB P
            $('#dataTablesLPBP').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('logistic.monitoring.monitoring_count_lpbp') }}',
                "pageLength": 10,
                autoWidth: false,
                columns: [
                    {data: 'doc_no_lpb', name: 'lpb.doc_no', orderable: false},
                    {data: 'doc_no_dpm', name: 'purchases.doc_no', orderable: false},
                    {data: 'created_by', name: 'users.name', orderable: false},
                    {data: 'created_at', name: 'lpb.created_at',searchable: false},
                    {data: 'status', name: 'lpb.status', orderable: false, searchable: false}
                ],
                "order": [[ 3, "ASC" ]],
                columnDefs: [
                    { width: '25%', targets: 0 }, 
                    { width: '25%', targets: 1 },
                    { width: '20%', targets: 2 }, 
                    { width: '15%', targets: 3 },  
                    { width: '15%', targets: 4 }  
                ]
            });

            $('#dataTablesSPBP').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('logistic.monitoring.monitoring_count_spbp') }}',
                "pageLength": 10,
                autoWidth: false,
                columns: [
                    {data: 'doc_no_spb', name: 'spb.doc_no',orderable: false},
                    {data: 'doc_no_dpm', name: 'purchases.doc_no',orderable: false},
                    {data: 'created_at', name: 'spb.created_at', searchable: false},
                    {data: 'status', name: 'spb.status', orderable: false, searchable: false},
                    {data: 'name', name: 'users.name', visible: false},
                ],
                "order": [[ 2, "ASC" ]],
                columnDefs: [
                    { width: '20%', targets: 0 }, 
                    { width: '50%', targets: 1 }, 
                    { width: '15%', targets: 2 },  
                    { width: '15%', targets: 3 }  
                ],
                "rowCallback": function(row, data, index) {
                    $('td', row).css('height', '100%');
                }
            });

            function getLastSixMonths() {
                var labels = [];
                var date = new Date();
                date.setDate(1);
                for (var i = 2; i >= 0; i--) {
                    date.setMonth(date.getMonth() - i);
                    var month = date.toLocaleString('default', { month: 'short' });
                    labels.push(month);
                    date.setMonth(date.getMonth() + i);
                }
                return labels;
            }
            var labels = getLastSixMonths();
            var config = {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'DPM ',
                        backgroundColor: 'rgb(240, 128, 128)',
                        borderColor: 'rgb(240, 128, 128)',
                        data: [{{ $gradeValueDPM }}],
                        fill: false,
                    }, {
                        label: 'PR    ',
                        fill: false,
                        backgroundColor: 'rgb(135, 206, 250)',
                        borderColor: 'rgb(135, 206, 250)',
                        data: [{{ $gradeValuePR }}],
                    },{
                        label: 'PO Jakarta',
                        fill: false,
                        backgroundColor: 'rgb(40, 178, 170)',
                        borderColor: 'rgb(40, 178, 170)',
                        data: [{{ $gradeValuePOJ }}],
                        stack: 'stack0'
                    },{
                        label: 'PO Lokal',
                        fill: false,
                        backgroundColor: 'rgb(170,204,0)',
                        borderColor: 'rgb(170,204,0)',
                        data: [{{ $gradeValuePOL }}],
                        stack: 'stack0'                    
                    },{
                        label: 'PO Jakarta Done',
                        fill: false,
                        backgroundColor: 'rgb(238, 121, 66)',
                        borderColor: 'rgb(238, 121, 66)',
                        data: [{{ $gradeValuePOJDone }}],
                        stack: 'stack1'
                    }, {
                        label: 'PO Lokal Done',
                        fill: false,
                        backgroundColor: 'rgb(108, 52, 131)',
                        borderColor: 'rgb(108, 52, 131)',
                        data: [{{ $gradeValuePOLDone }}],
                        stack: 'stack1'
                    }]
                },
                options: {
                    responsive: true,
                    title: {
                        display: false,
                        text: 'Statistics'
                    },
                    tooltips: {
                        mode: 'index',
                        intersect: false,
                    },
                    hover: {
                        mode: 'nearest',
                        intersect: true
                    },
                    scales: {
                        xAxes: [{
                            display: true,
                            scaleLabel: {
                                display: true,
                                labelString: 'Bulan'
                            }
                        }],
                        yAxes: [{
                            display: true,
                            scaleLabel: {
                                display: true,
                                labelString: 'Total'
                            },
                            ticks: {
                                beginAtZero: true,
                                stepSize: 50
                            }
                        }]
                    }
                }
            };
            var ctx = document.getElementById('canvas').getContext('2d');
            var myLine = new Chart(ctx, config);
        });
    </script>
@endsection
