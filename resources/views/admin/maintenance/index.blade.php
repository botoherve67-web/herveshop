@extends('admin.layout')

@section('title', 'Mode maintenance — Admin')

@section('admin-content')
    <h1>Mode maintenance</h1>
    <div class="card" style="max-width:680px;">
        <p>Lorsque le mode maintenance est actif, les visiteurs voient la page de maintenance. Les administrateurs peuvent continuer à accéder à l’administration.</p>
        <form action="{{ route('admin.maintenance.update') }}" method="POST">
            @csrf
            @method('PATCH')
            <label style="display:flex; align-items:center; gap:8px; font-weight:600; margin:18px 0;">
                <input type="checkbox" name="maintenance_enabled" value="1" style="width:auto; margin:0;" @checked($enabled)>
                Activer le mode maintenance
            </label>
            <label for="maintenance_message">Message affiché aux visiteurs</label>
            <textarea id="maintenance_message" name="maintenance_message" rows="4" maxlength="500" required>{{ old('maintenance_message', $message) }}</textarea>
            <button type="submit" class="btn">Enregistrer la configuration</button>
        </form>
    </div>
@endsection