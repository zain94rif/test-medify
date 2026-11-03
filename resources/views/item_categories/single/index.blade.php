@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="form-group mb-2">
                <a href="{{ route('item-categories.index') }}" class="btn btn-secondary">Kembali ke Daftar Kategori</a>
            </div>
            <div class="card">
                <div class="card-header">Kategori Item</div>
                <div class="card-body">
                    <table class="table table-borderless w-auto mb-4">
                        <tr>
                            <th>Nama</th>
                            <td>:</td>
                            <td>{{ $category->nama }}</td>
                        </tr>
                        <tr>
                            <th>Kode</th>
                            <td>:</td>
                            <td>{{ $category->kode }}</td>
                        </tr>
                    </table>

                    <h5>Item dalam Kategori</h5>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Nama</th>
                                <th>Jenis</th>
                                <th>Harga Beli</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($category->masterItems as $item)
                            <tr>
                                <td>{{ $item->kode }}</td>
                                <td>{{ $item->nama }}</td>
                                <td>{{ $item->jenis }}</td>
                                <td>{{ $item->harga_beli }}</td>
                                <td><a href="{{ url('master-items/view/' . $item->kode) }}" class="btn btn-primary btn-sm">View</a></td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-muted">Belum ada item pada kategori ini.</td></tr>
                            @endforelse
                        </tbody>
                    </table>

                    <a class="btn btn-info" href="{{ route('item-categories.form', ['method' => 'edit', 'id' => $category->id]) }}">Edit</a>
                    <a class="btn btn-success" href="{{ route('item-categories.pdf', $category->id) }}">Download PDF</a>
                    <form method="POST" action="{{ route('item-categories.delete', $category->id) }}" class="d-inline" onsubmit="return confirm('Hapus kategori ini? Hubungan dengan item juga akan dihapus.');">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
