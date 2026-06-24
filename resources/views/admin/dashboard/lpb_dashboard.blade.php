@extends('layouts.app')

@section('content')

<div class="row gap-20 pos-r">
    <div class="row" style="width:100%">
        <div class="mb-3" style="width:40%;">
            <div class="col-12 mb-2">
                <div class="layers bd bgc-white p-20">
                    <div class="layer w-100 mB-10" style="display: flex;justify-content:space-between;align-items:center;">
                        <span class="mb-0"><strong>Purchase Order [PO] Franco Jakarta</strong>
                            <br>Jumlah Dokumen PO Belum Tuntas Dibuatkan <br>Laporan Penerimaan Barang [LPB]
                        </span>
                        <a href="#" type="button" data-toggle="modal" data-target="#exampleModalPOJP"><span class="d-ib lh-0 va-m fw-600 bdrs-10em pX-15 pY-15 bgc-red-50 c-red-500">{{$countPOJP}}</span></a>
                    </div>
                </div>
            </div>
            <div class="col-12 mb-2">
                <div class="layers bd bgc-white p-20">
                    <div class="layer w-100 mB-10" style="display: flex;justify-content:space-between;align-items:center;">
                        <span class="mb-0"><strong>Laporan Penerimaan Barang [LPB]</strong>
                            <br>Jumlah Dokumen LPB Belum Tuntas Dibuatkan <br>Surat Pengantar Barang [SPB]
                        </span>
                        <a href="#" type="button" data-toggle="modal" data-target="#exampleModalLPBP"><span class="d-ib lh-0 va-m fw-600 bdrs-10em pX-15 pY-15 bgc-red-50 c-red-500">{{$countLPBP}}</span></a>
                    </div>
                </div>
            </div>
            <div class="col-12 mb-3">
                <div class="layers bd bgc-white p-20">
                    <div class="layer w-100 mB-10">
                        <div style="display: flex; justify-content: space-between; align-items: center;">

                        </div>
                    <h6 class="lh-1" style="text-align: center;font-size:10pt;">Daftar SOH Item LPB</h6>
                    </div>
                    <div class="layer w-100">
                        <table id="dataTablesSohLpb" class="table table-bordered" cellspacing="0" width="100%">
                            <thead>
                                <tr style="background-color: rgb(226, 226, 226);">
                                    <th style="font-size:8pt;">Nama Item</th>
                                    <th style="max-width: 20%;font-size:8pt;">Code Company</th>
                                    <th style="max-width: 20%;font-size:8pt;" class="text-center">SOH</th>
                                    <th style="max-width: 5%;font-size:8pt;" class="text-center">#</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="mb-3 row"style="width:60%;">
            <div class="col-12 mb-2">
                <div class="bd" style="background-color: #f3f3f3; color:black; padding-top: 10px;">
                    <div style="position: absolute; top: 20px; right: 40px;">
                        <small>
                            <a title="Export data item LPB 30 hari terakhir" href="{{ route('export_instan_item_lpb_30Days') }}" class="text-center">
                                <span class="d-ib lh-0 va-m fw-600 bdrs-10em pX-15 pY-15 bgc-red-50 c-red-500">
                                    <i class="fa fa-download"></i>
                                </span>
                            </a>
                        </small>
                    </div>
                    <div class="text-center mt-4" style="font-weight: bold; text-decoration: underline;">
                        Statistik jumlah barang In Out LPB 30 Hari Terakhir
                    </div>
                    <div class="peers fxw-nw@lg+ ai-s">
                        <div class="peer peer-greed w-70p@lg+ w-100@lg- p-20">
                            <div class="layers">
                                <div class="layer w-100">
                                    <canvas id="canvas" style="min-height:250px;"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
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
<div class="modal fade" id="modalShow" tabindex="-1" role="dialog" aria-labelledby="exampleModalLongTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" style="color: black;font-weight:normal;" id="exampleModalLongTitle">Data Masuk Item : <span style="font-weight: bold;" class="product_info"></span></h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <table id="dataTablesShowProduct" class="table table-bordered dataTablesShowProduct" cellspacing="0" width="100%">
            <thead>
                <tr style="background-color: rgb(226, 226, 226);">
                    <th >No. PO</th>
                    <th >No. LPB</th>
                    <th >QTY</th>
                    <th >Tgl Masuk</th>
                    <th >Dibuat Oleh</th>
                    <th >Status</th>
                </tr>
            </thead>
          </table>
        </div>
      </div>
    </div>
