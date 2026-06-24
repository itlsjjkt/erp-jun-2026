@extends('layouts.app')

@section('page-header')
    Aset
@stop

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('accounting.asset.index') }}">Aset</a></li>
        <li class="breadcrumb-item active" aria-current="page">Show</li>
    </ol>
@endsection


@section('content')
<div class="row mB-40">

	<div class="col-sm-12">
        <div class="bgc-white p-30 bd">
            <h6><a class="float-left" href="{{ route('accounting.asset.index') }}"><i class="ti-arrow-left mR-10"></i></a> {{ trans('Back') }}</h6>
            <hr class="mB-30">

            
            <div class="row mt-5">
                <div class="col-lg-6">
                    <div class="row">
                        <label  class="col-sm-3">Nama</label>
                        <div class="col-sm-8">
                           {{$item->product->code}} - {{$item->product->name}} [{{$item->product->part_number}}]   
                        </div>
                    </div>

                    <div class="row">
                        <label  class="col-sm-3">Tanggal Pembukuan</label>
                        <div class="col-sm-8">
                            {{ date('d/m/Y',strtotime( $item->date_input)) }}
                        </div>
                    </div>
            
                    <div class="row">
                        <label  class="col-sm-3">Kategori</label>
                        <div class="col-sm-8">
                            {{$item->category->name}}
                        </div>
                    </div>
            
                    <div class="row">
                        <label class="col-sm-3">Referensi</label>
                        <div class="col-sm-8">
                            {{$item->reference}}
                        </div>
                    </div>

                    <div class="row">
                        <label class="col-sm-3">Pengguna</label>
                        <div class="col-sm-8">
                            {{$item->used}}
                        </div>
                    </div>
            
                </div>
            
                <div class="col-lg-6">
            
                    <div class="row">
                        <label class="col-sm-3">Mata Uang</label>
                        <div class="col-sm-8">
                            {{$item->currency}}
                        </div>
                    </div>
            
                    <div class="row">
                        <label class="col-sm-3">Nilai Kotor</label>
                        <div class="col-sm-8">
                            {{format_number($item->gross_value)}}
                        </div>
                    </div>
            
                    <div class="row">
                        <label class="col-sm-3">Nilai Tetap</label>
                        <div class="col-sm-8">
                            {{format_number($item->salvage_value)}}
                        </div>
                    </div>
            
                    <div class="row">
                        <label class="col-sm-3">Catatan</label>
                        <div class="col-sm-8">
                            {{$item->description}}
                        </div>
                    </div>
            
                </div>
            
            
            </div>
            
            <ul class="nav nav-tabs mt-4" id="myTab" role="tablist">
                <li class="nav-item" role="presentation">
                  <button class="nav-link active" id="home-tab" data-toggle="tab" data-target="#home" type="button" role="tab" aria-controls="home" aria-selected="true">Depresiasi</button>
                </li>
                <li class="nav-item" role="presentation">
                  <button class="nav-link" id="profile-tab" data-toggle="tab" data-target="#profile" type="button" role="tab" aria-controls="profile" aria-selected="false">Informasi Depresiasi</button>
                </li>
              </ul>
              <div class="tab-content" id="myTabContent">
                <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">
                    <div class="table-responsive mt-3">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Tanggal Depresiasi</th>
                                    <th>Depresiasi</th>
                                    <th>Kumulatif Depresiasi</th>
                                    <th>Sisa</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(count($item->depreciation))
                                    @php
                                        $cumulative = 0;
                                        $no = 0;
                                    @endphp
                                    @foreach ($item->depreciation as $val)
                                        @php
                                            $cumulative += $val->depreciation;
                                            $residual = $item->gross_value - $cumulative;
                                            $no++;
                                        @endphp
                                        <tr>
                                            <td>{{ $no }}</td>
                                            <td>{{ date('d/m/Y',strtotime( $val->date)) }}</td>
                                            <td>{{ format_number($val->depreciation) }}</td>
                                            <td>{{ format_number($cumulative) }}</td>
                                            <td>{{ format_number($residual) }}</td>
                                        </tr> 
                                    @endforeach
                                @else
                                    <tr><td colspan="5" class="text-center">Tidak ditemukan data</td></tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
              
                <div class="tab-pane fade" id="profile" role="tabpanel" aria-labelledby="profile-tab">

                    <div class="row mt-4">
                        <div class="col-lg-6">
                            <div class="row">
                                <label class="col-sm-3"> Metode Waktu</label>
                                <div class="col-sm-9 checkbox-custom">
                                    {{ ($item->time_method == 'number') ? "Jumlah Entri" : "Tanggal" }}
                                </div>
                            </div>
                            
                            <div class="row">
                                <label class="col-sm-3"> {{ ($item->time_method == 'number') ? "Jumlah Entri" : "Tanggal berakhir" }}</label>
                                <div class="col-sm-5">
                                    {{ ($item->time_method == 'number') ? $item->number_entry : $item->ending_date }}
                                </div>
                            </div>

                            <div class="row">
                                <label class="col-sm-3">Lama Periode</label>
                                <div class="col-sm-5">
                                    {{ $item->number_sequence }}
                                </div>
                            </div>
                        </div>

                        <div class="col-6">
                    
                            <div class="row">
                                <label class="col-sm-3"> Metode</label>
                                <div class="col-sm-9 checkbox-custom">
                                     {{ ($item->compute_method == 'linier') ? "Linier" : "Degressive" }}
                                </div>
                            </div>
                    
                           @if($item->compute_method == 'degressive')
                                <div class="row">
                                    <label class="col-sm-3">Faktor Degressive</label>
                                    <div class="col-sm-5">
                                        {{ $item->degressive_factor }}
                                    </div>
                                </div>
                            @endif
                    
                        </div>
                    </div>

                </div>
              </div>

           
                
		</div>  
	</div>
</div>
	
@stop