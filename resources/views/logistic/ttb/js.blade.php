<script>

$(document).ready(function() {

    var i = "{{ $id }}";

    function isEmpty(obj) {
        for (var key in obj) {
            if (obj.hasOwnProperty(key))
                return true;
        }
        return false;
    }

    $('#product_' + i).select2().on('change', function() {
        $(this).valid();
    });

    $('#product_' + i).select2({
        width: 'resolve',
        placeholder: 'Cari produk dengan mengetik Nama Produk...',
        minimumInputLength: 2,
        ajax: {
            url: "{{ route('logistic.inventory.get_data')}}/" + $('#location_id').val(),
            dataType: 'json',
            delay: 250,
            processResults: function(data) {
                return {
                    results: $.map(data, function(item) {
                        if(item.part_number === '' || item.part_number == null){
                            var text = item.code + " - " + item.name;
                        }else{
                            var text = item.code + " - " + item.name + "<br><small>" + item.part_number + "</small>";
                        }
                        return {
                            id: item.id,
                            text: text,
                            rack: item.rack,
                            measure: item.unit,
                            stock: item.stock
                        }
                    })
                };
            },
            cache: false
        },
        escapeMarkup: function (text) { return text; }
    }).on('change', function() {
        var product_id      = $('#product_' + i).val();

        var stock = $('#product_' + i).select2('data')[0].stock;
        var measure = $('#product_' + i).select2('data')[0].measure;
        var rack = $('#product_' + i).select2('data')[0].rack;

        $('#rack_' + i).text(rack);
        $('#measure_' + i).text(measure);
        $('#qty_stock_' + i).val(stock);
        $('#preview_qty_stock_' + i).text(stock);

    });

    $('#qty_ttb_' + i).on('keyup', function(e) {
        var qty_ttb  = $('#qty_ttb_' + i).val();
        var qty_stock = $('#qty_stock_' + i).val();
        if(parseInt(qty_ttb) > parseInt(qty_stock)){
            e.preventDefault();
            Swal.fire(
            'Peringatan!',
                'QTY TTB tidak boleh melebihi QTY STOCK',
                'warning'
            );
            $('#qty_ttb_' + i).val('');
        }
    });

});
</script>
