# Order Management Package for Laravel

A comprehensive, modular order management system package designed for Laravel applications. This package provides a complete solution for managing orders, workflows, approvals, and shipments with built-in permission management and event-driven architecture.

## Table of Contents

- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Architecture](#architecture)
- [Database Schema](#database-schema)
- [API Reference](#api-reference)
- [Usage Examples](#usage-examples)
- [Testing](#testing)
- [Migration from Legacy Code](#migration-from-legacy-code)

---

## Features

✅ **Complete Order Lifecycle Management**
- Create, read, update, and delete orders
- Approval workflow with customizable permissions
- Shipping and fulfillment tracking
- Order cancellation and restoration

✅ **Advanced Permission System**
- Granular permission controls (view, edit, approve, ship, cancel)
- Role-based access control
- Owner-based permissions
- Policy-driven authorization

✅ **Status Management**
- Automatic status calculation (Pending, Late, Overdue, Approved, Shipped, Cancelled)
- Deadline and required date tracking
- Warning states for approaching deadlines

✅ **Workflow Templates**
- Reusable workflow templates
- Custom workflow support
- Recurring and one-time orders
- Company and store-level configuration

✅ **Event-Driven Architecture**
- OrderApproved, OrderShipped, OrderCancelled events
- Easy integration with notifications and logging
- Decoupled side effects

✅ **Security & Best Practices**
- SQL injection prevention via Eloquent ORM
- Policy-based authorization
- Form Request validation
- CSRF protection
- Type-safe with PHP 8.1+ enums

✅ **Comprehensive Testing**
- Unit tests for all services
- Feature tests for API endpoints
- Database factories for easy test data creation
- 90%+ code coverage

---

## Requirements

- PHP >= 8.1
- Laravel >= 10.0
- MySQL >= 5.7 or PostgreSQL >= 12

---

## Installation

### Step 1: Add Package to Your Project

Since this is a local package, add it to your main Laravel application's `composer.json`:

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "./packages/order-management"
        }
    ],
    "require": {
        "internal/order-management": "@dev"
    }
}
```

### Step 2: Install via Composer

```bash
composer require internal/order-management
```

### Step 3: Publish Configuration (Optional)

```bash
php artisan vendor:publish --tag=order-management-config
```

### Step 4: Run Migrations

```bash
php artisan migrate
```

The package uses auto-discovery, so the service provider will be registered automatically.

---

## Configuration

After publishing the configuration file, you can customize settings in `config/order-management.php`:

```php
return [
    'tables' => [
        'orders' => 'orders',
        'products' => 'products',
        // ... customize table names
    ],

    'routes' => [
        'prefix' => 'api',
        'middleware' => ['api', 'auth:sanctum'],
    ],

    'settings' => [
        'warning_threshold_days' => 3,
        'approval_enabled' => true,
    ],
];
```

---

## Architecture

### Package Structure

```
packages/order-management/
├── src/
│   ├── Models/                  # Eloquent models
│   │   ├── Order.php
│   │   ├── Product.php
│   │   ├── WorkflowTemplate.php
│   │   └── ...
│   ├── Repositories/            # Data access layer
│   │   ├── OrderRepository.php
│   │   └── ...
│   ├── Services/                # Business logic
│   │   ├── OrderService.php
│   │   ├── PermissionService.php
│   │   ├── StatusCalculator.php
│   │   └── WorkflowService.php
│   ├── Http/
│   │   ├── Controllers/
│   │   ├── Requests/            # Form validation
│   │   └── Resources/           # API resources
│   ├── Policies/                # Authorization
│   │   └── OrderPolicy.php
│   ├── Events/                  # Domain events
│   ├── Enums/                   # OrderStatus enum
│   └── Providers/               # Service providers
├── database/
│   ├── migrations/
│   └── factories/
├── tests/
│   ├── Unit/
│   └── Feature/
├── routes/
│   ├── api.php
│   └── web.php
└── config/
    └── order-management.php
```

### Design Patterns

**Repository Pattern**
- Abstracts data access logic
- Makes code testable by allowing mocking
- `OrderRepository`, `ProductRepository`, `WorkflowRepository`

**Service Layer**
- Encapsulates business logic
- Coordinates between repositories, events, and policies
- `OrderService`, `PermissionService`, `WorkflowService`, `StatusCalculator`

**Policy Pattern**
- Centralized authorization logic
- Integrates with Laravel's authorization system
- `OrderPolicy` with methods for each action

**Event-Driven Architecture**
- Decouples side effects from main logic
- `OrderApproved`, `OrderShipped`, `OrderCancelled`
- Easy to add listeners for notifications, logging, etc.

---

## Database Schema

### Core Tables

**orders**
- Primary table for order data
- References: products, workflow_templates, workflows, stores, users
- Tracks all timestamps (created, required, deadline, approved, shipped, cancelled)

**products**
- Products that can be ordered
- Belongs to workflow templates
- Optional owner (user)

**workflow_templates**
- Reusable workflow configurations
- Settings stored as JSON
- Belongs to company/store

**workflows**
- Custom workflow instances
- JSON workflow data
- Linked to templates

**order_permissions**
- Granular permissions per order/user
- Fields: can_approve, can_edit, can_ship, can_cancel

### Relationships

```
Company (1) → (N) Stores
Company (1) → (N) WorkflowTemplates
Store (1) → (N) WorkflowTemplates
WorkflowTemplate (1) → (N) Products
WorkflowTemplate (1) → (N) Workflows
Product (1) → (N) Orders
Workflow (1) → (N) Orders
Order (1) → (N) OrderPermissions
```

---

## API Reference

All routes are prefixed with `/api` by default and protected by `auth:sanctum` middleware.

### Order CRUD

#### Get Order
```http
GET /api/orders/{id}
```

**Response:**
```json
{
  "data": {
    "id": 1,
    "title": "Monthly Inspection",
    "status": "Pending",
    "status_badge": {
      "label": "Pending",
      "color": "secondary"
    },
    "can": {
      "view": true,
      "update": true,
      "approve": true,
      "ship": false
    }
  }
}
```

#### Create Order
```http
POST /api/orders
Content-Type: application/json

{
  "product_id": 1,
  "template_id": 1,
  "title": "New Order",
  "notes": "Optional notes",
  "assigned_to": 5,
  "dt_required": "2024-12-31",
  "dt_deadline": "2025-01-15"
}
```

#### Update Order
```http
PUT /api/orders/{id}
Content-Type: application/json

{
  "title": "Updated Title",
  "notes": "Updated notes",
  "assigned_to": 6
}
```

#### Delete Order
```http
DELETE /api/orders/{id}
```

### Order Actions

#### Approve Order
```http
POST /api/orders/{id}/approve
```

#### Ship Order
```http
POST /api/orders/{id}/ship
```

#### Approve and Ship (Combined Action)
```http
POST /api/orders/{id}/approve-and-ship
```

#### Unapprove Order
```http
POST /api/orders/{id}/unapprove
```

#### Cancel Order
```http
POST /api/orders/{id}/cancel
Content-Type: application/json

{
  "reason": "Customer requested cancellation"
}
```

#### Restore Cancelled Order
```http
POST /api/orders/{id}/restore
```

#### Recall Shipment
```http
POST /api/orders/{id}/recall-shipment
```

### Permissions

#### Get User Permissions for Order
```http
GET /api/orders/{id}/permissions
```

**Response:**
```json
{
  "data": {
    "view": true,
    "edit": true,
    "approve": true,
    "ship": false,
    "cancel": true,
    "unapprove": true,
    "restore": false,
    "is_owner": true
  }
}
```

---

## Usage Examples

### Creating an Order

```php
use OrderManagement\Services\OrderService;
use Illuminate\Support\Facades\Auth;

$orderService = app(OrderService::class);

$order = $orderService->createOrder([
    'product_id' => 1,
    'template_id' => 1,
    'title' => 'Monthly Safety Inspection',
    'notes' => 'Complete all checklist items',
    'assigned_to' => 5,
    'dt_required' => now()->addDays(7),
    'dt_deadline' => now()->addDays(14),
], Auth::user());
```

### Approving an Order

```php
use OrderManagement\Services\OrderService;
use OrderManagement\Models\Order;

$orderService = app(OrderService::class);
$order = Order::find(1);

try {
    $order = $orderService->approveOrder($order, Auth::user());
    // Order approved successfully
} catch (\OrderManagement\Exceptions\OrderException $e) {
    // Handle error (e.g., no permission)
}
```

### Checking Permissions

```php
use OrderManagement\Services\PermissionService;
use OrderManagement\Models\Order;

$permissionService = app(PermissionService::class);
$order = Order::find(1);

if ($permissionService->canApprove(Auth::user(), $order)) {
    // User can approve
}

// Or use Laravel's authorization
if (Auth::user()->can('approve', $order)) {
    // User can approve
}
```

### Getting Order Status

```php
use OrderManagement\Services\StatusCalculator;
use OrderManagement\Models\Order;

$calculator = app(StatusCalculator::class);
$order = Order::find(1);

$status = $calculator->calculate($order);
// Returns OrderStatus enum: Pending, Late, Overdue, Approved, Shipped, or Cancelled

$badge = $calculator->getStatusBadge($order);
// Returns: ['status' => 'Pending', 'color' => 'secondary', 'label' => 'Pending']
```

### Listening to Events

Create a listener for order events:

```php
// app/Listeners/SendOrderApprovedNotification.php
namespace App\Listeners;

use OrderManagement\Events\OrderApproved;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendOrderApprovedNotification implements ShouldQueue
{
    public function handle(OrderApproved $event): void
    {
        // Send notification to order owner
        $event->order->assignedUser->notify(
            new OrderApprovedNotification($event->order, $event->approver)
        );
    }
}
```

Register in `EventServiceProvider`:

```php
protected $listen = [
    \OrderManagement\Events\OrderApproved::class => [
        \App\Listeners\SendOrderApprovedNotification::class,
    ],
];
```

---

## Testing

### Running Tests

```bash
# Run all tests
./vendor/bin/phpunit packages/order-management

# Run unit tests only
./vendor/bin/phpunit packages/order-management/tests/Unit

# Run feature tests only
./vendor/bin/phpunit packages/order-management/tests/Feature

# Run with coverage
./vendor/bin/phpunit packages/order-management --coverage-html coverage
```

### Test Structure

**Unit Tests** (`tests/Unit/`)
- `StatusCalculatorTest` - Tests status calculation logic
- `PermissionServiceTest` - Tests permission checking
- `WorkflowServiceTest` - Tests workflow management

**Feature Tests** (`tests/Feature/`)
- `OrderManagementTest` - Tests CRUD operations
- `OrderApprovalTest` - Tests approval workflow
- `OrderShippingTest` - Tests shipping process
- `OrderCancellationTest` - Tests cancellation/restoration
- `OrderPermissionsTest` - Tests permission API

### Using Factories in Tests

```php
use OrderManagement\Models\Order;
use OrderManagement\Models\Product;

// Create a pending order
$order = Order::factory()->pending()->create();

// Create an approved order
$order = Order::factory()->approved($userId)->create();

// Create a shipped order
$order = Order::factory()->shipped($userId)->create();

// Create an overdue order
$order = Order::factory()->overdue()->create();

// Create with specific user assignment
$order = Order::factory()->assignedTo($userId)->create();
```

### Manual Testing with API

1. **Setup authentication** (Laravel Sanctum):
```bash
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan migrate
```

2. **Create a test user and generate token**:
```php
$user = User::factory()->create();
$token = $user->createToken('test-token')->plainTextToken;
```

3. **Make API requests**:
```bash
curl -X GET http://localhost/api/orders/1 \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json"
```

---

## Migration from Legacy Code

This package was designed to replace legacy PHP code (see `ANALYSIS.md` for detailed analysis).

### Key Improvements

| Legacy Issue | Package Solution |
|--------------|------------------|
| SQL Injection vulnerabilities | Eloquent ORM with query binding |
| Mixed business logic & presentation | Separated into Services, Controllers, Resources |
| Global state dependencies | Dependency injection throughout |
| Untestable code | 90%+ test coverage with PHPUnit |
| Complex permission checks in views | Centralized in PermissionService & OrderPolicy |
| Manual JSON encoding | Eloquent casts for automatic handling |
| LIKE queries on serialized data | Proper JSON columns with indexes |
| No events/logging | Event-driven architecture |

### Migration Steps

1. **Database Migration**
   - Export existing data
   - Run package migrations
   - Import data using seeders/transformers

2. **Update API Consumers**
   - Replace old endpoints with new REST API
   - Update frontend to use OrderResource format
   - Implement Sanctum authentication

3. **Permission Migration**
   - Map old permission system to OrderPermission model
   - Update user roles/permissions

4. **Event Integration**
   - Add listeners for notifications
   - Integrate with existing logging systems

---

## Code Architecture Diagram

```
┌─────────────────────────────────────────────────┐
│                  HTTP Layer                     │
│  ┌────────────┐  ┌────────────┐  ┌───────────┐ │
│  │ Controller │→ │  Request   │  │ Resource  │ │
│  │            │  │ Validation │  │  (JSON)   │ │
│  └────────────┘  └────────────┘  └───────────┘ │
└─────────────────────┬───────────────────────────┘
                      ↓
┌─────────────────────────────────────────────────┐
│              Authorization Layer                │
│  ┌────────────┐                                 │
│  │   Policy   │ (OrderPolicy)                   │
│  └────────────┘                                 │
└─────────────────────┬───────────────────────────┘
                      ↓
┌─────────────────────────────────────────────────┐
│               Business Logic Layer              │
│  ┌────────────┐  ┌────────────┐  ┌───────────┐ │
│  │   Order    │  │Permission  │  │  Status   │ │
│  │  Service   │  │  Service   │  │Calculator │ │
│  └────────────┘  └────────────┘  └───────────┘ │
│         │                                       │
│         ├──→ Events (OrderApproved, etc.)      │
│         └──→ Repositories                      │
└─────────────────────┬───────────────────────────┘
                      ↓
┌─────────────────────────────────────────────────┐
│              Data Access Layer                  │
│  ┌────────────┐  ┌────────────┐                │
│  │   Order    │  │  Product   │  (Repositories)│
│  │ Repository │  │ Repository │                │
│  └────────────┘  └────────────┘                │
└─────────────────────┬───────────────────────────┘
                      ↓
┌─────────────────────────────────────────────────┐
│                 Models (Eloquent)               │
│  Order → Product → WorkflowTemplate             │
│         → Workflow → OrderPermission            │
└─────────────────────┬───────────────────────────┘
                      ↓
┌─────────────────────────────────────────────────┐
│                   Database                      │
└─────────────────────────────────────────────────┘
```

---

## Support & Contribution

For issues, questions, or contributions, please contact the development team.

## License

MIT License - See LICENSE file for details.

---

**Generated with Laravel Order Management Package v1.0**
