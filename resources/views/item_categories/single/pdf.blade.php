<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 28px 30px 60px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #222; }
        h1 { font-size: 18px; margin-bottom: 18px; }
        .details { margin-bottom: 20px; }
        .details td { padding: 3px 8px 3px 0; }
        table.items { width: 100%; border-collapse: collapse; }
        .items th, .items td { border: 1px solid #999; padding: 6px; text-align: left; }
        .items th { background: #eee; }
        .footer { position: fixed; bottom: -38px; left: 0; right: 0; text-align: center; font-size: 9px; }
    </style>
</head>
<body>
    <h1>Detail Kategori Item</h1>
    <table class="details">
        <tr><th>Nama kategori</th><td>:</td><td>{{ $category->nama }}</td></tr>
        <tr><th>Kode kategori</th><td>:</td><td>{{ $category->kode }}</td></tr>
    </table>

    <h2>Item dalam Kategori</h2>
    <table class="items">
        <thead>
            <tr>
                <th>No</th>
                <th>Kode</th>
                <th>Nama items</th>
                <th>Jenis</th>
                <th>Supplier</th>
                <th>Harga</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($category->masterItems as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $item->kode }}</td>
                <td>{{ $item->nama }}</td>
                <td>{{ $item->jenis }}</td>
                <td>{{ $item->supplier }}</td>
                <td>{{ number_format($item->harga_beli, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr><td colspan="6">Belum ada item pada kategori ini.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">Dicetak pada {{ $printedAt }}</div>
</body>
</html>
