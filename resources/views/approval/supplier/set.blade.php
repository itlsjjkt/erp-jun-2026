@extends('layouts.app')

@section('page-header')
    Approval Supplier
@stop

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('approval.supplier.index') }}">Approval Supplier</a></li>
        <li class="breadcrumb-item active" aria-current="page">Approval</li>
    </ol>
@endsection

@section('content')
<div class="mB-40">
    {!! Form::open([
        'action' => ['Approval\SupplierController@update', $supplier->id],
        'method' => 'post',
        'class'  => 'form-horizontal mt-3',
        'id'     => 'formSupplier',
    ]) !!}

    <div class="bgc-white p-30 bd">
        <div class="d-block">
            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" data-toggle="tab" href="#tab1" role="tab">Detail Supplier</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#tab2" role="tab">Histori</a>
                </li>
            </ul>
        </div>

        <div class="tab-content mt-5">

            {{-- TAB DETAIL --}}
            <div class="tab-pane active" id="tab1" role="tabpanel">
                <div class="row">
                    <div class="col-sm-6">
                        <div class="row">
                            <label class="col-sm-4">Nama Supplier</label>
                            <div class="col-sm-7">: <strong>{{ $supplier->name }}</strong></div>
                        </div>
                        <div class="row">
                            <label class="col-sm-4">Alamat</label>
                            <div class="col-sm-7">: {{ $supplier->address ?? '-' }}</div>
                        </div>
                        <div class="row">
                            <label class="col-sm-4">Payment Term</label>
                            <div class="col-sm-7">: {{ $supplier->payment_term_name ?? '-' }}</div>
                        </div>
                        <div class="row">
                            <label class="col-sm-4">Metode Pembayaran</label>
                            <div class="col-sm-7">: {{ $supplier->payment_method_name ?? '-' }}</div>
                        </div>
                        <div class="row">
                            <label class="col-sm-4">Mata Uang</label>
                            <div class="col-sm-7">: {{ $supplier->currency ?? '-' }}</div>
                        </div>
                        <div class="row">
                            <label class="col-sm-4">PPN</label>
                            <div class="col-sm-7">:
                                @if($supplier->is_ppn != 0)
                                    <span class="badge badge-primary">PPN {{ $supplier->is_ppn }}%</span>
                                @else
                                    <span class="badge badge-warning">Tidak PPN</span>
                                @endif
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-sm-4">Status</label>
                            <div class="col-sm-7">:
                                @if($supplier->status == 1)
                                    <span class="badge badge-success">Aktif</span>
                                @else
                                    <span class="badge badge-secondary">Non Aktif</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="row">
                            <label class="col-sm-4">NPWP</label>
                            <div class="col-sm-7">: {{ $supplier->npwp ?? '-' }}</div>
                        </div>
                        <div class="row">
                            <label class="col-sm-4">NIB</label>
                            <div class="col-sm-7">: {{ $supplier->nib ?? '-' }}</div>
                        </div>
                        <div class="row">
                            <label class="col-sm-4">PKP</label>
                            <div class="col-sm-7">: {{ $supplier->pkp ?? '-' }}</div>
                        </div>
                        <div class="row">
                            <label class="col-sm-4">Surat Agent</label>
                            <div class="col-sm-7">: {{ $supplier->surat_agent ?? '-' }}</div>
                        </div>
                        <div class="row">
                            <label class="col-sm-4">Dibuat Oleh</label>
                            <div class="col-sm-7">: {{ $supplier->created_by_name ?? '-' }}</div>
                        </div>
                        <div class="row">
                            <label class="col-sm-4">Step Approval</label>
                            <div class="col-sm-7">: <span class="badge badge-info">Step {{ $supplier->step }}</span></div>
                        </div>
                    </div>
                </div>

                {{-- KATEGORI --}}
                <h6 class="mT-20">Kategori</h6>
                <hr>
                @if(count($categories) > 0)
                    @foreach($categories as $cat)
                        <span class="badge badge-light border mR-5 mB-5">{{ $cat->name }}</span>
                    @endforeach
                @else
                    <p class="text-muted">-</p>
                @endif

                {{-- PIC --}}
                <h6 class="mT-20">Daftar PIC</h6>
                <hr>
                @if(count($contacts) > 0)
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th style="width:40px">No</th>
                                <th>Nama</th>
                                <th>Mobile Phone</th>
                                <th>Telepon</th>
                                <th>Email</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($contacts as $i => $pic)
                            <tr>
                                <td class="text-center">{{ $loop->iteration }}</td>
                                <td>{{ $pic->title ? $pic->title.' ' : '' }}{{ $pic->name }}</td>
                                <td>{{ $separatedTelp[$i]['telp1'] ?? '-' }}</td>
                                <td>{{ $separatedTelp[$i]['telp2'] ?? '-' }}</td>
                                <td>{{ $pic->email ?? '-' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p class="text-muted">Tidak ada PIC terdaftar.</p>
                @endif

                {{-- FORM CATATAN --}}
                <div class="form-group mT-20">
                    <label>Catatan</label>
                    {!! Form::textarea('message', null, ['class' => 'form-control', 'rows' => 2, 'placeholder' => '']) !!}
                </div>
            </div>

            {{-- TAB HISTORI --}}
            <div class="tab-pane" id="tab2" role="tabpanel">
                <div class="timeline">
                    <div class="timeline__group">
                        @foreach($history as $h)
                        <div class="timeline__box">
                            <div class="timeline__date"></div>
                            <div class="timeline__post">
                                <div class="timeline__content">
                                    @if($h->jenis == 'approval')
                                        <p>Disetujui oleh <strong>{{ $h->user_name }}</strong> pada {{ \Carbon\Carbon::parse($h->date_approved)->format('d/m/Y H:i') }}</p>
                                        <p><strong>Catatan: </strong>{{ $h->message ?? '-' }}</p>
                                    @elseif($h->jenis == 'revisi')
                                        <p>Dikembalikan untuk revisi oleh <strong>{{ $h->user_name }}</strong> pada {{ \Carbon\Carbon::parse($h->date_approved)->format('d/m/Y H:i') }}</p>
                                        <p><strong>Catatan: </strong>{{ $h->message ?? '-' }}</p>
                                    @elseif($h->jenis == 'cancel')
                                        <p>Dibatalkan oleh <strong>{{ $h->user_name }}</strong> pada {{ \Carbon\Carbon::parse($h->date_approved)->format('d/m/Y H:i') }}</p>
                                        <p><strong>Catatan: </strong>{{ $h->message ?? '-' }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

        </div>
    </div>

    <div class="mt-4">
        <a href="{{ route('approval.supplier.index') }}" class="btn btn-light text-uppercase fsz-sm fw-600 mr-1">Kembali</a>
        <input type="hidden" value="0" name="status">
        <input class="btn btn-info text-uppercase fsz-sm fw-600 float-right ml-2" type="submit" name="save" value="Revisi" id="btn-revisi">
        <input class="btn btn-danger text-uppercase fsz-sm fw-600 float-right" type="submit" name="publish" id="btn-approve" value="Approve">
    </div>

    {!! Form::close() !!}
</div>
@stop

@section('js')
<script type='text/javascript'>
    $(document).ready(function() {
        $(document).on("click", "#btn-approve", function(e) {
            $('input[name="status"]').val('1');
        });

        $(document).on("click", "#btn-revisi", function(e) {
            $('input[name="status"]').val('0');
        });

        $('#formSupplier').on('submit', function() {
            $('#btn-approve').attr('disabled', true).val('Memproses...');
            $('#btn-revisi').attr('disabled', true).val('Memproses...');
        });
    });
</script>
@stop
