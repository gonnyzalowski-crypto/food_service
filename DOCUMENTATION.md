# Streicher GmbH E-Commerce Platform Documentation

## Overview

The Streicher GmbH E-Commerce Platform is a full-featured industrial equipment and software sales website built for a German engineering company. The platform supports B2B sales of heavy industrial equipment and enterprise software solutions.

**Live URL:** https://streichergmbh.com

---

## Table of Contents

1. [Technology Stack](#technology-stack)
2. [Features Overview](#features-overview)
3. [Product Categories](#product-categories)
4. [User Features](#user-features)
5. [Admin Features](#admin-features)
6. [Order Flow](#order-flow)
7. [Payment System](#payment-system)
8. [Notification System](#notification-system)
9. [Database Schema](#database-schema)
10. [API Endpoints](#api-endpoints)
11. [Deployment](#deployment)
12. [Achievements](#achievements)

---

## Technology Stack

| Component | Technology |
|-----------|------------|
| Backend | PHP 8.x |
| Database | MySQL (Railway) |
| Frontend | HTML5, CSS3, JavaScript |
| Hosting | Railway.app |
| Version Control | GitHub |
| CI/CD | Railway Auto-Deploy |

---

## Features Overview

### Public Features
- **Multi-language Support**: German (DE) and English (EN)
- **Multi-currency Display**: EUR and USD with live exchange rates
- **Product Catalog**: 74+ products across 11 categories
- **Product Search**: Full-text search across products
- **Category Filtering**: Browse by product category
- **Shopping Cart**: Session-based cart management
- **Order Tracking**: Track orders by order number
- **Quote Requests**: Request custom quotes for bulk orders
- **Contact Forms**: Customer inquiry system
- **Live Chat**: Real-time customer support messaging

### Business Features
- **Hardware Products**: 56 industrial equipment items (including 10 aviation)
- **Software Products**: 18 enterprise software licenses (including 3 aviation)
- **Digital Delivery**: Software orders with license key delivery via email
- **Bank Transfer Payments**: Wire transfer with receipt upload
- **Order Management**: Full order lifecycle management
- **Shipment Tracking**: Integration with shipping carriers

---

## Product Categories

### Hardware Categories (6)
1. **Pipelines & Plants** - Pipeline construction and plant equipment
2. **Mechanical Engineering** - Mechanical engineering equipment
3. **Drilling Technology** - Drilling rigs and equipment
4. **Hydraulic Systems** - Hydraulic power units and components
5. **Instrumentation** - Measurement and control instruments
6. **Aviation Engineering** - Aircraft maintenance equipment, ground support systems (10 products, $52K-$180K)

### Additional Categories (3)
7. **Electrical Engineering** - Electrical systems and components
8. **Civil Engineering** - Civil and structural engineering equipment
9. **Raw Materials** - Raw and construction materials

### Software Category (1)
10. **Engineering Software** - Enterprise software solutions (18 products)
    - Pipeline simulation software
    - CAD/CAM solutions
    - SCADA systems
    - Process control software
    - Drilling simulation platforms
    - Aviation software (AeroCAD Pro Suite, FlightSim Certification, AeroMaint MRO Manager)

---

## User Features

### Account Management
- User registration and login
- Password reset functionality
- Profile management
- Order history

### Shopping Experience
- Browse products by category
- Search products by name/description
- View product details with images
- Add products to cart
- Adjust quantities
- Proceed to checkout

### Order Tracking
- Track orders by order number
- View order status updates
- Download invoices
- Communication with support

### Support
- FAQ section
- Contact form
- Live chat messaging
- Returns and warranty information

---

## Admin Features

### Dashboard (`/admin`)
- Overview statistics (orders, revenue, products)
- Recent orders list
- Quick action buttons
- Order status breakdown

### Order Management (`/admin/orders`)
- View all orders
- Filter by status
- Update order status
- Confirm payments
- Create shipments
- Add tracking numbers

### Product Management (`/admin/products`)
- Add new products
- Edit existing products
- Manage product images
- Set pricing
- Manage stock levels
- Toggle product visibility

### Customer Management (`/admin/customers`)
- View customer list
- Customer order history
- Customer communication

### Shipment Management (`/admin/shipments`)
- Create shipments
- Add tracking information
- Update shipment status

### Support Tickets (`/admin/tickets`)
- View customer inquiries
- Respond to tickets
- Close resolved tickets

### Settings (`/admin/settings`)
- **Company Information**: Name, VAT ID, Address
- **Bank Details**: Bank name, IBAN, BIC/SWIFT (displayed on checkout)
- **Contact Information**: Support email, phone, sales email
- **Tax & Shipping**: VAT rate, currency, free shipping threshold
- **Notification Settings**: Email notifications for orders/payments
- **Admin Users**: Manage admin accounts

---

## Order Flow

### Hardware Orders
```
1. Customer adds products to cart
2. Customer proceeds to checkout
3. Customer fills billing/shipping information
4. Order created with status: "Awaiting Payment"
5. Customer receives bank details
6. Customer makes bank transfer
7. Customer uploads payment receipt
8. Status changes to: "Payment Uploaded"
9. Admin reviews and confirms payment
10. Status changes to: "Payment Confirmed"
11. Admin creates shipment with tracking
12. Status changes to: "Shipped"
13. Customer receives tracking information
14. Status changes to: "Delivered" upon delivery
```

### Software Orders
```
1. Customer adds software products to cart
2. Customer proceeds to checkout
3. Customer fills billing information (no shipping needed)
4. Order created with type: "software"
5. Customer receives bank details
6. Customer makes bank transfer
7. Customer uploads payment receipt
8. Admin reviews and confirms payment
9. Admin sends license key via email
10. Status changes to: "License Sent" / "Delivered"
11. Customer receives software instructions via email
```

---

## Payment System

### Supported Payment Methods
- **Bank Transfer / Wire Payment**
  - Customer receives bank details at checkout
  - Bank details are configurable in admin settings
  - Customer uploads payment receipt/confirmation
  - Admin manually verifies payment

### Bank Details Configuration
Admin can configure:
- Bank Name (e.g., "Commerzbank AG Frankfurt")
- Account Holder (e.g., "Streicher GmbH")
- IBAN (e.g., "DE91 5004 0000 0123 4567 89")
- BIC/SWIFT (e.g., "COBADEFFXXX")

### Payment Receipt Upload
- Supported formats: PDF, JPG, PNG
- Maximum file size: 10MB
- Stored securely on server
- Linked to order for admin review

---

## Notification System

### Telegram Integration
- Admin receives Telegram notifications for:
  - New customer messages
  - Payment uploads
  - New orders
- Admin can reply to customers via Telegram
- Replies are sent to customer's email

### Email Notifications
- Order confirmation emails
- Payment confirmation emails
- Shipment notifications with tracking
- Software license delivery emails

---

## Database Schema

### Core Tables

#### `users`
- id, email, password_hash, full_name, phone, role, is_active, created_at

#### `products`
- id, sku, name, slug, description, specifications
- category_id, unit_price, currency, stock_quantity
- lead_time_days, weight_kg, dimensions
- image_url, **product_type** (hardware/software)
- is_active, is_featured, created_at, updated_at

#### `categories`
- id, name, slug, description, image_url, parent_id, sort_order

#### `orders`
- id, order_number, user_id, status, **order_type** (hardware/software/mixed)
- subtotal, tax_amount, shipping_amount, total_amount, currency
- billing_address (JSON), shipping_address (JSON)
- notes, created_at, updated_at

#### `order_items`
- id, order_id, product_id, sku, name, quantity, unit_price, total_price

#### `shipments`
- id, order_id, carrier, tracking_number, status
- shipped_at, delivered_at, created_at

#### `payment_uploads`
- id, order_id, file_path, file_name, status, notes, created_at

#### `tracking_communications`
- id, tracking_number, sender_type, message, created_at

#### `settings`
- id, setting_key, setting_value, updated_at

---

## API Endpoints

### Public Endpoints
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/` | Home page |
| GET | `/catalog` | Product catalog |
| GET | `/catalog?category={slug}` | Category filtered catalog |
| GET | `/product?sku={sku}` | Product detail page |
| GET | `/cart` | Shopping cart |
| POST | `/cart/add` | Add to cart |
| POST | `/cart/update` | Update cart quantity |
| POST | `/cart/remove` | Remove from cart |
| GET | `/checkout` | Checkout page |
| POST | `/checkout` | Process checkout |
| GET | `/track` | Order tracking page |
| POST | `/track` | Track order by number |
| GET | `/order/{id}` | Order status page |
| GET | `/order/{id}/payment` | Payment upload page |
| POST | `/order/{id}/payment` | Upload payment receipt |

### Admin Endpoints
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/admin` | Admin dashboard |
| GET | `/admin/orders` | Order management |
| GET | `/admin/orders/{id}` | Order detail |
| POST | `/admin/orders/{id}/status` | Update order status |
| POST | `/admin/orders/{id}/confirm-payment` | Confirm payment |
| GET | `/admin/products` | Product management |
| GET | `/admin/products/new` | Add product form |
| POST | `/admin/products` | Create product |
| GET | `/admin/products/{id}/edit` | Edit product form |
| POST | `/admin/products/{id}` | Update product |
| GET | `/admin/shipments` | Shipment management |
| POST | `/admin/shipments` | Create shipment |
| GET | `/admin/settings` | Settings page |
| POST | `/admin/settings` | Save settings |
| GET | `/admin/customers` | Customer list |
| GET | `/admin/tickets` | Support tickets |

### Utility Endpoints
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/health` | Health check |
| GET | `/setup` | Database setup |
| GET | `/update-product-images` | Update product image URLs |
| GET | `/seed-software-products` | Seed software products |
| GET | `/setup-software-category` | Setup software category |
| POST | `/telegram-webhook` | Telegram bot webhook |

---

## Deployment

### Railway Deployment
The application is deployed on Railway.app with:
- Automatic deployments from GitHub main branch
- MySQL database provisioned by Railway
- Environment variables for configuration

### Environment Variables
```
DB_HOST=<railway-mysql-host>
DB_PORT=3306
DB_NAME=streicher
DB_USER=<username>
DB_PASS=<password>
TELEGRAM_BOT_TOKEN=<bot-token>
TELEGRAM_ADMIN_ID=<admin-chat-id>
```

### Deployment Process
1. Push changes to GitHub main branch
2. Railway automatically detects changes
3. Railway builds and deploys the application
4. Database migrations run automatically on `/setup`

---

## Achievements

### E-Commerce Platform
- ✅ Full product catalog with 74+ products
- ✅ 11 product categories including Engineering Software and Aviation Engineering
- ✅ Shopping cart and checkout system
- ✅ Bank transfer payment with receipt upload
- ✅ Order tracking system
- ✅ Multi-language support (DE/EN)
- ✅ Multi-currency display (EUR/USD)

### Admin Panel
- ✅ Comprehensive dashboard with statistics
- ✅ Order management with status workflow
- ✅ Payment confirmation system
- ✅ Shipment creation with tracking
- ✅ Product management (CRUD)
- ✅ Customer management
- ✅ Configurable settings (banking, company info)
- ✅ Support ticket system

### Software Products
- ✅ 18 enterprise software products added (including 3 aviation software)
- ✅ Separate "Engineering Software" category
- ✅ Software badge on product cards
- ✅ Different order flow for digital delivery
- ✅ License delivery via email workflow
- ✅ Featured in landing page, profile, business sectors

### Aviation Engineering
- ✅ 10 aviation hardware products ($52K-$180K)
- ✅ 3 aviation software products ($35K-$145K)
- ✅ Separate "Aviation Engineering" category with ✈️ icon
- ✅ Featured in landing page, business sectors, profile page
- ✅ 2 aviation reference projects (Airbus A320, Lufthansa Technik)
- ✅ Products include: Engine Test Stands, Wing Assembly Jigs, Autoclaves, Avionics Test Systems

### Communication
- ✅ Telegram notifications for admin
- ✅ Admin can reply via Telegram
- ✅ Live chat messaging system
- ✅ Order status notifications

### UI/UX
- ✅ Modern, responsive design
- ✅ Mobile-friendly layout
- ✅ Professional German engineering aesthetic
- ✅ Clear navigation and breadcrumbs
- ✅ Product images with gallery view
- ✅ Currency toggle (EUR/USD)
- ✅ Language toggle (DE/EN)

### Security
- ✅ Password hashing
- ✅ Session management
- ✅ Admin authentication
- ✅ CSRF protection
- ✅ Input sanitization
- ✅ Secure file uploads

### Performance
- ✅ Optimized database queries
- ✅ Image optimization
- ✅ Efficient session handling
- ✅ Fast page loads

---

## File Structure

```
Streicher/
├── web/
│   ├── index.php              # Main application entry point
│   ├── assets/
│   │   ├── css/
│   │   │   └── style.css      # Main stylesheet
│   │   ├── js/
│   │   │   └── main.js        # JavaScript functionality
│   │   └── software-product.svg # Software product icon
│   ├── images/
│   │   ├── photos/            # Site photos
│   │   └── {product-slug}/    # Product images
│   └── templates/
│       ├── layout.php         # Main layout template
│       ├── home.php           # Home page
│       ├── catalog.php        # Product catalog
│       ├── product.php        # Product detail
│       ├── cart.php           # Shopping cart
│       ├── checkout.php       # Checkout page
│       ├── order_status.php   # Order tracking
│       ├── admin/             # Admin templates
│       └── pages/             # Static pages
├── .env                       # Environment variables
├── .gitignore                 # Git ignore rules
├── README.md                  # Project readme
└── DOCUMENTATION.md           # This file
```

---

## Support

For technical support or questions about this platform:
- **Email:** store@streichergmbh.com
- **Website:** https://streichergmbh.com/contact

---

*Documentation last updated: December 13, 2025*
