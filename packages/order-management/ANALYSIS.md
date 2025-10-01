# Legacy Code Analysis: example.inc.php

## Executive Summary
The legacy code is an order review/management system that handles inspection forms, permissions, and workflow states. It combines database queries, business logic, and presentation in a single file with significant security and maintainability concerns.

---

## What the Legacy Code Does

### 1. **Data Retrieval (Lines 5-53)**

#### Order Data Query (Lines 5-31)
```sql
SELECT o.order_id, o.customer_id, o.store_id, o.product_id, o.workflow_id,
       o.template_id, o.id, o.title, o.notes, o.dt_created, ...
```
**Purpose:**
- Fetches comprehensive order details including all user assignments and timestamps
- Performs 4 LEFT OUTER JOINs to get user names (assigned user, approver, shipper, canceller)
- Calculates dynamic status using CASE statements (Cancelled, Shipped, Approved, Overdue, Late, Pending)
- Determines if order belongs to current user (`is_mine`)
- Checks if current user can approve the order via subquery in permissions table

**Security Issues:**
- SQL injection vulnerabilities (direct variable interpolation: `$_SESSION['user']['user_id']`, `$order_id`, `$APP_CONFIG['module']`)
- No prepared statements or parameter binding

#### Product Data Query (Lines 33-43)
```sql
SELECT p.product_id, p.owner_id, p.name, wt.workflow_name, wt.icon, ...
```
**Purpose:**
- Fetches product details with workflow template information
- Gets product owner name via JOIN with user_accounts

**Security Issues:**
- SQL injection via `$dbGET_ORDER['product_id']`

#### Template & Workflow Queries (Lines 45-53)
**Purpose:**
- Loads workflow template settings and approval configuration
- Retrieves workflow data (JSON-encoded)
- Determines approval style from store/company settings

**Issues:**
- Query within query pattern (inefficient)
- Mixed conditional logic for store vs company lookup
- JSON decoding without validation

#### Field Lookup Queries (Lines 55-59)
**Purpose:**
- Finds specific field IDs for:
  - Completion date field (using LIKE on serialized PHP string)
  - Priority level field
- Stores in `$ORDER_FIELDS` array

**Critical Issues:**
- Using LIKE on serialized data is extremely fragile and slow
- Will break if serialization format changes

---

### 2. **Presentation Logic (Lines 62-131)**

#### Order Information Table (Lines 63-90)
**Displays:**
- Assigned user with avatar
- Product name with icon and link (if user has permission)
- Order status with badge
- Product owner
- Required date (with warning/danger styling based on proximity)
- Workflow frequency
- Deadline (conditional on approval style)

**Logic Issues:**
- Complex conditional styling mixed with HTML
- Permission checking within view (`check_user_permission()`)
- Date calculations for warnings inline (3 days = 259200 seconds)

#### Action Buttons (Lines 91-130)
**Conditional Button Display:**

1. **Cancel/Restore Order** (Lines 94-100)
   - Show "Cancel" if status is Pending or Late AND user has cancel permission
   - Show "Restore" if status is Cancelled

2. **Unapprove Order** (Lines 102-105)
   - If already approved AND
   - (User owns it OR can approve OR can ship) AND
   - Status is Pending/Late/Approved AND
   - Not past deadline (unless user has edit_overdue permission)

3. **Ship Order** (Lines 107-113)
   - User has ship permission AND no incomplete validations (or order cancelled)
   - Show "Ship" if Approved
   - Show "Recall Shipment" if Shipped

4. **Approve Order** (Lines 114-126)
   - NOT cancelled AND NOT already approved
   - User owns order OR can approve
   - Two variations:
     - If can ship AND no incomplete validations: Show both "Approve Order" and "Approve & Ship"
     - Otherwise: Show only "Approve Order"

5. **Force Approve** (Lines 123-125)
   - User has edit permission but doesn't own/can't approve
   - Allows overriding normal approval workflow

**Issues:**
- Complex nested conditionals difficult to test
- Business logic in view layer
- Permission checks scattered throughout
- Magic numbers (259200 for 3 days)

---

## Migration Strategy & Solution Mapping

### Why This Architecture Was Chosen

| Legacy Problem | Laravel Solution | Rationale |
|----------------|------------------|-----------|
| **SQL Injection** | Eloquent ORM with relationships | Type-safe, automatic query binding |
| **Mixed concerns** | MVC + Service Layer | Separation of business logic, data access, presentation |
| **Global state** | Dependency Injection | Testable, explicit dependencies |
| **Complex permissions** | Policy classes | Centralized, reusable authorization |
| **Status calculation** | StatusCalculator service | Single responsibility, testable |
| **Inline validation** | Form Requests | Declarative validation rules |
| **Scattered business logic** | OrderService | Centralized order operations |
| **No events** | Laravel Events | Decoupled side effects (notifications, logging) |
| **Fragile field lookups** | Eloquent relationships | Type-safe, indexed queries |
| **JSON handling** | Casts & Attributes | Automatic serialization with validation |

---

### Detailed Mapping

#### 1. Status Calculation (Legacy Lines 13-20)
**Legacy:**
```php
CASE
    WHEN o.dt_cancelled > 0 AND o.dt_shipped > 0 THEN 'Cancelled'
    WHEN o.dt_shipped > 0 THEN 'Shipped'
    WHEN o.dt_approved > 0 THEN 'Approved'
    WHEN o.dt_deadline > 0 AND '.time().' > o.dt_deadline THEN 'Overdue'
    WHEN o.dt_required > 0 AND '.time().' > o.dt_required THEN 'Late'
    ELSE 'Pending'
END as status
```

