@extends('layouts.app')

@section('title', 'Livraison et retrait — HerveShop')
@section('meta_description', 'Modes, zones et frais de livraison actuellement proposés par HerveShop au Togo.')
@section('meta_robots', 'noindex,follow')
@section('canonical', route('delivery'))

@section('content')
    <section class="card" style="max-width:900px;margin:0 auto;">
        <span class="eyebrow">Service client</span>
        <h1>Livraison et retrait</h1>
        <p>Les options et frais affichés au moment de la commande font foi. Vérifiez le récapitulatif avant de confirmer votre achat.</p>

        <h2>Livraison à domicile</h2>
        <p>Le site propose actuellement les zones et frais suivants :</p>
        <ul>
            <li>Lomé centre : 1 000 FCFA</li>
            <li>Lomé périphérie : 1 500 FCFA</li>
            <li>Intérieur du Togo : 3 000 FCFA</li>
            <li>Hors Togo : 5 000 FCFA</li>
        </ul>
        <p>Les frais sont ajoutés au total affiché lors de la commande. Indiquez une adresse suffisamment précise; l’équipe peut vous contacter pour confirmer les modalités et la disponibilité de la livraison dans votre zone.</p>

        <h2>Point de retrait</h2>
        <p>Le retrait est proposé sans frais de livraison dans le calcul actuel. Le lieu et les modalités doivent être convenus avec l’équipe avant le déplacement; le site ne publie pas de liste officielle de points de retrait.</p>

        <h2>Délais et précommandes</h2>
        <p>Un délai de livraison garanti n’est pas défini dans le système. Contactez l’équipe pour confirmer le délai estimé avant de finaliser si votre date de réception est importante.</p>
        <p>Pour une précommande, l’échéance de solde affichée par le site est de 48 heures après l’arrivée du produit. Le montant de l’acompte peut varier selon le produit et les montants confirmés dans le récapitulatif de commande prévalent.</p>

        <p><a href="{{ route('contact.index') }}">Contacter le service client au sujet d’une livraison</a></p>
    </section>
@endsection
