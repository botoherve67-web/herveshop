@extends('layouts.app')

@section('title', 'Conditions de vente — HerveShop')
@section('meta_description', 'Résumé des modalités de commande, de précommande et de paiement actuellement proposées par HerveShop.')
@section('meta_robots', 'noindex,follow')
@section('canonical', route('terms'))

@section('content')
    <section class="card" style="max-width:900px;margin:0 auto;">
        <span class="eyebrow">Informations</span>
        <h1>Conditions de vente</h1>
        <p>Cette page résume les modalités actuellement visibles dans le parcours de commande. Elle est provisoire et ne remplace pas des conditions générales de vente complètes, qui doivent être validées par le responsable de HerveShop avant publication définitive.</p>

        <h2>Produits et commande</h2>
        <p>Les produits sont proposés selon leur disponibilité et les informations affichées sur leur fiche. Le récapitulatif de commande indique les articles, quantités, réductions éventuelles, frais de livraison et total avant confirmation. Une référence de commande est ensuite créée; le statut de paiement demeure en attente tant que le paiement n’a pas été vérifié.</p>

        <h2>Paiement</h2>
        <p>Le site propose actuellement un paiement manuel par Flooz ou TMoney. Après la commande, le client effectue le paiement et transmet l’identifiant de transaction ainsi qu’une preuve depuis le détail de la commande. Les coordonnées affichées pour le paiement sont celles du parcours de commande; vérifiez-les avant tout transfert.</p>

        <h2>Précommandes</h2>
        <p>Lorsqu’un article est indiqué en précommande, un acompte peut être demandé. Son montant est défini par article et affiché dans le parcours d’achat. Le solde est prévu sous 48 heures après l’arrivée du produit, selon l’échéance indiquée dans le détail de commande.</p>

        <h2>Livraison et retraits</h2>
        <p>Les frais actuellement configurés sont indiqués dans la <a href="{{ route('delivery') }}">page Livraison et retrait</a> et doivent être vérifiés dans le récapitulatif de commande. Le retrait n’est pas facturé dans le calcul actuel; son lieu doit être confirmé avec l’équipe.</p>

        <h2>Retours, annulations et litiges</h2>
        <p>Les règles détaillées d’annulation, de retour, de remboursement, de garanties, de réclamations et de règlement des litiges restent à formaliser. Consultez la page <a href="{{ route('returns') }}">Retours et remboursements</a> et demandez une confirmation écrite au service client avant toute action.</p>

        <h2>Informations à compléter avant publication définitive</h2>
        <ul>
            <li>Identité juridique du vendeur, adresse complète et coordonnées officielles.</li>
            <li>Règles d’acceptation et de confirmation des commandes, annulation et disponibilité des stocks.</li>
            <li>Conditions, délais et frais de livraison, retours, remboursements et garanties.</li>
            <li>Informations fiscales, droit applicable et procédure de traitement des litiges.</li>
            <li>Date d’entrée en vigueur et version de ces conditions.</li>
        </ul>
        <p><a href="{{ route('contact.index') }}">Contacter HerveShop pour une question sur une commande</a></p>
    </section>
@endsection
