@extends('admin.layout')

@section('title', 'Commande ' . $order->reference . ' — Admin')

@section('admin-content')
    <h1>Commande {{ $order->reference }}</h1>

    <div class="card">
        <p>Client : <strong>{{ $order->user->name }}</strong> — {{ $order->user->email }} — {{ $order->user->whatsapp }}</p>
        <p>Total : {{ number_format($order->total, 0, ',', ' ') }} FCFA</p>
        <p>Moyen de paiement : <strong>{{ strtoupper($order->moyen_paiement) }}</strong></p>
        <p>Statut du paiement : <strong>{{ ucfirst(str_replace('_', ' ', $order->statut_paiement)) }}</strong></p>
        <p style="display:flex; gap:8px; flex-wrap:wrap;">
            <a href="{{ route('admin.orders.invoice', $order) }}" class="btn outline">Télécharger la facture PDF</a>
            @if(in_array($order->statut_paiement, ['acompte_paye', 'paye'], true))
                <a href="{{ route('admin.orders.receipt', $order) }}" class="btn outline">Télécharger le reçu PDF</a>
            @endif
        </p>
        @if($order->tracking_code)
            <p>Code de suivi : <strong>{{ $order->tracking_code }}</strong></p>
        @endif
        @if($order->remarque_admin)
            <div class="alert-error"><strong>Remarque envoyée au client :</strong><br>{{ $order->remarque_admin }}</div>
        @endif

        <h3>Preuve de paiement</h3>
        @if($order->preuve_paiement_path)
            <p>Transaction : <strong>{{ $order->transaction_id }}</strong> — envoyée le {{ $order->preuve_paiement_envoyee_at?->format('d/m/Y H:i') }}</p>
            <a href="{{ route('admin.orders.payment-proof', $order) }}" target="_blank" class="btn outline">Voir la capture</a>
        @else
            <p>Aucune preuve de paiement envoyée.</p>
        @endif

        @if($order->type === 'precommande')
            <p>Acompte : {{ number_format($order->montant_acompte, 0, ',', ' ') }} FCFA —
               Solde : {{ number_format($order->montant_solde, 0, ',', ' ') }} FCFA
               (échéance {{ $order->solde_echeance_at?->format('d/m/Y H:i') }})</p>
        @endif

        <h3>Articles</h3>
        @foreach($order->items as $item)
            <p>{{ $item->product_name }} × {{ $item->quantity }} — {{ number_format($item->unit_price * $item->quantity, 0, ',', ' ') }} FCFA</p>
        @endforeach

        <h3>Livraison</h3>
        <p>{{ $order->mode_livraison === 'domicile' ? 'À domicile — '.$order->adresse_livraison : 'Point de retrait — '.$order->point_retrait }}</p>

        <h3>Historique</h3>
        @forelse($order->statusHistory as $history)
            <div style="border-left:3px solid var(--vert); padding-left:10px; margin-bottom:10px;">
                <strong>{{ ucfirst($history->type) }}</strong> : {{ $history->ancienne_valeur ? ucfirst(str_replace('_', ' ', $history->ancienne_valeur)).' → ' : '' }}{{ ucfirst(str_replace('_', ' ', $history->nouvelle_valeur)) }}<br>
                <small>{{ $history->created_at->format('d/m/Y H:i') }} — {{ $history->user?->name ?? 'Système' }}</small>
                @if($history->remarque)<p style="margin:4px 0 0;">{{ $history->remarque }}</p>@endif
            </div>
        @empty
            <p>Aucun changement enregistré.</p>
        @endforelse

        <div style="display:flex; gap:24px; flex-wrap:wrap; margin-top:16px;">
            <form action="{{ route('admin.orders.statut', $order) }}" method="POST">
                @csrf
                @method('PATCH')
                <label>Statut commande</label>
                <select name="statut">
                    @foreach(['en_attente','confirmee','en_preparation','expediee','livree','annulee'] as $s)
                        <option value="{{ $s }}" @selected($order->statut === $s)>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                    @endforeach
                </select>
                <label>Remarque (obligatoire en cas d'annulation)</label>
                <textarea name="remarque_admin" rows="3" maxlength="2000" placeholder="Expliquez l'annulation au client..."></textarea>
                <label>Code de suivi (facultatif, généré à l'expédition)</label>
                <input type="text" name="tracking_code" value="{{ $order->tracking_code }}" maxlength="100" placeholder="Ex : HS-TRK-AB12CD34">
                <button type="submit" class="btn">Enregistrer le statut</button>
            </form>

            <form action="{{ route('admin.orders.paiement', $order) }}" method="POST">
                @csrf
                @method('PATCH')
                <label>Statut paiement</label>
                <select name="statut_paiement">
                    @foreach(['en_attente','acompte_paye','paye','echec'] as $s)
                        <option value="{{ $s }}" @selected($order->statut_paiement === $s)>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                    @endforeach
                </select>
                <label>Remarque (obligatoire en cas d'échec)</label>
                <textarea name="remarque_admin" rows="3" maxlength="2000" placeholder="Expliquez le rejet de la preuve..."></textarea>
                <button type="submit" class="btn">Enregistrer le paiement</button>
            </form>
        </div>
    </div>
@endsection
