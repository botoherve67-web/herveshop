@extends('layouts.app')

@section('title', 'Inscription')

@section('content')
    <h1>Créer un compte</h1>

    <form action="{{ route('firebase.session') }}" method="POST" class="card" style="max-width:400px;" data-firebase-auth="register">
        @csrf
        <label>Nom complet</label>
        <input type="text" name="name" value="{{ old('name') }}" autocomplete="name" required>
        <label>Email</label>
        <input type="email" name="email" value="{{ old('email') }}" autocomplete="email" required>
        <label>WhatsApp (optionnel)</label>
        <input type="text" name="whatsapp" value="{{ old('whatsapp') }}">
        <label>Mot de passe</label>
        <input type="password" name="password" autocomplete="new-password" minlength="8" required>
        <label>Confirmer le mot de passe</label>
        <input type="password" name="password_confirmation" autocomplete="new-password" minlength="8" required>
        <button type="submit" class="btn" style="width:100%;">S'inscrire</button>
        <p data-auth-feedback role="status" aria-live="polite" hidden></p>
    </form>
    <noscript>Activez JavaScript pour créer un compte avec Firebase Authentication.</noscript>

    <p style="margin-top:14px;">Déjà un compte ? <a href="{{ route('login') }}">Connectez-vous</a></p>
@endsection
