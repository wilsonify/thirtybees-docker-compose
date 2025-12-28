---
title: "thirty bees Docker Compose"
description: "Deploy thirty bees e-commerce platform with Docker Compose and Cloudflare Tunnel"
---

# thirty bees Docker Compose

A complete Docker Compose setup for running [thirty bees](https://thirtybees.com/) e-commerce platform with:

- **thirty bees** - PHP 8.3 with Nginx
- **MariaDB** - Database server
- **Memcached** - Session and cache storage
- **Cloudflare Tunnel** - Secure HTTPS access without port forwarding

## Quick Start

```bash
# Clone the repository
git clone https://github.com/wilsonify/thirtybees-docker-compose.git
cd thirtybees-docker-compose/deploy/dev

# Copy and configure environment variables
cp .env.example .env
# Edit .env with your credentials

# Start the stack
docker compose up -d

# Run installation
docker exec dev-thirtybees-1 /usr/local/bin/install-thirtybees.sh
```

## Documentation

| Audience | Description |
|----------|-------------|
| [Users](/users/) | Shop owners and store managers |
| [Developers](/developers/) | Contributing to this project |
| [Admins](/admins/) | System administrators and DevOps |

## Architecture

```
┌─────────────────┐     ┌──────────────────┐
│   Cloudflare    │────▶│   cloudflared    │
│   (HTTPS)       │     │   (tunnel)       │
└─────────────────┘     └────────┬─────────┘
                                 │
                                 ▼
                        ┌────────────────┐
                        │  thirtybees    │
                        │  (nginx+php)   │
                        │  port 40080    │
                        └───────┬────────┘
                                │
              ┌─────────────────┼─────────────────┐
              ▼                 ▼                 ▼
      ┌───────────┐     ┌───────────┐     ┌───────────┐
      │  mariadb  │     │ memcached │     │  volumes  │
      │  (db)     │     │  (cache)  │     │  (data)   │
      └───────────┘     └───────────┘     └───────────┘
```

## License

This project is open source. thirty bees itself is licensed under the [Open Software License 3.0](https://opensource.org/licenses/OSL-3.0).
