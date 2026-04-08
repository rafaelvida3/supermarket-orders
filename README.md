# Supermarket Orders

A full-stack supermarket order management application built with **Laravel 12**, **Vue 3**, and **PostgreSQL**.

This project started as a technical challenge and was later refined into a portfolio case study focused on **separation of responsibilities**, **stock integrity**, **sound modeling decisions**, and **clear architecture**.

**Live demo:** https://supermarket-orders.onrender.com/

## Overview

The application covers the core order flow end to end:

- initial catalog import from an `.xlsx` spreadsheet
- product search with autocomplete
- dynamic order building with multiple items
- per-item subtotal and order total calculation
- stock validation on the front end and, most importantly, on the back end
- order and order item persistence
- stock deduction at confirmation time
- listing previously created orders
- detailed view of a saved order

More than simply "working", the project was organized to make it explicit **where each rule lives** and **how the domain is protected when concurrency and inconsistent input are involved**.

---

## What this project demonstrates

This repository was structured to highlight a few points I consider essential in business software:

- **Lean controllers**: the critical order creation orchestration is not scattered across the controller.
- **Server-side domain protection**: the front end improves UX, but the source of truth remains on the back end.
- **Consistent persistence**: order creation and stock deduction happen inside a **transaction**.
- **Concurrency handled carefully**: order products are loaded with `lockForUpdate()` before stock is decremented.
- **Modeling with preserved history**: `order_items` stores `unit_price` and `subtotal`, avoiding reliance on the current catalog price to reconstruct past orders.
- **Defense in depth**: the front end tries to avoid duplicate selection, but the back end also consolidates repeated products before saving.
- **Controlled scope**: no overengineering. The project uses abstractions only where they genuinely improve maintainability.

---

## Architecture

### Back-end

- **Laravel 12** as a REST API
- **PostgreSQL** as the primary database
- **Maatwebsite Excel** for product spreadsheet import
- **CreateOrderAction** to encapsulate the most important use case in the system
- **Form Request** for input validation
- **Eloquent** for modeling and persistence

### Front-end

- **Vue 3** with the Composition API
- **Vite** as the bundler
- **Vue Router** for SPA navigation
- **PrimeVue** for UI components
- **Tailwind CSS** for layout and responsiveness
- **Axios** for API consumption
- **Vitest** for front-end unit tests

### Infrastructure

- **Docker Compose** for local development with three services:
  - `app` (Laravel / PHP)
  - `frontend` (Vite / Vue)
  - `postgres` (PostgreSQL 18)
- **Multi-stage Dockerfile** for local usage and for a build/deploy flow closer to production

---

## Main business flow

### 1. Initial catalog

The catalog is loaded from `storage/app/Products.xlsx`.

The `php artisan products:import` command:

- reads the spreadsheet through `ProductsImport`
- validates the presence of required columns for each row
- creates products in the database
- avoids reimporting when records already exist

The spreadsheet included in the repository contains **50 valid products** ready for import.

### 2. Order building

On the front end, the user provides:

- customer name
- delivery date
- list of products and quantities

Order building is centralized in the `useOrderForm` composable, which handles:

- form state
- product autocomplete
- duplicate item merging in the interface
- total calculation
- payload assembly for the API
- order loading in view mode

### 3. Safe order creation

Order creation is executed in `CreateOrderAction`.

This flow does the following:

1. consolidates repeated products in the payload
2. opens a database transaction
3. loads products with `lockForUpdate()`
4. validates existence and stock availability
5. creates the order with an initial zero total
6. creates `order_items` with a unit price snapshot
7. deducts stock from each product
8. updates the final order total

This design avoids two common problems:

- **partially saved orders**
- **inconsistent stock deductions in concurrent scenarios**

---

## Modeling decisions

### Main tables

#### `products`
Product catalog and current stock.

Relevant fields:
- `id`
- `name`
- `price`
- `qty_stock`

#### `orders`
Order header.

Relevant fields:
- `id`
- `customer_name`
- `delivery_date`
- `total`

#### `order_items`
Order items.

Relevant fields:
- `order_id`
- `product_id`
- `qty`
- `unit_price`
- `subtotal`

### Why store `unit_price` and `subtotal`?

Because an order is a historical record. If the catalog price changes tomorrow, yesterday's order must remain intact.

### Why have `unique(order_id, product_id)`?

Because the same product should not exist more than once inside the same saved order. This also forces the system to preserve model consistency at the database level, not only in the UI.

---

## Implementation decisions

### Lean controller + dedicated action

Order creation was extracted to `app/Actions/Orders/CreateOrderAction.php`.

This reduces controller coupling and makes the critical rule easier to:

- test
- evolve
- reuse
- audit

### Front-end composable instead of a global store

The form state was centralized in `useOrderForm.js`.

For this scope, using Pinia or Vuex would be unnecessary. The composable solves the problem well with less complexity.

### Global overlay without a state library

Shared loading was implemented with `useLoadingOverlay`, using reactive state at module scope. It is simple, readable, and sufficient for the current need.

### Responsiveness handled with specific layouts

`OrderItemsSection.vue` has a dedicated structure for desktop and mobile.

There is some markup duplication, but it was a conscious trade-off to preserve interaction clarity in each context, especially when filling products and quantities on smaller screens.

### Product search guided by UX

The `GET /api/products` endpoint:

- accepts the `q` parameter
- uses `ILIKE` for case-insensitive search in PostgreSQL
- limits the response to 10 items

