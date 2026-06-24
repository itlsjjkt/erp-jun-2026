@extends('layouts.app')

@section('content')

    <div class="row gap-20 pos-r">
        <div class="col-4">

            <div class="layers bd bgc-white mb-3">
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

        </div>

        <div class="col-8">
          <div class="layers bd bgc-white p-20">
                <div class="layer w-100 mB-10">
                    <h6 class="lh-1">Approval DPM</h6>
                </div>
                <div class="layer w-100 mt-1">
                    <table class="table table-striped table-bordered" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th>No. DPM</th>
                                <th>Kapal/Departemen</th>
                                <th>Tgl Update</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($dpm as $item)
                                <tr>
                                    <td>{{ $item->doc_no }}</td>
                                    <td>{{ $item->department }}</td>
                                    <td>{{ date('d/m/Y H:i:s',strtotime( $item->updated_at)) }}</td>
                                    <td>
                                        <?php
                                            echo "<div class='btn-group'>
                                                <a href='".route('approval.purchase.set', ['id' => Hashids::encode($item->id)])."' title='Approval' data-toggle='tooltip' class='btn btn-outline'><span class='ti-thumb-up icon-lg'></span> </a>
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

    </div>


<div class="modal" id="modalAnnouncement" >
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title">{{ $announcement->title }}</h6>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                {!! $announcement->content !!}
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" data-dismiss="modal">Close</button>
            </div>

        </div>
    </div>
</div>




@endsection
