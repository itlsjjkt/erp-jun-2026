@section('content')
    <div>
        <form class="email"
            action="{{ route('purchasing.po.email', ['id' => $po->id]) }}"
            method="POST"
            id="form-email">
            @csrf
            <h5 class="text-center" style="text-decoration:underline; font-weight:bold;">{{$po->doc_no}}</h5>
            <div class="row">
                <div class="col-sm-10">
                    <div class="row" style="color">
                        <h6 class="col-sm-2">Supplier</h6>
                        <h6 class="col-sm-9">
                            : {{$po->supplier}}
                        </h6>
                    </div>
                    <div class="row">
                        <h6 class="col-sm-2">PIC</h6>
                        <h6 class="col-sm-9">
                            : {{ $po->picTitle }} {{ $po->picName }}
                        </h6>
                    </div>
                    <div class="row">
                        <h6 class="col-sm-2">Lampiran</h6>
                        <h6 class="col-sm-9">
                            :
                            <a title="Show Attachment" target="_blank" href="{{route('purchasing.po.Attachment_po',Hashids::encode($po->id))}}"><span class="fa fa-paperclip text-danger">..</span></a>
                        </h6>
                    </div>
                </div>
                <div class="col-sm-2">
                    <div class="clearfix">
                        <a title="Kirim" type="submit" data-toggle="tooltip" class="btn-primary btn-email float-right text-white px-3 py-1" style="border-radius:4px; cursor:pointer;">
                            <span class="fa fa-paper-plane" style="font-weight: bold;"></span> <span style="margin-left: 5px;">Kirim</span>
                        </a>
                    </div>
                </div>
            </div>
            <hr>
            <div class="row" style="padding: 5px;width:100%;">
                {{-- WHATSAPP --}}
                {{-- <div class="card" style="background-color: #f3f3f3;padding:15px;width:90%;margin: 0 auto;">
                    <div class="d-flex justify-content-between align-items-center">
                        <label class="mb-0" style="font-weight:bold;">No. Whatsapp</label>
                        <small>
                            <a class="btn_add_whatsapp btn-success text-white px-1 py-1" style="border-radius:4px; cursor:pointer;" title="Tambah Whatsapp">
                                <span class="ti-plus" style="font-weight: bold;"></span> Whatsapp
                            </a>
                        </small>
                    </div>
                    @php
                        $telpRaw = $po->picTelp ?? '';
                        $telpProtected = str_replace('||', '##DELIM##', $telpRaw);
                        $telpCleaned = preg_replace('/[^0-9#A-Z]/i', '', $telpProtected);
                        $telpCleaned = str_replace('##DELIM##', '||', $telpCleaned);
                        $whatsapp_numbers = explode('||', $telpCleaned);
                    @endphp
                    @foreach ($whatsapp_numbers as $whatsapp)
                        @php
                            $cleaned = ltrim(trim($whatsapp), '0');
                            $cleaned = '62' . $cleaned;
                        @endphp
                        @if($cleaned !== '62')
                        <div class="row mt-2 input-whatsapp">
                            <div class="col-sm-10" style="display: flex; align-items: center;">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text">+</div>
                                    </div>
                                    <input type="number" name="whatsapp[]" class="form-control whatsapp" placeholder="Whatsapp" value="{{ $cleaned }}">
                                </div>
                            </div>
                            <div class="col-sm-2" style="text-align:right;">
                                <button type="button" class="btn btn-danger btn_remove_whatsapp" title="Delete Whatsapp">
                                    <i class="ti-trash"></i>
                                </button>
                            </div>
                        </div>
                        @endif
                    @endforeach
                </div> --}}
                <!-- Email Tujuan -->
                <div class="card mt-3" style="background-color: #f3f3f3; padding:15px;width:90%;margin: 0 auto;">
                    <div class="d-flex justify-content-between align-items-center">
                        <label class="mb-0" style="font-weight:bold;">Email Tujuan</label>
                        <small>
                            <a class="btn_add_email_to bg-success text-white px-1 py-1" style="border-radius:4px; cursor:pointer;" title="Tambah Email Tujuan">
                                <span class="ti-plus" style="font-weight: bold;"></span> Email Tujuan
                            </a>
                        </small>
                    </div>
                    @foreach(explode(';', $po->picEmail) as $email)
                        @if(trim($email) !== '')
                            <div class="row mt-2 input-email_to-row">
                                <div class="col-sm-10">
                                    <input class="form-control email_to" type="email" name="email_to[]" value="{{ trim($email) }}" required>
                                </div>
                                <div class="col-sm-2" style="text-align:right;">
                                    <button type="button" class="btn btn-danger btn_remove_add_email" title="Delete Email">
                                        <i class="ti-trash"></i>
                                    </button>
                                </div>
                            </div>
                        @endif
                    @endforeach
                    <div id="form_add_email_to"></div>
                </div>
                <!-- CC Email -->
                <div class="card mt-3" style="background-color: #f3f3f3; padding:15px;width:90%;margin: 0 auto;">
                    <div class="d-flex justify-content-between align-items-center">
                        <label class="mb-0"style="font-weight:bold;">CC Email</label>
                        <small>
                            <a class="btn_add_cc bg-success text-white px-1 py-1" style="border-radius:4px; cursor:pointer;" title="Tambah CC Email">
                                <span class="ti-plus" style="font-weight: bold;"></span> CC Email
                            </a>
                        </small>
                    </div>
                    <div class="row mt-2 input-cc-row">
                        <div class="col-sm-10">
                            <small>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text">Purchaser</div>
                                    </div>
                                    <input class="form-control cc_email" name="cc_email[]" type="email" value="{{ $po->emailPurchaser }}" required>
                                </div>
                            </small>
                        </div>
                        <div class="col-sm-2" style="text-align: right;">
                            <small>
                                <button type="button" class="btn btn-danger btn_remove_cc" title="Delete CC Email">
                                    <i class="ti-trash"></i>
                                </button>
                            </small>
                        </div>
                    </div>
                    @foreach ($cc_emails as $cc)
                        <div class="row mt-2 input-cc-row">
                            <div class="col-sm-10">
                                <small>
                                    <input class="form-control cc_email" name="cc_email[]" type="email" value="{{ $cc->email }}" required>
                                </small>
                            </div>
                            <div class="col-sm-2" style="text-align: right;">
                                <small>
                                    <button type="button" class="btn btn-danger btn_remove_cc" title="Delete CC Email">
                                        <i class="ti-trash"></i>
                                    </button>
                                </small>
                            </div>
                        </div>
                    @endforeach
                    <div id="form_add_cc"></div>
                </div>
            </div>
            <input name="id" type="hidden" value="{{ $po->id }}">
        </form>
    </div>
    <script>
        $(document).ready(function () {
            // Tambah CC Email
            $(document).off("click", ".btn_add_cc").on("click", ".btn_add_cc", function (e) {
                e.preventDefault();
                var row = $('<div>', { class: 'row mt-2 input-cc-row' });
                var inputCol = $('<div>', { class: 'col-sm-10' }).append(
                    $('<input>', {
                        type: 'email',
                        class: 'form-control cc_email',
                        name: 'cc_email[]',
                        placeholder: 'Add CC Email',
                        autocomplete: 'off',
                        required:''
                    })
                );
                var buttonCol = $('<div>', { class: 'col-sm-2',style: 'text-align:right' }).append(
                    $('<button>', {
                        type: 'button',
                        class: 'btn btn-danger btn_remove_cc',
                        title: 'Delete CC Email'
                    }).append($('<i>', { class: 'ti-trash' }))
                );
                row.append(inputCol).append(buttonCol);
                $('#form_add_cc').append(row);
            });
            $(document).off("click", ".btn_remove_cc").on("click", ".btn_remove_cc", function () {
                $(this).closest('.input-cc-row').remove();
            });

            // Tambah Email Tujuan
            $(document).off("click", ".btn_add_email_to").on("click", ".btn_add_email_to", function (e) {
                e.preventDefault();
                var row = $('<div>', { class: 'row mt-2 input-email_to-row',style: 'text-align:right' });
                var inputCol = $('<div>', { class: 'col-sm-10' }).append(
                    $('<input>', {
                        type: 'email',
                        class: 'form-control email_to',
                        name: 'email_to[]',
                        placeholder: 'Tambah Email Tujuan',
                        autocomplete: 'off',
                        required:''
                    })
                );
                var buttonCol = $('<div>', { class: 'col-sm-2' }).append(
                    $('<button>', {
                        type: 'button',
                        class: 'btn btn-danger btn_remove_add_email',
                        title: 'Delete Email'
                    }).append($('<i>', { class: 'ti-trash' }))
                );
                row.append(inputCol).append(buttonCol);
                $('#form_add_email_to').append(row);
            });
            $(document).off("click", ".btn_remove_add_email").on("click", ".btn_remove_add_email", function () {
                $(this).closest('.input-email_to-row').remove();
            });

            $(document).on('input', 'input[name="whatsapp[]"]', function () {
                let val = $(this).val();
                // Jika diawali 0 dan panjangnya lebih dari 1
                if (val.length > 1 && val.startsWith('0')) {
                    val = val.replace(/^0+/, ''); // Hapus nol di depan
                    $(this).val(val);
                }
            });

            // Tambah No. Whatsapp
            $(document).off("click", ".btn_add_whatsapp").on("click", ".btn_add_whatsapp", function (e) {
                e.preventDefault();
                var row = $('<div>', { class: 'row mt-2 input-whatsapp' });
                var inputCol = $('<div>', {
                    class: 'col-sm-10',
                    style: 'display: flex; align-items: center;'
                }).append(
                    $('<div>', { class: 'input-group' }).append(
                        $('<div>', { class: 'input-group-prepend' }).append(
                            $('<div>', { class: 'input-group-text', text: '+' })
                        ),
                        $('<input>', {
                            type: 'number',
                            name: 'whatsapp[]',
                            class: 'form-control',
                            placeholder: 'Whatsapp'
                        })
                    )
                );
                var buttonCol = $('<div>', {
                    class: 'col-sm-2',
                    style: 'text-align: right;'
                }).append(
                    $('<button>', {
                        type: 'button',
                        class: 'btn btn-danger btn_remove_whatsapp',
                        title: 'Delete Whatsapp'
                    }).append(
                        $('<i>', { class: 'ti-trash' })
                    )
                );
                row.append(inputCol).append(buttonCol);
                $('.card .btn_add_whatsapp').closest('.card').append(row);
            });
            $(document).off("click", ".btn_remove_whatsapp").on("click", ".btn_remove_whatsapp", function () {
                $(this).closest('.input-whatsapp').remove();
            });

            // SUBMIT
            $(document).on('click', ".btn-email", function(e) {
                var _this = $(this);
                var form = _this.parents('form');
                e.preventDefault();

                // WHATSAPP
                var wa = $('.whatsapp');
                let allWa = true;
                wa.each(function () {
                    let valwa = $(this).val().trim();
                    if (valwa.length < 10 || valwa.length > 15) {
                        allWa = false;
                        $(this).css('border', '2px solid red');
                    } else {
                        $(this).css('border', '');
                    }
                });
                if (!allWa) {
                    Swal.fire(
                        'Peringatan',
                        'Panjang karakter no WhatsApp minimal 10 dan maksimal 15',
                        'warning'
                    );
                    return false;
                }

                // EMAIL TUJUAN
                var email_tujuan = $('.email_to');
                if (email_tujuan.length < 1) {
                    Swal.fire(
                        'Informasi',
                        'Email Tujuan Minimal 1 Email',
                        'warning'
                    );
                    return false;
                }
                let allValid = true;
                email_tujuan.each(function () {
                    let val = $(this).val().trim();
                    let pattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (val === '' || !pattern.test(val)) {
                        allValid = false;
                        $(this).css('border', '2px solid red');
                    } else {
                        $(this).css('border', '');
                    }
                });
                if (!allValid) {
                    Swal.fire(
                        'Peringatan',
                        'Semua Email Tujuan harus terisi dan berformat valid',
                        'warning'
                    );
                    return false;
                }

                // EMAIL CC
                var cc_email = $('.cc_email');
                if (cc_email.length < 1) {
                    Swal.fire(
                        'Informasi',
                        'Email CC Minimal 1 Email',
                        'warning'
                    );
                    return false;
                }
                let allValidcc = true;
                cc_email.each(function () {
                    let valcc = $(this).val().trim();
                    let pattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

                    if (valcc === '' || !pattern.test(valcc)) {
                        allValidcc = false;
                        $(this).css('border', '2px solid red');
                    } else {
                        $(this).css('border', '');
                    }
                });
                if (!allValidcc) {
                    Swal.fire(
                        'Peringatan',
                        'Semua Email CC harus terisi dan berformat valid',
                        'warning'
                    );
                    return false;
                }

                // SWALLFIRE
                Swal.fire({
                    title: 'Konfirmasi',
                    text: 'Apakah anda yakin untuk mengirimkan email untuk PO ini?',
                    type: 'question',
                    showCancelButton: true,
                    confirmButtonColor: 'null',
                    cancelButtonColor: 'null',
                    confirmButtonClass: 'btn btn-primary',
                    cancelButtonClass: 'btn btn-danger',
                    confirmButtonText: 'Ya, kirim',
                    cancelButtonText: 'Batal',
                }).then(res => {
                    if (res.value) {
                        Swal.fire({
                            title: 'Sending Email',
                            html: 'Don\'t refresh or close your browser until process is completed',
                            showConfirmButton: false,
                            allowOutsideClick: false,
                            width: '700px',
                            onBeforeOpen: () => {
                                Swal.showLoading();
                            },
                        });
                        _this.closest("form").submit();
                    }
                });
            });
        });
    </script>
@endsection
