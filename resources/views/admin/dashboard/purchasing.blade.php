@extends('layouts.app')

@section('content')

    <div class="row gap-20 masonry pos-r">
        <div class="masonry-sizer col-md-6"></div>
        <div class="masonry-item  w-100">
            <div class="row gap-20">
                <!-- #Toatl Visits ==================== -->
                <div class='col-md-3'>
                    <div class="layers bd bgc-white p-20">
                            <div class="layer w-100 mB-10">
                                <h6 class="mb-0">Purchase Requisition</h6>
                                <small>Total yang di-assign ke Anda</small>
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
                            <small>Total yang telah Anda terbitkan</small>
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
                <div class="peers fxw-nw@lg+ ai-s">
                    <div class="peer peer-greed w-70p@lg+ w-100@lg- p-20">
                        <div class="layers">
                            <div class="layer w-100 mB-10">
                                <div class="text-right">
                                    <a href="{{ route('purchasing.pr') }}"> 
                                        <span class="d-ib lh-0 va-m fw-600 bdrs-10em pX-15 pY-15 bgc-red-50 c-red-500">Tampilkan Semua</span>
                                    </a>
                                </div>
                                <h6 class="lh-1">Total Pending PR</h6>
                            </div>
                            <div class="layer w-100">
                                <table class="table table-bordered" cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th>NO. PR</th>
                                            <th>PROJECT</th>
                                            <th>KAPAL</th>
                                            <th>TIPE DPM</th>
                                            <th>TGL DIBUAT</th>
                                            <th>AKSI</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($pr as $item)
                                            @if($item->is_seen == 1)
                                                @php $class_seen = 'seen'; @endphp
                                            @else
                                                @php $class_seen = 'not_seen';  @endphp
                                            @endif
                                            <tr class="{{ $class_seen }}">
                                                <td>{{ $item->doc_no }}</td>
                                                <td>{{ $item->project }}</td>
                                                <td>{{ $item->department }}</td>
                                                <td>{{ strtoupper($item->type) }}</td>
                                                <td>{{ date('d/m/Y',strtotime( $item->created_at)) }}</td>
                                                <td>
                                                    <?php
                                                        $action = "<a data-id='$item->id' data-toggle='modal' data-target='#modalBlock' title='Tutup PR' class='btn btn-outline'><span class='ti-power-off text-danger icon-lg'></span> </a>";
                                                        if($item->type=='po') {
                                                            $action.= "<a href='".route('purchasing.po.create', ['id' => Hashids::encode($item->id)])."' title='Pembuatan PO' data-toggle='tooltip' class='btn btn-outline'><span class='ti-file icon-lg'></span> </a>";
                                                            // $action .= " <a href='".route('purchasing.dph.create_list_item', ['id' => Hashids::encode($item->id)])."' data-toggle='tooltip' title='Buat DPH' class='btn btn-outline'><span class='ti-files icon-lg'></span></a>";
                                                        }
                                                        echo "<div class='btn-group'>
                                                            <a href='".route('purchasing.pr.show', Hashids::encode($item->id))."' title='Detail PR' data-toggle='tooltip' class='btn btn-outline'><span class='ti-eye icon-lg'></span> </a>
                                                            ". $action."
                                                        </div>";
                                                    ?>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="5" class="text-center">Tidak ada data</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="peer bdL p-20 w-30p@lg+ w-100p@lg- col-sm-4">
                        <div class="layers">
                            <div class="layer w-100">
                                <h6 class="lh-1 mt-3">Jumlah Seluruh PO per Company</h6>
                                <div class="layers mt-4">
                                    @foreach ($loc as $key => $val)
                                        <div class="layer w-100 mB-20 border-bottom pb-3 row justify-content-between">
                                            <div class="layer w-100">
                                                <strong>{{ $key }}</strong>
                                            </div>
                                            <?php 
                                                foreach($val as $values)
                                                {
                                                    echo '<div class="col-sm-12 pt-2 pb-2 border border-info mt-2">';
                                                        echo "<span class='c-grey-600 text-capitalize'>".ucwords(strtolower($values->name)). "</span>";
                                                        echo "<span class='pull-right c-grey-600 font-weight-bold'>".$values->num."</span>";
                                                    echo "</div>";
                                                }
                                            ?>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>

@endsection


@section('js')
    <script>
    $(document).ready(function() {
        $(document).on('click', "form.close button", function(e) {
            var _this = $(this);
            e.preventDefault();
            Swal.fire({
                title: 'Konfirmasi', // Opération Dangereuse
                text: 'Apakah anda yakin untuk menutup PR ini?', // Êtes-vous sûr de continuer ?
                type: 'error',
                showCancelButton: true,
                confirmButtonColor: 'null',
                cancelButtonColor: 'null',
                confirmButtonClass: 'btn btn-danger',
                cancelButtonClass: 'btn btn-primary',
                confirmButtonText: 'Ya, tutup!', // Oui, sûr
                cancelButtonText: 'Batal', // Annuler
            }).then(res => {
                if (res.value) {
                    _this.closest("form").submit();
                }
            });
        });

    });
</script>
@stop