@extends('layouts.app')

@section('title', 'Mot de passe oublie')

@section('content')
    <h1>Mot de passe oublie</h1>

    <form action="{{ route('password.request') }}" method="POST" class="card" style="max-width:400px;" data-firebase-password-reset>
        <label>Email</label>
        <input type="email" name="email" value="{{ old('email') }}" autocomplete="email" required>
        <button type="submit" class="btn" style="width:100%;">Envoyer le lien de réinitialisation</button>
        <p data-auth-feedback role="status" aria-live="polite" hidden></p>
    </form>

    <noscript>Activez JavaScript pour demander un lien de réinitialisation Firebase.</noscript>
    <p style="margin-top:14px;"><a href="{{ route('login') }}">Retour connexion</a></p>
@endsection
