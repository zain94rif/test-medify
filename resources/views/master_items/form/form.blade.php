<form method="POST" enctype="multipart/form-data">
    @csrf
    @if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    @if($method == 'edit')
    <div class="form-group">
        <label>Kode Barang</label>
        <input type="text" class="form-control" name="kode_barang" required readonly value="{{$item->kode ?? ''}}">
    </div>
    @endif

    <div class="form-group">
        <label>Nama</label>
        <input type="text" class="form-control" name="nama" required value="{{ old('nama', $item->nama ?? '') }}">
    </div>

    <div class="form-group">
        <label>Harga Beli</label>
        <input type="number" class="form-control" name="harga_beli" required min="0" max="2147483647" step="1" value="{{ old('harga_beli', $item->harga_beli ?? '') }}">
        <small class="form-text text-muted">Masukkan bilangan bulat antara 0 dan 2.147.483.647.</small>
    </div>

    <div class="form-group">
        <label>Laba (dalam persen)</label>
        <input type="number" class="form-control" name="laba" required min="0" max="100" step="1" value="{{ old('laba', $item->laba ?? '') }}">
        <small class="form-text text-muted">Masukkan persentase bilangan bulat antara 0 dan 100.</small>
    </div>

    @php $selected = old('supplier', $item->supplier ?? ''); @endphp
    <div class="form-group">
        <label>Supplier</label>
        <select class="form-control" required name="supplier">
            <option @if($selected == '') selected @endif value="">--Pilih--</option>
            <option @if($selected == 'Tokopaedi') selected @endif>Tokopaedi</option>
            <option @if($selected == 'Bukulapuk') selected @endif>Bukulapuk</option>
            <option @if($selected == 'TokoBagas') selected @endif>TokoBagas</option>
            <option @if($selected == 'E Commurz') selected @endif>E Commurz</option>
            <option @if($selected == 'Blublu') selected @endif>Blublu</option>
        </select>
    </div>

    @php $selected = old('jenis', $item->jenis ?? ''); @endphp
    <div class="form-group">
        <label>Jenis</label>
        <select class="form-control" required name="jenis">
            <option @if($selected == '') selected @endif value="">--Pilih--</option>
            <option @if($selected == 'Obat') selected @endif>Obat</option>
            <option @if($selected == 'Alkes') selected @endif>Alkes</option>
            <option @if($selected == 'Matkes') selected @endif>Matkes</option>
            <option @if($selected == 'Umum') selected @endif>Umum</option>
            <option @if($selected == 'ATK') selected @endif>ATK</option>
        </select>
    </div>

    <div class="form-group">
        <label for="foto">Foto</label>
        <input type="file" class="form-control-file" id="foto" name="foto" accept="image/jpeg,image/png,image/webp">
        <small class="form-text text-muted">Format JPG, PNG, atau WebP. Maksimal 2 MB.</small>
        @if (!empty($item->foto_url))
        <div class="mt-2">
            <img src="{{ $item->foto_url }}" alt="Foto {{ $item->nama }}" class="img-thumbnail" style="max-width: 240px; max-height: 240px;">
            <div class="small text-muted">Kosongkan pilihan foto untuk mempertahankan foto saat ini.</div>
        </div>
        @endif
    </div>

    <button class="btn btn-primary mt-3">Submit</button>

</form>