This limit prevents noisy autocomplete behavior and reduces unnecessary rendering cost.

---

## Project structure

```text
app/
├── Actions/Orders/CreateOrderAction.php      # Order creation use case
├── Console/Commands/ImportProducts.php       # Product spreadsheet import
├── Http/Controllers/
│   ├── OrderController.php                   # List, create, and show orders
│   └── ProductController.php                 # Search/list products
├── Http/Requests/StoreOrderRequest.php       # Order payload validation
├── Imports/ProductsImport.php                # Spreadsheet-to-Product mapping
└── Models/                                   # Domain entities

resources/js/
├── components/
│   ├── common/LoadingOverlay.vue             # Global loading overlay
│   └── orders/OrderItemsSection.vue          # Order items UI
├── composables/
│   ├── useLoadingOverlay.js                  # Shared loading state
│   └── useOrderForm.js                       # Front-end form rules
├── pages/
│   ├── OrdersList.vue                        # Orders list
│   ├── OrderPage.vue                         # New order / view order
│   └── NotFound.vue                          # SPA 404 page
├── services/
│   ├── apiClient.js                          # Axios instance
│   ├── orderService.js                       # Orders API consumption
│   └── productService.js                     # Products API consumption
└── helpers/index.js                          # Date and currency formatting

database/
├── migrations/                               # Database structure
└── seeders/OrderSeeder.php                   # Demo order seeder

docker-compose.yml                            # Full local environment
Dockerfile                                    # Multi-stage build
```

---

## API endpoints

### `GET /api/products`
Lists products or searches by term.

Example:

```http
GET /api/products?q=ar
```

Response:

```json
[
  {
    "id": 1,
    "name": "RICE...",
    "price": "10.50",
    "qty_stock": 20
  }
]
```

### `GET /api/orders`
Returns the order list in descending order.

### `GET /api/orders/{id}`
Returns the order header and its items.

### `POST /api/orders`
Creates a new order.

Payload:

```json
{
  "customer_name": "Rafael",
  "delivery_date": "2026-04-10",
  "items": [
    { "product_id": 1, "qty": 2 },
    { "product_id": 5, "qty": 1 }
  ]
}
```

Success response:

```json
{
  "order_id": 1,
  "total": "28.00",
  "message": "Order created successfully."
}
```

---

## Validation and rule protection

### On the front end

- prevents submission without a name
- prevents submission without a date
- requires at least one product
- shows subtotal and total in real time
- warns when a product is out of stock
- prevents exceeding stock through `InputNumber`
- tries to merge duplicate items before submission

### On the back end

- validates payload structure with `StoreOrderRequest`
- validates product existence
- validates minimum quantity
- validates available stock using data locked inside the transaction
- consolidates duplicate items regardless of what came from the client

In other words: **the interface helps; the server guarantees**.

---

## Seeds and demo data

In addition to catalog import, the project includes `OrderSeeder` with **6 sample orders**.

An important detail: the seeder does not insert records "by force". It calls `CreateOrderAction` itself, keeping the demo data aligned with the same real business rules.

This is a small but important detail that helps avoid misleading seed data.

---

## Automated tests

The repository includes tests on both the back end and the front end.

### Back-end — PHPUnit

Current coverage for the main scenarios:

- order creation with stock deduction
- rejection due to insufficient stock
- order list sorting
- order details
- 404 for a missing order
- duplicate product consolidation
- valid product import
- invalid spreadsheet row discard
- product list filtering and limiting

### Front-end — Vitest

Current coverage for:

- total calculation in the composable
- item addition and removal
- payload assembly
- product suggestion loading
- formatting helpers
- order and product services

In total, the project currently includes **28 automated tests** covering the most important domain and interface flows.

---

## Running with Docker

### Requirements

- Docker Desktop
- Docker Compose

### Steps

```bash
cp .env.example .env
docker compose up --build
```

Available services:

- Laravel API: `http://localhost:8000`
- Vue/Vite: `http://localhost:5173`
- PostgreSQL: `localhost:5432`

### What the environment does automatically

When the `app` service starts, the project:

- installs PHP dependencies
- creates `.env` if it does not exist
- generates `APP_KEY` if needed
- runs migrations
- imports products from the spreadsheet
- runs `OrderSeeder`
- starts the Laravel server

The `frontend` service installs Node dependencies and starts Vite with hot reload.

---

## Running without Docker

### Back-end

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan products:import
php artisan db:seed --class=OrderSeeder
php artisan serve
```

### Front-end

```bash
npm install
npm run dev
```

### Database

This project is configured for **PostgreSQL**.

The search endpoint uses `ILIKE`, which is PostgreSQL-specific. If the goal is to port it to MySQL, this point would need to be adjusted.

---

## Useful commands

```bash
# Run back-end tests
php artisan test

# Run front-end tests
npm run test:front

# Import catalog manually
php artisan products:import
```

---

## What I would do in a next iteration

If this case evolved into a real product, the most natural next steps would be:

- authentication and access profiles
- order cancellation with stock restoration
- stock movement history
- pagination and filtering in the catalog beyond autocomplete
- API Resources for standardized responses
- OpenAPI / Swagger documentation
- observability and business logs
- CI pipeline to run PHPUnit and Vitest automatically

---

## Final notes

This project was designed to demonstrate a pragmatic approach:

- solve the business problem
- protect the data
- keep the code readable
- organize the application in a way that matches the scope

There is no abstraction for vanity. The existing separations were chosen to make the system more predictable, testable, and easier to evolve.