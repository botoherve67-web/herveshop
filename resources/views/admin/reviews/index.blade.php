@extends('admin.layout')

@section('title', 'Avis clients — Admin')

@section('admin-content')
    <div style="display:flex; justify-content:space-between; align-items:center; gap:16px; flex-wrap:wrap;">
        <h1>Avis clients</h1>
        <form method="GET">
            <select name="status" onchange="this.form.submit()">
                <option value="">Tous les avis</option>
                <option value="pending" @selected(request('status') === 'pending')>À modérer</option>
                <option value="reported" @selected(request('status') === 'reported')>Signalés</option>
                <option value="approved" @selected(request('status') === 'approved')>Approuvés</option>
            </select>
        </form>
    </div>

    @forelse($reviews as $review)
        <article class="card" style="margin-bottom:10px;">
            <div style="display:flex; justify-content:space-between; gap:16px; flex-wrap:wrap;">
                <div>
                    <strong>{{ $review->product->name }}</strong> — {{ $review->user->name }}<br>
                    <span>Note : {{ $review->note }}/5</span>
                    @if($review->is_approved)
                        <span class="badge-precommande">Publié</span>
                    @else
                        <span class="badge-precommande">À modérer</span>
                    @endif
                </div>
                @if($review->report_count)
                    <strong style="color:#a33;">{{ $review->report_count }} signalement{{ $review->report_count > 1 ? 's' : '' }}</strong>
                @endif
            </div>
            <p>{{ $review->commentaire ?: 'Sans commentaire.' }}</p>
            @if($review->report_reason)
                <div class="alert-error"><strong>Motif du signalement :</strong> {{ $review->report_reason }}</div>
            @endif
            <div style="display:flex; gap:8px; flex-wrap:wrap;">
                @if(!$review->is_approved)
                    <form action="{{ route('admin.reviews.approve', $review) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <button class="btn" type="submit">Approuver</button>
                    </form>
                @endif
                @if($review->report_count)
                    <form action="{{ route('admin.reviews.dismiss-report', $review) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <button class="btn outline" type="submit">Classer le signalement</button>
                    </form>
                @endif
                <form action="{{ route('admin.reviews.destroy', $review) }}" method="POST" onsubmit="return confirm('Supprimer cet avis ?');">
                    @csrf
                    @method('DELETE')
                    <button class="btn outline" type="submit">Supprimer</button>
                </form>
            </div>
        </article>
    @empty
        <div class="card">Aucun avis dans cette vue.</div>
    @endforelse

    {{ $reviews->links() }}
@endsection