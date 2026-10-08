@extends('layouts.app')

@section('title', 'Inscription')

@section('content')
    <h1>Créer un compte</h1>

    <form action="{{ route('firebase.session') }}" method="POST" class="card" style="max-width:400px;" data-firebase-auth="register">
        @csrf
        <label for="account_type">Type de compte</label>
        <select id="account_type" name="account_type">
            <option value="customer">Client — acheter sur HerveShop</option>
            <option value="courier">Livreur — effectuer des livraisons</option>
            <option value="partner">Partenaire — partager des produits et gagner 8 %</option>
        </select>
        <p data-role-description style="margin:6px 0 14px; color:#687184; font-size:.78rem;">Vous pourrez acheter, suivre vos commandes et enregistrer vos favoris.</p>
        <button type="button" class="btn outline" style="width:100%; margin-bottom:12px;" data-firebase-google>
            <svg aria-hidden="true" width="18" height="18" viewBox="0 0 48 48" style="vertical-align:middle; margin-right:8px;">
                <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5Z"/>
                <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.93c-.58 2.96-2.26 5.48-4.76 7.18l7.73 6C44.41 37.93 46.98 31.7 46.98 24.55Z"/>
                <path fill="#FBBC05" d="M10.53 28.59a14.4 14.4 0 0 1 0-9.18l-7.98-6.19a23.9 23.9 0 0 0 0 21.56l7.98-6.19Z"/>
                <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.9-5.8l-7.73-6c-2.14 1.44-4.88 2.3-8.17 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48Z"/>
            </svg>
            Continuer avec Google
        </button>
        <p style="text-align:center; margin:0 0 12px;">ou créez un compte par e-mail</p>
        <label>Nom complet</label>
        <input type="text" name="name" value="{{ old('name') }}" autocomplete="name" required>
        <label>Email</label>
        <input type="email" name="email" value="{{ old('email') }}" autocomplete="email" required>
        <label for="whatsapp">WhatsApp (optionnel)</label>
        <input id="whatsapp" type="tel" name="whatsapp" value="{{ old('whatsapp') }}" autocomplete="tel" aria-describedby="role-phone-hint">
        <small id="role-phone-hint" hidden style="margin:4px 0 10px; color:#687184;">Ce numéro est nécessaire pour vous contacter au sujet de votre activité.</small>
        <label>Mot de passe</label>
        <input type="password" name="password" autocomplete="new-password" minlength="8" required>
        <label>Confirmer le mot de passe</label>
        <input type="password" name="password_confirmation" autocomplete="new-password" minlength="8" required>
        <button type="submit" class="btn" style="width:100%;">S'inscrire</button>
        <p data-auth-feedback role="status" aria-live="polite" hidden></p>
    </form>
    <script>
        (() => {
            const form = document.querySelector('[data-firebase-auth="register"]');
            const accountType = form?.querySelector('[name="account_type"]');
            const phone = form?.querySelector('[name="whatsapp"]');
            const phoneLabel = form?.querySelector('label[for="whatsapp"]');
            const phoneHint = form?.querySelector('#role-phone-hint');
            const description = form?.querySelector('[data-role-description]');
            if (!accountType || !phone || !phoneLabel || !phoneHint || !description) return;

            const updateRoleFields = () => {
                const isProfessional = accountType.value !== 'customer';
                phone.required = isProfessional;
                phoneLabel.textContent = isProfessional ? 'WhatsApp (obligatoire)' : 'WhatsApp (optionnel)';
                phoneHint.hidden = !isProfessional;
                phoneHint.style.display = isProfessional ? 'block' : 'none';
                description.textContent = accountType.value === 'courier'
                    ? 'Accédez aux livraisons payées disponibles, réclamez-en une et confirmez sa livraison.'
                    : accountType.value === 'partner'
                        ? 'Partagez les liens des produits disponibles et gagnez 8 % sur les ventes payées via vos liens.'
                        : 'Vous pourrez acheter, suivre vos commandes et enregistrer vos favoris.';
            };

            accountType.addEventListener('change', updateRoleFields);
            updateRoleFields();
        })();
    </script>
    <noscript>Activez JavaScript pour créer un compte avec Firebase Authentication.</noscript>

    <p style="margin-top:14px;">Déjà un compte ? <a href="{{ route('login') }}">Connectez-vous</a></p>
@endsection