**Solution:** `StatusCalculator` service
```php
public function calculate(Order $order): OrderStatus
{
    if ($order->dt_cancelled && $order->dt_shipped) return OrderStatus::Cancelled;
    if ($order->dt_shipped) return OrderStatus::Shipped;
    if ($order->dt_approved) return OrderStatus::Approved;
    if ($order->dt_deadline && now()->gt($order->dt_deadline)) return OrderStatus::Overdue;
    if ($order->dt_required && now()->gt($order->dt_required)) return OrderStatus::Late;
    return OrderStatus::Pending;
}
```
- Unit testable
- No SQL injection risk
- Reusable across codebase
- Type-safe with enum

#### 2. Permission Checking (Legacy Lines 21-22)
**Legacy:**
```sql
CASE WHEN o.assigned_to = '.$_SESSION['user']['user_id'].' THEN 1 ELSE 0 END AS is_mine,
CASE WHEN EXISTS (SELECT user_id FROM app_permissions WHERE ...) THEN 1 ELSE 0 END AS can_approve
```

**Solution:** `OrderPolicy` + `PermissionService`
```php
// OrderPolicy
public function approve(User $user, Order $order): bool
{
    return $this->permissionService->canApprove($user, $order);
}

// PermissionService
public function canApprove(User $user, Order $order): bool
{
    if ($order->assigned_to === $user->id) return true;
    return $order->permissions()
        ->where('user_id', $user->id)
        ->where('can_approve', true)
        ->exists();
}
```
- Authorization centralized in policies
- Reusable permission service
- Works with Laravel's authorization system (`$this->authorize()`)

#### 3. Complex Queries with Joins (Legacy Lines 5-31)
**Legacy:** Single massive query with 4 LEFT JOINs

**Solution:** Eloquent relationships in models
```php
class Order extends Model
{
    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    // Usage with eager loading
    $order = Order::with(['assignedUser', 'product.owner', 'product.template'])
        ->findOrFail($id);
}
```
- N+1 query prevention via eager loading
- Type-safe relationships
- Automatic query optimization

#### 4. JSON Data Handling (Legacy Lines 48, 52-53)
**Legacy:**
```php
$dbGET_TEMPLATE['settings'] = json_decode($dbGET_TEMPLATE['settings'], true);
$dbGET_WORKFLOW['workflow_data'] = json_decode($dbGET_WORKFLOW['workflow_data'], true);
```

**Solution:** Eloquent casts
```php
class WorkflowTemplate extends Model
{
    protected $casts = [
        'settings' => 'array',
    ];
}

class Workflow extends Model
{
    protected $casts = [
        'workflow_data' => 'array',
    ];
}
```
- Automatic serialization/deserialization
- Type-safe
- No manual JSON handling

#### 5. Conditional Button Logic (Legacy Lines 94-127)
**Legacy:** Complex nested if statements in view

**Solution:** Policy methods + View composer/Resource
```php
// In OrderResource
public function toArray($request): array
{
    return [
        'id' => $this->order_id,
        'can' => [
            'cancel' => $request->user()->can('cancel', $this->resource),
            'approve' => $request->user()->can('approve', $this->resource),
            'ship' => $request->user()->can('ship', $this->resource),
            'unapprove' => $request->user()->can('unapprove', $this->resource),
        ],
        // ...
    ];
}
```
- Frontend receives permission flags
- Business logic stays in backend
- Single source of truth

---

## Security Improvements

### Legacy Vulnerabilities
1. **SQL Injection** (High)
   - Direct variable interpolation in queries
   - No parameterization
   - Affects all queries

2. **Session Hijacking Risk** (Medium)
   - Direct `$_SESSION` access
   - No CSRF protection visible

3. **Serialization Attacks** (Medium)
   - LIKE queries on serialized data (line 56)
   - Could be exploited with crafted field settings

### Laravel Solutions
1. **SQL Injection Prevention**
   - Eloquent ORM with query builder
   - Automatic parameter binding
   - Named parameters

2. **CSRF Protection**
   - Built-in middleware
   - Automatic token validation

3. **Authentication/Authorization**
   - Laravel Sanctum/Passport
   - Policy-based authorization
   - Middleware protection

---

## Performance Improvements

### Legacy Issues
- 6+ separate queries per page load
- No query result caching
- LIKE queries on serialized data
- Subqueries in SELECT clause

### Laravel Optimizations
- Eager loading (1-2 queries instead of 6+)
- Query caching available
- Proper indexes via migrations
- Repository pattern for query reuse

---

## Testability Comparison

### Legacy Code
- ❌ Cannot unit test (requires database, session, globals)
- ❌ Cannot mock dependencies
- ❌ Cannot test business logic independently
- ❌ Must test through UI

### Laravel Package
- ✅ Unit tests for StatusCalculator
- ✅ Unit tests for Services (with mocked repositories)
- ✅ Feature tests for API endpoints
- ✅ Policy tests for authorization
- ✅ Database factories for test data
- ✅ In-memory SQLite for fast tests

---

## Conclusion

The legacy code suffers from:
1. Security vulnerabilities (SQL injection)
2. Poor separation of concerns
3. Untestable architecture
4. Performance issues
5. Fragile queries (LIKE on serialized data)
6. Complex conditional logic in views

The Laravel package addresses all these issues through:
1. Eloquent ORM (security)
2. Service layer + Policies (separation of concerns)
3. Dependency injection (testability)
4. Eager loading + caching (performance)
5. Proper relationships + migrations (data integrity)
6. API resources + policies (clean presentation layer)

This migration transforms unmaintainable legacy code into a modern, testable, secure, and performant Laravel package following industry best practices.
