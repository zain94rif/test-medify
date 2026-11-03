<script src="https://code.jquery.com/jquery-3.5.1.js"></script>
<script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.12.1/js/dataTables.bootstrap5.min.js"></script>

<script>
    var start_date = '';
    var end_date = '';
    var data_per_fetch = 500;
    var data_fetched = 0;

    $(document).ready(function() {
        $('#table').DataTable({
            searching: false,
            order: [[1, 'desc']],
        });
        getData()
    });

    $('.btn-get-data').click(function() {
        getData()
    })

    function getData(){
        
        $('#loading-filter').show();
        var dataTableObj = $('#table').DataTable();
        var filter_kode = $('#filter-kode').val()
        var filter_nama = $('#filter-nama').val()
        var filter_harga_min = $('#filter-harga-min').val()
        var filter_harga_max = $('#filter-harga-max').val()
        dataTableObj.clear().draw();
        $('#filter-errors').empty().hide();

        $.ajax({
            url: '{{url("master-items/search")}}',
            dataType: 'json',
            tryCount: 0,
            retryLimit: 3,
            data: {
                kode: filter_kode,
                nama: filter_nama,
                hargamin: filter_harga_min,
                hargamax: filter_harga_max
            },
            success: function(results) {
                var data = results.data

                $.each(data, function(index, item) {
                    var harga_jual = item.harga_beli + item.harga_beli * item.laba / 100;
                    harga_jual = Math.round(harga_jual)
                    var kode = item.kode;

                    var html = `<a href="{{url('master-items/view/')}}/` + kode + `" class="btn btn-primary">View</a>`
                    var foto = item.foto_url
                        ? `<img src="${escapeHtml(item.foto_url)}" alt="Foto item" class="img-thumbnail" style="max-width: 72px; max-height: 72px;">`
                        : '<span class="text-muted">-</span>';

                    dataTableObj.row.add([
                        foto,
                        escapeHtml(item.kode),
                        escapeHtml(item.nama),
                        escapeHtml(item.jenis),
                        item.categories.map(function(category) {
                            return escapeHtml(category.nama);
                        }).join(', ') || '-',
                        item.harga_beli,
                        harga_jual,
                        escapeHtml(item.supplier),
                        html
                    ]).draw(true);
                });
                $('#loading-filter').hide();
                $('#filter-errors').empty().hide();
            },
            error: function(xhr, textStatus, errorThrown) {
                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    var $errors = $('<ul class="mb-0"></ul>');
                    $.each(xhr.responseJSON.errors, function(field, messages) {
                        $.each(messages, function(index, message) {
                            $errors.append($('<li></li>').text(message));
                        });
                    });
                    $('#filter-errors').empty().append($errors).show();
                    $('#loading-filter').hide();
                    return;
                }

                this.tryCount++;
                if (this.tryCount <= this.retryLimit) {
                    $.ajax(this);
                    return;
                }
                alert('Terjadi kesalahan server, tidak dapat mengambil data')
                $('#loading-filter').hide();

                return;
            }
        })
    }

    function escapeHtml(value) {
        return String(value).replace(/[&<>"']/g, function(character) {
            return {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            }[character];
        });
    }
</script>