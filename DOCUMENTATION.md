# Gordon Food Service (Galveston) - Platform Documentation

## Overview

Gordon Food Service (Galveston) is a B2B offshore and onshore food provisioning platform serving the Gulf Coast energy industry. The platform enables registered contractors to request supply packages for offshore rigs, vessels, and onshore operations with automated pricing, contractor discounts, and a streamlined admin review workflow.

**Location:** 28th–36th St Port/Harborside industrial zone, Galveston, TX  
**Contact:** contact@gordonfoods.com | +1 213-653-0266

---

## Table of Contents

1. [Technology Stack](#technology-stack)
2. [Features Overview](#features-overview)
3. [Supply Services](#supply-services)
4. [Contractor Portal](#contractor-portal)
5. [Admin Features](#admin-features)
6. [Supply Request Flow](#supply-request-flow)
7. [Payment System](#payment-system)
8. [Pricing Engine](#pricing-engine)
9. [Database Schema](#database-schema)
10. [API Endpoints](#api-endpoints)
11. [Deployment](#deployment)
12. [Visual Design](#visual-design)

---

## Technology Stack

| Component | Technology |
|-----------|------------|
| Backend | PHP 8.1 with Composer (PSR-4 autoloading) |
| Database | MySQL 8.0 |
| Frontend | HTML5, CSS3, JavaScript |
| Containerization | Docker Compose |
| Namespace | `GordonFoodService\App` |

---

## Features Overview

### Public Features
- **Landing Page**: Modern hero section with food service imagery
- **Supply Portal**: Contractor-code gated access to supply requests
- **Contact Forms**: Customer inquiry system
- **Responsive Design**: Mobile-friendly Teal+Coral+Cream color scheme

### Business Features
- **Supply Packages**: Water, dry food, canned food, mixed supplies
- **Contractor Discounts**: Up to 35% off for registered contractors
- **Automated Pricing**: Based on crew size, duration, delivery location, and speed
- **Admin Review Workflow**: Accept/decline supply requests with payment instructions
- **Encrypted Payment Collection**: AES-256-GCM encrypted card data with 24h expiry

---

## Supply Services

### Supply Types
1. **Water** - Potable water and beverages (0.9x multiplier)
2. **Dry Food** - Extended shelf-life provisions (1.0x multiplier)
3. **Canned Food** - Bulk canned goods (1.05x multiplier)
4. **Mixed Supplies** - Custom-configured packages (1.1x multiplier)

### Delivery Locations
- **Pickup** - 0.85x multiplier
- **Local** - 0.95x multiplier
- **Onshore** - 1.0x multiplier
- **Nearshore** - 1.15x multiplier
- **Offshore Rig** - 1.35x multiplier

### Delivery Speeds
- **Standard** - 1.0x multiplier
- **Priority** - 1.2x multiplier
- **Emergency** - 1.45x multiplier

---

## Contractor Portal

### Access (`/supply`)
- Enter contractor code (e.g., `GFS-DEMO-0001`)
- View supply request history with Price and Discounted Price columns
- Submit new supply requests
- Pay for approved requests via encrypted card submission

### Supply Request Form
- Crew size (number of personnel)
- Duration (days)
- Supply types (checkboxes)
- Delivery location
- Delivery speed
- Storage life preference
- Effective date
- Notes (rig name, dock instructions, etc.)

---

## Admin Features

### Dashboard (`/admin`)
- Overview statistics
- Recent orders list
- Quick action buttons

### Supply Requests (`/admin/supply-requests`)
- View all supply requests with status filtering
- **Price** and **Discounted Price** columns
- Accept/decline requests with payment instructions
- View encrypted payment details (card last 4, expiry)
- Mark transactions as completed

### Supply Request Create/Edit (`/admin/supply-requests/new`, `/admin/supply-requests/{id}/edit`)
- **Create new requests** for any contractor
- **Edit all fields**: crew, duration, supplies, delivery, storage life
- **Price override**: Edit base price, system auto-calculates discounted price
- **Backdate**: Change `created_at` timestamp
- **Status changes**: Even for completed/declined requests
- **Payment instructions**: Custom instructions shown to contractor

### Contractors (`/admin/contractors`)
- **List all contractors** with company, contact, code, discount, status
- **Create new contractor**: Auto-generates unique code (e.g., `GFS-C7FB0405`)
- **Edit contractor**: Name, company, code, discount percent, active status

### Settings (`/admin/settings`)
- Company information
- Contact details
- Notification preferences

---

## Supply Request Flow

```
1. Contractor enters code at /supply
2. Contractor fills supply request form
3. Request created with status: "Awaiting Review"
4. Admin reviews request at /admin/supply-requests/{id}
5. Admin accepts with payment instructions OR declines with reason
6. Status changes to: "Approved (Awaiting Payment)" or "Declined"
7. Contractor sees Pay button in Supply Portal
8. Contractor submits card details (encrypted, 24h expiry)
9. Status changes to: "Payment Processing"
10. Admin views decrypted card details
11. Admin processes payment externally
12. Admin marks as "Transaction Completed"
13. Encrypted payment data is purged
```

---

## Payment System

### Encrypted Card Collection
- **AES-256-GCM encryption** with random IV
- **Encryption key** stored in `settings` table
- **24-hour expiry** - data auto-purged after expiry
- **Admin-only decryption** for manual processing

### Card Data Collected
- Cardholder name
- Card number (stored encrypted)
- Expiry month/year
- CVV (stored encrypted)
- Billing address
- Phone number

### Payment Statuses
1. `awaiting_review` - Initial submission
2. `approved_awaiting_payment` - Admin accepted, awaiting card
3. `payment_submitted_processing` - Card submitted, admin processing
4. `transaction_completed` - Payment processed successfully
5. `declined` - Request declined by admin

---

## Pricing Engine

### SupplyPricingWorker
Located at `app/Services/SupplyPricingWorker.php`

### Calculation Formula
```
base_price = base_rate × crew_size × duration_days × type_multiplier × location_multiplier × speed_multiplier
discounted_price = base_price × (1 - contractor_discount_percent / 100)
```

### Configuration (`supply_pricing_config` table)
- `base_rate_per_person_day`: $22.50
- `type_multipliers`: JSON object
- `location_multipliers`: JSON object
- `speed_multipliers`: JSON object

### Price Display
- **Price**: Original base price before discount
- **Discounted Price**: Final price after contractor discount applied

---

## Database Schema

### Core Tables

#### `contractors`
- id, full_name, company_name, contractor_code (unique)
- discount_percent, discount_eligible, active
- created_at, updated_at

#### `supply_pricing_config`
- id, config_json (multipliers), created_at, updated_at

#### `supply_requests`
- id, request_number (unique), contractor_id
- duration_days, crew_size, supply_types (JSON)
- delivery_location, delivery_speed, storage_life_months
- **base_price**, calculated_price, currency
- status, effective_date, notes
- reviewed_by, reviewed_at, decline_reason
- payment_instructions, approved_at, declined_at
- payment_submitted_at, completed_at
- created_at, updated_at

#### `supply_request_payments`
- id, supply_request_id, contractor_id
- billing_name, phone, billing_address (JSON)
- card_brand, card_last4, exp_month, exp_year
- encrypted_payload, iv_b64, tag_b64
- created_ip, expires_at, created_at

#### `settings`
- id, setting_key, setting_value, updated_at
- Includes `payment_encryption_key` for AES-256-GCM

---

## API Endpoints

### Public Endpoints
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/` | Landing page |
| GET | `/supply` | Supply Portal |
| POST | `/supply` | Submit supply request |
| POST | `/supply/payment` | Submit encrypted payment |
| GET | `/contact` | Contact page |
| POST | `/contact` | Submit contact form |

### Admin Endpoints
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/admin` | Admin dashboard |
| GET | `/admin/supply-requests` | Supply request list |
| GET | `/admin/supply-requests/new` | New supply request form |
| POST | `/admin/supply-requests/new` | Create supply request |
| GET | `/admin/supply-requests/{id}` | Supply request detail |
| GET | `/admin/supply-requests/{id}/edit` | Edit supply request form |
| POST | `/admin/supply-requests/{id}/edit` | Update supply request |
| POST | `/admin/supply-requests/{id}/accept` | Accept request |
| POST | `/admin/supply-requests/{id}/decline` | Decline request |
| POST | `/admin/supply-requests/{id}/complete` | Mark completed |
| GET | `/admin/contractors` | Contractor list |
| GET | `/admin/contractors/new` | New contractor form |
| POST | `/admin/contractors/new` | Create contractor |
| GET | `/admin/contractors/{id}/edit` | Edit contractor form |
| POST | `/admin/contractors/{id}/edit` | Update contractor |
| GET | `/admin/settings` | Settings page |
| POST | `/admin/settings` | Save settings |

### Utility Endpoints
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/setup-database` | Database setup/migration |

---

## Deployment

### Docker Compose
```yaml
services:
  app:        # PHP 8.1 application on port 8000
  db:         # MySQL 8.0 on port 3306
  mailpit:    # Email testing on port 8025
  phpmyadmin: # Database UI on port 8080
```

### Environment Variables
```
DB_HOST=db
DB_PORT=3306
DB_NAME=gordon_food_service
DB_USER=gfs
DB_PASS=secret
MAIL_HOST=mailpit
MAIL_PORT=1025
```

### Default Admin Credentials
- **Email:** admin@gordonfoods.com
- **Password:** admin123

### Demo Contractor Code
- **Code:** GFS-DEMO-0001

---

## Visual Design

### Color Palette (Teal + Coral + Cream)
- **Primary (Deep Ink):** #0B1220
- **Accent (Teal):** #0F766E
- **Highlight (Coral):** #F97316
- **Background (Cream):** #FAF7F2

### Typography
- **Body:** Inter
- **Headings:** Plus Jakarta Sans

### Landing Page Sections
1. **Hero**: Full-width with background image, stats, CTAs
2. **Trust Bar**: USDA Certified, HACCP Compliant, Coast Guard Approved, 24/7 Dispatch
3. **Services Cards**: Fresh & Frozen, Dry & Canned, Water & Beverages, Mixed Packages
4. **Gallery**: Warehouse and food imagery grid
5. **Why Choose Us**: Feature list with icons
6. **CTA Banner**: Access Supply Portal call-to-action

---

## File Structure

```
Gordon_Food_Service_Galveston-TX/
├── app/
│   └── Services/
│       └── SupplyPricingWorker.php    # Pricing calculation engine
├── web/
│   ├── index.php                       # Main router (3800+ lines)
│   ├── assets/
│   │   └── styles.css                  # Global CSS with design system
│   └── templates/
│       ├── layout.php                  # Public layout
│       ├── home.php                    # Landing page
│       ├── supply.php                  # Contractor portal
│       └── admin/
│           ├── layout.php              # Admin layout
│           ├── supply_requests.php     # Supply request list
│           ├── supply_request_detail.php
│           ├── supply_request_form.php # Create/edit form
│           ├── contractors.php         # Contractor list
│           └── contractor_form.php     # Create/edit form
├── migrations/
│   ├── 001_initial_schema.sql
│   ├── 002_order_flow.sql
│   └── 003_offshore_supply.sql
├── docker-compose.yml
├── composer.json
├── .env
└── DOCUMENTATION.md
```

---

## Support

For technical support or questions:
- **Email:** contact@gordonfoods.com
- **Phone:** +1 213-653-0266
- **Location:** Galveston, TX

---

*Documentation last updated: December 15, 2025*
