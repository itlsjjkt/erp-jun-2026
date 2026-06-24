@extends('layouts.app')

@section('page-header')
    Inventory   
@endsection

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item active" aria-current="page">Inventory</li>
    </ol>
@endsection

@section('content')

<div class="row mB-40">
 
    <div class="col-sm-12">
        <div class="bgc-white p-20 bd">
            <div class="mB-20 mT-10">
                <div class="alert alert-info">
                    <strong>INFO ! </strong> Lakukan perpindahan Saldo terlebih dahulu untuk dapat menggunakan modul yang berkaitan dengan Inventory Control. <br>
                    Lakukan Export Data terlebih dahulu, sebelum melakukan perpindahan Saldo.
                </div>
                <form class="form-horizontal" action="{{ route('logistic.inventory.export')}}" method='POST'>
                    {{ csrf_field() }}
                        <hr>
                        <input type="hidden" name="location_id" id="location_id" value="{{ Auth::user()->location_id }}">
                        <button type="submit" class="btn btn-success mt-4">Export</button>
                        </div>
                </form>
                <form class="form-horizontal" action="{{ route('logistic.inventory.proses') }}" method='POST' id="formProses">
                    {{ csrf_field() }}
                    <div class="bgc-white bd bdrs-3 p-20">
                        <h6>Perpindahan Saldo Awal Bulan</h6>
                        <hr>
                        <div class="row">
                            <label class="col-sm-3 text-right">Bulan Proses</label>
                            <div class="col-sm-3">
                                <input type="hidden" name="month" class="form-control" value="{{ date('m') }}">
                                <input type="hidden" name="location_id" class="form-control" value="{{ $location }}">
                                {!! Form::text('month_value', date('M'), ['class' => 'form-control monthpicker', 'placeholder' => '', 'readonly' => 'readonly']) !!}
                            </div>
                            <div class="col-sm-3">
                                <button type="submit" class="btn btn-danger" id="btn-submit">Mulai</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
    <script>
        $(document).ready(function() {

        $(document).on("click", "#btn-submit", function(e) {

            var _this = $(this);
            var form = _this.parents('form');

            if (form.valid()) {
                Swal.fire({
                    title: 'Konfirmasi', // Opération Dangereuse
                    text: 'Apakah anda yakin melakukan proses Perpindahan Saldo Bulanan?', // Êtes-vous sûr de continuer ?
                    type: 'question',
                    showCancelButton: true,
                    confirmButtonColor: 'null',
                    cancelButtonColor: 'null',
                    confirmButtonClass: 'btn btn-danger',
                    cancelButtonClass: 'btn btn-primary',
                    confirmButtonText: 'Ya, lanjut', // Oui, sûr
                    cancelButtonText: 'Batal', // Annuler
                }).then(res => {
                    if (res.value) {
                       _this.closest("form").submit();
                    }
                });
            }

        });
        
    });
</script>
@stop