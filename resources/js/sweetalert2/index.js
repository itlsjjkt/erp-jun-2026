import * as $ from 'jquery';
import Swal from 'sweetalert2';

export default (function() {
    $(document).on('click', "form.delete button", function(e) {
        var _this = $(this);
        e.preventDefault();
        Swal.fire({
            title: 'Konfirmasi', // Opération Dangereuse
            text: 'Apakah anda yakin untuk menghapus Data?', // Êtes-vous sûr de continuer ?
            type: 'error',
            showCancelButton: true,
            confirmButtonColor: 'null',
            cancelButtonColor: 'null',
            confirmButtonClass: 'btn btn-danger',
            cancelButtonClass: 'btn btn-primary',
            confirmButtonText: 'Ya, hapus!', // Oui, sûr
            cancelButtonText: 'Batal', // Annuler
        }).then(res => {
            if (res.value) {
                _this.closest("form").submit();
            }
        });
    });

    $(document).on('click', "#btn-submit", function(e) {
        var _this = $(this);
        var form = _this.parents('form');

        form.validate({
            onfocusout: false,
            invalidHandler: function(form, validator) {
                var errors = validator.numberOfInvalids();
                if (errors) {
                    validator.errorList[0].element.focus();
                }
            }
        });

        e.preventDefault();
        if (form.valid()) {
            Swal.fire({
                title: 'Konfirmasi', // Opération Dangereuse
                text: 'Apakah anda yakin melanjutkan ini?', // Êtes-vous sûr de continuer ?
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

}())