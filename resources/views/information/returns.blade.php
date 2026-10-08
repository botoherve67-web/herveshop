@extends('layouts.app')

@section('title', 'Retours et remboursements — HerveShop')
@section('meta_description', 'Comment contacter HerveShop au sujet d’un article reçu, d’un retour ou d’un remboursement.')
@section('meta_robots', 'noindex,follow')
@section('canonical', route('returns'))

@section('content')
    <section class="card" style="max-width:900px;margin:0 auto;">
        <span class="eyebrow">Service client</span>
        <h1>Retours et remboursements</h1>
        <p>La procédure détaillée, les délais de retour et les conditions de remboursement ne sont pas encore définis dans les informations actuellement disponibles sur HerveShop. Nous ne souhaitons pas vous annoncer de délai ou de garantie non confirmés.</p>

        <h2>Un problème avec votre commande ?</h2>
        <ol>
            <li>Contactez-nous dès que possible en indiquant votre référence de commande.</li>
            <li>Décrivez le problème et joignez, si utile, des photos de l’article reçu.</li>
            <li>Attendez les instructions de l’équipe avant d’expédier ou de remettre un article.</li>
        </ol>
        <p>Les demandes seront examinées individuellement. L’acceptation d’un retour, les frais éventuels, le mode et le délai de remboursement doivent être confirmés par écrit par le service client avant tout retour.</p>

        <div class="alert-success">
            Cette page ne constitue pas une promesse de remboursement automatique. Les conditions finales doivent être confirmées par HerveShop et publiées ici.
        </div>
        <p><a href="{{ route('contact.index') }}">Contacter le service client</a></p>
    </section>
@endsection
