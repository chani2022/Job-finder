# Plateforme de recherche d'emploi

Une application web moderne de recherche d'emploi développée avec **Symfony** et **API Platform**, conçue pour offrir une expérience fluide et sécurisée aux candidats comme aux recruteurs.

## ✨ Fonctionnalités principales

- 🔍 Recherche rapide d'annonces via **Meilisearch**
- 👤 Authentification via **OAuth2** (Google, Facebook…)
- 👤 Vérification d'authentification via **Gmail**
- 💳 Paiement sécurisé avec **Stripe**
- ⚙️ API REST avec **API Platform**
- 🐳 Déploiement facile via **Docker**

## 🚀 Stack technique

- Backend : Symfony 7.1, API Platform
- Auth : OAuth2 (Google, Facebook, etc.)
- Search : Meilisearch
- Paiement : Stripe
- Containerisation : Docker / Docker Compose

## 🔧 Installation

```bash
git clone https://github.com/chani2022/job-finder.git
cd job-finder
cp .env.test .env.local
docker compose up --build -d
```

## ♿ Accessibilité

L'API est accessible via https://offre.local/api
