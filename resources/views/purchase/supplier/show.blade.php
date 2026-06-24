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
                        {{ $dataSupplier->name }}
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-3 col-form-label">Category :</label>
                    <div class="mt-2">
                        @if(Count($dataCategory) > 0)
                            @foreach($dataCategory as $val)
                                <div class="ms-3"> - {{ $val->nameCategory }}</div>
                            @endforeach
                        @else
                            <div class="ms-3"> -</div>
                        @endif
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-3 col-form-label">Alamat :</label>
                    <div class="mt-2" style="max-width:500px !important">
                        {{ $dataSupplier->address != null ? $dataSupplier->address : '-' }}
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-3 col-form-label">Payment Term :</label>
                    <div class="mt-2">
                        {{ $dataSupplier->p_payment_term ? $dataSupplier->p_payment_term : '-' }}
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-3 col-form-label">Metode Pembayaran :</label>
                    <div class="mt-2">
                        {{ $dataSupplier->payment_method ? $dataSupplier->payment_method : '-' }}
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-3 col-form-label">Mata Uang :</label>
                    <div class="mt-2">
                        {{ $dataSupplier->currency ? $dataSupplier->currency : '-' }}
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-3 col-form-label">Nomor Pokok Wajib Pajak ( NPWP ) :</label>
                    <div class="mt-2">
                        {{ $dataSupplier->npwp ? $dataSupplier->npwp : '-' }}
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-3 col-form-label">Nomor Induk Berusaha ( NIB ) :</label>
                    <div class="mt-2">
                        {{ $dataSupplier->nib ? $dataSupplier->nib : '-' }}
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-3 col-form-label">Nomor Pengusaha Wajib Pajak ( PKP ) :</label>
                    <div class="mt-2">
                        {{ $dataSupplier->pkp ? $dataSupplier->pkp : '-' }}
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-3 col-form-label">Nomor Surat Agent :</label>
                    <div class="mt-2">
                        {{ $dataSupplier->surat_agent ? $dataSupplier->surat_agent : '-' }}
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-3 col-form-label">PIC :</label>
                    <div>
                        @foreach($dataPIC as $val)
                            <div class="mt-2 ms-3">
                                Nama: {{ $val->title ? $val->title . ' ' : '' }}{{ $val->name }} ||
                                Telepon: {{ $separatedTelp[$loop->index]['telp1'] ?? '-' }} ||
                                Mobile Phone: {{ $separatedTelp[$loop->index]['telp2'] ?? '-' }} ||
                                Email: {{ $val->email ? $val->email : '-' }}
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-3 col-form-label">Status Supplier :</label>
                    <div>
                        @if($dataSupplier->status == 1)
                            <span class="badge badge-success mt-2">Aktif</span>
                        @else
                            <span class="badge badge-danger mt-2">Non Aktif</span>
                        @endif
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-3 col-form-label">PPN :</label>
                    <div>
                        @if($dataSupplier->is_ppn != 0)
                            <span class="badge badge-primary mt-2">{!! $dataSupplier->is_ppn.' %' !!}</span>
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
                            <small class="text-muted ml-2">{{ $dataSupplier->block_reason }}</small>
                        @else
                            <span class="badge badge-info mt-2">Tidak</span>
                        @endif
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-3 col-form-label">Status Approval :</label>
                    <div class="mt-2">
                        @php $aStatus = $dataSupplier->approval_status ?? 0; @endphp
                        @if($aStatus == 0)
                            <span class="badge badge-secondary">Draft</span>
                        @elseif($aStatus == 1)
                            <span class="badge badge-warning">Pending Approval (Step {{ $dataSupplier->step }})</span>
                        @elseif($aStatus == 2)
                            <span class="badge badge-success">Approved</span>
                        @elseif($aStatus == 3)
                            <span class="badge badge-danger">Perlu Revisi</span>
                        @elseif($aStatus == 4)
                            <span class="badge badge-dark">Dibatalkan</span>
                        @endif
                    </div>
                </div>

                @if(isset($approvalHistory) && count($approvalHistory) > 0)
                <hr>
                <h6 class="mT-10 mB-15">Riwayat Approval</h6>
                <table class="table table-sm table-bordered">
                    <thead class="thead-light">
                        <tr>
                            <th style="width:150px">Tanggal</th>
                            <th>User</th>
                            <th style="width:100px">Aksi</th>
                            <th>Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($approvalHistory as $h)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($h->date_approved)->format('d/m/Y H:i') }}</td>
                            <td>{{ $h->user_name }}</td>
                            <td>
                                @if($h->jenis == 'approval')
                                    <span class="badge badge-success">Approved</span>
                                @elseif($h->jenis == 'revisi')
                                    <span class="badge badge-danger">Revisi</span>
                                @elseif($h->jenis == 'cancel')
                                    <span class="badge badge-dark">Dibatalkan</span>
                                @endif
                            </td>
                            <td>{{ $h->message ?? '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @endif

                <hr>
                <a href="{{ route('purchasing.suppliers.index') }}" class="btn btn-light">
                    <i class="ti-arrow-left mR-5"></i> Kembali
                </a>

            </div>
        </div>
    </div>
@endsection
