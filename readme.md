# Manga Hub

Une application Symfony pour découvrir, noter et commenter des **animes/manga** (et extensions possibles vers les jeux vidéo). Ce README te guide pas-à-pas depuis `composer install` jusqu’au déploiement, avec une progression pédagogique pensée pour quelqu’un venant de **Laravel**.

---

## ✨ Fonctionnalités (v1)

* Parcourir la liste des animes
* Détails d’un anime (synopsis, image, moyenne des notes)
* Authentification (inscription, connexion, rôles User/Admin)
* Ajouter des **reviews** (note + commentaire)
* **Favoris** (watchlist) par utilisateur
* Back-office (optionnel) avec **EasyAdmin**
* API publique (optionnel) avec **API Platform**

---

## 🧰 Stack

* **Symfony 7** (webapp)
* **PHP 8.2+**, Composer
* **MySQL** ou **PostgreSQL**
* **Twig** pour le front
* **Doctrine ORM**
* **AssetMapper** (par défaut) — Tailwind via CDN pour aller vite
* **MakerBundle**, **DoctrineFixturesBundle** (développement)
* (Optionnels) **EasyAdmin**, **API Platform**

---

## ⚡ TL;DR — Démarrage rapide

```bash
# 1) Créer le projet
symfony new manga-hub --webapp
cd manga-hub

# 2) Dépendances dev utiles
composer require --dev symfony/maker-bundle doctrine/doctrine-fixtures-bundle

# 3) Configurer la base de données (.env)
# DATABASE_URL="mysql://user:pass@127.0.0.1:3306/manga_hub?serverVersion=8.0"
# ou PostgreSQL
# DATABASE_URL="postgresql://user:pass@127.0.0.1:5432/manga_hub?serverVersion=16&charset=utf8"

# 4) Créer la base + migrations
php bin/console doctrine:database:create
php bin/console make:migration
php bin/console doctrine:migrations:migrate -n

# 5) Lancer le serveur
symfony server:start -d
# puis ouvre http://127.0.0.1:8000
```

---

## 📦 Installation détaillée

### 1) Prérequis

