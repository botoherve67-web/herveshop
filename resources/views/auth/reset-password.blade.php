@extends('layouts.app')

@section('title', 'Nouveau mot de passe')

@section('content')
    <h1>Nouveau mot de passe</h1>

    <form action="{{ route('password.update') }}" method="POST" class="card" style="max-width:400px;">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <label>Email</label>
        <input type="email" name="email" value="{{ old('email', $email) }}" required>
        <label>Nouveau mot de passe</label>
        <input type="password" name="password" required>
        <label>Confirmer le mot de passe</label>
        <input type="password" name="password_confirmation" required>
        <button type="submit" class="btn" style="width:100%;">Modifier</button>
    </form>
@endsection
