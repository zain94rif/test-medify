@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Kategori Items</span>
                    <a href="{{ route('item-categories.form', ['method' => 'new']) }}" class="btn btn-primary">Tambah Kategori</a>
                </div>
                <div class="card-body">
                    @include('item_categories.index.filter')
                    <div class="mt-4">
                        @include('item_categories.index.table')
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
@include('item_categories.index.js')
@endsection
