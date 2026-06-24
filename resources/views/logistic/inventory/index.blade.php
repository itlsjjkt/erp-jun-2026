@extends('layouts.app')

@section('page-header')
    Inventory 
@endsection

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item active" aria-current="page">Inventory</li>
    </ol>
@endsection

@section('content')

<div class="row mB-40">

    <div class="col-sm-12">
        <div class="bgc-white p-20 bd">

            <div class="mB-20 mT-10">
                <h5 style="font-weight: bold; text-decoration: underline;">
                    @php
                        $lokasiData = getDataByID('locations',request('location_id') ?? $location->keys()->first());
                        $companyData = getDataByID('companies',$lokasiData->company_id);
                        echo $lokasiData->name.' - '.$companyData->code;
                    @endphp
                </h5>
                <div class="btn-group">
                    <a href="{{ route('logistic.inventory.create')}}" class="btn btn-info text-uppercase fsz-sm fw-600">
                        <i class="ti-plus"></i> Tambah
                    </a>
                    <form class="form-horizontal" action="{{ route('logistic.ttb.create')}}" method='GET' id='formTTB'>
                        {{ csrf_field() }}
                        <input type="hidden" name="inv_id">
                        <button type="submit" class="btn btn-danger text-uppercase border ml-2">INPUT TTB</button>
                    </form>
					@if(Gate::allows('adjustment'))
                        <form class="form-horizontal" action="{{ route('logistic.inventory.adjustment_merge') }}" method="GET" id="formAdjMerge">
                            {{ csrf_field() }}
                            <input type="hidden" name="inv_id">
                            <button type="submit" class="btn btn-warning text-uppercase border ml-2">ADJUSTMENT</button>
                        </form>
					@endif
                    @if(Gate::allows('transfer'))
                        <form class="form-horizontal" action="{{ route('logistic.transfer_out.create')}}" method='GET' id='formTransfer'>
                            {{ csrf_field() }}
                            <input type="hidden" name="inv_id">
                            <button type="submit" class="btn btn-primary text-uppercase border ml-2">TRANSFER OUT</button>
                        </form>
                    @endif
                    {{-- <form class="form-horizontal" action="{{ route('logistic.dpm.create')}}" method='GET' id='formDPM'>
                        {{ csrf_field() }}
                        <input type="hidden" name="inv_id">
                        <button type="submit" class="btn btn-success text-uppercase border ml-2">INPUT DPM</button>
                    </form> --}}
                    <a  href="#" data-toggle="dropdown" class="dropdown-toggle btn border ml-2 float-right text-uppercase fsz-sm fw-600 mr-2 ">
                         AKSI LAINNYA
                    </a>
                    <ul class="dropdown-menu">
                        {{-- <li class="dropdown-item">
                            <form class="form-horizontal" action="{{ route('logistic.return_out.create')}}" method='GET' id='formReturn'>
                                {{ csrf_field() }}
                                <input type="hidden" name="inv_id">
                                <button type="submit" class="btn btn-outline font-normal">RETURN OUT</button>
                            </form>
                        </li> --}}
                        <li class="dropdown-item">
                            <form class="form-horizontal" action="{{ route('logistic.conversion.create')}}" method='GET' id='formKonversi'>
                                {{ csrf_field() }}
                                <input type="hidden" name="inv_id">
                                <button type="submit" class="btn btn-outline font-normal">KONVERSI</button>
                            </form>
                        </li>
                        {{-- <li class="dropdown-item">
                            <form class="form-horizontal" action="{{ route('logistic.inventory.print_qr')}}" method='GET' id='formPrint' target="_blank">
                                {{ csrf_field() }}
                                <input type="hidden" name="inv_id">
                                <input type="hidden" value="selected" name="type">
                                <button type="submit" class="btn btn-outline font-normal">Cetak Label Selected</button>
                            </form>
                        </li>

                        <li class="dropdown-item">
                            <a href="#" data-toggle="collapse" data-target="#print_label" class="btn btn-outline text-uppercase fw-600">
                                Cetak Label By Filter
                            </a>
                        </li> --}}
                        <li class="dropdown-item">
                            <a href="#" data-toggle="collapse"  data-target="#stock" class="btn btn-outline text-uppercase fw-600">
                                Blangko Stock Opname
                            </a>
                         </li>
                         {{-- <li class="dropdown-item">
                            <a href="{{ route('logistic.inventory.import')}}" class="btn btn-outline text-uppercase fw-600">
                                Import Data
                            </a>
                         </li> --}}
                    </ul>
                    @if(Gate::allows('print_qr_inventory'))
                        <form id="formPrintUkuran" target="_blank" action="{{ route('logistic.inventory.print_merge') }}"
                            method="GET">
                            @csrf
                            <input type="hidden" name="inv_id">
                            <input type="hidden" name="ukuran" id="ukuranInput" value="36">
                            <div class="dropdown">
                                <button class="btn border float-right dropdown-toggle" type="button" id="dropdownUkuranBtn"
                                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fa fa-qrcode icon-lg"></i> Print QR
                                </button>
                                <div class="dropdown-menu" aria-labelledby="dropdownUkuranBtn">
                                    <a class="dropdown-item ukuran-option" href="#" data-ukuran="24">Print QR 24
                                        mm</a>
                                    <a class="dropdown-item ukuran-option" href="#" data-ukuran="36">Print QR 36
                                        mm</a>
                                </div>
                            </div>
                        </form>
                    @endif
                </div>
                <a  href="#" class="btn btn-outline border-dark fsz-sm fw-600  float-right mr-2 " data-toggle="collapse" data-target="#export">
                    <i class="fa fa-file-excel-o text-success"></i>  Export Data
                </a>
                <a  href="#" class="btn btn-outline border-dark fsz-sm fw-600  float-right mr-2 " data-toggle="collapse" data-target="#export_aging">
                    <i class="fa fa-file-excel-o text-success"></i>  Export Aging
                </a>
                {{-- <a href="#" class="btn btn-outline border-dark fsz-sm fw-600  float-right mr-2 " data-toggle="collapse" data-target="#filter">
                    <i class="ti-search"></i> Pencarian
                </a> --}}
                <a href="{{ route('logistic.inventory.scan_qr')}}" class="btn btn-outline border-dark fsz-sm fw-600  float-right mr-2 ">
                    <i class="ti-camera"></i> Scan QR
                </a>
            </div>
            <hr>

            <div class="collapse mB-20" id="filter" aria-expanded="false">
                <form class="form-horizontal" action="{{ route('logistic.inventory.search')}}" method='GET'>
                    {{ csrf_field() }}
                    <div class="bgc-white bd bdrs-3 p-20">
                        <a class="float-right" data-target="#filter" data-toggle="collapse"><i class="ti-close"></i></a>
                        <h6>Form Pencarian </h6>
                        <hr>
                        <div class="form-group row">
                            <div class="col-sm-3">
                                <label>Lokasi</label>
                                @if(isAdministrator() || isAdministratorCompany() || isAdmin())
                                    {!! Form::select('location_id', $location, old('location_id'), ['class' => 'form-control select2', 'id'=>'locationID','required' => 'required']) !!}
                                @elseif(isAdministratorLocation())
                                    <input type="text" readonly class="form-control" value="{{ getDataByID('locations',Auth::user()->location_id )->name }}">
                                    <input type="hidden" name="location_id" id="locationID" value="{{ Auth::user()->location_id }}">
                                {{-- @else --}}
                                    {{-- <input type="text" readonly class="form-control" value="{{ getDataByID('locations',Auth::user()->location_id)->name }}">
                                    <input type="hidden" name="location_id" id="locationID" value="{{Auth::user()->location_id}}"> --}}
                                @endif
                            </div>
                            <div class="col-sm-3">
                                <label>Kategori Produk</label>
                                {!! Form::select('item_id', $item, old('item_id'), ['class' => 'form-control select2 item']) !!}
                            </div>
                            <div class="col-sm-3">
                                <label>Status</label>
                                <select name="status" class="form-control select2">
                                    <option value="">Silahkan Pilih</option>
                                    <option value="Max Stock">Max Stock</option>
                                    <option value="Over Stock">Over Stock</option>
                                    <option value="Urgent Order">Urgent Order</option>
                                    <option value="Safe Stock">Safe Stock</option>
                                </select>
                            </div>
                            <div class="col-sm-3">
                                <label for="soh">SOH</label> <br>
                                <input value="ADA" type="checkbox" id="stock_onhand" name="stock_onhand" style="transform: scale(2); margin-top:10px;margin-left:10px;">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-12">
                            <div class="float-right">
                                <button type="submit" class="btn btn-danger mt-3 text-uppercase fsz-sm fw-600">Cari</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="collapse mB-20" id="export" aria-expanded="false">
                <form class="form-horizontal" action="{{ route('logistic.inventory.export')}}" method='POST'>
                    {{ csrf_field() }}
                    <div class="bgc-white bd bdrs-3 p-20">
                        <a class="float-right" data-target="#export" data-toggle="collapse"><i class="ti-close"></i></a>
                        <h6>Export Data</h6>
                        <hr>
                        <div class="row">
                            <div class="col-sm-3">
                                <label>Lokasi</label>
                                @if(isAdministrator() || isAdministratorCompany() || isAdmin())
                                    {!! Form::select('location_id', $location, old('location_id'), ['class' => 'form-control select2']) !!}
                                @elseif(isAdministratorLocation())
                                    <input type="text" readonly class="form-control" value="{{ getDataByID('locations',Auth::user()->location_id )->name }}">
                                    <input type="hidden" name="location_id" value="{{ Auth::user()->location_id }}">
                                {{-- @else
                                    <input type="text" readonly class="form-control" value="{{ getDataByID('locations',Auth::user()->location_id)->name }}">
                                    <input type="hidden" name="location_id" value="{{Auth::user()->location_id}}"> --}}
                                @endif
                            </div>
                            <div class="col-sm-3">
                                <label>Kategori Produk</label>
                                {!! Form::select('item_id', $item, old('item_id'), ['class' => 'form-control select2 item']) !!}
                            </div>
                            <div class="col-sm-3">
                                <label>Status</label>
                                <select name="status" class="form-control select2">
                                    <option value="">Silahkan Pilih</option>
                                    <option value="Max Stock">Max Stock</option>
                                    <option value="Over Stock">Over Stock</option>
                                    <option value="Urgent Order">Urgent Order</option>
                                    <option value="Safe Stock">Safe Stock</option>
                                </select>
                            </div>
                            <div class="col-sm-3">
                                <button type="submit" class="btn btn-danger mt-4">Export</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="collapse mB-20" id="export_aging" aria-expanded="false">
                <form class="form-horizontal" action="{{ route('logistic.inventory.export_aging')}}" method='POST'>
                    {{ csrf_field() }}
                    <div class="bgc-white bd bdrs-3 p-20">
                        <a class="float-right" data-target="#export_aging" data-toggle="collapse"><i class="ti-close"></i></a>
                        <h6>Export Aging Inventory</h6>
                        <hr>
                        <div class="row">
                            <div class="col-sm-3">
                                <label>Lokasi</label>
                                @if(isAdministrator() || isAdministratorCompany())
                                    {!! Form::select('location_id', $location, old('location_id'), ['class' => 'form-control select2']) !!}
                                @elseif(isAdministratorLocation())
                                    <input type="text" readonly class="form-control" value="{{ getDataByID('locations',Auth::user()->location_id )->name }}">
                                    <input type="hidden" name="location_id" value="{{ Auth::user()->location_id }}">
                                {{-- @else
                                    <input type="text" readonly class="form-control" value="{{ getDataByID('locations',Auth::user()->location_id)->name }}">
                                    <input type="hidden" name="location_id" value="{{Auth::user()->location_id}}"> --}}
                                @endif
                            </div>
                            <div class="col-sm-3">
                                <label>Kategori Produk</label>
                                {!! Form::select('item_id', $item, old('item_id'), ['class' => 'form-control select2 item']) !!}
                            </div>
                            <div class="col-sm-3">
                                <label>Status</label>
                                <select name="status" class="form-control select2">
                                    <option value="">Silahkan Pilih</option>
                                    <option value="Max Stock">Max Stock</option>
                                    <option value="Over Stock">Over Stock</option>
                                    <option value="Urgent Order">Urgent Order</option>
                                    <option value="Safe Stock">Safe Stock</option>
                                </select>
                            </div>
                            <div class="col-sm-3">
                                <button type="submit" class="btn btn-success mt-4"><i class="fa fa-file-excel-o"></i> Export</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="collapse mB-20" id="print_label" aria-expanded="false">
                <div class="bgc-white bd bdrs-3 p-20">
                    <a class="float-right" data-target="#print_label" data-toggle="collapse"><i class="ti-close"></i></a>
                    <h6>Cetak Label</h6>
                    <hr>
                    <div class="row">
                        <div class="col-8">
                            <form class="form-horizontal" action="{{ route('logistic.inventory.print_qr')}}" method='GET' target="_blank">
                                <input type="hidden" value="filtered" name="type">
                                <div class="row">
                                {{ csrf_field() }}
                                <div class="col-sm-4">
                                    <label>Lokasi</label>
                                    @if(isAdministrator() || isAdministratorCompany())
                                        {!! Form::select('location_id', $location, old('location_id'), ['class' => 'form-control select2']) !!}
                                    @elseif(isAdministratorLocation())
                                        <input type="text" readonly class="form-control" value="{{ getDataByID('locations',Auth::user()->location_id )->name }}">
                                        <input type="hidden" name="location_id" value="{{ Auth::user()->location_id }}">
                                    {{-- @else
                                        <input type="text" readonly class="form-control" value="{{ getDataByID('locations',Auth::user()->location_id)->name }}">
                                        <input type="hidden" name="location_id" value="{{Auth::user()->location_id}}"> --}}
                                    @endif
                                </div>
                                <div class="col-sm-4">
                                    <label>Kategori Produk</label>
                                    {!! Form::select('item_id', $item, old('item_id'), ['class' => 'form-control select2 item']) !!}
                                </div>
                                <div class="col-sm-3">
                                    <button type="submit" class="btn btn-danger mt-4">Cetak Label </button>
                                </div>
                            </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="collapse mB-20" id="stock" aria-expanded="false">
                <form class="form-horizontal" action="{{ route('logistic.inventory.stock_opname')}}" method='POST'>
                    {{ csrf_field() }}
                    <div class="bgc-white bd bdrs-3 p-20">
                        <a class="float-right" data-target="#stock" data-toggle="collapse"><i class="ti-close"></i></a>
                        <h6>Download Format Stock Opname</h6>
                        <hr>
                        <div class="row">
                            <div class="col-sm-3">
                                <label>Lokasi</label>
                                @if(isAdministrator() || isAdministratorCompany())
                                    {!! Form::select('location_id', $location, old('location_id'), ['class' => 'form-control select2']) !!}
                                @elseif(isAdministratorLocation())
                                    <input type="text" readonly class="form-control" value="{{ getDataByID('locations',Auth::user()->location_id )->name }}">
                                    <input type="hidden" name="location_id" value="{{ Auth::user()->location_id }}">
                                {{-- @else
                                    <input type="text" readonly class="form-control" value="{{ getDataByID('locations',Auth::user()->location_id)->name }}">
                                    <input type="hidden" name="location_id" value="{{Auth::user()->location_id}}"> --}}
                                @endif
                            </div>
                            <div class="col-sm-3">
                                <label>Kategori Produk</label>
                                {!! Form::select('item_id', $item, old('item_id'), ['class' => 'form-control select2 item']) !!}
                            </div>
                            <div class="col-sm-3">
                                <button type="submit" class="btn btn-danger mt-4">DOWNLOAD</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div id="showSelected"></div>
            <div class="table-responsive mt-4">
                <div class="alert alert-info">
                    <strong>INFO ! </strong> Checklist Item untuk Input TTB, DPM, Konversi atau Return Out maksimal 25 Item.
                    <br> <label class='badge badge-success' style="padding: .1em .4em;">&nbsp;</label> Fast Moving
                    <label class='ml-3 badge badge-danger' style="padding: .1em .4em;">&nbsp;</label> Slow Moving
                    <label class='ml-3 badge badge-dark' style="padding: .1em .4em;">&nbsp;</label> Dead Stock <br><br>
                    <span>
                        <strong>
                            - KODE PRODUK DENGAN WARNA TEXT BIRU MENUNJUKKAN BAHWA QR CODE UNTUK KODE PRODUK TERSEBUT TELAH DICETAK. <br>
                            - KODE PRODUK DENGAN WARNA TEXT HIJAU MENUNJUKKAN BAHWA QR CODE UNTUK KODE PRODUK TERSEBUT TELAH DICETAK DAN DI TEMPEL.
                        </strong>
                    </span>
                </div>
                <table id="dataTables" class="table table-bordered table-hover" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th></th>
                            <th style="width:80px">Rak</th>
                            <th style="width:120px">Kode</th>
                            <th>Nama Barang</th>
                            <th>PN/SPEC</th>
                            <th>Unit</th>
                            <th>Location</th>
                            <th>Awal</th>
                            <th>In</th>
                            <th>Out</th>
                            <th>SOH</th>
                            <th>Min</th>
                            <th>Max</th>
                            <th>Status</th>
                            <th>Updated</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
    <script>
        function printExternal(url) {
            var printWindow = window.open(url, 'Print', 'left=200, top=200, width=950, height=800, toolbar=0, resizable=0');
            printWindow.addEventListener('load', function() {
                printWindow.print();
            }, true);
        }
    $(document).ready(function() {

        var rows_selected = [];
        var locationId = '{{ request('location_id') ?? $location->keys()->first() }}';
        var table = $('#dataTables').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 50,
            ajax: {
                url: '{{ route('logistic.inventory.datatables') }}',
                data: function(d) {
                    d.company_id = '{{ request('company_id') }}';
                    d.location_id = '{{ request('location_id') }}';
                }
            },
            columnDefs: [
                {
                    targets: 0,
                    searchable: false,
                    orderable: false,
                    width: '1%',
                    className: 'text-center',
                    render: function (data, type, full, meta){
                        return '<input type="checkbox" class="magic-checkbox inv_id" value="'+ data +'" ><label></label>';
                    }
                },
                { targets: 7, className: 'text-right' },
                { targets: 8, className: 'text-right' },
                { targets: 9, className: 'text-right' },
                { targets: 10, className: 'text-right' },
                { targets: 11, className: 'text-right' },
                { targets: 12, className: 'text-right' }
            ],
            orderFixed: [0, 'desc'],
            columns: [
                {data: 'id'},
                {data: 'code_rack', name: 'code_rack'},
                {data: 'productCode', name: 'master_item_products.code'},
                {data: 'productName', name: 'master_item_products.name'},
                {data: 'productPartNumber', name: 'master_item_products.part_number'},
                {data: 'unit', name: 'measures.name', searchable: true},
                {data: 'location', name: 'locations.name', searchable: true},
                {data: 'initial', name: 'initial', searchable: false},
                {data: 'in', name: 'in', searchable: false},
                {data: 'out', name: 'out', searchable: false},
                {data: 'stock_onhand', name: 'stock_onhand', searchable: false},
                {data: 'stock_min', name: 'stock_min', searchable: false},
                {data: 'stock_max', name: 'stock_max', searchable: false},
                {data: 'status', name: 'status', searchable: false},
                {data: 'updated_at', name: 'updated_at', searchable: false},
                {data: 'action', name: 'action', orderable: false, searchable: false},
                {data: 'productBrand', name: 'master_item_brands.name', visible: false},
                {data: 'aliasss', name: 'locations.alias', visible: false},
            ],
            order: [[ 14, "DESC" ]],
            rowCallback: function(row, data, dataIndex){
                var rowId = data['id'];
                if($.inArray(rowId, rows_selected) !== -1){
                    $(row).find('input[type="checkbox"]').prop('checked', true);
                    $(row).addClass('selected');
                }
            }
        });

        // Jika location_id berubah, reload datatable
        $('#location_id').on('change', function() {
            table.ajax.reload();
        });


        $('#dataTables tbody').on('click', 'input[type="checkbox"]', function(e){
            var $row = $(this).closest('tr');
            var data = table.row($row).data();
            var rowId = data['id'];
            var index = $.inArray(rowId, rows_selected);
            if(this.checked && index === -1){
                rows_selected.push(rowId);
            } else if (!this.checked && index !== -1){
                rows_selected.splice(index, 1);
            }
            if(this.checked){
                $row.addClass('selected');
            } else {
                $row.removeClass('selected');
            }
            $('#showSelected').text('');
            $('#showSelected').text('Data Selected:'+ rows_selected.length);
            e.stopPropagation();
        });

        $('#dataTables').on('click', 'tbody td, thead th:first-child', function(e){
            $(this).parent().find('input[type="checkbox"]').trigger('click');
        });

        $('#formTTB').on('submit', function(e){
            var form = this;
            var arr_id = [];

            $.each(rows_selected, function(index, rowId){
                arr_id.push(rowId);
            });
            if(arr_id.length === 0){
                Swal.fire(
                    'Informasi',
                    'Minimal Checklist 1 Item Inventory',
                    'warning'
                );
                return false;
            }else{
                if (arr_id.length > 26){
                    Swal.fire(
                        'Informasi',
                        'Maksimal Checklist 25 Item Inventory',
                        'warning'
                    );
                    return false;
                }else{
                    var id = arr_id;
                    $('input[name="inv_id"]').val(id);
                }
            }
        });


        $('#formAdjMerge').on('submit', function(e){
            var form = this;
            var arr_id = [];

            $.each(rows_selected, function(index, rowId){
                arr_id.push(rowId);
            });
            if(arr_id.length === 0){
                Swal.fire(
                    'Informasi',
                    'Minimal Checklist 1 Item Inventory',
                    'warning'
                );
                return false;
            }else{
                if (arr_id.length > 26){
                    Swal.fire(
                        'Informasi',
                        'Maksimal Checklist 25 Item Inventory',
                        'warning'
                    );
                    return false;
                }else{
                    var id = arr_id;
                    debugger;
                    $('input[name="inv_id"]').val(id);
                }
            }
        });

        $('#formDPM').on('submit', function(e){
            var form = this;
            var arr_id = [];

            $.each(rows_selected, function(index, rowId){
                arr_id.push(rowId);
            });
            if(arr_id.length === 0){
                Swal.fire(
                    'Informasi',
                    'Minimal Checklist 1 Item Inventory',
                    'warning'
                );
                return false;
            }else{
                if (arr_id.length > 26){
                    Swal.fire(
                        'Informasi',
                        'Maksimal Checklist 25 Item Inventory',
                        'warning'
                    );
                    return false;
                }else{
                    var id = arr_id;
                    $('input[name="inv_id"]').val(id);
                }
            }
        });

        $('#formKonversi').on('submit', function(e){
            var form = this;
            var arr_id = [];

            $.each(rows_selected, function(index, rowId){
                arr_id.push(rowId);
            });
            if(arr_id.length === 0){
                Swal.fire(
                    'Informasi',
                    'Minimal Checklist 1 Item Inventory',
                    'warning'
                );
                return false;
            }else{
                if (arr_id.length > 10 ){
                    Swal.fire(
                        'Informasi',
                        'Maksimal Checklist 10 Item Inventory',
                        'warning'
                    );
                    return false;
                }else{
                    var id = arr_id;
                    $('input[name="inv_id"]').val(id);
                }
            }
        });

        $('#formReturn').on('submit', function(e){
            var form = this;
            var arr_id = [];

            $.each(rows_selected, function(index, rowId){
                arr_id.push(rowId);
            });
            if(arr_id.length === 0){
                Swal.fire(
                    'Informasi',
                    'Minimal Checklist 1 Item Inventory',
                    'warning'
                );
                return false;
            }else{
                if (arr_id.length > 25 ){
                    Swal.fire(
                        'Informasi',
                        'Maksimal Checklist 10 Item Inventory',
                        'warning'
                    );
                    return false;
                }else{
                    var id = arr_id;
                    $('input[name="inv_id"]').val(id);
                }
            }
        });

        $('#formPrint').on('submit', function(e){
            var form = this;
            var arr_id = [];

            $.each(rows_selected, function(index, rowId){
                arr_id.push(rowId);
            });
            if(arr_id.length === 0){
                Swal.fire(
                    'Informasi',
                    'Minimal Checklist 1 Item Inventory',
                    'warning'
                );
                return false;
            }else{
                var id = arr_id;
                $('input[name="inv_id"]').val(id);
            }
        });

        $('#formTransfer').on('submit', function(e){
            var form = this;
            var arr_id = [];

            $.each(rows_selected, function(index, rowId){
                arr_id.push(rowId);
            });
            if(arr_id.length === 0){
                Swal.fire(
                    'Informasi',
                    'Minimal Checklist 1 Item Inventory',
                    'warning'
                );
                return false;
            }else{
                if (arr_id.length > 10 ){
                    Swal.fire(
                        'Informasi',
                        'Maksimal Checklist 10 Item Inventory',
                        'warning'
                    );
                    return false;
                }else{
                    var id = arr_id;
                    $('input[name="inv_id"]').val(id);
                }
            }
        });

        var $item = $('.item');
        var $category = $(".category");

        $('.monthpicker').datepicker({
            minViewMode: 1,
            format: 'MM',
            maxDate: 1
        });


        $('#formProses').on('submit', function(e){

            Swal.fire({
                title: 'Terms and conditions',
                input: 'checkbox',
                inputValue: 1,
                inputPlaceholder:
                'Apakah anda yakin melakukan proses Perpindahan Saldo Bulanan',
                confirmButtonText:
                'Continue<i class="fa fa-arrow-right"></i>',
                inputValidator: (result) => {
                return !result && 'You need to agree with T&C'
                }
            })

        });

        $('.ukuran-option').on('click', function(e) {
            // e.preventDefault();

            if (rows_selected.length === 0) {
                Swal.fire(
                    'Informasi',
                    'Minimal Checklist 1 QR',
                    'warning'
                );
                return false;
            }

            var ukuran = $(this).data('ukuran');
            $('#ukuranInput').val(ukuran);
            $('input[name="inv_id"]').val(rows_selected.join(','));
            $('#formPrintUkuran').submit();
        });

    });
</script>
@stop
