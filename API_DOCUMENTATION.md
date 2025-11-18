# 📚 Documentation API - BatiSaaS

API REST complète pour la gestion de chantiers de construction.

## 🔐 Authentification

L'API utilise l'authentification par token Bearer. Incluez votre token dans le header Authorization de chaque requête :

```
Authorization: Bearer YOUR_API_TOKEN
```

### Obtenir un token API

Pour obtenir un token API, connectez-vous à l'interface web et allez dans **Paramètres > API**.
Vous pouvez générer un nouveau token ou révoquer les tokens existants.

## 📡 Base URL

```
https://votre-domaine.com/api
```

## 📋 Format des réponses

### Succès

```json
{
  "success": true,
  "message": "Success",
  "data": { ... },
  "timestamp": "2024-01-15T10:30:00+00:00"
}
```

### Erreur

```json
{
  "success": false,
  "message": "Error message",
  "errors": { ... },
  "timestamp": "2024-01-15T10:30:00+00:00"
}
```

### Pagination

```json
{
  "success": true,
  "message": "Success",
  "data": {
    "items": [ ... ],
    "pagination": {
      "total": 100,
      "per_page": 20,
      "current_page": 1,
      "total_pages": 5,
      "has_more": true
    }
  }
}
```

## 🏗️ Endpoints - Chantiers

### Liste des chantiers

```http
GET /api/chantiers
```

**Paramètres de requête:**
- `page` (int) - Numéro de page (défaut: 1)
- `per_page` (int) - Éléments par page (défaut: 20, max: 100)
- `status` (string) - Filtrer par statut: `planifie`, `en_cours`, `termine`, `suspendu`, `annule`
- `client_id` (int) - Filtrer par client

**Exemple de réponse:**
```json
{
  "success": true,
  "data": {
    "items": [
      {
        "id": 1,
        "name": "Rénovation appartement",
        "client_id": 5,
        "client_name": "Jean Dupont",
        "status": "en_cours",
        "progress": 65,
        "estimated_budget": 25000,
        "start_date": "2024-01-15",
        "end_date": "2024-03-31",
        "created_at": "2024-01-10 14:30:00"
      }
    ],
    "pagination": { ... }
  }
}
```

### Détails d'un chantier

```http
GET /api/chantiers/:id
```

**Exemple de réponse:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Rénovation appartement",
    "description": "Rénovation complète d'un appartement de 75m²",
    "client_id": 5,
    "client_name": "Jean Dupont",
    "client_email": "jean@example.com",
    "client_phone": "0612345678",
    "status": "en_cours",
    "progress": 65,
    "estimated_budget": 25000,
    "total_depenses": 16500,
    "depenses_count": 12,
    "start_date": "2024-01-15",
    "end_date": "2024-03-31"
  }
}
```

### Créer un chantier

```http
POST /api/chantiers
```

**Body (JSON):**
```json
{
  "name": "Rénovation appartement",
  "client_id": 5,
  "description": "Rénovation complète",
  "start_date": "2024-01-15",
  "end_date": "2024-03-31",
  "estimated_budget": 25000,
  "status": "planifie",
  "progress": 0
}
```

**Champs requis:** `name`, `client_id`, `start_date`

**Réponse:**
```json
{
  "success": true,
  "message": "Chantier created successfully",
  "data": {
    "id": 1
  }
}
```

### Mettre à jour un chantier

```http
PUT /api/chantiers/:id
```

**Body (JSON):**
```json
{
  "status": "en_cours",
  "progress": 75
}
```

### Supprimer un chantier

```http
DELETE /api/chantiers/:id
```

### Statistiques d'un chantier

```http
GET /api/chantiers/:id/stats
```

**Exemple de réponse:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Rénovation appartement",
    "total_depenses": 16500,
    "depenses_count": 12,
    "total_factures": 25000,
    "total_paye": 25000,
    "marge": 8500,
    "taux_marge": 34
  }
}
```

