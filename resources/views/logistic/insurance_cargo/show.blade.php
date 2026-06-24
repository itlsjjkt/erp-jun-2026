@extends('layouts.app')

@section('page-header')
	View Asuransi Cargo <small>{{ $insurance->doc_no }}</small>
@stop

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('logistic.insurance_cargo.index') }}">Asuransi Cargo</a></li>
        <li class="breadcrumb-item active" aria-current="page">Detail</li>
    </ol>
@endsection

@section('content')

<div class="mB-40">
	<div class="bgc-white p-30 bd">
	
		<div class="row mb-1 justify-content-end">
			<div class="col-sm-6">
				<a class="btn btn-outline float-right" href="#" title="Print Data" onclick='printExternal("/logistic/insurance_cargo_print/{{ Hashids::encode($insurance->id) }}/print")'><i class="ti-printer icon-lg"></i></a>
			</div>
		</div>
		<h6 class="text-center font-weight-bold mB-40" style="text-decoration:underline">{{ $insurance->doc_no }}</h6>

		<div class="row">
			<div class="col-sm-6"> 
				<div class="row">
					<label class="col-sm-4">Ekpedisi</label>
					<div class="col-sm-7">: {{ $insurance->expedition }}</div>
				</div>
				<div class="row">
					<label class="col-sm-4">Periode</label>
					<div class="col-sm-7">: {{ $insurance->period }}</div>
				</div>
				<div class="row">
					<label class="col-sm-4">Risk Location</label>
					<div class="col-sm-7">: {{ $insurance->risk_location }}</div>
				</div>
			</div>
			<div class="col-sm-6">
				<div class="row">
					<label class="col-sm-4">Dibuat Oleh</label>
					<div class="col-sm-7">: {{ $insurance->created }} [ {{ idDate($insurance->created_at) }}]
					</div>
				</div>
				<div class="row">
					<label class="col-sm-4">Catatan</label>
					<div class="col-sm-7">: {{ $insurance->notes }}
					</div>
				</div>
			</div>
		</div>

		<h6 class="mT-30">Daftar Barang</h6>
		<table class="table table-bordered">
			<thead>
				<tr>
					<th style="width:50px">No</th>
					<th>Nama Barang</th>
					<th>Catatan/Spesifikasi</th>
					<th class="text-center">QTY </th>
					<th class="text-center">No. PO</th>
					<th class="text-center">No. SPB</th>
					<th class="text-center">Supplier</th>
					<th class="text-center">Harga</th>
					<th class="text-center">Diskon</th>
					<th class="text-center">PPN (11%)</th>
					<th class="text-center">Total</th>
				</tr>
			</thead>
			<tbody>
					@php
						$no = 1;
						$totalharga = 0;
					@endphp
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
							<td>{{ $no }}</td>
							<td>[{{ $item->productCode }}] - {{ $item->product }} <br><small>PN/SPEC: {{ $item->productPartNumber }} | Brand: {{ $item->productBrand }}</small></td>
							<td>{{ $item->notes }}</td>
							<td>{{ $item->qtyKoli }} {{ $item->measure }}</td>
							<td>{{ $item->noPO }}</td>
							<td>{{ $item->noSPB }}</td>
							<td>{{ $item->supplier }}</td>
							<td class="text-right">{{ number_format($item->price ,2,".",',') }}</td>
							<td class="text-right">{{ number_format($item->discount ,2,".",',') }}</td>
							<td>{{ $item->ppn == 1 ? "Ya" : "Tidak" }}</td>
							<td class="text-right">{{ number_format($subtotal ,2,".",',') }}</td>
						</tr>
					@php
						$no++;
					@endphp
					@endforeach
			</tbody>
			<tfoot>
				<tr>
					<td class="text-right font-weight-bold" colspan="10">TOTAL</td>
					<td class="text-right font-weight-bold">  {{ number_format($totalharga ,2,".",',') }} </td>
				</tr>
			</tfoot>
		</table>

		<table class="table table-bordered">
			<thead>
				<tr>
					<th rowspan="2" class="text-center">Prepared By</th>
					<th rowspan="2" class="text-center">Checked By</th>
					<th rowspan="2" class="text-center">Approved By</th>
					<th colspan="2" class="text-center">Checked By</th>
					<th colspan="2" class="text-center">Received By</th>
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
					<td class="text-center"> {{ $insurance->prepared_by }}</td>
					<td class="text-center"> {{ $insurance->checked_by }} </td>
					<td class="text-center"> {{ $insurance->approved_by }} </td>
					<td class="text-center"> {{ $insurance->checked_purchasing_1 }}  </td>
					<td class="text-center"> {{ $insurance->checked_purchasing_2 }} </td>
					<td class="text-center"> {{ $insurance->received_by_1 }}  </td>
					<td class="text-center"> {{ $insurance->received_by_2 }}  </td>
				</tr>
			</tbody>
		</table>
		
		@if ($insurance->status == '0'  )

			<div class="mt-4 btn-group">

				<a href="{{ route('logistic.insurance_cargo.edit',Hashids::encode($insurance->id)) }}" class="btn btn-info mr-1 text-uppercase fsz-sm fw-600">Edit Draft</a>
				<form class='delete' action="{{ route('logistic.insurance_cargo.delete', ['id' => $insurance->id]) }}" method='POST'>
					{{ csrf_field() }}
					<button class='btn btn-danger mr-1 text-uppercase fsz-sm fw-600' title='Hapus'>Hapus Draft</button>
				</form>
			</div>

		@endif
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