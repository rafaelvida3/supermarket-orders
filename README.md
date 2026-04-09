# Supermarket Orders

A portfolio-ready supermarket order management application built with Laravel 12, Vue 3, and PostgreSQL.

It started as a technical challenge and was then refined into a small case study focused on code clarity, stock integrity, pragmatic architecture, and maintainable front-end structure.

Live demo: https://supermarket-orders.onrender.com/

## Why this project matters

This repository was shaped to show how I approach business software when the goal is not just to "make it work", but to keep the code predictable and safe:

- controllers stay thin and delegate the main use case to an action
- order creation runs inside a database transaction
- stock is validated on the server, not trusted to the UI
- duplicated products are consolidated before persistence
- historical prices are preserved in `order_items`
- database constraints reinforce invariants beyond the application layer
- the front end is organized around the order flow without adding unnecessary state management

## Main stack

### Back-end

- Laravel 12
- PostgreSQL
- Eloquent ORM
- Form Requests for validation
- Maatwebsite Excel for catalog import

### Front-end

- Vue 3 with the Composition API
- Vite
- Vue Router
- PrimeVue
- Tailwind CSS
- Axios
- Vitest

### Tooling

- Docker Compose for local development
- GitHub Actions for PHP lint, Laravel tests, front-end tests, and build validation

## Core flow

### 1. Catalog import

The catalog is loaded from `storage/app/Products.xlsx` with `php artisan products:import`.

The import is designed to be safe to rerun:

- valid rows are mapped to `Product`
- incomplete rows are ignored
- existing rows are updated through upsert behavior
- the PostgreSQL sequence is resynchronized after import

### 2. Order creation

The user fills:

- customer name
- delivery date
- one or more products with quantity

The front end handles autocomplete, subtotal display, total calculation, duplicate item merging, and payload assembly.

### 3. Stock-safe persistence

`CreateOrderAction` is responsible for the critical flow:

1. aggregate duplicated products
2. start a transaction
3. lock products with `lockForUpdate()`
4. validate existence and stock
5. create the order
6. create `order_items` with a price snapshot
7. decrement stock
8. persist the final total

This avoids partially saved orders and keeps stock consistent under concurrent writes.

## Modeling decisions

### `products`

Stores the catalog and the current stock.

Relevant fields:

- `id`
- `name`
- `price`
- `qty_stock`

### `orders`

Stores the order header.

Relevant fields:

- `id`
- `customer_name`
- `delivery_date`
- `total`

### `order_items`

Stores each purchased product.

Relevant fields:

- `order_id`
- `product_id`
- `qty`
- `unit_price`
- `subtotal`

### Why `unit_price` and `subtotal` are stored

Orders are historical records. If a catalog price changes later, past orders must still reflect the original value.

### Why `unique(order_id, product_id)` matters

The same product should not be saved twice inside a single order. The UI already tries to prevent that, but the database also protects the invariant.

## Project structure

```text
app/
├── Actions/Orders/CreateOrderAction.php
├── Console/Commands/ImportProducts.php
├── Http/Controllers/
│   ├── OrderController.php
│   └── ProductController.php
├── Http/Requests/StoreOrderRequest.php
├── Imports/ProductsImport.php
└── Models/

resources/js/
├── components/
│   ├── common/LoadingOverlay.vue
│   └── orders/OrderItemsSection.vue
├── composables/
│   ├── orderFormUtils.js
│   ├── useLoadingOverlay.js
│   └── useOrderForm.js
├── pages/
│   ├── OrderPage.vue
│   ├── OrdersList.vue
│   ├── StockPage.vue
│   └── NotFound.vue
├── router/index.js
├── services/
│   ├── apiClient.js
│   ├── orderService.js
│   └── productService.js
└── helpers/index.js

.github/workflows/ci.yml
Dockerfile
docker-compose.yml
```

## API overview

### `GET /api/products`

Autocomplete-oriented product search.

Example:

```http
GET /api/products?q=ri
```

Response:

```json
{
  "data": [
    {
      "id": 1,
      "name": "Rice",
      "price": "10.50",
      "qty_stock": 20
    }
  ]
}
```

### `GET /api/products/stock`

Returns the full stock snapshot used by the inventory page.

### `GET /api/orders`

Returns the saved orders list in descending order.

### `GET /api/orders/{id}`

Returns one order with its items and product names.

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
  "data": {
    "id": 1,
    "total": "28.00"
  },
  "message": "Pedido criado com sucesso."
}
```

## Validation strategy

### Front end

The interface improves the flow by:

- requiring customer name and delivery date
- requiring at least one selected product
- showing subtotal and total in real time
- preventing quantity below 1
- warning when the selected product is out of stock
- merging duplicated selections before submission

### Back end

The API remains the source of truth by:

- validating request shape with `StoreOrderRequest`
- validating product existence
- validating quantity bounds
- validating stock inside the transaction using locked rows
- aggregating duplicated items even if the client sends them twice

## Tests

The repository includes automated tests for both the back end and the front end.

Covered scenarios include:

- successful order creation with stock deduction
- rejection when stock is insufficient
- duplicate product consolidation
- order listing and order details
- product import behavior
- product search filtering and limits
- order form total calculation and payload assembly
- front-end service behavior

## Running locally with Docker

### Requirements

- Docker Desktop
- Docker Compose

### Start the application

```bash
cp .env.example .env
docker compose up --build
```

Available services:

- Laravel API: `http://localhost:8000`
- Vue/Vite: `http://localhost:5173`
- PostgreSQL: `localhost:5432`

### Load demo data

```bash
docker compose exec app sh docker/app/setup-demo.sh
```

Demo bootstrap is intentionally separate from normal startup, so the app container does not rerun migrations and seeds on every boot.

## Running locally without Docker

### Minimal application setup

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan serve
```

### Full demo bootstrap

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan products:import
php artisan db:seed --class=OrderSeeder
npm ci
npm run build
```

Or use the Composer helper:

```bash
composer setup:demo
```

## Useful commands

```bash
composer setup:app
composer setup:demo
php artisan test
npm run test:front
php artisan products:import
```

## Possible next iterations

Natural next steps for a production version would be:

- order cancellation with stock restoration
- stock movement history
- pagination and richer catalog filters
- API Resources for response standardization
- authentication and access control
- OpenAPI documentation

## Final note

The goal of this project is simple: show practical engineering judgment.

It is intentionally small in scope, but the important parts are treated seriously: validation, transaction safety, persistence modeling, test coverage, and readable structure.
