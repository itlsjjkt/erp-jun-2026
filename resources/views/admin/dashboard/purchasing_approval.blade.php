@extends('layouts.app')

@section('content')

    <div class="row gap-20 masonry mB-20 pos-r">
        <div class="masonry-sizer col-md-6"></div>
        <div class="masonry-item  w-100">
            <div class="row gap-20">
                <!-- #Toatl Visits ==================== -->
                <div class='col-md-3'>
                    <div class="layers bd bgc-white p-20">
                            <div class="layer w-100 mB-10">
                                <h6 class="mb-0">Purchase Requisition</h6>
                                <small>Total yang telah terbit</small>
                            </div>
                            <div class="layer w-100">
                                <div class="peers ai-sb fxw-nw">
                                    <div class="peer peer-greed">
                                        <i class="ti-file fa-2x"></i>
                                    </div>
                                    <div class="peer">
                                        <a href="{{ route('purchasing.pr') }}">
                                            <span class="d-ib lh-0 va-m fw-600 bdrs-10em pX-15 pY-15 bgc-green-50 c-green-500">{{ $countPR }}</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                    </div>
                </div>

                <!-- #Total Page Views ==================== -->
                <div class='col-md-3'>
                    <div class="layers bd bgc-white p-20">
                        <div class="layer w-100 mB-10">
                            <h6 class="mb-0">Purchase Order</h6>
                            <small>Total yang telah terbit</small>
                        </div>
                        <div class="layer w-100">
                            <div class="peers ai-sb fxw-nw">
                                <div class="peer peer-greed">
                                    <i class="ti-shopping-cart fa-2x"></i>
                                </div>
                                <div class="peer">
                                    <a href="{{ route('purchasing.po.index') }}">
                                        <span class="d-ib lh-0 va-m fw-600 bdrs-10em pX-15 pY-15 bgc-red-50 c-red-500">{{ $countPO }}</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- #Unique Visitors ==================== -->
                <div class='col-md-3'>
                    <div class="layers bd bgc-white p-20">
                        <div class="layer w-100 mB-10">
                            <h6 class="mb-0">Supplier</h6>
                            <small>Total supplier Aktif</small>
                        </div>
                        <div class="layer w-100">
                            <div class="peers ai-sb fxw-nw">
                                <div class="peer peer-greed">
                                    <i class="ti-truck fa-2x"></i>
                                </div>
                                <div class="peer">
                                    <a href="{{ route('purchasing.suppliers.index') }}">
                                        <span class="d-ib lh-0 va-m fw-600 bdrs-10em pX-15 pY-15 bgc-purple-50 c-purple-500">{{ $countSupplier }}</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- #Bounce Rate ==================== -->
                <div class='col-md-3'>
                    <div class="layers bd bgc-white p-20">
                        <div class="layer w-100 mB-10">
                            <h6 class="mb-0">Approval PO</h6>
                            <small>Total PO yang harus anda Approve</small>
                        </div>
                        <div class="layer w-100">
                            <div class="peers ai-sb fxw-nw">
                                <div class="peer peer-greed">
                                    <i class="ti-thumb-up fa-2x"></i>
                                </div>
                                <div class="peer">
                                    <a href="{{ route('approval.po.index') }}">
                                        <span class="d-ib lh-0 va-m fw-600 bdrs-10em pX-15 pY-15 bgc-blue-50 c-blue-500">{{ $countApproval }}</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <div class="masonry-item col-12">
            <!-- #Site Visits ==================== -->
            <div class="bd bgc-white">
                <div class="peers fxw-nw@lg+ ai-s row">
                    <div class="peer peer-greed bdL w-70p@lg+ w-100@lg- p-20 col-sm-6">
                        <div class="layers">
                            <div class="layer w-100 mB-10">
                                <div class="text-right">
                                    <a href="{{ route('approval.supplier.index') }}">
                                        <span class="d-ib lh-0 va-m fw-600 bdrs-10em pX-15 pY-15 bgc-red-50 c-red-500">Tampilkan Semua</span>
                                    </a>
                                </div>
                                <h6 class="lh-1 text-black">Supplier Pending Approval</h6>
                            </div>
                            <div class="layer w-100">
                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered" cellspacing="0" width="100%">
                                        <thead>
                                            <tr>
                                                <th>Nama Supplier</th>
                                                <th>Dibuat Oleh</th>
                                                <th>Step</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @if(count($supplier_approval) > 0)
                                                @foreach($supplier_approval as $val)
                                                <tr>
                                                    <td>{{ $val->name }}</td>
                                                    <td>{{ $val->created_by_name }}</td>
                                                    <td><span class="badge badge-info">Step {{ $val->step }}</span></td>
                                                    <td>
                                                        <a href="{{ route('approval.supplier.set', Hashids::encode($val->id)) }}" class="btn btn-outline" title="Proses Approval">
                                                            <span class="ti-thumb-up icon-lg"></span>
                                                        </a>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            @else
                                                <tr><td colspan="4" class="text-center">Tidak ada data</td></tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="peer peer-greed bdL w-70p@lg+ w-100@lg- p-20 col-sm-6">
                        <div class="layers">
                            <div class="layer w-100 mB-10">
                                <div class="text-right">
                                    <a href="{{ route('approval.po.index') }}">
                                        <span class="d-ib lh-0 va-m fw-600 bdrs-10em pX-15 pY-15 bgc-red-50 c-red-500">Tampilkan Semua PO</span>
                                    </a>
                                </div>
                                <h6 class="lh-1 text-black">Purchase Order Pending Approval</h6>
                            </div>
                            <div class="layer w-100">
                                <div id="container" style="width: 100%;">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-bordered" cellspacing="0" width="100%">
                                            <thead>
                                                <tr>
                                                    <th>NO. PO</th>
                                                    <th>AMOUNT</th>
                                                    <th>SUPPLIER</th>
                                                    <th>PURCHASER</th>
                                                    <th>AKSI</th>
                                                </tr>
                                            </thead>
                                            @if (count($po) > 0)
                                                @foreach ($po as $val)
                                                    <tr>
                                                        <td>
                                                            <?php
                                                            echo "<a target='_blank' href='".route('purchasing.po.show', Hashids::encode($val->id))."'>". $val->doc_no." </a>";
                                                            ?>
                                                        </td>
                                                        <td>
                                                            <?php
                                                                echo "<span class='currency' data-content='".$val->currency."'>".number_format($val->payment_amount,2,".",',')."</span>";
                                                            ?>
                                                        </td>
                                                        <td>{{ $val->supplier }}</td>
                                                        <td>{{ $val->created }}</td>
                                                        <td>
                                                            <?php
                                                                echo "<div class='btn-group'>
                                                                <a href='".route('approval.po.set', ['id' => Hashids::encode($val->id)])."' title='Approval PO' data-toggle='tooltip' class='btn btn-outline'><span class='ti-thumb-up icon-lg'></span> </a>
                                                                </div>";
                                                            ?>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @else
                                                <tr><td colspan="5" class="text-center">Tidak ada data</td></tr>
                                            @endif
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div href="#" id="toggleButton" style="background-color: white; border-color: white;border-bottom-left-radius: 0;" class="btn btn-outline text-uppercase fw-600" data-toggle="collapse" data-target="#infoPo" aria-expanded="false" onclick="toggleText()">
        TAMPILKAN DATA JUMLAH PO PER COMPANY
    </div>

    <div class="collapse mB-20" id="infoPo" aria-expanded="false">
        <div class="bgc-white bd bdrs-3 p-20 mB-20">
                <h5 style="font-weight:bold;">
                    DATA JUMLAH PO PER COMPANY
                </h5>
            <div class="bd bgc-white" style="width 5000px;">
                <div class="row d-flex flex-wrap justify-content-between">
                    @foreach ($loc as $key => $val)
                        <div class="col p-2" style="flex: 1 1 0%; text-align: center; box-sizing: border-box;">
                            <div class="layer w-100 text-end">
                                <strong>{{ $key }}</strong>
                            </div>
                            <div class="layer w-100" style="display: flex; justify-content: center; align-items: center; flex-direction: column;">
                                <?php
                                foreach($val as $values) {
                                    echo '<div class="pt-2 pb-2 border border-info mt-2" style="box-sizing: border-box; width: 80%; display: flex; justify-content: space-between; align-items: center; background-color: rgba(128, 128, 128, 0.1);">';
                                        echo "<span style='padding-left:10px;' class='c-grey-600 text-capitalize'>".ucwords(strtolower($values->name)). "</span>";
                                        echo "<span style='padding-right:10px;' class='c-grey-600 font-weight-bold'>".($values->num ?? 0)."</span>";
                                    echo "</div>";
                                }
                                ?>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

@endsection


@section('js')
<script>
    $(document).ready(function() {


    });

    function toggleText() {
        var collapseElement = document.getElementById("infoPo");
        var toggleButton = document.getElementById("toggleButton");

        if (collapseElement.classList.contains("show")) {
            toggleButton.innerText = "TAMPILKAN DATA JUMLAH PO PER COMPANY";
        } else {
            toggleButton.innerText = "SEMBUNYIKAN DATA JUMLAH PO PER COMPANY";
        }
    }
</script>
@stop


