@extends('layouts.app')

@section('title', 'Commande ' . $order->reference)

@section('content')
    @php
        $statusSteps = [
            'en_attente' => 'En attente',
            'confirmee' => 'Confirmee',
            'en_preparation' => 'Preparation',
            'expediee' => 'Expediee',
            'livree' => 'Livree',
        ];
        $currentIndex = array_search($order->statut, array_keys($statusSteps), true);
        $currentIndex = $currentIndex === false ? 0 : $currentIndex;
    @endphp

    <style>
        .order-layout{display:grid;grid-template-columns:minmax(0,1fr) 330px;gap:18px}.order-panel{padding:16px;border:1px solid #e3edf8;border-radius:8px;background:#fff;box-shadow:0 8px 24px rgba(28,78,128,.06)}.order-top{display:flex;justify-content:space-between;gap:16px;align-items:flex-start;margin-bottom:16px}.order-top h1{margin:0}.order-top p{margin:4px 0;color:#7187a4;font-size:.72rem}.order-badges{display:flex;gap:8px;flex-wrap:wrap}.order-badge{display:inline-flex;padding:5px 9px;border-radius:999px;background:#eaf4ff;color:#0969ed;font-size:.62rem;font-weight:700}.order-badge.pay{background:#fff8e9;color:#a06400}.timeline{display:grid;grid-template-columns:repeat(5,1fr);gap:8px;margin:14px 0 4px}.timeline-step{position:relative;padding-top:20px;text-align:center;color:#8aa0bb;font-size:.58rem;font-weight:700}.timeline-step:before{content:'';position:absolute;top:5px;left:50%;width:10px;height:10px;margin-left:-5px;border-radius:50%;background:#cbd8e6}.timeline-step:after{content:'';position:absolute;top:9px;left:0;width:100%;height:2px;background:#dce8f5;z-index:0}.timeline-step:first-child:after{left:50%;width:50%}.timeline-step:last-child:after{width:50%}.timeline-step.is-active,.timeline-step.is-done{color:#0969ed}.timeline-step.is-active:before,.timeline-step.is-done:before{background:#0969ed}.timeline-step.is-done:after{background:#0969ed}.order-items{display:grid;gap:8px}.order-item{display:flex;justify-content:space-between;gap:12px;padding:10px 0;border-bottom:1px solid #edf2f7;font-size:.72rem}.order-money p{display:flex;justify-content:space-between;margin:8px 0;color:#52606d;font-size:.72rem}.order-money strong{color:#173f5f}.history{display:grid;gap:9px}.history-item{display:grid;grid-template-columns:12px minmax(0,1fr);gap:10px}.history-dot{width:10px;height:10px;margin-top:5px;border-radius:50%;background:#0969ed}.history-copy strong{display:block;color:#173f5f;font-size:.68rem}.history-copy small{color:#7187a4;font-size:.58rem}.history-copy p{margin:4px 0 0;color:#52606d;font-size:.62rem}.pay-box{padding:12px;border-radius:8px;background:#eff7ff;color:#25466f;font-size:.68rem}.pay-form{margin-top:14px}.pay-form label{display:block;margin-top:10px;font-size:.65rem;font-weight:700;color:#25466f}.pay-form input{margin-top:5px}.doc-actions{display:flex;gap:8px;flex-wrap:wrap}.admin-note{padding:12px;border-radius:8px;background:#fff0f2;color:#8a1f2d;font-size:.68rem}@media(max-width:850px){.order-layout{grid-template-columns:1fr}.timeline{grid-template-columns:1fr}.timeline-step{text-align:left;padding:6px 0 6px 24px}.timeline-step:before{left:5px;top:10px}.timeline-step:after{left:9px;top:0;width:2px;height:100%}.timeline-step:first-child:after,.timeline-step:last-child:after{left:9px;width:2px;height:50%}}
    </style>

    <div class="order-top">
        <div>
            <h1>Commande {{ $order->reference }}</h1>
            <p>Enregistree le {{ $order->created_at->format('d/m/Y H:i') }}</p>
        </div>
        <div class="order-badges">
            <span class="order-badge">{{ ucfirst(str_replace('_', ' ', $order->statut)) }}</span>
            <span class="order-badge pay">{{ ucfirst(str_replace('_', ' ', $order->statut_paiement)) }}</span>
        </div>
    </div>

    <div class="order-layout">
        <section style="display:grid;gap:16px;">
            <div class="order-panel">
                <h2>Suivi commande</h2>
                <div class="timeline">
                    @foreach($statusSteps as $key => $label)
                        @php $index = $loop->index; @endphp
                        <span class="timeline-step {{ $index < $currentIndex ? 'is-done' : '' }} {{ $index === $currentIndex ? 'is-active' : '' }}">{{ $label }}</span>
                    @endforeach
                </div>

                @if($order->tracking_code)
                    <div class="pay-box" style="margin-top:14px;">
                        <strong>Code de suivi :</strong> {{ $order->tracking_code }}
                    </div>
                @endif

                @if($order->remarque_admin)
                    <div class="admin-note" style="margin-top:14px;">
                        <strong>Message admin</strong><br>{{ $order->remarque_admin }}
                    </div>
                @endif
            </div>

            <div class="order-panel">
                <h2>Articles</h2>
                <div class="order-items">
                    @foreach($order->items as $item)
                        <div class="order-item">
                            <span>{{ $item->product_name }} x {{ $item->quantity }}</span>
                            <strong>{{ number_format($item->unit_price * $item->quantity, 0, ',', ' ') }} FCFA</strong>
                        </div>
                    @endforeach
                </div>
                <div class="order-money">
                    <p><span>Sous-total</span><span>{{ number_format($order->sous_total, 0, ',', ' ') }} FCFA</span></p>
                    @if($order->reduction > 0)
                        <p><span>Reduction {{ $order->code_promo }}</span><span>-{{ number_format($order->reduction, 0, ',', ' ') }} FCFA</span></p>
                    @endif
                    <p><span>Livraison</span><span>{{ number_format($order->frais_livraison, 0, ',', ' ') }} FCFA</span></p>
                    <p><strong>Total</strong><strong>{{ number_format($order->total, 0, ',', ' ') }} FCFA</strong></p>
                </div>
            </div>

            <div class="order-panel">
                <h2>Historique</h2>
                <div class="history">
                    <div class="history-item">
                        <span class="history-dot"></span>
                        <div class="history-copy"><strong>Commande creee</strong><small>{{ $order->created_at->format('d/m/Y H:i') }}</small></div>
                    </div>
                    @if($order->preuve_paiement_envoyee_at)
                        <div class="history-item">
                            <span class="history-dot"></span>
                            <div class="history-copy"><strong>Preuve de paiement envoyee</strong><small>{{ $order->preuve_paiement_envoyee_at->format('d/m/Y H:i') }}</small></div>
                        </div>
                    @endif
                    @forelse($order->statusHistory->sortBy('created_at') as $history)
                        <div class="history-item">
                            <span class="history-dot"></span>
                            <div class="history-copy">
                                <strong>{{ ucfirst($history->type) }} : {{ ucfirst(str_replace('_', ' ', $history->nouvelle_valeur)) }}</strong>
                                <small>{{ $history->created_at->format('d/m/Y H:i') }}</small>
                                @if($history->remarque)<p>{{ $history->remarque }}</p>@endif
                            </div>
                        </div>
                    @empty
                    @endforelse
                </div>
            </div>
        </section>

        <aside style="display:grid;gap:16px;">
            <div class="order-panel">
                <h2>Paiement</h2>
                <div class="pay-box">
                    Flooz : +228 96 29 20 39<br>
                    TMoney : +228 93 84 33 57<br>
                    Moyen choisi : {{ strtoupper($order->moyen_paiement) }}
                </div>

                @if($order->preuve_paiement_path)
                    <p style="font-size:.72rem;">Preuve envoyee le {{ $order->preuve_paiement_envoyee_at?->format('d/m/Y H:i') }}<br>Transaction : <strong>{{ $order->transaction_id }}</strong></p>
                @else
                    <form action="{{ route('orders.payment-proof', $order) }}" method="POST" enctype="multipart/form-data" class="pay-form">
                        @csrf
                        <label for="transaction_id">Identifiant transaction</label>
                        <input id="transaction_id" type="text" name="transaction_id" required maxlength="100" placeholder="Ex : TXN123456789">
                        <label for="preuve_paiement">Capture paiement</label>
                        <input id="preuve_paiement" type="file" name="preuve_paiement" accept="image/jpeg,image/png,image/webp" required>
                        <button type="submit" class="btn" style="margin-top:12px;width:100%;">Envoyer preuve</button>
                    </form>
                @endif
            </div>

            @if($order->type === 'precommande')
                <div class="order-panel">
                    <h2>Precommande</h2>
                    <p style="font-size:.72rem;">Acompte : {{ number_format($order->montant_acompte, 0, ',', ' ') }} FCFA</p>
                    <p style="font-size:.72rem;">Solde : {{ number_format($order->montant_solde, 0, ',', ' ') }} FCFA</p>
                    @if($order->solde_echeance_at)<p style="font-size:.72rem;">Echeance : {{ $order->solde_echeance_at->format('d/m/Y H:i') }}</p>@endif
                </div>
            @endif

            <div class="order-panel">
                <h2>Livraison</h2>
                <p style="font-size:.72rem;">
                    {{ $order->mode_livraison === 'domicile' ? 'Livraison a domicile' : 'Point de retrait' }}<br>
                    {{ $order->adresse_livraison ?? $order->point_retrait }}
                </p>
            </div>

            <div class="order-panel">
                <h2>Documents</h2>
                <div class="doc-actions">
                    <a href="{{ route('orders.invoice', $order) }}" class="btn outline">Facture PDF</a>
                    @if(in_array($order->statut_paiement, ['acompte_paye', 'paye'], true))
                        <a href="{{ route('orders.receipt', $order) }}" class="btn outline">Recu PDF</a>
                    @endif
                </div>
            </div>
        </aside>
    </div>
@endsection
