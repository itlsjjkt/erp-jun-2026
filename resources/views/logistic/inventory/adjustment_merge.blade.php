@extends('layouts.app')

@section('page-header')
    Adjustment Stock
@stop

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('logistic.inventory.index') }}">Inventory</a></li>
        <li class="breadcrumb-item active" aria-current="page">Adjustment Stock</li>
    </ol>
@endsection

@section('content')
{!! Form::open(['method' => 'POST', 'route' => ['logistic.inventory.adjustment_merge.store'], 'id'=>'form-adjustment-merge', 'files' => true]) !!}
{{ csrf_field() }}
<div class="row mB-40">
    <div class="col-sm-12">
        <div class="bgc-white p-30 bd">

            <h6><a class="float-left" href="{{ route('logistic.inventory.index') }}"><i class="ti-arrow-left mR-10"></i></a> Kembali</h6>
            <hr class="mB-30">

            <div class="form-group row">
                <label class="col-sm-2">Nama Pemeriksa </label>
                <div class="col-sm-2">
                    {!! Form::text('operatorAll', '', ['class' => 'form-control operatorAll', 'placeholder' => '','id' => 'operatorAll']) !!}
                </div>
                <div class="col-sm-3" id="discount_item_wrapper_operator">
                    <input type="checkbox" name="operator_all" id="operator_all" class="switch switch-info" value="0"> <label for="operator_all">Operator Untuk Semua Item</label>
                </div>
            </div>
            <div class="form-group row">
                <label class="col-sm-2">Alasan Adjustment </label>
                <div class="col-sm-2">
                    {!! Form::textarea('reasonAll', '', ['class' => 'form-control reasonAll', 'style' => 'height:72px;','placeholder' => '','id' => 'reasonAll']) !!}
                </div>
                <div class="col-sm-3" id="discount_item_wrapper_reason">
                    <input type="checkbox" name="reason_all" id="reason_all" class="switch switch-info" value="0"> <label for="reason_all">Alasan Untuk Semua Item</label>
                </div>
            </div>
            <div class="row mB-25">
                <label class="col-sm-2 mt-1">Attachment Berita Acara <i class="fa fa-file-pdf-o text-danger"></i></label>
                <div class="col-sm-10">
                    <div class="form-inline">
                        {!! Form::myFile('file', '', ['class' => '']) !!}
                    </div>
                </div>
            </div>
            
            <table class="table table-bordered" cellspacing="0" width="100%">
                <thead style="border-radius: 20px;">
                    <tr style="background-color: rgb(240, 240, 240)">
                        <th style="width: 100px !important;">No Rak</th>
                        <th style="width: 100px !important;">Kode</th>
                        <th>Nama Produk</th>
                        <th style="width: 150px !important;">Lokasi</th>
                        <th style="width: 100px !important;">Stock</th>
                        <th style="width: 200px !important;">Qty Fisik</th>
                        <th style="width: 200px !important;">Nama Pemeriksa</th>
                        <th style="width: 250px !important;">Alasan</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($inventories as $key => $inventory)
                    <tr>
                        <td class="text-center">
                            {{$inventory->code_rack ? $inventory->code_rack : '-'}}
                        </td>
                        <td>{{$inventory->productCode}}</td>
                        <td>
                            {{ $inventory->productName }}
                            <br>
                            <small>
                                {{ $inventory->productPartNumber ? 'PN: ' . $inventory->productPartNumber : 'PN: -' }} <br>
                                {{ $inventory->brand ? 'Brand: ' . $inventory->brand : 'Brand: -'}}
                            </small>
                        </td>                                
                        <td>{{ $inventory->location}}</td>
                        <td>{{ $inventory->stock_onhand }} {{ $inventory->unit }}</td>
                        <td>
                            <div style="display: flex; align-items: center;">
                                {!! Form::text('qty_fisik[]', old('qty_fisik'), ['class' => 'form-control qty_fisik' , 'placeholder' => '', 'required' => '', 'style' => 'width: 100px; margin-right: 10px;', 'id' => 'qty_fisik_'.$inventory->id])!!}
                                <div>
                                    {{ $inventory->unit }}
                                </div>
                            </div>
                        </td>                                              
                        <td>
                            {!! Form::text('operator[]', old('operator'), ['class' => 'form-control operator', 'placeholder' => '', 'required' => '', 'id' => 'operator_'.$inventory->id]) !!}
                        </td>
                        <td>
                            {!! Form::textarea('reason[]', old('reason'), ['class' => 'form-control reason','style' => 'height:36px;', 'rows' => 3, 'placeholder' => '', 'required' => '', 'id' => 'reason_'.$inventory->id]) !!}
                        </td>
                        {!! Form::hidden('inventory_id[]', $inventory->id) !!}
                        {!! Form::hidden('location_id[]', $inventory->locationID) !!}
                        {!! Form::hidden('qty_awal[]', $inventory->stock_onhand) !!}

                    </tr>
                @endforeach
                </tbody>
            </table>
            <div class="form-group row">
                <div class="col-sm-12 text-right">
                    <a href="{{ route('logistic.inventory.index') }}" class="btn btn-secondary text-uppercase fsz-sm fw-600 float-righ">{{ trans('Cancel') }}</a> &nbsp;&nbsp;
                    <input class="btn btn-success text-uppercase fsz-sm fw-600 float-right" type="submit" name="publish" id="btn-submit-dpm" value="INPUT ADJUSTMENT">
                </div>
            </div>                  
		</div>  
	</div>
