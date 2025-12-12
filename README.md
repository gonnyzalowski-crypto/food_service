# Streicher GmbH - Industrial Equipment E-Commerce Platform

<div align="center">

![Streicher GmbH](https://img.shields.io/badge/Streicher-GmbH-0066cc?style=for-the-badge)
![PHP 8.2](https://img.shields.io/badge/PHP-8.2-777BB4?style=flat-square&logo=php)
![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=flat-square&logo=mysql&logoColor=white)
![License](https://img.shields.io/badge/License-Proprietary-red?style=flat-square)

**Enterprise-grade B2B e-commerce platform for industrial machinery and heavy equipment**

[Live Demo](https://streichergmbh.com) · [Documentation](#user-guide) · [Deployment](#deployment)

</div>

---

## Executive Summary

**Streicher GmbH** is a comprehensive B2B e-commerce solution designed for the industrial equipment sector. The platform enables manufacturers and distributors to sell high-value machinery (€50,000 - €400,000+) with enterprise-grade security, multi-currency support, and full order lifecycle management.

### Market Opportunity

| Metric | Value |
|--------|-------|
| Global Industrial Equipment Market | $650B+ (2024) |
| B2B E-commerce Growth Rate | 17.5% CAGR |
| Digital Transformation Gap | 70% of industrial companies lack modern e-commerce |
| Average Order Value | €85,000 - €250,000 |

### Why Streicher?

- **Purpose-Built**: Designed specifically for high-value industrial sales, not adapted from retail
- **Bank Transfer Focus**: Optimized for B2B payment workflows (wire transfers, not credit cards)
- **Full Lifecycle**: Quote → Order → Payment → Shipping → Delivery tracking
- **Multi-Language**: German/English with easy expansion
- **Enterprise Security**: CSRF protection, rate limiting, secure sessions

---

## Key Features

### For Customers

| Feature | Description |
|---------|-------------|
| **Product Catalog** | Browse industrial equipment by category with detailed specifications |
| **Multi-Currency** | Toggle between EUR/USD with real-time exchange rates |
| **Quote Requests** | Request custom pricing for bulk orders or special requirements |
| **Secure Checkout** | Bank transfer payment with receipt upload |
| **Order Tracking** | Real-time shipment tracking with customs status updates |
| **Support Tickets** | Direct communication with sales and support teams |

### For Administrators

| Feature | Description |
|---------|-------------|
| **Dashboard** | Overview of orders, revenue, and pending actions |
| **Order Management** | Full lifecycle from pending → shipped → delivered |
| **Payment Verification** | Review and approve payment receipts |
| **Shipment Tracking** | Add tracking updates including customs hold/cleared |
| **Customer Management** | View customer history and communications |
| **Settings** | Configure bank details, company info, notifications |
| **Support Tickets** | Respond to customer inquiries and quote requests |

---

## Architecture

```bash
┌─────────────────────────────────────────────────────────────┐
│                      FRONTEND                                │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐          │
│  │   Catalog   │  │   Checkout  │  │   Tracking  │          │
│  │   Browser   │  │    Flow     │  │    Portal   │          │
│  └─────────────┘  └─────────────┘  └─────────────┘          │
├─────────────────────────────────────────────────────────────┤
│                      BACKEND (PHP 8.2)                       │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐          │
│  │  REST API   │  │   Admin     │  │   Email     │          │
│  │  Endpoints  │  │  Dashboard  │  │   Service   │          │
│  └─────────────┘  └─────────────┘  └─────────────┘          │
├─────────────────────────────────────────────────────────────┤
│                      DATABASE (MySQL 8.0)                    │
│  ┌─────────┐ ┌─────────┐ ┌─────────┐ ┌─────────┐            │
│  │ Orders  │ │Products │ │Shipments│ │ Users   │            │
│  └─────────┘ └─────────┘ └─────────┘ └─────────┘            │
└─────────────────────────────────────────────────────────────┘
```

### Tech Stack

| Layer | Technology |
|-------|------------|
| **Backend** | PHP 8.2 (vanilla, no framework overhead) |
| **Database** | MySQL 8.0 with PDO prepared statements |
| **Frontend** | Server-rendered PHP templates, CSS3 |
| **Deployment** | Docker, Railway, or any PHP host |
| **Security** | CSRF tokens, rate limiting, secure sessions |

---

## User Guide

### For Customers

#### 1. Browsing Products

1. Visit the **Products** page from the main navigation
2. Use the **Search** box to find specific equipment
3. Filter by **Category** (Pipelines, Mechanical, Electrical, etc.)
4. Toggle **EUR/USD** to view prices in your preferred currency
5. Click **View** to see full product details and specifications

#### 2. Requesting a Quote

1. Navigate to **Request Quote** in the menu
2. Fill in your contact information and company details
3. Specify product requirements and quantity
4. Submit the form - you'll receive a ticket number
5. Our sales team will respond within 24-48 hours

#### 3. Placing an Order

1. Add products to your cart
2. Proceed to **Checkout**
3. Enter billing and shipping information
4. Review the bank transfer details provided
5. Complete the wire transfer to our account
6. Upload your payment receipt on the confirmation page

#### 4. Tracking Your Order

1. Use your **Order Number** or **Tracking Number**
2. Visit the **Track Order** page
3. View real-time status updates including:
   - Payment confirmation
   - Processing status
   - Shipment pickup
   - Customs clearance
   - Delivery updates

### For Administrators

#### Accessing the Admin Panel

1. Navigate to `/admin/login`
2. Enter your admin credentials
3. Access the full dashboard

#### Managing Orders

1. **Dashboard** shows pending orders requiring action
2. Click an order to view details
3. **Verify Payment**: Review uploaded receipts, approve or decline
4. **Update Status**: Move orders through the workflow
5. **Add Tracking**: Enter shipment details and tracking number

#### Adding Tracking Updates

1. Open an order with an active shipment
2. Use the **Add Tracking Update** form
3. Select status: Picked Up, In Transit, Customs Hold, Customs Cleared, Delivered
4. Add location and description
5. Customer sees updates in real-time on their tracking page

#### Managing Support Tickets

1. Navigate to **Support Tickets** in admin menu
2. View all tickets including quote requests
3. Click to view details and respond
4. Close tickets when resolved

---

## Deployment

### Railway (Recommended)

Railway provides the simplest deployment with automatic MySQL provisioning.

#### Step 1: Create Railway Project

1. Go to [railway.app](https://railway.app)
2. Click **New Project**
3. Select **Deploy from GitHub repo**
4. Connect `gonnyzalowski-crypto/machine-store`

#### Step 2: Add MySQL Database

1. In your project, click **Add Service**
2. Select **Database** → **MySQL**
3. Railway auto-creates connection variables

#### Step 3: Configure Environment

Railway auto-populates these variables:
- `MYSQL_HOST`
- `MYSQL_PORT`
- `MYSQL_DATABASE`
- `MYSQL_USER`
- `MYSQL_PASSWORD`

Optional email configuration:
```bash
MAIL_HOST=smtp.sendgrid.net
MAIL_PORT=587
MAIL_USERNAME=apikey
MAIL_PASSWORD=your_sendgrid_api_key
MAIL_FROM_ADDRESS=store@streichergmbh.com
```

#### Step 4: Deploy

Railway automatically builds using the `Dockerfile` and deploys.

### Docker (Local Development)

```bash
# Clone repository
git clone https://github.com/gonnyzalowski-crypto/machine-store.git
cd machine-store

# Copy environment file
cp .env.example .env
# Edit .env with your database credentials

# Start containers
docker-compose up -d

# Access at http://localhost:8000
```

---

## 🔐 Security Features

| Feature | Implementation |
|---------|----------------|
| **CSRF Protection** | Token generation on session start, verified on all POST requests |
| **SQL Injection** | 100% PDO prepared statements with parameterized queries |
| **XSS Prevention** | All output escaped with `htmlspecialchars()` |
| **Session Security** | HttpOnly, SameSite=Strict, Secure flags; regeneration on login |
| **Rate Limiting** | Max 5 failed login attempts per IP per 15 minutes |
| **File Upload** | MIME type, extension, and content validation |
| **Password Storage** | bcrypt hashing with `password_hash()` |

---

## 📁 Project Structure

```
streicher/
├── web/                    # Web root
│   ├── index.php           # Single entry point (router)
│   ├── translations.php    # i18n strings (DE/EN)
│   ├── email_service.php   # SMTP email handling
│   ├── css/                # Stylesheets
│   └── templates/          # PHP templates
│       ├── admin/          # Admin panel views
│       └── pages/          # Static pages
├── uploads/                # User uploads (gitignored)
│   ├── payments/           # Payment receipts
│   └── tracking/           # Tracking documents
├── scripts/                # Database migrations
├── Dockerfile              # Container build
├── railway.json            # Railway deployment config
├── docker-compose.yml      # Local development
└── .env.example            # Environment template
```

---

## 💼 Investment Opportunity

### Business Model

| Revenue Stream | Description |
|----------------|-------------|
| **Platform License** | One-time setup + annual maintenance |
| **Transaction Fees** | 0.5-1% on orders processed |
| **Custom Development** | Integrations, customizations |
| **Support Contracts** | Premium support tiers |

### Competitive Advantages

1. **Vertical Focus**: Built for industrial B2B, not adapted from retail
2. **No Dependencies**: Vanilla PHP means no framework lock-in or security vulnerabilities
3. **Low Infrastructure Cost**: Runs on $5/month hosting, scales to enterprise
4. **Rapid Deployment**: Live in hours, not months
5. **Full Source Access**: No SaaS lock-in, customer owns the code

### Roadmap

| Phase | Timeline | Features |
|-------|----------|----------|
| **v1.0** | ✅ Complete | Core e-commerce, admin, tracking |
| **v1.1** | Q1 2025 | Email notifications, PDF invoices |
| **v1.2** | Q2 2025 | Customer portal, order history |
| **v2.0** | Q3 2025 | API integrations (ERP, CRM) |
| **v2.1** | Q4 2025 | Mobile app, push notifications |

---

## 📞 Contact

- **Website**: [streichergmbh.com](https://streichergmbh.com)
- **Email**: store@streichergmbh.com
- **Location**: Industriestraße 45, 93055 Regensburg, Germany

---

## 📄 License

Copyright © 2024 Streicher GmbH. All rights reserved.

This software is proprietary and confidential. Unauthorized copying, distribution, or use is strictly prohibited.

---

<div align="center">

**Built with precision. Engineered for industry.**

Made in Germany 🇩🇪

</div>
