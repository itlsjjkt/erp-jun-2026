
@section('content')

<div class="container">
    <div class="row">
        <div class="col-lg-12">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th rowspan="2" style="width:50px">No</th>
                        <th rowspan="2" style="width:150px">PN/SPEC</th>
                        <th rowspan="2" style="width:300px">Nama Barang</th>
                        <th rowspan="2">Tipe</th>
                        <th rowspan="2">Brand</th>
                        <th rowspan="2" style="width:300px">Spesifikasi</th>
                        <th colspan="2" class="text-center">Jumlah </th>
                        <th rowspan="2" class="text-center">Satuan</th>
                        <th rowspan="2" class="text-center">Harga</th>
                    </tr>
                    <tr>
                        <th style="width:150px" class="text-center">Dipesan</th>
                        <th style="width:150px" class="text-center">Diterima</th>
                    </tr>
                </thead>
                <tbody>
                        @php
                            $no = 1;
                        @endphp
                        @foreach ($lpb_items as $item)
                            <tr>
                                <td>{{ $no }}</td>
                                <td>{{ $item->productPartNumber }}</td>
                                <td style="width:300px">{{ $item->productCode }} - {{ $item->product }}</td>
                                <td>{{ $item->productTipe }}</td>
                                <td>{{ $item->productBrand }}</td>
                                <td>{{ $item->specification }}</td>
                                <td>{{ $item->qtyPO }}</td>
                                <td>{{ $item->qty }}</td>
                                <td>{{ $item->qty_retur }}</td>
                                <td>{{ $item->measure }}</td>
                                <td>{{ number_format($item->price ,2,".",',') }}</td>
                            </tr>
                        @php
                            $no++;
                        @endphp
                        @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection