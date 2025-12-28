---
title: "User Guide"
description: "Guide for shop owners and store managers"
weight: 1
---

# User Guide

This guide is for **shop owners and store managers** who want to run their online store using thirty bees.

## Getting Started

### Prerequisites

- A server or computer with Docker installed
- A domain name (optional, but recommended)
- Cloudflare account (for secure HTTPS access)

### First-Time Setup

1. **Access your store** at your configured domain (e.g., `https://your-domain.com/`)

2. **Access the admin panel** at `/admin-dev/`
   - Default email: `admin@your-domain.com`
   - Password: As configured in `.env`

### Admin Panel Overview

The thirty bees admin panel lets you:

- **Catalog** - Manage products, categories, and attributes
- **Orders** - Process and track customer orders
- **Customers** - View and manage customer accounts
- **Marketing** - Create discounts, promotions, and email campaigns
- **Shipping** - Configure carriers and shipping costs
- **Payment** - Set up payment methods
- **Statistics** - View sales and visitor analytics

## Common Tasks

### Adding Products

1. Go to **Catalog → Products**
2. Click **Add new product**
3. Fill in:
   - Name and description
   - Price and tax rules
   - Categories
   - Images
4. Set **Status** to "Enabled"
5. Click **Save**

### Managing Orders

1. Go to **Orders → Orders**
2. Click on an order to view details
3. Update order status as you process it:
   - Payment accepted
   - Processing in progress
   - Shipped
   - Delivered

### Creating Discounts

1. Go to **Price Rules → Cart Rules**
2. Click **Add new cart rule**
3. Configure:
   - Code (or leave empty for automatic)
   - Discount type (percentage, amount, free shipping)
   - Conditions (minimum order, specific products)
4. Click **Save**

## Backup & Recovery

Your data is stored in Docker volumes. Contact your administrator to:

- Schedule regular backups
- Restore from a backup if needed

## Getting Help

- [thirty bees Forums](https://forum.thirtybees.com/)
- [thirty bees Documentation](https://docs.thirtybees.com/)
- Contact your system administrator
