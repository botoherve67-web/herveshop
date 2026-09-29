# HerveShop — MVP 1.0

Plateforme e-commerce Laravel (stock + précommandes), Afrique de l'Ouest.

## Installation

```bash
composer install
cp .env.example .env   # déjà fait, ajuster si besoin
php artisan key:generate
```

Créer la base MySQL `hervershop` (ou renommer dans `.env`), puis :

```bash
php artisan migrate --seed
php artisan storage:link
php artisan serve
```

## Compte admin par défaut

- Email : `admin@hervershop.tg`
- Mot de passe : `changeme123`
→ à changer immédiatement en production.

## Structure clé

- `app/Models` : User, Category, Product, Order, OrderItem, Review, PromoCode, ProductImage
- `app/Http/Controllers` : catalogue, panier, commande, compte client, avis
- `app/Http/Controllers/Admin` : dashboard, produits, catégories, commandes, codes promo
- `database/migrations` : schéma complet
- `resources/views` : Blade, identité vert + blanc

## Fonctionnalités MVP

- Catalogue avec recherche + filtres (catégorie, stock/précommande, prix, tri)
- Précommande : acompte 70% par défaut (réglable par produit), solde sous 48h après arrivage
- Bascule automatique stock → précommande si épuisé (réglable par produit)
- Livraison à domicile (frais par zone) ou point de retrait
- Paiement manuel Flooz / TMoney (pas d'API au lancement)
- Codes promo (pourcentage ou montant fixe)
- Avis clients notés 1 à 5
- Compte client avec historique commandes détaillé
- Connexion par email ou WhatsApp + mot de passe
- Notification email au client (à brancher : voir commentaire dans `Admin/OrderController@updateStatut`)
- Un seul administrateur, un seul vendeur

## Non inclus au lancement (par choix du cahier des charges)

- API de paiement automatisée
- OTP
- Multi-langue (prévu plus tard)
- Multi-vendeur
