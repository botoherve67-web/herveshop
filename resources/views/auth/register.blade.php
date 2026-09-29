@extends('layouts.app')

@section('title', 'Inscription')

@section('content')
    <h1>Créer un compte</h1>

    <form action="{{ route('register') }}" method="POST" class="card" style="max-width:400px;">
        @csrf
        <label>Nom complet</label>
        <input type="text" name="name" value="{{ old('name') }}" required>
        <label>Email</label>
        <input type="email" name="email" value="{{ old('email') }}" required>
        <label>WhatsApp (optionnel)</label>
        <input type="text" name="whatsapp" value="{{ old('whatsapp') }}">
        <label>Mot de passe</label>
        <input type="password" name="password" required>
        <label>Confirmer le mot de passe</label>
        <input type="password" name="password_confirmation" required>
        <button type="submit" class="btn" style="width:100%;">S'inscrire</button>
    </form>

    <p style="margin-top:14px;">Déjà un compte ? <a href="{{ route('login') }}">Connectez-vous</a></p>
@endsection
