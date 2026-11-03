<div id="category-filter-container">
    <h4>Filter</h4>
    <div class="row">
        <div class="col-md-4">
            <label for="filter-kode-kategori">Kode</label>
            <input type="text" class="form-control" id="filter-kode-kategori">
        </div>
        <div class="col-md-8">
            <label for="filter-nama-kategori">Nama</label>
            <input type="text" class="form-control" id="filter-nama-kategori">
        </div>
    </div>
    <button class="btn btn-primary mt-3" id="btn-get-category-data">Filter</button>
    <span id="loading-category-filter" style="display: none;">Loading...</span>
    <div id="category-filter-errors" class="alert alert-danger mt-2" role="alert" style="display: none;"></div>
</div>
