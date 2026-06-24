@extends('layouts.app')

@php 
    use Illuminate\Support\Facades\Gate;
@endphp

@section('page-header')
    @if(GATE::allows('inventory_monitoring'))
        Monitoring Inventory
    @else
        Inventory
    @endif
@endsection

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        @if(GATE::allows('inventory_monitoring'))
            <li class="breadcrumb-item"><a href="{{ route('logistic.monitoring.inv') }}">Monitoring Inventory</a></li>
        @else
            <li class="breadcrumb-item"><a href="{{ route('logistic.inventory.index') }}">Inventory</a></li>
        @endif
        <li class="breadcrumb-item active" aria-current="page">Scan QR</li>
    </ol>
@endsection

@section('content')
    <style>
        #reader {
            width: 300px;
            margin: 20px auto;
        }

        #product-info {
            margin: 20px auto;
            width: 90%;
            max-width: 600px;
            padding: 10px;
            border: 1px solid #ccc;
            font-family: Arial, sans-serif;
        }

        .error {
            color: red;
        }

        img.product-image {
            max-width: 100px;
            max-height: 100px;
            margin-top: 10px;
        }

        .warning {
            font-weight: bold;
            color: rgb(255, 242, 0);
        }

    </style>
    <div class="row mB-40">
        <div class="col-sm-12">
            <div class="bgc-white p-20 bd">
                <div class="row mb-3 justify-content-end">
                    <div class="col-sm-6 ">
                        <a href="{{ route('logistic.inventory.index') }}" class="nav-link"> <i class="ti-arrow-left"></i> Kembali  </a>
                    </div>
                    <div class="col-sm-6">
                        <div style="display: flex; justify-content: flex-end; width: 100%; margin-bottom:10px;">
                            <a href="{{ route('logistic.inventory.scan_qr') }}" class="btn btn-info">
                                <i class="ti-reload"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <!-- Scan -->
                    <div class="col-md-6 mb-3">
                        <div class="card shadow-sm">
                            <div class="card-header">
                                <h6 class="card-title"> <b>Scan Kode QR</b> </h6>
                            </div>
                            <div class="card-body">
                                <div id="reader" style="width: 100%; max-width: 500px;"></div>
                            </div>
                        </div>
                    </div>
                    <!-- Result -->
                    <div class="col-md-6 mb-3">
                        <div class="card shadow-sm">
                            <form action="{{ route('logistic.inventory.store_status_label_applied') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="card-header">
                                    <h6 class="card-title"> <b>Informasi Produk</b> </h6>
                                </div>
                                <div class="card-body">
                                    <div class="card-text" id="product-info" style="display: none;"></div>
                                        <div class="row">
                                            <div class="col">
                                                <div class="mb-3">
                                                    <label for="uuid" class="form-label">UUID</label>
                                                    <input type="text" class="form-control" name="uuid" id="uuid" required autofocus
                                                        readonly>
                                                </div>
                                            </div>
                                            <div class="col">
                                                <div class="mb-3">
                                                    <label for="product_code" class="form-label">Kode Produk</label>
                                                    <input type="text" class="form-control" name="product_code" id="product_code" readonly>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="product_name" class="form-label">Nama Produk</label>
                                            <input type="text" class="form-control" name="product_name" id="product_name" required
                                                readonly>
                                        </div>

                                        <div class="row">
                                            <div class="col">
                                                <div class="mb-3">
                                                    <label for="part_number" class="form-label">PN/Spec</label>
                                                    <input type="text" class="form-control" name="part_number" id="part_number" readonly>
                                                </div>
                                            </div>
                                            <div class="col">
                                                <div class="mb-3">
                                                    <label for="code_rack" class="form-label">Kode Rak</label>
                                                    <input type="text" class="form-control" name="code_rack" id="code_rack" readonly>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col">
                                                <div class="mb-3">
                                                    <label for="stock_onhand" class="form-label">Stock On Hand</label>
                                                    <input type="number" class="form-control" name="stock_onhand" id="stock_onhand"
                                                        required readonly>
                                                </div>
                                            </div>
                                            <div class="col">
                                                <div class="mb-3">
                                                    <label for="measure" class="form-label">Satuan</label>
                                                    <input type="text" class="form-control" name="measure" id="measure" readonly>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="location_name" class="form-label">Lokasi</label>
                                            <input type="text" class="form-control" name="location_name" id="location_name" required
                                                readonly>
                                        </div>

                                        <div class="mb-3">
                                            <label for="status_label_applied" class="form-label">Status Pengaplikasian QR</label>
                                            <div class="form-control" readonly id="status_label_applied"></div>
                                        </div>
                                        <div class="row">
                                            <div class="col text-right">
                                                <button type="submit" id="saveButton" class="btn btn-success" style="display: none;">Set Sudah Di Tempel</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <hr>
                        <div class="card-header">
                            <h6 class="card-title"> <b>Kartu Stock</b> </h6>
                        </div>
                        <div id="history-info"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')

    <!-- Html5-QRCode -->
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    {{-- Audio Scan --}}
    <audio id="beep" preload="auto">
        <source src="{{ asset('sound/beep.mp3') }}" type="audio/mpeg">
    </audio>

    <!-- Scanner Logic -->
    <script>
        const canPrintQR = @json($canPrintQR);
        const productInfoEl = document.getElementById('product-info');
        let html5QrcodeScanner;
            // Menampilkan Data Hasil Scan
            function displayProduct(data) {
                // Menampilkan data produk
                productInfoEl.innerHTML = `
                    <p><strong>Location :</strong> ${data.location_name ? `${data.location_name} - ${data.company_alias}` : '-'}</p>
                    <p><strong>UUID :</strong> ${data.uuid}</p>
                    <p><strong>Product Code :</strong> ${data.product_code ?? '-'}</p>
                    <p><strong>Product Name :</strong> ${data.product_name ?? '-'}</p>
                    <p><strong>Part Number :</strong> ${data.part_number ?? '-'}</p>
                    <p><strong>Rack Existing :</strong> ${data.code_rack ?? '-'}</p>
                    <p><strong>Stok Onhand :</strong> ${data.stock_onhand}</p>
                    <p><strong>Measure :</strong> ${data.measure_name ?? '-'}</p>
                    <p><strong>Pengaplikasian Label :</strong> ${data.status_label_applied ?? '-'}</p>
                `;

                // Mengisi form produk
                document.getElementById('location_name').value = data.location_name ? data.location_name + ' - ' + data.company_alias : '-';
                document.getElementById('uuid').value = data.uuid ?? '-';
                document.getElementById('product_code').value = data.product_code ?? '-';
                document.getElementById('product_name').value = data.product_name ?? '-';
                document.getElementById('part_number').value = data.product_part_number ?? '-';
                document.getElementById('code_rack').value = data.code_rack ?? '-';
                document.getElementById('stock_onhand').value = data.stock_onhand ?? '-';
                document.getElementById('measure').value = data.measure_name ?? '-';
                document.getElementById('status_label_applied').innerHTML = 
                    data.status_label_applied === 1 ? 
                    '<strong class="text-success">Sudah Ditempel</strong>' : 
                    (data.status_label_applied === 0 ? 
                    '<strong class="text-danger">Belum Ditempel</strong>' : 
                    'Status Tidak Diketahui');

                // Menampilkan riwayat inventaris
                const historyTableContent = data.history_data.map(history => {
                    const createdDate = new Date(history.created);
                    const formattedDate = new Intl.DateTimeFormat('en-GB', {
                        day: '2-digit',
                        month: 'short',
                        year: 'numeric'
                    }).format(createdDate);

                    return `
                        <tr>
                            <td>${formattedDate}</td>
                            <td>${history.doc_no ?? '-'}</td>
                            <td>${history.stock_awal ?? '0'}</td>
                            <td><span class='${Number(history.qty_in) > 0 ? 'text-success' : ''}'>${history.qty_in ?? '0'}</span></td>
                            <td><span class='${Number(history.qty_out) > 0 ? 'text-danger' : ''}'>${history.qty_out ?? '0'}</span></td>
                            <td>${Number(history.stock_awal) + Number(history.qty_in) - Number(history.qty_out)}</td>
                            <td>${history.description ?? '-'}</td>
                        </tr>
                    `;
                }).join('');

                // Menambahkan riwayat ke halaman
                document.getElementById('history-info').innerHTML = `
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Doc No</th>
                                <th>Stock Awal</th>
                                <th>Qty In</th>
                                <th>Qty Out</th>
                                <th>Stock</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${historyTableContent}
                        </tbody>
                    </table>
                `;
                const saveButton = document.getElementById('saveButton');
                if (data.uuid && data.status_label_applied === 0 && canPrintQR) {
                    saveButton.style.display = 'inline-block';
                } else {
                    saveButton.style.display = 'none';
                }
            }




            // Display Error
            function displayError(message) {
                productInfoEl.innerHTML = `<p class="text-danger fw-bold">${message}</p>`;
            }

            // CEK DATA
            function fetchProductByUuid(uuid) {
                const baseUrl = '{{ route("logistic.inventory.fetch_produk_by_uuid", ":uuid") }}'.replace(":uuid", uuid);
                fetch(baseUrl)
                .then(response => {
                    if (!response.ok) {
                    throw new Error('Data Tidak Ditemukan (404)');
                    }
                    return response.json();
                })
                .then(data => {
                    displayProduct(data);
                })
                .catch(err => {
                    displayError('Terjadi Kesalahan Mengambil Data : ' + err.message);
                });
            }

            // SUKSES
            function onScanSuccess(decodedText, decodedResult) {
                document.getElementById('product-info').style.display = 'none';
                document.getElementById('beep').play();
                html5QrcodeScanner.clear().then(() => {
                    console.log("Scanner Stopped");
                }).catch(err => {
                    console.error("Gagal Menghentikan Scanner : ", err);
                });
                productInfoEl.innerHTML = `<p>Mengambil Data Produk UUID : <strong>${decodedText}</strong></p>`;
                fetchProductByUuid(decodedText);
                document.getElementById('uuid').value = decodedText;
            }

            function onScanFailure(error) {
                // Bisa Diabaikan Agar Tidak Terlalu Banyak Log
                // onsole.warn(`Scan error: ${error}`);
            }

            html5QrcodeScanner = new Html5QrcodeScanner(
                "reader", { fps: 10, qrbox: 250 }, false);
            html5QrcodeScanner.render(onScanSuccess, onScanFailure);
    </script>
@stop
