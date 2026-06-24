@extends('layouts.app')

@section('page-header')
    Approval DPM
@stop

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('approval.purchase.index') }}">Approval DPM</a></li>
        <li class="breadcrumb-item active" aria-current="page">Approval</li>
    </ol>
@endsection


@section('content')

<div class="mB-40">
        {!! Form::model($pr, [
                'action' => ['Approval\PurchaseController@update', $pr->id],
                'method' => 'post',
                'class' => 'form-horizontal mt-3',
                'id'    => 'formPR',
                'files' => true
            ])
        !!}

		<div class="bgc-white p-30 bd">
            <div class="d-block">
                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-toggle="tab" href="#tab1" role="tab">DPM</a>
                    </li>
                    <li class="nav-item">
                        <?php
                        if ($pr->mr_file)
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
				    <h6 class="text-center font-weight-bold mB-30" style="text-decoration:underline">{{ $pr->doc_no }}</h6>
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="row">
                                <label class="col-sm-3">Tipe DPM</label>
                                <div class="col-sm-8">: {{ strtoupper($pr->type) }}</div>
                            </div>
                            <div class="row">
                                <label class="col-sm-3">Lokasi/Kapal </label>
                                <div class="col-sm-8">: {{ ($pr->location) ? $pr->location->name : '-' }}</div>
                            </div>
                            <div class="row">
                                <label class="col-sm-3">Departemen </label>
                                <div class="col-sm-8">: {{ ($pr->department) ? $pr->department->name : '' }}</div>
                            </div>
                            <div class="row">
                                <label class="col-sm-3">Project </label>
                                <div class="col-sm-8">: {{ ($pr->project) ? $pr->project->name : '' }}</div>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="row">
                                <label class="col-sm-3">Deskripsi</label>
                                <div class="col-sm-8">: {{ ($pr->description) ? $pr->description : '-' }}</div>
                            </div>
                            <div class="row">
                                <label class="col-sm-3">Dibuat Oleh</label>
                                <div class="col-sm-8">: {{ ($pr->creator) ? $pr->creator->name : '' }}</div>
                            </div>
                            @if(Auth::user()->id == 1)
                                <div class="row">
                                    <label class="col-sm-3">File Approval <kbd class="btn-danger 				mr-2"><i class="fa fa-file-pdf-o"></i> PDF</kbd></label>
                                    <div class="col-sm-8">
                                        {!! Form::myFile('approval_dpm_file', '', ['accept' => '.pdf']) !!}
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <hr>
                    <div class="alert alert-info">
                        - Checklist Item Barang yang disetujui atau direject, Catatan bisa diberikan per Item Barang <br>
                        - Fitur Hold akan mengembalikan seluruh item DPM ke pembuat untuk direvisi/tambah item, Fitur ini hanya bisa digunakan jika tidak ada parsial Approval
                    </div>
                    <table class="table table-bordered mt-2">
                        <thead>
                            <th style="width:50px" class="text-center"><input class="magic-checkbox" name="checkedAll" id="checkedAll" type="checkbox">  <label for="checkedAll"></label></th>
                            <th style="min-width:250px">Nama Barang</th>
                            <th style="width:200px">QTY</th>
                            <th>Flag</th>
                            <th>Tgl Dibutuhkan</th>
                            <th >Catatan</th>
                        </thead>
                        <tbody class="item_form">
                            @if (count($pr_items) > 0)
                                @php
                                    $no = 1
                                @endphp
                                @foreach ($pr_items as $item)
                                        <tr>
                                            <td class="text-center"><input name="pr_item[]" class="checkSingle magic-checkbox" type="checkbox" value="{{ $item->id }}" id="checkbox_{{ $item->id }}"><label for="checkbox_{{ $item->id }}"></label></td>
                                            <td>
                                                [{{ $item->productCode }}] - {{ $item->product }} {!! $item->productPartNumber != NULL ? '<br> PN/Spec: '.$item->productPartNumber : '' !!}
											    {!! $item->productBrand != NULL ? '<br>Brand: '.$item->productBrand : '' !!}
                                                {!! $item->notes  != NULL ? '<br>Catatan: '.$item->notes : '' !!} <br>
                                                @php
                                                    $dataHistoryRequest = getHistoryRequest($item->produkId,$pr->location_id);
                                                @endphp
                                                @if(!empty($dataHistoryRequest))
                                                <small>
                                                    <strong class="text-danger">
                                                        Request Terakhir : {{$dataHistoryRequest->nopo}} <br>
                                                        Diterima : {{ \Carbon\Carbon::parse($dataHistoryRequest->tglpenerimaan)->translatedFormat('F Y') }}
                                                    </strong>
                                                </small>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="input-group">
                                                    <input type="number" min="1" name="qty[]" class="form-control" value="{{$item->qty}}" required onwheel="return false;"> 
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text"> {{$item->measure}} </span>
                                                    </div>
                                                </div>
                                                <div class="info_stock_{{ $item->id }}">
                                                        <br />Informasi Stock: <br/>
                                                        <?php setlocale(LC_TIME, 'id_ID.utf8'); ?>
                                                        Min: {!! !empty(getStock($item->product_id, $pr->location_id)) ? getStock($item->product_id, $pr->location_id)[0]->stock_min : "-" !!} 
                                                        Max: {!! !empty(getStock($item->product_id, $pr->location_id)) ? getStock($item->product_id, $pr->location_id)[0]->stock_max : "-" !!}
                                                        Onhand: {!! !empty(getStock($item->product_id, $pr->location_id))  ? getStock($item->product_id, $pr->location_id)[0]->stock_onhand : "-" !!} <br><small>{!! !empty(getStock($item->product_id, $pr->location_id)) ? "(Update terakhir : ". Carbon\Carbon::parse(getStock($item->product_id, $pr->location_id)[0]->updated_at)->formatLocalized('%A, %d %B %Y Jam %H:%M')." )" : "(Update terakhir tidak tersedia)" !!}</small>
                                                </div>
                                            </td>
                                            <td>{{ $item->flag == "normal" ? "Normal" : "Urgent" }} </td>
                                            <td>{{ date('d/m/Y',strtotime( $item->needed_on_date)) }}</td>
                                            <td>
                                                <input type="hidden" name="status[]" id="statusv_{{ $item->id}}" value="{{ $item->status }}">
                                                <textarea id="reason_{{$item->id}}" name="reason[]" class="form-control"></textarea>
                                            </td>
                                            <td>
                                                @if(getDPMLog($item->id) > 0)
                                                    <a href="#" value="{{ action('Logistic\PurchaseRequestController@getPrItemNotes',['id'=>$item->id]) }}" class="icon-lg modalMd" title="History Approval Data" data-toggle="modal" data-target="#modalMd"><span class="ti-eye text-danger"></span></a>
                                                @else
                                                    <a href="#" value="{{ action('Logistic\PurchaseRequestController@getPrItemNotes',['id'=>$item->id]) }}" class="icon-lg modalMd" title="History Approval Data" data-toggle="modal" data-target="#modalMd"><span class="ti-eye text-muted"></span></a>
                                                @endif
											    <a href="#" value="{{ action('Purchasing\PoController@getItems',['id'=>$item->product_id]) }}" class="icon-lg modalMdPO ml-1" data-toggle="modal" data-target="#modalHistoryPO"><span class="ti-shopping-cart text-muted"></span></a>
                                            </td>
                                        </tr>
                                        @php
                                            $no++
                                        @endphp
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>

                <div class="tab-pane" id="tab2" role="tabpanel" aria-labelledby="tab2">
                    <div class="row">
                        <div class="col-12">
						    @if ($pr->mr_file)
                                <embed class="col align-self-center" src="{{ asset('storage'.$pr->mr_file) }}" width="600" height="500" alt="pdf" />
                            @else
                                <p class="text-center">File tidak ditemukan</p>
                            @endif
                        </div>
                    </div>
                </div>
		    </div>
		</div>

        <div class="mt-4 row">
            <div class="col-6">
                <a href="{{ route('approval.purchase.index') }}" class="btn btn-light text-uppercase fsz-sm fw-600">{{ trans('Cancel') }}</a>
                <input class="btn btn-success btn-submit text-uppercase fsz-sm fw-600" type="submit" name="publish" id="btn-submit" value="Approve">
            </div>
            <div class="col-6">
                <div class="float-right">
                    @if (count($pr->PurchaseRequestItem) == count($pr_items))
                        <a class="btn btn-warning text-uppercase fsz-sm fw-600 text-dark" data-toggle="modal" data-target="#modalHold"> Hold </a>
                    @endif
                    <input class="btn btn-danger btn-reject text-uppercase fsz-sm fw-600" type="submit" name="reject"  id="btn-submit" value="Reject">
                </div>
            </div>
        </div>

        <input type="hidden" value="1" name="status">

	{!! Form::close() !!}
