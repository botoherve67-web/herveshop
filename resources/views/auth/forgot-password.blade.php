@extends('layouts.app')

@section('title', 'Mot de passe oublie')

@section('content')
    <h1>Mot de passe oublie</h1>

    <form action="{{ route('password.email') }}" method="POST" class="card" style="max-width:400px;">
        @csrf
        <label>Email</label>
        <input type="email" name="email" value="{{ old('email') }}" required>
        <button type="submit" class="btn" style="width:100%;">Recevoir le lien</button>
    </form>

    <p style="margin-top:14px;"><a href="{{ route('login') }}">Retour connexion</a></p>
@endsection
