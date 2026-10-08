@extends('admin.layout')

@section('title', 'Retraits partenaires — Admin')

@section('admin-content')
    <style>
        .withdrawal-heading{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:16px}.withdrawal-heading h1{margin:0;color:#102e55;font-size:1.2rem}.withdrawal-count{padding:6px 10px;border-radius:99px;color:#0969ed;background:#eaf4ff;font-size:.68rem;font-weight:800}.withdrawal-list{display:grid;gap:11px}.withdrawal-card{padding:15px;border:1px solid #e2eaf3;border-radius:12px;background:#fff;box-shadow:0 8px 22px rgba(16,46,85,.05)}.withdrawal-top{display:flex;justify-content:space-between;gap:10px;align-items:start}.withdrawal-top h2{margin:0;color:#173f5f;font-size:.82rem}.withdrawal-top p,.withdrawal-details{margin:5px 0 0;color:#7187a4;font-size:.67rem}.withdrawal-status{padding:4px 8px;border-radius:99px;color:#0969ed;background:#eaf4ff;font-size:.59rem;font-weight:800;white-space:nowrap}.withdrawal-status.approved{color:#14753a;background:#e9f8ef}.withdrawal-status.rejected{color:#a52232;background:#fff0f1}.withdrawal-form{display:grid;grid-template-columns:minmax(0,1fr) auto auto;gap:8px;align-items:end;margin-top:12px}.withdrawal-form label{display:block;margin-bottom:4px;color:#466383;font-size:.62rem;font-weight:700}.withdrawal-form textarea{width:100%;min-height:38px;resize:vertical;padding:8px 9px;border:1px solid #dce8f5;border-radius:7px;font:inherit;font-size:.66rem}.withdrawal-form .btn{min-height:38px;padding:7px 11px;font-size:.64rem}.withdrawal-form .btn.reject{border:1px solid #f0c6ca;color:#a52232;background:#fff}.withdrawal-empty{padding:22px;color:#7187a4;text-align:center;font-size:.72rem}@media(max-width:640px){.withdrawal-heading{align-items:flex-start;flex-direction:column}.withdrawal-form{grid-template-columns:1fr 1fr}.withdrawal-form>div:first-child{grid-column:1/-1}.withdrawal-form button{width:100%}}
    </style>

    <div class="withdrawal-heading">
        <div><h1>Retraits partenaires</h1><p style="margin:5px 0 0;color:#7187a4;font-size:.68rem;">Contrôlez le solde du partenaire avant chaque approbation.</p></div>
        <span class="withdrawal-count">{{ $pendingCount }} en attente</span>
    </div>

    @if(session('success'))
        <div class="card" style="margin-bottom:12px;color:#14753a;background:#e9f8ef;">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        @foreach($errors->all() as $error)<div class="card" style="margin-bottom:8px;color:#a52232;background:#fff0f1;">{{ $error }}</div>@endforeach
    @endif

    <div class="withdrawal-list">
        @forelse($requests as $withdrawal)
            <article class="withdrawal-card">
                <div class="withdrawal-top">
                    <div>
                        <h2>{{ $withdrawal->partner?->name ?? 'Partenaire supprimé' }} · {{ number_format($withdrawal->amount, 0, ',', ' ') }} FCFA</h2>
                        <p>{{ $withdrawal->partner?->email }} · Demande du {{ $withdrawal->created_at->format('d/m/Y à H:i') }}</p>
                    </div>
                    <span class="withdrawal-status {{ $withdrawal->status }}">{{ match($withdrawal->status) {'pending' => 'En attente', 'approved' => 'Approuvée', 'rejected' => 'Refusée', default => ucfirst($withdrawal->status)} }}</span>
                </div>
                <p class="withdrawal-details"><strong>Paiement :</strong> {{ strtoupper($withdrawal->payment_method) }} · {{ $withdrawal->payment_number }}</p>
                @if($withdrawal->admin_note)<p class="withdrawal-details"><strong>Note :</strong> {{ $withdrawal->admin_note }}</p>@endif

                @if($withdrawal->status === 'pending')
                    <form class="withdrawal-form" method="POST" action="{{ route('admin.affiliate-withdrawals.review', $withdrawal) }}">
                        @csrf
                        @method('PATCH')
                        <div><label for="note-{{ $withdrawal->id }}">Note interne (facultative)</label><textarea id="note-{{ $withdrawal->id }}" name="admin_note" maxlength="1000">{{ old('admin_note') }}</textarea></div>
                        <button class="btn" type="submit" name="decision" value="approve">Approuver</button>
                        <button class="btn reject" type="submit" name="decision" value="reject">Rejeter</button>
                    </form>
                @else
                    <p class="withdrawal-details">Traité le {{ $withdrawal->reviewed_at?->format('d/m/Y à H:i') ?? '—' }}</p>
                @endif
            </article>
        @empty
            <div class="withdrawal-card withdrawal-empty">Aucune demande de retrait pour le moment.</div>
        @endforelse
    </div>

    {{ $requests->links() }}
@endsection
