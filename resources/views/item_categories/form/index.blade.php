@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="form-group mb-2">
                <a href="{{ route('item-categories.index') }}" class="btn btn-secondary">Kembali ke Daftar Kategori</a>
            </div>
            <div class="card">
                <div class="card-header">{{ $method === 'new' ? 'Buat Kategori Baru' : 'Edit Kategori' }}</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('item-categories.submit', ['method' => $method, 'id' => $category->exists ? $category->id : null]) }}">
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
                        <div class="form-group mb-3">
                            <label for="nama">Nama</label>
                            <input type="text" class="form-control" id="nama" name="nama" maxlength="255" required value="{{ old('nama', $category->nama) }}">
                        </div>
                        <div class="form-group mb-3">
                            <label for="kode">Kode</label>
                            <input type="text" class="form-control" id="kode" name="kode" maxlength="255" required value="{{ old('kode', $category->kode) }}">
                        </div>
                        <button class="btn btn-primary">Simpan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
