@extends('layouts.app')

@section('title', 'Confidentialité — HerveShop')
@section('meta_description', 'Informations sur les données personnelles utilisées lors de la navigation, de la commande et de la création d’un compte HerveShop.')
@section('meta_robots', 'noindex,follow')
@section('canonical', route('privacy'))

@section('content')
    <section class="card" style="max-width:900px;margin:0 auto;">
        <span class="eyebrow">Informations</span>
        <h1>Confidentialité et données personnelles</h1>
        <p>Cette page décrit les catégories de données que le site traite pour fonctionner. L’identité juridique du responsable, les durées de conservation et les modalités formelles d’exercice des droits doivent encore être complétées par HerveShop avant publication d’une politique définitive.</p>

        <h2>Données traitées</h2>
        <ul>
            <li>Compte : nom, adresse e-mail, numéro WhatsApp et, si vous les renseignez, date de naissance, genre, ville, adresse et consignes de livraison.</li>
            <li>Commandes : produits, quantités, coordonnées et informations de livraison, moyen de paiement choisi, identifiant de transaction et preuve de paiement envoyée.</li>
            <li>Demandes de contact : nom, adresse e-mail, téléphone facultatif, objet et contenu du message.</li>
            <li>Fonctionnement et mesure d’audience : session et panier, pages consultées, liens cliqués et identifiants techniques pseudonymisés dérivés notamment de l’adresse IP et du navigateur.</li>
            <li>Authentification : Firebase Authentication fournit la connexion par e-mail/mot de passe ou Google. HerveShop reçoit les informations d’identité nécessaires pour associer la session et le compte boutique.</li>
        </ul>

        <h2>Utilisation et prestataires</h2>
        <p>Ces données servent à gérer le compte, préparer et suivre les commandes, confirmer les paiements, livrer les produits, répondre aux demandes et assurer le fonctionnement et la sécurité du site. Certaines données sont traitées par les prestataires techniques configurés pour l’hébergement, l’authentification, le stockage et l’envoi d’e-mails.</p>
        <p>Les preuves de paiement sont stockées dans un espace privé de l’application et ne sont pas destinées à être affichées publiquement. Ne transmettez pas de données sensibles qui ne sont pas nécessaires au traitement d’une commande.</p>

        <h2>Conservation et demandes</h2>
        <p>Une durée de conservation par catégorie et une procédure formelle de demande d’accès, de correction ou de suppression restent à définir. Pour toute question concernant vos données, contactez HerveShop en précisant l’adresse e-mail du compte et l’objet de votre demande. Certaines informations peuvent devoir être conservées pour gérer une commande ou répondre à des obligations applicables; les règles correspondantes doivent être confirmées par le responsable du site.</p>

        <h2>Informations à compléter</h2>
        <ul>
            <li>Nom légal et coordonnées complètes du responsable du traitement.</li>
            <li>Durées de conservation des comptes, commandes, preuves de paiement et journaux techniques.</li>
            <li>Prestataires effectivement utilisés, lieux de traitement et éventuels transferts de données.</li>
            <li>Procédure de demande relative aux données et autorité de contrôle compétente.</li>
        </ul>
        <p><a href="{{ route('contact.index') }}">Contacter HerveShop au sujet de vos données</a></p>
    </section>
@endsection
