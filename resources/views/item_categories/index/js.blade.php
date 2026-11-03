<script src="https://code.jquery.com/jquery-3.5.1.js"></script>
<script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.12.1/js/dataTables.bootstrap5.min.js"></script>
<script>
    $(function() {
        $('#category-table').DataTable({
            searching: false,
            order: [[0, 'asc']]
        });
        loadCategories();
        $('#btn-get-category-data').on('click', loadCategories);
    });

    function loadCategories() {
        var table = $('#category-table').DataTable();
        $('#loading-category-filter').show();
        $('#category-filter-errors').empty().hide();
        $.getJSON('{{ route("item-categories.search") }}', {
            kode: $('#filter-kode-kategori').val(),
            nama: $('#filter-nama-kategori').val()
        }).done(function(response) {
            table.clear();
            $.each(response.data, function(index, category) {
                var actions = '<a class="btn btn-primary btn-sm" href="{{ url("item-categories/view") }}/' + category.id + '">View</a> '
                    + '<a class="btn btn-info btn-sm" href="{{ url("item-categories/form/edit") }}/' + category.id + '">Edit</a>';
                table.row.add([
                    escapeCategoryHtml(category.kode),
                    escapeCategoryHtml(category.nama),
                    category.master_items_count,
                    actions
                ]);
            });
            table.draw();
        }).fail(function(xhr) {
            var message = 'Terjadi kesalahan server, tidak dapat mengambil data.';
            if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                message = Object.values(xhr.responseJSON.errors).flat().join(' ');
            }
            $('#category-filter-errors').text(message).show();
        }).always(function() {
            $('#loading-category-filter').hide();
        });
    }

    function escapeCategoryHtml(value) {
        return $('<div>').text(value).html();
    }
</script>
