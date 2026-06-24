@extends('layouts.app')

@section('page-header')
    Detail Daftar Inventory Asset
@endsection

@section('breadcrumbs')
<ol class="breadcrumb">
	<li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
	<li class="breadcrumb-item"><a href="{{ route('logistic.parent_inventory_asset.index') }}">Daftar Inventory Asset</a></li>
	<li class="breadcrumb-item active" aria-current="page">Detail</li>
</ol>
@endsection

@section('content')
<style>
	.table td,
	.table th {
		padding: 0.3em 0.6em !important;
		vertical-align: middle !important;
	}
</style>
<div class="mB-40">
	<div class="bgc-white p-20 bd">
        <div class="row mb-3 justify-content-end">

			<div class="col-sm-6 ">
                <a href="{{ route('logistic.parent_inventory_asset.index') }}" class="nav-link"> <i class="ti-arrow-left"></i> Kembali  </a>
            </div>
			<div class="col-sm-6 d-flex justify-content-end align-items-center">
                <a href="{{ route('logistic.parent_inventory_asset.print', Hashids::encode($dia->id)) }}" target="_blank">
                    <i class="ti-printer icon-lg"></i>
                </a>
            </div>

		</div>
        <hr>
        <h6 class="text-center font-weight-bold mB-30" style="text-decoration:underline;margin-top:50px;">{{ $dia->doc_no }}</h6>
        <div class="row">
            <div class="col-sm-7">
                <div class="row">
                    <label class="col-sm-4 text-right">Dibuat Oleh </label>
                    <div class="col-sm-8">:
                        {!! getUserByID($dia->created_by) !!}
                    </div>
                </div>
            </div>
            <div class="col-sm-5">
                <div class="row">
                    <label class="col-sm-4 text-right">Tanggal Pembuatan</label>
                    <div class="col-sm-8">:
                        {!! getDateId($dia->created_at) !!}
                    </div>
                </div>
            </div>
        </div>
		<div class="tab-content mt-5 table-responsive">
            <table class="table table-bordered table-striped" style="width:100%;" id="datatablesX">
                <thead>
                    <tr>
                        <th>QR Code</th>
                        <th>Produk</th>
                        <th>Kode Asset</th>
                        <th>Tgl Dokumen</th>
                        <th>Dibuat Oleh</th>
                        <th>Status</th>
                        <th style="text-align: center;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    use Vinkla\Hashids\Facades\Hashids;
                    ?>
                    @foreach ($ast as $val)
                        <tr>
                            <td style="text-align: center;width:130px;">
                                @php
                                    $qrcode = QrCode::size(80)->generate('https://erp.haritashipping.com/inventory_asset/' . Hashids::encode($val->id).'/'.$val->uuid);
                                    // $qrcode = QrCode::size(80)->generate('http://192.168.1.77:8000/inventory_asset/' . Hashids::encode($val->id).'/'.$val->uuid);
                                @endphp
                                {!! $qrcode !!}
                            </td>
                            <td>
                                {!! $val->produk !!} <br>
                                <small>
                                    PN/Spec : {{$val->produkpn??'-'}} <br>
                                    Brand : {{$val->brand??'-'}} <br>
                                    UOM : {{$val->measure??'-'}}
                                </small>
                            </td>
                            <td>
                                {{$val->doc_no}}
                            </td>
                            <td>
                                {{getDateId($val->created_at)}}
                            </td>
                            <td>
                                {{getUserByID($val->created_by)}}
                            </td>
                            <td>
                                {!!getStatusInventoryAsset($val->status)!!}
                            </td>
                            <td style="width:200px;">
                                <div class="btn-group">
                                    <a value='{{route('logistic.inventory_asset.show', ['inventory_asset' => Hashids::encode($val->id)])}}'
                                        class='btn btn-outline modalShow'
                                        style='padding-top: 5px;padding-left: 5px;'
                                        title='Show Data'
                                        data-toggle='modal'
                                        data-target='#modalShow'>
                                        <i class='ti-eye'></i>
                                    </a>
                                    <a href="#"
                                        data-url="{{ route('logistic.inventory_asset.edit', Hashids::encode($val->id)) }}"
                                        class="btn btn-outline modalEdit"
                                        data-toggle="tooltip"
                                        title="Update Data">
                                        <span class="ti-pencil-alt"></span>
                                    </a>

                                    <div class="dropdown" style="margin-left:8px;">
                                        <a class="btn btn-outline dropdown-toggle" href="#" role="button" id="dropdownMenuPrint{{ $val->id }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="Cetak QR">
                                            <i class="fa fa-qrcode icon-lg"></i>
                                        </a>
                                        <div class="dropdown-menu" aria-labelledby="dropdownMenuPrint{{ $val->id }}">
                                            <a class="dropdown-item" href="{{ route('logistic.inventory_asset.print', Hashids::encode($val->id)) }}?ukuran=24" target="_blank">Label 24 mm</a>
                                            <a class="dropdown-item" href="{{ route('logistic.inventory_asset.print', Hashids::encode($val->id)) }}?ukuran=36" target="_blank">Label 36 mm</a>
                                        </div>
                                    </div>
                                    
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <hr>
	</div>
