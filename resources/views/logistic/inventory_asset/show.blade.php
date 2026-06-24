@section('content')
    <div>
        <style>
            table.full-width-table {
                border: 1px solid black;
                border-collapse: collapse;
            }
            table.full-width-table th,
            table.full-width-table td {
                border: 1px solid black !important;
                text-align: left;
                padding: 8px;
            }

            table.full-width-table thead th {
                top: 0;
                color: white;
                background-color: #222222;
                z-index: 999;
                border-top: 5px solid black;
                border-bottom: 5px solid black;
            }
            #imagePreviewContainer {
                display: none;
                position: fixed;
                z-index: 9999;
                top: 0;
                left: 0;
                width: 100vw;
                height: 100vh;
                background-color: rgba(0,0,0,0.85);
                justify-content: center;
                align-items: center;
                cursor: zoom-out;
            }

            #imagePreviewContainer img {
                max-width: 90vw;
                max-height: 90vh;
                border-radius: 10px;
                box-shadow: 0 0 10px #000;
            }

            .btn-flat-bottom {
                border-bottom-left-radius: 0 !important;
                border-bottom-right-radius: 0 !important;
            }

        </style>
        <h6 class="text-center" style="text-decoration:underline; font-weight:bold;">{{$data->doc_no}}</h6>
        <table class="table full-width-table">
            <thead>
                <tr>
                    <th style="width:150px;">DATA</th>
                    <th>DETAIL</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="background-color: #eeeeee;">COMPANY</td>
                    <td>{{ $data->companyy ?? ' -' }}</td>
                </tr>
                <tr>
                    <td style="background-color: #eeeeee;">LOKASI ASSET</td>
                    <td>{{ $data->lokasi ?? ' -' }}</td>
                </tr>
                <tr>
                    <td style="background-color: #eeeeee;">DEPARTMENT</td>
                    <td>{{ $data->depttt ?? ' -' }}</td>
                </tr>
                <tr>
                    <td style="background-color: #eeeeee;">KODE ASSET</td>
                    <td>{{ $data->doc_no ?? ' -' }}</td>
                </tr>
                <tr>
                    <td style="background-color: #eeeeee;">NAMA PRODUK</td>
                    <td>{{ $data->produk ?? ' -' }}</td>
                </tr>
                <tr>
                    <td style="background-color: #eeeeee;">PN / SPEC</td>
                    <td>{{ $data->produkpn ?? ' -' }}</td>
                </tr>
                <tr>
                    <td style="background-color: #eeeeee;">BRAND</td>
                    <td>{{ $data->brand ?? ' -' }}</td>
                </tr>
                <tr>
                    <td style="background-color: #eeeeee;">UOM</td>
                    <td>{{ $data->measure ?? ' -' }}</td>
                </tr>
                <!-- <tr>
                    <td style="background-color: #eeeeee;">USER</td>
                    <td>{{ $data->user ?? ' -' }}</td>
                </tr> -->
                <tr>
                    <td style="background-color: #eeeeee;">RELASI</td>
                    <td>{{ $data->type_relation ? strtoupper($data->type_relation) : ' -' }}</td>
                </tr>
                <tr>
                    <td style="background-color: #eeeeee;">RELASI DATA</td>
                    <td>{{ getDataRelationAst($data->type_relation, $data->relation_item_id)->doc_no ?? '-' }}</td>
                </tr>
                <tr>
                    <td style="background-color: #eeeeee;">HARGA ASSET</td>
                    <td>IDR {{ $data->price ? format_number($data->price) : '0.00' }}</td>
                </tr>
                <tr>
                    <td style="background-color: #eeeeee;">CATATAN</td>
                    <td>{{ $data->notes ?? ' -' }}</td>
                </tr>
                <tr>
                    <td style="background-color: #eeeeee;">GAMBAR</td>
                    <td>
                        @if($data->image)
                            <img src="{{ asset('storage' . $data->image) }}"
                                class="img-fluid img-thumbnail"
                                style="cursor: zoom-in; border-radius: 10px; width:100px;"
                                onclick="previewImage(this)">
                        @else
                             -
                        @endif
                    </td>
                </tr>
                <tr>
                    <td style="background-color: #eeeeee;">LAMPIRAN</td>
                    <td style="padding:0;">
                        @if($data->attachment)
                            <a href="{{ route('download_lampiran_inventory_asset', $data->id) }}" target="_blank"
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
                        @php
                            $history = getHistoryInvAsset($data->id);
                            $first = true;
                        @endphp
                        @if(count($history) > 0)
                            <div style="display: flex; justify-content: left; align-items: left;">
                                <a href="#" id="btnToggleHistory" data-toggle="collapse" data-target="#historyyyyy"
                                    title="Show Data"
                                    style="border:1px solid #ccc; border-radius:6px; padding:1px 12px; display:inline-block;">
                                    Cek History
                                </a>
                            </div>
                            <div class="collapse" id="historyyyyy" aria-expanded="false">
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
                                                        echo  "melakukan pembuatan data ".$data->doc_no;
                                                    } elseif ($his->type == 'draft') {
                                                        echo  "melakukan draft data ".$data->doc_no;
                                                    } elseif ($his->type == 'update') {
                                                        echo  "melakukan update data ".$data->doc_no;
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
                        @else
                         -
                        @endif
                    </td>
                </tr>
            </tbody>
        </table>
        <div id="imagePreviewContainer" onclick="closeImagePreview()">
            <img id="previewImage" src="" alt="Preview">
        </div>
    </div>

    <script>
        function previewImage(img) {
            document.getElementById('previewImage').src = img.src;
            document.getElementById('imagePreviewContainer').style.display = 'flex';
        }

        function closeImagePreview() {
            document.getElementById('imagePreviewContainer').style.display = 'none';
        }
         $(document).ready(function () {
            $('#historyyyyy').on('show.bs.collapse', function () {
                $('#btnToggleHistory').addClass('btn-flat-bottom');
            });

            $('#historyyyyy').on('hide.bs.collapse', function () {
                $('#btnToggleHistory').removeClass('btn-flat-bottom');
            });
        });
    </script>
@endsection
