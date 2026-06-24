@extends('layouts.app')

@section('page-header')
    Go To Inventory
@endsection

@section('breadcrumbs')
<ol class="breadcrumb">
    <li class="breadcrumb-item">
        <a href="{{ route(ADMIN . '.dashboard') }}">Home</a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">
        Go To Inventory
    </li>
</ol>
@endsection

@section('content')
<div class="row mB-40">
    <div class="col-sm-12">
        <div class="bgc-white p-20 bd">

            <div class="alert alert-info">
                <strong>INFO !</strong>
                SILAHKAN PILIH LOKASI INVENTORY SESUAI DATA INVENTORY YANG AKAN DI CARI.
            </div>

            <hr>

            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="company_id">Company</label>
                        <select name="company_id" id="company_id" class="form-control select2" required>
                            @foreach ($company as $id => $name)
                                <option value="{{ $id }}" {{ request('company_id') == $id ? 'selected' : '' }}>
                                    {{ strtoupper($name) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="location-search">Cari Lokasi</label>
                        <input type="text" id="location-search" class="form-control" placeholder="Cari lokasi...">
                    </div>
                </div>
            </div>

            <div class="row d-none" id="locations-container">
                <div class="col-md-6 mb-4">
                    <h6 style="text-decoration: underline;">LOKASI AKTIF</h6>
                    <div id="active-location-cards" class="row"></div>
                </div>

                <div class="col-md-6 mb-4">
                    <h6 style="text-decoration: underline;">LOKASI TIDAK AKTIF</h6>
                    <div id="inactive-location-cards" class="row"></div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@section('js')
<script>
$(document).ready(function () {

    $('#company_id').select2({ placeholder: 'PILIH COMPANY', width: '100%' });

    $('#company_id').on('change', function () {
        let companyId = $(this).val();

        $('#active-location-cards, #inactive-location-cards').empty();

        if (!companyId) {
            $('#locations-container').addClass('d-none');
            return;
        }

        $.ajax({
            url: "{{ route('logistic.inventory-get-locations') }}",
            type: "GET",
            data: { company_id: companyId },
            success: function (data) {

                $('#locations-container').removeClass('d-none');

                if (!data.length) {
                    $('#active-location-cards, #inactive-location-cards')
                        .html('<p>Tidak ada lokasi untuk company ini.</p>');
                    return;
                }

                $.each(data, function(index, loc) {
                    let card = $(`
                        <div class="col-md-6 col-lg-6 mb-3 location-wrapper">
                            <div class="card h-100 location-card" data-id="${loc.id}">
                                <div class="card-body text-center">
                                    <h6 class="card-title">${loc.name}</h6>
                                </div>
                            </div>
                        </div>
                    `);

                    if (loc.status == 1) {
                        $('#active-location-cards').append(card);
                    } else {
                        $('#inactive-location-cards').append(card);
                    }

                    setTimeout(function() {
                        card.addClass('show');
                    }, 50);
                });

                $('.location-card').on('click', function () {
                    let locationId = $(this).data('id');
                    let url = "{{ route('logistic.inventory.index') }}" +
                              "?company_id=" + companyId + "&location_id=" + locationId;
                    window.location.href = url;
                });
            },
            error: function (xhr) {
                let message = xhr.responseJSON?.message || xhr.responseText || 'Terjadi kesalahan';
                alert('Error ' + xhr.status + ' : ' + message);
            }
        });
    });

    $('#location-search').on('keyup', function() {
        let searchTerm = $(this).val().toLowerCase();
        $('.location-card').each(function() {
            let cardText = $(this).find('.card-title').text().toLowerCase();
            if (cardText.indexOf(searchTerm) > -1) {
                $(this).parent().show();
            } else {
                $(this).parent().hide();
            }
        });
    });

});
</script>

<style>
.location-wrapper {
    opacity: 0;
    transform: translateY(10px);
    transition: all 0.5s ease;
}

.location-wrapper.show {
    opacity: 1;
    transform: translateY(0);
}

.location-card {
    border: 1px solid slategray;
    border-radius: none;
    cursor: pointer;
    background-color: white;
    transition: transform 0.2s, box-shadow 0.2s, border-color 0.2s;
}

.location-card:hover {
    transform: scale(1.005);
    box-shadow: 0 0 15px rgba(0,0,0,0.3);
    background-color: #c7ffb983;
}

.location-card .card-body {
    padding: 5px;
}
</style>
@stop