</div>

<div class="modal fade" id="modalShow" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
    data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-lg" role="document" style="box-shadow: 0 4px 8px rgba(0, 0, 0, 0.4);">
        <div class="modal-content" style="border: 2px solid #0088c1; border-radius: 10px;">
            <div class="modal-header" style="background-color: #0088c1">
                <h5 class="modal-title" style="color: white" id="modalShowTitle">SHOW DATA</h5>
                <button type="button" class="close" style="color: white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="margin: 20px; max-height: 600px; overflow-y: auto;">
                <div class="modalError"></div>
                <div id="modalShowContent"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEdit" tabindex="-1" role="dialog" aria-labelledby="modalEditLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content" style="border: 2px solid #000; border-radius: 10px;">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalEditLabel">Edit Data</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="modalEditContent">
                <p class="text-center">Loading...</p>
            </div>
        </div>
    </div>
</div>


@stop


@section('js')

<script type='text/javascript'>
	window.addEventListener("pageshow", function (event) {
		if (event.persisted) {
		window.location.reload();
		}
	});
    $(document).ready(function(){
        $('#datatablesX').DataTable({
            processing: true,
            order: [],
            pageLength: 10,
            columnDefs: [
                {
                    targets: [0, 5, 6],
                    orderable: false,
                    searchable: false
                },{
                    targets: [1,2,3,4],
                    orderable: false,
                    searchable: true
                }
            ]
        });

        $(document).on('click', '.modalShow', function (e) {
            e.preventDefault();
            var url = $(this).attr('value');
            debugger;
            $('#modalShowContent').html('');
            $('.modalError').html('');
            $.ajax({
                url: url,
                type: 'GET',
                dataType: 'html',
                success: function (response) {
                    $('#modalShowContent').html(response);
                    $('#modalShow').modal('show');
                },
                error: function (xhr, status, error) {
                    $('.modalError').html('<div class="alert alert-danger">Failed to load data. Please try again later.</div>');
                }
            });
        });

        $(document).on('click', '.modalEdit', function(e) {
            e.preventDefault();
            var url = $(this).data('url');

            $('#modalEdit').modal('show');
            $('#modalEditContent').html('<p class="text-center">Loading...</p>');

            $.get(url)
                .done(function(data) {
                    $('#modalEditContent').html(data);
                })
                .fail(function(jqXHR, textStatus, errorThrown) {
                    let errorMessage = '<div class="alert alert-danger text-center">' +
                                    'Terjadi kesalahan saat memuat form:<br>' +
                                    '<strong>' + jqXHR.status + ' ' + errorThrown + '</strong>' +
                                    '</div>';
                    $('#modalEditContent').html(errorMessage);
                });
        });

    });
</script>
@stop
