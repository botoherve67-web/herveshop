@extends('admin.layout')

@section('title', 'Statistiques — HerveShop')

@section('admin-content')
    <h1>Statistiques visiteurs</h1>
    <div class="grid" style="margin-bottom:24px;">
        <div class="card"><strong>Visites</strong><p style="font-size:1.6rem;">{{ $stats['visites'] }}</p></div>
        <div class="card"><strong>Visiteurs uniques</strong><p style="font-size:1.6rem;">{{ $stats['visiteurs'] }}</p></div>
        <div class="card"><strong>Clics suivis</strong><p style="font-size:1.6rem;">{{ $stats['clics'] }}</p></div>
    </div>
    <h2>Pages consultées</h2>
    @forelse($pages as $page)
        <div class="card" style="display:flex;justify-content:space-between;margin-bottom:8px;">
            <span>{{ $page->path }}</span><strong>{{ $page->total }}</strong>
        </div>
    @empty
        <div class="card">Aucune visite enregistrée.</div>
    @endforelse
@endsection
