@extends('layouts.app')

@section('page-header')
    Monitoring Inventory   
@endsection

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item active" aria-current="page">Monitoring Inventory</li>
    </ol>
@endsection

@section('content')

<div class="row mB-40">
 
    <div class="col-sm-12">
        <div class="bgc-white p-20 bd">
            <div class="row d-flex justify-content-between">
                <div class="col-auto">
                    @php
                        use Illuminate\Support\Facades\Gate;
                    @endphp
                    @if(GATE::allows('inventory'))
                    <a href="#" data-toggle="dropdown" class="dropdown-toggle btn btn-outline border-dark text-uppercase fsz-sm fw-600 mr-2">
                        <i class="fa fa-file-excel-o text-success icon-lg"></i> Download
                    </a>
                    <ul class="dropdown-menu">
                        <li class="dropdown-item">
                            <a href="#" data-toggle="collapse" data-target="#export">
                                Export Data
                            </a>
                        </li>
                        <li class="dropdown-item">
                            <a href="#" data-toggle="collapse" data-target="#stock">
                                Blangko Stock Opname
                            </a>
                        </li>
                    </ul>
                    @endif
                </div>
                <div class="col-auto">
                    <a href="{{ route('logistic.inventory.scan_qr') }}" class="btn btn-info">
                        <i class="ti-camera"> Scan</i>
                    </a>
                </div>
            </div>

            <div class="collapse mB-20" id="export" aria-expanded="false">
                <form class="form-horizontal" action="{{ route('logistic.monitoring.inv.export')}}" method='POST'>
                    {{ csrf_field() }}
                    <div class="bgc-white bd bdrs-3 p-20">
                        <h6>Export Data</h6>
                        <hr>
                        <div class="row">
                            <div class="col-sm-3">
                                <label>Lokasi</label>
                                @if(isAdministrator() || isAdministratorCompany() || isAdmin())
                                    {!! Form::select('location_id', $location, old('location_id'), ['class' => 'form-control select2', 'id'=>'location_id']) !!}
                                @elseif(isAdministratorLocation())
                                    <input type="text" readonly class="form-control" value="{{ getDataByID('locations',Auth::user()->location_id )->name }}">
                                    <input type="hidden" name="location_id" id="location_id" value="{{ Auth::user()->location_id }}">
                                @else
                                    <input type="text" readonly class="form-control" value="{{ getDataByID('locations',Auth::user()->location_id)->name }}">
                                    <input type="hidden" name="location_id" id="location_id" value="{{Auth::user()->location_id}}">
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


            <div class="collapse mB-20" id="stock" aria-expanded="false">
                <form class="form-horizontal" action="{{ route('logistic.inventory.stock_opname')}}" method='POST'>
                    {{ csrf_field() }}
                    <div class="bgc-white bd bdrs-3 p-20">
                        <h6>Download Format Stock Opname</h6>
                        <hr>
                        <div class="row">
                            <div class="col-sm-3">
                                <label>Lokasi</label>
                                @if(isAdministrator() || isAdministratorCompany())
                                    {!! Form::select('location_id', $location, old('location_id'), ['class' => 'form-control select2', 'id'=>'location_id']) !!}
                                @elseif(isAdministratorLocation())
                                    <input type="text" readonly class="form-control" value="{{ getDataByID('locations',Auth::user()->location_id )->name }}">
                                    <input type="hidden" name="location_id" id="location_id" value="{{ Auth::user()->location_id }}">
                                @else
                                    <input type="text" readonly class="form-control" value="{{ getDataByID('locations',Auth::user()->location_id)->name }}">
                                    <input type="hidden" name="location_id" id="location_id" value="{{Auth::user()->location_id}}">
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
                                <button type="submit" class="btn btn-danger mt-4">DOWNLOAD</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <hr>

            <div class="table-responsive mt-4">
                <div class="alert ">
                     <label class='badge badge-success' style="padding: .1em .4em;">&nbsp;</label> Fast Moving  <label class='ml-3 badge badge-danger' style="padding: .1em .4em;">&nbsp;</label> Slow Moving 
                </div>
                <table id="dataTables" class="table table-bordered" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th style="width:80px">Rak</th>
                            <th style="width:120px">Kode</th>
                            <th>Nama Barang</th>
                            <th>PN/SPEC</th>
                            <th>Unit</th>
                            @if(isAdministrator() || isAdministratorCompany() || isLocationAdministrator() ||  isEmployeeAdministrator() )
                                <th>Location</th>
                            @endif
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
    $(document).ready(function() {
        var table = $('#dataTables').DataTable({
            processing: true,
            serverSide: true,
            "pageLength": 50,
            ajax: '{{ route('logistic.monitoring.inv.datatables') }}',
            'columnDefs': [
                {
                    'targets': 7,
                    'className': 'text-right',
                },
                {
                    'targets': 8,
                    'className': 'text-right',
                },
                {
                    'targets': 9,
                    'className': 'text-right',
                },
                {
                    'targets': 10,
                    'className': 'text-right',
                },
                {
                    'targets': 11,
                    'className': 'text-right',
                },
                {
                    'targets': 12,
                    'className': 'text-right',
                }
            ],
            orderFixed: [0, 'desc'],
            columns: [
                {data: 'code_rack', name: 'code_rack'},
                {data: 'productCode', name: 'master_item_products.code'},
                {data: 'productName', name: 'master_item_products.name'},
                {data: 'productPartNumber', name: 'master_item_products.part_number'},
                {data: 'unit', name: 'measures.name', searchable: true},
                @if(isAdministrator() || isAdministratorCompany() || isLocationAdministrator() ||  isEmployeeAdministrator() )
                    {data: 'location', name: 'locations.name'},
                @endif
                {data: 'initial', name: 'initial', searchable: false},
                {data: 'in', name: 'in', searchable: false},
                {data: 'out', name: 'out', searchable: false},
                {data: 'stock_onhand', name: 'stock_onhand', searchable: false},
                {data: 'stock_min', name: 'stock_min', searchable: false},
                {data: 'stock_max', name: 'stock_max', searchable: false},
                {data: 'status', name: 'status', searchable: false},
                {data: 'updated_at', name: 'updated_at', searchable: false},
                {data: 'action', name: 'action', orderable: false, searchable: false},
                {data: 'companyCode', name: 'companies.alias', visible:false},
                
            ],
            @if(isAdministrator() || isAdministratorCompany() || isLocationAdministrator() ||  isEmployeeAdministrator() )
                "order": [[ 14, "DESC" ]],
            @else
                "order": [[ 13, "DESC" ]],
            @endif
           
        });

        $('.monthpicker').datepicker({
            minViewMode: 1,
            format: 'MM',
            maxDate: 1
        });

    });
</script>
@stop