</div>
{!! Form::close() !!}
@stop

@section('js')
    <script>
        
        $(document).ready(function() {
            @foreach($inventories as $inventory)
                $('#dataTables_{{ $inventory->id }}').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: '{{ route('logistic.inventory.adjustment.datatables',['id' => Hashids::encode($inventory->id)]) }}',
                    columns: [
                        {data: 'doc_no', name: 'doc_no'},
                        {data: 'qty_awal', name: 'qty_awal', searchable: false},
                        {data: 'qty_fisik', name: 'qty_fisik', searchable: false},
                        {data: 'reason', name: 'reason', searchable: false},
                        {data: 'operator', name: 'operator'},
                        {data: 'created_at', name: 'created_at', searchable: false},
                        {data: 'action', name: 'action', searchable: false},
                    ],
                    "order": [[ 5, "desc" ]]
                });
            @endforeach

            $('#operator_all').on('ifChecked', function() {
                var operatorrr = $("#operatorAll").val();
                $(".operator").val(operatorrr).prop('readonly', true);
                $(".operatorAll").prop('readonly', true);
                $("#operator_all").attr('value', '1');
            });

            $('#operator_all').on('ifUnchecked', function() {
                $(".operator").val('').prop('readonly', false);
                $(".operatorAll").prop('readonly', false);
                $("#operator_all").attr('value', '0');
            });

            $('#reason_all').on('ifChecked', function() {
                var reasonnn = $("#reasonAll").val();
                $(".reason").val(reasonnn).prop('readonly', true);
                $(".reasonAll").prop('readonly', true);
                $("#reason_all").attr('value', '1');
            });

            $('#reason_all').on('ifUnchecked', function() {
                $(".reason").val('').prop('readonly', false);
                $(".reasonAll").prop('readonly', false);
                $("#reason_all").attr('value', '0');
            });
			function validateInput(event) {
					var inputValue = event.target.value;
					var cleanedValue = inputValue.replace(/[^0-9.]/g, '');
					event.target.value = cleanedValue;
				}
				var inputs = document.querySelectorAll('.qty_fisik');
				inputs.forEach(function(input) {
					input.addEventListener('input', validateInput);
			});		
        });
    </script>
@stop

{{-- <th style="width: 50px !important;">Attachment</th> --}}

{{-- <td>
    <kbd class="btn-danger"><i class="fa fa-file-pdf-o"></i> PDF</kbd>
    {!! Form::myFile('file[]', '',['class' => '','style' => 'max-width:200px;', 'id' => 'file_'.$inventory->id]) !!}
</td> --}}