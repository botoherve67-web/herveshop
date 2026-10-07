@extends('layouts.app')

@section('title', 'Vérification de l’e-mail')

@section('content')
    <h1>Vérifiez votre adresse e-mail</h1>
    <p>Un code à 6 chiffres a été envoyé à <strong>{{ $user->email }}</strong>. Il est valable 10 minutes.</p>

    @if (session('status'))
        <div class="alert alert-success" role="status">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger" role="alert">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form action="{{ route('email.verify.submit') }}" method="POST" class="card" style="max-width:400px;">
        @csrf
        <label for="code">Code de vérification</label>
        <input id="code" type="text" name="code" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]{6}" maxlength="6" required autofocus>
        <button type="submit" class="btn" style="width:100%;">Vérifier mon e-mail</button>
    </form>

    <form action="{{ route('email.verify.resend') }}" method="POST" style="max-width:400px;margin-top:14px;">
        @csrf
        <button type="submit" class="btn btn-secondary" style="width:100%;">Renvoyer le code</button>
    </form>

    <p style="margin-top:14px;">Vous vous souvenez de votre compte ? <a href="{{ route('login') }}">Connectez-vous</a></p>
@endsection
