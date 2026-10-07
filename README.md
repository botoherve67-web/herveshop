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

## E-mails et notifications

Les e-mails de contact, de bienvenue, de commande, de compte, de vérification d'adresse et de réinitialisation utilisent le mailer Laravel et l'API Resend.

Sur Render Free, le trafic SMTP sortant sur les ports 25, 465 et 587 est bloqué. Utiliser l'API HTTPS Resend :

1. Vérifier un domaine d'envoi dans Resend et créer une clé API.
2. Dans les variables d'environnement Render, définir `MAIL_MAILER=resend`, `RESEND_API_KEY`, `MAIL_FROM_ADDRESS` (adresse du domaine vérifié) et `CONTACT_EMAIL` (adresse de réception des messages du formulaire).
3. Redéployer le service après l'ajout des variables.

Révoquer toute clé API partagée dans une conversation et en créer une nouvelle. Ne jamais l'ajouter au dépôt. Les erreurs d'envoi sont consignées dans les logs Render (`LOG_CHANNEL=stderr`).

### Suppression ponctuelle des anciens comptes clients

Les comptes clients déjà présents ne sont pas supprimés automatiquement lors du déploiement. Après avoir vérifié une sauvegarde restaurable, exécuter dans le Shell du service Render :

```bash
php artisan users:purge-customers
php artisan users:purge-customers --force
```

La première commande affiche le nombre de comptes concernés sans rien supprimer. La seconde supprime définitivement tous les comptes non-admins et les enregistrements liés soumis aux suppressions en cascade, notamment leurs commandes. Les comptes administrateurs sont conservés. Ne lancer `--force` qu'après avoir contrôlé le résultat du dry run et confirmé la sauvegarde.

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
- Notifications email de bienvenue, de compte, de commandes et de paiements
- Un seul administrateur, un seul vendeur

## Non inclus au lancement (par choix du cahier des charges)

- API de paiement automatisée
- Multi-facteur par OTP (la vérification de l'adresse e-mail à l'inscription est activée)
- Multi-langue (prévu plus tard)
- Multi-vendeur
