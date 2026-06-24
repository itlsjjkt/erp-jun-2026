@extends('layouts.app')

@section('page-header')
    Mutasi Barang  
@endsection

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item active" aria-current="page">Mutasi Barang</li>
    </ol>
@endsection

@section('content')

<div class="row mB-40">
 
    <div class="col-sm-12">
        <div class="bgc-white p-20 bd">
        
            <div class="mB-20 mT-10">
                <div class="row">
                    <div class="col-sm-6">
                        <a  href="{{ route('logistic.inventory.mutation.summary_month') }}" class="btn btn-outline border-dark text-uppercase fsz-sm fw-600">
                            <i class="fa fa-database icon-lg"></i> Summary Bulanan
                        </a>
                    </div>
                    <div class="col-sm-6">
                        <a  href="#" data-toggle="collapse" data-target="#filter" class="btn btn-outline border-dark float-right text-uppercase fsz-sm fw-600">
                             FILTER | Export Data
                        </a>
                    </div>
                </div>
            </div>
            <hr>

            <div class="collapse mB-20" id="filter" aria-expanded="false">
                <form class="form-horizontal" id="form" method='GET'>
                    {{ csrf_field() }}
                    <div class="bgc-white bd bdrs-3 p-20">
                        <h6>Filter Data</h6>
                        <hr>
                        <div class="row">
                            @if(isAdministrator() || isAdministratorCompany())
                                <div class="col-sm-3">
                                    <label>Lokasi </label>
                                    {!! Form::select('location_id', $location, old('location_id'), ['class' => 'form-control select2','required ' => 'required', 'id'=>'location_id']) !!}
                                </div>
                            @elseif(isAdministratorLocation())
                                <input type="hidden" name="location_id" value="{{ Auth::user()->location_id }}">
                            @else
                                <input type="hidden" name="location_id" value="{{Auth::user()->location_id}}">
                            @endif
                            <div class="col-sm-3">
                                <label>Kategori</label>
                                {!! Form::select('item_id', $master_item, old('item_id'), ['class' => 'form-control select2 item']) !!}
                            </div>
                            <div class="col-sm-4">
                                <label>Periode </label>
                                <div class="input-group w-100">
                                    <input type="text" name="start_date"  class="form-control datepicker m-r-n-1" placeholder="mm/dd/yyyy" required value="{{ $start_date }}">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text">ke</div>
                                    </div>
                                    <input type="text" name="end_date"  class="form-control datepicker" placeholder="mm/dd/yyyy" required value="{{ $end_date }}">
                                </div>
                            </div>
                        
                            <div class="col-sm-3 mt-4">
                                <button type="submit" class="btn btn-info" id="btn-filter">FILTER</button>
                                <button type="submit" class="btn btn-success" id="btn-export">EXPORT</button>
                                <a  href="{{ route('logistic.inventory.mutation') }}" class="btn text-uppercase fsz-sm fw-600">
                                    RESET
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div> 


            <div class="text-center">
                <h5>PERGERAKAN STOCK HARIAN</h5>
                Dari Tanggal: {{ date('d M Y',strtotime( $start_date)) }} s/d {{ date('d M Y',strtotime( $end_date)) }}   <br>
                {{ $loc_name != '' ? "Lokasi: ". $loc_name : "" }} 
            </div>


            <div class="float-right">
                {{ $item_name != '' ? "Kategori: ". $item_name : "" }} 
            </div>

            <div class="table-responsive mt-4">
                <table id="dataTables" class="table table-bordered" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th style="width:120px">KODE</th>
                            <th>NAMA BARANG /  DESKRIPSI</th>
                            <th>STN</th>
                            <th style="width:80px">SALDO <br> AWAL</th>
                            <th style="width:80px">MASUK</th>
                            <th style="width:80px">KELUAR</th>
                            <th style="width:80px">SALDO <br> AKHIR</th>
                            <th style="width:300px">KETERANGAN</th>
                            <th style="width:120px">RAK</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        ?>
                        @if(count($result_item ) > 0)
                        
                            @foreach ($result_item  as $item)
                                <tr class="bg-grey-custome">
                                    <td>{{ $item['productcode'] }}</td>
                                    <td>{{ $item['productname'] }} <br> {{ $item['productpartnumber'] !=''  ? " PN: ".$item['productpartnumber'] : "" }} {{ $item['productbrand'] !=''  ? " Merk: ".$item['productbrand'] : "" }}</td>
                                    <td>{{ $item['unit'] }}</td>
                                    <td class="text-right">{{ $item['qty_awal'] }}</td>
                                    <td colspan="4"></td>
                                    <td>{{ $item['code_rack'] }}</td>
                                </tr>
                                @php
                                    $sum_in  = 0;
                                    $sum_out = 0;
                                    asort($result[$item['productcode']]);
                                @endphp
                                @foreach ($result[$item['productcode']] as $val)
                                    @php
                                        $sum_in  += $val->qty_in;
                                        $sum_out += $val->qty_out;
                                    @endphp

                                    <tr>
                                        <td></td>
                                        <td>{{ $val->message}}</td>
                                        <td></td>
                                        <td></td>
                                        <td class="text-right">{{ $val->qty_in }}</td>
                                        <td class="text-right">{{ $val->qty_out }}</td>
                                        <td></td>
                                        <td>{{ $val->description }}</td>
                                        <td></td>
                                    </tr>
                                @endforeach
                                @php
                                    $sum_last = $item['qty_awal'] - $sum_out + $sum_in;
                                @endphp
                                <tr>
                                    <td colspan="3" class="text-right font-bold">Sub Total</td>
                                    <td></td>
                                    <td class="text-right">{{ $sum_in }}</td>
                                    <td class="text-right">{{ $sum_out }}</td>
                                    <td class="text-right">{{ $sum_last }}</td>
                                    <td colspan="2"></td>
                                </tr>
                            @endforeach
                        @else
                            <tr><td colspan="11" class="text-center"> Data tidak ditemukan</td></tr>
                        @endif

                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
    <script>
        $(document).ready(function() {
      

            $('#btn-filter').click( function() {
                $('form#form').attr('action',"{{ route('logistic.inventory.mutation') }}");
                if($('form#form').valid()){
                    $('form#form').submit();
                }
            });

            $('#btn-export').click( function(e) {
                e.preventDefault();
                $('form#form').attr('action',"{{ route('logistic.inventory.mutation.export') }}");
                if($('form#form').valid()){
                    $('form#form').submit();
                }
            });

        });

    </script>
@stop