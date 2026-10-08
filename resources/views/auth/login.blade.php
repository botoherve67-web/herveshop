@extends('layouts.app')

@section('title', 'Connexion')

@section('content')
    <h1>Connexion</h1>

    <form action="{{ route('firebase.session') }}" method="POST" class="card" style="max-width:400px;" data-firebase-auth="login">
        @csrf
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