## 👥 Endpoints - Clients

### Liste des clients

```http
GET /api/clients
```

**Paramètres de requête:**
- `page` (int) - Numéro de page
- `per_page` (int) - Éléments par page
- `type` (string) - Filtrer par type: `individual`, `company`
- `status` (string) - Filtrer par statut: `active`, `inactive`
- `search` (string) - Rechercher dans nom, email, téléphone

### Détails d'un client

```http
GET /api/clients/:id
```

### Créer un client

```http
POST /api/clients
```

**Body (JSON):**
```json
{
  "type": "individual",
  "name": "Jean Dupont",
  "email": "jean@example.com",
  "phone": "0612345678",
  "address": "12 rue de la Paix",
  "postal_code": "75001",
  "city": "Paris",
  "status": "active"
}
```

**Champs requis:** `name`, `type`

### Mettre à jour un client

```http
PUT /api/clients/:id
```

### Supprimer un client

```http
DELETE /api/clients/:id
```

**Note:** Impossible de supprimer un client avec des chantiers existants.

### Chantiers d'un client

```http
GET /api/clients/:id/chantiers
```

## 📊 Endpoints - Statistiques

### Dashboard global

```http
GET /api/stats/dashboard
```

**Exemple de réponse:**
```json
{
  "success": true,
  "data": {
    "chantiers": {
      "total": 45,
      "en_cours": 12,
      "planifies": 8,
      "termines": 20
    },
    "clients": {
      "total": 38,
      "actifs": 35
    },
    "finances": {
      "chiffre_affaires_mois": 85000,
      "chiffre_affaires_annee": 950000,
      "factures_impayees": 12000,
      "marge_moyenne": 32.5
    }
  }
}
```

### Revenus mensuels

```http
GET /api/stats/revenue?year=2024
```

### Chantiers par statut

```http
GET /api/stats/chantiers-status
```

## 📄 Endpoints - Devis

### Liste des devis

```http
GET /api/devis
```

**Paramètres:**
- `status` - Filtrer par statut: `brouillon`, `envoye`, `accepte`, `refuse`, `expire`
- `client_id` - Filtrer par client
- `chantier_id` - Filtrer par chantier

### Créer un devis

```http
POST /api/devis
```

**Body:**
```json
{
  "client_id": 5,
  "chantier_id": 10,
  "number": "DEV-2024-0001",
  "date": "2024-01-15",
  "validity_date": "2024-02-15",
  "title": "Devis rénovation",
  "items": [
    {
      "description": "Main d'oeuvre",
      "quantity": 100,
      "unit_price": 45,
      "tva_rate": 20
    }
  ],
  "notes": "Conditions générales..."
}
```

## 🧾 Endpoints - Factures

### Liste des factures

```http
GET /api/factures
```

**Paramètres:**
- `status` - Filtrer par statut: `brouillon`, `envoyee`, `payee`, `en_retard`, `annulee`
- `client_id` - Filtrer par client
- `chantier_id` - Filtrer par chantier
- `unpaid` (bool) - Uniquement les impayées

### Enregistrer un paiement

```http
POST /api/factures/:id/payment
```

**Body:**
```json
{
  "amount": 5000,
  "date": "2024-01-15",
  "method": "virement",
  "notes": "Paiement partiel"
}
```

## 💰 Endpoints - Dépenses

### Liste des dépenses

```http
GET /api/depenses
```

**Paramètres:**
- `chantier_id` - Filtrer par chantier
- `category` - Filtrer par catégorie: `materiel`, `main_doeuvre`, `equipement`, `autre`
- `date_from` - Date de début
- `date_to` - Date de fin

### Créer une dépense

```http
POST /api/depenses
```

**Body:**
```json
{
  "chantier_id": 10,
  "description": "Achat matériaux",
  "amount": 2500,
  "date": "2024-01-15",
  "category": "materiel",
  "fournisseur_id": 3
}
```

