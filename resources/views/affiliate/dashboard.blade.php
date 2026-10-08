@extends('layouts.app')

@section('title', 'Espace partenaire — HerveShop')

@section('content')
    <style>
        .affiliate-page{display:grid;gap:18px;max-width:1040px;margin:0 auto}.affiliate-hero{padding:24px;border-radius:18px;color:#fff;background:linear-gradient(125deg,#062b52,#0969ed);box-shadow:0 14px 32px rgba(6,43,82,.18)}.affiliate-hero small{font-size:.68rem;font-weight:800;letter-spacing:.12em;text-transform:uppercase;color:#b9d9ff}.affiliate-hero h1{margin:6px 0;font-size:1.45rem}.affiliate-hero p{max-width:620px;margin:0;color:#e5f1ff;font-size:.82rem}.affiliate-stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px}.affiliate-stat,.affiliate-panel{padding:16px;border:1px solid #e2eaf3;border-radius:14px;background:#fff;box-shadow:0 8px 22px rgba(16,46,85,.06)}.affiliate-stat small{display:block;color:#7187a4;font-size:.66rem}.affiliate-stat strong{display:block;margin-top:5px;color:#102e55;font-size:1.05rem}.affiliate-panel h2{margin:0;color:#102e55;font-size:.94rem}.affiliate-panel>p{margin:5px 0 14px;color:#7187a4;font-size:.7rem}.affiliate-grid{display:grid;grid-template-columns:minmax(0,1.15fr) minmax(280px,.85fr);gap:16px;align-items:start}.affiliate-product{display:grid;grid-template-columns:56px minmax(0,1fr);gap:12px;padding:12px 0;border-top:1px solid #edf2f7}.affiliate-product-art{width:56px;height:56px;object-fit:contain;border-radius:10px;background:#f3f7fc}.affiliate-product-main{min-width:0}.affiliate-product-main strong{display:block;overflow:hidden;color:#25466f;font-size:.74rem;text-overflow:ellipsis;white-space:nowrap}.affiliate-product-main small{display:block;margin:3px 0;color:#7187a4;font-size:.64rem}.affiliate-link-row{display:flex;gap:7px;margin-top:7px}.affiliate-link-row input,.affiliate-form input,.affiliate-form select,.affiliate-form textarea{width:100%;min-width:0;margin:0;padding:9px 10px;border:1px solid #dce8f5;border-radius:8px;color:#25466f;background:#f8fbff;font-size:.68rem}.affiliate-copy{flex:0 0 auto;padding:8px 10px;border:0;border-radius:8px;color:#fff;background:#0969ed;font-size:.64rem;font-weight:700;cursor:pointer}.affiliate-form{display:grid;gap:11px}.affiliate-form label{display:block;margin-bottom:5px;color:#25466f;font-size:.68rem;font-weight:700}.affiliate-form .btn{width:100%}.affiliate-table{width:100%;border-collapse:collapse;font-size:.68rem}.affiliate-table th,.affiliate-table td{padding:10px 8px;border-top:1px solid #edf2f7;text-align:left;vertical-align:top}.affiliate-table th{color:#7187a4;font-weight:700}.affiliate-status{display:inline-block;padding:4px 8px;border-radius:99px;color:#0969ed;background:#eaf4ff;font-size:.6rem;font-weight:700;white-space:nowrap}.affiliate-error{margin:0;padding:9px 11px;border-radius:8px;color:#9b1c2d;background:#fff1f2;font-size:.68rem}.affiliate-empty{padding:14px 0;color:#7187a4;font-size:.7rem}.affiliate-page .pagination{margin-top:12px}@media(max-width:760px){.affiliate-page{gap:12px}.affiliate-hero{padding:19px;border-radius:15px}.affiliate-hero h1{font-size:1.2rem}.affiliate-stats{grid-template-columns:repeat(2,minmax(0,1fr));gap:8px}.affiliate-stat{padding:12px}.affiliate-grid{grid-template-columns:1fr}.affiliate-panel{padding:14px}.affiliate-table{font-size:.62rem}.affiliate-table th,.affiliate-table td{padding:8px 5px}.affiliate-link-row{align-items:stretch}.affiliate-copy{padding-inline:9px}}
    </style>

    <div class="affiliate-page">
        <header class="affiliate-hero">
            <small>Espace partenaire HerveShop</small>
            <h1>Bonjour {{ $partner->name }}</h1>
            <p>Partagez vos liens produits. Vous gagnez 8 % sur le montant des articles (après réduction et hors livraison), une fois le paiement intégral confirmé.</p>
        </header>

        <section class="affiliate-stats" aria-label="Résumé du compte partenaire">
            <div class="affiliate-stat"><small>Commissions gagnées</small><strong>{{ number_format($balances['earned'], 0, ',', ' ') }} F</strong></div>
            <div class="affiliate-stat"><small>Déjà retiré</small><strong>{{ number_format($balances['withdrawn'], 0, ',', ' ') }} F</strong></div>
            <div class="affiliate-stat"><small>Demandes en attente</small><strong>{{ number_format($balances['reserved'], 0, ',', ' ') }} F</strong></div>
            <div class="affiliate-stat"><small>Solde disponible</small><strong>{{ number_format($balances['available'], 0, ',', ' ') }} F</strong></div>
        </section>

        <div class="affiliate-grid">
            <section class="affiliate-panel">
                <h2>Liens à partager</h2>
                <p>Chaque lien contient votre code partenaire. Les ventes attribuées apparaîtront dans votre historique.</p>
                @forelse($products as $product)
                    @php
                        $productUrl = route('products.show', $product->slug, true).'?ref='.urlencode($partner->affiliate_code);
                        $productImage = $product->images->first();
                    @endphp
                    <article class="affiliate-product">
                        <img class="affiliate-product-art" src="{{ $productImage ? asset('storage/'.$productImage->path) : asset('images/logo.png') }}" alt="{{ $product->imageAltText() }}">
                        <div class="affiliate-product-main">
                            <strong>{{ $product->name }}</strong>
                            <small>{{ number_format($product->price, 0, ',', ' ') }} FCFA · Commission indicative : {{ number_format(intdiv((int) $product->price * 8, 100), 0, ',', ' ') }} F</small>
                            <div class="affiliate-link-row">
                                <input type="url" value="{{ $productUrl }}" readonly aria-label="Lien partenaire pour {{ $product->name }}" data-affiliate-link>
                                <button class="affiliate-copy" type="button" data-copy-link>Copier</button>
                            </div>
                        </div>
                    </article>
                @empty
                    <p class="affiliate-empty">Aucun produit n’est disponible au partage pour le moment.</p>
                @endforelse
                {{ $products->links() }}
            </section>

            <div style="display:grid;gap:16px;">
                <section class="affiliate-panel">
                    <h2>Demander un retrait</h2>
                    <p>Minimum 2 500 FCFA. Choisissez Flooz ou TMoney et indiquez le numéro à créditer.</p>
                    @if($errors->any())
                        @foreach($errors->all() as $error)<p class="affiliate-error">{{ $error }}</p>@endforeach
                    @endif
                    <form class="affiliate-form" method="POST" action="{{ route('affiliate.withdrawals.store') }}">
                        @csrf
                        <div><label for="withdrawal-amount">Montant (FCFA)</label><input id="withdrawal-amount" type="number" name="amount" min="2500" max="{{ $balances['available'] }}" step="1" value="{{ old('amount') }}" required></div>
                        <div><label for="withdrawal-method">Moyen de paiement</label><select id="withdrawal-method" name="payment_method" required><option value="">Choisir</option><option value="flooz" @selected(old('payment_method') === 'flooz')>Flooz</option><option value="tmoney" @selected(old('payment_method') === 'tmoney')>TMoney</option></select></div>
                        <div><label for="withdrawal-number">Numéro de paiement</label><input id="withdrawal-number" type="tel" name="payment_number" value="{{ old('payment_number', $partner->whatsapp) }}" maxlength="30" required></div>
                        <button class="btn" type="submit" @disabled($balances['available'] < 2500)>Envoyer la demande</button>
                    </form>
                </section>

                <section class="affiliate-panel">
                    <h2>Mes retraits</h2>
                    @forelse($withdrawals as $withdrawal)
                        <div class="affiliate-product" style="grid-template-columns:minmax(0,1fr) auto;">
                            <div class="affiliate-product-main"><strong>{{ number_format($withdrawal->amount, 0, ',', ' ') }} FCFA · {{ strtoupper($withdrawal->payment_method) }}</strong><small>{{ $withdrawal->created_at->format('d/m/Y') }} · {{ $withdrawal->payment_number }}</small></div>
                            <span class="affiliate-status">{{ match($withdrawal->status) {'pending' => 'En attente', 'approved' => 'Approuvée', 'rejected' => 'Refusée', default => ucfirst($withdrawal->status)} }}</span>
                        </div>
                    @empty
                        <p class="affiliate-empty">Aucune demande de retrait pour le moment.</p>
                    @endforelse
                </section>

                <section class="affiliate-panel">
                    <h2>Dernières commissions</h2>
                    @forelse($commissions as $commission)
                        <div class="affiliate-product" style="grid-template-columns:minmax(0,1fr) auto;">
                            <div class="affiliate-product-main"><strong>Commande {{ $commission->order?->reference ?? '#'.$commission->order_id }}</strong><small>{{ number_format($commission->eligible_amount, 0, ',', ' ') }} F éligibles · {{ $commission->created_at->format('d/m/Y') }}</small></div>
                            <span class="affiliate-status">{{ $commission->status === 'credited' ? '+' : 'Annulée · ' }}{{ number_format($commission->commission_amount, 0, ',', ' ') }} F</span>
                        </div>
                    @empty
                        <p class="affiliate-empty">Les commissions validées apparaîtront ici.</p>
                    @endforelse
                </section>
            </div>
        </div>
    </div>

    <script>
        document.querySelectorAll('[data-copy-link]').forEach((button) => {
            button.addEventListener('click', async () => {
                const input = button.closest('.affiliate-link-row')?.querySelector('[data-affiliate-link]');
                if (!input) return;
                try {
                    await navigator.clipboard.writeText(input.value);
                    button.textContent = 'Copié';
                    window.setTimeout(() => { button.textContent = 'Copier'; }, 1600);
                } catch (error) {
                    input.focus();
                    input.select();
                    button.textContent = 'Sélectionné';
                    window.setTimeout(() => { button.textContent = 'Copier'; }, 1600);
                }
            });
        });
    </script>
@endsection
