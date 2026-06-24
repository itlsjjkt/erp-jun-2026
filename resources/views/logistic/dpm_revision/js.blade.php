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
    function convertDateTimeDBtoIndo(string) {
        bulanIndo = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September' , 'Oktober', 'November', 'Desember'];
        date = string.split(" ")[0];
        time = string.split(" ")[1];
        tanggal = date.split("-")[2];
        bulan = date.split("-")[1];
        tahun = date.split("-")[0];
        return tanggal + " " + bulanIndo[Math.abs(bulan)] + " " + tahun + " Jam " + time;
    }

    $("#datepicker_" + i).datepicker({
        todayHighlight: 'TRUE',
        autoclose: true,
    });

    $('#product_' + i).select2().on('change', function() {
        $(this).valid();
    });

    $('#product_' + i).select2({
        width: 'resolve',
        placeholder: 'Cari produk dengan mengetik Nama Produk...',
        minimumInputLength: 2,
        ajax: {
            url: "{{ route('master.get_products') }}/" + $('#category_id').val(),
            dataType: 'json',
            delay: 250,
            processResults: function(data) {
                return {
                    results: $.map(data, function(item) {
                        if(item.part_number === '' || item.part_number == null){
                            var part_number = " [" + item.code + " - " + item.brand + "]";
                        }else{
                            var part_number = " [" + item.part_number + "] ["+ item.code + "] [" + item.brand + "]";
                        }
                        return {
                            id: item.id,
                            text: item.name + part_number,
                            measure: item.measure,
                            code: item.code,
                            description: item.description,
                            item: item.item_id
                        }
                    })
                };
            },
            cache: false
        }
    }).on('change', function() {
        var product_id      = $('#product_' + i).val();
        var location_id     = $('#location_id').val();
      
        $('#measure_' + i).show();
        $('.product_' + i).css("background-color", "#fff");

        var item = $('#product_' + i).select2('data')[0].item;
        var code = $('#product_' + i).select2('data')[0].code;
        var measure = $('#product_' + i).select2('data')[0].measure;
        var description = $('#product_' + i).select2('data')[0].description;

        $('#measure_' + i).val(measure);
        $('#measure_text_' + i).text(measure);
        $('#description_' + i).val(description);
        $('#input_qty_' + i).removeAttr('readonly');
        $('#input_qty_' + i).val('');
        $('#preview_qty_' + i).text('');

        var stock_min, checkStockMin, stock_max, checkStockMax, stock_onhand, checkStockOnhand,
                updated_at, checkUpdated, infoStock, warningStock = "";

        $.ajax({
                url: "{{ route('logistic.get_stock_product') }}/" + product_id + "/" + location_id,
                type: 'GET',
                cache: false,
                success: function (data) {

                    stock_min = data.stock_min;
                    checkStockMin = (stock_min !== null && stock_min !== "" && stock_min !== undefined) ? stock_min : "-";
                    stock_max = data.stock_max;
                    checkStockMax = (stock_max !== null && stock_max !== "" && stock_max !== undefined) ? stock_max : "-";
                    stock_onhand = data.stock_onhand;
                    checkStockOnhand = (stock_onhand !== null && stock_onhand !== "" && stock_onhand !== undefined) ? stock_onhand : "-";
                    updated_at = data.updated_at;
                    checkUpdated = (updated_at !== null && updated_at !== "" && updated_at !== undefined) ? " <br /><small>(Update terakhir : "+  convertDateTimeDBtoIndo(updated_at)+")</small>" : " <small>(Tidak ada di Inventory)</small>";
                    infoStock = "Min: "+checkStockMin+", Max: "+checkStockMax+", Onhand: " + checkStockOnhand + checkUpdated;

                    $('.info_stock_' + i).html(infoStock);
                    $('#stock_min_' + i).val(stock_min);
                    $('#stock_max_' + i).val(stock_max);
                    $('#stock_onhand_' + i).val(stock_onhand);
                }
        });
    });

    $('#input_qty_' + i).on('keyup', function() {
        var location_id = $('#location_id').val();
        var stock_min = parseInt($('#stock_min_' + i).val());
        var stock_max = parseInt($('#stock_max_' + i).val());
        var stock_onhand = parseInt($('#stock_onhand_' + i).val());
        var qty = parseInt($(this).val());
        if (stock_min === 0 && stock_max === 0) {
            $(this).valid();
        }
        else if (stock_min > 0 && stock_max > 0) {
            if (stock_onhand <= stock_max) {
                var residual = stock_max - stock_onhand;
                if (qty != null && qty != undefined) {
                    if (qty <= residual) {
                        $(this).valid();
                    } else {
                        $('#input_qty_'+ i).val("");
                        Swal.fire({
                            title: 'Kesalahan',
                            html: 'QTY/Satuan tidak boleh melebihi <span style="font-weight: bold;">' + residual + '</span><br/> (stock max dikurangi stock on hand)',
                            type: 'error',
                        });
                    }
                } else {
                    $('#input_qty_' + i).val("");
                    Swal.fire(
                        'Kesalahan',
                        'QTY/Satuan tidak boleh kosong',
                        'error'
                    );
                }
            } else {
                $('#input_qty_' + i).val("");
                Swal.fire(
                    'Kesalahan',
                    'Stock onhand sudah melebihi stock max',
                    'error'
                );
            }
        }
    });


});
</script>
