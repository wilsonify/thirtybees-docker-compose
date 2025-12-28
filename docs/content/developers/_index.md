---
title: "Developer Guide"
description: "Guide for developers contributing to this project"
weight: 2
---

# Developer Guide

This guide is for **developers** who want to contribute to or customize this Docker Compose setup.

## Project Structure

```
thirtybees-docker-compose/
├── deploy/
│   ├── dev/                    # Development environment
│   │   ├── docker-compose.yml  # Main compose file
│   │   ├── config.yaml         # Cloudflare tunnel config
│   │   ├── .env.example        # Environment template
│   │   └── makefile            # Helper commands
│   └── prod/                   # Production (Kubernetes)
│       ├── base/               # Kustomize base
│       └── helm-chart/         # Helm chart
├── src/
│   ├── cloudflared/            # Cloudflare tunnel container
│   ├── db/                     # MariaDB container
│   ├── memcached/              # Memcached container
│   └── thirtybees/             # Main application
│       ├── Dockerfile          # PHP 8.3 + Nginx
│       ├── default.conf        # Nginx config
│       ├── supervisord.conf    # Process manager
│       ├── install.sh          # Auto-installer
│       └── www/thirtybees/     # Application source
└── docs/                       # This documentation (Hugo)
```

## Local Development

### Prerequisites

- Docker & Docker Compose
- Git

### Setup

```bash
# Clone the repo
git clone https://github.com/wilsonify/thirtybees-docker-compose.git
cd thirtybees-docker-compose

# Set up environment
cd deploy/dev
cp .env.example .env
# Edit .env with your settings

# Build and start
docker compose up -d --build

# Run installation
docker exec dev-thirtybees-1 /usr/local/bin/install-thirtybees.sh

# View logs
docker compose logs -f thirtybees
```

### Access Points

| Service | URL | Purpose |
|---------|-----|---------|
| Store | http://localhost:40080/ | Frontend |
| Admin | http://localhost:40080/admin-dev/ | Back office |
| Database | localhost:3306 | MariaDB (via container) |

## Docker Images

### thirtybees (PHP + Nginx)

Built from `php:8.3-fpm-bookworm` with:

- PHP Extensions: bcmath, curl, gd, iconv, imap, intl, mbstring, pdo_mysql, soap, xml, zip, memcached
- Nginx as reverse proxy
- Supervisor managing both services
- Composer for dependencies

### MariaDB

Built from `mariadb:11.8.4-noble` with:

- Custom healthcheck
- Initialization scripts

### Memcached

Standard memcached for session storage and caching.

## Making Changes

### Modifying the Dockerfile

```bash
# Edit the Dockerfile
vim src/thirtybees/Dockerfile

# Rebuild
cd deploy/dev
docker compose build --no-cache thirtybees
docker compose up -d thirtybees
```

### Modifying Nginx Config

```bash
# Edit nginx config
vim src/thirtybees/default.conf

# Rebuild and restart
docker compose build thirtybees
docker compose up -d thirtybees
```

### Modifying Installation Script

The install script at `src/thirtybees/install.sh` uses environment variables:

| Variable | Default | Description |
|----------|---------|-------------|
| `SHOP_DOMAIN` | thirtybees-dev.renewed-renaissance.com | Store domain |
| `DB_SERVER` | mariadb | Database host |
| `DB_USER` | thirtybees | Database user |
| `DB_PASSWORD` | thirtybees | Database password |
| `DB_NAME` | thirtybees | Database name |
| `ADMIN_EMAIL` | admin@renewed-renaissance.com | Admin email |
| `ADMIN_PASSWORD` | - | Admin password |

## Contributing

1. Fork the repository
2. Create a feature branch: `git checkout -b feature/my-feature`
3. Make your changes
4. Test locally with Docker Compose
5. Submit a pull request

## Building Documentation

```bash
cd docs
hugo server -D  # Development server at localhost:1313
hugo            # Build static site to public/
```
