<div id="filter-container">
    <h4>Filter</h4>
    <div class="row">
        <div class="col-4">
            <div class="form-group" id="filter-container">
                <label>Kode</label>
                <input type="text" class="form-control" id="filter-kode">
            </div>
        </div>
        <div class="col-4">
            <div class="form-group" id="filter-container">
                <label>Nama</label>
                <input type="text" class="form-control" id="filter-nama">
            </div>
        </div>
        <div class="col-2">
            <div class="form-group" id="filter-container">
                <label>Harga Min</label>
                <input type="number" class="form-control" id="filter-harga-min" min="0" max="2147483647" step="1">
            </div>
        </div>
        <div class="col-2">
            <div class="form-group" id="filter-container">
                <label>Harga Max</label>
                <input type="number" class="form-control" id="filter-harga-max" min="0" max="2147483647" step="1">
            </div>
        </div>
    </div>
    <button class="btn btn-primary mt-1 btn-get-data">Filter</button>
    <span id="loading-filter" style="display: none;">Loading...</span>
    <div id="filter-errors" class="alert alert-danger mt-2" role="alert" style="display: none;"></div>
</div>
