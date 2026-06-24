@section('content')
    <div>
        <style>

        </style>
        <h6 class="text-center" style="text-decoration:underline; font-weight:bold;">DATA ASSET {{$user->name}}</h6>
        <table class="table full-width-table table-bordered table-striped">
            <thead>
                <tr>
                    <th style="width:50px;">No</th>
                    <th>Produk</th>
                    <th>DIA</th>
                    <th>Dokumen Asset</th>
                    <th>QR Code</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $index = 1;
                @endphp
            @foreach ($data as $val)
                <tr>
                    <td>
                        {{$index}}
                        @php
                            $index++;
                        @endphp
                    </td>
                    <td>
                        {{$val->produk}} <br>
                        <small>
                            PN/Spec : {{$val->produkpn??'-'}} <br>
                            Brand : {{$val->brand??'-'}} <br>
                            UOM : {{$val->measure??'-'}}
                        </small>
                    </td>
                    <td>
                        {{$val->parent_doc_no}}
                    </td>
                    <td>
                        {{$val->doc_no}}
                    </td>
                    <td>
                        @php
                            $qrcode = QrCode::size(80)->generate('https://erp.haritashipping.com/inventory_asset/' . Hashids::encode($val->id).'/'.$val->uuid);
                            // $qrcode = QrCode::size(80)->generate('http://192.168.1.77:8000/inventory_asset/' . Hashids::encode($val->id).'/'.$val->uuid);
                        @endphp
                        {!! $qrcode !!}
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <script>

    </script>
@endsection
