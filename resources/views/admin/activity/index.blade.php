@extends('admin.layout')

@section('title', 'Historique administrateur — Admin')

@section('admin-content')
    <div style="display:flex; justify-content:space-between; align-items:center; gap:16px; flex-wrap:wrap;">
        <h1>Historique des actions administrateur</h1>
        <form method="GET" style="display:flex; gap:8px;">
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Action, route ou administrateur">
            <button type="submit" class="btn">Rechercher</button>
        </form>
    </div>

    @forelse($logs as $log)
        <div class="card" style="margin-bottom:8px;">
            <strong>{{ $log->user?->name ?? 'Système' }}</strong>
            — <code>{{ $log->action }}</code>
            @if($log->subject_type)
                — {{ class_basename($log->subject_type) }} #{{ $log->subject_id }}
            @endif
            <br>
            <small>{{ $log->method }} {{ $log->route }} — {{ $log->created_at->format('d/m/Y H:i:s') }} — IP {{ $log->ip_address ?: 'inconnue' }}</small>
        </div>
    @empty
        <div class="card">Aucune action administrateur enregistrée.</div>
    @endforelse

    {{ $logs->links() }}
@endsection