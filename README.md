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

Les e-mails de contact, de bienvenue, de commande, de compte et de réinitialisation utilisent le mailer Laravel et l'API Resend.

Sur Render Free, le trafic SMTP sortant sur les ports 25, 465 et 587 est bloqué. Utiliser l'API HTTPS Resend :

1. Vérifier un domaine d'envoi dans Resend et créer une clé API.
2. Dans les variables d'environnement Render, définir `MAIL_MAILER=resend`, `RESEND_API_KEY`, `MAIL_FROM_ADDRESS` (adresse du domaine vérifié) et `CONTACT_EMAIL` (adresse de réception des messages du formulaire).
3. Redéployer le service après l'ajout des variables.

Révoquer toute clé API partagée dans une conversation et en créer une nouvelle. Ne jamais l'ajouter au dépôt. Les erreurs d'envoi sont consignées dans les logs Render (`LOG_CHANNEL=stderr`).

## Firebase Authentication

La connexion et l'inscription par e-mail/mot de passe utilisent Firebase Authentication. Laravel continue de gérer les sessions de l'application, les rôles, les commandes et les données client; les jetons Firebase sont vérifiés côté serveur avant l'ouverture d'une session Laravel. Les comptes existants sont associés à leur identité Firebase uniquement lorsque Firebase confirme que leur adresse e-mail est vérifiée. Les mots de passe Laravel ne sont pas transférables : chaque ancien utilisateur doit créer son identité Firebase avec la même adresse, vérifier celle-ci si nécessaire, puis choisir un nouveau mot de passe.

Avant le déploiement :

1. Dans Firebase Console > Authentication > Sign-in method, activer le fournisseur **Email/Password** et ajouter le domaine de production aux domaines autorisés.
2. Définir `FIREBASE_PROJECT_ID=elledji` dans l'environnement Laravel (et dans `.env` en local). Aucun compte de service Firebase n'est requis pour vérifier les jetons d'identité.
3. Restreindre la clé API Web aux domaines de l'application dans Google Cloud. La configuration Web Firebase reste publique; elle ne constitue pas un secret serveur.

Firebase envoie le lien de réinitialisation du mot de passe; les anciennes demandes de réinitialisation approuvées par un administrateur ne modifient plus les mots de passe Firebase. Lorsqu'un administrateur supprime un client, son UID est bloqué par Laravel pour empêcher la recréation de son compte applicatif. L'identité correspondante reste dans Firebase Authentication : supprimez-la aussi depuis Firebase Console si elle doit être définitivement retirée ou si son adresse doit être réutilisée.

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
- Vérification de l'adresse e-mail par OTP à l'inscription
- Multi-langue (prévu plus tard)
- Multi-vendeur