* PHP 8.2+
* Composer
* MySQL (8.0+) ou PostgreSQL (15/16+)
* Extension php: pdo_mysql ou pdo_pgsql
* **Symfony CLI** (recommandé) : [https://symfony.com/download](https://symfony.com/download)

### 2) Création du projet

```bash
symfony new manga-hub --webapp
cd manga-hub
```

La version `--webapp` inclut routeur, Twig, ORM, sécurité, etc.

### 3) Dépendances utiles en dev

```bash
composer require --dev symfony/maker-bundle doctrine/doctrine-fixtures-bundle
```

### 4) Configuration `.env`

Ouvre `.env` et ajuste `DATABASE_URL` :

```env
# MySQL
DATABASE_URL="mysql://root:root@127.0.0.1:3306/manga_hub?serverVersion=8.0"

# PostgreSQL (exemple)
# DATABASE_URL="postgresql://postgres:postgres@127.0.0.1:5432/manga_hub?serverVersion=16&charset=utf8"
```

> Astuce WAMP/MAMP/XAMPP : vérifie le port et l’utilisateur.

### 5) Base de données & migrations

```bash
php bin/console doctrine:database:create
php bin/console make:migration
php bin/console doctrine:migrations:migrate -n
```

---

## 🧱 Modélisation (v1)

### Entités

* **User**: id, email, roles, password, **username** (unique), createdAt
* **Anime**: id, **title**, **slug**, synopsis(text), coverUrl, status(enum: ONGOING/FINISHED), **avgRating**(float), createdAt, updatedAt
* **Review**: id, **rating**(1-10), content(text), **author**(User), **anime**(Anime), createdAt
* **Favorite**: id, **user**(User), **anime**(Anime), createdAt (unique user+anime)

### Génération rapide (Maker)

```bash
php bin/console make:user           # User (security)
php bin/console make:entity Anime   # ajoute title, slug, synopsis, coverUrl, status, avgRating, timestamps
php bin/console make:entity Review  # rating(int), content(text), author(ManyToOne User), anime(ManyToOne Anime)
php bin/console make:entity Favorite# user(ManyToOne User), anime(ManyToOne Anime), createdAt

php bin/console make:migration && php bin/console doctrine:migrations:migrate -n
```

### Contraintes & index utiles

* Unicité: `User.username`, `Anime.slug`, couple unique `Favorite(user, anime)`
* Validations: `Review.rating` ∈ [1,10], `Anime.title` non vide

---

## 🔐 Authentification & sécurité

```bash
php bin/console make:registration-form   # inscription + encodage password
php bin/console make:auth                # login form authenticator
```

* Protège les routes création/édition/suppression par `ROLE_USER`.
* Admin: crée un utilisateur avec `ROLE_ADMIN` pour le back-office.

---

## 🖥️ Contrôleurs & pages (Twig)

### Routes de base

* `/` — Accueil (animes récents, top notés)
* `/anime` — Liste paginée
* `/anime/{slug}` — Détail + reviews + bouton Favori
* `/login` `/register` `/logout`

### Génération

```bash
php bin/console make:controller HomeController
php bin/console make:controller AnimeController
php bin/console make:controller ReviewController
```

### Templates (rapide)

* `templates/base.html.twig` : intègre Tailwind (CDN) pour aller vite

```html
<!doctype html>
<html>
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{% block title %}Manga Hub{% endblock %}</title>
    <script src="https://cdn.tailwindcss.com"></script>
  </head>
  <body class="bg-slate-50 text-slate-900">
    <header class="max-w-5xl mx-auto p-4 flex justify-between items-center">
      <a href="/" class="font-bold text-xl">Manga Hub</a>
      <nav class="flex gap-4">
        <a href="/anime" class="hover:underline">Animes</a>
        {% if app.user %}
          <a href="/logout">Déconnexion</a>
        {% else %}
          <a href="/login">Connexion</a>
          <a href="/register">Inscription</a>
        {% endif %}
      </nav>
    </header>
    <main class="max-w-5xl mx-auto p-4">{% block body %}{% endblock %}</main>
  </body>
</html>
```

---

## 🧮 Logique métier clé

* Calcul de `Anime.avgRating` via **EventSubscriber** Doctrine sur `Review` (postPersist/postUpdate) **ou** via requête agrégée à l’affichage.
* Slug automatique à partir du `title` (utilise `symfony/string` Slugger).
* Politique d’accès : un utilisateur peut éditer/supprimer **sa** review ; l’admin peut tout.

---

## 🌱 Fixtures (données de démo)

```bash
php bin/console make:fixtures  # App\DataFixtures\AppFixtures
```

Dans `AppFixtures`, crée 20 animes fake, 50 reviews, 1 admin :

```php
// Extrait d’exemple à adapter
$admin = (new User())
  ->setEmail('admin@local.test')
  ->setUsername('admin')
  ->setRoles(['ROLE_ADMIN'])
  ->setPassword($this->hasher->hashPassword($admin, 'password'));
$this->em->persist($admin);
```

Puis :

```bash
php bin/console doctrine:fixtures:load -n
```

---

## 🧭 Feuille de route (proposée)

* [x] CRUD Anime (index, show, create/edit/delete — admin)
* [x] Authentification + inscription
* [x] Review (create/delete par auteur)
* [x] Favoris (toggle)
* [ ] Pagination & recherche (titre, statut)
* [ ] Import d’animes via **Jikan API** (MyAnimeList)
* [ ] Back-office **EasyAdmin**
* [ ] API publique **API Platform** (`/api/animes`)
* [ ] Upload image (cover) avec **VichUploader** (optionnel)

---

## 🗂️ Structure du projet

```
manga-hub/
├─ config/
├─ migrations/
├─ public/
├─ src/
│  ├─ Controller/
│  ├─ Entity/
│  ├─ Repository/
│  ├─ Security/
│  ├─ EventSubscriber/
│  └─ DataFixtures/
├─ templates/
│  ├─ base.html.twig
│  ├─ home/
│  ├─ anime/
│  └─ review/
├─ var/
└─ vendor/
```

---

## 🧪 Tests (PHPUnit)

```bash
composer require --dev symfony/phpunit-bridge
php bin/phpunit --version

# Exemple : test d’un service de calcul de moyenne
php bin/console make:test AverageCalculatorTest
```

---

## 🛠️ Commandes utiles (Makefile facultatif)

Crée un `Makefile` à la racine :

```make
start:
	 symfony server:start -d
stop:
	 symfony server:stop
migrate:
	 php bin/console doctrine:migrations:migrate -n
fixtures:
	 php bin/console doctrine:fixtures:load -n
reset:
	 php bin/console doctrine:database:drop --force || true
	 php bin/console doctrine:database:create
	 php bin/console doctrine:migrations:migrate -n
	 php bin/console doctrine:fixtures:load -n
```

---

## 🧰 Back-office (optionnel)

```bash
composer require easycorp/easyadmin-bundle
```

Génère des CRUD pour `Anime`, `Review`, `User`. Restreins l’accès à `ROLE_ADMIN`.

---

## 🔌 API (optionnel)

```bash
composer require api
# Visite /api pour la documentation auto (SwaggerUI)
```

Expose `Anime`, `Review` en lecture ; sécurise les endpoints d’écriture (`POST /reviews`).

---

## 🚀 Déploiement (ex. Render, Railway, PaaS)

* Variables env (prod):

  * `APP_ENV=prod`, `APP_SECRET`, `DATABASE_URL`
* Build : `composer install --no-dev --optimize-autoloader`
* Cache : `php bin/console cache:clear --env=prod`
* Migrations : `php bin/console doctrine:migrations:migrate -n --env=prod`
* Web : configure le document root sur `public/`

---

## 🐞 Dépannage

* **Page blanche en prod** ➜ active `APP_DEBUG=1` temporairement (jamais en vrai prod), vérifie `var/log/prod.log`.
* **Assets non chargés** ➜ vérifie le layout `base.html.twig` et la balise `<script src="https://cdn.tailwindcss.com"></script>`.
* **Migrations bloquées** ➜ supprime le dernier fichier dans `migrations/` (si vide en BDD) ou corrige manuellement, puis relance.
* **Problèmes d’URL de BDD** ➜ attention au `serverVersion` et au port.

---

## 🤝 Contribution (perso)

* Commits clairs (Conventional Commits si possible)
* Branches par feature, PR courte, tests verts

---

## 📜 Licence

MIT — fais-toi plaisir et apprends 💪

---

## 📎 Annexes — Snippets rapides

### Service de calcul de moyenne (option)

```php
// src/Service/AverageCalculator.php
namespace App\Service;

class AverageCalculator
{
    /** @param int[]|float[] $values */
    public function average(array $values): float
    {
        $values = array_values(array_filter($values, static fn($v) => $v !== null));
        if (!count($values)) { return 0.0; }
        return round(array_sum($values) / count($values), 2);
    }
}
```

### Utilisation du Slugger (constructeur d’entité ou subscriber)

```php
use Symfony\Component\String\Slugger\SluggerInterface;

public function __construct(private SluggerInterface $slugger) {}

public function setTitle(string $title): self
{
    $this->title = $title;
    $this->slug = strtolower($this->slugger->slug($title));
    return $this;
}
```

### Form Review basique (note + commentaire)

```php
// php bin/console make:form ReviewType
$builder
  ->add('rating', IntegerType::class, [ 'attr' => ['min' => 1, 'max' => 10] ])
  ->add('content', TextareaType::class)
;
```

