@extends('admin.layout')

@section('title', 'Catégories — Admin')

@section('admin-content')
    <h1>Catégories</h1>

    <form action="{{ route('admin.categories.store') }}" method="POST" class="card" style="max-width:400px; margin-bottom:20px;">
        @csrf
        <label>Nouvelle catégorie</label>
        <input type="text" name="name" required>
        <button type="submit" class="btn">Ajouter</button>
    </form>

    @foreach($categories as $cat)
        <div class="card" style="margin-bottom:8px;">
            <strong>{{ $cat->name }}</strong> — {{ $cat->products_count }} produits
        </div>
    @endforeach
@endsection
