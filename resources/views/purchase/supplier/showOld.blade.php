@extends('layouts.app')

@section('page-header')
    Supplier
@endsection
@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('purchasing.suppliers.index') }}">Supplier</a></li>
        <li class="breadcrumb-item active" aria-current="page">Show</li>
    </ol>
@endsection

@section('content')
	<div class="row mB-40">
        <div class="col-sm-12">
            <div class="bgc-white p-20 bd">
            <h5>Detail Supplier</h5>
               <hr class="mB-30">
                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label">Nama Perusahaan :</label>
                        <div class="mt-2">
                            {{$dataSupplier->name}}
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label">Category :</label>
                        <div class="mt-2"style="max-width:500px !important">
                            @if(Count($dataCategory) > 0)
                                @foreach($dataCategory as $val)
                                    <div class="ms-3"> - {{$val->nameCategory}}</div>
                                @endforeach
                            @else
                            <div class="ms-3"> -</div>
                            @endif
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label">Alamat :</label>
                        <div class="mt-2"style="max-width:500px !important">
                            {{$dataSupplier->address != null ? $dataSupplier->address : '-'}}
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label">Payment Term :</label>
                        <div class="mt-2">
                            {{$dataSupplier->p_payment_term ? $dataSupplier->p_payment_term : '-'}}
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label">Nomor Pokok Wajib Pajak ( NPWP ) :</label>
                        <div class="mt-2">
                            {{$dataSupplier->npwp ? $dataSupplier->npwp : '-'}}
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label">Nomor Induk Berusaha ( NIB ) :</label>
                        <div class="mt-2">
                            {{$dataSupplier->nib ? $dataSupplier->nib : '-'}}
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label">Nomor Pengusaha Wajib Pajak ( PKP ) :</label>
                        <div class="mt-2">
                            {{$dataSupplier->pkp ? $dataSupplier->pkp : '-'}}
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label">Nomor Surat Agent :</label>
                        <div class="mt-2">
                            {{$dataSupplier->surat_agent ? $dataSupplier->surat_agent : '-'}}
                        </div>
                    </div>
					<div class="form-group row">
                        <label class="col-sm-3 col-form-label">PIC :</label>
                        <div>
                            @foreach($dataPIC as $val)
                                <div class="mt-2 ms-3">
                                    Nama: {{$val->title ? $val->title . ' ' : ''}}{{$val->name}} ||
                                    Telepon: {{$separatedTelp[$loop->index]['telp1'] ?? '-'}} ||
                                    Mobile Phone: {{$separatedTelp[$loop->index]['telp2'] ?? '-'}} || 
                                    Email: {{$val->email ? $val->email : '-'}}
                                </div>
                            @endforeach
                        </div>
					</div>
                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label">Status supplier :</label>
                        <div>
                            @if($dataSupplier->status == 1)
                                <span class="badge badge-success mt-2">Aktif</span>
                            @else
                                <span class="badge badge-danger mt-2">Non Aktif</span>
                            @endif
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label">Status PPN :</label>
                        <div>
                            @if($dataSupplier->is_ppn == 1)
                                <span class="badge badge-info mt-2">PPN</span>
                            @else
                                <span class="badge badge-info mt-2">Non PPN</span>
                            @endif
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label">Blacklist :</label>
                        <div>
                            @if($dataSupplier->is_block == 1)
                                <span class="badge badge-danger mt-2">Blacklist</span>
                            @else
                                <span class="badge badge-primary mt-2">Tidak</span>
                            @endif
                        </div>
                    </div>                  
                <hr>
            </div>  
        </div>
    </div> 
@endsection