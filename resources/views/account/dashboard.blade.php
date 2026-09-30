@extends('layouts.app')

@section('title', 'Mon profil - HerveShop')

@section('content')
    @php
        $initials = collect(preg_split('/\s+/', trim($user->name)))->filter()->map(fn ($part) => strtoupper(substr($part, 0, 1)))->take(2)->implode('');
    @endphp

    <style>
        .profile-page{display:grid;grid-template-columns:220px minmax(0,1fr) 320px;gap:16px;align-items:start}.profile-nav,.profile-panel,.profile-side-panel{border:1px solid #e3edf8;border-radius:8px;background:#fff;box-shadow:0 8px 24px rgba(28,78,128,.06)}.profile-nav{position:sticky;top:16px;padding:12px}.profile-nav a{display:flex;align-items:center;gap:9px;padding:10px;border-radius:6px;color:#25466f;font-size:.72rem}.profile-nav a.active,.profile-nav a:hover{color:#fff;background:#0969ed}.profile-main{display:grid;gap:16px;min-width:0}.profile-hero{display:flex;align-items:center;gap:16px;padding:20px;border-radius:8px;color:#173f5f;background:linear-gradient(115deg,#d8ecff,#f5fbff)}.profile-avatar{display:grid;place-items:center;flex:0 0 76px;width:76px;height:76px;border:4px solid #fff;border-radius:50%;color:#fff;background:linear-gradient(145deg,#173f5f,#0969ed);font-size:1.45rem;font-weight:800}.profile-hero h1{margin:0 0 6px;font-size:1.15rem;color:#102e55}.profile-hero p{display:flex;align-items:center;gap:7px;margin:3px 0;color:#466383;font-size:.68rem}.profile-edit{margin-left:auto;padding:8px 12px;border:1px solid #a8d0fa;border-radius:16px;color:#0969ed;background:#fff;font-size:.62rem;font-weight:700}.profile-stats{display:grid;grid-template-columns:repeat(4,1fr);gap:10px}.profile-stat{padding:13px;border:1px solid #e3edf8;border-radius:8px;background:#fff}.profile-stat strong{display:block;color:#173f5f;font-size:1rem}.profile-stat span{display:block;color:#7187a4;font-size:.62rem}.profile-stat a{display:block;margin-top:8px;color:#0969ed;font-size:.59rem;font-weight:700}.profile-panel{padding:16px}.profile-heading{display:flex;align-items:center;gap:10px;margin-bottom:13px}.profile-heading>span{display:grid;place-items:center;width:30px;height:30px;border-radius:50%;color:#fff;background:#0969ed}.profile-heading h2{margin:0;color:#173f5f;font-size:.82rem}.profile-heading p{margin:2px 0 0;color:#8aa0bb;font-size:.6rem}.profile-fields{display:grid;grid-template-columns:1fr 1fr;gap:0 16px}.profile-field{display:grid;grid-template-columns:25px 1fr 1fr;gap:7px;align-items:center;padding:8px 0;border-bottom:1px solid #edf2f7;font-size:.62rem}.profile-field svg{color:#254e83}.profile-field strong{color:#25466f}.profile-field span{color:#6681a4;word-break:break-word}.profile-form-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px 14px}.profile-form-grid label{display:block;margin-bottom:5px;color:#25466f;font-size:.65rem;font-weight:700}.profile-form-grid input,.profile-form-grid select{width:100%;margin:0;padding:9px 10px;border:1px solid #dce8f5;border-radius:7px;background:#f8fbff}.profile-form-grid .wide{grid-column:1/-1}.profile-form-grid .btn{grid-column:1/-1;width:max-content;min-height:36px;padding:7px 14px;font-size:.7rem}.profile-right{display:grid;gap:16px}.profile-side-panel{padding:15px}.profile-side-panel h2{margin:0 0 8px;color:#173f5f;font-size:.82rem}.profile-side-panel p,.profile-side-panel small{color:#6681a4;font-size:.62rem}.profile-order{display:flex;align-items:center;gap:9px;padding:9px 0;border-bottom:1px solid #edf2f7}.profile-order-art{display:grid;place-items:center;flex:0 0 40px;height:40px;overflow:hidden;border-radius:8px;color:#0969ed;background:#edf5ff}.profile-order-art img{width:100%;height:100%;object-fit:contain}.profile-order-copy{min-width:0;flex:1}.profile-order-copy strong{display:block;overflow:hidden;color:#25466f;font-size:.63rem;text-overflow:ellipsis;white-space:nowrap}.profile-order-copy small{display:block;color:#8aa0bb;font-size:.56rem}.profile-status{padding:4px 7px;border-radius:10px;color:#0969ed;background:#eaf4ff;font-size:.54rem;white-space:nowrap}@media(max-width:1050px){.profile-page{grid-template-columns:180px minmax(0,1fr)}.profile-right{grid-column:2;grid-template-columns:1fr 1fr}}@media(max-width:760px){.profile-page{grid-template-columns:1fr}.profile-nav{position:static}.profile-nav nav{display:flex;overflow-x:auto}.profile-nav a{flex:0 0 auto}.profile-right{grid-column:auto;grid-template-columns:1fr}.profile-hero{flex-wrap:wrap}.profile-edit{margin-left:0}.profile-stats{grid-template-columns:1fr 1fr}.profile-fields,.profile-form-grid{grid-template-columns:1fr}.profile-form-grid .wide,.profile-form-grid .btn{grid-column:auto;width:100%}}
    </style>

    <div class="profile-page">
        <aside class="profile-nav">
            <nav aria-label="Navigation du compte">
                <a class="active" href="{{ route('account.dashboard') }}"><x-icon name="user" size="18"/> Profil</a>
                <a href="{{ route('orders.index') }}"><x-icon name="box" size="18"/> Commandes</a>
                <a href="{{ route('wishlist.index') }}"><x-icon name="heart" size="18"/> Favoris</a>
                <a href="#profile-address"><x-icon name="pin" size="18"/> Adresse</a>
                <a href="#profile-edit-form"><x-icon name="settings" size="18"/> Parametres</a>
                <form action="{{ route('logout') }}" method="POST" style="margin-top:8px;">
                    @csrf
                    <button type="submit" class="profile-logout" style="display:flex;align-items:center;gap:9px;width:100%;padding:10px;border:0;border-radius:6px;color:#b42332;background:#fff4f5;font-size:.72rem;cursor:pointer;"><x-icon name="logout" size="18"/> Se déconnecter</button>
                </form>
            </nav>
        </aside>

        <section class="profile-main">
            <div class="profile-hero">
                <div class="profile-avatar">{{ $initials ?: 'HS' }}</div>
                <div>
                    <h1>Bonjour, {{ $user->name }} !</h1>
                    <p><x-icon name="mail" size="13"/> {{ $user->email }}</p>
                    <p><x-icon name="calendar" size="13"/> Membre depuis {{ optional($user->created_at)->translatedFormat('F Y') ?: 'recemment' }}</p>
                </div>
                <a class="profile-edit" href="#profile-edit-form"><x-icon name="edit" size="12"/> Modifier</a>
            </div>

            <div class="profile-stats">
                <div class="profile-stat"><strong>{{ $ordersCount }}</strong><span>Commandes</span><a href="{{ route('orders.index') }}">Voir</a></div>
                <div class="profile-stat"><strong>{{ $wishlistCount }}</strong><span>Favoris</span><a href="{{ route('wishlist.index') }}">Voir</a></div>
                <div class="profile-stat"><strong>{{ $reviewsCount }}</strong><span>Avis</span><a href="{{ route('products.index') }}">Voir</a></div>
                <div class="profile-stat"><strong>OK</strong><span>Email actif</span><a href="#profile-settings">Gerer</a></div>
            </div>

            <div class="profile-panel" id="profile-settings">
                <div class="profile-heading"><span><x-icon name="user" size="16"/></span><div><h2>Informations personnelles</h2><p>Profil client complet</p></div></div>
                <div class="profile-fields">
                    <div class="profile-field"><x-icon name="user" size="14"/><strong>Nom</strong><span>{{ $user->name }}</span></div>
                    <div class="profile-field"><x-icon name="mail" size="14"/><strong>Email</strong><span>{{ $user->email }}</span></div>
                    <div class="profile-field"><x-icon name="phone" size="14"/><strong>WhatsApp</strong><span>{{ $user->whatsapp ?: 'Non renseigne' }}</span></div>
                    <div class="profile-field"><x-icon name="calendar" size="14"/><strong>Naissance</strong><span>{{ $user->birth_date?->format('d/m/Y') ?: 'Non renseignee' }}</span></div>
                    <div class="profile-field"><x-icon name="user" size="14"/><strong>Genre</strong><span>{{ $user->gender ? ucfirst($user->gender) : 'Non renseigne' }}</span></div>
                    <div class="profile-field"><x-icon name="pin" size="14"/><strong>Zone</strong><span>{{ $user->zone ?: 'Non renseignee' }}</span></div>
                </div>
            </div>

            <div class="profile-panel" id="profile-address">
                <div class="profile-heading"><span><x-icon name="pin" size="16"/></span><div><h2>Livraison</h2><p>Adresse et consignes</p></div></div>
                <div class="profile-fields">
                    <div class="profile-field"><x-icon name="pin" size="14"/><strong>Adresse</strong><span>{{ $user->address ?: 'Non renseignee' }}</span></div>
                    <div class="profile-field"><x-icon name="box" size="14"/><strong>Note</strong><span>{{ $user->delivery_notes ?: 'Non renseignee' }}</span></div>
                </div>
            </div>

            <div class="profile-panel" id="profile-edit-form">
                <div class="profile-heading"><span><x-icon name="edit" size="16"/></span><div><h2>Modifier mon profil</h2><p>Utilise pour commandes et livraisons</p></div></div>
                <form action="{{ route('account.update') }}" method="POST" class="profile-form-grid">
                    @csrf
                    @method('PATCH')
                    <div><label for="profile-name">Nom complet</label><input id="profile-name" type="text" name="name" value="{{ old('name', $user->name) }}" required></div>
                    <div><label for="profile-whatsapp">Telephone / WhatsApp</label><input id="profile-whatsapp" type="text" name="whatsapp" value="{{ old('whatsapp', $user->whatsapp) }}"></div>
                    <div><label for="profile-birth-date">Date de naissance</label><input id="profile-birth-date" type="date" name="birth_date" value="{{ old('birth_date', $user->birth_date?->format('Y-m-d')) }}"></div>
                    <div><label for="profile-gender">Genre</label><select id="profile-gender" name="gender"><option value="">Non renseigne</option><option value="homme" @selected(old('gender', $user->gender) === 'homme')>Homme</option><option value="femme" @selected(old('gender', $user->gender) === 'femme')>Femme</option><option value="autre" @selected(old('gender', $user->gender) === 'autre')>Autre</option></select></div>
                    <div><label for="profile-zone">Ville / zone</label><input id="profile-zone" type="text" name="zone" value="{{ old('zone', $user->zone) }}"></div>
                    <div><label for="profile-address-input">Adresse</label><input id="profile-address-input" type="text" name="address" value="{{ old('address', $user->address) }}"></div>
                    <div class="wide"><label for="profile-delivery-notes">Note livraison</label><input id="profile-delivery-notes" type="text" name="delivery_notes" value="{{ old('delivery_notes', $user->delivery_notes) }}" placeholder="Ex: appeler avant livraison"></div>
                    <button class="btn" type="submit">Enregistrer les modifications</button>
                </form>
            </div>
        </section>

        <aside class="profile-right">
            <div class="profile-side-panel">
                <h2>Historique recent</h2>
                @forelse($orders as $order)
                    @php $item = $order->items->first(); $image = $item?->product?->images?->first(); @endphp
                    <a class="profile-order" href="{{ route('orders.show', $order) }}">
                        <span class="profile-order-art">@if($image)<img src="{{ asset('storage/'.$image->path) }}" alt="{{ $item?->product_name }}">@else<x-icon name="box" size="19"/>@endif</span>
                        <span class="profile-order-copy"><strong>{{ $item?->product_name ?: 'Commande '.$order->reference }}</strong><small>#{{ $order->reference }} - {{ optional($order->created_at)->format('d/m/Y') }}</small></span>
                        <span class="profile-status">{{ ucfirst(str_replace('_',' ',$order->statut)) }}</span>
                    </a>
                @empty
                    <p>Aucune commande recente.</p>
                @endforelse
            </div>

            <div class="profile-side-panel">
                <h2>Support</h2>
                <small>Notre equipe peut vous aider pour commandes, livraison ou compte.</small>
                <p><a href="{{ route('contact.index') }}">Contacter le support</a></p>
            </div>
        </aside>
    </div>
@endsection
