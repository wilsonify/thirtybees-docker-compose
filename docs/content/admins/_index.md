---
title: "Admin Guide"
description: "Guide for system administrators and DevOps"
weight: 3
---

# Administrator Guide

This guide is for **system administrators and DevOps** who deploy and maintain the infrastructure.

## Deployment Options

| Environment | Directory | Technology |
|-------------|-----------|------------|
| Development | `deploy/dev/` | Docker Compose |
| Production | `deploy/prod/` | Kubernetes (Helm/Kustomize) |

## Docker Compose Deployment

### Prerequisites

- Docker Engine 24+
- Docker Compose v2+
- Cloudflare account with tunnel token

### Configuration

#### Environment Variables (.env)

```bash
# Copy template
cp deploy/dev/.env.example deploy/dev/.env
```

Edit `.env` with your values:

```ini
# Cloudflare Tunnel
CLOUD_FLARE_TOKEN=your-tunnel-token

# Database
MARIADB_DATABASE=thirtybees
MARIADB_USER=thirtybees
MARIADB_PASSWORD=secure-password-here
MARIADB_ROOT_PASSWORD=secure-root-password

# Application
SHOP_DOMAIN=your-domain.com
DB_SERVER=mariadb
DB_USER=thirtybees
DB_PASSWORD=secure-password-here
DB_NAME=thirtybees

# Admin
ADMIN_FIRSTNAME=Admin
ADMIN_LASTNAME=User
ADMIN_EMAIL=admin@your-domain.com
ADMIN_PASSWORD=secure-admin-password
```

#### Cloudflare Tunnel Setup

1. Go to [Cloudflare Zero Trust Dashboard](https://one.dash.cloudflare.com/)
2. Navigate to **Networks → Tunnels**
3. Create a new tunnel and copy the token
4. Add the token to `.env` as `CLOUD_FLARE_TOKEN`
5. Configure public hostname in Cloudflare dashboard:
   - Subdomain: your-subdomain
   - Domain: your-domain.com
   - Service: http://localhost:40080

### Commands

```bash
cd deploy/dev

# Start all services
docker compose up -d

# View status
docker compose ps

# View logs
docker compose logs -f

# Stop services
docker compose down

# Stop and remove volumes (WARNING: deletes data)
docker compose down -v

# Rebuild after changes
docker compose build --no-cache
docker compose up -d
```

## Container Management

### Service Overview

| Container | Image | Ports | Purpose |
|-----------|-------|-------|---------|
| thirtybees | dev-thirtybees | 40080:80 | PHP app + Nginx |
| mariadb | dev-mariadb | 3306 (internal) | Database |
| memcached | dev-memcached | 11211 (internal) | Cache |
| cloudflared | dev-cloudflared | - | HTTPS tunnel |

### Health Checks

```bash
# Check container health
docker compose ps

# Test HTTP response
curl -I http://localhost:40080/

# Check database connectivity
docker exec dev-thirtybees-1 mysql -h mariadb -u thirtybees -p$DB_PASSWORD -e "SELECT 1"

# View PHP info
docker exec dev-thirtybees-1 php -v
```

### Executing Commands

```bash
# Shell into container
docker exec -it dev-thirtybees-1 bash

# Run thirty bees CLI
docker exec dev-thirtybees-1 php /var/www/default/bin/console

# Clear cache
docker exec dev-thirtybees-1 rm -rf /var/www/default/cache/smarty/compile/*
```

## Data Management

### Volumes

| Volume | Mount Point | Purpose |
|--------|-------------|---------|
| mariadb-data | /var/lib/mysql | Database files |
| thirtybees-config | /var/www/default/config | Shop configuration |
| thirtybees-img | /var/www/default/img | Product images |
| thirtybees-modules | /var/www/default/modules | Installed modules |
| thirtybees-themes | /var/www/default/themes | Shop themes |

### Backup

```bash
# Backup database
docker exec dev-mariadb-1 mariadb-dump -u root -p$MARIADB_ROOT_PASSWORD thirtybees > backup.sql

# Backup volumes
docker run --rm -v dev_thirtybees-img:/data -v $(pwd):/backup alpine tar czf /backup/img-backup.tar.gz -C /data .

# Backup all volumes
for vol in config img modules themes; do
  docker run --rm -v dev_thirtybees-$vol:/data -v $(pwd):/backup alpine tar czf /backup/$vol-backup.tar.gz -C /data .
done
```

### Restore

```bash
# Restore database
cat backup.sql | docker exec -i dev-mariadb-1 mariadb -u root -p$MARIADB_ROOT_PASSWORD thirtybees

# Restore volume
docker run --rm -v dev_thirtybees-img:/data -v $(pwd):/backup alpine sh -c "rm -rf /data/* && tar xzf /backup/img-backup.tar.gz -C /data"
```

## SSL/HTTPS Configuration

The stack uses Cloudflare Tunnel for HTTPS. Key settings:

1. **Cloudflare handles SSL termination** - Traffic is HTTPS externally, HTTP internally
2. **Nginx passes HTTPS header** - `fastcgi_param HTTPS "on"` tells PHP the connection is secure
3. **Database setting** - `PS_SSL_ENABLED = 1` must be set

If you see redirect loops on login:

```bash
# Enable SSL in database
docker exec dev-thirtybees-1 mysql -h mariadb -u thirtybees -p$DB_PASSWORD thirtybees \
  -e "UPDATE tb_configuration SET value = '1' WHERE name = 'PS_SSL_ENABLED';"
```

## Monitoring

### Logs

```bash
# All services
docker compose logs -f

# Specific service
docker compose logs -f thirtybees

# Nginx access logs
docker exec dev-thirtybees-1 tail -f /var/log/nginx/access.log

# PHP-FPM logs
docker exec dev-thirtybees-1 tail -f /var/log/php-fpm.log
```

### Resource Usage

```bash
# Container stats
docker stats

# Disk usage
docker system df
```

## Troubleshooting

### Container Won't Start

```bash
# Check logs
docker compose logs thirtybees

# Check if port is in use
ss -tlnp | grep 40080
```

### Database Connection Failed

```bash
# Check mariadb is running
docker compose ps mariadb

# Check credentials
docker exec dev-mariadb-1 mariadb -u root -p$MARIADB_ROOT_PASSWORD -e "SELECT user, host FROM mysql.user;"
```

### 502 Bad Gateway

```bash
# Check PHP-FPM is running
docker exec dev-thirtybees-1 supervisorctl status

# Restart services inside container
docker exec dev-thirtybees-1 supervisorctl restart all
```

### Clear All Caches

```bash
docker exec dev-thirtybees-1 rm -rf \
  /var/www/default/cache/smarty/compile/* \
  /var/www/default/cache/smarty/cache/* \
  /var/www/default/cache/class_index.php
```

## Security Checklist

- [ ] Change default admin password
- [ ] Use strong database passwords
- [ ] Keep `.env` file secure (not in version control)
- [ ] Enable Cloudflare WAF rules
- [ ] Regular backups
- [ ] Keep Docker images updated
- [ ] Review Cloudflare Access policies