</div>

<div class="modal fade" id="modalMd" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="modalMdTitle">History Data Approval</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			</div>
			<div class="modal-body">
				<div class="modalError"></div>
				<div id="modalMdContent"></div>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="modalHistoryPO" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="modalMdTitle">History Purchase Order</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			</div>
			<div class="modal-body">
				<div class="modalError"></div>
				<div id="modalMdContentPO"></div>
			</div>
		</div>
	</div>
</div>


<div class="modal fade" id="modalHold" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
            <form action="{{ route('approval.purchase.hold') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="modalMdTitle">Apakah anda yakin untuk melakukan Hold Approval?</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <input type='hidden' value="{{ $pr->id }}" name="dpm_id">
                    <textarea name="message" class="form-control" placeholder='Isi Alasan kenapa hold'></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light text-uppercase fsz-sm fw-600" data-dismiss="modal" aria-label="Close">Batal</button>
                    <button class="btn btn-danger text-uppercase fsz-sm fw-600" type="submit" id="btn-hold">Submit</button>
                </div>
            </form>
		</div>
	</div>
</div>


@stop

@section('js')

<script  type='text/javascript'>

    $(document).ready(function() {

        $('.modalMd').off('click').on('click', function () {
            $('#modalMdContent').load($(this).attr('value'));
        });

        $('.modalMdPO').off('click').on('click', function () {
            $('#modalMdContentPO').load($(this).attr('value'));
        });

        $("#formPR").validate({
            rules: {
                "pr_item[]": {
                    required: true,
                    minlength: 1
                }
            },
            messages: {
                "pr_item[]": "Minimal Checklist 1 Item"
            }
        });

        $(document).on("click", ".btn-submit, .btn-reject, .btn-hold", function(e) {
            var actionType = $(this).hasClass('btn-reject') ? '2' : ($(this).hasClass('btn-hold') ? '3' : '1');
            $('input[name="status"]').val(actionType);
            var checkbox= document.querySelector('input[name="pr_item[]"]:checked');
            if(!checkbox) {
                Swal.fire(
                    'Informasi',
                    'Minimal Checklist 1 Item',
                    'warning'
                );
                return false;
            } else {
                removeUncheckedItems();
            }
        });

        $("#checkedAll").change(function(){
            if(this.checked){
                $(".checkSingle").each(function(){
                    this.checked=true;
                })
            }else{
                $(".checkSingle").each(function(){
                    this.checked=false;
                })
            }
        });

        $(".checkSingle").click(function () {
            if ($(this).is(":checked")){
                var isAllChecked = 0;
                $(".checkSingle").each(function(){
                    if(!this.checked)
                        isAllChecked = 1;
                })
                if(isAllChecked == 0){ 
                    $("#checkedAll").prop("checked", true); 
                }
            } else {
                $("#checkedAll").prop("checked", false);
            }
        });
    });

    function removeUncheckedItems() {
        $(".checkSingle").each(function(){
            if(!this.checked) {
                $(this).closest('tr').find('input, textarea').attr('disabled', 'disabled');
            }
        });
    }

    function valthisform()
    {
        var checkboxs=document.getElementsByName("pr_item");
        var okay=false;
        for(var i=0,l=checkboxs.length;i<l;i++)
        {
            if(checkboxs[i].checked)
            {
                okay=true;
                break;
            }
        }
        if(okay)return true;
        else alert("Please check a checkbox");
    }
</script>

@stop
