# Order Management API - Quick Reference

## Base URL
All endpoints are prefixed with `/api` (configurable)

## Authentication
All requests require Bearer token authentication (Laravel Sanctum):
```
Authorization: Bearer {your-token}
```

---

## Endpoints Overview

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/orders/{id}` | Get order details |
| POST | `/orders` | Create new order |
| PUT | `/orders/{id}` | Update order |
| DELETE | `/orders/{id}` | Delete order |
| POST | `/orders/{id}/approve` | Approve order |
| POST | `/orders/{id}/ship` | Ship order |
| POST | `/orders/{id}/approve-and-ship` | Approve and ship in one action |
| POST | `/orders/{id}/unapprove` | Remove approval |
| POST | `/orders/{id}/cancel` | Cancel order |
| POST | `/orders/{id}/restore` | Restore cancelled order |
| POST | `/orders/{id}/recall-shipment` | Recall shipped order |
| GET | `/orders/{id}/permissions` | Get user permissions |

---

## Request/Response Examples

### GET /api/orders/{id}
**Response:**
```json
{
  "data": {
    "id": 1,
    "title": "Monthly Safety Inspection",
    "status": "Pending",
    "status_badge": {
      "label": "Pending",
      "color": "secondary"
    },
    "dt_required": "2024-12-31T00:00:00Z",
    "dt_deadline": "2025-01-15T00:00:00Z",
    "assigned_user": {
      "id": 5,
      "name": "John Doe"
    },
    "product": {
      "id": 1,
      "name": "Safety Inspection Product"
    },
    "can": {
      "view": true,
      "update": true,
      "approve": true,
      "ship": false,
      "cancel": true
    }
  }
}
```

### POST /api/orders
**Request:**
```json
{
  "product_id": 1,
  "template_id": 1,
  "title": "Weekly Inspection",
  "notes": "Complete all safety checks",
  "assigned_to": 5,
  "dt_required": "2024-12-31",
  "dt_deadline": "2025-01-15"
}
```

**Response:**
```json
{
  "message": "Order created successfully",
  "data": {
    "id": 123,
    "title": "Weekly Inspection",
    "status": "Pending"
  }
}
```

### PUT /api/orders/{id}
**Request:**
```json
{
  "title": "Updated Title",
  "notes": "Updated notes",
  "assigned_to": 6
}
```

### POST /api/orders/{id}/approve
**Response:**
```json
{
  "message": "Order approved successfully",
  "data": {
    "id": 1,
    "status": "Approved",
    "dt_approved": "2024-11-20T10:30:00Z"
  }
}
```

### POST /api/orders/{id}/cancel
**Request:**
```json
{
  "reason": "Customer requested cancellation"
}
```

**Response:**
```json
{
  "message": "Order cancelled successfully",
  "data": {
    "id": 1,
    "status": "Cancelled",
    "cancel_reason": "Customer requested cancellation"
  }
}
```

### GET /api/orders/{id}/permissions
**Response:**
```json
{
  "data": {
    "view": true,
    "edit": true,
    "approve": true,
    "ship": false,
    "cancel": true,
    "unapprove": false,
    "restore": false,
    "is_owner": true
  }
}
```

---

## Status Values

| Status | Description | Color |
|--------|-------------|-------|
| `Pending` | Order created, not yet due | `secondary` |
| `Late` | Past required date but not deadline | `warning` |
| `Overdue` | Past deadline | `danger` |
| `Approved` | Approved but not shipped | `info` |
| `Shipped` | Order fulfilled | `success` |
| `Cancelled` | Order cancelled | `dark` |

---

## Error Responses

### 401 Unauthorized
```json
{
  "message": "Unauthenticated."
}
```

### 403 Forbidden
```json
{
  "message": "You do not have permission to perform this action."
}
```

### 404 Not Found
```json
{
  "message": "Order not found."
}
```

### 422 Validation Error
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "product_id": ["The product id field is required."],
    "title": ["The title field is required."]
  }
}
```

### 500 Server Error
```json
{
  "message": "Order must be approved before shipping."
}
```

---

## Permission Rules

| Action | Requirements |
|--------|--------------|
| **View** | Owner OR has view permission |
| **Edit** | Owner OR has edit permission, AND not shipped/cancelled, AND not past deadline (unless special permission) |
| **Approve** | Owner OR has approve permission, AND not already approved |
| **Ship** | Has ship permission, AND approved, AND not already shipped |
| **Cancel** | Has cancel permission, AND not already cancelled/shipped |
| **Unapprove** | Owner OR has approve/ship permission, AND approved, AND not shipped |
| **Restore** | Has cancel permission, AND currently cancelled |

---

## cURL Examples

### Get Order
```bash
curl -X GET "http://localhost/api/orders/1" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

### Create Order
```bash
curl -X POST "http://localhost/api/orders" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "product_id": 1,
    "template_id": 1,
    "title": "New Order",
    "dt_required": "2024-12-31"
  }'
```

### Approve Order
```bash
curl -X POST "http://localhost/api/orders/1/approve" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

### Cancel Order
```bash
curl -X POST "http://localhost/api/orders/1/cancel" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "reason": "Customer request"
  }'
```

---

## JavaScript/Axios Examples

### Get Order
```javascript
const response = await axios.get('/api/orders/1', {
  headers: {
    'Authorization': `Bearer ${token}`,
    'Accept': 'application/json'
  }
});
```

### Create Order
```javascript
const response = await axios.post('/api/orders', {
  product_id: 1,
  template_id: 1,
  title: 'New Order',
  dt_required: '2024-12-31'
}, {
  headers: {
    'Authorization': `Bearer ${token}`,
    'Accept': 'application/json'
  }
});
```

### Approve Order
```javascript
const response = await axios.post(`/api/orders/${orderId}/approve`, {}, {
  headers: {
    'Authorization': `Bearer ${token}`,
    'Accept': 'application/json'
  }
});
```

---

## Rate Limiting

Default Laravel throttle applies:
- API routes: 60 requests per minute per user

Configure in `config/order-management.php`:
```php
'routes' => [
    'middleware' => ['api', 'auth:sanctum', 'throttle:60,1'],
],
```

---

**Package Version:** 1.0.0
