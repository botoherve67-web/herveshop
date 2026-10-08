@extends('layouts.app')

@section('title', 'Connexion')

@section('content')
    <h1>Connexion</h1>

    <form action="{{ route('firebase.session') }}" method="POST" class="card" style="max-width:400px;" data-firebase-auth="login">
        @csrf
        <button type="button" class="btn outline" style="width:100%; margin-bottom:12px;" data-firebase-google>
            <svg aria-hidden="true" width="18" height="18" viewBox="0 0 48 48" style="vertical-align:middle; margin-right:8px;">
                <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5Z"/>
                <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.93c-.58 2.96-2.26 5.48-4.76 7.18l7.73 6C44.41 37.93 46.98 31.7 46.98 24.55Z"/>
                <path fill="#FBBC05" d="M10.53 28.59a14.4 14.4 0 0 1 0-9.18l-7.98-6.19a23.9 23.9 0 0 0 0 21.56l7.98-6.19Z"/>
                <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.9-5.8l-7.73-6c-2.14 1.44-4.88 2.3-8.17 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48Z"/>
            </svg>
            Continuer avec Google
        </button>
        <p style="text-align:center; margin:0 0 12px;">ou avec votre adresse e-mail</p>
        <label>Email</label>
        <input type="email" name="email" value="{{ old('email') }}" autocomplete="email" required>
        <label>Mot de passe</label>
        <input type="password" name="password" autocomplete="current-password" required>
        <label style="font-weight:normal;">
            <input type="checkbox" name="remember" value="1" style="width:auto; margin-right:6px;"> Se souvenir de moi
        </label>
        <button type="submit" class="btn" style="width:100%;">Se connecter</button>
        <p data-auth-feedback role="status" aria-live="polite" hidden></p>
    </form>
    <noscript>Activez JavaScript pour vous connecter avec Firebase Authentication.</noscript>

    <p style="margin-top:14px;"><a href="{{ route('password.request') }}">Mot de passe oublie ?</a></p>
    <p style="margin-top:14px;">Pas encore de compte ? <a href="{{ route('register') }}">Inscrivez-vous</a></p>
@endsection
