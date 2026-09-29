@php($logo = resource_path('images/logo.png'))
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #1f2933; font-size: 12px; }
        .header { border-bottom: 3px solid #2a9d8f; padding-bottom: 18px; margin-bottom: 28px; }
        .brand { color: #173f5f; font-size: 25px; font-weight: bold; }
        .muted { color: #627d98; }
        .right { text-align: right; }
        table { width: 100%; border-collapse: collapse; margin-top: 22px; }
        th { background: #173f5f; color: white; text-align: left; padding: 9px; }
        td { border-bottom: 1px solid #d9e2ec; padding: 9px; }
        .total { margin-top: 20px; margin-left: auto; width: 260px; }
        .total td { border: 0; padding: 5px; }
        .grand-total { color: #173f5f; font-size: 15px; font-weight: bold; border-top: 2px solid #2a9d8f !important; }
    </style>
</head>
<body>
    <div class="header">
        <table style="margin:0;">
            <tr>
                <td style="border:0; padding:0;"><div class="brand"><img src="data:image/png;base64,{{ base64_encode(file_get_contents($logo)) }}" alt="HerveShop" style="height:38px;width:auto;vertical-align:middle;margin-right:8px;">HerveShop</div><div class="muted">Facture client</div></td>
                <td style="border:0; padding:0;" class="right"><strong>Facture</strong><br>{{ $order->reference }}<br>{{ $order->created_at?->format('d/m/Y') }}</td>
            </tr>
        </table>
    </div>
    <table style="margin:0;">
        <tr>
            <td style="border:0; padding:0;"><strong>Client</strong><br>{{ $order->user->name }}<br>{{ $order->user->email }}<br>{{ $order->user->address }}</td>
            <td style="border:0; padding:0;" class="right"><strong>Livraison</strong><br>{{ $order->mode_livraison === 'domicile' ? $order->adresse_livraison : $order->point_retrait }}</td>
        </tr>
    </table>
    <table>
        <thead><tr><th>Article</th><th>Quantité</th><th>Prix unitaire</th><th>Total</th></tr></thead>
        <tbody>
            @foreach($order->items as $item)
                <tr><td>{{ $item->product_name }}</td><td>{{ $item->quantity }}</td><td>{{ number_format($item->unit_price, 0, ',', ' ') }} FCFA</td><td>{{ number_format($item->unit_price * $item->quantity, 0, ',', ' ') }} FCFA</td></tr>
            @endforeach
        </tbody>
    </table>
    <table class="total">
        <tr><td>Sous-total</td><td class="right">{{ number_format($order->sous_total, 0, ',', ' ') }} FCFA</td></tr>
        <tr><td>Livraison</td><td class="right">{{ number_format($order->frais_livraison, 0, ',', ' ') }} FCFA</td></tr>
        <tr><td>Réduction</td><td class="right">-{{ number_format($order->reduction, 0, ',', ' ') }} FCFA</td></tr>
        <tr><td class="grand-total">Total</td><td class="right grand-total">{{ number_format($order->total, 0, ',', ' ') }} FCFA</td></tr>
    </table>
    <p class="muted" style="margin-top:40px;">Merci pour votre confiance. HerveShop.</p>
</body>
</html>