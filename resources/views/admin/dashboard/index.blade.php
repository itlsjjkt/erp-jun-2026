@extends('layouts.app')

@section('content')

    <div class="row gap-20 pos-r">
        <div class="col-4">

            <div class="layers bd mb-3" style="background: #ffffe0">
                <div class="p-20 w-100">
                    <div class="layer w-100 mB-10">
                        <h6 class="lh-1">Pengumuman</h6>
                    </div>
                    <div class="layer w-100 mt-3">
                        <a href="#" class="nav-link font-weight-bold p-0" data-toggle="modal" data-target="#modalAnnouncement">{{ $announcement->title }}</a>
                        <?php echo $announcement->content ?>
                    </div>
                </div>
            </div>

            <div class="layers bd bgc-white mb-3">
                <div class="p-20 w-100">
                    <div class="layer mB-30">
                        <h6 class="lh-1">Stastitik Delivery Tahun {{ date('Y') }}</h6>
                    </div>
                        <!-- Progress Bars -->
                    <div class="layer">
                        <h5 class="mB-5">LPB</h5>
                        <small class="fw-600 c-grey-700">Total transaksi</small>
                        <span class="pull-right c-grey-600 fsz-sm">{{ $count['3']->count }}</span>
                        <hr>
                    </div>
                    <div class="layer w-100 mT-15">
                        <h5 class="mB-5">SPB</h5>
                        <small class="fw-600 c-grey-700">Total transaksi</small>
                        <span class="pull-right c-grey-600 fsz-sm">{{ $count['4']->count }}</span>
                        <hr>
                    </div>
                    <div class="layer w-100 mT-15">
                        <h5 class="mB-5">BPB Jakarta</h5>
                        <small class="fw-600 c-grey-700">Total transaksi</small>
                        <span class="pull-right c-grey-600 fsz-sm">{{ $count['5']->count }}</span>
                        <hr>
                    </div>
                    <div class="layer w-100 mT-15">
                        <h5 class="mB-5">BPB Lokal</h5>
                        <small class="fw-600 c-grey-700">Total transaksi</small>
                        <span class="pull-right c-grey-600 fsz-sm">{{ $count['6']->count }}</span>
                    </div>
                </div>
            </div>

        </div>

        <div class="col-8">
            <div class="row gap-20">
                <!-- #Toatl Visits ==================== -->
                <div class='col-md-4'>
                    <div class="layers bd bgc-white p-20">
                        <div class="layer w-100 mB-10">
                            <h6 class="lh-1">Total <br> DPM</h6>
                        </div>
                        <div class="layer w-100">
                            <div class="peers ai-sb fxw-nw">
                                <div class="peer peer-greed">
                                    <i class="ti-file fa-2x"></i>
                                </div>
                                <div class="peer">
                                    <a href="{{ route('logistic.monitoring.dpm') }}">
                                        <span class="d-ib lh-0 va-m fw-600 bdrs-10em pX-15 pY-15 bgc-green-50 c-green-500">{{ $count['0']->count }}</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- #Total Page Views ==================== -->
                <div class='col-md-4'>
                    <div class="layers bd bgc-white p-20">
                        <div class="layer w-100 mB-10">
                            <h6 class="lh-1">Total <br>Purchase Requisition (PR)</h6>
                        </div>
                        <div class="layer w-100">
                            <div class="peers ai-sb fxw-nw">
                                <div class="peer peer-greed">
                                    <i class="ti-files fa-2x"></i>
                                </div>
                                <div class="peer">
                                    <span class="d-ib lh-0 va-m fw-600 bdrs-10em pX-15 pY-15 bgc-red-50 c-red-500">{{ $count['1']->count }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- #Unique Visitors ==================== -->
                <div class='col-md-4'>
                    <div class="layers bd bgc-white p-20">
                        <div class="layer w-100 mB-10">
                            <h6 class="lh-1">Total <br>Purchase Order (PO)</h6>
                        </div>
                        <div class="layer w-100">
                            <div class="peers ai-sb fxw-nw">
                                <div class="peer peer-greed">
                                    <i class="ti-shopping-cart fa-2x"></i>
                                </div>
                                <div class="peer">
                                    <span class="d-ib lh-0 va-m fw-600 bdrs-10em pX-15 pY-15 bgc-red-50 c-red-500">{{ $count['2']->count }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row gap-20">
                <div class='col-md-12'>
                    <div class="bd bgc-white">
                        <div class="peers fxw-nw@lg+ ai-s">
                            <div class="peer peer-greed w-70p@lg+ w-100@lg- p-20">
                                <div class="layers">
                                    <div class="layer w-100">
                                        <canvas id="canvas" style="min-height:250px"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                </div>
            </div>

        </div>

        
    </div>

@endsection

<?php 

    $grade_value= [];
    $grade_value= [];
    $month =  array('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul','Aug', 'Sep', 'Oct', 'Nov', 'Dec');

    foreach ($statistics as $val => $item){
        $grade_value[$val][]= $item->jan;
        $grade_value[$val][]= $item->feb;
        $grade_value[$val][]= $item->mar;
        $grade_value[$val][]= $item->apr;
        $grade_value[$val][]= $item->mei;
        $grade_value[$val][]= $item->jun;
        $grade_value[$val][]= $item->jul;
        $grade_value[$val][]= $item->aug;
        $grade_value[$val][]= $item->sep;
        $grade_value[$val][]= $item->oct;
        $grade_value[$val][]= $item->nov;
        $grade_value[$val][]= $item->dec;
    }

    $gradeValueDPM  = implode(',', $grade_value[0]) ;
    $gradeValuePR   = implode(',', $grade_value[1]) ;
    $gradeValuePO   = implode(',', $grade_value[2]) ;
    
?>

@section('js')
    <script>
        $(document).ready(function() {
            var config = {
                type: 'bar',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul','Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                    datasets: [{
                        label: 'DPM',
                        backgroundColor: 'rgb(255, 99, 132)',
                        borderColor: 'rgb(255, 99, 132)',
                        data: [{{ $gradeValueDPM }}],
                        fill: false,
                    }, {
                        label: 'PR',
                        fill: false,
                        backgroundColor: 'rgb(54, 162, 235)',
                        borderColor: 'rgb(54, 162, 235)',
                        data: [{{ $gradeValuePR }}],
                    },{
                        label: 'PO',
                        fill: false,
                        backgroundColor: 'rgb(75, 192, 192)',
                        borderColor: 'rgb(75, 192, 192)',
                        data: [{{ $gradeValuePO }}],
                    }
                    ]
                },
                options: {
                    responsive: true,
                    title: {
                        display: true,
                        text: 'Statistik Transaksi Per Bulan Tahun {{ date("Y") }}'
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
                                labelString: 'Data Transaksi'
                            },
                            ticks: {
                                beginAtZero: true
                            }
                        }]
                    }
                }
            };

            var ctx = document.getElementById('canvas').getContext('2d');
            window.myLine = new Chart(ctx, config);

        });
    </script>
@stop