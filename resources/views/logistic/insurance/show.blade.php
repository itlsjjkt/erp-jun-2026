@extends('layouts.app')

@section('page-header')
	View Asuransi <small>{{ $insurance->doc_no }}</small>
@stop

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('logistic.insurance.index') }}">Asuransi</a></li>
        <li class="breadcrumb-item active" aria-current="page">Detail</li>
    </ol>
@endsection

@section('content')

<div class="mB-40">
	<div class="bgc-white p-30 bd">
		<div class="row mb-1 justify-content-end">
			<div class="col-sm-6">
				{{-- <a class="btn btn-outline float-right" href="#" title="Print Data" onclick='printExternal("/logistic/insurance_print/{{ Hashids::encode($insurance->id) }}/print")'><i class="ti-printer icon-lg"></i></a> --}}
				<a class="btn btn-outline float-right" href="{{route('logistic.insurance.print',[ 'id' => Hashids::encode($insurance->id) , 'type' => 'print' ])}}" title="Print Data"><i class="fa fa-file-pdf-o text-danger icon-lg"></i></a>
			</div>
		</div>
		<div class="d-block">
			<ul class="nav nav-tabs" role="tablist">
				<li class="nav-item">
					<a class="nav-link active" data-toggle="tab" href="#tab1" role="tab">Asuransi</a>
				</li>
				<li class="nav-item">
					<?php 
					if ($insurance->mr_file)
						$badge = "<sup class='badge '><span class='bg-success rounded-circle text-success' style='padding:0 5px'></span></sup>";
					else
						$badge = "";
					?>
					<a class="nav-link" data-toggle="tab" href="#tab2" role="tab">Dokumen MR {!! $badge !!}</a>
				</li>
			</ul>
		</div>
		<div class="tab-content mT-30" >
			<div class="tab-pane active" id="tab1" role="tabpanel" aria-labelledby="tab1">

				<h6 class="text-center font-weight-bold mB-40">== FORM PERMINTAAN COVER ASURANSI ==</h6>
				<div class="row">
					<div class="col-sm-7"> 
						<div class="row">
							<label class="col-sm-4">COMPANY</label>
							<div class="col-sm-7">: {{ $insurance->company ?? '-' }}</div>
						</div>
						<div class="row">
							<label class="col-sm-4">PROJECT</label>
							<div class="col-sm-7">: {{ $insurance->project ?? '-' }}</div>
						</div>
						<div class="row">
							<label class="col-sm-4">INSURANCE NUMBER</label>
							<div class="col-sm-7">: {{ $insurance->doc_no }}</div>
						</div>
						<div class="row">
							<label class="col-sm-4">MANIFEST NUMBER</label>
							<div class="col-sm-7">
								@php
									$previousNoSPB = null;
								@endphp
								@foreach($insurance_items as $val)
									@if ($val->noSPB != $previousNoSPB)
										<li>{{ $val->noSPB }}</li>
									@endif
									@php
										$previousNoSPB = $val->noSPB;
									@endphp
								@endforeach
							</div>
						</div>
					</div>
					<div class="col-sm-5">
						<div class="row">
							<label class="col-sm-4">EKSPEDISI / FORWARDER</label>
							<div class="col-sm-7">: {{ $insurance->expedition_forwarder ?? '-' }}
							</div>
						</div>
						<div class="row">
							<label class="col-sm-4">RISK LOCATION</label>
							<div class="col-sm-7">: {{ $insurance->risk_location ?? '-' }}
							</div>
						</div>
						<div class="row">
							<label class="col-sm-4">ETD / ETA</label>
							<div class="col-sm-7">: {{ $insurance->etd_eta ? idDate2($insurance->etd_eta) : '-' }}
							</div>
						</div>
						<div class="row">
							<label class="col-sm-4">READY TO SHIPPED BY</label>
							<div class="col-sm-7">: {{ $insurance->shipped_by ?? '-' }}
							</div>
						</div>
						<div class="row">
							<label class="col-sm-4">Dibuat Oleh</label>
							<div class="col-sm-7">: {{  $insurance->created ?? '-' }} [ {{ $insurance->created_at ? idDate($insurance->created_at) : '-' }}]
							</div>
						</div>
						<div class="row">
							<label class="col-sm-4">Status</label>
							<div class="col-sm-7">: {!! $insurance->status ? getStatusInsurance($insurance->status, null) : '-' !!}
							</div>
						</div>
					</div>
				</div>

				<h6 class="mT-30">Daftar Barang</h6>
				<table class="table table-bordered">
					<thead>
						<tr>
							<th class="text-center" style="width: 60%" colspan="4">DIKIRIM</th>
							<th class="text-center" colspan="7">ASURANSI</th>
						</tr>
						<tr>
							<th rowspan="2">Nama Barang</th>
							<th rowspan="2" style="width:300px">Notes</th>
							<th colspan="2" class="text-center" style="width: 150px !important;">Item</th>
							<th rowspan="2" class="text-center" style="width: 150px !important;">Harga / Item</th>
							<th rowspan="2" class="text-center" style="width:60px">Disc(%)</th>
							<th rowspan="2" class="text-center" style="width:60px">PPN(%)</th>
							<th rowspan="2" class="text-center" style="width: 200px !important;">Total</th>
						</tr>
						<tr>
							<th class="text-center" style="">QTY</th>
							<th class="text-center" style="">UOM</th>
						</tr>
					</thead>
					<tbody>
							@php
								$no = 1;
								$totalharga = 0;
							@endphp
							<?php $akhir = 0; ?>
							@foreach ($insurance_items as $item)
								@php
									$total= $item->price * $item->qtyKoli - (($item->price * $item->qtyKoli) *  $item->discount /100);
									if($item->ppn == 1){
										$subtotal = $total + ( $total * 11/100);
									}else{
										$subtotal = $total;
									}

									$totalharga += $subtotal;
								@endphp
								<tr>
									<td>
										{{'['.$item->productCode.'] '}}{{$item->product}}<br> 
										<small>
											{{$item->productPartNumber?'PN/Spec : '.$item->productPartNumber : 'PN/Spec : -'}} <br>
											{{$item->productBrand?'Brand : '.$item->productBrand : 'Brand : -'}}
										</small>
									</td>
									<td>{!!$item->notes?$item->notes:'-'!!}</td>
									<td class="text-center">{{$item->qtyKoli}}</td>
									<td class="text-center">{{$item->measure}}</td>
									<td><div class="currency" data-content="{{$item->symbol.'.'}}">{{number_format($item->price,2,",",'.')}}</div></td>
									<td class="text-center">{{$item->discount}}</td>
									<td class="text-center">{{$item->ppn}}</td>
									<?php
										$harga_diskon = $item->price * (1-($item->discount/100));
										$harga_ppn = $harga_diskon * ($item->ppn/100);
										$total = ($harga_diskon + $harga_ppn ) * $item->qtyKoli;
										$akhir += $total;
										$mataUang = $item->symbol;
									?>
									<td style="text-align: right">
										<div class="currency" data-content="{{$item->symbol.'.'}}">{{number_format($total,2,",",'.')}}</div>
									</td>
									</style>
								</tr>
							@php
								$no++;
							@endphp
							@endforeach
					</tbody>
					<tfoot>
						<tr>
							<td colspan="6"></td>
							<td colspan="1" class="text-center font-weight-bold">TOTAL</td>
							<td colspan="1" class="text-right font-weight-bold"><div class="currency" data-content="{{$mataUang.'.'}}">{{number_format($akhir ,2,",",'.')}}</div></td>
						</tr>
					</tfoot>
				</table>

				<table class="table table-bordered">
					<thead>
						<tr>
							<th class="text-center">Prepared By</th>
							<th colspan="2" class="text-center">Checked By</th>
							<th colspan="3" class="text-center">Mengetahui</th>
							<th class="text-center">Received By</th>
							<th class="text-center">Menyetujui</th>
						</tr>
					</thead>
					<tbody>
						<tr style="height: 100px">
							<td> </td>
							<td> </td>
							<td> </td>
							<td> </td>
							<td> </td>
							<td> </td>
							<td> </td>
						</tr>
						<tr>
							<td style="width: 12.5%" class="text-center"> {{ $insurance->prepared_by }}</td>
							<td style="width: 12.5%" class="text-center"> {{ $insurance->checked_by_1 }} </td>
							<td style="width: 12.5%" class="text-center"> {{ $insurance->checked_by_2 }} </td>
							<td style="width: 12.5%" class="text-center"> {{ $insurance->known_by_1 }}  </td>
							<td style="width: 12.5%" class="text-center"> {{ $insurance->known_by_2 }} </td>
							<td style="width: 12.5%" class="text-center"> {{ $insurance->known_by_3 }}  </td>
							<td style="width: 12.5%" class="text-center"> {{ $insurance->received_by }}  </td>
							<td style="width: 12.5%" class="text-center"> {{ $insurance->approved_by }}  </td>
						</tr>
					</tbody>
				</table>
			</div>
			<div class="tab-pane" id="tab2" role="tabpanel" aria-labelledby="tab2">
				<div class="row">
					<div class="col-12">
						@if ($insurance->mr_file)
							<embed class="col align-self-center" src="{{ asset('storage'.$insurance->mr_file) }}" width="600" height="500" alt="pdf" />
						@else
							<p class="text-center">File MR tidak dilampirkan dalam Asuransi</p>
						@endif
					</div>
				</div>
  			</div>
		</div>
	</div>
</div>
	
@stop


@section('js')

	<script  type='text/javascript'>
	function printExternal(url) {
			var printWindow = window.open(url, 'Print', 'left=200, top=200, width=950, height=800, toolbar=0, resizable=0');
			printWindow.addEventListener('load', function() {
				printWindow.print();
			}, true);
		}
	</script>
@stop