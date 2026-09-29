@extends('layouts.app')

@section('title', 'Contact — HerveShop')
@section('meta_description', 'Contactez HerveShop pour toute question, commande ou demande de livraison. Nous sommes à votre écoute au Togo.')
@section('canonical', route('contact.index'))

@section('content')
    <section class="contact-hero">
        <div>
            <span class="shop-kicker">Contact</span>
            <h1>Une question ?<br><strong>Nous sommes là pour vous aider.</strong></h1>
            <p>Que ce soit pour une commande, une livraison, un renseignement produit ou un retour, notre équipe reste à votre écoute.</p>
            <div class="hero-actions">
                <a href="tel:{{ preg_replace('/[^0-9+]/', '', $contact['phone']) }}" class="home-btn home-btn-primary">Appeler maintenant&nbsp; →</a>
                <a href="mailto:{{ $contact['email'] }}" class="home-btn home-btn-light"><x-icon name="mail"/> Écrire un email</a>
            </div>
        </div>
        <div class="contact-hero-cards">
            <div class="info-card info-card-highlight">
                <span class="info-icn"><x-icon name="headset" size="24"/></span>
                <strong>{{ $contact['phone'] }}</strong>
                <small>Service client</small>
            </div>
            <div class="info-card">
                <span class="info-icn"><x-icon name="mail" size="24"/></span>
                <strong>{{ $contact['email'] }}</strong>
                <small>Email</small>
            </div>
            <div class="info-card">
                <span class="info-icn"><x-icon name="pin" size="24"/></span>
                <strong>{{ $contact['address'] }}</strong>
                <small>Adresse</small>
            </div>
        </div>
    </section>

    <section class="contact-grid">
        <div class="card contact-card">
            <div class="section-heading compact">
                <div>
                    <span class="eyebrow">Nous contacter</span>
                    <h2>Envoyez-nous un message</h2>
                </div>
            </div>

            <form action="{{ route('contact.store') }}" method="POST" class="contact-form">
                @csrf
                <div class="form-row two-cols">
                    <div>
                        <label for="name">Nom complet</label>
                        <input id="name" type="text" name="name" value="{{ old('name') }}" placeholder="Votre nom" required>
                    </div>
                    <div>
                        <label for="email">Email</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" placeholder="votre@email.com" required>
                    </div>
                </div>

                <div class="form-row two-cols">
                    <div>
                        <label for="phone">Téléphone</label>
                        <input id="phone" type="tel" name="phone" value="{{ old('phone') }}" placeholder="Votre numéro">
                    </div>
                    <div>
                        <label for="subject">Objet</label>
                        <input id="subject" type="text" name="subject" value="{{ old('subject') }}" placeholder="Demande de renseignement" required>
                    </div>
                </div>

                <div>
                    <label for="message">Message</label>
                    <textarea id="message" name="message" rows="6" placeholder="Décrivez votre demande..." required>{{ old('message') }}</textarea>
                </div>

                <button type="submit" class="home-btn home-btn-primary">Envoyer le message</button>
            </form>
        </div>

        <aside class="card contact-sidebar">
            <span class="eyebrow">Informations</span>
            <h3>Pourquoi nous écrire ?</h3>

            <div class="contact-points">
                <div>
                    <span class="icon-wrap"><x-icon name="bag" size="18"/></span>
                    <div>
                        <strong>Commande</strong>
                        <small>Suivi, paiement et confirmation</small>
                    </div>
                </div>
                <div>
                    <span class="icon-wrap"><x-icon name="truck" size="18"/></span>
                    <div>
                        <strong>Livraison</strong>
                        <small>Disponibilité, délais et suivi colis</small>
                    </div>
                </div>
                <div>
                    <span class="icon-wrap"><x-icon name="shield" size="18"/></span>
                    <div>
                        <strong>Service client</strong>
                        <small>Réponses rapides et personnalisées</small>
                    </div>
                </div>
            </div>

            <div class="contact-meta">
                <p><x-icon name="clock" size="16"/> {{ $contact['hours'] }}</p>
                <p><x-icon name="headset" size="16"/> {{ $contact['phone'] }}</p>
                <p><x-icon name="mail" size="16"/> {{ $contact['email'] }}</p>
                <p><x-icon name="pin" size="16"/> {{ $contact['address'] }}</p>
            </div>
        </aside>
    </section>
@endsection
