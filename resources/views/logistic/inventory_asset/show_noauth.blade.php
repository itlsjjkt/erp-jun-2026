@extends('layouts.app_noauth')

@section('content')
<style>
    /* Wrapper untuk scroll tabel dan sticky thead */
    .table-wrapper {
        width: 90vw;
        overflow-x: auto;
        margin-left: 35px;
        max-height: 80vh;           /* scroll hanya area tabel */
        overflow-y: auto;
        margin-top: 80px;
        padding: 0;
    }

    /* Tabel full lebar */
    table.full-width-table {
        width: 100%;
        border: 1px solid black;
        border-collapse: collapse;
    }

    /* Sel tabel */
    table.full-width-table th,
    table.full-width-table td {
        border: 1px solid black !important;
        text-align: left;
        padding: 8px;
    }

    table.full-width-table thead th {
        position: sticky;
        top: 0;
        color: white;
        background-color: #222222;
        z-index: 999;
        border-top: 5px solid black;
        border-bottom: 5px solid black;
    }

    /* Fixed untuk judul H4 */
    .fixed-header {
        font-weight: bold;
        text-decoration: underline;
        color: black;
        background-color: white;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        z-index: 999;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
        text-align: center;
        z-index:1000;
    }
</style>

<?php setlocale(LC_TIME, 'id_ID.utf8'); $index = 0; ?>

<body>
    <!-- Fixed Header -->
    <div class="fixed-header">
        <a class="logo" href="#" style="display: inline-block; max-width: 220px; max-height: 40px; overflow: hidden;">
            <img src="/images/{{ config('app.logo') }}" alt="" style="max-width: 100%; max-height: 100%; width: auto; height: auto; object-fit: contain; border-radius: 10px;">
        </a>
    </div>

    <!-- Tabel dengan wrapper scroll -->
    <div class="table-wrapper text-center">
        <table class="table full-width-table">
            <thead>
                <tr>
                    <th >DATA</th>
                    <th>DETAIL</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="background-color: #eeeeee;">COMPANY</td>
                    <td>{{ $result->companyy ?? ' -' }}</td>
                </tr>
                <tr>
                    <td style="background-color: #eeeeee;">LOKASI ASSET</td>
                    <td>{{ $result->lokasi ?? ' -' }}</td>
                </tr>
                <tr>
                    <td style="background-color: #eeeeee;">DEPARTMENT</td>
                    <td>{{ $result->depttt ?? ' -' }}</td>
                </tr>
                <tr>
                    <td style="background-color: #eeeeee;">KODE ASSET</td>
                    <td>{{ $result->doc_no ?? ' -' }}</td>
                </tr>
                <tr>
                    <td style="background-color: #eeeeee;">NAMA PRODUK</td>
                    <td>{{ $result->produk ?? ' -' }}</td>
                </tr>
                <tr>
                    <td style="background-color: #eeeeee;">PN / SPEC</td>
                    <td>{{ $result->produkpn ?? ' -' }}</td>
                </tr>
                <tr>
                    <td style="background-color: #eeeeee;">BRAND</td>
                    <td>{{ $result->brand ?? ' -' }}</td>
                </tr>
                <tr>
                    <td style="background-color: #eeeeee;">UOM</td>
                    <td>{{ $result->measure ?? ' -' }}</td>
                </tr>
                {{-- <tr>
                    <td style="background-color: #eeeeee;">USER</td>
                    <td>{{ $result->user ?? ' -' }}</td>
                </tr> --}}
                <tr>
                    <td style="background-color: #eeeeee;">RELASI</td>
                    <td>{{ $result->type_relation ? strtoupper($result->type_relation) : ' -' }}</td>
                </tr>
                <tr>
                    <td style="background-color: #eeeeee;">RELASI DATA</td>
                    <td>{{ getDataRelationAst($result->type_relation, $result->relation_item_id)->doc_no ?? '-' }}</td>
                </tr>
                <tr>
                    <td style="background-color: #eeeeee;">HARGA ASSET</td>
                    <td>
                        @if(Auth::check() && Auth::user()->id) 
                            IDR {{ $result->price ? format_number($result->price) : '0.00' }}
                        @else
                            IDR {{ $result->price ? str_repeat('X', strlen($result->price)) : 'X.XX' }}
                        @endif
                    </td>
                </tr>
                <tr>
                    <td style="background-color: #eeeeee;">CATATAN</td>
                    <td>{{ $result->notes ?? ' -' }}</td>
                </tr>
                <tr>
                    <td style="background-color: #eeeeee;">GAMBAR</td>
                    <td>
                        @if ($result->image)
                            <img src="{{ asset('storage' . $result->image) }}"class="img-fluid img-thumbnail" style="cursor: pointer;border-radius: 10px; width:100px;"data-toggle="modal"data-target="#modalGambar"onclick="showImageModal(this.src)">
                        @else
                             -
                        @endif
                    </td>
                </tr>
                <tr>
                    <td style="background-color: #eeeeee;">LAMPIRAN</td>
                    <td style="padding:0;">
                        @if($result->attachment)
                            <a href="{{ route('download_lampiran_inventory_asset', $result->id) }}" target="_blank"
                            style="border:1px solid #ccc; border-radius:6px; padding:1px 12px; display:inline-block;">
                                Download Lampiran PDF
                            </a>
                        @else
                         -
                        @endif
                    </td>
                </tr>
                <tr>
                    <td style="background-color: #eeeeee;">HISTORY DATA</td>
                    <td style="padding:0;">
                        <a href="#" class=".3modalHistory" title="Show Data" data-toggle="modal" data-target="#modalHistory" style="border:1px solid #ccc; border-radius:6px; padding:1px 12px; display:inline-block;">Cek History</a>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</body>
