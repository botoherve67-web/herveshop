@extends('layouts.app')

@section('title', 'Espace livreur — HerveShop')

@section('content')
    <style>
        .courier-page{display:grid;gap:16px;max-width:960px;margin:0 auto}.courier-hero{padding:23px;border-radius:17px;color:#fff;background:linear-gradient(125deg,#062b52,#0969ed)}.courier-hero small{color:#b9d9ff;font-size:.66rem;font-weight:800;letter-spacing:.12em;text-transform:uppercase}.courier-hero h1{margin:5px 0;font-size:1.35rem}.courier-hero p{margin:0;color:#e5f1ff;font-size:.75rem}.courier-panel{padding:16px;border:1px solid #e2eaf3;border-radius:14px;background:#fff;box-shadow:0 8px 22px rgba(16,46,85,.06)}.courier-panel h2{margin:0 0 12px;color:#102e55;font-size:.94rem}.courier-card{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:12px;align-items:center;padding:14px 0;border-top:1px solid #edf2f7}.courier-card:first-of-type{border-top:0}.courier-card h3{margin:0;color:#25466f;font-size:.78rem}.courier-card p{margin:4px 0;color:#7187a4;font-size:.67rem}.courier-card address{margin-top:7px;color:#466383;font-size:.67rem;font-style:normal}.courier-card-items{margin-top:7px;color:#6681a4;font-size:.64rem}.courier-action{display:grid;gap:7px;min-width:130px}.courier-action .btn,.courier-action button{width:100%;font-size:.66rem}.courier-status{display:inline-block;padding:4px 8px;border-radius:99px;color:#0969ed;background:#eaf4ff;font-size:.59rem;font-weight:700}.courier-error{padding:9px 11px;border-radius:8px;color:#9b1c2d;background:#fff1f2;font-size:.68rem}.courier-empty{padding:14px 0;color:#7187a4;font-size:.7rem}@media(max-width:640px){.courier-page{gap:12px}.courier-hero{padding:19px}.courier-hero h1{font-size:1.2rem}.courier-panel{padding:14px}.courier-card{grid-template-columns:1fr;gap:10px}.courier-action{grid-template-columns:1fr;min-width:0}.courier-card h3{font-size:.74rem}}
    </style>

    <div class="courier-page">
        <header class="courier-hero">
            <small>Espace livreur HerveShop</small>
            <h1>Bonjour {{ Auth::user()->name }}</h1>
            <p>Réclamez une livraison disponible pour vous l’attribuer, puis confirmez-la une fois remise au client.</p>
        </header>

        @if($errors->any())
            @foreach($errors->all() as $error)<p class="courier-error">{{ $error }}</p>@endforeach
        @endif
        @if(session('success'))<p class="courier-status">{{ session('success') }}</p>@endif

        <section class="courier-panel">
            <h2>Livraisons disponibles · {{ $availableOrders->count() }}</h2>
            @forelse($availableOrders as $order)
                <article class="courier-card">
                    <div>
                        <h3>Commande {{ $order->reference }} <span class="courier-status">{{ $order->statusLabel() }}</span></h3>
                        <p>{{ $order->user?->name }} · {{ $order->user?->whatsapp ?: 'Téléphone non renseigné' }}</p>
                        <address>{{ $order->adresse_livraison ?: $order->zone_livraison ?: 'Adresse à confirmer avec le client' }}</address>
                        <div class="courier-card-items">@foreach($order->items as $item){{ $item->product_name }} × {{ $item->quantity }}@if(!$loop->last) · @endif @endforeach</div>
                    </div>
                    <div class="courier-action">
                        <strong>{{ number_format($order->total, 0, ',', ' ') }} FCFA</strong>
                        <form method="POST" action="{{ route('courier.orders.claim', $order) }}">
                            @csrf
                            <button class="btn" type="submit">Réclamer cette livraison</button>
                        </form>
                    </div>
                </article>
            @empty
                <p class="courier-empty">Aucune livraison n’est disponible actuellement.</p>
            @endforelse
        </section>

        <section class="courier-panel">
            <h2>Mes livraisons en cours · {{ $myOrders->count() }}</h2>
            @forelse($myOrders as $order)
                <article class="courier-card">
                    <div>
                        <h3>Commande {{ $order->reference }} <span class="courier-status">{{ $order->statusLabel() }}</span></h3>
                        <p>{{ $order->user?->name }} · {{ $order->user?->whatsapp ?: 'Téléphone non renseigné' }}</p>
                        <address>{{ $order->adresse_livraison ?: $order->zone_livraison ?: 'Adresse à confirmer avec le client' }}</address>
                        <div class="courier-card-items">@foreach($order->items as $item){{ $item->product_name }} × {{ $item->quantity }}@if(!$loop->last) · @endif @endforeach</div>
                    </div>
                    <div class="courier-action">
                        <strong>{{ number_format($order->total, 0, ',', ' ') }} FCFA</strong>
                        <form method="POST" action="{{ route('courier.orders.complete', $order) }}">
                            @csrf
                            @method('PATCH')
                            <button class="btn" type="submit">Confirmer la livraison</button>
                        </form>
                    </div>
                </article>
            @empty
                <p class="courier-empty">Vous n’avez pas de livraison en cours.</p>
            @endforelse
        </section>

        <section class="courier-panel">
            <h2>Livraisons terminées</h2>
            @forelse($completedOrders as $order)
                <article class="courier-card">
                    <div><h3>Commande {{ $order->reference }}</h3><p>{{ $order->user?->name }} · {{ $order->updated_at->format('d/m/Y') }}</p></div>
                    <span class="courier-status">Livrée</span>
                </article>
            @empty
                <p class="courier-empty">Vos livraisons effectuées apparaîtront ici.</p>
            @endforelse
        </section>
    </div>
@endsection
