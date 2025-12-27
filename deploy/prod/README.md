# HatShop Production Deployment

Kubernetes deployment for HatShop production environment using KIND (Kubernetes in Docker) with Neon PostgreSQL.

## Overview

This deployment runs on a local KIND cluster with **Chapter 12 features**:
- **PHP Application** - HatShop application with all features up to Chapter 12
- **Nginx** - Reverse proxy for `/prod` path routing
- **Cloudflared** - Cloudflare Tunnel for public access
- **Neon PostgreSQL** - Cloud-hosted PostgreSQL database

## Features Enabled (Chapter 12)

| Chapter | Feature |
|---------|---------|
| 2 | Departments |
| 3 | Categories |
| 4 | Products, Product Details, Pagination |
| 5 | Search |
| 6 | PayPal Payments |
| 7 | Catalog Administration |
| 8 | Shopping Cart |
| 9 | Customer Orders |
| 10 | Product Recommendations |
| 11 | Customer Details |
| 12 | Order Storage |

## Architecture

```
Internet
    │
    ▼
Cloudflare Tunnel (cloudflared pod)
    │
    ▼
Nginx Service (NodePort :30082 → host :10082)
    │
    └── /prod path routing
            │
            ▼
      PHP Service (:80)
        ├── ConfigMap (thirtybees-config)
        ├── Secret (thirtybees-secrets)
        │
        └── Neon PostgreSQL (external)
              └── ep-jolly-bird-a53fz3ri-pooler.us-east-2.aws.neon.tech
```

## Database Configuration

This deployment uses Neon PostgreSQL as the database backend:

| Setting | Value |
|---------|-------|
| Host | `ep-jolly-bird-a53fz3ri-pooler.us-east-2.aws.neon.tech` |
| Database | `neondb` |
| Username | `neondb_owner` |
| SSL Mode | `require` |

## Quick Start

### Prerequisites

1. Docker installed and running
2. KIND installed (`brew install kind` or `go install sigs.k8s.io/kind@latest`)
3. kubectl installed
4. Helm installed (for SOPS operator)
5. SOPS installed (for secret encryption)
6. Cloudflare tunnel token

### Deploy

```bash
# 1. Create .env file with Cloudflare tunnel token
echo "CLOUD_FLARE_TOKEN=your-cloudflare-tunnel-token" > .env

# 2. Set age secret key for SOPS
export AGE_SECRET_KEY=$(cat ~/.sops/age/keys.txt)

# 3. Encrypt secrets (if not already encrypted)
make encrypt-secrets

# 4. Deploy everything
make all
```

### Access URLs

| Environment | URL |
|-------------|-----|
| Local | http://localhost:10082/prod |
| Cloudflare | https://thirtybees.renewed-renaissance.com/prod |

## Makefile Commands

| Command | Description |
|---------|-------------|
| `make all` | Create cluster, load image, setup SOPS, deploy |
| `make cluster-create` | Create KIND cluster |
| `make cluster-delete` | Delete KIND cluster |
| `make load-image` | Load thirtybees image into cluster |
| `make sops-operator` | Install SOPS secrets operator |
| `make sops-age-secret` | Create age key secret for SOPS |
| `make encrypt-secrets` | Encrypt secrets with SOPS |
| `make secret` | Create cloudflared secret from .env |
| `make deploy` | Deploy application |
| `make undeploy` | Remove deployment |
| `make status` | Show deployment status |
| `make logs` | View logs from all components |
| `make logs-php` | Follow PHP logs |
| `make logs-nginx` | Follow Nginx logs |
| `make logs-cloudflared` | Follow Cloudflared logs |
| `make port-forward` | Port forward nginx to localhost:8082 |
| `make test-db` | Test database connection |
| `make show-db-config` | Show database configuration |
| `make restart` | Restart all deployments |
| `make clean` | Undeploy and delete cluster |

## Differences from Stage

| Aspect | Stage | Production |
|--------|-------|------------|
| Namespace | `thirtybees-stage` | `thirtybees-prod` |
| Database | Local PostgreSQL | Neon PostgreSQL |
| Path prefix | `/stage` | `/prod` |
| NodePort | 30081 | 30082 |
| Host port | 10081 | 10082 |
| PHP replicas | 1 | 2 |
| Nginx replicas | 1 | 2 |
| PayPal | Sandbox | Production |
| Debugging | false | false |

## Secrets Management

Secrets are managed using SOPS with age encryption.

### Encrypt secrets

```bash
cd base
sops encrypt --age age1mg4zlx7p736nnrp7glt7gyd96s33kmy8wlck903m0srkkndeaawqrqfzek \
  --encrypted-regex "PASSWORD|EMAIL|TOKEN" \
  thirtybees-secrets.dec.yaml > thirtybees-secrets.enc.yaml
```

### Decrypt secrets (for editing)

```bash
cd base
sops decrypt thirtybees-secrets.enc.yaml > thirtybees-secrets.dec.yaml
```

## Troubleshooting

### Database connection issues

1. Check configmap has correct Neon host:
   ```bash
   make show-db-config
   ```

2. Verify SSL mode is enabled in application

3. Check PHP logs for connection errors:
   ```bash
   make logs-php
   ```

### Pod not starting

1. Check pod status:
   ```bash
   make status
   ```

2. Check events:
   ```bash
   kubectl -n thirtybees-prod get events --sort-by='.lastTimestamp'
   ```

### Image not found

1. Build the image:
   ```bash
   cd ../../src/thirtybees
   docker build -t ghcr.io/wilsonify/thirtybees:latest .
   ```

2. Load into cluster:
   ```bash
   make load-image
   ```
