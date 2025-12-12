# Streicher GmbH - Industrial Parts & Equipment Store

A professional B2B e-commerce platform for industrial parts and equipment.

**Domain**: streichergmbh.com

## Features

- Product catalog with categories
- Shopping cart & checkout
- Bank transfer payment with receipt upload
- Order tracking with real-time updates
- Admin dashboard with full order management
- Support ticket system
- Multi-language support (DE/EN)

## Tech Stack

- PHP 8.2
- MySQL 8.0
- Docker

## Local Development

```bash
# Clone and setup
git clone https://github.com/YOUR_USERNAME/machine-store.git
cd machine-store
cp .env.example .env
# Edit .env with your DB credentials

# Using Docker
docker-compose up -d

# Or using PHP built-in server
composer install
php -S 0.0.0.0:8000 -t web
```

Access at http://localhost:8000

## Railway Deployment

### Environment Variables Required

```
DB_HOST=your-mysql-host
DB_PORT=3306
DB_NAME=streicher
DB_USER=your-db-user
DB_PASS=your-db-password
```

### Deploy Steps

1. Push to GitHub repository `machine-store`
2. Connect Railway to GitHub repo
3. Add MySQL service in Railway
4. Set environment variables from Railway MySQL service
5. Deploy

### Database Setup

After first deploy, run the migration:
```sql
CREATE DATABASE streicher CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- Run migrations/001_initial_schema.sql
```

## Admin Access

- URL: `/admin/login`
- Create admin user:

```sql
INSERT INTO users (company_id, email, password_hash, full_name, role)
VALUES (NULL, 'admin@streichergmbh.com', '$2y$10$YOUR_BCRYPT_HASH', 'Admin', 'admin');
```

Generate bcrypt hash: `php -r "echo password_hash('your-password', PASSWORD_DEFAULT);"`

## Security Features

- CSRF protection on forms
- Secure session handling
- Prepared statements (SQL injection prevention)
- XSS protection with output escaping
- Session regeneration on login

## File Structure

```
├── web/                 # Web root
│   ├── index.php       # Main router
│   ├── templates/      # PHP templates
│   └── translations.php
├── uploads/            # User uploads (gitignored)
├── images/             # Product images
├── scripts/            # Utility scripts
├── Dockerfile
├── docker-compose.yml
└── railway.json
```

## API Endpoints

- `GET /api/products` - List products
- `POST /api/cart` - Add to cart
- `GET /api/cart` - Get cart
- `POST /api/checkout` - Create order
- `POST /api/orders/{id}/upload-payment` - Upload payment receipt
- `POST /api/track` - Track shipment