## 🚚 Endpoints - Fournisseurs

### Liste des fournisseurs

```http
GET /api/fournisseurs
```

### Créer un fournisseur

```http
POST /api/fournisseurs
```

**Body:**
```json
{
  "name": "Matériaux Pro",
  "email": "contact@materiaux-pro.fr",
  "phone": "0143556677",
  "address": "25 rue industrielle",
  "postal_code": "93100",
  "city": "Montreuil",
  "status": "active"
}
```

## ⚠️ Codes d'erreur HTTP

- `200` - Succès
- `201` - Créé avec succès
- `400` - Requête invalide
- `401` - Non authentifié
- `403` - Accès refusé
- `404` - Ressource non trouvée
- `422` - Erreur de validation
- `500` - Erreur serveur

## 🔒 Rate Limiting

L'API est limitée à **1000 requêtes par heure** par token.

Les headers de réponse incluent:
- `X-RateLimit-Limit` - Limite totale
- `X-RateLimit-Remaining` - Requêtes restantes
- `X-RateLimit-Reset` - Timestamp de réinitialisation

## 📝 Webhooks

Configurez des webhooks pour recevoir des notifications en temps réel:

### Événements disponibles

- `chantier.created` - Nouveau chantier créé
- `chantier.updated` - Chantier mis à jour
- `chantier.deleted` - Chantier supprimé
- `facture.created` - Nouvelle facture
- `facture.paid` - Facture payée
- `devis.accepted` - Devis accepté

### Configuration

```http
POST /api/webhooks
```

**Body:**
```json
{
  "url": "https://votre-app.com/webhook",
  "events": ["chantier.created", "facture.paid"],
  "secret": "your_webhook_secret"
}
```

### Format des payloads

```json
{
  "event": "chantier.created",
  "timestamp": "2024-01-15T10:30:00+00:00",
  "data": {
    "id": 1,
    "name": "Nouveau chantier",
    ...
  }
}
```

## 🛠️ Exemples d'utilisation

### cURL

```bash
# Liste des chantiers
curl -X GET "https://api.batisaas.com/api/chantiers?page=1&per_page=20" \
  -H "Authorization: Bearer YOUR_TOKEN"

# Créer un client
curl -X POST "https://api.batisaas.com/api/clients" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "type": "individual",
    "name": "Jean Dupont",
    "email": "jean@example.com"
  }'
```

### JavaScript (Fetch)

```javascript
// Liste des chantiers
const response = await fetch('https://api.batisaas.com/api/chantiers', {
  headers: {
    'Authorization': 'Bearer YOUR_TOKEN',
    'Content-Type': 'application/json'
  }
});

const data = await response.json();
console.log(data.data.items);

// Créer un chantier
const newChantier = await fetch('https://api.batisaas.com/api/chantiers', {
  method: 'POST',
  headers: {
    'Authorization': 'Bearer YOUR_TOKEN',
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    name: 'Nouveau chantier',
    client_id: 5,
    start_date: '2024-01-15'
  })
});
```

### Python (Requests)

```python
import requests

headers = {
    'Authorization': 'Bearer YOUR_TOKEN',
    'Content-Type': 'application/json'
}

# Liste des chantiers
response = requests.get('https://api.batisaas.com/api/chantiers', headers=headers)
chantiers = response.json()['data']['items']

# Créer un client
client_data = {
    'type': 'individual',
    'name': 'Jean Dupont',
    'email': 'jean@example.com'
}

response = requests.post(
    'https://api.batisaas.com/api/clients',
    headers=headers,
    json=client_data
)
```

## 📞 Support

Pour toute question ou problème avec l'API, contactez-nous à [support@batisaas.com](mailto:support@batisaas.com).

## 📅 Changelog

### v1.0.0 (2024-01-15)
- Version initiale de l'API
- Endpoints pour chantiers, clients, devis, factures, dépenses
- Authentification par token Bearer
- Pagination et filtres
- Webhooks
