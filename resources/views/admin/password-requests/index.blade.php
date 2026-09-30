@extends('admin.layout')

@section('title', 'Demandes mot de passe')

@section('admin-content')
    <h1>Demandes mot de passe</h1>
    @forelse($requests as $request)
        <div class="card" style="display:flex;justify-content:space-between;align-items:center;gap:14px;margin-bottom:8px;flex-wrap:wrap;">
            <div><strong>{{ $request->user->name }}</strong><br><small>{{ $request->user->email }} — {{ $request->created_at->format('d/m/Y H:i') }}</small></div>
            <span>{{ ucfirst($request->status) }}</span>
            @if($request->status === 'pending')
                <div style="display:flex;gap:8px;">
                    <form method="POST" action="{{ route('admin.password-requests.approve', $request) }}">@csrf @method('PATCH')<button class="btn" type="submit">Approuver</button></form>
                    <form method="POST" action="{{ route('admin.password-requests.reject', $request) }}">@csrf @method('PATCH')<button class="btn outline" type="submit">Refuser</button></form>
                </div>
            @endif
        </div>
    @empty
        <div class="card">Aucune demande.</div>
    @endforelse
    {{ $requests->links() }}
@endsection