</div>
@endsection

@section('js')
    <script>
        $(document).ready(function() {

            $('#dataTablesSohLpb').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('logistic.monitoring.dpm.datatables_lpb_dashboard') }}',
                "pageLength":5,
                columns: [
                    {data: 'product', name: 'master_item_products.name', orderable: false},
                    {data: 'companyCode', name: 'companies.code', orderable: false},
                    {data: 'soh', name: 'lpb_items.qty', orderable: false},
                    {data: 'action', name: 'action', orderable: false, searchable: false},
                    {data: 'code', name: 'master_item_products.code', visible: false},
                    {data: 'part_number', name: 'master_item_products.part_number', visible: false},
                    {data: 'brand', name: 'master_item_brands.name', visible: false},
                ],
                "order": [[ 0, "ASC" ]]
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

            function getDays() {
                var labels = [];
                var date = new Date();
                var months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];

                for (var i = 29; i >= 0; i--) {
                    var dayDate = new Date(date);  // Membuat salinan dari objek date
                    dayDate.setDate(date.getDate() - i);  // Mengubah tanggal dengan benar
                    
                    var day = dayDate.getDate().toString().padStart(2, '0');
                    var month = months[dayDate.getMonth()];
                    var year = dayDate.getFullYear();
                    
                    labels.push(`${day}/${month}/${year}`);
                }

                return labels;
            }


            var labels = getDays();
            var inData = [{{ $gradeValueIN }}]; 
            var outData = [{{ $gradeValueOUT }}]; 

            var maxInValue = Math.max(...inData); 

            var stepSize = Math.ceil(maxInValue / 10);

            var config = {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'OUT',
                        backgroundColor: 'rgba(0, 0, 255, 0.2)', 
                        borderColor: '#000080',
                        data: outData,
                        fill: true,
                        borderWidth: 2,
                        stepped: true,
                    },{
                        label: 'IN',
                        backgroundColor: 'rgba(255, 69, 0, 0.2)',
                        borderColor: '#FF4500',
                        data: inData,
                        fill: false,
                        borderWidth: 2,
                        stepped: true,
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
                                labelString: 'Tanggal',
                                fontColor: '#000000'
                            },
                            ticks: {
                                fontColor: '#000000'
                            }
                        }],
                        yAxes: [{
                            display: true,
                            scaleLabel: {
                                display: true,
                                labelString: 'Total QTY',
                                fontColor: '#000000'
                            },
                            ticks: {
                                beginAtZero: true,
                                stepSize: stepSize, 
                                fontColor: '#000000'
                            }
                        }]
                    },
                    legend: {
                        labels: {
                            fontColor: '#000000'
                        }
                    }
                }
            };

            // Create chart
            var ctx = document.getElementById('canvas').getContext('2d');
            var myLine = new Chart(ctx, config);

            $(document).on('click', '.btnShow', function() {
                var product_id = $(this).data('product_id');
                var company_id = $(this).data('company_id');
                var product_info = $(this).data('product_info');

                $('.product_info').text(product_info);

                // Hancurkan DataTable jika sudah ada
                if ($.fn.dataTable.isDataTable('.dataTablesShowProduct')) {
                    $('.dataTablesShowProduct').DataTable().clear().destroy();
                }

                // Inisialisasi DataTable
                $('.dataTablesShowProduct').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: '{{ route('logistic.monitoring_lpb.data_item_lpb', ['product_id' => '__product_id__', 'company_id' => '__company_id__']) }}'
                            .replace('__product_id__', product_id)
                            .replace('__company_id__', company_id),
                        error: function(xhr, error, thrown) {
                            console.error('Error fetching data: ', error);
                        }
                    },
                    pageLength: 10,
                    autoWidth: false,
                    columns: [
                        { data: 'no_po', name: 'po.doc_no', orderable: false },
                        { data: 'doc_no', name: 'lpb.doc_no', orderable: false },
                        { data: 'qty', name: 'lpb_items.qty', orderable: false },
                        { data: 'created_at', name: 'lpb.created_at', orderable: false },
                        { data: 'created_by', name: 'lpb.created_by', orderable: false },
                        { data: 'status', name: 'lpb.status', orderable: false, searchable: false }
                    ],
                    order: [[3, "DESC"]]
                });
            });

        });
    </script>
@endsection