<div class="modal fade" id="modalGambar" tabindex="-1" role="dialog" aria-labelledby="modalGambarLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
    <div class="modal-content bg-white">
      <div class="modal-header">
        <h5 class="modal-title" id="modalGambarLabel">Pratinjau Gambar</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body text-center">
        <img src="" id="modalImageTag" class="img-fluid" style="max-height: 90vh; object-fit: contain;">
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="modalHistory" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-l" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalMdTitle">History</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="max-height: 400px; overflow-y: auto;">
                @php
                    $history = getHistoryInvAsset($result->id);
                    $first = true;
                @endphp
                <div style="border-left: 2px solid #17a2b8;">
                    @foreach ($history as $his)
                        <div @if($first) style="background-color:#17a2b8; border-top-right-radius: 15px; border-bottom-right-radius: 15px;" @endif>
                            @if(!$first)
                            <div style="width: 15px; height: 15px; background-color: #17a2b8; border-radius: 50%; position: relative; left: -8px; top:20px;"></div>
                            @endif
                            <div style="margin-left: 15px;" @if($first) class="text-light" @endif>
                                {!! \Carbon\Carbon::parse($his->created_at)->format('d/M/Y H:i') . ' <strong>' . getUserByID($his->created_by) . '</strong> <br>'!!}
                                <?php
                                    if ($his->type == 'create') {
                                        echo  "melakukan pembuatan data ".$result->doc_no;
                                    } elseif ($his->type == 'update') {
                                        echo  "melakukan update data ".$result->doc_no;
                                    } elseif ($his->type == 'draft') {
                                        echo  "melakukan draft data ".$result->doc_no;
                                    }else {
                                        echo  "unknow type ";
                                    }
                                ?>
                            </div>
                            <br>
                        </div>
                        @php
                            $first = false;
                        @endphp
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

@stop

@section('js')
<script>
    function showImageModal(src) {
        document.getElementById('modalImageTag').src = src;
    }
    function showPdfModal(pdfUrl) {
        document.getElementById('modalPdfFrame').src = pdfUrl;
    }
</script>
@stop
