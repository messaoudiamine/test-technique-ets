# Test Technique - Application Web

Application Web de Gestion de Compte et d’Articles avec Symfony et React

## 📋 Prérequis

- **Docker** et **Docker Compose**
- **Node.js** (version 18 ou supérieure) et **npm**
- **PHP 8.1+** et **Composer** (si _vous_ lancez le backend sans Docker)


### Backend (Symfony)

1. **Aller dans le dossier backend**
   ```bash
   cd backend
   ```

2. **Créer le fichier `.env`**
   
   Créer le fichier `.env` à la racine du dossier backend avec le contenu suivant :
   ```env
   APP_ENV=dev
   MONGODB_URI=mongodb://mongo:27017
   MONGODB_DB=app
   JWT_PASSPHRASE=changeme
   
   ```
   - `APP_ENV` : Environnement de l'application (dev, prod, test)
   - `MONGODB_URI` : URI de connexion a MongoDB (utilise le nom du service Docker `mongo`)
   - `MONGODB_DB` : Nom de la base de données MongoDB
   - `JWT_PASSPHRASE` : Passphrase pour la génération des clés JWT (à changer en production)
     
Le fichier `.env` est lu automatiquement par Docker Compose via `env_file` dans `docker-compose.yml`.

3. **Lancer**
   ```bash
   docker compose build
   ```

4. **Lancer les services Docker**
   ```bash
   docker compose up -d
   ```

5. **Installer les dépendances PHP**
   ```bash
   docker compose exec app composer install
   ```

6. **Générer les clés JWT**
   ```bash
   docker compose exec app php bin/console lexik:jwt:generate-keypair
   ```
   
   Cette commande génère les clés privées et publiques nécessaires pour l'authentification JWT. Si vous êtes invité à saisir la passphrase, utilisez la valeur de `JWT_PASSPHRASE` définie dans votre fichier `.env`.

7. **Charger les fixtures**
   ```bash
   docker compose exec app php bin/console app:load-fixtures
   ```

Le backend sera accessible sur **http://localhost:8080/api**



### Frontend (Next.js)

1. **Aller dans le dossier frontend**
   ```bash
   cd frontend
   ```

2. **Installer les dépendances**
   ```bash
   npm install
   ```
   
Variables d'environnement front 

3. **Créer le fichier `.env.local`** (si nécessaire)
   ```bash
   echo "NEXT_PUBLIC_API_URL=http://localhost:8080/api" > .env.local
   ```

4. **Lancer le serveur de développement**
   ```bash
   npm run dev
   ```

Le frontend sera accessible sur **http://localhost:3000**

## 🚀 Fonctionnalités

- ✅ **Authentification JWT** : Connexion et inscription avec tokens JWT
- ✅ **Page de compte utilisateur** : Affichage et modification des informations personnelles
- ✅ **Gestion des articles** : Liste, création, modification et suppression d'articles
- ✅ **Pagination** : Navigation paginée pour les articles
- ✅ **Design responsive** : Interface adaptée à tous les écrans avec Tailwind CSS

## 🔑 Comptes par défaut (Fixtures)

- **Admin** :
  - Email : `admin@test.com`
  - Password : `password123`


## 📚 Documentation de l'API

### Base URL

```
http://localhost:8080/api
```

### Authentification

L'authentification se fait via JWT. Après connexion, incluez le token dans l'en-tête :

```
Authorization: Bearer <votre_token_jwt>
```

### Endpoints

#### 🔐 Authentification

**POST** `/api/auth/register`
- Enregistre un nouvel utilisateur
- **Body** :
```json
{
  "name": "John Doe",
  "email": "john@test.com",
  "password": "password123"
}
```
- **Réponse** :
```json
{
  "message": "User created successfully",
  "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
  "user": {
    "id": "...",
    "name": "John Doe",
    "email": "john@test.com",
    "roles": ["ROLE_USER"]
  }
}
```

**POST** `/api/auth/login`
- Connecte un utilisateur existant
- **Body** :
```json
{
  "email": "john@test.com",
  "password": "password123"
}
```
- **Réponse** : Retourne un token JWT

#### 👤 Utilisateurs

**GET** `/api/users/profile` ⚠️ Requiert authentification
- Récupère le profil de l'utilisateur connecté
- **Headers** : `Authorization: Bearer <token>`

**PUT** `/api/users/profile` ⚠️ Requiert authentification
- Met à jour le profil de l'utilisateur connecté
- **Body** :
```json
{
  "name": "John Updated",
  "email": "john.updated@test.com",
  "password": "newpassword123"
}
```

**GET** `/api/users` ⚠️ Requiert rôle ADMIN
- Liste tous les utilisateurs (paginé)
- **Query params** : `?page=1&limit=10`

#### 📝 Articles

**GET** `/api/articles` ⚠️ Requiert authentification
- Liste les articles de l'utilisateur connecté (paginé)
- **Query params** : `?page=1&limit=10`

**GET** `/api/articles/{id}` ⚠️ Requiert authentification
- Récupère un article par son ID (seulement si l'article appartient à l'utilisateur)

**POST** `/api/articles` ⚠️ Requiert authentification
- Crée un nouvel article
- **Body** :
```json
{
  "title": "Mon article",
  "content": "Contenu de mon article"
}
```

**PUT** `/api/articles/{id}` ⚠️ Requiert authentification
- Met à jour un article (seulement si l'article appartient à l'utilisateur)
- **Body** :
```json
{
  "title": "Titre mis à jour",
  "content": "Contenu mis à jour"
}
```

**DELETE** `/api/articles/{id}` ⚠️ Requiert authentification
- Supprime un article (seulement si l'article appartient à l'utilisateur)

### Format de réponse - Pagination

Toutes les listes paginées retournent le format suivant :

```json
{
  "data": [...],
  "total": 40,
  "page": 1,
  "limit": 10,
  "has_previous": false,
  "has_next": true,
  "total_pages": 4
}
```
