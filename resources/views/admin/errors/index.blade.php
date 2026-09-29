@extends('admin.layout')

@section('title', 'Journal des erreurs — Admin')

@section('admin-content')
    <div style="display:flex; justify-content:space-between; align-items:center; gap:16px; flex-wrap:wrap;">
        <h1>Journal des erreurs</h1>
        <form method="GET" style="display:flex; gap:8px;">
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Message, classe ou URL">
            <button type="submit" class="btn">Rechercher</button>
        </form>
    </div>

    @forelse($errors as $error)
        <details class="card" style="margin-bottom:8px;">
            <summary style="cursor:pointer;">
                <strong>{{ $error->exception_class }}</strong>
                — {{ $error->created_at->format('d/m/Y H:i:s') }}
                — {{ $error->method }} {{ $error->url }}
            </summary>
            <p>{{ $error->message }}</p>
            <small>Utilisateur : {{ $error->user?->email ?? 'Invité' }} — IP : {{ $error->ip_address ?: 'inconnue' }}</small>
            @if($error->trace)
                <pre style="white-space:pre-wrap; overflow:auto; margin-top:12px;">{{ $error->trace }}</pre>
            @endif
        </details>
    @empty
        <div class="card">Aucune erreur enregistrée.</div>
    @endforelse

    {{ $errors->links() }}
@endsection