@extends('layouts.app')

@section('title', 'Connexion')

@section('content')
    <h1>Connexion</h1>

    <form action="{{ route('login') }}" method="POST" class="card" style="max-width:400px;">
        @csrf
        <label>Email ou WhatsApp</label>
        <input type="text" name="identifiant" value="{{ old('identifiant') }}" required>
        <label>Mot de passe</label>
        <input type="password" name="password" required>
        <label style="font-weight:normal;">
            <input type="checkbox" name="remember" style="width:auto; margin-right:6px;"> Se souvenir de moi
        </label>
        <button type="submit" class="btn" style="width:100%;">Se connecter</button>
    </form>

    <p style="margin-top:14px;"><a href="{{ route('password.request') }}">Mot de passe oublie ?</a></p>
    <p style="margin-top:14px;">Pas encore de compte ? <a href="{{ route('register') }}">Inscrivez-vous</a></p>
@endsection
